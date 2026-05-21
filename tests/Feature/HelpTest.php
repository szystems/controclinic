<?php

namespace Tests\Feature;

use App\Livewire\App\Help\Index;
use App\Livewire\App\Help\Show;
use App\Models\Clinic;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class HelpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function makeClinicWithOwner(): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->create(['clinic_id' => $clinic->id]);
        $clinic->update(['owner_id' => $owner->id]);
        $owner->assignRole('owner');

        return [$clinic, $owner];
    }

    #[Test]
    public function help_index_renders_all_modules(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();
        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->withQueryParams(['clinic' => $clinic->slug])
            ->test(Index::class)
            ->assertStatus(200)
            ->assertSee(__('help.title'))
            ->assertSee(__('help.search_placeholder'));
    }

    #[Test]
    public function help_show_renders_valid_module(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();
        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(Show::class, ['module' => 'patients'])
            ->assertStatus(200)
            ->assertSee(__('help.modules.patients.title'))
            ->assertSee(__('help.modules.patients.summary'));
    }

    #[Test]
    public function help_show_returns_404_for_unknown_module(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();
        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(Show::class, ['module' => 'nonexistent-module'])
            ->assertStatus(404);
    }

    #[Test]
    public function help_index_modules_list_is_complete(): void
    {
        $modules = Index::modules();

        $this->assertContains('patients', $modules);
        $this->assertContains('appointments', $modules);
        $this->assertContains('medical-records', $modules);
        $this->assertContains('invoices', $modules);
        $this->assertContains('prescriptions', $modules);
        $this->assertContains('staff', $modules);
        $this->assertContains('reports', $modules);
        $this->assertContains('schedule', $modules);
        $this->assertCount(8, $modules);
    }

    #[Test]
    public function authenticated_user_can_access_help_index_route(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();
        app()->instance('current_clinic', $clinic);

        $this->actingAs($owner)
            ->get(route('app.help.index', ['clinic' => $clinic->slug]))
            ->assertStatus(200);
    }

    #[Test]
    public function authenticated_user_can_access_help_show_route(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();
        app()->instance('current_clinic', $clinic);

        $this->actingAs($owner)
            ->get(route('app.help.show', ['clinic' => $clinic->slug, 'module' => 'appointments']))
            ->assertStatus(200);
    }

    #[Test]
    public function help_show_route_returns_404_for_unknown_module(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();
        app()->instance('current_clinic', $clinic);

        $this->actingAs($owner)
            ->get(route('app.help.show', ['clinic' => $clinic->slug, 'module' => 'unknown']))
            ->assertStatus(404);
    }
}
