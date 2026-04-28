<?php

namespace App\Http\Middleware;

use App\Models\Clinic;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     * Resuelve el tenant (clínica) desde la URL y lo setea globalmente.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Obtener la clínica desde la ruta (ya resuelta por route model binding) o subdomain
        $clinicParam = $request->route('clinic');

        // Si ya es un objeto Clinic (route model binding)
        if ($clinicParam instanceof Clinic) {
            $clinic = $clinicParam;
        } else {
            // Es un slug string, buscar la clínica
            $clinicSlug = $clinicParam ?? $this->getSubdomain($request);

            if (! $clinicSlug) {
                abort(404, 'Clínica no especificada');
            }

            $clinic = Clinic::where('slug', $clinicSlug)->first();

            if (! $clinic) {
                abort(404, 'Clínica no encontrada');
            }
        }

        // Verificar que la clínica está activa
        if (! $clinic->isActive()) {
            abort(403, 'Esta clínica no está activa. Contacte al administrador.');
        }

        // Si el usuario está autenticado, verificar que pertenece a esta clínica
        if (auth()->check() && auth()->user()->clinic_id !== $clinic->id) {
            abort(403, 'No tienes acceso a esta clínica');
        }

        // Setear el tenant context global
        app()->instance('current_clinic', $clinic);

        // Compartir con todas las vistas
        view()->share('currentClinic', $clinic);

        // Setear locale: prioriza sesión del usuario > config de clínica > config default
        $locale = session('locale', $clinic->locale ?? config('app.locale'));
        app()->setLocale($locale);
        config(['app.timezone' => $clinic->timezone ?? config('app.timezone')]);

        return $next($request);
    }

    /**
     * Extraer subdomain del request (para fase 2)
     */
    protected function getSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        $parts = explode('.', $host);

        // Si hay más de 2 partes (ej: clinica.controclinic.com)
        if (count($parts) > 2) {
            $subdomain = $parts[0];
            // Excluir subdomains reservados
            if (! in_array($subdomain, ['www', 'app', 'admin', 'api'])) {
                return $subdomain;
            }
        }

        return null;
    }
}
