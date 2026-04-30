<?php

return [
    'title' => 'Configuración de la Clínica',
    'subtitle' => 'Administra la configuración de tu clínica',

    // Tabs
    'tabs' => [
        'general' => 'Información General',
        'localization' => 'Localización',
        'appointments' => 'Citas',
        'notifications' => 'Notificaciones',
        'billing' => 'Facturación',
        'branding' => 'Marca',
    ],

    // Messages
    'general_saved' => 'Información general guardada correctamente',
    'localization_saved' => 'Configuración de localización guardada correctamente',
    'appointments_saved' => 'Configuración de citas guardada correctamente',
    'notifications_saved' => 'Configuración de notificaciones guardada correctamente',
    'billing_saved' => 'Información de facturación guardada correctamente',
    'branding_saved' => 'Configuración de marca guardada correctamente',
    'logo_removed' => 'Logo eliminado correctamente',
    'favicon_removed' => 'Favicon eliminado correctamente',
    'legal_saved' => 'Configuración legal guardada correctamente',
    'defaults_saved' => 'Valores por defecto guardados correctamente',
    'features_saved' => 'Funcionalidades actualizadas correctamente',

    // General
    'general' => [
        'title' => 'Información General',
        'description' => 'Información básica de tu clínica que aparecerá en documentos y comunicaciones',
        'name' => 'Nombre de la clínica',
        'email' => 'Email de contacto',
        'phone' => 'Teléfono',
        'website' => 'Sitio web',
        'address' => 'Dirección',
        'city' => 'Ciudad',
        'country' => 'País',
        'description_placeholder' => 'Breve descripción de los servicios que ofrece tu clínica...',
    ],

    // Localization
    'localization' => [
        'title' => 'Localización',
        'description' => 'Configura el idioma, zona horaria y formato de fechas',
        'language' => 'Idioma',
        'timezone' => 'Zona horaria',
        'currency' => 'Moneda',
        'date_format' => 'Formato de fecha',
        'time_format' => 'Formato de hora',
    ],

    // Appointments
    'appointments' => [
        'title' => 'Configuración de Citas',
        'description' => 'Configura cómo funcionan las citas en tu clínica',
        'working_hours' => 'Horario de Atención',
        'start_time' => 'Hora de apertura',
        'end_time' => 'Hora de cierre',
        'working_days' => 'Días laborables',
        'duration_settings' => 'Duración de Citas',
        'default_duration' => 'Duración predeterminada',
        'buffer_time' => 'Tiempo entre citas',
        'online_booking' => 'Reservas en Línea',
        'allow_online' => 'Permitir reservas en línea',
        'allow_online_desc' => 'Los pacientes podrán agendar citas desde el portal público',
        'require_confirmation' => 'Requiere confirmación',
        'require_confirmation_desc' => 'Las citas agendadas en línea requieren confirmación manual',
        'min_notice' => 'Anticipación mínima',
        'max_advance' => 'Máximo anticipación',
        'cancellation_notice' => 'Aviso de cancelación',
    ],

    // Notifications
    'notifications' => [
        'title' => 'Notificaciones',
        'description' => 'Configura las notificaciones automáticas a pacientes',
        'send_confirmations' => 'Enviar confirmaciones',
        'send_confirmations_desc' => 'Enviar email de confirmación cuando se agenda una cita',
        'send_reminders' => 'Enviar recordatorios',
        'send_reminders_desc' => 'Enviar recordatorio por email antes de la cita',
        'reminder_time' => 'Enviar recordatorio',
        'hours_before' => 'horas antes',
    ],

    // Billing
    'billing' => [
        'title' => 'Datos de Facturación',
        'description' => 'Información fiscal para facturas y documentos legales',
        'tax_id' => 'NIT / RFC / Identificación fiscal',
        'legal_name' => 'Razón social',
        'address' => 'Dirección fiscal',
    ],

    // Branding
    'branding' => [
        'title' => 'Personalización de Marca',
        'description' => 'Personaliza la apariencia de tu clínica en la plataforma',
        'logo' => 'Logo de la clínica',
        'remove_logo' => 'Eliminar logo',
        'primary_color' => 'Color primario',
        'secondary_color' => 'Color secundario',
        'preview' => 'Vista previa',
    ],
];
