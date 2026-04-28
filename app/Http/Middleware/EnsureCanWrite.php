<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloquea operaciones de escritura cuando la clínica está en modo lectura
 * (trial expirado o suscripción inactiva). Ver ADR-008.
 *
 * Debe ejecutarse DESPUÉS de TenantMiddleware (para que `current_clinic` esté resuelta).
 */
class EnsureCanWrite
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->bound('current_clinic')) {
            return $next($request);
        }

        $clinic = app('current_clinic');

        if ($clinic->canWrite()) {
            return $next($request);
        }

        // Read-only: redirigir a billing con mensaje
        return redirect()
            ->route('app.billing.index', $clinic->slug)
            ->with('warning', __('billing.account_read_only'));
    }
}
