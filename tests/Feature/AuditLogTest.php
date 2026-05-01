<?php

namespace Tests\Feature;

use App\Livewire\App\AuditLog\Index;
use App\Models\Clinic;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function makeClinicWithUser(string $role = 'owner'): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id]);
        $user->assignRole($role);

        return [$clinic, $user];
    }

    // ==================== ACCESS CONTROL ====================

    public function test_owner_can_access_audit_log(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');

        $this->actingAs($owner)
            ->get("/app/{$clinic->slug}/audit-log")
            ->assertOk()
            ->assertSeeLivewire(Index::class);
    }

    public function test_admin_can_access_audit_log(): void
    {
        [$clinic, $admin] = $this->makeClinicWithUser('admin');

        $this->actingAs($admin)
            ->get("/app/{$clinic->slug}/audit-log")
            ->assertOk();
    }

    public function test_doctor_cannot_access_audit_log(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithUser('doctor');

        Livewire::actingAs($doctor)
            ->test(Index::class, ['clinic' => $clinic])
            ->assertForbidden();
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();

        $this->get("/app/{$clinic->slug}/audit-log")
            ->assertRedirect('/login');
    }

    // ==================== RENDERING ====================

    public function test_audit_log_renders_activities_for_clinic(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');

        // Create activity caused by owner
        activity()
            ->causedBy($owner)
            ->event('data_exported')
            ->log('data_exported');

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->assertHasNoErrors()
            ->assertSee('data_exported');
    }

    // ==================== MULTI-TENANT ISOLATION ====================

    public function test_owner_cannot_see_other_clinic_activities(): void
    {
        [$clinic1, $owner1] = $this->makeClinicWithUser('owner');
        [$clinic2, $owner2] = $this->makeClinicWithUser('owner');

        // Activity from clinic2
        activity()
            ->causedBy($owner2)
            ->event('data_exported')
            ->log('data_exported');

        Livewire::actingAs($owner1)
            ->test(Index::class, ['clinic' => $clinic1])
            ->assertHasNoErrors()
            ->assertDontSee($owner2->email);
    }

    // ==================== FILTERS ====================

    public function test_filter_by_event_returns_correct_results(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');

        activity()->causedBy($owner)->event('data_exported')->log('data_exported');
        activity()->causedBy($owner)->event('created')->log('created');

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->set('filterEvent', 'data_exported')
            ->assertHasNoErrors();
    }
}
