<?php

namespace App\Services;

use App\Models\Clinic;
use Illuminate\Http\Request;

class ClinicLocaleResolver
{
    /**
     * @return array{country: string, phone_country: string, phone_country_code: string, timezone: string, currency: string, locale: string}
     */
    public function resolve(?Request $request = null, ?string $browserTimezone = null): array
    {
        $request ??= request();

        $countryCode = $this->detectCountryCode($request);
        $profile = $this->countryProfile($countryCode);

        $locale = $this->detectLocale($request) ?? $profile['locale'];
        $timezone = $this->resolveTimezone($browserTimezone, $profile['timezone']);

        return [
            'country' => $countryCode,
            'phone_country' => $profile['phone_country'],
            'phone_country_code' => $profile['phone_country_code'],
            'timezone' => $timezone,
            'currency' => $profile['currency'],
            'locale' => $locale,
        ];
    }

    public function usesLegacyDefaults(Clinic $clinic): bool
    {
        if ($clinic->onboarding_completed_at) {
            return false;
        }

        $legacyTimezones = ['America/Guatemala'];
        $legacyCountries = [null, '', 'GT'];

        return in_array($clinic->timezone, $legacyTimezones, true)
            && in_array($clinic->country, $legacyCountries, true);
    }

    /**
     * @param  array{country: string, phone_country: string, phone_country_code: string, timezone: string, currency: string, locale: string}  $defaults
     */
    public function applyToClinic(Clinic $clinic, array $defaults): void
    {
        $settings = $clinic->settings ?? [];
        $settings['phone_country_code'] = $defaults['phone_country_code'];

        $clinic->fill([
            'country' => $defaults['country'],
            'timezone' => $defaults['timezone'],
            'currency' => $defaults['currency'],
            'locale' => $defaults['locale'],
            'settings' => $settings,
        ])->save();
    }

    public function isSupportedTimezone(string $timezone): bool
    {
        if (! in_array($timezone, timezone_identifiers_list(), true)) {
            return false;
        }

        $supported = collect(config('timezones.groups', []))
            ->flatten(1)
            ->keys()
            ->all();

        return in_array($timezone, $supported, true);
    }

    protected function detectCountryCode(?Request $request): string
    {
        $candidates = array_filter([
            $request?->header('CF-IPCountry'),
            $request?->server('HTTP_CF_IPCOUNTRY'),
        ]);

        foreach ($candidates as $code) {
            $code = strtoupper(trim((string) $code));
            if ($code !== '' && $code !== 'XX' && $code !== 'T1' && isset(config('clinic_locale.countries')[$code])) {
                return $code;
            }
        }

        return $this->platformFallback()['country'];
    }

    protected function detectLocale(?Request $request): ?string
    {
        $acceptLanguage = $request?->header('Accept-Language');
        if (! $acceptLanguage) {
            return null;
        }

        foreach (explode(',', $acceptLanguage) as $language) {
            $lang = strtolower(trim(explode(';', $language)[0]));
            if (in_array($lang, ['es', 'en'], true)) {
                return $lang;
            }

            $short = substr($lang, 0, 2);
            if (in_array($short, ['es', 'en'], true)) {
                return $short;
            }
        }

        return null;
    }

    protected function resolveTimezone(?string $browserTimezone, string $countryTimezone): string
    {
        if ($browserTimezone && $this->isSupportedTimezone($browserTimezone)) {
            return $browserTimezone;
        }

        return $countryTimezone;
    }

    /**
     * @return array{country: string, phone_country: string, phone_country_code: string, timezone: string, currency: string, locale: string}
     */
    protected function countryProfile(string $countryCode): array
    {
        $countries = config('clinic_locale.countries', []);
        $profile = $countries[$countryCode] ?? null;

        if (! $profile) {
            return $this->platformFallback();
        }

        return array_merge($this->platformFallback(), ['country' => $countryCode], $profile);
    }

    /**
     * @return array{country: string, phone_country: string, phone_country_code: string, timezone: string, currency: string, locale: string}
     */
    protected function platformFallback(): array
    {
        $configured = [
            'timezone' => app_setting('defaults.timezone'),
            'currency' => app_setting('defaults.currency'),
            'locale' => app_setting('defaults.locale'),
        ];

        $fallback = config('clinic_locale.fallback', []);

        return [
            'country' => $fallback['country'] ?? 'US',
            'phone_country' => $fallback['phone_country'] ?? 'US',
            'phone_country_code' => $fallback['phone_country_code'] ?? '1',
            'timezone' => $configured['timezone'] ?: ($fallback['timezone'] ?? 'America/New_York'),
            'currency' => $configured['currency'] ?: ($fallback['currency'] ?? 'USD'),
            'locale' => $configured['locale'] ?: ($fallback['locale'] ?? 'en'),
        ];
    }
}
