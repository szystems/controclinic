<?php

namespace Tests\Feature;

use App\Livewire\App\Reports\Index;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ReportsTest extends TestCase
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

        return [$clinic, $user];
    }

    // ==================== ACCESS CONTROL ====================

    public function test_owner_can_access_reports(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');

        $this->actingAs($owner)
            ->get("/app/{$clinic->slug}/reports")
            ->assertOk()
            ->assertSeeLivewire(Index::class);
    }

    public function test_doctor_can_access_reports(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithUser('doctor');

        $this->actingAs($doctor)
            ->get("/app/{$clinic->slug}/reports")
            ->assertOk();
    }

    public function test_admin_can_access_reports(): void
    {
        [$clinic, $admin] = $this->makeClinicWithUser('admin');

        $this->actingAs($admin)
            ->get("/app/{$clinic->slug}/reports")
            ->assertOk();
    }

    public function test_receptionist_cannot_access_reports(): void
    {
        [$clinic, $receptionist] = $this->makeClinicWithUser('receptionist');

        Livewire::actingAs($receptionist)
            ->test(Index::class, ['clinic' => $clinic])
            ->assertForbidden();
    }

    public function test_secretary_cannot_access_reports(): void
    {
        [$clinic, $secretary] = $this->makeClinicWithUser('secretary');

        Livewire::actingAs($secretary)
            ->test(Index::class, ['clinic' => $clinic])
            ->assertForbidden();
    }

    public function test_cross_tenant_access_is_forbidden(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        [$otherClinic] = $this->makeClinicWithUser('owner');

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $otherClinic])
            ->assertForbidden();
    }

    // ==================== SUMMARY STATS ====================

    public function test_summary_stats_reflect_appointments(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $today = now()->toDateString();

        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $owner->id,
            'appointment_date' => $today,
            'status' => 'completed',
        ]);

        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $owner->id,
            'appointment_date' => $today,
            'status' => 'cancelled',
        ]);

        // Use 'this_month' (default period) — today is within the current month
        $component = Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic]);

        $component->assertOk()
            ->assertViewHas('totalAppointments', 2)
            ->assertViewHas('completedAppointments', 1)
            ->assertViewHas('cancelledAppointments', 1);
    }

    public function test_stats_are_isolated_to_clinic(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        [$otherClinic, $otherOwner] = $this->makeClinicWithUser('owner');

        $today = now()->toDateString();

        // Create appointment for OTHER clinic
        Appointment::factory()->create([
            'clinic_id' => $otherClinic->id,
            'patient_id' => Patient::factory()->create(['clinic_id' => $otherClinic->id])->id,
            'doctor_id' => $otherOwner->id,
            'appointment_date' => $today,
            'status' => 'completed',
        ]);

        // Owner clinic should see 0 appointments
        $component = Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic]);

        $component->assertViewHas('totalAppointments', 0);
    }

    // ==================== FILTERS ====================

    public function test_status_filter_narrows_results(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $today = now()->toDateString();

        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $owner->id,
            'appointment_date' => $today,
            'status' => 'completed',
        ]);

        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $owner->id,
            'appointment_date' => $today,
            'status' => 'cancelled',
        ]);

        $component = Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->set('statusFilter', 'completed');

        $component->assertViewHas('totalAppointments', 1);
    }

    public function test_doctor_filter_narrows_results(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        $doctor = User::factory()->create(['clinic_id' => $clinic->id]);
        $doctor->assignRole('doctor');
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $today = now()->toDateString();

        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $owner->id,
            'appointment_date' => $today,
            'status' => 'completed',
        ]);

        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $today,
            'status' => 'completed',
        ]);

        $component = Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->set('doctorFilter', (string) $doctor->id);

        $component->assertViewHas('totalAppointments', 1);
    }

    // ==================== CSV EXPORT ====================

    public function test_owner_can_export_csv(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        Patient::factory()->create(['clinic_id' => $clinic->id]);

        $response = Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->call('exportCsv');

        // exportCsv dispatches a stream response — no exception = success
        $response->assertOk();
    }

    public function test_doctor_without_export_permission_cannot_export(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithUser('doctor');

        Livewire::actingAs($doctor)
            ->test(Index::class, ['clinic' => $clinic])
            ->call('exportCsv')
            ->assertForbidden();
    }

    // ==================== PERIOD PRESETS ====================

    public function test_period_change_updates_dates(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');

        $component = Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic]);

        $component->set('period', 'today');

        $this->assertEquals(now()->toDateString(), $component->get('dateFrom'));
        $this->assertEquals(now()->toDateString(), $component->get('dateTo'));
    }

    public function test_custom_period_does_not_auto_recalculate_dates(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');

        $component = Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic]);

        $component
            ->set('period', 'custom')
            ->set('dateFrom', '2025-01-01')
            ->set('dateTo', '2025-01-31');

        $this->assertEquals('2025-01-01', $component->get('dateFrom'));
        $this->assertEquals('2025-01-31', $component->get('dateTo'));
    }
}
