<?php

namespace Tests\Feature;

use App\Livewire\App\GlobalSearch;
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

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @return array{0: Clinic, 1: User} */
    private function makeContext(string $role = 'doctor'): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id]);
        $user->assignRole($role);

        return [$clinic, $user];
    }

    private function bindClinic(Clinic $clinic): void
    {
        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);
    }

    public function test_global_search_renders_on_app_pages(): void
    {
        [$clinic, $user] = $this->makeContext();
        $this->bindClinic($clinic);

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/")
            ->assertOk()
            ->assertSeeLivewire(GlobalSearch::class);
    }

    public function test_short_query_returns_empty_results(): void
    {
        [$clinic, $user] = $this->makeContext();
        $this->bindClinic($clinic);

        Livewire::actingAs($user)
            ->test(GlobalSearch::class, ['clinic' => $clinic])
            ->set('query', 'a')
            ->assertViewHas('results', []);
    }

    public function test_search_finds_patient_by_name(): void
    {
        [$clinic, $user] = $this->makeContext();
        Patient::factory()->create([
            'clinic_id' => $clinic->id,
            'first_name' => 'Juanita',
            'last_name' => 'Perez',
        ]);
        $this->bindClinic($clinic);

        Livewire::actingAs($user)
            ->test(GlobalSearch::class, ['clinic' => $clinic])
            ->set('query', 'Juanita')
            ->assertSee('Juanita Perez');
    }

    public function test_search_finds_patient_by_email(): void
    {
        [$clinic, $user] = $this->makeContext();
        Patient::factory()->create([
            'clinic_id' => $clinic->id,
            'email' => 'unique.test.email@example.com',
            'first_name' => 'EmailUser',
        ]);
        $this->bindClinic($clinic);

        Livewire::actingAs($user)
            ->test(GlobalSearch::class, ['clinic' => $clinic])
            ->set('query', 'unique.test.email')
            ->assertSee('EmailUser');
    }

    public function test_search_finds_appointment_by_patient_name(): void
    {
        [$clinic, $user] = $this->makeContext();
        $patient = Patient::factory()->create([
            'clinic_id' => $clinic->id,
            'first_name' => 'Roberto',
            'last_name' => 'Torres',
        ]);
        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $user->id,
        ]);
        $this->bindClinic($clinic);

        Livewire::actingAs($user)
            ->test(GlobalSearch::class, ['clinic' => $clinic])
            ->set('query', 'Roberto')
            ->assertSee('Roberto Torres');
    }

    public function test_search_does_not_leak_across_tenants(): void
    {
        [$clinic1, $user1] = $this->makeContext();
        [$clinic2] = $this->makeContext();

        // first_name is used as the search query (appears in input value)
        // last_name would only appear in results — we assert it's NOT shown
        Patient::factory()->create([
            'clinic_id' => $clinic2->id,
            'first_name' => 'CrossTenantFirst',
            'last_name' => 'CrossTenantLast',
        ]);
        $this->bindClinic($clinic1);

        Livewire::actingAs($user1)
            ->test(GlobalSearch::class, ['clinic' => $clinic1])
            ->set('query', 'CrossTenantFirst')
            ->assertDontSee('CrossTenantLast'); // last_name only appears in results, not in input
    }

    public function test_search_finds_medical_record_by_title(): void
    {
        [$clinic, $user] = $this->makeContext();
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        MedicalRecord::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $user->id,
            'title' => 'ConsultaXYZUnica',
            'is_confidential' => false,
        ]);
        $this->bindClinic($clinic);

        Livewire::actingAs($user)
            ->test(GlobalSearch::class, ['clinic' => $clinic])
            ->set('query', 'ConsultaXYZUnica')
            ->assertSee('ConsultaXYZUnica');
    }
}
