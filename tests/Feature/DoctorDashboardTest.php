<?php

namespace Tests\Feature;

use App\Livewire\App\Dashboard;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DoctorDashboardTest extends TestCase
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

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        return [$clinic, $user];
    }

    public function test_owner_dashboard_is_not_personalised(): void
    {
        [$clinic, $owner] = $this->makeContext('owner');

        $component = Livewire::actingAs($owner)->test(Dashboard::class, ['clinic' => $clinic]);

        $component->assertSet('isPersonalizedForDoctor', false);
    }

    public function test_doctor_dashboard_is_personalised(): void
    {
        [$clinic, $doctor] = $this->makeContext('doctor');

        $component = Livewire::actingAs($doctor)->test(Dashboard::class, ['clinic' => $clinic]);

        $component->assertSet('isPersonalizedForDoctor', true);
    }

    public function test_doctor_today_schedule_only_shows_own_appointments(): void
    {
        [$clinic, $doctorA] = $this->makeContext('doctor');

        $doctorB = User::factory()->create(['clinic_id' => $clinic->id]);
        $doctorB->assignRole('doctor');

        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $today = now()->toDateString();

        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctorA->id,
            'appointment_date' => $today,
            'start_time' => '09:00:00',
        ]);

        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctorB->id,
            'appointment_date' => $today,
            'start_time' => '10:00:00',
        ]);

        // Verify 2 appointments exist in DB for this clinic today
        $this->assertDatabaseCount('appointments', 2);

        $component = Livewire::actingAs($doctorA)->test(Dashboard::class, ['clinic' => $clinic]);

        // Doctor view is personalised so only doctorA's appointment should count
        $component->assertSet('todayAppointments', 1);
    }

    public function test_owner_today_schedule_shows_all_appointments(): void
    {
        [$clinic, $owner] = $this->makeContext('owner');

        $doctor = User::factory()->create(['clinic_id' => $clinic->id]);
        $doctor->assignRole('doctor');

        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $today = now()->toDateString();

        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $owner->id,
            'appointment_date' => $today,
        ]);

        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $today,
        ]);

        $component = Livewire::actingAs($owner)->test(Dashboard::class, ['clinic' => $clinic]);

        // Owner view is not personalised — all appointments should count
        $component->assertSet('todayAppointments', 2);
    }
}
