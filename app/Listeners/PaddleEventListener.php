<?php

namespace App\Listeners;

use App\Models\Clinic;
use App\Models\Plan;
use Illuminate\Events\Dispatcher;
use Laravel\Paddle\Events\SubscriptionCanceled;
use Laravel\Paddle\Events\SubscriptionCreated;
use Laravel\Paddle\Events\SubscriptionUpdated;

class PaddleEventListener
{
    public function handleSubscriptionCreated(SubscriptionCreated $event): void
    {
        $clinic = $event->billable;

        if (! $clinic instanceof Clinic) {
            return;
        }

        $plan = $this->resolvePlanFromPrice($event->subscription);

        if ($plan) {
            // Don't override manually assigned plans
            if ($clinic->is_manual_plan) {
                $clinic->update(['status' => 'active']);

                return;
            }

            $clinic->applyPlan($plan, activate: true);
        }
    }

    public function handleSubscriptionUpdated(SubscriptionUpdated $event): void
    {
        // SubscriptionUpdated only exposes the subscription + payload (no billable).
        $clinic = $event->subscription->billable;

        if (! $clinic instanceof Clinic) {
            return;
        }

        $plan = $this->resolvePlanFromPrice($event->subscription);

        if ($plan) {
            // Don't override manually assigned plans
            if ($clinic->is_manual_plan) {
                return;
            }
            $clinic->applyPlan($plan);
        }
    }

    public function handleSubscriptionCanceled(SubscriptionCanceled $event): void
    {
        // SubscriptionCanceled only exposes the subscription + payload (no billable).
        $clinic = $event->subscription->billable;

        if (! $clinic instanceof Clinic) {
            return;
        }

        // Don't immediately downgrade — subscription stays valid until grace period ends.
        // The CheckPlanLimits middleware handles the downgrade when the subscription actually expires.
    }

    /**
     * Resolve plan from subscription's price ID.
     */
    private function resolvePlanFromPrice($subscription): ?Plan
    {
        $priceId = $subscription->items->first()?->price_id;

        if (! $priceId) {
            return null;
        }

        // Try to find plan by Paddle price ID in DB
        $plan = Plan::findByPaddlePriceId($priceId);

        if ($plan) {
            return $plan;
        }

        // Fallback: resolve from config (legacy env mapping)
        $prices = config('cashier.prices', []);

        foreach ($prices as $slug => $cycles) {
            foreach ($cycles as $priceValue) {
                if ($priceValue === $priceId) {
                    return Plan::where('slug', $slug)->first();
                }
            }
        }

        return null;
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(SubscriptionCreated::class, [self::class, 'handleSubscriptionCreated']);
        $events->listen(SubscriptionUpdated::class, [self::class, 'handleSubscriptionUpdated']);
        $events->listen(SubscriptionCanceled::class, [self::class, 'handleSubscriptionCanceled']);
    }
}
