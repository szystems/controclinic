<?php

namespace Tests\Feature;

use App\Livewire\App\Appointments\Show as AppointmentShow;
use App\Livewire\App\Patients\Edit as PatientEdit;
use App\Models\Appointment;
use App\Models\AppointmentComment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InternalNotesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @return array{0: Clinic, 1: User} */
    private function makeContext(string $role = 'doctor'): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id]);
        $user->assignRole($role);
        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        return [$clinic, $user];
    }

    // ─────────────────────────────── Patient Internal Notes ───────────────

    public function test_patient_internal_notes_can_be_saved(): void
    {
        [$clinic, $owner] = $this->makeContext('owner');

        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);

        Livewire::actingAs($owner)
            ->test(PatientEdit::class, ['clinic' => $clinic, 'patient' => $patient])
            ->set('internal_notes', 'Solo para el equipo: alergias graves.')
            ->call('save');

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'internal_notes' => 'Solo para el equipo: alergias graves.',
        ]);
    }

    public function test_patient_internal_notes_can_be_cleared(): void
    {
        [$clinic, $owner] = $this->makeContext('owner');

        $patient = Patient::factory()->create([
            'clinic_id' => $clinic->id,
            'internal_notes' => 'Nota existente',
        ]);

        Livewire::actingAs($owner)
            ->test(PatientEdit::class, ['clinic' => $clinic, 'patient' => $patient])
            ->set('internal_notes', '')
            ->call('save');

        $patient->refresh();
        $this->assertNull($patient->internal_notes);
    }

    public function test_patient_internal_notes_is_loaded_on_edit(): void
    {
        [$clinic, $owner] = $this->makeContext('owner');

        $patient = Patient::factory()->create([
            'clinic_id' => $clinic->id,
            'internal_notes' => 'Nota cargada',
        ]);

        Livewire::actingAs($owner)
            ->test(PatientEdit::class, ['clinic' => $clinic, 'patient' => $patient])
            ->assertSet('internal_notes', 'Nota cargada');
    }

    // ─────────────────────────────── Appointment Comments ─────────────────

    public function test_doctor_can_add_comment_to_appointment(): void
    {
        [$clinic, $doctor] = $this->makeContext('doctor');

        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $appointment = Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);

        Livewire::actingAs($doctor)
            ->test(AppointmentShow::class, ['clinic' => $clinic, 'appointment' => $appointment])
            ->set('newComment', 'Paciente presentó mejoría notable.')
            ->call('addComment');

        $this->assertDatabaseHas('appointment_comments', [
            'appointment_id' => $appointment->id,
            'user_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'body' => 'Paciente presentó mejoría notable.',
        ]);
    }

    public function test_add_comment_validates_empty_body(): void
    {
        [$clinic, $doctor] = $this->makeContext('doctor');

        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $appointment = Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);

        Livewire::actingAs($doctor)
            ->test(AppointmentShow::class, ['clinic' => $clinic, 'appointment' => $appointment])
            ->set('newComment', '')
            ->call('addComment')
            ->assertHasErrors(['newComment']);

        $this->assertDatabaseCount('appointment_comments', 0);
    }

    public function test_author_can_delete_own_comment(): void
    {
        [$clinic, $doctor] = $this->makeContext('doctor');

        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $appointment = Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);

        $comment = AppointmentComment::create([
            'appointment_id' => $appointment->id,
            'user_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'body' => 'Comentario a eliminar',
        ]);

        Livewire::actingAs($doctor)
            ->test(AppointmentShow::class, ['clinic' => $clinic, 'appointment' => $appointment])
            ->call('deleteComment', $comment->id);

        $this->assertDatabaseMissing('appointment_comments', ['id' => $comment->id]);
    }

    public function test_non_author_cannot_delete_others_comment(): void
    {
        [$clinic, $owner] = $this->makeContext('owner');

        $otherDoctor = User::factory()->create(['clinic_id' => $clinic->id]);
        $otherDoctor->assignRole('doctor');

        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $appointment = Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $otherDoctor->id,
        ]);

        $comment = AppointmentComment::create([
            'appointment_id' => $appointment->id,
            'user_id' => $otherDoctor->id,
            'clinic_id' => $clinic->id,
            'body' => 'Comentario del otro doctor',
        ]);

        // A regular doctor (not owner/admin) cannot delete another doctor's comment
        [$clinic2, $anotherDoctor] = $this->makeContext('doctor');
        // reuse same clinic
        $anotherDoctor->clinic_id = $clinic->id;
        $anotherDoctor->save();
        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($anotherDoctor)
            ->test(AppointmentShow::class, ['clinic' => $clinic, 'appointment' => $appointment])
            ->call('deleteComment', $comment->id);

        // Comment should still exist
        $this->assertDatabaseHas('appointment_comments', ['id' => $comment->id]);
    }

    public function test_comment_belongs_to_clinic_scope(): void
    {
        [$clinic, $doctor] = $this->makeContext('doctor');

        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $appointment = Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);

        // Insert a comment that belongs to a DIFFERENT clinic directly via DB
        $otherClinic = Clinic::factory()->onboarded()->create();
        $fakeId = Str::uuid()->toString();
        DB::table('appointment_comments')->insert([
            'id' => $fakeId,
            'appointment_id' => $appointment->id,
            'user_id' => $doctor->id,
            'clinic_id' => $otherClinic->id,
            'body' => 'Otro clinic comment',
            'is_internal' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Our clinic's doctor tries to delete a comment from another clinic → should fail silently (404)
        Livewire::actingAs($doctor)
            ->test(AppointmentShow::class, ['clinic' => $clinic, 'appointment' => $appointment])
            ->call('deleteComment', $fakeId);

        // Comment from other clinic should still exist
        $this->assertDatabaseHas('appointment_comments', ['id' => $fakeId]);
    }
}
