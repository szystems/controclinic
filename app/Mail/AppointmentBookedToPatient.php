<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentBookedToPatient extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment,
    ) {}

    public function envelope(): Envelope
    {
        $clinic = $this->appointment->clinic;

        return new Envelope(
            subject: __('appointments_mail.booked_patient_subject', [
                'clinic' => $clinic->name,
            ]),
        );
    }

    public function content(): Content
    {
        $appointment = $this->appointment->loadMissing(['clinic', 'patient', 'doctor']);

        return new Content(
            markdown: 'mail.appointments.booked-patient',
            with: [
                'appointment' => $appointment,
                'clinic' => $appointment->clinic,
                'patient' => $appointment->patient,
                'doctor' => $appointment->doctor,
                'reference' => strtoupper(substr($appointment->id, 0, 8)),
                'requiresConfirmation' => $appointment->status === Appointment::STATUS_SCHEDULED,
            ],
        );
    }
}
