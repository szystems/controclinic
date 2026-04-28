<?php

namespace Database\Factories;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClinicFactory extends Factory
{
    protected $model = Clinic::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(4),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'country' => 'MX',
            'timezone' => 'America/Mexico_City',
            'currency' => 'MXN',
            'locale' => 'es',
            'plan_type' => 'solo',
            // Tests: marcar como manual para que CheckPlanLimits no haga downgrade
            // (no hay Paddle subscription real en suite de tests).
            'is_manual_plan' => true,
            'status' => 'active',
            'settings' => [],
            'branding' => [],
            'public_portal_enabled' => false,
            'max_patients' => 25,
            'max_appointments_per_month' => 5,
            'max_doctors' => 1,
            'max_staff' => 0,
            'max_storage_bytes' => 524288000,
        ];
    }

    public function onboarded(): static
    {
        return $this->state(fn () => [
            'onboarding_completed_at' => now(),
        ]);
    }

    public function trial(): static
    {
        return $this->state(fn () => [
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => [
            'status' => 'suspended',
        ]);
    }

    public function withPlan(string $plan): static
    {
        $limits = Clinic::PLAN_LIMITS[$plan] ?? Clinic::PLAN_LIMITS['free'];

        return $this->state(fn () => [
            'plan_type' => $plan,
            // Plan Free en factories legacy: marcar como cortesía admin para que tenga acceso full
            // (ADR-010 + ADR-008). Si un test necesita Free no-cortesía, usa $clinic->update(['is_manual_plan' => false]).
            'is_manual_plan' => $plan === 'free',
            'manual_plan_reason' => $plan === 'free' ? 'Test fixture' : null,
            'status' => 'active',
            'trial_ends_at' => null,
            'max_patients' => $limits['max_patients'],
            'max_appointments_per_month' => $limits['max_appointments_per_month'],
            'max_doctors' => $limits['max_doctors'],
            'max_staff' => $limits['max_staff'],
            'max_storage_bytes' => $limits['max_storage_bytes'],
        ]);
    }
}
