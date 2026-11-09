<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformAvailable
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app_setting('features.maintenance_mode', false)) {
            return $next($request);
        }

        if ($this->canPass($request)) {
            return $next($request);
        }

        return response()
            ->view('maintenance', [], 503)
            ->header('Retry-After', '3600');
    }

    private function canPass(Request $request): bool
    {
        if ($request->is(
            'up',
            'health',
            'login',
            'logout',
            'forgot-password',
            'reset-password',
            'reset-password/*',
            'two-factor-challenge',
            'lang/*',
            'paddle',
            'paddle/*',
        )) {
            return true;
        }

        if ($request->is('livewire/update') && ! $request->user()) {
            return true;
        }

        return (bool) $request->user()?->is_super_admin;
    }
}
