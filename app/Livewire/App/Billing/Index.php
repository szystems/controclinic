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

    public string $promoCode = '';

    public function mount(Clinic $clinic): void
    {
        $this->clinic = $clinic;
        $this->selectedPlan = $this->clinic->isOnFreePlan() ? '' : $this->clinic->planSlug();
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
        return $this->clinic->planSlug();
    }

    public function getPlansProperty()
    {
        return Plan::billingVisibleFor($this->clinic)
            ->ordered()
            ->get();
    }

    public function getIsPaddleReadyProperty(): bool
    {
        return (bool) config('cashier.api_key')
            && (config('cashier.client_side_token') || config('cashier.seller_id'));
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

        return $plan->paddlePriceIdForCycle($this->billingCycle);
    }

    public function redeemPromoCode(): void
    {
        $this->validate([
            'promoCode' => ['required', 'string', 'max:64'],
        ]);

        $plan = Plan::findByAccessCode($this->promoCode);

        if (! $plan) {
            $this->addError('promoCode', __('billing.promo_code_invalid'));

            return;
        }

        $this->clinic->unlockPlan($plan);
        $this->clinic->refresh();
        $this->promoCode = '';

        session()->flash('success', __('billing.promo_code_applied', ['plan' => $plan->name]));
    }

    public function checkout(string $planSlug): void
    {
        if ($planSlug === 'enterprise') {
            $this->redirect(route('contact'));

            return;
        }

        if (! $this->isPaddleReady) {
            session()->flash('error', __('billing.paddle_not_configured'));

            return;
        }

        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->first();

        if (! $plan || $plan->is_free) {
            session()->flash('error', __('billing.plan_not_available'));

            return;
        }

        if ($plan->is_private && ! $this->clinic->hasUnlockedPlan($plan)) {
            session()->flash('error', __('billing.plan_requires_code'));

            return;
        }

        $priceId = $this->resolvePaddlePriceId($plan, $planSlug);

        if (! $priceId) {
            session()->flash('error', __('billing.plan_price_not_configured', ['plan' => $plan->name]));

            return;
        }

        $this->selectedPlan = $planSlug;

        // ADR-012: no trial period — checkout uses price ID only (no trial_days).
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
            $message = $this->resolveCheckoutErrorMessage($e);
            session()->flash('error', $message);
            $this->dispatch('notify', type: 'error', message: $message);
            logger()->error('Paddle checkout error: '.$e->getMessage());
        }
    }

    protected function resolveCheckoutErrorMessage(\Exception $e): string
    {
        if ($e instanceof \GuzzleHttp\Exception\ClientException && $e->getResponse()) {
            $body = json_decode((string) $e->getResponse()->getBody(), true);
            $code = data_get($body, 'error.code');

            if ($code === 'transaction_default_checkout_url_not_set') {
                return __('billing.paddle_default_payment_link_missing');
            }
        }

        return __('billing.checkout_failed');
    }

    public function changePlan(string $planSlug): void
    {
        if (! $this->isSubscribed || $planSlug === 'enterprise') {
            return;
        }

        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->first();

        if (! $plan || ($plan->is_private && ! $this->clinic->hasUnlockedPlan($plan))) {
            session()->flash('error', __('billing.plan_not_available'));

            return;
        }

        $priceId = $this->resolvePaddlePriceId($plan, $planSlug);

        if (! $priceId) {
            session()->flash('error', __('billing.plan_not_available'));

            return;
        }

        $this->clinic->subscription()->swap($priceId);
        $this->clinic->applyPlan($plan);

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

    protected function resolvePaddlePriceId(Plan $plan, string $planSlug): ?string
    {
        $priceId = $plan->paddlePriceIdForCycle($this->billingCycle);

        if ($priceId) {
            return $priceId;
        }

        return config("cashier.prices.{$planSlug}.{$this->billingCycle}");
    }

    public function render()
    {
        return view('livewire.app.billing.index')
            ->layout('layouts.app');
    }
}
