<?php

namespace App\Livewire\App\Invoices;

use App\Models\Clinic;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\View\View;
use Livewire\Component;

class Show extends Component
{
    public Clinic $currentClinic;

    public Invoice $invoice;

    // Estado del modal de pago
    public bool $showPaymentModal = false;

    public string $pay_amount = '';

    public string $pay_method = 'cash';

    public string $pay_reference = '';

    public string $pay_notes = '';

    public string $pay_date = '';

    protected function paymentRules(): array
    {
        return [
            'pay_amount' => ['required', 'numeric', 'min:0.01'],
            'pay_method' => ['required', 'in:cash,card,transfer,insurance,other'],
            'pay_reference' => ['nullable', 'string', 'max:255'],
            'pay_notes' => ['nullable', 'string', 'max:500'],
            'pay_date' => ['required', 'date'],
        ];
    }

    public function mount(Clinic $clinic, Invoice $invoice): void
    {
        abort_unless($invoice->clinic_id === $clinic->id, 404);

        $this->currentClinic = $clinic;
        $this->invoice = $invoice;
        $this->pay_date = now()->toDateString();
    }

    public function openPaymentModal(): void
    {
        $this->authorize('invoices.record_payment');
        $this->reset(['pay_amount', 'pay_method', 'pay_reference', 'pay_notes']);
        $this->pay_date = now()->toDateString();
        $this->pay_method = 'cash';
        $balance = (float) $this->invoice->balance;
        $this->pay_amount = $balance > 0 ? (string) $balance : '';
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
    }

    public function recordPayment(): void
    {
        $this->authorize('invoices.record_payment');

        $validated = $this->validate($this->paymentRules());

        app(InvoiceService::class)->recordPayment($this->invoice, [
            'recorded_by' => auth()->id(),
            'amount' => $validated['pay_amount'],
            'currency' => $this->invoice->currency,
            'method' => $validated['pay_method'],
            'reference' => $validated['pay_reference'] ?: null,
            'notes' => $validated['pay_notes'] ?: null,
            'paid_at' => $validated['pay_date'],
        ]);

        $this->invoice->refresh();
        $this->closePaymentModal();
        $this->dispatch('payment-recorded');
        session()->flash('success', __('invoices.payment_recorded'));
    }

    public function deletePayment(string $paymentId): void
    {
        $this->authorize('invoices.record_payment');

        $invoice = $this->invoice;
        abort_if($invoice->status === Invoice::STATUS_CANCELLED, 403);

        app(InvoiceService::class)->deletePayment($invoice, $paymentId);

        $this->invoice->refresh();
        session()->flash('success', __('invoices.payment_deleted'));
    }

    public function cancel(): void
    {
        $this->authorize('invoices.edit');
        app(InvoiceService::class)->cancel($this->invoice);
        $this->invoice->refresh();
        session()->flash('success', __('invoices.invoice_cancelled'));
    }

    public function render(): View
    {
        $this->authorize('invoices.view');

        $this->invoice->loadMissing(['patient', 'doctor', 'appointment', 'items', 'payments.recordedBy', 'createdBy']);

        $paymentMethods = InvoiceService::paymentMethods();

        return view('livewire.app.invoices.show', compact('paymentMethods'))
            ->layout('layouts.app');
    }
}
