<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
    ) {}

    public function envelope(): Envelope
    {
        $clinic = $this->invoice->clinic;

        return new Envelope(
            subject: __('invoices_mail.receipt_subject', [
                'clinic' => $clinic->name,
                'number' => $this->invoice->invoice_number,
            ]),
        );
    }

    public function content(): Content
    {
        $invoice = $this->invoice->loadMissing(['clinic', 'patient', 'doctor', 'items', 'payments']);

        return new Content(
            markdown: 'mail.invoices.receipt',
            with: [
                'invoice' => $invoice,
                'clinic' => $invoice->clinic,
                'patient' => $invoice->patient,
                'doctor' => $invoice->doctor,
                'items' => $invoice->items,
            ],
        );
    }
}
