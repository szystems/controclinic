<?php

namespace App\Livewire\App\Settings;

use App\Models\Clinic;
use App\Models\ServiceCatalog;
use Illuminate\View\View;
use Livewire\Component;

class Catalog extends Component
{
    public $currentClinic;

    // Lista + filtros
    public string $search = '';

    public string $filterType = '';

    // Modal de crear/editar
    public bool $showModal = false;

    public ?string $editingId = null;

    public string $name = '';

    public string $type = 'service';

    public string $sku = '';

    public string $description = '';

    public string $default_price = '0';

    public string $tax_rate_override = '';

    public string $unit = 'unit';

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'type' => 'required|in:service,product',
            'sku' => 'nullable|string|max:60',
            'description' => 'nullable|string|max:500',
            'default_price' => 'required|numeric|min:0',
            'tax_rate_override' => 'nullable|numeric|min:0|max:100',
            'unit' => 'required|string|max:30',
            'is_active' => 'boolean',
        ];
    }

    public function mount(Clinic $clinic): void
    {
        $this->currentClinic = $clinic;
    }

    // ==================== MODAL ====================

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'sku', 'description', 'default_price', 'tax_rate_override', 'unit', 'is_active']);
        $this->type = 'service';
        $this->default_price = '0';
        $this->unit = 'unit';
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $item = ServiceCatalog::where('clinic_id', $this->currentClinic->id)->findOrFail($id);
        $this->editingId = $item->id;
        $this->name = $item->name;
        $this->type = $item->type;
        $this->sku = $item->sku ?? '';
        $this->description = $item->description ?? '';
        $this->default_price = (string) $item->default_price;
        $this->tax_rate_override = $item->tax_rate_override !== null ? (string) $item->tax_rate_override : '';
        $this->unit = $item->unit;
        $this->is_active = $item->is_active;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    // ==================== SAVE ====================

    public function save(): void
    {
        $this->authorize('settings.edit');
        $validated = $this->validate();

        $data = [
            'clinic_id' => $this->currentClinic->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'sku' => $validated['sku'] ?: null,
            'description' => $validated['description'] ?: null,
            'default_price' => $validated['default_price'],
            'tax_rate_override' => $validated['tax_rate_override'] !== '' ? $validated['tax_rate_override'] : null,
            'unit' => $validated['unit'],
            'is_active' => $validated['is_active'],
        ];

        if ($this->editingId) {
            $item = ServiceCatalog::where('clinic_id', $this->currentClinic->id)->findOrFail($this->editingId);
            $item->update($data);
            session()->flash('success', __('catalog.item_updated'));
        } else {
            ServiceCatalog::create($data);
            session()->flash('success', __('catalog.item_created'));
        }

        $this->showModal = false;
    }

    // ==================== DELETE / TOGGLE ====================

    public function toggleActive(string $id): void
    {
        $this->authorize('settings.edit');
        $item = ServiceCatalog::where('clinic_id', $this->currentClinic->id)->findOrFail($id);
        $item->update(['is_active' => ! $item->is_active]);
    }

    public function delete(string $id): void
    {
        $this->authorize('settings.edit');
        $item = ServiceCatalog::where('clinic_id', $this->currentClinic->id)->findOrFail($id);

        // No eliminar si tiene ítems de factura asociados
        if ($item->invoiceItems()->count() > 0) {
            session()->flash('error', __('catalog.cannot_delete_has_items'));

            return;
        }

        $item->delete();
        session()->flash('success', __('catalog.item_deleted'));
    }

    // ==================== RENDER ====================

    public function render(): View
    {
        $this->authorize('settings.edit');

        $items = ServiceCatalog::where('clinic_id', $this->currentClinic->id)
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('sku', 'like', "%{$this->search}%"))
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(25);

        return view('livewire.app.settings.catalog', compact('items'))
            ->layout('layouts.app');
    }
}
