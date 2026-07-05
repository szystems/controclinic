<?php

namespace Tests\Feature;

use App\Livewire\Admin\Plans\Edit as PlansEdit;
use App\Livewire\Admin\Plans\Index as PlansIndex;
use App\Models\Clinic;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPlansManagementTest extends TestCase
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

    public function test_admin_plans_index_hides_inactive_by_default(): void
    {
        $admin = $this->createSuperAdmin();
        Plan::create(['name' => 'Active', 'slug' => 'active-plan', 'sort_order' => 1, 'is_active' => true]);
        Plan::create(['name' => 'Inactive', 'slug' => 'inactive-plan', 'sort_order' => 2, 'is_active' => false]);

        Livewire::actingAs($admin)
            ->test(PlansIndex::class)
            ->assertSee('Active')
            ->assertDontSee('Inactive');
    }

    public function test_admin_plans_index_shows_inactive_when_toggled(): void
    {
        $admin = $this->createSuperAdmin();
        Plan::create(['name' => 'Inactive', 'slug' => 'inactive-plan', 'sort_order' => 2, 'is_active' => false]);

        Livewire::actingAs($admin)
            ->test(PlansIndex::class)
            ->set('showInactive', true)
            ->assertSee('Inactive');
    }

    public function test_admin_can_delete_plan_with_no_clinics(): void
    {
        $admin = $this->createSuperAdmin();
        $plan = Plan::create(['name' => 'Legacy', 'slug' => 'legacy-plan', 'sort_order' => 99, 'is_active' => false]);

        Livewire::actingAs($admin)
            ->test(PlansIndex::class)
            ->set('showInactive', true)
            ->call('deletePlan', $plan->id)
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('plans', ['id' => $plan->id]);
    }

    public function test_admin_cannot_delete_free_plan(): void
    {
        $admin = $this->createSuperAdmin();
        $plan = Plan::create(['name' => 'Free', 'slug' => 'free', 'sort_order' => 0, 'is_active' => true, 'is_free' => true]);

        Livewire::actingAs($admin)
            ->test(PlansIndex::class)
            ->call('deletePlan', $plan->id)
            ->assertSessionHas('error');

        $this->assertDatabaseHas('plans', ['id' => $plan->id]);
    }

    public function test_admin_cannot_delete_plan_with_clinics(): void
    {
        $admin = $this->createSuperAdmin();
        $plan = Plan::create(['name' => 'Solo', 'slug' => 'solo', 'sort_order' => 1, 'is_active' => true]);
        Clinic::factory()->onboarded()->create(['plan_id' => $plan->id, 'plan_type' => 'solo']);

        Livewire::actingAs($admin)
            ->test(PlansIndex::class)
            ->call('deletePlan', $plan->id)
            ->assertSessionHas('error');

        $this->assertDatabaseHas('plans', ['id' => $plan->id]);
    }

    public function test_admin_plans_edit_can_delete_plan(): void
    {
        $admin = $this->createSuperAdmin();
        $plan = Plan::create(['name' => 'Legacy', 'slug' => 'legacy-plan', 'sort_order' => 99, 'is_active' => false]);

        Livewire::actingAs($admin)
            ->test(PlansEdit::class, ['plan' => $plan])
            ->call('deletePlan')
            ->assertRedirect(route('admin.plans.index'));

        $this->assertDatabaseMissing('plans', ['id' => $plan->id]);
    }
}
