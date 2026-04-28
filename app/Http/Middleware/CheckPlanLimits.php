<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimits
{
    /**
     * Sincroniza plan_type según la suscripción Paddle. NO redirige.
     * - Si la cuenta es billing_only o read_only, los middlewares correspondientes
     *   (TenantMiddleware / EnsureCanWrite) se encargan de redirigir o bloquear.
     * - Plan pagado sin suscripción válida → downgrade silencioso a free
     *   (accessLevel() lo verá como read_only por ADR-010 + ADR-008).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clinic = app('current_clinic');

        if (! $clinic) {
            return $next($request);
        }

        // Plan pagado sin suscripción Paddle válida → downgrade a free
        if ($clinic->plan_type !== 'free' && ! $clinic->is_manual_plan) {
            $subscription = $clinic->subscription();
            $valid = $subscription && ($subscription->active() || $subscription->onTrial() || $subscription->pastDue());

            if (! $valid) {
                $clinic->update(['plan_type' => 'free']);

                if (! $request->session()->has('warning')) {
                    $request->session()->flash('warning', __('billing.subscription_expired'));
                }
            }
        }

        return $next($request);
    }
}
