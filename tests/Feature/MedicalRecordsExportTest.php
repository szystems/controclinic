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

    public function test_doctor_can_export_prescription_pdf(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithUser('doctor');
        [$patient, $record] = $this->makeRecord($clinic, [
            'doctor_id' => $doctor->id,
            'prescriptions' => [
                ['drug' => 'Omeprazol 20mg', 'dosage' => '1 cap c/12h', 'duration' => '14 días', 'notes' => 'Antes del desayuno'],
                ['drug' => 'Paracetamol 500mg', 'dosage' => '1 tab c/8h', 'duration' => '5 días', 'notes' => null],
            ],
        ]);

        Livewire::actingAs($doctor)
            ->test(RecordShow::class, ['patient' => $patient, 'record' => $record])
            ->call('exportPrescriptionPdf')
            ->assertFileDownloaded(null, null, 'application/pdf');
    }

    public function test_export_prescription_pdf_aborts_when_no_prescriptions(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithUser('doctor');
        [$patient, $record] = $this->makeRecord($clinic, [
            'doctor_id' => $doctor->id,
            'prescriptions' => [],
        ]);

        Livewire::actingAs($doctor)
            ->test(RecordShow::class, ['patient' => $patient, 'record' => $record])
            ->call('exportPrescriptionPdf')
            ->assertStatus(404);
    }

    public function test_doctor_without_print_permission_cannot_export_prescription_pdf(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithUser('doctor');
        $doctor->roles->first()->revokePermissionTo('records.print');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        [$patient, $record] = $this->makeRecord($clinic, [
            'doctor_id' => $doctor->id,
            'prescriptions' => [['drug' => 'Test', 'dosage' => '1x', 'duration' => '1d', 'notes' => null]],
        ]);

        Livewire::actingAs($doctor->fresh())
            ->test(RecordShow::class, ['patient' => $patient, 'record' => $record])
            ->call('exportPrescriptionPdf')
            ->assertForbidden();
    }

    public function test_prescription_pdf_filename_includes_type_patient_and_date(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithUser('doctor');
        [$patient, $record] = $this->makeRecord($clinic, [
            'doctor_id' => $doctor->id,
            'record_type' => MedicalRecord::TYPE_PRESCRIPTION,
            'prescriptions' => [
                ['drug' => 'Omeprazol 20mg', 'dosage' => '1 cap c/12h', 'duration' => '14 días', 'notes' => null],
            ],
        ]);
        $patient->forceFill(['first_name' => 'Ariadna', 'last_name' => 'Brito'])->save();
        $record->forceFill(['created_at' => '2026-04-30 10:00:00'])->save();

        $this->actingAs($doctor);
        $component = new RecordShow;
        $component->mount($patient->fresh(), $record->fresh());
        $response = $component->exportPrescriptionPdf();

        $disposition = $response->headers->get('content-disposition');
        $this->assertNotNull($disposition);
        $this->assertStringContainsString('receta', $disposition);
        $this->assertStringContainsString('ariadna-brito', $disposition);
        $this->assertStringContainsString('30-04-2026', $disposition);
    }

    public function test_record_pdf_filename_uses_type_label_for_consultation(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithUser('doctor');
        [$patient, $record] = $this->makeRecord($clinic, [
            'doctor_id' => $doctor->id,
            'record_type' => MedicalRecord::TYPE_CONSULTATION,
        ]);
        $patient->forceFill(['first_name' => 'Ariadna', 'last_name' => 'Brito'])->save();
        $record->forceFill(['created_at' => '2026-04-30 10:00:00'])->save();

        $this->actingAs($doctor);
        $component = new RecordShow;
        $component->mount($patient->fresh(), $record->fresh());
        $response = $component->exportPdf();

        $disposition = $response->headers->get('content-disposition');
        $this->assertNotNull($disposition);
        $this->assertStringContainsString('ariadna-brito', $disposition);
        $this->assertStringContainsString('30-04-2026', $disposition);
    }
}
