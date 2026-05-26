<?php

namespace Tests\Feature;

use App\Livewire\App\Dashboard\SetupChecklist;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SetupChecklistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function makeOwner(): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->create(['clinic_id' => $clinic->id]);
        $owner->assignRole('owner');
        $clinic->update(['owner_id' => $owner->id]);

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        return [$clinic, $owner];
    }

    // ── Visibilidad ────────────────────────────────────────────────────────────

    public function test_checklist_renders_for_owner(): void
    {
        [$clinic, $owner] = $this->makeOwner();

        Livewire::actingAs($owner)
            ->test(SetupChecklist::class, ['clinic' => $clinic])
            ->assertOk();
    }

    public function test_checklist_hidden_for_doctor(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->create(['clinic_id' => $clinic->id]);
        $owner->assignRole('owner');
        $clinic->update(['owner_id' => $owner->id]);

        $doctor = User::factory()->create(['clinic_id' => $clinic->id]);
        $doctor->assignRole('doctor');

        app()->instance('current_clinic', $clinic);

        // El dashboard blade condiciona el render al rol owner; el doctor no debe verlo.
        $response = $this->actingAs($doctor)
            ->get(route('app.dashboard', $clinic->slug));

        $response->assertOk();
        $response->assertDontSee('app.dashboard.setup-checklist');
    }

    // ── Progreso de pasos ──────────────────────────────────────────────────────

    public function test_all_steps_incomplete_on_fresh_clinic(): void
    {
        [$clinic, $owner] = $this->makeOwner();

        $component = Livewire::actingAs($owner)
            ->test(SetupChecklist::class, ['clinic' => $clinic]);

        $component->assertSet('completedCount', 0);
        $status = $component->get('stepsStatus');
        $this->assertFalse($status['logo']);
        $this->assertFalse($status['schedule']);
        $this->assertFalse($status['patient']);
        $this->assertFalse($status['appointment']);
        $this->assertFalse($status['staff']);
        $this->assertFalse($status['public_page']);
    }

    public function test_patient_step_marks_done_when_patient_exists(): void
    {
        [$clinic, $owner] = $this->makeOwner();
        Patient::factory()->create(['clinic_id' => $clinic->id]);

        $component = Livewire::actingAs($owner)
            ->test(SetupChecklist::class, ['clinic' => $clinic]);

        $status = $component->get('stepsStatus');
        $this->assertTrue($status['patient']);
    }

    public function test_appointment_step_marks_done_when_appointment_exists(): void
    {
        [$clinic, $owner] = $this->makeOwner();
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $owner->id,
        ]);

        $component = Livewire::actingAs($owner)
            ->test(SetupChecklist::class, ['clinic' => $clinic]);

        $status = $component->get('stepsStatus');
        $this->assertTrue($status['appointment']);
    }

    public function test_staff_step_marks_done_when_second_user_exists(): void
    {
        [$clinic, $owner] = $this->makeOwner();
        $doctor = User::factory()->create(['clinic_id' => $clinic->id]);
        $doctor->assignRole('doctor');

        $component = Livewire::actingAs($owner)
            ->test(SetupChecklist::class, ['clinic' => $clinic]);

        $status = $component->get('stepsStatus');
        $this->assertTrue($status['staff']);
    }

    public function test_progress_percent_reflects_completed_steps(): void
    {
        [$clinic, $owner] = $this->makeOwner();
        // Completar 3 de 6 pasos: patient, appointment, staff
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $owner->id,
        ]);
        $extra = User::factory()->create(['clinic_id' => $clinic->id]);
        $extra->assignRole('doctor');

        $component = Livewire::actingAs($owner)
            ->test(SetupChecklist::class, ['clinic' => $clinic]);

        $this->assertEquals(3, $component->get('completedCount'));
        $this->assertEquals(50, $component->get('progressPercent'));
    }

    // ── Colapsar / expandir ────────────────────────────────────────────────────

    public function test_toggle_collapse_flips_state_and_persists(): void
    {
        [$clinic, $owner] = $this->makeOwner();

        $component = Livewire::actingAs($owner)
            ->test(SetupChecklist::class, ['clinic' => $clinic]);

        $component->assertSet('collapsed', false);

        $component->call('toggleCollapse');
        $component->assertSet('collapsed', true);

        $owner->refresh();
        $this->assertTrue((bool) ($owner->preferences['setup_checklist_collapsed'] ?? false));
    }

    public function test_toggle_collapse_twice_returns_to_expanded(): void
    {
        [$clinic, $owner] = $this->makeOwner();

        Livewire::actingAs($owner)
            ->test(SetupChecklist::class, ['clinic' => $clinic])
            ->call('toggleCollapse')
            ->call('toggleCollapse')
            ->assertSet('collapsed', false);
    }

    // ── Dismiss ────────────────────────────────────────────────────────────────

    public function test_dismiss_dispatches_event_and_persists_preference(): void
    {
        [$clinic, $owner] = $this->makeOwner();

        Livewire::actingAs($owner)
            ->test(SetupChecklist::class, ['clinic' => $clinic])
            ->call('dismiss')
            ->assertDispatched('setup-checklist-dismissed');

        $owner->refresh();
        $this->assertTrue((bool) ($owner->preferences['setup_checklist_dismissed'] ?? false));
    }

    public function test_dashboard_hides_checklist_after_dismiss(): void
    {
        [$clinic, $owner] = $this->makeOwner();
        $owner->update(['preferences' => ['setup_checklist_dismissed' => true]]);

        $response = $this->actingAs($owner)
            ->get(route('app.dashboard', $clinic->slug));

        $response->assertOk();
        // El bloque @livewire del checklist no debe estar en el HTML
        $response->assertDontSee('app.dashboard.setup-checklist');
    }
}
