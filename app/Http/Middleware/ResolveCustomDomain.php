<?php

namespace App\Http\Middleware;

use App\Models\Clinic;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ResolveCustomDomain
{
    /**
     * Si el Host header pertenece a un dominio custom verificado, sirve el portal
     * de la clínica correspondiente sin redirigir (la URL permanece en el dominio custom).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?? '';

        // Es el dominio principal de la app — no hacer nada
        if ($host === $appHost || str_ends_with($host, '.'.$appHost)) {
            return $next($request);
        }

        // Buscar clínica con este dominio custom verificado (caché 5 min)
        $clinic = Cache::remember("custom_domain:{$host}", 300, function () use ($host): ?Clinic {
            return Clinic::where('custom_domain', $host)
                ->whereNotNull('custom_domain_verified_at')
                ->first();
        });

        if (! $clinic) {
            return $next($request);
        }

        // Servir el portal de reservas de la clínica sin cambiar la URL del navegador.
        // Creamos un request interno hacia /c/{slug} con las mismas cookies/sesión.
        $internalRequest = Request::create(
            uri: '/c/'.$clinic->slug,
            method: 'GET',
            parameters: [],
            cookies: $request->cookies->all(),
            files: [],
            server: array_merge($request->server->all(), [
                'REQUEST_URI' => '/c/'.$clinic->slug,
                'PATH_INFO' => '/c/'.$clinic->slug,
            ]),
        );

        if ($request->hasSession()) {
            $internalRequest->setLaravelSession($request->session());
        }

        // Dispatch a través del router (sin volver a ejecutar global middleware)
        return app('router')->dispatch($internalRequest);
    }
}
