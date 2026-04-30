<?php

namespace Tests\Feature;

use App\Livewire\App\Patients\Index as PatientsIndex;
use App\Livewire\App\Patients\Show as PatientsShow;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PatientsExportTest extends TestCase
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

        // Bind & share so views can resolve $currentClinic during render
        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        return [$clinic, $user];
    }

    private function downloadContent($component): string
    {
        $download = data_get($component->effects, 'download');
        $this->assertNotNull($download, 'Expected a file download effect.');

        return base64_decode($download['content']);
    }

    // ==================== CSV EXPORT ====================

    public function test_owner_can_export_patients_csv(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        Patient::factory()->create([
            'clinic_id' => $clinic->id,
            'first_name' => 'Juan',
            'last_name' => 'Perez',
        ]);

        $component = Livewire::actingAs($owner)
            ->test(PatientsIndex::class, ['clinic' => $clinic])
            ->call('exportCsv')
            ->assertFileDownloaded(null, null, 'text/csv; charset=UTF-8');

        $content = $this->downloadContent($component);
        $this->assertStringContainsString('Juan', $content);
        $this->assertStringContainsString('Perez', $content);
    }

    public function test_receptionist_cannot_export_csv(): void
    {
        [$clinic, $user] = $this->makeClinicWithUser('receptionist');

        Livewire::actingAs($user)
            ->test(PatientsIndex::class, ['clinic' => $clinic])
            ->call('exportCsv')
            ->assertForbidden();
    }

    public function test_csv_export_respects_search_filter(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        Patient::factory()->create(['clinic_id' => $clinic->id, 'first_name' => 'Maria', 'last_name' => 'Lopez']);
        Patient::factory()->create(['clinic_id' => $clinic->id, 'first_name' => 'Carlos', 'last_name' => 'Ruiz']);

        $component = Livewire::actingAs($owner)
            ->test(PatientsIndex::class, ['clinic' => $clinic])
            ->set('search', 'Maria')
            ->call('exportCsv')
            ->assertFileDownloaded();

        $content = $this->downloadContent($component);
        $this->assertStringContainsString('Maria', $content);
        $this->assertStringNotContainsString('Carlos', $content);
    }

    public function test_csv_export_isolated_to_clinic(): void
    {
        [$clinicA, $ownerA] = $this->makeClinicWithUser('owner');
        $clinicB = Clinic::factory()->onboarded()->create();

        Patient::factory()->create(['clinic_id' => $clinicA->id, 'first_name' => 'Ana', 'last_name' => 'TenantA']);
        Patient::factory()->create(['clinic_id' => $clinicB->id, 'first_name' => 'Beto', 'last_name' => 'TenantB']);

        app()->instance('current_clinic', $clinicA);
        view()->share('currentClinic', $clinicA);

        $component = Livewire::actingAs($ownerA)
            ->test(PatientsIndex::class, ['clinic' => $clinicA])
            ->call('exportCsv')
            ->assertFileDownloaded();

        $content = $this->downloadContent($component);
        $this->assertStringContainsString('TenantA', $content);
        $this->assertStringNotContainsString('TenantB', $content);
    }

    // ==================== PDF EXPORT (Index list) ====================

    public function test_owner_can_export_patients_pdf(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        Patient::factory()->count(2)->create(['clinic_id' => $clinic->id]);

        Livewire::actingAs($owner)
            ->test(PatientsIndex::class, ['clinic' => $clinic])
            ->call('exportPdf')
            ->assertFileDownloaded(null, null, 'application/pdf');
    }

    public function test_receptionist_cannot_export_patients_pdf(): void
    {
        [$clinic, $user] = $this->makeClinicWithUser('receptionist');

        Livewire::actingAs($user)
            ->test(PatientsIndex::class, ['clinic' => $clinic])
            ->call('exportPdf')
            ->assertForbidden();
    }

    // ==================== PDF EXPORT (Show — patient card) ====================

    public function test_owner_can_export_patient_card_pdf(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);

        Livewire::actingAs($owner)
            ->test(PatientsShow::class, ['patient' => $patient])
            ->call('exportPdf')
            ->assertFileDownloaded(null, null, 'application/pdf');
    }

    public function test_receptionist_cannot_export_patient_card_pdf(): void
    {
        [$clinic, $user] = $this->makeClinicWithUser('receptionist');
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);

        Livewire::actingAs($user)
            ->test(PatientsShow::class, ['patient' => $patient])
            ->call('exportPdf')
            ->assertForbidden();
    }
}
