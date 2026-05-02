<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user &&
            $user->two_factor_enabled &&
            $user->two_factor_confirmed_at !== null &&
            ! $request->session()->get('two_factor_verified', false)
        ) {
            // Store the intended URL so the challenge can redirect after verification
            if (! $request->is('two-factor-challenge') && ! $request->is('logout')) {
                $request->session()->put('url.intended', $request->url());
            }

            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
