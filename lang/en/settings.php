<?php

return [
    'title' => 'Clinic Settings',
    'subtitle' => 'Manage your clinic settings',

    // Tabs
    'tabs' => [
        'general' => 'General Information',
        'localization' => 'Localization',
        'appointments' => 'Appointments',
        'notifications' => 'Notifications',
        'billing' => 'Billing',
        'branding' => 'Branding',
    ],

    // Messages
    'general_saved' => 'General information saved successfully',
    'localization_saved' => 'Localization settings saved successfully',
    'appointments_saved' => 'Appointment settings saved successfully',
    'notifications_saved' => 'Notification settings saved successfully',
    'billing_saved' => 'Billing information saved successfully',
    'branding_saved' => 'Branding settings saved successfully',
    'public_page_saved' => 'Public page updated successfully',
    'cover_removed' => 'Cover image removed',

    'public_page' => [
        'tab'                   => 'Public Page',
        'title'                 => 'Configure your public page',
        'subtitle'              => 'This information is visible to your patients on your public portal.',
        'cover_image'           => 'Cover image',
        'cover_hint'            => 'Recommended: 1200×400px. JPG or PNG. Max 4 MB.',
        'remove_cover'          => 'Remove cover image',
        'description'           => 'Clinic description',
        'description_placeholder' => 'Tell who you are, your specialty, years of experience, values…',
        'description_hint'      => 'This text will appear in the “About us” section. Max 3000 characters.',
        'services'              => 'Featured services',
        'no_services'           => 'No services added yet. Click "Add" to create the first one.',
        'service_title'         => 'Service name',
        'service_description'   => 'Short description (optional)',
        'show_doctors'          => 'Show medical team',
        'show_doctors_desc'     => 'Display your clinic’s doctors on the public page.',
        'seo_title_section'     => 'SEO',
        'seo_title'             => 'SEO title',
        'seo_description'       => 'Meta description',
    ],
    'logo_removed' => 'Logo removed successfully',
    'favicon_removed' => 'Favicon removed successfully',
    'legal_saved' => 'Legal settings saved successfully',
    'defaults_saved' => 'Default settings saved successfully',
    'features_saved' => 'Feature flags updated successfully',
    'seo_saved' => 'SEO settings saved successfully',

    // General
    'general' => [
        'title' => 'General Information',
        'description' => 'Basic clinic information that will appear on documents and communications',
        'name' => 'Clinic name',
        'email' => 'Contact email',
        'phone' => 'Phone',
        'website' => 'Website',
        'address' => 'Address',
        'city' => 'City',
        'country' => 'Country',
        'description_placeholder' => 'Brief description of the services your clinic offers...',
    ],

    // Localization
    'localization' => [
        'title' => 'Localization',
        'description' => 'Configure language, timezone and date formats',
        'language' => 'Language',
        'timezone' => 'Timezone',
        'currency' => 'Currency',
        'date_format' => 'Date format',
        'time_format' => 'Time format',
        'phone_country_code' => 'Default country code (phone)',
        'phone_country_code_hint' => 'International phone code used by default when registering new patients.',
    ],

    // Appointments
    'appointments' => [
        'title' => 'Appointment Settings',
        'description' => 'Configure how appointments work in your clinic',
        'working_hours' => 'Working Hours',
        'start_time' => 'Opening time',
        'end_time' => 'Closing time',
        'working_days' => 'Working days',
        'duration_settings' => 'Appointment Duration',
        'default_duration' => 'Default duration',
        'buffer_time' => 'Buffer between appointments',
        'online_booking' => 'Online Booking',
        'allow_online' => 'Allow online booking',
        'allow_online_desc' => 'Patients can schedule appointments from the public portal',
        'require_confirmation' => 'Require confirmation',
        'require_confirmation_desc' => 'Online appointments require manual confirmation',
        'min_notice' => 'Minimum notice',
        'max_advance' => 'Maximum advance booking',
        'cancellation_notice' => 'Cancellation notice',
    ],

    // Notifications
    'notifications' => [
        'title' => 'Notifications',
        'description' => 'Configure automatic notifications to patients',
        'send_confirmations' => 'Send confirmations',
        'send_confirmations_desc' => 'Send confirmation email when an appointment is scheduled',
        'send_reminders' => 'Send reminders',
        'send_reminders_desc' => 'Send email reminder before the appointment',
        'reminder_time' => 'Send reminder',
        'hours_before' => 'hours before',
    ],

    // Billing
    'billing' => [
        'title' => 'Billing Information',
        'description' => 'Tax information for invoices and legal documents',
        'billing_enabled' => 'Billing module',
        'billing_enabled_label' => 'Enable billing and consultation payments',
        'billing_enabled_hint' => 'Activates the invoicing and revenue module for your clinic. When disabled, no pricing or payment options are shown.',
        'tax_rate' => 'Default tax rate',
        'tax_rate_hint' => 'Applied to all invoices. Use 0 if you do not charge tax. Example: 16 for 16% VAT.',
        'tax_label' => 'Tax label',
        'tax_label_hint' => 'Name shown on invoices: VAT, Tax, GST, etc.',
        'tax_id' => 'Tax ID / VAT Number',
        'legal_name' => 'Legal name',
        'address' => 'Billing address',
    ],

    // Branding
    'branding' => [
        'title' => 'Brand Customization',
        'description' => 'Customize the appearance of your clinic on the platform',
        'logo' => 'Clinic logo',
        'remove_logo' => 'Remove logo',
        'primary_color' => 'Primary color',
        'secondary_color' => 'Secondary color',
        'preview' => 'Preview',
    ],

    'data' => [
        'tab' => 'Export data',
        'title' => 'Export clinic data',
        'subtitle' => 'Download all your clinic data as CSV files inside a ZIP archive.',
        'info' => 'The ZIP file will include the following CSV files with all your clinic information:',
        'file_patients' => 'patients.csv — full list of patients',
        'file_appointments' => 'appointments.csv — appointment history',
        'file_records' => 'records.csv — medical records summary',
        'file_staff' => 'staff.csv — team members',
        'export_btn' => 'Download ZIP with data',
        'export_hint' => 'Only the clinic owner can download clinic data. Generation may take a few seconds.',
    ],
];
