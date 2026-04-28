<?php

namespace Tests\Unit;

use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicAccessLevelTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_clinic_has_full_access(): void
    {
        $clinic = Clinic::factory()->create([
            'plan_type' => 'solo',
            'status' => 'active',
            'trial_ends_at' => null,
        ]);

        $this->assertSame(Clinic::ACCESS_FULL, $clinic->accessLevel());
        $this->assertTrue($clinic->canWrite());
        $this->assertFalse($clinic->isReadOnly());
        $this->assertFalse($clinic->isBillingOnly());
        $this->assertTrue($clinic->isAccessible());
    }

    public function test_trial_in_progress_has_full_access(): void
    {
        $clinic = Clinic::factory()->create([
            'plan_type' => 'solo',
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(7),
        ]);

        $this->assertSame(Clinic::ACCESS_FULL, $clinic->accessLevel());
        $this->assertTrue($clinic->canWrite());
        $this->assertTrue($clinic->isAccessible());
    }

    public function test_expired_trial_is_read_only(): void
    {
        $clinic = Clinic::factory()->create([
            'status' => 'trial',
            'trial_ends_at' => now()->subDay(),
        ]);

        $this->assertSame(Clinic::ACCESS_READ_ONLY, $clinic->accessLevel());
        $this->assertFalse($clinic->canWrite());
        $this->assertTrue($clinic->isReadOnly());
        $this->assertFalse($clinic->isBillingOnly());
        $this->assertTrue($clinic->isAccessible(), 'Expired trial must still see its data');
    }

    public function test_suspended_clinic_is_billing_only(): void
    {
        $clinic = Clinic::factory()->create([
            'status' => 'suspended',
        ]);

        $this->assertSame(Clinic::ACCESS_BILLING_ONLY, $clinic->accessLevel());
        $this->assertFalse($clinic->canWrite());
        $this->assertFalse($clinic->isReadOnly());
        $this->assertTrue($clinic->isBillingOnly());
        $this->assertFalse($clinic->isAccessible());
    }

    public function test_cancelled_clinic_is_billing_only(): void
    {
        $clinic = Clinic::factory()->create([
            'status' => 'cancelled',
        ]);

        $this->assertSame(Clinic::ACCESS_BILLING_ONLY, $clinic->accessLevel());
        $this->assertFalse($clinic->isAccessible());
    }

    public function test_free_courtesy_plan_has_full_access(): void
    {
        $clinic = Clinic::factory()->create([
            'status' => 'active',
            'trial_ends_at' => null,
            'plan_type' => 'free',
            'is_manual_plan' => true,
            'manual_plan_reason' => 'Cortesía partner',
        ]);

        $this->assertSame(Clinic::ACCESS_FULL, $clinic->accessLevel());
        $this->assertTrue($clinic->canWrite());
    }

    public function test_free_non_manual_plan_is_read_only(): void
    {
        $clinic = Clinic::factory()->create([
            'status' => 'active',
            'trial_ends_at' => null,
            'plan_type' => 'free',
            'is_manual_plan' => false,
        ]);

        $this->assertSame(Clinic::ACCESS_READ_ONLY, $clinic->accessLevel());
        $this->assertFalse($clinic->canWrite());
    }
}
