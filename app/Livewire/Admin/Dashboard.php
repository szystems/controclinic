<?php

namespace App\Livewire\Admin;

use App\Models\Clinic;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Paddle\Subscription;
use Laravel\Paddle\Transaction;
use Livewire\Component;

class Dashboard extends Component
{
    public function getTotalClinicsProperty(): int
    {
        return Clinic::count();
    }

    public function getActiveClinicsProperty(): int
    {
        return Clinic::active()->count();
    }

    public function getSuspendedClinicsProperty(): int
    {
        return Clinic::where('status', 'suspended')->count();
    }

    public function getTrialClinicsProperty(): int
    {
        return Clinic::where('status', 'trial')->count();
    }

    public function getTotalUsersProperty(): int
    {
        return User::count();
    }

    public function getNewClinicsThisMonthProperty(): int
    {
        return Clinic::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    public function getClinicsByPlanProperty(): array
    {
        return Clinic::select('plan_type', DB::raw('count(*) as total'))
            ->groupBy('plan_type')
            ->pluck('total', 'plan_type')
            ->toArray();
    }

    public function getRecentClinicsProperty()
    {
        return Clinic::with('owner', 'plan')
            ->latest()
            ->take(10)
            ->get();
    }

    public function getActivePlansProperty()
    {
        return Plan::active()->ordered()->withCount('clinics')->get();
    }

    // --- Paddle Subscription Stats ---

    public function getActiveSubscriptionsProperty(): int
    {
        return Subscription::where('status', Subscription::STATUS_ACTIVE)->count();
    }

    public function getTrialingSubscriptionsProperty(): int
    {
        return Subscription::where('status', Subscription::STATUS_TRIALING)->count();
    }

    public function getPastDueSubscriptionsProperty(): int
    {
        return Subscription::where('status', Subscription::STATUS_PAST_DUE)->count();
    }

    public function getPausedSubscriptionsProperty(): int
    {
        return Subscription::where('status', Subscription::STATUS_PAUSED)->count();
    }

    public function getCanceledSubscriptionsProperty(): int
    {
        return Subscription::where('status', Subscription::STATUS_CANCELED)->count();
    }

    public function getSubscriptionBreakdownProperty(): array
    {
        return [
            'active' => $this->activeSubscriptions,
            'trialing' => $this->trialingSubscriptions,
            'past_due' => $this->pastDueSubscriptions,
            'paused' => $this->pausedSubscriptions,
            'canceled' => $this->canceledSubscriptions,
        ];
    }

    // --- Revenue Stats ---

    public function getRevenueThisMonthProperty(): array
    {
        $transactions = Transaction::where('status', Transaction::STATUS_COMPLETED)
            ->whereMonth('billed_at', now()->month)
            ->whereYear('billed_at', now()->year)
            ->get();

        $total = $transactions->sum(fn ($t) => (int) $t->total);
        $currency = $transactions->first()?->currency ?? 'USD';

        return [
            'total' => number_format($total / 100, 2),
            'currency' => $currency,
            'count' => $transactions->count(),
        ];
    }

    public function getRevenueLastMonthProperty(): array
    {
        $lastMonth = now()->subMonth();
        $transactions = Transaction::where('status', Transaction::STATUS_COMPLETED)
            ->whereMonth('billed_at', $lastMonth->month)
            ->whereYear('billed_at', $lastMonth->year)
            ->get();

        $total = $transactions->sum(fn ($t) => (int) $t->total);
        $currency = $transactions->first()?->currency ?? 'USD';

        return [
            'total' => number_format($total / 100, 2),
            'currency' => $currency,
            'count' => $transactions->count(),
        ];
    }

    // --- Conversion & Manual Plans ---

    public function getConversionRateProperty(): float
    {
        $totalClinics = $this->totalClinics;
        if ($totalClinics === 0) {
            return 0;
        }

        $paidClinics = Clinic::whereHas('plan', fn ($q) => $q->where('is_free', false))->count();

        return round(($paidClinics / $totalClinics) * 100, 1);
    }

    public function getManualPlanClinicsProperty(): int
    {
        return Clinic::where('is_manual_plan', true)->count();
    }

    // --- Recent Transactions ---

    public function getRecentTransactionsProperty()
    {
        return Transaction::with('billable')
            ->where('status', '!=', Transaction::STATUS_DRAFT)
            ->latest('billed_at')
            ->take(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.dashboard', [
            'totalClinics' => $this->totalClinics,
            'activeClinics' => $this->activeClinics,
            'suspendedClinics' => $this->suspendedClinics,
            'trialClinics' => $this->trialClinics,
            'totalUsers' => $this->totalUsers,
            'newClinicsThisMonth' => $this->newClinicsThisMonth,
            'clinicsByPlan' => $this->clinicsByPlan,
            'recentClinics' => $this->recentClinics,
            'activePlans' => $this->activePlans,
            'activeSubscriptions' => $this->activeSubscriptions,
            'subscriptionBreakdown' => $this->subscriptionBreakdown,
            'revenueThisMonth' => $this->revenueThisMonth,
            'revenueLastMonth' => $this->revenueLastMonth,
            'conversionRate' => $this->conversionRate,
            'manualPlanClinics' => $this->manualPlanClinics,
            'recentTransactions' => $this->recentTransactions,
        ])->layout('layouts.admin');
    }
}
