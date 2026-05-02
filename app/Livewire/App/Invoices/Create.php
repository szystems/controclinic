<?php

namespace App\Livewire\App\Invoices;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class Create extends Component
{
    public Clinic $currentClinic;

    public ?string $appointmentId = null;

    // Encabezado
    public string $patient_id = '';

    public ?string $doctor_id = null;

    public string $issued_at = '';

    public string $due_at = '';

    public string $currency = 'USD';

    public string $notes = '';

    // Ítems dinámicos
    public array $items = [];

    // Búsqueda de paciente
    public string $patientSearch = '';

    public bool $showPatientDropdown = false;

    public ?string $patientName = null;

    protected function rules(): array
    {
        return [
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['nullable', 'exists:users,id'],
            'issued_at' => ['required', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', 'in:consultation,procedure,medication,lab,other'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function mount(Clinic $clinic, ?string $appointment = null): void
    {
        $this->currentClinic = $clinic;
        $this->issued_at = now()->toDateString();
        $this->currency = $clinic->currency ?: 'USD';

        $defaultPrice = (float) ($clinic->settings['default_consultation_price'] ?? 0);
        $defaultTaxRate = (float) ($clinic->settings['tax_rate'] ?? 0);

        // Si viene de una cita, prellena datos
        if ($appointment) {
            $appt = Appointment::where('clinic_id', $clinic->id)->findOrFail($appointment);
            $this->appointmentId = $appt->id;
            $this->patient_id = $appt->patient_id;
            $this->doctor_id = $appt->doctor_id ? (string) $appt->doctor_id : null;
            $this->patientName = $appt->patient->full_name ?? '';
            $this->patientSearch = $this->patientName;
        }

        $this->items = [
            [
                'type' => InvoiceItem::TYPE_CONSULTATION,
                'description' => __('invoices.item_type_consultation'),
                'quantity' => 1,
                'unit_price' => $defaultPrice,
                'discount_amount' => 0,
                'tax_rate' => $defaultTaxRate,
            ],
        ];
    }

    public function addItem(): void
    {
        $this->items[] = [
            'type' => InvoiceItem::TYPE_OTHER,
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'discount_amount' => 0,
            'tax_rate' => (float) ($this->currentClinic->settings['tax_rate'] ?? 0),
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function getItemsSubtotalProperty(): float
    {
        return $this->getBreakdownProperty()['total'];
    }

    public function getBreakdownProperty(): array
    {
        $subtotal = 0.0;
        $discount = 0.0;
        $tax = 0.0;

        foreach ($this->items as $item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $base = $qty * $price;
            $disc = (float) ($item['discount_amount'] ?? 0);
            $rate = (float) ($item['tax_rate'] ?? 0);
            $net = max($base - $disc, 0);
            $itemTax = round($net * ($rate / 100), 2);

            $subtotal += round($base, 2);
            $discount += round($disc, 2);
            $tax += $itemTax;
        }

        $total = round($subtotal - $discount + $tax, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'tax' => round($tax, 2),
            'total' => $total,
        ];
    }

    public function clearPatient(): void
    {
        $this->patient_id = '';
        $this->patientName = null;
        $this->patientSearch = '';
    }

    public function searchPatients(): array
    {
        if (strlen($this->patientSearch) < 2) {
            return [];
        }

        $search = $this->patientSearch;

        return Patient::query()
            ->where('clinic_id', $this->currentClinic->id)
            ->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'email', 'phone'])
            ->map(fn ($p) => [
                'id' => $p->id,
                'full_name' => $p->first_name.' '.$p->last_name,
                'email' => $p->email,
                'phone' => $p->phone,
            ])
            ->toArray();
    }

    public function selectPatient(string $id, string $name): void
    {
        $this->patient_id = $id;
        $this->patientName = $name;
        $this->patientSearch = $name;
        $this->showPatientDropdown = false;
        $this->resetErrorBag('patient_id');
    }

    public function save(): void
    {
        $this->authorize('invoices.create');
        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            $service = app(InvoiceService::class);
            $number = $service->nextInvoiceNumber($this->currentClinic);

            $invoice = Invoice::create([
                'clinic_id' => $this->currentClinic->id,
                'patient_id' => $validated['patient_id'],
                'doctor_id' => $validated['doctor_id'] ?: null,
                'appointment_id' => $this->appointmentId,
                'created_by' => auth()->id(),
                'invoice_number' => $number,
                'issued_at' => $validated['issued_at'],
                'due_at' => $validated['due_at'] ?: null,
                'currency' => $this->currency,
                'notes' => $validated['notes'] ?: null,
                'status' => Invoice::STATUS_PENDING,
                'subtotal' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => 0,
                'paid_amount' => 0,
            ]);

            foreach ($validated['items'] as $order => $itemData) {
                $item = new InvoiceItem([
                    'invoice_id' => $invoice->id,
                    'order' => $order + 1,
                    'type' => $itemData['type'],
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount_amount' => $itemData['discount_amount'] ?? 0,
                    'tax_rate' => $itemData['tax_rate'] ?? 0,
                    'total' => 0,
                ]);
                $item->total = $item->calculateTotal();
                $item->save();
            }

            $service->recalculate($invoice);

            $this->redirect(route('app.invoices.show', [
                'clinic' => $this->currentClinic->slug,
                'invoice' => $invoice->id,
            ]), navigate: true);
        });

        session()->flash('success', __('invoices.invoice_created'));
    }

    public function render(): View
    {
        $this->authorize('invoices.create');

        $doctors = User::query()
            ->where('clinic_id', $this->currentClinic->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'doctor'))
            ->orderBy('name')
            ->get();

        $patients = strlen($this->patientSearch) >= 2
            ? $this->searchPatients()
            : [];

        $itemTypes = InvoiceService::itemTypes();

        return view('livewire.app.invoices.create', compact('doctors', 'patients', 'itemTypes'))
            ->layout('layouts.app');
    }
}
