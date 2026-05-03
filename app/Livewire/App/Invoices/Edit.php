<?php

namespace App\Livewire\App\Invoices;

use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class Edit extends Component
{
    public Clinic $currentClinic;

    public Invoice $invoice;

    // Encabezado
    public ?string $doctor_id = null;

    public string $issued_at = '';

    public string $due_at = '';

    public string $notes = '';

    // Ítems dinámicos
    public array $items = [];

    // catalog_item_id por posición (forward-compat)
    public array $itemCatalogIds = [];

    protected function rules(): array
    {
        return [
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

    public function mount(Clinic $clinic, Invoice $invoice): void
    {
        abort_unless($invoice->clinic_id === $clinic->id, 404);

        // Solo se puede editar si está en draft o pending y sin pagos
        abort_unless(
            in_array($invoice->status, [Invoice::STATUS_DRAFT, Invoice::STATUS_PENDING])
            && $invoice->payments()->count() === 0,
            403
        );

        $this->currentClinic = $clinic;
        $this->invoice = $invoice;

        $this->doctor_id = $invoice->doctor_id ? (string) $invoice->doctor_id : null;
        $this->issued_at = $invoice->issued_at->toDateString();
        $this->due_at = $invoice->due_at?->toDateString() ?? '';
        $this->notes = $invoice->notes ?? '';

        $this->items = $invoice->items->sortBy('order')->values()->map(fn ($item) => [
            'type' => $item->type,
            'description' => $item->description,
            'quantity' => (string) $item->quantity,
            'unit_price' => (string) $item->unit_price,
            'discount_amount' => (string) $item->discount_amount,
            'tax_rate' => (string) $item->tax_rate,
        ])->toArray();

        $this->itemCatalogIds = $invoice->items->sortBy('order')->values()
            ->map(fn ($item) => $item->catalog_item_id)
            ->toArray();
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
        unset($this->itemCatalogIds[$index]);
        $this->itemCatalogIds = array_values($this->itemCatalogIds);
    }

    // ==================== CATÁLOGO ====================

    public function searchCatalog(string $term): array
    {
        if (strlen($term) < 1) {
            return [];
        }

        return ServiceCatalog::where('clinic_id', $this->currentClinic->id)
            ->where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'type', 'default_price', 'tax_rate_override', 'unit'])
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'type' => $c->type,
                'price' => (float) $c->default_price,
                'tax_rate' => $c->tax_rate_override !== null ? (float) $c->tax_rate_override : null,
                'unit' => $c->unit,
            ])
            ->toArray();
    }

    public function fillItemFromCatalog(int $index, string $catalogId): void
    {
        $item = ServiceCatalog::where('clinic_id', $this->currentClinic->id)
            ->where('is_active', true)
            ->findOrFail($catalogId);

        $taxRate = $item->tax_rate_override !== null
            ? (float) $item->tax_rate_override
            : (float) ($this->currentClinic->settings['tax_rate'] ?? 0);

        $this->items[$index]['description'] = $item->name;
        $this->items[$index]['unit_price'] = (string) $item->default_price;
        $this->items[$index]['tax_rate'] = (string) $taxRate;
        $this->itemCatalogIds[$index] = $catalogId;
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

    public function save(): void
    {
        $this->authorize('invoices.edit');
        $validated = $this->validate();

        abort_unless(
            in_array($this->invoice->status, [Invoice::STATUS_DRAFT, Invoice::STATUS_PENDING])
            && $this->invoice->payments()->count() === 0,
            403
        );

        DB::transaction(function () use ($validated) {
            $this->invoice->update([
                'doctor_id' => $validated['doctor_id'] ?: null,
                'issued_at' => $validated['issued_at'],
                'due_at' => $validated['due_at'] ?: null,
                'notes' => $validated['notes'] ?: null,
            ]);

            // Borrar ítems existentes y recrear
            $this->invoice->items()->delete();

            foreach ($validated['items'] as $order => $itemData) {
                $item = new InvoiceItem([
                    'invoice_id' => $this->invoice->id,
                    'catalog_item_id' => $this->itemCatalogIds[$order] ?? null,
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

            app(InvoiceService::class)->recalculate($this->invoice);
        });

        session()->flash('success', __('invoices.invoice_updated'));

        $this->redirect(route('app.invoices.show', [
            'clinic' => $this->currentClinic->slug,
            'invoice' => $this->invoice->id,
        ]), navigate: true);
    }

    public function render(): View
    {
        $this->authorize('invoices.edit');

        $doctors = User::query()
            ->where('clinic_id', $this->currentClinic->id)
            ->whereIn('role', ['doctor', 'owner'])
            ->orderBy('name')
            ->get();

        $itemTypes = InvoiceService::itemTypes();
        $breakdown = $this->breakdown;

        return view('livewire.app.invoices.edit', compact('doctors', 'itemTypes', 'breakdown'))
            ->layout('layouts.app');
    }
}
