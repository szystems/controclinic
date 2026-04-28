<?php

namespace App\Mail;

use App\Models\ClinicInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClinicInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ClinicInvitation $invitation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('invitations.email_subject', ['clinic' => $this->invitation->clinic->name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.clinic-invitation',
            with: [
                'invitation' => $this->invitation,
                'acceptUrl' => route('invitations.accept', $this->invitation->token),
                'clinicName' => $this->invitation->clinic->name,
                'inviterName' => $this->invitation->inviter->name,
                'roleName' => __('staff.role_'.$this->invitation->role),
                'expiresAt' => $this->invitation->expires_at,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
