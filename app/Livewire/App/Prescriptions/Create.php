<?php

namespace App\Livewire\App\Prescriptions;

use App\Models\Patient;
use App\Models\Prescription;
use Livewire\Component;

class Create extends Component
{
    // Prescription fields
    public string $patientId = '';

    public string $patientSearch = '';

    public string $selectedPatientName = '';

    public string $diagnosis = '';

    public string $notes = '';

    public string $internalNotes = '';

    public string $validUntil = '';

    public string $issuedAt = '';

    public bool $issueNow = false;

    // Items (repeater)
    public array $items = [];

    protected function rules(): array
    {
        return [
            'patientId' => ['required', 'uuid', 'exists:patients,id'],
            'diagnosis' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'internalNotes' => ['nullable', 'string', 'max:1000'],
            'validUntil' => ['nullable', 'date', 'after_or_equal:today'],
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

    public function mount(?string $patientId = null): void
    {
        $this->authorize('create', Prescription::class);
        $this->issuedAt = now()->toDateString();
        if ($patientId) {
            $this->patientId = $patientId;
            $patient = Patient::find($patientId);
            $this->selectedPatientName = $patient?->full_name ?? '';
        }
        $this->addItem();
    }

    public function selectPatient(string $id, string $name): void
    {
        $this->patientId = $id;
        $this->selectedPatientName = $name;
        $this->patientSearch = '';
    }

    public function clearPatient(): void
    {
        $this->patientId = '';
        $this->selectedPatientName = '';
        $this->patientSearch = '';
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

    public function save(bool $issue = false): void
    {
        $this->authorize('create', Prescription::class);
        $this->validate();

        $clinic = app('current_clinic');

        // Verificar que el paciente pertenece a esta clínica
        $patient = Patient::where('id', $this->patientId)
            ->where('clinic_id', $clinic->id)
            ->firstOrFail();

        $prescription = Prescription::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => auth()->id(),
            'status' => Prescription::STATUS_DRAFT,
            'diagnosis' => $this->diagnosis ?: null,
            'notes' => $this->notes ?: null,
            'internal_notes' => $this->internalNotes ?: null,
            'issued_at' => $this->issuedAt ?: now()->toDateString(),
            'valid_until' => $this->validUntil ?: null,
        ]);

        foreach ($this->items as $order => $itemData) {
            $prescription->items()->create(array_merge($itemData, ['order' => $order]));
        }

        if ($issue || $this->issueNow) {
            $prescription->issue();
        }

        session()->flash('success', __('prescriptions.created_successfully'));

        $this->redirect(
            route('app.prescriptions.show', ['clinic' => $clinic->slug, 'prescription' => $prescription->id]),
            navigate: true
        );
    }

    public function getSearchResultsProperty()
    {
        if (strlen(trim($this->patientSearch)) < 2) {
            return collect();
        }

        $clinic = app('current_clinic');

        return Patient::where('clinic_id', $clinic->id)
            ->where('is_active', true)
            ->search($this->patientSearch)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'phone', 'email']);
    }

    public function render()
    {
        return view('livewire.app.prescriptions.create', [
            'currentClinic' => app('current_clinic'),
            'searchResults' => $this->searchResults,
        ])->layout('layouts.app');
    }
}
