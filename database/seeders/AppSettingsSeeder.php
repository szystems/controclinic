<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingsSeeder extends Seeder
{
    /**
     * Valores iniciales de configuración global de la plataforma.
     * Todos son overridables desde el panel super admin (/admin/settings).
     */
    public function run(): void
    {
        $defaults = [
            // --- Branding ---
            [
                'group' => 'branding',
                'key' => 'branding.app_name',
                'value' => 'ControClinic',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Nombre de la plataforma mostrado en la UI',
            ],
            [
                'group' => 'branding',
                'key' => 'branding.logo_url',
                'value' => null,
                'type' => 'string',
                'is_public' => true,
                'description' => 'URL del logo principal (null = usa el logo SVG por defecto)',
            ],
            [
                'group' => 'branding',
                'key' => 'branding.favicon_url',
                'value' => null,
                'type' => 'string',
                'is_public' => true,
                'description' => 'URL del favicon/ícono de la plataforma (null = usa favicon.svg por defecto)',
            ],
            [
                'group' => 'branding',
                'key' => 'branding.primary_color',
                'value' => '#2563eb',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Color primario de la interfaz (hex)',
            ],
            // --- Legal ---
            [
                'group' => 'legal',
                'key' => 'legal.terms_url',
                'value' => '/terms',
                'type' => 'string',
                'is_public' => true,
                'description' => 'URL de los Términos y Condiciones',
            ],
            [
                'group' => 'legal',
                'key' => 'legal.privacy_url',
                'value' => '/privacy',
                'type' => 'string',
                'is_public' => true,
                'description' => 'URL de la Política de Privacidad',
            ],
            [
                'group' => 'legal',
                'key' => 'legal.support_email',
                'value' => 'soporte@controclinic.com',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Email de soporte visible a los usuarios',
            ],
            // --- Defaults ---
            [
                'group' => 'defaults',
                'key' => 'defaults.locale',
                'value' => 'es',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Idioma por defecto para nuevas clínicas (es|en)',
            ],
            [
                'group' => 'defaults',
                'key' => 'defaults.timezone',
                'value' => 'America/Bogota',
                'type' => 'string',
                'is_public' => false,
                'description' => 'Zona horaria por defecto para nuevas clínicas',
            ],
            [
                'group' => 'defaults',
                'key' => 'defaults.currency',
                'value' => 'USD',
                'type' => 'string',
                'is_public' => false,
                'description' => 'Moneda por defecto para facturación',
            ],
            // --- Feature flags ---
            [
                'group' => 'features',
                'key' => 'features.portal_enabled',
                'value' => false,
                'type' => 'boolean',
                'is_public' => false,
                'description' => 'Habilitar portal del paciente (en desarrollo)',
            ],
            [
                'group' => 'features',
                'key' => 'features.telemedicine_enabled',
                'value' => false,
                'type' => 'boolean',
                'is_public' => false,
                'description' => 'Habilitar videoconsulta (telemedicina)',
            ],
            [
                'group' => 'features',
                'key' => 'features.ai_enabled',
                'value' => false,
                'type' => 'boolean',
                'is_public' => false,
                'description' => 'Habilitar asistente IA (resúmenes de historial)',
            ],
            [
                'group' => 'features',
                'key' => 'features.registration_open',
                'value' => true,
                'type' => 'boolean',
                'is_public' => true,
                'description' => 'Permitir nuevos registros de clínicas',
            ],
            [
                'group' => 'features',
                'key' => 'features.maintenance_mode',
                'value' => false,
                'type' => 'boolean',
                'is_public' => false,
                'description' => 'Mostrar página de mantenimiento a usuarios normales',
            ],
            // --- SEO ---
            [
                'group' => 'seo',
                'key' => 'seo.meta_title',
                'value' => 'ControClinic — Software para Clínicas Médicas',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Título por defecto en meta tags y Open Graph',
            ],
            [
                'group' => 'seo',
                'key' => 'seo.meta_description',
                'value' => 'ControClinic es el software de gestión para clínicas médicas más fácil de usar. Agenda citas, gestiona pacientes y haz crecer tu práctica médica.',
                'type' => 'string',
                'is_public' => true,
                'description' => 'Descripción por defecto en meta tags (recomendado 150-160 caracteres)',
            ],
            [
                'group' => 'seo',
                'key' => 'seo.og_image_url',
                'value' => null,
                'type' => 'string',
                'is_public' => true,
                'description' => 'URL de imagen Open Graph (1200×630 px recomendado)',
            ],
            [
                'group' => 'seo',
                'key' => 'seo.google_analytics_id',
                'value' => null,
                'type' => 'string',
                'is_public' => true,
                'description' => 'ID de Google Analytics 4 (formato G-XXXXXXXXXX)',
            ],
            [
                'group' => 'seo',
                'key' => 'seo.gtm_id',
                'value' => null,
                'type' => 'string',
                'is_public' => true,
                'description' => 'ID de Google Tag Manager (formato GTM-XXXXXXX)',
            ],
        ];

        foreach ($defaults as $setting) {
            AppSetting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        AppSetting::clearCache();
    }
}
