<?php

namespace App\Livewire\Admin\Settings;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    // --- Branding ---
    public string $branding_app_name = '';

    public string $branding_logo_url = '';

    public string $branding_primary_color = '';

    // --- Legal ---
    public string $legal_terms_url = '';

    public string $legal_privacy_url = '';

    public string $legal_support_email = '';

    // --- Defaults ---
    public string $defaults_locale = 'es';

    public string $defaults_timezone = 'America/Bogota';

    public string $defaults_currency = 'USD';

    // --- Feature flags ---
    public bool $features_portal_enabled = false;

    public bool $features_telemedicine_enabled = false;

    public bool $features_ai_enabled = false;

    public bool $features_registration_open = true;

    public bool $features_maintenance_mode = false;

    public function mount(): void
    {
        $settings = AppSetting::allCached();

        $this->branding_app_name = $settings['branding.app_name'] ?? 'ControClinic';
        $this->branding_logo_url = $settings['branding.logo_url'] ?? '';
        $this->branding_primary_color = $settings['branding.primary_color'] ?? '#2563eb';

        $this->legal_terms_url = $settings['legal.terms_url'] ?? '/terms';
        $this->legal_privacy_url = $settings['legal.privacy_url'] ?? '/privacy';
        $this->legal_support_email = $settings['legal.support_email'] ?? '';

        $this->defaults_locale = $settings['defaults.locale'] ?? 'es';
        $this->defaults_timezone = $settings['defaults.timezone'] ?? 'America/Bogota';
        $this->defaults_currency = $settings['defaults.currency'] ?? 'USD';

        $this->features_portal_enabled = (bool) ($settings['features.portal_enabled'] ?? false);
        $this->features_telemedicine_enabled = (bool) ($settings['features.telemedicine_enabled'] ?? false);
        $this->features_ai_enabled = (bool) ($settings['features.ai_enabled'] ?? false);
        $this->features_registration_open = (bool) ($settings['features.registration_open'] ?? true);
        $this->features_maintenance_mode = (bool) ($settings['features.maintenance_mode'] ?? false);
    }

    public function saveBranding(): void
    {
        $this->validate([
            'branding_app_name' => 'required|string|max:60',
            'branding_logo_url' => 'nullable|url|max:500',
            'branding_primary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $userId = Auth::id();

        AppSetting::set('branding.app_name', $this->branding_app_name, $userId);
        AppSetting::set('branding.logo_url', $this->branding_logo_url ?: null, $userId);
        AppSetting::set('branding.primary_color', $this->branding_primary_color, $userId);

        $this->dispatch('notify', type: 'success', message: __('settings.branding_saved'));
    }

    public function saveLegal(): void
    {
        $this->validate([
            'legal_terms_url' => 'required|string|max:500',
            'legal_privacy_url' => 'required|string|max:500',
            'legal_support_email' => 'required|email|max:255',
        ]);

        $userId = Auth::id();

        AppSetting::set('legal.terms_url', $this->legal_terms_url, $userId);
        AppSetting::set('legal.privacy_url', $this->legal_privacy_url, $userId);
        AppSetting::set('legal.support_email', $this->legal_support_email, $userId);

        $this->dispatch('notify', type: 'success', message: __('settings.legal_saved'));
    }

    public function saveDefaults(): void
    {
        $this->validate([
            'defaults_locale' => 'required|in:es,en',
            'defaults_timezone' => 'required|string|max:60',
            'defaults_currency' => 'required|string|size:3',
        ]);

        $userId = Auth::id();

        AppSetting::set('defaults.locale', $this->defaults_locale, $userId);
        AppSetting::set('defaults.timezone', $this->defaults_timezone, $userId);
        AppSetting::set('defaults.currency', $this->defaults_currency, $userId);

        $this->dispatch('notify', type: 'success', message: __('settings.defaults_saved'));
    }

    public function saveFeatures(): void
    {
        $userId = Auth::id();

        AppSetting::set('features.portal_enabled', $this->features_portal_enabled, $userId);
        AppSetting::set('features.telemedicine_enabled', $this->features_telemedicine_enabled, $userId);
        AppSetting::set('features.ai_enabled', $this->features_ai_enabled, $userId);
        AppSetting::set('features.registration_open', $this->features_registration_open, $userId);
        AppSetting::set('features.maintenance_mode', $this->features_maintenance_mode, $userId);

        $this->dispatch('notify', type: 'success', message: __('settings.features_saved'));
    }

    public function render()
    {
        return view('livewire.admin.settings.index')->layout('layouts.admin');
    }
}
