<?php

namespace App\Livewire\App\Patients;

use App\Models\Clinic;
use App\Models\Patient;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public Clinic $currentClinic;
    public string $search = '';
    public string $status = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    protected $listeners = [
        'patientCreated' => '$refresh',
        'patientUpdated' => '$refresh',
        'patientDeleted' => '$refresh',
    ];

    public function mount(Clinic $clinic): void
    {
        $this->currentClinic = $clinic;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getPatientsProperty()
    {
        return Patient::query()
            ->where('clinic_id', $this->currentClinic->id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('medical_record_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status !== '', function ($query) {
                $query->where('is_active', $this->status === 'active');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);
    }

    public function deletePatient(string $id): void
    {
        $patient = Patient::findOrFail($id);

        if (!auth()->user()->can('patients.delete')) {
            session()->flash('error', __('general.unauthorized'));
            return;
        }

        $patient->delete();

        session()->flash('success', __('patients.deleted_successfully'));
        $this->dispatch('patientDeleted');
    }

    public function toggleStatus(string $id): void
    {
        $patient = Patient::findOrFail($id);

        if (!auth()->user()->can('patients.edit')) {
            session()->flash('error', __('general.unauthorized'));
            return;
        }

        $patient->update(['is_active' => !$patient->is_active]);

        session()->flash('success', __('patients.status_updated'));
    }

    public function render()
    {
        return view('livewire.app.patients.index', [
            'patients' => $this->patients,
        ])->layout('layouts.app');
    }
}
