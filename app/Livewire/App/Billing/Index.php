<?php

namespace App\Livewire\App\Billing;

use App\Models\Clinic;
use App\Models\Plan;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Paddle\Cashier;
use Laravel\Paddle\Exceptions\PaddleException;
use Livewire\Component;

class Index extends Component
{
    public Clinic $clinic;

    public string $billingCycle = 'monthly';

    public string $selectedPlan = '';

    public string $promoCode = '';

    public function mount(Clinic $clinic): void
    {
        $this->authorizeBilling();
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
        $this->authorizeBilling();

        $key = 'billing-promo:'.$this->clinic->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('promoCode', __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => (int) ceil($seconds / 60),
            ]));

            return;
        }
        RateLimiter::hit($key, 600);

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
        $this->authorizeBilling();

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
        try {
            $customer = $this->resolveBillableCustomer();

            $response = Cashier::api('POST', 'transactions', [
                'items' => [['price_id' => $priceId, 'quantity' => 1]],
                'customer_id' => $customer->paddle_id,
                'custom_data' => [
                    'clinic_id' => $this->clinic->id,
                    'plan_type' => $planSlug,
                    'billing_cycle' => $this->billingCycle,
                ],
            ]);

            $transactionId = $response['data']['id'] ?? null;

            if ($transactionId) {
                $this->dispatch('open-paddle-checkout', transactionId: $transactionId);
            } else {
                $this->dispatch('notify', type: 'error', message: __('billing.checkout_failed'));
            }
        } catch (\Exception $e) {
            $message = $this->resolveCheckoutErrorMessage($e);
            $this->dispatch('notify', type: 'error', message: $message);
            logger()->error('Paddle checkout error: '.$e->getMessage());
        }
    }

    protected function resolveCheckoutErrorMessage(\Exception $e): string
    {
        $code = $e instanceof PaddleException ? ($e->getError()['code'] ?? null) : null;

        if ($code === 'transaction_default_checkout_url_not_set') {
            return __('billing.paddle_default_payment_link_missing');
        }

        return __('billing.checkout_failed');
    }

    /**
     * Customer row for the current Paddle account. A stored id from an older
     * account is replaced; clinics with no customer are created as usual.
     */
    protected function resolveBillableCustomer(): \Laravel\Paddle\Customer
    {
        $email = $this->clinic->owner->email ?? auth()->user()->email;
        $name = $this->clinic->owner->name ?? $this->clinic->name;
        $customer = $this->clinic->customer;

        if (! $customer) {
            return $this->clinic->createAsCustomer([
                'email' => $email,
                'name' => $name,
            ]);
        }

        if ($this->paddleCustomerExists($customer->paddle_id)) {
            return $customer;
        }

        $response = Cashier::api('GET', 'customers', [
            'status' => 'active,archived',
            'email' => $email,
        ])['data'][0] ?? null;

        if ($response === null) {
            $response = Cashier::api('POST', 'customers', [
                'email' => $email,
                'name' => $name,
            ])['data'];
        }

        $customer->paddle_id = $response['id'];
        $customer->name = $response['name'] ?? $name;
        $customer->email = $response['email'] ?? $email;
        $customer->save();

        return $customer;
    }

    protected function paddleCustomerExists(string $paddleId): bool
    {
        try {
            Cashier::api('GET', 'customers/'.$paddleId);

            return true;
        } catch (PaddleException $e) {
            if ($this->isMissingPaddleResource($e)) {
                return false;
            }

            throw $e;
        }
    }

    protected function isMissingPaddleResource(\Throwable $e): bool
    {
        if (! $e instanceof PaddleException) {
            return false;
        }

        $code = $e->getError()['code'] ?? null;

        return $code === 'url_called_is_invalid'
            || str_contains($e->getMessage(), 'URL called is invalid.');
    }

    public function changePlan(string $planSlug): void
    {
        $this->authorizeBilling();

        if (! $this->isSubscribed || $planSlug === 'enterprise') {
            return;
        }

        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->first();

        if (! $plan || $plan->is_free || ($plan->is_private && ! $this->clinic->hasUnlockedPlan($plan))) {
            $this->dispatch('notify', type: 'error', message: __('billing.plan_not_available'));

            return;
        }

        $priceId = $this->resolvePaddlePriceId($plan, $planSlug);

        if (! $priceId) {
            $this->dispatch('notify', type: 'error', message: __('billing.plan_not_available'));

            return;
        }

        $subscription = $this->clinic->subscription();

        if ($subscription->hasPrice($priceId)) {
            $this->dispatch('notify', type: 'info', message: __('billing.already_on_plan'));

            return;
        }

        $currentSort = $this->clinic->resolvePlan()?->sort_order ?? 0;
        $isUpgrade = $plan->sort_order > $currentSort;

        try {
            if ($isUpgrade) {
                // Upgrade: cobra la diferencia prorrateada de inmediato al método de pago en archivo.
                $subscription->swapAndInvoice($priceId);
            } else {
                // Downgrade: se aplica al final del período actual (prorrateo al próximo ciclo).
                $subscription->swap($priceId);
            }

            // Quien llega aquí es suscriptor real de Paddle: ya no es un plan manual del admin.
            // La fuente de verdad es Paddle (webhook subscription.updated); reflejamos local para UX inmediata.
            if ($this->clinic->is_manual_plan) {
                $this->clinic->update(['is_manual_plan' => false]);
            }

            $this->clinic->applyPlan($plan);

            $this->clinic->refresh();
            $this->selectedPlan = $planSlug;

            $message = $isUpgrade
                ? __('billing.plan_upgraded', ['plan' => $plan->name])
                : __('billing.plan_downgrade_scheduled', ['plan' => $plan->name]);

            session()->flash('success', $message);
            $this->dispatch('notify', type: 'success', message: $message);
        } catch (\Exception $e) {
            logger()->error('Paddle change plan error: '.$e->getMessage());

            // The local Cashier row can point at a subscription from a previous Paddle
            // account. That account answers "URL called is invalid" and there is nothing
            // to prorate, so the selected plan has to start as a new checkout.
            if ($this->isMissingPaddleResource($e)) {
                $this->dispatch('notify', type: 'info', message: __('billing.subscription_restart_checkout', [
                    'plan' => $plan->name,
                ]));
                $this->checkout($planSlug);

                return;
            }

            $this->dispatch('notify', type: 'error', message: __('billing.change_plan_failed'));
        }
    }

    public function cancelSubscription(): void
    {
        $this->authorizeBilling();

        $subscription = $this->clinic->subscription();

        if (! $subscription || $subscription->canceled()) {
            return;
        }

        try {
            $subscription->cancel();
            session()->flash('success', __('billing.subscription_cancelled'));
            $this->dispatch('notify', type: 'success', message: __('billing.subscription_cancelled'));
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: __('billing.change_plan_failed'));
            logger()->error('Paddle cancel error: '.$e->getMessage());
        }
    }

    public function resumeSubscription(): void
    {
        $this->authorizeBilling();

        $subscription = $this->clinic->subscription();

        // resume() applies to a subscription canceled on grace period or paused.
        $resumable = $subscription
            && ($subscription->onGracePeriod() || $subscription->paused() || $subscription->onPausedGracePeriod());

        if (! $resumable) {
            return;
        }

        try {
            $subscription->resume();
            session()->flash('success', __('billing.subscription_resumed'));
            $this->dispatch('notify', type: 'success', message: __('billing.subscription_resumed'));
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: __('billing.change_plan_failed'));
            logger()->error('Paddle resume error: '.$e->getMessage());
        }
    }

    public function redirectToCustomerPortal(): void
    {
        $this->authorizeBilling();

        try {
            $url = $this->clinic->customerPortalUrl();
            $this->redirect($url, navigate: false);
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: __('billing.portal_unavailable'));
            logger()->error('Paddle customer portal error: '.$e->getMessage());
        }
    }

    private function authorizeBilling(): void
    {
        abort_unless(auth()->user()?->can('billing.manage'), 403);
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
