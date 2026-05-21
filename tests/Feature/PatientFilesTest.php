<?php

namespace Tests\Feature;

use App\Livewire\App\Patients\Files as PatientFiles;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PatientFilesTest extends TestCase
{
    use RefreshDatabase;

    private function createClinicWithOwner(): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->owner()->create(['clinic_id' => $clinic->id]);

        return [$clinic, $owner];
    }

    private function createPatient(Clinic $clinic): Patient
    {
        return Patient::factory()->create(['clinic_id' => $clinic->id]);
    }

    private function createFile(Clinic $clinic, Patient $patient, User $user): PatientFile
    {
        return PatientFile::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'uploaded_by_user_id' => $user->id,
            'category' => 'lab',
            'name' => 'Hemograma',
            'original_filename' => 'hemograma.pdf',
            'disk_path' => "clinics/{$clinic->id}/patients/{$patient->id}/files/test.pdf",
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'size_bytes' => 12345,
        ]);
    }

    // ─── Visibility ───────────────────────────────────────────────────────────

    public function test_owner_can_see_files_component(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        $patient = $this->createPatient($clinic);

        Livewire::actingAs($owner)
            ->test(PatientFiles::class, ['clinic' => $clinic, 'patient' => $patient])
            ->assertStatus(200);
    }

    public function test_doctor_with_files_view_can_see_files(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        $doctor = User::factory()->create(['clinic_id' => $clinic->id])->assignRole('doctor');
        $patient = $this->createPatient($clinic);

        Livewire::actingAs($doctor)
            ->test(PatientFiles::class, ['clinic' => $clinic, 'patient' => $patient])
            ->assertStatus(200);
    }

    public function test_receptionist_cannot_see_files(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        $receptionist = User::factory()->create(['clinic_id' => $clinic->id])->assignRole('receptionist');
        $patient = $this->createPatient($clinic);

        Livewire::actingAs($receptionist)
            ->test(PatientFiles::class, ['clinic' => $clinic, 'patient' => $patient])
            ->assertForbidden();
    }

    public function test_secretary_cannot_see_files(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        $secretary = User::factory()->create(['clinic_id' => $clinic->id])->assignRole('secretary');
        $patient = $this->createPatient($clinic);

        Livewire::actingAs($secretary)
            ->test(PatientFiles::class, ['clinic' => $clinic, 'patient' => $patient])
            ->assertForbidden();
    }

    // ─── Cross-tenant isolation ───────────────────────────────────────────────

    public function test_cannot_view_patient_from_other_clinic(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        [$otherClinic] = $this->createClinicWithOwner();
        $patientFromOther = $this->createPatient($otherClinic);

        Livewire::actingAs($owner)
            ->test(PatientFiles::class, ['clinic' => $clinic, 'patient' => $patientFromOther])
            ->assertNotFound();
    }

    // ─── Upload ───────────────────────────────────────────────────────────────

    public function test_doctor_can_upload_file(): void
    {
        Storage::fake('local');

        [$clinic, $owner] = $this->createClinicWithOwner();
        $doctor = User::factory()->create(['clinic_id' => $clinic->id])->assignRole('doctor');
        $patient = $this->createPatient($clinic);

        $fakeFile = UploadedFile::fake()->create('resultado.pdf', 100, 'application/pdf');

        Livewire::actingAs($doctor)
            ->test(PatientFiles::class, ['clinic' => $clinic, 'patient' => $patient])
            ->set('uploads', [$fakeFile])
            ->set('uploadCategory', 'lab')
            ->set('uploadName', 'Hemograma mayo 2026')
            ->call('uploadFiles')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('patient_files', [
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'category' => 'lab',
            'name' => 'Hemograma mayo 2026',
        ]);
    }

    public function test_assistant_can_upload_file(): void
    {
        Storage::fake('local');

        [$clinic, $owner] = $this->createClinicWithOwner();
        $assistant = User::factory()->create(['clinic_id' => $clinic->id])->assignRole('assistant');
        $patient = $this->createPatient($clinic);

        $fakeFile = UploadedFile::fake()->create('informe.pdf', 50, 'application/pdf');

        Livewire::actingAs($assistant)
            ->test(PatientFiles::class, ['clinic' => $clinic, 'patient' => $patient])
            ->set('uploads', [$fakeFile])
            ->set('uploadCategory', 'report')
            ->call('uploadFiles')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('patient_files', [
            'clinic_id' => $clinic->id,
            'category' => 'report',
        ]);
    }

    public function test_receptionist_cannot_upload_file(): void
    {
        Storage::fake('local');

        [$clinic, $owner] = $this->createClinicWithOwner();
        $receptionist = User::factory()->create(['clinic_id' => $clinic->id])->assignRole('receptionist');
        $patient = $this->createPatient($clinic);

        Livewire::actingAs($receptionist)
            ->test(PatientFiles::class, ['clinic' => $clinic, 'patient' => $patient])
            ->assertForbidden();
    }

    public function test_upload_validates_allowed_mime_types(): void
    {
        Storage::fake('local');

        [$clinic, $owner] = $this->createClinicWithOwner();
        $patient = $this->createPatient($clinic);

        // .exe should be rejected
        $badFile = UploadedFile::fake()->create('malware.exe', 10, 'application/x-msdownload');

        Livewire::actingAs($owner)
            ->test(PatientFiles::class, ['clinic' => $clinic, 'patient' => $patient])
            ->set('uploads', [$badFile])
            ->set('uploadCategory', 'other')
            ->call('uploadFiles')
            ->assertHasErrors(['uploads.*']);
    }

    // ─── Delete ───────────────────────────────────────────────────────────────

    public function test_doctor_can_delete_file(): void
    {
        Storage::fake('local');

        [$clinic, $owner] = $this->createClinicWithOwner();
        $doctor = User::factory()->create(['clinic_id' => $clinic->id])->assignRole('doctor');
        $patient = $this->createPatient($clinic);

        // Create physical fake file
        Storage::disk('local')->put(
            "clinics/{$clinic->id}/patients/{$patient->id}/files/test.pdf",
            'fake content'
        );

        $file = $this->createFile($clinic, $patient, $doctor);

        Livewire::actingAs($doctor)
            ->test(PatientFiles::class, ['clinic' => $clinic, 'patient' => $patient])
            ->call('confirmDelete', $file->id)
            ->call('deleteFile')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('patient_files', ['id' => $file->id]);
    }

    public function test_admin_without_delete_permission_cannot_delete(): void
    {
        Storage::fake('local');

        [$clinic, $owner] = $this->createClinicWithOwner();
        $admin = User::factory()->create(['clinic_id' => $clinic->id])->assignRole('admin');
        $patient = $this->createPatient($clinic);
        $file = $this->createFile($clinic, $patient, $owner);

        Livewire::actingAs($admin)
            ->test(PatientFiles::class, ['clinic' => $clinic, 'patient' => $patient])
            ->call('confirmDelete', $file->id)
            ->call('deleteFile')
            ->assertForbidden();
    }

    // ─── Streaming (controller) ───────────────────────────────────────────────

    public function test_stream_blocked_for_unauthenticated_user(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        $patient = $this->createPatient($clinic);
        $file = $this->createFile($clinic, $patient, $owner);

        $this->get(route('app.patient-files.show', [
            'clinic' => $clinic->slug,
            'file' => $file->id,
        ]))->assertRedirect(route('login'));
    }

    public function test_stream_blocked_for_user_without_files_view(): void
    {
        Storage::fake('local');

        [$clinic, $owner] = $this->createClinicWithOwner();
        $receptionist = User::factory()->create(['clinic_id' => $clinic->id])->assignRole('receptionist');
        $patient = $this->createPatient($clinic);
        $file = $this->createFile($clinic, $patient, $owner);

        $this->actingAs($receptionist)
            ->get(route('app.patient-files.show', [
                'clinic' => $clinic->slug,
                'file' => $file->id,
            ]))->assertForbidden();
    }

    public function test_stream_blocked_for_file_from_other_clinic(): void
    {
        Storage::fake('local');

        [$clinic, $owner] = $this->createClinicWithOwner();
        [$otherClinic, $otherOwner] = $this->createClinicWithOwner();
        $patient = $this->createPatient($otherClinic);
        $file = $this->createFile($otherClinic, $patient, $otherOwner);

        // Auth as owner of $clinic trying to access file from $otherClinic
        $this->actingAs($owner)
            ->get(route('app.patient-files.show', [
                'clinic' => $clinic->slug,
                'file' => $file->id,
            ]))->assertNotFound();
    }

    // ─── Category filter ──────────────────────────────────────────────────────

    public function test_category_filter_shows_only_matching_files(): void
    {
        Storage::fake('local');

        [$clinic, $owner] = $this->createClinicWithOwner();
        $patient = $this->createPatient($clinic);
        $labFile = $this->createFile($clinic, $patient, $owner);

        $reportFile = PatientFile::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'uploaded_by_user_id' => $owner->id,
            'category' => 'report',
            'name' => 'Informe consulta',
            'original_filename' => 'informe.pdf',
            'disk_path' => "clinics/{$clinic->id}/patients/{$patient->id}/files/report.pdf",
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'size_bytes' => 500,
        ]);

        $component = Livewire::actingAs($owner)
            ->test(PatientFiles::class, ['clinic' => $clinic, 'patient' => $patient])
            ->set('filterCategory', 'lab');

        // After filtering, only lab file should be in the rendered view
        $component->assertSee('Hemograma')
            ->assertDontSee('Informe consulta');
    }
}
