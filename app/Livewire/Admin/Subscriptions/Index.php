<?php

namespace App\Livewire\Admin\Subscriptions;

use App\Billing\LiveSubscriptions;
use App\Models\Plan;
use Laravel\Paddle\Subscription;
use Laravel\Paddle\Transaction;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $status = '';

    public string $account = 'live';

    public function mount(): void
    {
        abort_unless(auth()->user()?->is_super_admin, 403);

        $status = (string) request()->query('status', '');
        if (in_array($status, ['active', 'past_due', 'paused', 'canceled', 'trialing'], true)) {
            $this->status = $status;
        }

        $account = (string) request()->query('account', 'live');
        if (in_array($account, ['live', 'previous'], true)) {
            $this->account = $account;
        }
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingAccount(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        abort_unless(auth()->user()?->is_super_admin, 403);

        $subscriptions = Subscription::query()
            ->with(['billable.owner', 'items'])
            ->when($this->account === 'previous', fn ($query) => $query->whereNull('billing_environment'))
            ->when($this->account !== 'previous', fn ($query) => $query->where('billing_environment', LiveSubscriptions::ENVIRONMENT))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->latest('id')
            ->paginate(20);

        $plansByPrice = [];
        foreach (Plan::query()->get() as $plan) {
            if ($plan->paddle_monthly_price_id) {
                $plansByPrice[$plan->paddle_monthly_price_id] = ['name' => $plan->name, 'cycle' => 'monthly'];
            }
            if ($plan->paddle_yearly_price_id) {
                $plansByPrice[$plan->paddle_yearly_price_id] = ['name' => $plan->name, 'cycle' => 'yearly'];
            }
        }

        $lastPayments = Transaction::query()
            ->whereIn('paddle_subscription_id', $subscriptions->getCollection()->pluck('paddle_id'))
            ->where('status', Transaction::STATUS_COMPLETED)
            ->orderByDesc('billed_at')
            ->get()
            ->unique('paddle_subscription_id')
            ->keyBy('paddle_subscription_id');

        return view('livewire.admin.subscriptions.index', [
            'subscriptions' => $subscriptions,
            'plansByPrice' => $plansByPrice,
            'lastPayments' => $lastPayments,
        ])->layout('layouts.admin');
    }
}
