<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureCanWriteTest extends TestCase
{
    use RefreshDatabase;

    protected function makeClinicAndUser(array $clinicState = []): array
    {
        $clinic = Clinic::factory()->onboarded()->create(array_merge([
            'plan_type' => 'solo',
            'status' => 'active',
        ], $clinicState));

        $user = User::factory()->owner()->create(['clinic_id' => $clinic->id]);

        return [$clinic, $user];
    }

    public function test_active_clinic_can_access_create_routes(): void
    {
        [$clinic, $user] = $this->makeClinicAndUser();

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/patients/create")
            ->assertOk();

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/appointments/create")
            ->assertOk();
    }

    public function test_expired_trial_redirects_create_routes_to_billing(): void
    {
        [$clinic, $user] = $this->makeClinicAndUser([
            'status' => 'trial',
            'trial_ends_at' => now()->subDay(),
        ]);

        $billingUrl = route('app.billing.index', $clinic->slug);

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/patients/create")
            ->assertRedirect($billingUrl);

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/appointments/create")
            ->assertRedirect($billingUrl);

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/staff/create")
            ->assertRedirect($billingUrl);

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/settings")
            ->assertRedirect($billingUrl);
    }

    public function test_expired_trial_can_still_read(): void
    {
        [$clinic, $user] = $this->makeClinicAndUser([
            'status' => 'trial',
            'trial_ends_at' => now()->subDay(),
        ]);

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/patients")
            ->assertOk();

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/appointments")
            ->assertOk();
    }

    public function test_expired_trial_can_access_billing(): void
    {
        [$clinic, $user] = $this->makeClinicAndUser([
            'status' => 'trial',
            'trial_ends_at' => now()->subDay(),
        ]);

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/billing")
            ->assertOk();
    }

    public function test_suspended_clinic_redirects_everything_to_billing(): void
    {
        [$clinic, $user] = $this->makeClinicAndUser(['status' => 'suspended']);

        $billingUrl = route('app.billing.index', $clinic->slug);

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}")
            ->assertRedirect($billingUrl);

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/patients")
            ->assertRedirect($billingUrl);

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/billing")
            ->assertOk();
    }
}
