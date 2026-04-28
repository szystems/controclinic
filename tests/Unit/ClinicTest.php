<?php

namespace Tests\Unit;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicTest extends TestCase
{
    use RefreshDatabase;

    public function test_clinic_has_correct_plan_limits_for_free(): void
    {
        $clinic = Clinic::factory()->withPlan('free')->create();

        $limits = $clinic->getPlanLimits();

        $this->assertEquals(25, $limits['max_patients']);
        $this->assertEquals(5, $limits['max_appointments_per_month']);
        $this->assertEquals(1, $limits['max_doctors']);
        $this->assertEquals(0, $limits['max_staff']);
    }

    public function test_clinic_has_unlimited_limits_for_solo(): void
    {
        $clinic = Clinic::factory()->withPlan('solo')->create();

        $limits = $clinic->getPlanLimits();

        $this->assertNull($limits['max_patients']);
        $this->assertNull($limits['max_appointments_per_month']);
        $this->assertEquals(1, $limits['max_doctors']);
        $this->assertEquals(1, $limits['max_staff']);
    }

    public function test_clinic_has_unlimited_limits_for_enterprise(): void
    {
        $clinic = Clinic::factory()->withPlan('enterprise')->create();

        $limits = $clinic->getPlanLimits();

        $this->assertNull($limits['max_patients']);
        $this->assertNull($limits['max_appointments_per_month']);
        $this->assertNull($limits['max_doctors']);
        $this->assertNull($limits['max_staff']);
    }

    public function test_can_add_patient_respects_free_plan_limit(): void
    {
        $clinic = Clinic::factory()->withPlan('free')->onboarded()->create();

        // Free plan allows 25 patients
        Patient::factory()->count(24)->create(['clinic_id' => $clinic->id]);
        $this->assertTrue($clinic->canAddPatient());

        Patient::factory()->create(['clinic_id' => $clinic->id]);
        $this->assertFalse($clinic->canAddPatient()); // 25 reached
    }

    public function test_can_add_patient_unlimited_on_solo_plan(): void
    {
        $clinic = Clinic::factory()->withPlan('solo')->onboarded()->create();

        Patient::factory()->count(100)->create(['clinic_id' => $clinic->id]);
        $this->assertTrue($clinic->canAddPatient());
    }

    public function test_can_add_doctor_respects_plan_limit(): void
    {
        $clinic = Clinic::factory()->withPlan('free')->onboarded()->create();

        // Free plan allows 1 doctor
        $this->assertTrue($clinic->canAddDoctor());

        User::factory()->doctor()->create(['clinic_id' => $clinic->id]);
        $this->assertFalse($clinic->canAddDoctor());
    }

    public function test_can_add_doctor_group_plan_allows_five(): void
    {
        $clinic = Clinic::factory()->withPlan('group')->onboarded()->create();

        User::factory()->doctor()->count(4)->create(['clinic_id' => $clinic->id]);
        $this->assertTrue($clinic->canAddDoctor());

        User::factory()->doctor()->create(['clinic_id' => $clinic->id]);
        $this->assertFalse($clinic->canAddDoctor()); // 5 reached
    }

    public function test_has_feature_returns_correct_values(): void
    {
        $freeClinic = Clinic::factory()->withPlan('free')->create();
        $soloClinic = Clinic::factory()->withPlan('solo')->create();
        $groupClinic = Clinic::factory()->withPlan('group')->create();

        $this->assertTrue($freeClinic->hasFeature('basic_forms'));
        $this->assertFalse($freeClinic->hasFeature('ai'));

        $this->assertTrue($soloClinic->hasFeature('ai'));
        $this->assertTrue($soloClinic->hasFeature('custom_portal'));
        $this->assertFalse($soloClinic->hasFeature('api'));

        $this->assertTrue($groupClinic->hasFeature('audit_logs'));
        $this->assertTrue($groupClinic->hasFeature('multi_doctor_portal'));
    }

    public function test_is_on_trial_returns_correct_value(): void
    {
        $trialClinic = Clinic::factory()->trial()->create();
        $activeClinic = Clinic::factory()->create();
        $expiredTrialClinic = Clinic::factory()->create([
            'status' => 'trial',
            'trial_ends_at' => now()->subDay(),
        ]);

        $this->assertTrue($trialClinic->isOnTrial());
        $this->assertFalse($activeClinic->isOnTrial());
        $this->assertFalse($expiredTrialClinic->isOnTrial());
    }

    public function test_is_active_returns_correct_value(): void
    {
        $activeClinic = Clinic::factory()->create(['status' => 'active']);
        $trialClinic = Clinic::factory()->trial()->create();
        $suspendedClinic = Clinic::factory()->suspended()->create();
        $expiredTrialClinic = Clinic::factory()->create([
            'status' => 'trial',
            'trial_ends_at' => now()->subDay(),
        ]);

        $this->assertTrue($activeClinic->isActive());
        $this->assertTrue($trialClinic->isActive());
        $this->assertFalse($suspendedClinic->isActive());
        $this->assertFalse($expiredTrialClinic->isActive());
    }

    public function test_has_completed_onboarding(): void
    {
        $completed = Clinic::factory()->onboarded()->create();
        $notCompleted = Clinic::factory()->create();

        $this->assertTrue($completed->hasCompletedOnboarding());
        $this->assertFalse($notCompleted->hasCompletedOnboarding());
    }

    public function test_clinic_uses_slug_as_route_key(): void
    {
        $clinic = Clinic::factory()->create();

        $this->assertEquals('slug', $clinic->getRouteKeyName());
    }

    public function test_clinic_has_users_relationship(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        User::factory()->count(3)->create(['clinic_id' => $clinic->id]);

        $this->assertCount(3, $clinic->users);
    }

    public function test_clinic_doctors_scope_filters_correctly(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        User::factory()->doctor()->count(2)->create(['clinic_id' => $clinic->id]);
        User::factory()->assistant()->create(['clinic_id' => $clinic->id]);

        $this->assertCount(2, $clinic->doctors);
        $this->assertCount(1, $clinic->staff);
    }
}
