<?php

namespace Tests\Feature;

use App\Jobs\SendAppointmentNotification;
use App\Livewire\App\Appointments\Edit as AppointmentEdit;
use App\Mail\AppointmentBookedToClinic;
use App\Mail\AppointmentBookedToPatient;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentConfirmed;
use App\Mail\AppointmentReminder;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AppointmentNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function makeAppointment(array $appointmentState = [], array $clinicState = [], ?string $patientEmail = 'patient@example.com'): Appointment
    {
        static $counter = 0;
        $counter++;
        $clinic = Clinic::factory()->onboarded()->create(array_merge([
            'email' => 'clinic'.$counter.'@example.com',
            'locale' => 'es',
        ], $clinicState));

        $doctor = User::factory()->create([
            'clinic_id' => $clinic->id,
            'role' => User::ROLE_DOCTOR,
            'is_active' => true,
        ]);

        $patient = Patient::factory()->create([
            'clinic_id' => $clinic->id,
            'primary_doctor_id' => $doctor->id,
            'email' => $patientEmail,
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

    public function test_booking_dispatches_notification_job(): void
    {
        Bus::fake();

        $appointment = $this->makeAppointment();

        SendAppointmentNotification::dispatch(
            $appointment->id,
            SendAppointmentNotification::TYPE_BOOKED,
        );

        Bus::assertDispatched(SendAppointmentNotification::class, function ($job) use ($appointment) {
            return $job->appointmentId === $appointment->id
                && $job->type === SendAppointmentNotification::TYPE_BOOKED;
        });
    }

    public function test_booked_job_sends_email_to_patient_and_clinic(): void
    {
        Mail::fake();

        $appointment = $this->makeAppointment();

        (new SendAppointmentNotification($appointment->id, SendAppointmentNotification::TYPE_BOOKED))
            ->handle();

        Mail::assertSent(AppointmentBookedToPatient::class, function ($mail) use ($appointment) {
            return $mail->hasTo($appointment->patient->email)
                && $mail->appointment->id === $appointment->id;
        });

        Mail::assertSent(AppointmentBookedToClinic::class, function ($mail) use ($appointment) {
            return $mail->hasTo($appointment->clinic->email);
        });
    }

    public function test_booked_job_skips_patient_email_when_missing(): void
    {
        Mail::fake();

        $appointment = $this->makeAppointment([], [], null);

        (new SendAppointmentNotification($appointment->id, SendAppointmentNotification::TYPE_BOOKED))
            ->handle();

        Mail::assertNotSent(AppointmentBookedToPatient::class);
        Mail::assertSent(AppointmentBookedToClinic::class);
    }

    public function test_confirm_dispatches_confirmed_notification(): void
    {
        Bus::fake();

        $appointment = $this->makeAppointment();
        $appointment->confirm();

        Bus::assertDispatched(SendAppointmentNotification::class, function ($job) use ($appointment) {
            return $job->appointmentId === $appointment->id
                && $job->type === SendAppointmentNotification::TYPE_CONFIRMED;
        });
    }

    public function test_cancel_dispatches_cancelled_notification(): void
    {
        Bus::fake();

        $appointment = $this->makeAppointment();
        $appointment->cancel('Patient requested');

        Bus::assertDispatched(SendAppointmentNotification::class, function ($job) use ($appointment) {
            return $job->appointmentId === $appointment->id
                && $job->type === SendAppointmentNotification::TYPE_CANCELLED;
        });
    }

    public function test_confirmed_job_sends_email_to_patient(): void
    {
        Mail::fake();

        $appointment = $this->makeAppointment(['status' => Appointment::STATUS_CONFIRMED]);

        (new SendAppointmentNotification($appointment->id, SendAppointmentNotification::TYPE_CONFIRMED))
            ->handle();

        Mail::assertSent(AppointmentConfirmed::class, function ($mail) use ($appointment) {
            return $mail->hasTo($appointment->patient->email);
        });
    }

    public function test_cancelled_job_sends_email_to_patient(): void
    {
        Mail::fake();

        $appointment = $this->makeAppointment([
            'status' => Appointment::STATUS_CANCELLED,
            'cancellation_reason' => 'Doctor unavailable',
        ]);

        (new SendAppointmentNotification($appointment->id, SendAppointmentNotification::TYPE_CANCELLED))
            ->handle();

        Mail::assertSent(AppointmentCancelled::class);
    }

    public function test_reminder_job_sends_email_and_marks_flag(): void
    {
        Mail::fake();

        $appointment = $this->makeAppointment(['status' => Appointment::STATUS_CONFIRMED]);
        $this->assertFalse($appointment->reminder_sent);

        (new SendAppointmentNotification($appointment->id, SendAppointmentNotification::TYPE_REMINDER))
            ->handle();

        Mail::assertSent(AppointmentReminder::class);

        $appointment->refresh();
        $this->assertTrue($appointment->reminder_sent);
        $this->assertNotNull($appointment->reminder_sent_at);
    }

    public function test_reminder_command_dispatches_for_upcoming_appointments(): void
    {
        Bus::fake();

        // Within next 24h -> should dispatch
        $upcoming = $this->makeAppointment([
            'status' => Appointment::STATUS_CONFIRMED,
            'appointment_date' => now()->addHours(12)->toDateString(),
            'start_time' => now()->addHours(12)->format('H:i'),
        ]);

        // 3 days away -> should NOT dispatch
        $this->makeAppointment([
            'status' => Appointment::STATUS_CONFIRMED,
            'appointment_date' => now()->addDays(3)->toDateString(),
            'start_time' => '10:00',
        ]);

        // Already cancelled -> should NOT dispatch
        $this->makeAppointment([
            'status' => Appointment::STATUS_CANCELLED,
            'appointment_date' => now()->addHours(12)->toDateString(),
            'start_time' => now()->addHours(12)->format('H:i'),
        ]);

        // Reminder already sent -> should NOT dispatch
        $this->makeAppointment([
            'status' => Appointment::STATUS_CONFIRMED,
            'appointment_date' => now()->addHours(12)->toDateString(),
            'start_time' => now()->addHours(12)->format('H:i'),
            'reminder_sent' => true,
            'reminder_sent_at' => now(),
        ]);

        $this->artisan('appointments:send-reminders --hours=24')
            ->assertSuccessful();

        Bus::assertDispatchedTimes(SendAppointmentNotification::class, 1);
        Bus::assertDispatched(SendAppointmentNotification::class, function ($job) use ($upcoming) {
            return $job->appointmentId === $upcoming->id
                && $job->type === SendAppointmentNotification::TYPE_REMINDER;
        });
    }

    public function test_reminder_command_dry_run_does_not_dispatch(): void
    {
        Bus::fake();

        $appointment = $this->makeAppointment([
            'status' => Appointment::STATUS_CONFIRMED,
            'appointment_date' => now()->addHours(6)->toDateString(),
            'start_time' => now()->addHours(6)->format('H:i'),
        ]);

        $this->artisan('appointments:send-reminders --hours=24 --dry-run')
            ->assertSuccessful();

        Bus::assertNotDispatched(SendAppointmentNotification::class);
        $this->assertFalse($appointment->fresh()->reminder_sent);
    }

    public function test_reminder_command_respects_clinic_timezone(): void
    {
        Bus::fake();

        // Clínica en zona horaria distante: si comparamos por hora de servidor (UTC),
        // una cita "mañana 10:00 hora local de Tokyo" caería fuera de las próximas 24h
        // dependiendo del momento en que corra el comando. Validamos que el cálculo
        // se haga en el timezone de la clínica.
        $tz = 'Asia/Tokyo';
        $localNow = now($tz);
        // Cita en 12 horas locales de Tokyo
        $apptLocal = $localNow->copy()->addHours(12);

        $this->makeAppointment(
            [
                'status' => Appointment::STATUS_CONFIRMED,
                'appointment_date' => $apptLocal->toDateString(),
                'start_time' => $apptLocal->format('H:i'),
            ],
            ['timezone' => $tz]
        );

        $this->artisan('appointments:send-reminders --hours=24')
            ->assertSuccessful();

        Bus::assertDispatchedTimes(SendAppointmentNotification::class, 1);
    }

    public function test_notification_job_stops_when_the_patient_was_deleted(): void
    {
        Mail::fake();

        $appointment = $this->makeAppointment([
            'status' => Appointment::STATUS_CONFIRMED,
            'appointment_date' => now()->addHours(12)->toDateString(),
            'start_time' => now()->addHours(12)->format('H:i'),
        ]);
        $appointment->patient->delete();

        (new SendAppointmentNotification($appointment->id, SendAppointmentNotification::TYPE_REMINDER))
            ->handle();

        Mail::assertNothingSent();
    }

    public function test_reminder_command_continues_when_a_clinic_timezone_is_invalid(): void
    {
        Bus::fake();

        $valid = $this->makeAppointment([
            'status' => Appointment::STATUS_CONFIRMED,
            'appointment_date' => now()->addHours(12)->toDateString(),
            'start_time' => now()->addHours(12)->format('H:i'),
        ]);

        $this->makeAppointment([
            'status' => Appointment::STATUS_CONFIRMED,
            'appointment_date' => now()->addHours(12)->toDateString(),
            'start_time' => now()->addHours(12)->format('H:i'),
        ], ['timezone' => 'Not/AZone']);

        $this->artisan('appointments:send-reminders --hours=24')
            ->assertSuccessful();

        Bus::assertDispatchedTimes(SendAppointmentNotification::class, 1);
        Bus::assertDispatched(SendAppointmentNotification::class, function ($job) use ($valid) {
            return $job->appointmentId === $valid->id;
        });
    }

    public function test_reminder_command_skips_a_clinic_that_turned_reminders_off(): void
    {
        Bus::fake();

        $appointment = $this->makeAppointment([
            'status' => Appointment::STATUS_CONFIRMED,
            'appointment_date' => now()->addHours(12)->toDateString(),
            'start_time' => now()->addHours(12)->format('H:i'),
        ], [
            'settings' => ['send_reminders' => false],
        ]);

        $this->artisan('appointments:send-reminders --hours=24')->assertSuccessful();

        Bus::assertNotDispatched(SendAppointmentNotification::class);
        $this->assertFalse($appointment->fresh()->reminder_sent);
    }

    public function test_reminder_command_uses_the_clinic_reminder_window(): void
    {
        Bus::fake();

        $tz = 'America/Mexico_City';
        $inside = now($tz)->addHours(36);
        $outside = now($tz)->addHours(60);

        $due = $this->makeAppointment([
            'status' => Appointment::STATUS_CONFIRMED,
            'appointment_date' => $inside->toDateString(),
            'start_time' => $inside->format('H:i'),
        ], [
            'timezone' => $tz,
            'settings' => ['send_reminders' => true, 'reminder_hours_before' => 48],
        ]);

        $this->makeAppointment([
            'status' => Appointment::STATUS_CONFIRMED,
            'appointment_date' => $outside->toDateString(),
            'start_time' => $outside->format('H:i'),
        ], [
            'timezone' => $tz,
            'settings' => ['send_reminders' => true, 'reminder_hours_before' => 48],
        ]);

        $this->artisan('appointments:send-reminders --hours=24')->assertSuccessful();

        Bus::assertDispatchedTimes(SendAppointmentNotification::class, 1);
        Bus::assertDispatched(SendAppointmentNotification::class, function ($job) use ($due) {
            return $job->appointmentId === $due->id;
        });
    }

    public function test_reminder_command_marks_an_appointment_without_email_and_does_not_dispatch(): void
    {
        Bus::fake();

        $appointment = $this->makeAppointment([
            'status' => Appointment::STATUS_CONFIRMED,
            'appointment_date' => now()->addHours(6)->toDateString(),
            'start_time' => now()->addHours(6)->format('H:i'),
        ], [], null);

        $this->artisan('appointments:send-reminders --hours=24')->assertSuccessful();

        Bus::assertNotDispatched(SendAppointmentNotification::class);
        $this->assertTrue($appointment->fresh()->reminder_sent);
    }

    public function test_reminder_command_claims_the_appointment_so_a_second_run_does_not_dispatch(): void
    {
        Bus::fake();

        $this->makeAppointment([
            'status' => Appointment::STATUS_CONFIRMED,
            'appointment_date' => now()->addHours(6)->toDateString(),
            'start_time' => now()->addHours(6)->format('H:i'),
        ]);

        $this->artisan('appointments:send-reminders --hours=24')->assertSuccessful();
        $this->artisan('appointments:send-reminders --hours=24')->assertSuccessful();

        Bus::assertDispatchedTimes(SendAppointmentNotification::class, 1);
    }

    public function test_reminder_job_does_not_email_a_cancelled_or_completed_appointment(): void
    {
        Mail::fake();

        $cancelled = $this->makeAppointment(['status' => Appointment::STATUS_CANCELLED]);
        $completed = $this->makeAppointment(['status' => Appointment::STATUS_COMPLETED]);

        (new SendAppointmentNotification($cancelled->id, SendAppointmentNotification::TYPE_REMINDER))->handle();
        (new SendAppointmentNotification($completed->id, SendAppointmentNotification::TYPE_REMINDER))->handle();

        Mail::assertNotSent(AppointmentReminder::class);
    }

    public function test_confirmation_emails_respect_the_clinic_setting(): void
    {
        Mail::fake();

        $appointment = $this->makeAppointment(['status' => Appointment::STATUS_CONFIRMED], [
            'settings' => ['send_confirmations' => false],
        ]);

        (new SendAppointmentNotification($appointment->id, SendAppointmentNotification::TYPE_CONFIRMED))->handle();
        (new SendAppointmentNotification($appointment->id, SendAppointmentNotification::TYPE_BOOKED))->handle();

        Mail::assertNotSent(AppointmentConfirmed::class);
        Mail::assertNotSent(AppointmentBookedToPatient::class);
        Mail::assertSent(AppointmentBookedToClinic::class);
    }

    public function test_editing_the_appointment_time_clears_the_reminder_flag(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $appointment = $this->makeAppointment([
            'status' => Appointment::STATUS_SCHEDULED,
            'appointment_date' => now()->addDays(3)->toDateString(),
            'start_time' => '10:00',
            'reminder_sent' => true,
        ]);

        $owner = User::factory()->owner()->create([
            'clinic_id' => $appointment->clinic_id,
        ]);

        app()->instance('current_clinic', $appointment->clinic);
        view()->share('currentClinic', $appointment->clinic);

        Livewire::actingAs($owner)
            ->test(AppointmentEdit::class, [
                'clinic' => $appointment->clinic,
                'appointment' => $appointment,
            ])
            ->set('start_time', '15:00')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertFalse((bool) $appointment->fresh()->reminder_sent);
    }
}
