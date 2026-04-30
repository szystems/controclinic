<?php

namespace Tests\Feature;

use App\Livewire\App\Staff\Edit as StaffEdit;
use App\Models\Clinic;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StaffCustomPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function bootstrapClinic(): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->create(['clinic_id' => $clinic->id, 'role' => 'owner']);
        $owner->assignRole('owner');
        $clinic->update(['owner_id' => $owner->id]);

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        return [$clinic, $owner];
    }

    public function test_owner_can_assign_extra_permission_to_assistant(): void
    {
        [$clinic, $owner] = $this->bootstrapClinic();
        $assistant = User::factory()->create(['clinic_id' => $clinic->id, 'role' => 'assistant']);
        $assistant->assignRole('assistant');

        $this->assertFalse($assistant->can('records.view'));

        Livewire::actingAs($owner)
            ->test(StaffEdit::class, ['user' => $assistant])
            ->set('extraPermissions', ['records.view'])
            ->call('save');

        $assistant->refresh();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->assertTrue($assistant->fresh()->can('records.view'));
        $this->assertContains('records.view', $assistant->fresh()->getDirectPermissions()->pluck('name')->all());
    }

    public function test_role_inherited_permissions_are_not_stored_as_direct(): void
    {
        [$clinic, $owner] = $this->bootstrapClinic();
        $doctor = User::factory()->create(['clinic_id' => $clinic->id, 'role' => 'doctor']);
        $doctor->assignRole('doctor');

        Livewire::actingAs($owner)
            ->test(StaffEdit::class, ['user' => $doctor])
            // El UI manda heredados + extras; el componente debe filtrar los heredados.
            ->set('extraPermissions', ['patients.view', 'patients.create', 'reports.export'])
            ->call('save');

        $direct = $doctor->fresh()->getDirectPermissions()->pluck('name')->all();

        $this->assertNotContains('patients.view', $direct);
        $this->assertNotContains('patients.create', $direct);
        $this->assertContains('reports.export', $direct);
    }

    public function test_extra_permissions_outside_catalog_are_ignored(): void
    {
        [$clinic, $owner] = $this->bootstrapClinic();
        $assistant = User::factory()->create(['clinic_id' => $clinic->id, 'role' => 'assistant']);
        $assistant->assignRole('assistant');

        Livewire::actingAs($owner)
            ->test(StaffEdit::class, ['user' => $assistant])
            ->set('extraPermissions', ['records.view', 'made.up.permission'])
            ->call('save');

        $direct = $assistant->fresh()->getDirectPermissions()->pluck('name')->all();
        $this->assertContains('records.view', $direct);
        $this->assertNotContains('made.up.permission', $direct);
    }

    public function test_removing_extra_permission_revokes_access(): void
    {
        [$clinic, $owner] = $this->bootstrapClinic();
        $assistant = User::factory()->create(['clinic_id' => $clinic->id, 'role' => 'assistant']);
        $assistant->assignRole('assistant');
        $assistant->givePermissionTo('records.view');

        $this->assertTrue($assistant->fresh()->can('records.view'));

        Livewire::actingAs($owner)
            ->test(StaffEdit::class, ['user' => $assistant])
            ->set('extraPermissions', [])
            ->call('save');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->assertFalse($assistant->fresh()->can('records.view'));
    }
}
