<?php

namespace App\Livewire\App\Prescriptions;

use App\Models\Prescription;
use Livewire\Component;

class Show extends Component
{
    public Prescription $prescription;

    public bool $showCancelModal = false;

    public function mount(Prescription $prescription): void
    {
        $this->authorize('view', $prescription);
        $this->prescription = $prescription->load(['patient', 'doctor', 'items', 'medicalRecord']);
    }

    public function issue(): void
    {
        $this->authorize('issue', $this->prescription);
        $this->prescription->issue();
        $this->prescription->refresh()->load(['items']);
        session()->flash('success', __('prescriptions.issued_successfully'));
    }

    public function confirmCancel(): void
    {
        $this->authorize('cancel', $this->prescription);
        $this->showCancelModal = true;
    }

    public function cancel(): void
    {
        $this->authorize('cancel', $this->prescription);
        $this->prescription->cancel();
        $this->prescription->refresh();
        $this->showCancelModal = false;
        session()->flash('success', __('prescriptions.cancelled_successfully'));
    }

    public function render()
    {
        return view('livewire.app.prescriptions.show', [
            'currentClinic' => app('current_clinic'),
        ])->layout('layouts.app');
    }
}
