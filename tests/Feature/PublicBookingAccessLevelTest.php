<?php

namespace Tests\Feature;

use App\Livewire\Public\Booking;
use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicBookingAccessLevelTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_access_clinic_shows_booking_wizard(): void
    {
        $clinic = Clinic::factory()->create([
            'plan_type' => 'solo',
            'is_manual_plan' => true,
            'status' => 'active',
            'public_portal_enabled' => true,
            'public_portal_slug' => 'demo-full',
        ]);

        Livewire::test(Booking::class, ['clinic' => $clinic])
            ->assertSet('portalDisabled', false);
    }

    public function test_expired_trial_disables_public_portal(): void
    {
        $clinic = Clinic::factory()->create([
            'plan_type' => 'solo',
            'is_manual_plan' => false,
            'status' => 'trial',
            'trial_ends_at' => now()->subDay(),
            'public_portal_enabled' => true,
            'public_portal_slug' => 'demo-trial',
        ]);

        Livewire::test(Booking::class, ['clinic' => $clinic])
            ->assertSet('portalDisabled', true)
            ->assertSee(__('booking.booking_unavailable'));
    }

    public function test_billing_only_clinic_disables_public_portal(): void
    {
        $clinic = Clinic::factory()->create([
            'plan_type' => 'solo',
            'is_manual_plan' => true,
            'status' => 'suspended',
            'public_portal_enabled' => true,
            'public_portal_slug' => 'demo-susp',
        ]);

        Livewire::test(Booking::class, ['clinic' => $clinic])
            ->assertSet('portalDisabled', true);
    }
}
