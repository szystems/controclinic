<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentConfirmationTest extends TestCase
{
    use RefreshDatabase;

    private function makeAppointment(array $appointmentState = []): Appointment
    {
        static $counter = 0;
        $counter++;

        $clinic = Clinic::factory()->onboarded()->create([
            'email' => 'clinic'.$counter.'@example.com',
        ]);

        $doctor = User::factory()->create([
            'clinic_id' => $clinic->id,
            'role' => User::ROLE_DOCTOR,
            'is_active' => true,
        ]);

        $patient = Patient::factory()->create([
            'clinic_id' => $clinic->id,
            'primary_doctor_id' => $doctor->id,
        ]);

        return Appointment::create(array_merge([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_type' => Appointment::TYPE_SCHEDULED,
            'appointment_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '10:30',
            'duration_minutes' => 30,
            'status' => Appointment::STATUS_SCHEDULED,
            'reminder_sent' => false,
        ], $appointmentState));
    }

    public function test_token_is_generated_on_appointment_creation(): void
    {
        $appointment = $this->makeAppointment();

        $this->assertNotNull($appointment->confirmation_token);
        $this->assertEquals(64, strlen($appointment->confirmation_token));
    }

    public function test_each_appointment_gets_unique_token(): void
    {
        $a1 = $this->makeAppointment();
        $a2 = $this->makeAppointment();

        $this->assertNotEquals($a1->confirmation_token, $a2->confirmation_token);
    }

    public function test_patient_can_confirm_appointment_via_link(): void
    {
        $appointment = $this->makeAppointment();

        $response = $this->get(route('appointment.confirm', $appointment->confirmation_token));

        $response->assertStatus(200);
        $response->assertViewIs('appointment.confirmed');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::STATUS_CONFIRMED,
            'confirmed_via' => 'link',
        ]);
    }

    public function test_patient_can_cancel_appointment_via_link(): void
    {
        $appointment = $this->makeAppointment();

        $response = $this->get(route('appointment.cancel', $appointment->confirmation_token));

        $response->assertStatus(200);
        $response->assertViewIs('appointment.cancelled');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::STATUS_CANCELLED,
        ]);
        $this->assertNotNull($appointment->fresh()->cancelled_at);
    }

    public function test_invalid_token_returns_invalid_token_view(): void
    {
        $response = $this->get(route('appointment.confirm', 'invalid-token-that-does-not-exist'));

        $response->assertStatus(200);
        $response->assertViewIs('appointment.invalid-token');
    }

    public function test_cancel_invalid_token_returns_invalid_token_view(): void
    {
        $response = $this->get(route('appointment.cancel', 'invalid-token-that-does-not-exist'));

        $response->assertStatus(200);
        $response->assertViewIs('appointment.invalid-token');
    }

    public function test_confirming_already_confirmed_appointment_is_idempotent(): void
    {
        $appointment = $this->makeAppointment(['status' => Appointment::STATUS_CONFIRMED, 'confirmed_via' => 'link']);

        $response = $this->get(route('appointment.confirm', $appointment->confirmation_token));

        $response->assertStatus(200);
        $response->assertViewIs('appointment.confirmed');

        // Status should not change
        $this->assertEquals(Appointment::STATUS_CONFIRMED, $appointment->fresh()->status);
    }

    public function test_cancelling_already_cancelled_appointment_shows_already_cancelled_view(): void
    {
        $appointment = $this->makeAppointment([
            'status' => Appointment::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        $response = $this->get(route('appointment.cancel', $appointment->confirmation_token));

        $response->assertStatus(200);
        $response->assertViewIs('appointment.already-cancelled');
    }

    public function test_confirming_cancelled_appointment_shows_already_cancelled_view(): void
    {
        $appointment = $this->makeAppointment([
            'status' => Appointment::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        $response = $this->get(route('appointment.confirm', $appointment->confirmation_token));

        $response->assertStatus(200);
        $response->assertViewIs('appointment.already-cancelled');
    }
}
