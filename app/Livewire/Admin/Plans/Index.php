<?php

namespace App\Livewire\Admin\Plans;

use App\Models\Plan;
use Livewire\Component;

class Index extends Component
{
    public bool $showInactive = false;

    public function deletePlan(int $planId): void
    {
        $plan = Plan::query()->withCount('clinics')->findOrFail($planId);

        if (! $plan->canBeDeleted()) {
            session()->flash('error', $plan->deletionBlockReason());

            return;
        }

        $plan->delete();

        session()->flash('success', __('admin.plan_deleted'));
    }

    public function render()
    {
        $query = Plan::query()->ordered()->withCount('clinics');

        if (! $this->showInactive) {
            $query->active();
        }

        return view('livewire.admin.plans.index', [
            'plans' => $query->get(),
        ])->layout('layouts.admin');
    }
}
