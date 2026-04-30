<?php

namespace Tests\Feature;

use App\Livewire\App\MedicalRecords\Show as RecordShow;
use App\Models\Clinic;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MedicalRecordsExportTest extends TestCase
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

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        return [$clinic, $user];
    }

    private function makeRecord(Clinic $clinic, array $attrs = []): array
    {
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $doctor = User::factory()->create(['clinic_id' => $clinic->id]);
        $doctor->assignRole('doctor');

        $record = MedicalRecord::factory()->create(array_merge([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ], $attrs));

        return [$patient, $record];
    }

    public function test_doctor_can_export_record_pdf(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithUser('doctor');
        [$patient, $record] = $this->makeRecord($clinic, ['doctor_id' => $doctor->id]);

        Livewire::actingAs($doctor)
            ->test(RecordShow::class, ['patient' => $patient, 'record' => $record])
            ->call('exportPdf')
            ->assertFileDownloaded(null, null, 'application/pdf');
    }

    public function test_owner_can_export_record_pdf(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        [$patient, $record] = $this->makeRecord($clinic);

        Livewire::actingAs($owner)
            ->test(RecordShow::class, ['patient' => $patient, 'record' => $record])
            ->call('exportPdf')
            ->assertFileDownloaded(null, null, 'application/pdf');
    }

    public function test_doctor_without_print_permission_cannot_export_record_pdf(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithUser('doctor');
        // Revoke print to verify guard inside exportPdf()
        $doctor->roles->first()->revokePermissionTo('records.print');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        [$patient, $record] = $this->makeRecord($clinic, ['doctor_id' => $doctor->id]);

        Livewire::actingAs($doctor->fresh())
            ->test(RecordShow::class, ['patient' => $patient, 'record' => $record])
            ->call('exportPdf')
            ->assertForbidden();
    }

    public function test_confidential_record_blocks_view_without_view_confidential(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithUser('doctor');
        $doctor->roles->first()->revokePermissionTo('records.view_confidential');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        [$patient, $record] = $this->makeRecord($clinic, [
            'doctor_id' => $doctor->id,
            'is_confidential' => true,
        ]);

        // mount() aborts before exportPdf can be called — verify via HTTP
        $this->actingAs($doctor->fresh())
            ->get("/app/{$clinic->slug}/patients/{$patient->id}/records/{$record->id}")
            ->assertForbidden();
    }
}
