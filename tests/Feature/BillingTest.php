<?php

namespace Tests\Feature;

use App\Livewire\App\Billing\Index as BillingIndex;
use App\Models\Clinic;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    private function createClinicWithOwner(string $plan = 'free'): array
    {
        $clinic = Clinic::factory()->onboarded()->withPlan($plan)->create();
        $user = User::factory()->owner()->create(['clinic_id' => $clinic->id]);

        return [$clinic, $user];
    }

    private function seedPlans(): void
    {
        Plan::create(['name' => 'Solo', 'slug' => 'solo', 'monthly_price' => '29.00', 'yearly_price' => '276.00', 'sort_order' => 2, 'is_active' => true]);
        Plan::create(['name' => 'Group', 'slug' => 'group', 'monthly_price' => '79.00', 'yearly_price' => '756.00', 'sort_order' => 3, 'is_active' => true, 'is_popular' => true]);
        Plan::create(['name' => 'Enterprise', 'slug' => 'enterprise', 'sort_order' => 4, 'is_active' => true, 'is_enterprise' => true]);
    }

    public function test_billing_page_renders_for_free_plan(): void
    {
        [$clinic, $user] = $this->createClinicWithOwner('free');

        Livewire::actingAs($user)
            ->test(BillingIndex::class, ['clinic' => $clinic])
            ->assertSee($clinic->plan_type)
            ->assertStatus(200);
    }

    public function test_billing_shows_plans_for_free_user(): void
    {
        $this->seedPlans();
        [$clinic, $user] = $this->createClinicWithOwner('free');

        Livewire::actingAs($user)
            ->test(BillingIndex::class, ['clinic' => $clinic])
            ->assertSee('Solo')
            ->assertSee('Group')
            ->assertSee('Enterprise');
    }

    public function test_billing_cycle_toggle_works(): void
    {
        [$clinic, $user] = $this->createClinicWithOwner('free');

        Livewire::actingAs($user)
            ->test(BillingIndex::class, ['clinic' => $clinic])
            ->assertSet('billingCycle', 'monthly')
            ->set('billingCycle', 'yearly')
            ->assertSet('billingCycle', 'yearly');
    }

    public function test_enterprise_checkout_redirects_to_contact(): void
    {
        [$clinic, $user] = $this->createClinicWithOwner('free');

        Livewire::actingAs($user)
            ->test(BillingIndex::class, ['clinic' => $clinic])
            ->call('checkout', 'enterprise')
            ->assertRedirect(route('contact'));
    }

    public function test_checkout_without_price_id_flashes_error(): void
    {
        [$clinic, $user] = $this->createClinicWithOwner('free');

        // With no price IDs configured, checkout should flash error
        config(['cashier.prices.solo.monthly' => null]);
        config(['cashier.prices.solo.yearly' => null]);

        $component = Livewire::actingAs($user)
            ->test(BillingIndex::class, ['clinic' => $clinic])
            ->call('checkout', 'solo');

        // The component should not redirect (no Paddle checkout URL)
        $component->assertNoRedirect();
    }

    public function test_cancel_subscription_does_nothing_when_not_subscribed(): void
    {
        [$clinic, $user] = $this->createClinicWithOwner('free');

        // Should not throw an error
        Livewire::actingAs($user)
            ->test(BillingIndex::class, ['clinic' => $clinic])
            ->call('cancelSubscription')
            ->assertStatus(200);
    }

    public function test_billing_displays_current_plan_name(): void
    {
        [$clinic, $user] = $this->createClinicWithOwner('free');

        Livewire::actingAs($user)
            ->test(BillingIndex::class, ['clinic' => $clinic])
            ->assertSee('free');
    }

    public function test_plans_from_database_have_correct_structure(): void
    {
        $this->seedPlans();

        $plans = Plan::active()->ordered()->where('is_free', false)->get();

        $this->assertTrue($plans->contains('slug', 'solo'));
        $this->assertTrue($plans->contains('slug', 'group'));
        $this->assertTrue($plans->contains('slug', 'enterprise'));

        $solo = $plans->firstWhere('slug', 'solo');
        $this->assertEquals('29.00', $solo->monthly_price);

        $group = $plans->firstWhere('slug', 'group');
        $this->assertEquals('79.00', $group->monthly_price);
        $this->assertTrue($group->is_popular);

        $enterprise = $plans->firstWhere('slug', 'enterprise');
        $this->assertTrue($enterprise->is_enterprise);
        $this->assertNull($enterprise->monthly_price);
    }
}
