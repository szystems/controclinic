<?php

namespace Tests\Feature;

use App\Livewire\App\Staff\Index as StaffIndex;
use App\Models\Clinic;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StaffExportTest extends TestCase
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
        $user = User::factory()->create([
            'clinic_id' => $clinic->id,
            'role' => $role,
        ]);
        $user->assignRole($role);

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        return [$clinic, $user];
    }

    public function test_owner_can_export_staff_pdf(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        User::factory()->count(3)->create(['clinic_id' => $clinic->id, 'role' => 'doctor']);

        Livewire::actingAs($owner)
            ->test(StaffIndex::class, ['clinic' => $clinic])
            ->call('exportPdf')
            ->assertFileDownloaded(null, null, 'application/pdf');
    }

    public function test_admin_can_export_staff_pdf(): void
    {
        [$clinic, $admin] = $this->makeClinicWithUser('admin');

        Livewire::actingAs($admin)
            ->test(StaffIndex::class, ['clinic' => $clinic])
            ->call('exportPdf')
            ->assertFileDownloaded(null, null, 'application/pdf');
    }

    public function test_doctor_cannot_export_staff_pdf(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithUser('doctor');

        Livewire::actingAs($doctor)
            ->test(StaffIndex::class, ['clinic' => $clinic])
            ->call('exportPdf')
            ->assertForbidden();
    }
}
