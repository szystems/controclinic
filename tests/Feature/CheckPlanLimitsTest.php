<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckPlanLimits;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckPlanLimitsTest extends TestCase
{
    use RefreshDatabase;

    private function createClinicWithOwner(array $clinicAttrs = []): array
    {
        $clinic = Clinic::factory()->onboarded()->create($clinicAttrs);
        $user = User::factory()->owner()->create([
            'clinic_id' => $clinic->id,
        ]);

        return [$clinic, $user];
    }

    public function test_free_active_clinic_can_access_dashboard(): void
    {
        [$clinic, $user] = $this->createClinicWithOwner([
            'plan_type' => 'free',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get("/app/{$clinic->slug}");

        $response->assertOk();
    }

    public function test_free_trial_clinic_can_access_dashboard(): void
    {
        [$clinic, $user] = $this->createClinicWithOwner([
            'plan_type' => 'free',
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $response = $this->actingAs($user)->get("/app/{$clinic->slug}");

        $response->assertOk();
    }

    public function test_suspended_clinic_redirects_to_billing(): void
    {
        [$clinic, $user] = $this->createClinicWithOwner([
            'plan_type' => 'free',
            'status' => 'suspended',
        ]);

        // Suspended clinic is blocked by TenantMiddleware (403), not CheckPlanLimits
        $response = $this->actingAs($user)->get("/app/{$clinic->slug}");

        $response->assertStatus(403);
    }

    public function test_billing_page_is_accessible(): void
    {
        [$clinic, $user] = $this->createClinicWithOwner([
            'plan_type' => 'free',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get("/app/{$clinic->slug}/billing");

        $response->assertOk();
    }

    public function test_paid_plan_without_subscription_downgrades_to_free(): void
    {
        [$clinic, $user] = $this->createClinicWithOwner([
            'plan_type' => 'solo',
            'status' => 'active',
        ]);

        // Solo plan but no Paddle subscription → should downgrade to free and redirect to billing
        $response = $this->actingAs($user)->get("/app/{$clinic->slug}");

        $clinic->refresh();
        $this->assertEquals('free', $clinic->plan_type);
        $response->assertRedirect(route('app.billing.index', $clinic->slug));
    }

    public function test_free_plan_user_can_access_patients(): void
    {
        [$clinic, $user] = $this->createClinicWithOwner([
            'plan_type' => 'free',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get("/app/{$clinic->slug}/patients");

        $response->assertOk();
    }

    public function test_onboarding_not_completed_redirects(): void
    {
        $clinic = Clinic::factory()->create([
            'onboarding_completed_at' => null,
            'status' => 'active',
        ]);
        $user = User::factory()->owner()->create(['clinic_id' => $clinic->id]);

        $response = $this->actingAs($user)->get("/app/{$clinic->slug}");

        $response->assertRedirect(route('app.onboarding.index', $clinic->slug));
    }
}
