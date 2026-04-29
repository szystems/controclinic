<?php

namespace Tests\Feature;

use App\Livewire\App\Appointments\Calendar;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AppointmentsCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @return array{0: Clinic, 1: User} */
    private function makeContext(string $role = 'doctor', array $clinicState = []): array
    {
        $clinic = Clinic::factory()->onboarded()->create($clinicState);
        $user = User::factory()->create(['clinic_id' => $clinic->id]);
        $user->assignRole($role);

        return [$clinic, $user];
    }

    private function bindClinic(Clinic $clinic): void
    {
        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);
    }

    public function test_calendar_route_renders_for_authorized_user(): void
    {
        [$clinic, $user] = $this->makeContext();

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/appointments/calendar")
            ->assertOk()
            ->assertSeeLivewire(Calendar::class);
    }

    public function test_fetch_events_returns_appointments_in_range_for_clinic(): void
    {
        [$clinic, $user] = $this->makeContext();
        $this->bindClinic($clinic);

        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);

        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $user->id,
            'appointment_date' => '2026-05-10',
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
        ]);

        // Out of range — should not appear
        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $user->id,
            'appointment_date' => '2026-06-15',
            'start_time' => '10:00:00',
        ]);

        $component = Livewire::actingAs($user)->test(Calendar::class, ['clinic' => $clinic]);
        $events = $component->instance()->fetchEvents('2026-05-01', '2026-05-31');

        $this->assertCount(1, $events);
        $this->assertSame('2026-05-10T10:00:00', $events[0]['start']);
        $this->assertStringContainsString('appointments/', $events[0]['url']);
    }

    public function test_fetch_events_does_not_leak_other_clinics(): void
    {
        [$clinicA, $userA] = $this->makeContext();
        [$clinicB, $userB] = $this->makeContext();
        $this->bindClinic($clinicA);

        $patientB = Patient::factory()->create(['clinic_id' => $clinicB->id]);
        Appointment::factory()->create([
            'clinic_id' => $clinicB->id,
            'patient_id' => $patientB->id,
            'doctor_id' => $userB->id,
            'appointment_date' => '2026-05-10',
            'start_time' => '10:00:00',
        ]);

        $component = Livewire::actingAs($userA)->test(Calendar::class, ['clinic' => $clinicA]);
        $events = $component->instance()->fetchEvents('2026-05-01', '2026-05-31');

        $this->assertCount(0, $events);
    }

    public function test_fetch_events_filters_by_selected_doctors(): void
    {
        [$clinic, $userA] = $this->makeContext();
        $userB = User::factory()->create(['clinic_id' => $clinic->id]);
        $userB->assignRole('doctor');
        $this->bindClinic($clinic);

        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);

        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $userA->id,
            'appointment_date' => '2026-05-10',
            'start_time' => '10:00:00',
        ]);
        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $userB->id,
            'appointment_date' => '2026-05-11',
            'start_time' => '11:00:00',
        ]);

        $component = Livewire::actingAs($userA)
            ->test(Calendar::class, ['clinic' => $clinic])
            ->set('selectedDoctors', [(string) $userB->id]);

        $events = $component->instance()->fetchEvents('2026-05-01', '2026-05-31');

        $this->assertCount(1, $events);
        $this->assertSame('2026-05-11T11:00:00', $events[0]['start']);
    }

    public function test_reschedule_event_updates_appointment_when_authorized(): void
    {
        [$clinic, $user] = $this->makeContext();
        $this->bindClinic($clinic);

        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $appointment = Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $user->id,
            'appointment_date' => '2026-05-10',
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
        ]);

        $component = Livewire::actingAs($user)->test(Calendar::class, ['clinic' => $clinic]);
        $result = $component->instance()->rescheduleEvent(
            (string) $appointment->id,
            '2026-05-12T14:00:00',
            '2026-05-12T14:30:00'
        );

        $this->assertTrue($result['success']);
        $appointment->refresh();
        $this->assertSame('2026-05-12', $appointment->appointment_date->toDateString());
        $this->assertSame('14:00', $appointment->start_time->format('H:i'));
    }

    public function test_reschedule_event_blocked_when_clinic_is_read_only(): void
    {
        [$clinic, $user] = $this->makeContext('doctor', [
            'status' => 'cancelled',
            'is_manual_plan' => false,
        ]);
        $this->bindClinic($clinic);

        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $appointment = Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $user->id,
            'appointment_date' => '2026-05-10',
            'start_time' => '09:00:00',
        ]);

        $component = Livewire::actingAs($user)->test(Calendar::class, ['clinic' => $clinic]);
        $result = $component->instance()->rescheduleEvent(
            (string) $appointment->id,
            '2026-05-12T14:00:00'
        );

        $this->assertFalse($result['success']);
        $appointment->refresh();
        $this->assertSame('2026-05-10', $appointment->appointment_date->toDateString());
    }

    public function test_reschedule_event_blocked_when_user_lacks_permission(): void
    {
        [$clinic, $user] = $this->makeContext('receptionist');
        // receptionist has appointments.view but check whether edit is granted; if granted skip — use bare user
        $bare = User::factory()->create(['clinic_id' => $clinic->id]);
        $this->bindClinic($clinic);

        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $appointment = Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $user->id,
            'appointment_date' => '2026-05-10',
            'start_time' => '09:00:00',
        ]);

        $component = Livewire::actingAs($bare)->test(Calendar::class, ['clinic' => $clinic]);
        $result = $component->instance()->rescheduleEvent(
            (string) $appointment->id,
            '2026-05-12T14:00:00'
        );

        $this->assertFalse($result['success']);
    }

    public function test_reschedule_event_cannot_target_appointment_from_other_clinic(): void
    {
        [$clinicA, $userA] = $this->makeContext();
        [$clinicB, $userB] = $this->makeContext();
        $this->bindClinic($clinicA);

        $patientB = Patient::factory()->create(['clinic_id' => $clinicB->id]);
        $foreignAppt = Appointment::factory()->create([
            'clinic_id' => $clinicB->id,
            'patient_id' => $patientB->id,
            'doctor_id' => $userB->id,
            'appointment_date' => '2026-05-10',
            'start_time' => '09:00:00',
        ]);

        $component = Livewire::actingAs($userA)->test(Calendar::class, ['clinic' => $clinicA]);
        $result = $component->instance()->rescheduleEvent(
            (string) $foreignAppt->id,
            '2026-05-12T14:00:00'
        );

        $this->assertFalse($result['success']);
        $foreignAppt->refresh();
        $this->assertSame('2026-05-10', $foreignAppt->appointment_date->toDateString());
    }

    public function test_toggle_doctor_filter_dispatches_refresh(): void
    {
        [$clinic, $user] = $this->makeContext();
        $this->bindClinic($clinic);

        Livewire::actingAs($user)
            ->test(Calendar::class, ['clinic' => $clinic])
            ->call('toggleDoctor', $user->id)
            ->assertSet('selectedDoctors', [(string) $user->id])
            ->assertDispatched('calendar-refresh')
            ->call('clearDoctorFilter')
            ->assertSet('selectedDoctors', [])
            ->assertDispatched('calendar-refresh');
    }
}
