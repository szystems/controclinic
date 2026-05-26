<?php

namespace Tests\Feature;

use App\Livewire\App\Appointments\Index as AppointmentsIndex;
use App\Livewire\App\Invoices\Index as InvoicesIndex;
use App\Livewire\App\Patients\Index as PatientsIndex;
use App\Livewire\App\Prescriptions\Index as PrescriptionsIndex;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EmptyStatesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function makeContext(string $role = 'owner'): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id]);
        $user->assignRole($role);

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        return [$clinic, $user];
    }

    // ── Pacientes ──────────────────────────────────────────────────────────────

    public function test_patients_shows_empty_state_with_no_patients(): void
    {
        [$clinic, $user] = $this->makeContext('doctor');

        Livewire::actingAs($user)
            ->test(PatientsIndex::class, ['clinic' => $clinic])
            ->assertSee(__('patients.no_patients'));
    }

    public function test_patients_hides_empty_state_when_patient_exists(): void
    {
        [$clinic, $user] = $this->makeContext('doctor');
        Patient::factory()->create(['clinic_id' => $clinic->id]);

        Livewire::actingAs($user)
            ->test(PatientsIndex::class, ['clinic' => $clinic])
            ->assertDontSee(__('patients.no_patients'));
    }

    // ── Citas ──────────────────────────────────────────────────────────────────

    public function test_appointments_shows_empty_state_with_no_appointments(): void
    {
        [$clinic, $user] = $this->makeContext('doctor');

        Livewire::actingAs($user)
            ->test(AppointmentsIndex::class, ['clinic' => $clinic])
            ->assertSee(__('appointments.no_appointments'));
    }

    public function test_appointments_hides_empty_state_when_appointment_exists(): void
    {
        [$clinic, $user] = $this->makeContext('doctor');
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $user->id,
            'appointment_date' => today()->toDateString(),
        ]);

        Livewire::actingAs($user)
            ->test(AppointmentsIndex::class, ['clinic' => $clinic])
            ->assertDontSee(__('appointments.no_appointments'));
    }

    // ── Facturas ───────────────────────────────────────────────────────────────

    public function test_invoices_shows_empty_state_with_no_invoices(): void
    {
        [$clinic, $user] = $this->makeContext('owner');
        $clinic->update(['settings' => ['billing_enabled' => true]]);

        Livewire::actingAs($user)
            ->test(InvoicesIndex::class, ['clinic' => $clinic->fresh()])
            ->assertSee(__('invoices.no_invoices'));
    }

    public function test_invoices_hides_empty_state_when_invoice_exists(): void
    {
        [$clinic, $user] = $this->makeContext('owner');
        $clinic->update(['settings' => ['billing_enabled' => true]]);
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        Invoice::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'invoice_number' => 'CC-000001',
            'issued_at' => today(),
            'status' => 'pending',
        ]);

        Livewire::actingAs($user)
            ->test(InvoicesIndex::class, ['clinic' => $clinic->fresh()])
            ->assertDontSee(__('invoices.no_invoices'));
    }

    // ── Recetas ────────────────────────────────────────────────────────────────

    public function test_prescriptions_shows_empty_state_with_no_prescriptions(): void
    {
        [$clinic, $user] = $this->makeContext('doctor');

        Livewire::actingAs($user)
            ->test(PrescriptionsIndex::class)
            ->assertSee(__('prescriptions.no_prescriptions'));
    }

    public function test_prescriptions_hides_empty_state_when_prescription_exists(): void
    {
        [$clinic, $user] = $this->makeContext('doctor');
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        Prescription::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $user->id,
            'status' => 'draft',
        ]);

        Livewire::actingAs($user)
            ->test(PrescriptionsIndex::class)
            ->assertDontSee(__('prescriptions.no_prescriptions'));
    }
}
