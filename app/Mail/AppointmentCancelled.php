<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentCancelled extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('appointments_mail.cancelled_subject', [
                'clinic' => $this->appointment->clinic->name,
            ]),
        );
    }

    public function content(): Content
    {
        $appointment = $this->appointment->loadMissing(['clinic', 'patient', 'doctor']);

        return new Content(
            markdown: 'mail.appointments.cancelled',
            with: [
                'appointment' => $appointment,
                'clinic' => $appointment->clinic,
                'patient' => $appointment->patient,
                'doctor' => $appointment->doctor,
                'reference' => strtoupper(substr($appointment->id, 0, 8)),
                'reason' => $appointment->cancellation_reason,
            ],
        );
    }
}
