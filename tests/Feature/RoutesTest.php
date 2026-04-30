<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutesTest extends TestCase
{
    use RefreshDatabase;

    // ==================== PUBLIC ROUTES ====================

    public function test_home_page_loads(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_pricing_page_loads(): void
    {
        $this->get('/pricing')->assertOk();
    }

    public function test_contact_page_loads(): void
    {
        $this->get('/contact')->assertOk();
    }

    public function test_terms_page_loads(): void
    {
        $this->get('/terms')->assertOk()->assertSee('Términos');
    }

    public function test_privacy_page_loads(): void
    {
        $this->get('/privacy')->assertOk()->assertSee('Privacidad');
    }

    // ==================== AUTH ROUTES ====================

    public function test_login_page_loads(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_register_page_loads(): void
    {
        $this->get('/register')->assertOk();
    }

    // ==================== APP ROUTES ====================

    public function test_dashboard_redirects_to_clinic(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->owner()->create(['clinic_id' => $clinic->id]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect("/app/{$clinic->slug}");
    }

    public function test_dashboard_without_clinic_redirects_to_register(): void
    {
        $user = User::factory()->create(['clinic_id' => null]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('register'));
    }

    public function test_super_admin_dashboard_redirects_to_admin(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $admin = User::factory()->owner()->create([
            'clinic_id' => $clinic->id,
            'is_super_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_billing_route_exists(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->owner()->create(['clinic_id' => $clinic->id]);

        $response = $this->actingAs($user)->get("/app/{$clinic->slug}/billing");

        $response->assertOk();
    }

    public function test_settings_route_exists(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->owner()->create(['clinic_id' => $clinic->id]);

        $response = $this->actingAs($user)->get("/app/{$clinic->slug}/settings");

        $response->assertOk();
    }

    public function test_patients_route_exists(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->owner()->create(['clinic_id' => $clinic->id]);

        $response = $this->actingAs($user)->get("/app/{$clinic->slug}/patients");

        $response->assertOk();
    }

    public function test_appointments_route_exists(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->owner()->create(['clinic_id' => $clinic->id]);

        $response = $this->actingAs($user)->get("/app/{$clinic->slug}/appointments");

        $response->assertOk();
    }

    public function test_onboarding_route_exists(): void
    {
        $clinic = Clinic::factory()->create(['onboarding_completed_at' => null]);
        $user = User::factory()->owner()->create(['clinic_id' => $clinic->id]);

        $response = $this->actingAs($user)->get("/app/{$clinic->slug}/onboarding");

        $response->assertOk();
    }

    // ==================== LANG SWITCH ====================

    public function test_language_switch_sets_session(): void
    {
        $response = $this->get('/lang/en');

        $response->assertRedirect();
    }

    public function test_invalid_language_does_not_set_session(): void
    {
        $response = $this->get('/lang/fr');

        $response->assertRedirect();
    }
}
