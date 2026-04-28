<?php

namespace App\Livewire\App\Billing;

use App\Models\Clinic;
use App\Models\Plan;
use GuzzleHttp\Client;
use Livewire\Component;

class Index extends Component
{
    public Clinic $clinic;

    public string $billingCycle = 'monthly';

    public string $selectedPlan = '';

    public function mount(Clinic $clinic): void
    {
        $this->clinic = $clinic;
        $this->selectedPlan = $clinic->plan_type === 'free' ? '' : $clinic->plan_type;
    }

    public function getSubscriptionProperty()
    {
        return $this->clinic->subscription();
    }

    public function getIsSubscribedProperty(): bool
    {
        return $this->subscription && $this->subscription->valid();
    }

    public function getCurrentPlanProperty(): string
    {
        return $this->clinic->plan_type;
    }

    public function getPlansProperty()
    {
        return Plan::active()
            ->ordered()
            ->where('is_free', false)
            ->get();
    }

    public function getPriceIdProperty(): ?string
    {
        if (! $this->selectedPlan || $this->selectedPlan === 'free' || $this->selectedPlan === 'enterprise') {
            return null;
        }

        $plan = $this->plans->firstWhere('slug', $this->selectedPlan);

        if (! $plan) {
            return null;
        }

        return $this->billingCycle === 'yearly'
            ? $plan->paddle_yearly_price_id
            : $plan->paddle_monthly_price_id;
    }

    public function checkout(string $planSlug): void
    {
        if ($planSlug === 'enterprise') {
            $this->redirect(route('contact'));

            return;
        }

        $plan = Plan::where('slug', $planSlug)->first();

        if (! $plan) {
            session()->flash('error', __('billing.plan_not_available'));

            return;
        }

        $priceId = $this->billingCycle === 'yearly'
            ? $plan->paddle_yearly_price_id
            : $plan->paddle_monthly_price_id;

        if (! $priceId) {
            $priceId = config("cashier.prices.{$planSlug}.{$this->billingCycle}");
        }

        if (! $priceId) {
            session()->flash('error', __('billing.plan_not_available'));

            return;
        }

        $this->selectedPlan = $planSlug;

        // Create transaction via Paddle API (bypasses domain check)
        $email = $this->clinic->owner->email ?? auth()->user()->email;
        $customer = $this->clinic->customer;

        try {
            $apiKey = config('cashier.api_key');
            $baseUrl = config('cashier.sandbox')
                ? 'https://sandbox-api.paddle.com'
                : 'https://api.paddle.com';

            $payload = [
                'items' => [['price_id' => $priceId, 'quantity' => 1]],
                'custom_data' => [
                    'clinic_id' => $this->clinic->id,
                    'plan_type' => $planSlug,
                    'billing_cycle' => $this->billingCycle,
                ],
            ];

            if ($customer) {
                $payload['customer_id'] = $customer->paddle_id;
            } else {
                // Create customer first or let Paddle auto-create
                $payload['customer'] = ['email' => $email];
            }

            $http = new Client;
            $response = $http->post("{$baseUrl}/transactions", [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody(), true);
            $transactionId = $data['data']['id'] ?? null;

            if ($transactionId) {
                $this->dispatch('open-paddle-checkout', transactionId: $transactionId);
            } else {
                session()->flash('error', __('billing.plan_not_available'));
            }
        } catch (\Exception $e) {
            session()->flash('error', __('billing.plan_not_available'));
            logger()->error('Paddle checkout error: '.$e->getMessage());
        }
    }

    public function changePlan(string $planSlug): void
    {
        if (! $this->isSubscribed || $planSlug === 'enterprise') {
            return;
        }

        $plan = Plan::where('slug', $planSlug)->first();

        if (! $plan) {
            session()->flash('error', __('billing.plan_not_available'));

            return;
        }

        $priceId = $this->billingCycle === 'yearly'
            ? $plan->paddle_yearly_price_id
            : $plan->paddle_monthly_price_id;

        if (! $priceId) {
            $priceId = config("cashier.prices.{$planSlug}.{$this->billingCycle}");
        }

        if (! $priceId) {
            session()->flash('error', __('billing.plan_not_available'));

            return;
        }

        $this->clinic->subscription()->swap($priceId);
        $this->clinic->update([
            'plan_id' => $plan->id,
            'plan_type' => $planSlug,
        ]);

        session()->flash('success', __('billing.plan_changed'));
    }

    public function cancelSubscription(): void
    {
        if (! $this->isSubscribed) {
            return;
        }

        $this->clinic->subscription()->cancel();
        session()->flash('success', __('billing.subscription_cancelled'));
    }

    public function resumeSubscription(): void
    {
        $subscription = $this->clinic->subscription();

        if ($subscription && $subscription->canceled()) {
            $subscription->resume();
            session()->flash('success', __('billing.subscription_resumed'));
        }
    }

    public function redirectToCustomerPortal(): void
    {
        $url = $this->clinic->customerPortalUrl();
        $this->redirect($url, navigate: false);
    }

    public function render()
    {
        return view('livewire.app.billing.index')
            ->layout('layouts.app');
    }
}
