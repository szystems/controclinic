<?php

namespace Tests\Feature;

use App\Livewire\App\Settings\Index as SettingsIndex;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ClinicDataExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function makeClinicWithOwner(): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->create([
            'clinic_id' => $clinic->id,
            'id' => $clinic->owner_id,
        ]);
        $owner->assignRole('owner');

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        return [$clinic, $owner];
    }

    /** @test */
    #[Test]
    public function owner_can_export_clinic_data_as_zip(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();

        // Create a patient so the CSV has at least one row
        Patient::factory()->create(['clinic_id' => $clinic->id]);

        $response = Livewire::actingAs($owner)
            ->test(SettingsIndex::class, ['clinic' => $clinic])
            ->call('exportData');

        // Livewire returns a StreamedResponse — check it dispatched without error
        $response->assertHasNoErrors();
    }

    /** @test */
    #[Test]
    public function non_owner_cannot_export_clinic_data(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $doctor = User::factory()->create(['clinic_id' => $clinic->id]);
        $doctor->assignRole('doctor');

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        Livewire::actingAs($doctor)
            ->test(SettingsIndex::class, ['clinic' => $clinic])
            ->call('exportData')
            ->assertForbidden();
    }
}
