<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentBookedToClinic extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('appointments_mail.booked_clinic_subject', [
                'patient' => $this->appointment->patient->full_name,
            ]),
        );
    }

    public function content(): Content
    {
        $appointment = $this->appointment->loadMissing(['clinic', 'patient', 'doctor']);
        $appUrl = route('app.appointments.show', [
            'clinic' => $appointment->clinic->slug,
            'appointment' => $appointment->id,
        ], false);

        return new Content(
            markdown: 'mail.appointments.booked-clinic',
            with: [
                'appointment' => $appointment,
                'clinic' => $appointment->clinic,
                'patient' => $appointment->patient,
                'doctor' => $appointment->doctor,
                'reference' => strtoupper(substr($appointment->id, 0, 8)),
                'requiresConfirmation' => $appointment->status === Appointment::STATUS_SCHEDULED,
                'manageUrl' => url($appUrl),
            ],
        );
    }
}
