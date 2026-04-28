<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $clinic = app('current_clinic');

        if ($clinic && ! $clinic->hasCompletedOnboarding()) {
            // Allow access to onboarding routes
            if ($request->routeIs('app.onboarding.*')) {
                return $next($request);
            }

            return redirect()->route('app.onboarding.index', $clinic->slug);
        }

        return $next($request);
    }
}
