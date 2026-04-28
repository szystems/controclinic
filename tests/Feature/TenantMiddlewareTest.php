<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_clinic_slug_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/app/nonexistent-clinic');

        $response->assertNotFound();
    }

    public function test_user_cannot_access_other_clinic(): void
    {
        $clinic1 = Clinic::factory()->onboarded()->create();
        $clinic2 = Clinic::factory()->onboarded()->create();

        $user = User::factory()->owner()->create(['clinic_id' => $clinic1->id]);

        $response = $this->actingAs($user)->get("/app/{$clinic2->slug}");

        $response->assertForbidden();
    }

    public function test_user_can_access_own_clinic(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->owner()->create(['clinic_id' => $clinic->id]);

        $response = $this->actingAs($user)->get("/app/{$clinic->slug}");

        $response->assertOk();
    }

    public function test_unauthenticated_user_redirects_to_login(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();

        $response = $this->get("/app/{$clinic->slug}");

        $response->assertRedirect('/login');
    }
}
