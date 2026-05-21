<?php

namespace App\Livewire\App\Prescriptions;

use App\Models\Prescription;
use Livewire\Component;

class Edit extends Component
{
    public Prescription $prescription;

    public string $diagnosis = '';

    public string $notes = '';

    public string $internalNotes = '';

    public string $validUntil = '';

    public string $issuedAt = '';

    public array $items = [];

    protected function rules(): array
    {
        return [
            'diagnosis' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'internalNotes' => ['nullable', 'string', 'max:1000'],
            'validUntil' => ['nullable', 'date'],
            'issuedAt' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medication_name' => ['required', 'string', 'max:200'],
            'items.*.dose' => ['nullable', 'string', 'max:100'],
            'items.*.frequency' => ['nullable', 'string', 'max:100'],
            'items.*.duration' => ['nullable', 'string', 'max:100'],
            'items.*.route' => ['nullable', 'string', 'max:100'],
            'items.*.instructions' => ['nullable', 'string', 'max:500'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
            'items.*.is_controlled' => ['boolean'],
        ];
    }

    public function mount(Prescription $prescription): void
    {
        $this->authorize('update', $prescription);
        $this->prescription = $prescription->load('items');

        $this->diagnosis = $prescription->diagnosis ?? '';
        $this->notes = $prescription->notes ?? '';
        $this->internalNotes = $prescription->internal_notes ?? '';
        $this->validUntil = $prescription->valid_until?->toDateString() ?? '';
        $this->issuedAt = $prescription->issued_at?->toDateString() ?? now()->toDateString();

        $this->items = $prescription->items->map(fn ($item) => [
            'id' => $item->id,
            'medication_name' => $item->medication_name,
            'active_ingredient' => $item->active_ingredient ?? '',
            'presentation' => $item->presentation ?? '',
            'dose' => $item->dose ?? '',
            'frequency' => $item->frequency ?? '',
            'duration' => $item->duration ?? '',
            'route' => $item->route ?? '',
            'instructions' => $item->instructions ?? '',
            'quantity' => $item->quantity,
            'is_controlled' => (bool) $item->is_controlled,
        ])->toArray();

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function addItem(): void
    {
        $this->items[] = [
            'medication_name' => '',
            'active_ingredient' => '',
            'presentation' => '',
            'dose' => '',
            'frequency' => '',
            'duration' => '',
            'route' => '',
            'instructions' => '',
            'quantity' => null,
            'is_controlled' => false,
        ];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            array_splice($this->items, $index, 1);
            $this->items = array_values($this->items);
        }
    }

    public function save(): void
    {
        $this->authorize('update', $this->prescription);
        $this->validate();

        $this->prescription->update([
            'diagnosis' => $this->diagnosis ?: null,
            'notes' => $this->notes ?: null,
            'internal_notes' => $this->internalNotes ?: null,
            'issued_at' => $this->issuedAt ?: null,
            'valid_until' => $this->validUntil ?: null,
        ]);

        // Reemplazar todos los items
        $this->prescription->items()->delete();
        foreach ($this->items as $order => $itemData) {
            $this->prescription->items()->create(array_merge(
                collect($itemData)->except('id')->toArray(),
                ['order' => $order]
            ));
        }

        $clinic = app('current_clinic');
        session()->flash('success', __('prescriptions.updated_successfully'));

        $this->redirect(
            route('app.prescriptions.show', ['clinic' => $clinic->slug, 'prescription' => $this->prescription->id]),
            navigate: true
        );
    }

    public function render()
    {
        return view('livewire.app.prescriptions.edit', [
            'currentClinic' => app('current_clinic'),
        ])->layout('layouts.app');
    }
}
