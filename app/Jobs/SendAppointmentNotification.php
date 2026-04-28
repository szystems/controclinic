<?php

namespace App\Jobs;

use App\Mail\AppointmentBookedToClinic;
use App\Mail\AppointmentBookedToPatient;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentConfirmed;
use App\Mail\AppointmentReminder;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAppointmentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const TYPE_BOOKED = 'booked';

    public const TYPE_CONFIRMED = 'confirmed';

    public const TYPE_CANCELLED = 'cancelled';

    public const TYPE_REMINDER = 'reminder';

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public string $appointmentId,
        public string $type,
    ) {}

    public function handle(): void
    {
        $appointment = Appointment::with(['clinic', 'patient', 'doctor'])
            ->find($this->appointmentId);

        if (! $appointment) {
            return;
        }

        $clinic = $appointment->clinic;
        $patient = $appointment->patient;
        $locale = $clinic->locale ?: config('app.locale');

        // Switch locale for translations & date formatting
        $previousLocale = App::getLocale();
        App::setLocale($locale);

        try {
            switch ($this->type) {
                case self::TYPE_BOOKED:
                    if ($patient->email) {
                        Mail::to($patient->email)
                            ->locale($locale)
                            ->send(new AppointmentBookedToPatient($appointment));
                    }
                    if ($clinic->email) {
                        Mail::to($clinic->email)
                            ->locale($locale)
                            ->send(new AppointmentBookedToClinic($appointment));
                    }
                    break;

                case self::TYPE_CONFIRMED:
                    if ($patient->email) {
                        Mail::to($patient->email)
                            ->locale($locale)
                            ->send(new AppointmentConfirmed($appointment));
                    }
                    break;

                case self::TYPE_CANCELLED:
                    if ($patient->email) {
                        Mail::to($patient->email)
                            ->locale($locale)
                            ->send(new AppointmentCancelled($appointment));
                    }
                    break;

                case self::TYPE_REMINDER:
                    if ($patient->email) {
                        Mail::to($patient->email)
                            ->locale($locale)
                            ->send(new AppointmentReminder($appointment));

                        $appointment->forceFill([
                            'reminder_sent' => true,
                            'reminder_sent_at' => now(),
                        ])->save();
                    }
                    break;

                default:
                    Log::warning('Unknown appointment notification type', [
                        'type' => $this->type,
                        'appointment_id' => $this->appointmentId,
                    ]);
            }
        } finally {
            App::setLocale($previousLocale);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Appointment notification failed', [
            'appointment_id' => $this->appointmentId,
            'type' => $this->type,
            'error' => $exception->getMessage(),
        ]);
    }
}
