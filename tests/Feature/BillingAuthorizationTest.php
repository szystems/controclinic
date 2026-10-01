<?php

namespace Tests\Feature;

use App\Livewire\App\Billing\Index;
use App\Models\Clinic;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class BillingAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_receptionist_cannot_open_billing_or_cancel_a_subscription(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id, 'role' => 'receptionist']);
        $user->assignRole('receptionist');

        Livewire::actingAs($user)
            ->test(Index::class, ['clinic' => $clinic])
            ->assertForbidden();
    }

    public function test_owner_can_open_billing(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->create(['clinic_id' => $clinic->id, 'role' => 'owner']);
        $owner->assignRole('owner');
        $clinic->update(['owner_id' => $owner->id]);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->assertOk();
    }

    public function test_promo_codes_are_rate_limited(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->create(['clinic_id' => $clinic->id, 'role' => 'owner']);
        $owner->assignRole('owner');
        $clinic->update(['owner_id' => $owner->id]);

        RateLimiter::clear('billing-promo:'.$clinic->id);

        $component = Livewire::actingAs($owner)->test(Index::class, ['clinic' => $clinic]);

        for ($i = 0; $i < 5; $i++) {
            $component->set('promoCode', 'NO-EXISTE')->call('redeemPromoCode');
        }

        $component->set('promoCode', 'NO-EXISTE')->call('redeemPromoCode')
            ->assertHasErrors('promoCode');
    }
}
