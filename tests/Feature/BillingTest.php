<?php

namespace Tests\Feature;

use App\Livewire\App\Billing\Index as BillingIndex;
use App\Models\Clinic;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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

    public function test_private_plan_hidden_until_promo_code_redeemed(): void
    {
        Plan::create([
            'name' => 'Friendly',
            'slug' => 'friendly',
            'monthly_price' => '9.00',
            'sort_order' => 5,
            'is_active' => true,
            'is_private' => true,
            'requires_code' => true,
            'access_code' => 'CC-TEST',
        ]);

        [$clinic, $user] = $this->createClinicWithOwner('free');

        Livewire::actingAs($user)
            ->test(BillingIndex::class, ['clinic' => $clinic])
            ->assertDontSee('Friendly')
            ->set('promoCode', 'cc-test')
            ->call('redeemPromoCode')
            ->assertHasNoErrors()
            ->assertSee('Friendly');

        $this->assertTrue($clinic->fresh()->hasUnlockedPlan(Plan::where('slug', 'friendly')->first()));
    }

    public function test_invalid_promo_code_shows_error(): void
    {
        Plan::create([
            'name' => 'Friendly',
            'slug' => 'friendly',
            'sort_order' => 5,
            'is_active' => true,
            'is_private' => true,
            'requires_code' => true,
            'access_code' => 'CC-TEST',
        ]);

        [$clinic, $user] = $this->createClinicWithOwner('free');

        Livewire::actingAs($user)
            ->test(BillingIndex::class, ['clinic' => $clinic])
            ->set('promoCode', 'WRONG')
            ->call('redeemPromoCode')
            ->assertHasErrors(['promoCode']);
    }

    public function test_customer_portal_url_falls_back_to_billing_when_no_subscription(): void
    {
        [$clinic, $user] = $this->createClinicWithOwner('free');

        $this->assertSame(
            route('app.billing.index', ['clinic' => $clinic->slug]),
            $clinic->customerPortalUrl()
        );
    }

    public function test_resume_does_nothing_when_no_subscription(): void
    {
        [$clinic, $user] = $this->createClinicWithOwner('free');

        Livewire::actingAs($user)
            ->test(BillingIndex::class, ['clinic' => $clinic])
            ->call('resumeSubscription')
            ->assertStatus(200);
    }

    public function test_checkout_private_plan_without_unlock_flashes_error(): void
    {
        Plan::create([
            'name' => 'Friendly',
            'slug' => 'friendly',
            'monthly_price' => '9.00',
            'paddle_monthly_price_id' => 'pri_test',
            'sort_order' => 5,
            'is_active' => true,
            'is_private' => true,
            'requires_code' => true,
            'access_code' => 'CC-TEST',
        ]);

        [$clinic, $user] = $this->createClinicWithOwner('free');

        Livewire::actingAs($user)
            ->test(BillingIndex::class, ['clinic' => $clinic])
            ->call('checkout', 'friendly')
            ->assertNotDispatched('open-paddle-checkout')
            ->assertSessionHas('error', __('billing.plan_requires_code'));
    }

    public function test_change_plan_starts_a_new_checkout_when_paddle_does_not_know_the_subscription(): void
    {
        config([
            'cashier.api_key' => 'test_key',
            'cashier.client_side_token' => 'test_token',
            'cashier.sandbox' => true,
        ]);

        [$clinic, $user] = $this->createClinicWithOwner('practica');
        $clinic->update(['owner_id' => $user->id]);
        $clinic->customer()->create([
            'paddle_id' => 'ctm_oldsandbox',
            'name' => $user->name,
            'email' => $user->email,
        ]);
        $clinic->subscriptions()->create([
            'type' => 'default',
            'paddle_id' => 'sub_oldsandbox',
            'status' => 'active',
        ]);

        $clinica = Plan::findBySlug('clinica');

        Http::fake(function ($request) use ($user) {
            $method = $request->method();
            $url = $request->url();

            if ($method === 'PATCH' && str_contains($url, 'subscriptions/sub_oldsandbox')) {
                return Http::response([
                    'error' => [
                        'type' => 'request_error',
                        'code' => 'url_called_is_invalid',
                        'detail' => 'URL called is invalid.',
                    ],
                ], 404);
            }

            if ($method === 'GET' && str_contains($url, 'customers/ctm_oldsandbox')) {
                return Http::response([
                    'error' => [
                        'type' => 'request_error',
                        'code' => 'url_called_is_invalid',
                        'detail' => 'URL called is invalid.',
                    ],
                ], 404);
            }

            if ($method === 'GET' && str_contains($url, '/customers')) {
                return Http::response(['data' => []], 200);
            }

            if ($method === 'POST' && str_contains($url, '/customers')) {
                return Http::response([
                    'data' => [
                        'id' => 'ctm_current',
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ], 200);
            }

            if ($method === 'POST' && str_contains($url, '/transactions')) {
                return Http::response(['data' => ['id' => 'txn_restart']], 200);
            }

            return Http::response(['error' => ['detail' => 'unexpected']], 500);
        });

        Livewire::actingAs($user)
            ->test(BillingIndex::class, ['clinic' => $clinic])
            ->call('changePlan', 'clinica')
            ->assertDispatched('notify', type: 'info', message: __('billing.subscription_restart_checkout', [
                'plan' => $clinica->name,
            ]))
            ->assertDispatched('open-paddle-checkout', transactionId: 'txn_restart', planSlug: 'clinica')
            ->assertNotDispatched('notify', type: 'error', message: __('billing.change_plan_failed'));

        $this->assertSame('ctm_current', $clinic->customer()->first()->paddle_id);
        $this->assertSame('practica', $clinic->fresh()->plan_type);
    }

    public function test_subscription_reflects_matches_only_the_saved_plan(): void
    {
        [$clinic, $user] = $this->createClinicWithOwner('practica');

        Livewire::actingAs($user)
            ->test(BillingIndex::class, ['clinic' => $clinic])
            ->call('subscriptionReflects', 'clinica')
            ->assertReturned(false)
            ->call('subscriptionReflects', 'practica')
            ->assertReturned(true);
    }
}
