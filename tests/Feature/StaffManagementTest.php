<?php

namespace Tests\Feature;

use App\Livewire\App\Staff\Create;
use App\Livewire\App\Staff\Edit;
use App\Livewire\App\Staff\Index;
use App\Mail\ClinicInvitationMail;
use App\Models\Clinic;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Mail::fake();
    }

    private function createClinicWithOwner(string $plan = 'free'): array
    {
        $clinic = Clinic::factory()->onboarded()->withPlan($plan)->create();
        $user = User::factory()->owner()->create(['clinic_id' => $clinic->id]);
        $user->assignRole('owner');

        // Bind current_clinic so views (upgrade-nudge) can resolve it
        app()->instance('current_clinic', $clinic);

        return [$clinic, $user];
    }

    // ===== Routes & Rendering =====

    public function test_staff_index_page_renders(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();

        $this->actingAs($owner)
            ->get(route('app.staff.index', $clinic->slug))
            ->assertOk();
    }

    public function test_staff_create_page_renders(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();

        $this->actingAs($owner)
            ->get(route('app.staff.create', $clinic->slug))
            ->assertOk();
    }

    public function test_staff_edit_page_renders(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();

        $doctor = User::factory()->doctor()->create(['clinic_id' => $clinic->id]);

        $this->actingAs($owner)
            ->get(route('app.staff.edit', ['clinic' => $clinic->slug, 'user' => $doctor->id]))
            ->assertOk();
    }

    public function test_staff_route_exists(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();

        $response = $this->actingAs($owner)
            ->get(route('app.staff.index', $clinic->slug));

        $this->assertNotEquals(404, $response->status());
    }

    // ===== Staff Index =====

    public function test_staff_index_shows_members(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $doctor = User::factory()->doctor()->create([
            'clinic_id' => $clinic->id,
            'name' => 'Dr. Test',
        ]);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->assertSee('Dr. Test');
    }

    public function test_staff_index_shows_owner(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->assertSee($owner->name);
    }

    public function test_staff_index_search_filters_members(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $doctor = User::factory()->doctor()->create([
            'clinic_id' => $clinic->id,
            'name' => 'Dr. Cardiology',
        ]);

        $assistant = User::factory()->assistant()->create([
            'clinic_id' => $clinic->id,
            'name' => 'Nurse Smith',
        ]);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->set('search', 'Cardiology')
            ->assertSee('Dr. Cardiology')
            ->assertDontSee('Nurse Smith');
    }

    public function test_staff_index_role_filter(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('group');

        $doctor = User::factory()->doctor()->create([
            'clinic_id' => $clinic->id,
            'name' => 'Dr. FilterTest',
        ]);

        $assistant = User::factory()->assistant()->create([
            'clinic_id' => $clinic->id,
            'name' => 'Assistant FilterTest',
        ]);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->set('roleFilter', 'doctor')
            ->assertSee('Dr. FilterTest')
            ->assertDontSee('Assistant FilterTest');
    }

    // ===== Create Staff =====

    public function test_owner_can_invite_doctor(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        Livewire::actingAs($owner)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('name', 'Dr. New')
            ->set('email', 'dr.new@test.com')
            ->set('role', 'doctor')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('app.staff.index', $clinic->slug));

        $this->assertDatabaseHas('clinic_invitations', [
            'clinic_id' => $clinic->id,
            'email' => 'dr.new@test.com',
            'role' => 'doctor',
        ]);

        Mail::assertSent(ClinicInvitationMail::class);
    }

    public function test_owner_can_invite_staff_member(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        Livewire::actingAs($owner)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('name', 'New Secretary')
            ->set('email', 'secretary@test.com')
            ->set('role', 'secretary')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('app.staff.index', $clinic->slug));

        $this->assertDatabaseHas('clinic_invitations', [
            'clinic_id' => $clinic->id,
            'email' => 'secretary@test.com',
            'role' => 'secretary',
        ]);
    }

    public function test_create_validates_required_fields(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        Livewire::actingAs($owner)
            ->test(Create::class, ['clinic' => $clinic])
            ->call('save')
            ->assertHasErrors(['name', 'email', 'role']);
    }

    public function test_create_validates_unique_email_in_clinic(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        User::factory()->doctor()->create([
            'clinic_id' => $clinic->id,
            'email' => 'existing@test.com',
        ]);

        Livewire::actingAs($owner)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('name', 'Another Doctor')
            ->set('email', 'existing@test.com')
            ->set('role', 'doctor')
            ->call('save')
            ->assertHasErrors('email');
    }

    // ===== Plan Limits =====

    public function test_cannot_invite_doctor_when_at_limit(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        // Solo plan: max_doctors = 1
        User::factory()->doctor()->create(['clinic_id' => $clinic->id]);

        Livewire::actingAs($owner)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('name', 'Extra Doctor')
            ->set('email', 'extra@test.com')
            ->set('role', 'doctor')
            ->call('save')
            ->assertNoRedirect();

        $this->assertDatabaseMissing('clinic_invitations', [
            'email' => 'extra@test.com',
        ]);
    }

    public function test_cannot_invite_staff_when_at_limit(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        // Solo plan: max_staff = 1
        User::factory()->assistant()->create(['clinic_id' => $clinic->id]);

        Livewire::actingAs($owner)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('name', 'Extra Assistant')
            ->set('email', 'extra@test.com')
            ->set('role', 'assistant')
            ->call('save')
            ->assertNoRedirect();

        $this->assertDatabaseMissing('clinic_invitations', [
            'email' => 'extra@test.com',
        ]);
    }

    public function test_free_plan_cannot_invite_staff(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('free');

        // Free plan: max_staff = 0
        Livewire::actingAs($owner)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('name', 'Staff Member')
            ->set('email', 'staff@test.com')
            ->set('role', 'assistant')
            ->call('save')
            ->assertNoRedirect();

        $this->assertDatabaseMissing('clinic_invitations', [
            'email' => 'staff@test.com',
        ]);
    }

    public function test_group_plan_can_invite_multiple_doctors(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('group');

        // Group plan: max_doctors = 5
        User::factory()->doctor()->count(3)->create(['clinic_id' => $clinic->id]);

        Livewire::actingAs($owner)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('name', 'Dr. Four')
            ->set('email', 'dr4@test.com')
            ->set('role', 'doctor')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('app.staff.index', $clinic->slug));
    }

    // ===== Edit Staff =====

    public function test_owner_can_edit_staff_member(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $doctor = User::factory()->doctor()->create([
            'clinic_id' => $clinic->id,
            'name' => 'Dr. Original',
        ]);

        Livewire::actingAs($owner)
            ->test(Edit::class, ['user' => $doctor])
            ->set('name', 'Dr. Updated')
            ->set('email', $doctor->email)
            ->set('role', 'doctor')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('app.staff.index', $clinic->slug));

        $this->assertDatabaseHas('users', [
            'id' => $doctor->id,
            'name' => 'Dr. Updated',
        ]);
    }

    public function test_edit_password_is_optional(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $doctor = User::factory()->doctor()->create(['clinic_id' => $clinic->id]);
        $originalPassword = $doctor->password;

        Livewire::actingAs($owner)
            ->test(Edit::class, ['user' => $doctor])
            ->set('name', 'Dr. No Password Change')
            ->call('save')
            ->assertHasNoErrors();

        $doctor->refresh();
        $this->assertEquals($originalPassword, $doctor->password);
    }

    // ===== Toggle Status =====

    public function test_owner_can_toggle_member_status(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $doctor = User::factory()->doctor()->create([
            'clinic_id' => $clinic->id,
            'is_active' => true,
        ]);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->call('toggleStatus', $doctor->id);

        $doctor->refresh();
        $this->assertFalse($doctor->is_active);
    }

    public function test_cannot_deactivate_owner(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();

        // Create another owner to test (edge case)
        $anotherOwner = User::factory()->create([
            'clinic_id' => $clinic->id,
            'role' => 'owner',
        ]);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->call('toggleStatus', $anotherOwner->id);

        // Owner should still be active
        $anotherOwner->refresh();
        $this->assertTrue((bool) $anotherOwner->is_active);
    }

    // ===== Delete Staff =====

    public function test_owner_can_delete_member(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $doctor = User::factory()->doctor()->create(['clinic_id' => $clinic->id]);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->call('deleteMember', $doctor->id);

        $this->assertSoftDeleted('users', ['id' => $doctor->id]);
    }

    public function test_cannot_delete_self(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        // Owner tries to delete self — but owner is excluded from the member list
        // so use an assistant who tries to delete herself
        $assistant = User::factory()->assistant()->create(['clinic_id' => $clinic->id]);
        $assistant->assignRole('assistant');

        // Simulate the assistant having users.manage permission temporarily
        $assistant->givePermissionTo('users.manage');

        Livewire::actingAs($assistant)
            ->test(Index::class, ['clinic' => $clinic])
            ->call('deleteMember', $assistant->id);

        // User should still exist (not soft-deleted)
        $this->assertDatabaseHas('users', [
            'id' => $assistant->id,
            'deleted_at' => null,
        ]);
    }

    // ===== Permissions =====

    public function test_non_owner_cannot_invite_staff(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $doctor = User::factory()->doctor()->create(['clinic_id' => $clinic->id]);
        $doctor->assignRole('doctor');

        Livewire::actingAs($doctor)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('name', 'Unauthorized')
            ->set('email', 'unauth@test.com')
            ->set('role', 'assistant')
            ->call('save')
            ->assertNoRedirect();

        $this->assertDatabaseMissing('clinic_invitations', ['email' => 'unauth@test.com']);
    }

    public function test_non_owner_cannot_delete_member(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $doctor = User::factory()->doctor()->create(['clinic_id' => $clinic->id]);
        $doctor->assignRole('doctor');

        $assistant = User::factory()->assistant()->create(['clinic_id' => $clinic->id]);

        Livewire::actingAs($doctor)
            ->test(Index::class, ['clinic' => $clinic])
            ->call('deleteMember', $assistant->id);

        // User should still exist (not deleted)
        $this->assertDatabaseHas('users', [
            'id' => $assistant->id,
            'deleted_at' => null,
        ]);
    }

    // ===== Usage Badges =====

    public function test_index_shows_usage_badges(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $doctor = User::factory()->doctor()->create(['clinic_id' => $clinic->id]);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->assertSee(__('staff.doctors_usage', ['current' => 1, 'max' => 1]));
    }
}
