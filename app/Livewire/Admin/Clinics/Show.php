<?php

namespace App\Livewire\Admin\Clinics;

use App\Models\Clinic;
use App\Models\Plan;
use Livewire\Component;

class Show extends Component
{
    public Clinic $clinic;

    public string $manualPlanReason = '';

    public function mount(Clinic $clinic): void
    {
        $this->clinic = $clinic->load(['owner', 'plan', 'users']);
    }

    public function suspend(): void
    {
        $this->clinic->update(['status' => 'suspended']);
        session()->flash('success', __('admin.clinic_suspended'));
    }

    public function activate(): void
    {
        $this->clinic->update(['status' => 'active']);
        session()->flash('success', __('admin.clinic_activated'));
    }

    public function extendTrial(int $days = 14): void
    {
        $this->clinic->update([
            'status' => 'trial',
            'trial_ends_at' => now()->addDays($days),
        ]);
        session()->flash('success', __('admin.trial_extended', ['days' => $days]));
    }

    public function changePlan(int $planId): void
    {
        // Kept for backward compatibility but no longer exposed in UI.
        // Use assignManualPlan() instead.
        $plan = Plan::findOrFail($planId);

        $this->clinic->update([
            'plan_id' => $plan->id,
            'plan_type' => $plan->slug,
            'max_patients' => $plan->max_patients,
            'max_appointments_per_month' => $plan->max_appointments_per_month,
            'max_doctors' => $plan->max_doctors,
            'max_staff' => $plan->max_staff,
            'max_storage_bytes' => $plan->max_storage_bytes,
        ]);

        $this->clinic->refresh();
        session()->flash('success', __('admin.plan_changed', ['plan' => $plan->name]));
    }

    public function getSubscriptionInfoProperty(): ?array
    {
        $subscription = $this->clinic->subscription('default');

        if (! $subscription) {
            return null;
        }

        $item = $subscription->items->first();
        $priceId = $item?->price_id;

        // Resolve which plan they're paying for from Paddle price
        $paidPlan = null;
        if ($priceId) {
            $paidPlan = Plan::where('paddle_monthly_price_id', $priceId)
                ->orWhere('paddle_yearly_price_id', $priceId)
                ->first();
        }

        $isYearly = $paidPlan && $priceId === $paidPlan->paddle_yearly_price_id;

        return [
            'status' => $subscription->status,
            'paddle_id' => $subscription->paddle_id,
            'plan_name' => $paidPlan?->name,
            'price' => $paidPlan ? ($isYearly ? $paidPlan->yearly_price : $paidPlan->monthly_price) : null,
            'cycle' => $isYearly ? 'yearly' : 'monthly',
            'trial_ends_at' => $subscription->trial_ends_at,
            'ends_at' => $subscription->ends_at,
            'paused_at' => $subscription->paused_at,
        ];
    }

    public function assignManualPlan(int $planId): void
    {
        $this->validate([
            'manualPlanReason' => 'required|string|max:500',
        ]);

        $plan = Plan::findOrFail($planId);

        $this->clinic->update([
            'plan_id' => $plan->id,
            'plan_type' => $plan->slug,
            'is_manual_plan' => true,
            'manual_plan_reason' => $this->manualPlanReason,
            'max_patients' => $plan->max_patients,
            'max_appointments_per_month' => $plan->max_appointments_per_month,
            'max_doctors' => $plan->max_doctors,
            'max_staff' => $plan->max_staff,
            'max_storage_bytes' => $plan->max_storage_bytes,
        ]);

        $this->manualPlanReason = '';
        $this->clinic->refresh();
        session()->flash('success', __('admin.manual_plan_assigned', ['plan' => $plan->name]));
    }

    public function removeManualPlan(): void
    {
        $this->clinic->update([
            'is_manual_plan' => false,
            'manual_plan_reason' => null,
        ]);

        $this->clinic->refresh();
        session()->flash('success', __('admin.manual_plan_removed'));
    }

    public function getPatientsCountProperty(): int
    {
        return $this->clinic->patients()->count();
    }

    public function getAppointmentsThisMonthProperty(): int
    {
        return $this->clinic->appointments()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    public function render()
    {
        return view('livewire.admin.clinics.show', [
            'patientsCount' => $this->patientsCount,
            'appointmentsThisMonth' => $this->appointmentsThisMonth,
            'plans' => Plan::active()->ordered()->get(),
            'subscriptionInfo' => $this->subscriptionInfo,
        ])->layout('layouts.admin');
    }
}
