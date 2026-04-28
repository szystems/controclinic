<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     * Setea el locale según preferencia del usuario o detección automática.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->determineLocale($request);

        app()->setLocale($locale);

        return $next($request);
    }

    /**
     * Determina el locale a usar
     */
    protected function determineLocale(Request $request): string
    {
        // 1. Si hay un locale en la sesión (cambiado explícitamente por el usuario)
        if ($request->session()->has('locale')) {
            return $request->session()->get('locale');
        }

        // 2. Si el usuario está autenticado, usar su preferencia guardada
        if (auth()->check() && auth()->user()->locale) {
            return auth()->user()->locale;
        }

        // 3. Si hay una clínica en contexto, usar su locale
        if (app()->bound('current_clinic') && app('current_clinic')) {
            return app('current_clinic')->locale;
        }

        // 4. Detectar desde el header Accept-Language
        $browserLocale = $this->detectFromBrowser($request);
        if ($browserLocale) {
            return $browserLocale;
        }

        // 5. Default
        return config('app.locale', 'es');
    }

    /**
     * Detecta el locale desde los headers del browser
     */
    protected function detectFromBrowser(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');

        if (! $acceptLanguage) {
            return null;
        }

        $supported = config('laravellocalization.supportedLocales', []);
        $supportedKeys = array_keys($supported);

        // Parsear el header Accept-Language
        $languages = explode(',', $acceptLanguage);

        foreach ($languages as $language) {
            $parts = explode(';', trim($language));
            $lang = strtolower(trim($parts[0]));

            // Intentar match exacto
            if (in_array($lang, $supportedKeys)) {
                return $lang;
            }

            // Intentar match parcial (ej: es-MX -> es)
            $shortLang = substr($lang, 0, 2);
            if (in_array($shortLang, $supportedKeys)) {
                return $shortLang;
            }
        }

        return null;
    }
}
