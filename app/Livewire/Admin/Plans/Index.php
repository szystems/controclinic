<?php

namespace App\Livewire\Admin\Plans;

use App\Models\Plan;
use Livewire\Component;

class Index extends Component
{
    public bool $showInactive = false;

    public ?int $confirmDeletePlanId = null;

    public ?string $confirmDeletePlanName = null;

    public function askDeletePlan(int $planId): void
    {
        $plan = Plan::query()->withCount('clinics')->findOrFail($planId);

        if (! $plan->canBeDeleted()) {
            session()->flash('error', $plan->deletionBlockReason());

            return;
        }

        $this->confirmDeletePlanId = $plan->id;
        $this->confirmDeletePlanName = $plan->name;
    }

    public function cancelDeletePlan(): void
    {
        $this->confirmDeletePlanId = null;
        $this->confirmDeletePlanName = null;
    }

    public function deletePlan(): void
    {
        if (! $this->confirmDeletePlanId) {
            return;
        }

        $plan = Plan::query()->withCount('clinics')->findOrFail($this->confirmDeletePlanId);

        if (! $plan->canBeDeleted()) {
            session()->flash('error', $plan->deletionBlockReason());
            $this->cancelDeletePlan();

            return;
        }

        $plan->delete();

        $this->cancelDeletePlan();
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
