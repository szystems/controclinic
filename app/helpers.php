<?php

use App\Models\AppSetting;

if (! function_exists('app_setting')) {
    /**
     * Obtiene un valor de configuración global de la plataforma con caché.
     *
     * Wrapper conveniente para usar en vistas Blade y código de aplicación.
     * El valor se almacena en la tabla `app_settings` y es editable desde
     * el panel /admin/settings (super admin).
     *
     * Ejemplos:
     *   app_setting('branding.app_name', 'ControClinic')
     *   app_setting('branding.primary_color', '#2563eb')
     *   app_setting('seo.meta_description')
     */
    function app_setting(string $key, mixed $default = null): mixed
    {
        return AppSetting::get($key, $default);
    }
}

if (! function_exists('registration_is_open')) {
    function registration_is_open(): bool
    {
        return (bool) app_setting('features.registration_open', true);
    }
}
