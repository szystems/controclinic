<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimits
{
    /**
     * Check if the clinic has an active subscription or valid free plan.
     * Suspended/cancelled clinics get redirected to billing page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clinic = app('current_clinic');

        if (! $clinic) {
            return $next($request);
        }

        // Free plan and active/trial status — always allowed
        if ($clinic->plan_type === 'free' && $clinic->isActive()) {
            return $next($request);
        }

        // Paid plan: check subscription is valid
        if ($clinic->plan_type !== 'free') {
            $subscription = $clinic->subscription();

            // Has a valid (active, on trial, or past due but grace) subscription
            if ($subscription && ($subscription->active() || $subscription->onTrial() || $subscription->pastDue())) {
                return $next($request);
            }

            // Subscription expired or cancelled — still allow access to billing page
            if ($request->routeIs('app.billing.*')) {
                return $next($request);
            }

            // Downgrade to free if no valid subscription
            $clinic->update(['plan_type' => 'free']);
            session()->flash('warning', __('billing.subscription_expired'));

            return redirect()->route('app.billing.index', $clinic->slug);
        }

        // Suspended clinic
        if ($clinic->status === 'suspended') {
            if ($request->routeIs('app.billing.*')) {
                return $next($request);
            }

            return redirect()->route('app.billing.index', $clinic->slug)
                ->with('error', __('billing.account_suspended'));
        }

        return $next($request);
    }
}
