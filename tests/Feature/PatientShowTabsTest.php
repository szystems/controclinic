<?php

namespace Tests\Feature;

use App\Livewire\App\Patients\Show as PatientsShow;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PatientShowTabsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function makeClinicWithUser(string $role = 'owner'): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id]);
        $user->assignRole($role);

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        return [$clinic, $user];
    }

    private function makePatient(Clinic $clinic): Patient
    {
        return Patient::factory()->create(['clinic_id' => $clinic->id]);
    }

    // =========================================================
    // RENDERS
    // =========================================================

    /** @test */
    public function test_show_renders_datos_tab_by_default(): void
    {
        [$clinic, $user] = $this->makeClinicWithUser('doctor');
        $patient = $this->makePatient($clinic);

        Livewire::actingAs($user)
            ->test(PatientsShow::class, ['patient' => $patient])
            ->assertSet('tab', 'datos')
            ->assertSeeHtml('wire:click="setTab(\'datos\')"');
    }

    /** @test */
    public function test_show_displays_patient_name_in_header(): void
    {
        [$clinic, $user] = $this->makeClinicWithUser('doctor');
        $patient = $this->makePatient($clinic);

        Livewire::actingAs($user)
            ->test(PatientsShow::class, ['patient' => $patient])
            ->assertSee($patient->full_name);
    }

    // =========================================================
    // TAB SWITCHING
    // =========================================================

    /** @test */
    public function test_set_tab_changes_active_tab(): void
    {
        [$clinic, $user] = $this->makeClinicWithUser('owner');
        $patient = $this->makePatient($clinic);

        Livewire::actingAs($user)
            ->test(PatientsShow::class, ['patient' => $patient])
            ->assertSet('tab', 'datos')
            ->call('setTab', 'citas')
            ->assertSet('tab', 'citas')
            ->call('setTab', 'historial')
            ->assertSet('tab', 'historial');
    }

    /** @test */
    public function test_invalid_tab_is_ignored(): void
    {
        [$clinic, $user] = $this->makeClinicWithUser('owner');
        $patient = $this->makePatient($clinic);

        Livewire::actingAs($user)
            ->test(PatientsShow::class, ['patient' => $patient])
            ->call('setTab', 'hacker_tab')
            ->assertSet('tab', 'datos');
    }

    // =========================================================
    // URL PERSISTENCE (queryString)
    // =========================================================

    /** @test */
    public function test_tab_persisted_in_url_querystring(): void
    {
        [$clinic, $user] = $this->makeClinicWithUser('owner');
        $patient = $this->makePatient($clinic);

        // Component declares $queryString so switching tab updates URL param
        $component = Livewire::actingAs($user)
            ->test(PatientsShow::class, ['patient' => $patient])
            ->call('setTab', 'historial')
            ->assertSet('tab', 'historial');

        // queryString property should be synced
        $this->assertSame('historial', $component->get('tab'));
    }

    /** @test */
    public function test_tab_read_from_url_on_mount(): void
    {
        [$clinic, $user] = $this->makeClinicWithUser('owner');
        $patient = $this->makePatient($clinic);

        Livewire::actingAs($user)
            ->withQueryParams(['tab' => 'citas'])
            ->test(PatientsShow::class, ['patient' => $patient])
            ->assertSet('tab', 'citas');
    }

    /** @test */
    public function test_invalid_tab_in_url_falls_back_to_datos(): void
    {
        [$clinic, $user] = $this->makeClinicWithUser('owner');
        $patient = $this->makePatient($clinic);

        Livewire::actingAs($user)
            ->withQueryParams(['tab' => 'invalid'])
            ->test(PatientsShow::class, ['patient' => $patient])
            ->assertSet('tab', 'datos');
    }

    // =========================================================
    // TAB DATA — CITAS
    // =========================================================

    /** @test */
    public function test_citas_tab_loads_appointments(): void
    {
        [$clinic, $user] = $this->makeClinicWithUser('owner');
        $patient = $this->makePatient($clinic);
        $doctor = User::factory()->create(['clinic_id' => $clinic->id]);
        $doctor->assignRole('doctor');

        Appointment::factory()->create([
            'clinic_id'  => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id'  => $doctor->id,
        ]);

        $component = Livewire::actingAs($user)
            ->test(PatientsShow::class, ['patient' => $patient])
            ->call('setTab', 'citas')
            ->assertSet('tab', 'citas');

        $this->assertNotNull($component->get('allAppointments'));
    }

    /** @test */
    public function test_all_appointments_null_when_not_on_citas_tab(): void
    {
        [$clinic, $user] = $this->makeClinicWithUser('owner');
        $patient = $this->makePatient($clinic);

        $component = Livewire::actingAs($user)
            ->test(PatientsShow::class, ['patient' => $patient])
            ->assertSet('tab', 'datos');

        $this->assertNull($component->get('allAppointments'));
    }

    // =========================================================
    // TAB DATA — HISTORIAL
    // =========================================================

    /** @test */
    public function test_historial_tab_loads_records(): void
    {
        [$clinic, $user] = $this->makeClinicWithUser('owner');
        $patient = $this->makePatient($clinic);
        $doctor = User::factory()->create(['clinic_id' => $clinic->id]);
        $doctor->assignRole('doctor');

        MedicalRecord::factory()->create([
            'clinic_id'  => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id'  => $doctor->id,
        ]);

        $component = Livewire::actingAs($user)
            ->test(PatientsShow::class, ['patient' => $patient])
            ->call('setTab', 'historial')
            ->assertSet('tab', 'historial');

        $this->assertNotNull($component->get('allRecords'));
    }

    /** @test */
    public function test_all_records_null_when_not_on_historial_tab(): void
    {
        [$clinic, $user] = $this->makeClinicWithUser('owner');
        $patient = $this->makePatient($clinic);

        $component = Livewire::actingAs($user)
            ->test(PatientsShow::class, ['patient' => $patient])
            ->assertSet('tab', 'datos');

        $this->assertNull($component->get('allRecords'));
    }

    // =========================================================
    // ISOLATION: other clinic cannot see patient
    // =========================================================

    /** @test */
    public function test_patient_from_other_clinic_returns_404(): void
    {
        $clinic1 = Clinic::factory()->onboarded()->create();
        $user = User::factory()->create(['clinic_id' => $clinic1->id]);
        $user->assignRole('owner');

        $clinic2 = Clinic::factory()->onboarded()->create();
        $patient = Patient::factory()->create(['clinic_id' => $clinic2->id]);

        app()->instance('current_clinic', $clinic1);
        view()->share('currentClinic', $clinic1);

        $this->actingAs($user);

        Livewire::actingAs($user)
            ->test(PatientsShow::class, ['patient' => $patient])
            ->assertStatus(404);
    }
}
