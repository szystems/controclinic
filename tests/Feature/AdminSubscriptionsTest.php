<?php

namespace Tests\Feature;

use App\Billing\LiveSubscriptions;
use App\Listeners\PaddleEventListener;
use App\Livewire\Admin\Clinics\Index as ClinicsIndex;
use App\Livewire\Admin\Clinics\Show as ClinicsShow;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Subscriptions\Index as SubscriptionsIndex;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Paddle\Events\SubscriptionCreated;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $clinic = Clinic::factory()->onboarded()->create([
            'is_manual_plan' => false,
            'plan_type' => 'free',
        ]);

        return User::factory()->create([
            'clinic_id' => $clinic->id,
            'is_super_admin' => true,
        ]);
    }

    private function subscribe(Clinic $clinic, string $paddleId, string $status = 'active', ?string $environment = 'live'): void
    {
        $clinic->subscriptions()->create([
            'type' => 'default',
            'paddle_id' => $paddleId,
            'status' => $status,
            'billing_environment' => $environment,
        ]);
    }

    public function test_dashboard_counts_only_live_subscriptions_and_payments(): void
    {
        $admin = $this->superAdmin();

        $live = Clinic::factory()->onboarded()->create(['name' => 'Clinica Viva', 'is_manual_plan' => false]);
        $old = Clinic::factory()->onboarded()->create(['name' => 'Clinica Vieja', 'is_manual_plan' => false]);
        Clinic::factory()->onboarded()->create([
            'name' => 'Clinica Cortesia',
            'is_manual_plan' => true,
            'plan_type' => 'clinica',
        ]);

        $this->subscribe($live, 'sub_live');
        $this->subscribe($old, 'sub_old', environment: null);

        $live->transactions()->create([
            'paddle_id' => 'txn_live',
            'paddle_subscription_id' => 'sub_live',
            'status' => 'completed',
            'total' => '1900',
            'tax' => '0',
            'currency' => 'USD',
            'billed_at' => now(),
        ]);
        $old->transactions()->create([
            'paddle_id' => 'txn_old',
            'paddle_subscription_id' => 'sub_old',
            'status' => 'completed',
            'total' => '9900',
            'tax' => '0',
            'currency' => 'USD',
            'billed_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertViewHas('activeSubscriptions', 1)
            ->assertViewHas('conversionRate', 25.0)
            ->assertViewHas('revenueThisMonth', function (array $revenue) {
                return $revenue['total'] === '19.00' && $revenue['count'] === 1;
            });
    }

    public function test_past_due_banner_links_live_failures_only(): void
    {
        $admin = $this->superAdmin();
        $live = Clinic::factory()->onboarded()->create(['is_manual_plan' => false]);
        $old = Clinic::factory()->onboarded()->create(['is_manual_plan' => false]);
        $this->subscribe($live, 'sub_due', 'past_due');
        $this->subscribe($old, 'sub_old_due', 'past_due', null);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertSee(__('admin.past_due_banner', ['count' => 1]), false);
    }

    public function test_clinic_list_labels_subscription_courtesy_and_previous_account(): void
    {
        $admin = $this->superAdmin();
        $live = Clinic::factory()->onboarded()->create(['name' => 'Cobro Real', 'is_manual_plan' => false]);
        $old = Clinic::factory()->onboarded()->create(['name' => 'Cuenta Vieja', 'is_manual_plan' => false, 'plan_type' => 'free']);
        $courtesy = Clinic::factory()->onboarded()->create(['name' => 'Plan Regalado', 'is_manual_plan' => true, 'plan_type' => 'solo']);
        $this->subscribe($live, 'sub_live_list');
        $this->subscribe($old, 'sub_old_list', environment: null);

        Livewire::actingAs($admin)
            ->test(ClinicsIndex::class)
            ->assertSee('Cobro Real')
            ->assertSee('Cuenta Vieja')
            ->assertSee('Plan Regalado')
            ->assertSee(__('admin.billing_subscription'))
            ->assertSee(__('admin.billing_previous'))
            ->assertSee(__('admin.billing_courtesy'))
            ->set('filterBilling', 'previous')
            ->assertSee('Cuenta Vieja')
            ->assertDontSee('Cobro Real')
            ->assertDontSee('Plan Regalado');
    }

    public function test_subscribers_page_hides_previous_accounts_until_asked(): void
    {
        $admin = $this->superAdmin();
        $live = Clinic::factory()->onboarded()->create(['name' => 'Suscriptor Real', 'is_manual_plan' => false]);
        $old = Clinic::factory()->onboarded()->create(['name' => 'Suscriptor Viejo', 'is_manual_plan' => false]);
        $this->subscribe($live, 'sub_page_live');
        $this->subscribe($old, 'sub_page_old', environment: null);

        $this->actingAs($admin)->get(route('admin.subscriptions.index'))->assertOk();

        Livewire::actingAs($admin)
            ->test(SubscriptionsIndex::class)
            ->assertSee('Suscriptor Real')
            ->assertDontSee('Suscriptor Viejo')
            ->set('account', 'previous')
            ->assertSee('Suscriptor Viejo')
            ->assertDontSee('Suscriptor Real');
    }

    public function test_regular_user_cannot_open_subscribers(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->create([
            'clinic_id' => $clinic->id,
            'is_super_admin' => false,
        ]);

        $this->actingAs($user)
            ->get(route('admin.subscriptions.index'))
            ->assertForbidden();
    }

    public function test_cancel_refuses_a_previous_account_without_calling_paddle(): void
    {
        $admin = $this->superAdmin();
        $clinic = Clinic::factory()->onboarded()->create(['is_manual_plan' => false]);
        $this->subscribe($clinic, 'sub_old_cancel', environment: null);

        Http::fake();

        Livewire::actingAs($admin)
            ->test(ClinicsShow::class, ['clinic' => $clinic])
            ->call('cancelSubscription');

        Http::assertNothingSent();
        $this->assertSame('active', $clinic->subscriptions()->first()->status);
        $this->assertNull($clinic->subscriptions()->first()->ends_at);
    }

    public function test_cancel_live_subscription_ends_at_period_end(): void
    {
        $admin = $this->superAdmin();
        $clinic = Clinic::factory()->onboarded()->create(['is_manual_plan' => false]);
        $this->subscribe($clinic, 'sub_live_cancel');

        config(['cashier.api_key' => 'test-key', 'cashier.sandbox' => false]);
        Http::fake([
            '*' => Http::response([
                'data' => [
                    'status' => 'active',
                    'scheduled_change' => [
                        'action' => 'cancel',
                        'effective_at' => '2026-12-01T00:00:00Z',
                    ],
                ],
            ]),
        ]);

        Livewire::actingAs($admin)
            ->test(ClinicsShow::class, ['clinic' => $clinic])
            ->call('cancelSubscription');

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && str_contains($request->url(), '/subscriptions/sub_live_cancel/cancel')
                && ($request->data()['effective_from'] ?? null) === 'next_billing_period';
        });

        $subscription = $clinic->subscriptions()->first();
        $this->assertNotNull($subscription->ends_at);
        $this->assertDatabaseHas('activity_log', [
            'description' => 'subscription_canceled',
            'subject_id' => $clinic->id,
        ]);
    }

    public function test_listener_marks_live_subscriptions_only_outside_sandbox(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $subscription = $clinic->subscriptions()->create([
            'type' => 'default',
            'paddle_id' => 'sub_marked',
            'status' => 'active',
        ]);
        $listener = new PaddleEventListener;

        config(['cashier.sandbox' => true]);
        $listener->handleSubscriptionCreated(new SubscriptionCreated($clinic, $subscription, []));
        $this->assertNull($subscription->fresh()->billing_environment);

        config(['cashier.sandbox' => false]);
        $listener->handleSubscriptionCreated(new SubscriptionCreated($clinic, $subscription->fresh(), []));
        $this->assertSame(LiveSubscriptions::ENVIRONMENT, $subscription->fresh()->billing_environment);
    }
}
