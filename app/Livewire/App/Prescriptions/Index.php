<?php

namespace App\Livewire\App\Prescriptions;

use App\Models\Prescription;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterStatus = '';

    public string $filterDoctor = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => '', 'as' => 'status'],
        'filterDoctor' => ['except' => '', 'as' => 'doctor'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function getPrescriptionsProperty()
    {
        $clinic = app('current_clinic');

        return Prescription::with(['patient', 'doctor'])
            ->where('clinic_id', $clinic->id)
            ->when($this->search, function ($q) {
                $q->whereHas('patient', fn ($pq) => $pq->where('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%")
                );
            })
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterDoctor, fn ($q) => $q->where('doctor_id', $this->filterDoctor))
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    public function render()
    {
        $this->authorize('viewAny', Prescription::class);

        return view('livewire.app.prescriptions.index', [
            'prescriptions' => $this->prescriptions,
            'statuses' => Prescription::STATUSES,
            'currentClinic' => app('current_clinic'),
        ])->layout('layouts.app');
    }
}
