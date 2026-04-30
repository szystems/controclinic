<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class NavigationDrawerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function bootstrapOwner(): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->create(['clinic_id' => $clinic->id, 'role' => 'owner']);
        $owner->assignRole('owner');
        $clinic->update(['owner_id' => $owner->id]);

        return [$clinic, $owner];
    }

    public function test_navigation_renders_mobile_drawer_with_grouped_sections(): void
    {
        [$clinic, $owner] = $this->bootstrapOwner();

        $response = $this->actingAs($owner)->get("/app/{$clinic->slug}");

        $response->assertOk();

        // Drawer contenedor + atributos clave
        $response->assertSee('id="mobile-drawer"', false);
        $response->assertSee('role="dialog"', false);
        $response->assertSee('aria-modal="true"', false);

        // Headers de grupo traducidos
        $response->assertSee(__('general.nav_main'));
        $response->assertSee(__('general.nav_team'));
        $response->assertSee(__('general.nav_account'));

        // Botones de abrir / cerrar el drawer (i18n)
        $response->assertSee(__('general.open_menu'));
        $response->assertSee(__('general.close_menu'));

        // Items principales presentes
        $response->assertSee(__('general.dashboard'));
        $response->assertSee(__('general.patients'));
        $response->assertSee(__('general.appointments'));
        $response->assertSee(__('general.calendar'));
        $response->assertSee(__('general.staff'));
        $response->assertSee(__('general.reports'));
    }

    public function test_navigation_hides_team_links_for_users_without_permission(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $assistant = User::factory()->create(['clinic_id' => $clinic->id, 'role' => 'assistant']);
        $assistant->assignRole('assistant');

        $response = $this->actingAs($assistant)->get("/app/{$clinic->slug}");

        $response->assertOk();
        $response->assertDontSee(__('general.nav_team'));
        // Items de equipo no deben aparecer como enlace de navegaci\u00f3n
        $response->assertDontSee(route('app.staff.index', $clinic->slug));
        $response->assertDontSee(route('app.reports', $clinic->slug));
    }
}
