<?php

namespace App\Livewire\Admin\Clinics;

use App\Models\Clinic;
use App\Models\Plan;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterPlan = '';

    public string $filterStatus = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPlan(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $clinics = Clinic::with(['owner', 'plan'])
            ->withCount(['patients', 'users'])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('slug', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterPlan, fn ($q) => $q->where('plan_type', $this->filterPlan))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(20);

        return view('livewire.admin.clinics.index', [
            'clinics' => $clinics,
            'plans' => Plan::active()->ordered()->get(),
        ])->layout('layouts.admin');
    }
}
