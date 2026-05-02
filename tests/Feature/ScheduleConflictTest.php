<?php

namespace Tests\Feature;

use App\Livewire\App\Appointments\Create;
use App\Livewire\App\Appointments\Edit;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ScheduleConflictTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @return array{0: Clinic, 1: User, 2: Patient} */
    private function makeContext(): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id]);
        $user->assignRole('doctor');
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        return [$clinic, $user, $patient];
    }

    public function test_create_appointment_fails_when_slot_is_occupied(): void
    {
        [$clinic, $user, $patient] = $this->makeContext();

        // Existing appointment 09:00–09:30
        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $user->id,
            'appointment_date' => '2026-06-10',
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'confirmed',
        ]);

        Livewire::actingAs($user)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('doctor_id', $user->id)
            ->set('patient_id', $patient->id)
            ->set('appointment_date', '2026-06-10')
            ->set('start_time', '09:15')
            ->set('duration_minutes', 30)
            ->set('appointment_type', 'follow_up')
            ->call('save');

        // Should not have created a second appointment (conflict prevented it)
        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_create_appointment_succeeds_when_different_doctor_same_slot(): void
    {
        [$clinic, $userA, $patient] = $this->makeContext();

        $userB = User::factory()->create(['clinic_id' => $clinic->id]);
        $userB->assignRole('doctor');

        // UserA has 09:00–09:30
        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $userA->id,
            'appointment_date' => '2026-06-10',
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'confirmed',
        ]);

        // UserB books same slot — should succeed
        Livewire::actingAs($userB)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('doctor_id', $userB->id)
            ->set('patient_id', $patient->id)
            ->set('appointment_date', '2026-06-10')
            ->set('start_time', '09:00')
            ->set('duration_minutes', 30)
            ->set('appointment_type', 'follow_up')
            ->call('save')
            ->assertSessionMissing('error');
    }

    public function test_edit_appointment_fails_when_moved_to_occupied_slot(): void
    {
        [$clinic, $user, $patient] = $this->makeContext();

        // Blocking appointment at 10:00–10:30
        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $user->id,
            'appointment_date' => '2026-06-10',
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => 'confirmed',
        ]);

        // Appointment to edit — currently at 09:00
        $toEdit = Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $user->id,
            'appointment_date' => '2026-06-10',
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'confirmed',
        ]);

        Livewire::actingAs($user)
            ->test(Edit::class, ['clinic' => $clinic, 'appointment' => $toEdit])
            ->set('start_time', '10:15')
            ->call('save');

        // Appointment should NOT have been moved to the conflicting slot
        $toEdit->refresh();
        $this->assertSame('09:00', $toEdit->start_time->format('H:i'));
    }

    public function test_edit_appointment_excludes_self_from_conflict_check(): void
    {
        [$clinic, $user, $patient] = $this->makeContext();

        $appointment = Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $user->id,
            'appointment_date' => '2026-06-10',
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'confirmed',
        ]);

        // Saving without changing the time should not flag a conflict with itself
        Livewire::actingAs($user)
            ->test(Edit::class, ['clinic' => $clinic, 'appointment' => $appointment])
            ->set('notes', 'updated note')
            ->call('save');

        // The appointment should still exist at its original time (not blocked by self-conflict)
        $appointment->refresh();
        $this->assertSame('updated note', $appointment->notes);
    }
}
