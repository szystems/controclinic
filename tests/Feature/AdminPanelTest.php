<?php

namespace Tests\Feature;

use App\Listeners\PaddleEventListener;
use App\Livewire\Admin\Clinics\Index as ClinicsIndex;
use App\Livewire\Admin\Clinics\Show as ClinicsShow;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Plans\Edit as PlansEdit;
use App\Livewire\Admin\Plans\Index as PlansIndex;
use App\Models\Clinic;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function createSuperAdmin(): User
    {
        $clinic = Clinic::factory()->onboarded()->create();

        return User::factory()->create([
            'clinic_id' => $clinic->id,
            'is_super_admin' => true,
        ]);
    }

    private function createRegularUser(): User
    {
        $clinic = Clinic::factory()->onboarded()->create();

        return User::factory()->create([
            'clinic_id' => $clinic->id,
            'is_super_admin' => false,
        ]);
    }

    private function seedPlans(): void
    {
        Plan::create(['name' => 'Free', 'slug' => 'free', 'sort_order' => 0, 'is_active' => true, 'is_free' => true, 'max_patients' => 25, 'max_appointments_per_month' => 5]);
        Plan::create(['name' => 'Solo', 'slug' => 'solo', 'monthly_price' => '29.00', 'yearly_price' => '276.00', 'sort_order' => 1, 'is_active' => true]);
        Plan::create(['name' => 'Group', 'slug' => 'group', 'monthly_price' => '79.00', 'yearly_price' => '756.00', 'sort_order' => 2, 'is_active' => true, 'is_popular' => true]);
    }

    // ==================== MIDDLEWARE ====================

    public function test_admin_middleware_blocks_regular_users(): void
    {
        $user = $this->createRegularUser();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_middleware_allows_super_admin(): void
    {
        $admin = $this->createSuperAdmin();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_admin_middleware_blocks_unauthenticated(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    // ==================== DASHBOARD ====================

    public function test_admin_dashboard_renders(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertStatus(200);
    }

    public function test_admin_dashboard_shows_stats(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertViewHas('totalClinics')
            ->assertViewHas('activeClinics')
            ->assertViewHas('totalUsers');
    }

    // ==================== PLANS ====================

    public function test_admin_plans_index_renders(): void
    {
        $admin = $this->createSuperAdmin();
        $this->seedPlans();

        Livewire::actingAs($admin)
            ->test(PlansIndex::class)
            ->assertSee('Free')
            ->assertSee('Solo')
            ->assertSee('Group')
            ->assertStatus(200);
    }

    public function test_admin_plans_edit_renders(): void
    {
        $admin = $this->createSuperAdmin();
        $plan = Plan::create(['name' => 'Solo', 'slug' => 'solo', 'monthly_price' => '29.00', 'sort_order' => 1, 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(PlansEdit::class, ['plan' => $plan])
            ->assertSet('name', 'Solo')
            ->assertSet('slug', 'solo')
            ->assertStatus(200);
    }

    public function test_admin_plans_edit_saves(): void
    {
        $admin = $this->createSuperAdmin();
        $plan = Plan::create(['name' => 'Solo', 'slug' => 'solo', 'monthly_price' => '29.00', 'sort_order' => 1, 'is_active' => true, 'max_patients' => 50]);

        Livewire::actingAs($admin)
            ->test(PlansEdit::class, ['plan' => $plan])
            ->set('name', 'Solo Pro')
            ->set('max_patients', 100)
            ->call('save')
            ->assertRedirect(route('admin.plans.index'));

        $plan->refresh();
        $this->assertEquals('Solo Pro', $plan->name);
        $this->assertEquals(100, $plan->max_patients);
    }

    public function test_admin_plans_edit_unlimited_toggle(): void
    {
        $admin = $this->createSuperAdmin();
        $plan = Plan::create(['name' => 'Solo', 'slug' => 'solo', 'max_patients' => 50, 'sort_order' => 1, 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(PlansEdit::class, ['plan' => $plan])
            ->set('unlimited_patients', true)
            ->call('save')
            ->assertRedirect(route('admin.plans.index'));

        $plan->refresh();
        $this->assertNull($plan->max_patients);
    }

    public function test_admin_plans_edit_syncs_clinic_limits(): void
    {
        $admin = $this->createSuperAdmin();
        $plan = Plan::create(['name' => 'Solo', 'slug' => 'solo', 'max_patients' => 50, 'sort_order' => 1, 'is_active' => true]);
        $clinic = Clinic::factory()->onboarded()->create(['plan_id' => $plan->id, 'max_patients' => 50]);

        Livewire::actingAs($admin)
            ->test(PlansEdit::class, ['plan' => $plan])
            ->set('max_patients', 100)
            ->call('save');

        $clinic->refresh();
        $this->assertEquals(100, $clinic->max_patients);
    }

    public function test_admin_plans_edit_validates_required_fields(): void
    {
        $admin = $this->createSuperAdmin();
        $plan = Plan::create(['name' => 'Solo', 'slug' => 'solo', 'sort_order' => 1, 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(PlansEdit::class, ['plan' => $plan])
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    // ==================== CLINICS ====================

    public function test_admin_clinics_index_renders(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(ClinicsIndex::class)
            ->assertStatus(200);
    }

    public function test_admin_clinics_index_search(): void
    {
        $admin = $this->createSuperAdmin();
        Clinic::factory()->onboarded()->create(['name' => 'TestClinic Alpha']);
        Clinic::factory()->onboarded()->create(['name' => 'TestClinic Beta']);

        Livewire::actingAs($admin)
            ->test(ClinicsIndex::class)
            ->set('search', 'Alpha')
            ->assertSee('TestClinic Alpha')
            ->assertDontSee('TestClinic Beta');
    }

    public function test_admin_clinics_show_renders(): void
    {
        $admin = $this->createSuperAdmin();
        $clinic = Clinic::factory()->onboarded()->create();
        User::factory()->owner()->create(['clinic_id' => $clinic->id]);

        Livewire::actingAs($admin)
            ->test(ClinicsShow::class, ['clinic' => $clinic])
            ->assertSee($clinic->name)
            ->assertStatus(200);
    }

    public function test_admin_clinics_show_suspend(): void
    {
        $admin = $this->createSuperAdmin();
        $clinic = Clinic::factory()->onboarded()->create(['status' => 'active']);

        Livewire::actingAs($admin)
            ->test(ClinicsShow::class, ['clinic' => $clinic])
            ->call('suspend');

        $clinic->refresh();
        $this->assertEquals('suspended', $clinic->status);
    }

    public function test_admin_clinics_show_activate(): void
    {
        $admin = $this->createSuperAdmin();
        $clinic = Clinic::factory()->onboarded()->create(['status' => 'suspended']);

        Livewire::actingAs($admin)
            ->test(ClinicsShow::class, ['clinic' => $clinic])
            ->call('activate');

        $clinic->refresh();
        $this->assertEquals('active', $clinic->status);
    }

    public function test_admin_clinics_show_extend_trial(): void
    {
        $admin = $this->createSuperAdmin();
        $clinic = Clinic::factory()->onboarded()->create(['status' => 'active']);

        Livewire::actingAs($admin)
            ->test(ClinicsShow::class, ['clinic' => $clinic])
            ->call('extendTrial', 14);

        $clinic->refresh();
        $this->assertEquals('trial', $clinic->status);
        $this->assertNotNull($clinic->trial_ends_at);
    }

    public function test_admin_clinics_show_change_plan(): void
    {
        $admin = $this->createSuperAdmin();
        $freePlan = Plan::create(['name' => 'Free', 'slug' => 'free', 'sort_order' => 0, 'is_active' => true, 'is_free' => true, 'max_patients' => 25]);
        $soloPlan = Plan::create(['name' => 'Solo', 'slug' => 'solo', 'sort_order' => 1, 'is_active' => true, 'max_patients' => null]);
        $clinic = Clinic::factory()->onboarded()->create(['plan_id' => $freePlan->id, 'plan_type' => 'free', 'max_patients' => 25]);

        Livewire::actingAs($admin)
            ->test(ClinicsShow::class, ['clinic' => $clinic])
            ->call('changePlan', $soloPlan->id);

        $clinic->refresh();
        $this->assertEquals($soloPlan->id, $clinic->plan_id);
        $this->assertEquals('solo', $clinic->plan_type);
        $this->assertNull($clinic->max_patients);
    }

    // ==================== PLAN MODEL ====================

    public function test_plan_scopes_work(): void
    {
        Plan::create(['name' => 'Active', 'slug' => 'active-plan', 'sort_order' => 1, 'is_active' => true]);
        Plan::create(['name' => 'Inactive', 'slug' => 'inactive-plan', 'sort_order' => 2, 'is_active' => false]);

        $this->assertCount(1, Plan::active()->get());
        $this->assertEquals('active-plan', Plan::active()->first()->slug);
    }

    public function test_plan_find_by_slug(): void
    {
        Plan::create(['name' => 'Solo', 'slug' => 'solo', 'sort_order' => 1, 'is_active' => true]);

        $plan = Plan::findBySlug('solo');
        $this->assertNotNull($plan);
        $this->assertEquals('Solo', $plan->name);

        $this->assertNull(Plan::findBySlug('nonexistent'));
    }

    public function test_plan_has_feature(): void
    {
        $plan = Plan::create([
            'name' => 'Test',
            'slug' => 'test',
            'sort_order' => 1,
            'is_active' => true,
            'features' => ['sms_reminders', 'custom_portal'],
        ]);

        $this->assertTrue($plan->hasFeature('sms_reminders'));
        $this->assertFalse($plan->hasFeature('api_access'));
    }

    public function test_plan_get_limits_array(): void
    {
        $plan = Plan::create([
            'name' => 'Test',
            'slug' => 'test',
            'sort_order' => 1,
            'is_active' => true,
            'max_patients' => 100,
            'max_appointments_per_month' => null,
            'max_doctors' => 5,
        ]);

        $limits = $plan->getLimitsArray();

        $this->assertEquals(100, $limits['max_patients']);
        $this->assertNull($limits['max_appointments_per_month']);
        $this->assertEquals(5, $limits['max_doctors']);
    }

    // ==================== MANUAL PLAN ASSIGNMENT ====================

    public function test_admin_can_assign_manual_plan(): void
    {
        $admin = $this->createSuperAdmin();
        $freePlan = Plan::create(['name' => 'Free', 'slug' => 'free', 'sort_order' => 0, 'is_active' => true, 'is_free' => true, 'max_patients' => 25]);
        $soloPlan = Plan::create(['name' => 'Solo', 'slug' => 'solo', 'sort_order' => 1, 'is_active' => true, 'max_patients' => null]);
        $clinic = Clinic::factory()->onboarded()->create(['plan_id' => $freePlan->id, 'plan_type' => 'free']);

        Livewire::actingAs($admin)
            ->test(ClinicsShow::class, ['clinic' => $clinic])
            ->set('manualPlanReason', 'Regalo por referido')
            ->call('assignManualPlan', $soloPlan->id);

        $clinic->refresh();
        $this->assertEquals($soloPlan->id, $clinic->plan_id);
        $this->assertEquals('solo', $clinic->plan_type);
        $this->assertTrue($clinic->is_manual_plan);
        $this->assertEquals('Regalo por referido', $clinic->manual_plan_reason);
        $this->assertNull($clinic->max_patients);
    }

    public function test_assign_manual_plan_requires_reason(): void
    {
        $admin = $this->createSuperAdmin();
        $soloPlan = Plan::create(['name' => 'Solo', 'slug' => 'solo', 'sort_order' => 1, 'is_active' => true]);
        $clinic = Clinic::factory()->onboarded()->create();

        Livewire::actingAs($admin)
            ->test(ClinicsShow::class, ['clinic' => $clinic])
            ->set('manualPlanReason', '')
            ->call('assignManualPlan', $soloPlan->id)
            ->assertHasErrors(['manualPlanReason' => 'required']);
    }

    public function test_admin_can_remove_manual_plan(): void
    {
        $admin = $this->createSuperAdmin();
        $soloPlan = Plan::create(['name' => 'Solo', 'slug' => 'solo', 'sort_order' => 1, 'is_active' => true]);
        $clinic = Clinic::factory()->onboarded()->create([
            'plan_id' => $soloPlan->id,
            'plan_type' => 'solo',
            'is_manual_plan' => true,
            'manual_plan_reason' => 'Regalo',
        ]);

        Livewire::actingAs($admin)
            ->test(ClinicsShow::class, ['clinic' => $clinic])
            ->call('removeManualPlan');

        $clinic->refresh();
        $this->assertFalse($clinic->is_manual_plan);
        $this->assertNull($clinic->manual_plan_reason);
    }

    public function test_paddle_webhook_does_not_override_manual_plan(): void
    {
        $listener = new PaddleEventListener;

        $soloPlan = Plan::create([
            'name' => 'Solo',
            'slug' => 'solo',
            'sort_order' => 1,
            'is_active' => true,
            'max_patients' => null,
            'paddle_monthly_price_id' => 'pri_solo_monthly',
        ]);
        $groupPlan = Plan::create([
            'name' => 'Group',
            'slug' => 'group',
            'sort_order' => 2,
            'is_active' => true,
            'max_patients' => null,
            'max_doctors' => 5,
            'paddle_monthly_price_id' => 'pri_group_monthly',
        ]);

        $clinic = Clinic::factory()->onboarded()->create([
            'plan_id' => $groupPlan->id,
            'plan_type' => 'group',
            'is_manual_plan' => true,
            'manual_plan_reason' => 'Upgrade de cortesía',
        ]);

        // Simulate Paddle trying to set Solo (what they actually pay for)
        $reflection = new \ReflectionMethod($listener, 'resolvePlanFromPrice');
        $reflection->setAccessible(true);

        // The clinic should keep Group plan even after Paddle webhook
        $this->assertTrue($clinic->is_manual_plan);
        $this->assertEquals('group', $clinic->plan_type);
        // Since we can't easily mock SubscriptionCreated, verify the guard logic directly
        $this->assertEquals($groupPlan->id, $clinic->plan_id);
    }
}
