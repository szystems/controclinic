<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class HardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_rate_limiters_are_registered(): void
    {
        $this->assertNotNull(RateLimiter::limiter('api'));
        $this->assertNotNull(RateLimiter::limiter('global'));
        $this->assertNotNull(RateLimiter::limiter('sensitive'));
        $this->assertNotNull(RateLimiter::limiter('webhook'));
    }

    public function test_patient_policy_blocks_cross_tenant_access(): void
    {
        $clinicA = Clinic::factory()->onboarded()->create();
        $clinicB = Clinic::factory()->onboarded()->create();

        $userA = User::factory()->create(['clinic_id' => $clinicA->id, 'role' => 'doctor']);
        $userA->assignRole('doctor');

        app()->instance('current_clinic', $clinicB);
        $patientB = Patient::factory()->create(['clinic_id' => $clinicB->id]);

        $this->assertFalse($userA->can('view', $patientB));
        $this->assertFalse($userA->can('update', $patientB));
    }

    public function test_appointment_policy_blocks_cross_tenant_access(): void
    {
        $clinicA = Clinic::factory()->onboarded()->create();
        $clinicB = Clinic::factory()->onboarded()->create();

        $userA = User::factory()->create(['clinic_id' => $clinicA->id, 'role' => 'doctor']);
        $userA->assignRole('doctor');

        app()->instance('current_clinic', $clinicB);
        $patient = Patient::factory()->create(['clinic_id' => $clinicB->id]);
        $doctor = User::factory()->create(['clinic_id' => $clinicB->id]);
        $appt = Appointment::factory()->create([
            'clinic_id' => $clinicB->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);

        $this->assertFalse($userA->can('view', $appt));
    }

    public function test_medical_record_policy_respects_confidentiality(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $doctor = User::factory()->create(['clinic_id' => $clinic->id, 'role' => 'doctor']);
        $doctor->assignRole('doctor');
        // Revoke view_confidential
        $doctor->roles->first()->revokePermissionTo('records.view_confidential');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        app()->instance('current_clinic', $clinic);
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $confidential = MedicalRecord::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'is_confidential' => true,
        ]);

        $this->assertFalse($doctor->fresh()->can('view', $confidential));
        $this->assertFalse($doctor->fresh()->can('print', $confidential));
    }

    public function test_paddle_webhook_secret_is_configurable(): void
    {
        $this->assertArrayHasKey('webhook_secret', config('cashier'));
    }
}
