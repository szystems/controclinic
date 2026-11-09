<?php

namespace App\Billing;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Paddle\Subscription;
use Laravel\Paddle\Transaction;

class LiveSubscriptions
{
    public const ENVIRONMENT = 'live';

    /**
     * @return list<string>
     */
    public static function openStatuses(): array
    {
        return [
            Subscription::STATUS_ACTIVE,
            Subscription::STATUS_PAST_DUE,
            Subscription::STATUS_PAUSED,
            Subscription::STATUS_TRIALING,
        ];
    }

    public static function query(): Builder
    {
        return Subscription::query()->where('billing_environment', self::ENVIRONMENT);
    }

    /**
     * Mark a subscription as belonging to the current Paddle account.
     * Rows created while sandbox mode is on stay unmarked so old test
     * subscriptions are not treated as live charges.
     */
    public static function remember(Subscription $subscription): void
    {
        if (config('cashier.sandbox') || $subscription->billing_environment === self::ENVIRONMENT) {
            return;
        }

        $subscription->forceFill([
            'billing_environment' => self::ENVIRONMENT,
        ])->save();
    }

    public static function revenueTransactions(int $month, int $year): Builder
    {
        return Transaction::query()
            ->where('status', Transaction::STATUS_COMPLETED)
            ->whereMonth('billed_at', $month)
            ->whereYear('billed_at', $year)
            ->whereIn('paddle_subscription_id', static::query()->select('paddle_id'));
    }

    public static function payingClinicCount(): int
    {
        return Clinic::query()->whereHas('subscriptions', function (Builder $query) {
            $query->where('billing_environment', self::ENVIRONMENT)
                ->whereIn('status', [
                    Subscription::STATUS_ACTIVE,
                    Subscription::STATUS_PAST_DUE,
                ]);
        })->count();
    }

    public static function applyClinicFilter(Builder $query, string $filter): void
    {
        $live = function (Builder $subscriptions) {
            $subscriptions->where('billing_environment', self::ENVIRONMENT)
                ->whereIn('status', self::openStatuses());
        };

        if ($filter === 'subscription') {
            $query->whereHas('subscriptions', function (Builder $subscriptions) {
                $subscriptions->where('billing_environment', self::ENVIRONMENT)
                    ->whereIn('status', [
                        Subscription::STATUS_ACTIVE,
                        Subscription::STATUS_PAUSED,
                        Subscription::STATUS_TRIALING,
                    ]);
            });

            return;
        }

        if ($filter === 'past_due') {
            $query->whereHas('subscriptions', function (Builder $subscriptions) {
                $subscriptions->where('billing_environment', self::ENVIRONMENT)
                    ->where('status', Subscription::STATUS_PAST_DUE);
            });

            return;
        }

        if ($filter === 'courtesy') {
            $query->where('is_manual_plan', true)
                ->where(function (Builder $clinics) {
                    $clinics->whereHas('plan', fn (Builder $plan) => $plan->where('is_free', false))
                        ->orWhere(fn (Builder $withoutPlan) => $withoutPlan->whereNull('plan_id')->where('plan_type', '!=', 'free'));
                })
                ->whereDoesntHave('subscriptions', $live);

            return;
        }

        if ($filter === 'previous') {
            $query->whereHas('subscriptions', fn (Builder $subscriptions) => $subscriptions->whereNull('billing_environment'))
                ->whereDoesntHave('subscriptions', fn (Builder $subscriptions) => $subscriptions->where('billing_environment', self::ENVIRONMENT))
                ->where(function (Builder $clinics) {
                    $clinics->where('is_manual_plan', false)
                        ->orWhereHas('plan', fn (Builder $plan) => $plan->where('is_free', true))
                        ->orWhere(fn (Builder $withoutPlan) => $withoutPlan->whereNull('plan_id')->where('plan_type', 'free'));
                });

            return;
        }

        if ($filter === 'none') {
            $query->whereDoesntHave('subscriptions')
                ->where(function (Builder $clinics) {
                    $clinics->where('is_manual_plan', false)
                        ->orWhereHas('plan', fn (Builder $plan) => $plan->where('is_free', true))
                        ->orWhere(fn (Builder $withoutPlan) => $withoutPlan->whereNull('plan_id')->where('plan_type', 'free'));
                });
        }
    }
}
