<?php

namespace Tests\Unit;

use App\Models\Clinic;
use App\Services\ClinicLocaleResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ClinicLocaleResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolves_united_states_from_cloudflare_country_header(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('CF-IPCountry', 'US');
        $request->headers->set('Accept-Language', 'en-US,en;q=0.9');

        $resolved = app(ClinicLocaleResolver::class)->resolve($request, 'America/Los_Angeles');

        $this->assertSame('US', $resolved['country']);
        $this->assertSame('1', $resolved['phone_country_code']);
        $this->assertSame('America/Los_Angeles', $resolved['timezone']);
        $this->assertSame('USD', $resolved['currency']);
        $this->assertSame('en', $resolved['locale']);
    }

    public function test_resolves_canada_with_browser_timezone(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('CF-IPCountry', 'CA');
        $request->headers->set('Accept-Language', 'fr-CA,fr;q=0.9,en;q=0.8');

        $resolved = app(ClinicLocaleResolver::class)->resolve($request, 'America/Vancouver');

        $this->assertSame('CA', $resolved['country']);
        $this->assertSame('CAD', $resolved['currency']);
        $this->assertSame('America/Vancouver', $resolved['timezone']);
    }

    public function test_falls_back_to_platform_defaults_without_geo_headers(): void
    {
        $resolved = app(ClinicLocaleResolver::class)->resolve(Request::create('/', 'GET'));

        $this->assertSame('US', $resolved['country']);
        $this->assertSame('America/New_York', $resolved['timezone']);
        $this->assertSame('USD', $resolved['currency']);
    }

    public function test_detects_legacy_clinic_defaults(): void
    {
        $clinic = Clinic::factory()->create([
            'country' => 'GT',
            'timezone' => 'America/Guatemala',
            'onboarding_completed_at' => null,
        ]);

        $this->assertTrue(app(ClinicLocaleResolver::class)->usesLegacyDefaults($clinic));
    }

    public function test_does_not_treat_customized_clinic_as_legacy(): void
    {
        $clinic = Clinic::factory()->create([
            'country' => 'US',
            'timezone' => 'America/Chicago',
            'onboarding_completed_at' => null,
        ]);

        $this->assertFalse(app(ClinicLocaleResolver::class)->usesLegacyDefaults($clinic));
    }
}
