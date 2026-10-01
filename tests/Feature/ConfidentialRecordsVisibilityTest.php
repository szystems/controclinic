<?php

namespace Tests\Feature;

use App\Livewire\App\MedicalRecords\Edit;
use App\Livewire\App\Patients\Show as PatientShow;
use App\Models\Clinic;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ConfidentialRecordsVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_doctor_does_not_see_confidential_records_on_the_patient_history(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $doctor = User::factory()->create(['clinic_id' => $clinic->id, 'role' => 'doctor']);
        $doctor->assignRole('doctor');
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);

        $visible = MedicalRecord::factory()->forPatient($patient)->create([
            'doctor_id' => $doctor->id,
            'title' => 'Nota visible de control',
            'is_confidential' => false,
        ]);
        $hidden = MedicalRecord::factory()->forPatient($patient)->confidential()->create([
            'doctor_id' => $doctor->id,
            'title' => 'Nota confidencial secreta',
            'chief_complaint' => 'Motivo confidencial secreto',
        ]);

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        Livewire::actingAs($doctor)
            ->test(PatientShow::class, ['patient' => $patient])
            ->set('tab', 'historial')
            ->assertSee($visible->title)
            ->assertDontSee($hidden->title)
            ->assertDontSee('Motivo confidencial secreto');
    }

    public function test_doctor_cannot_open_a_confidential_draft(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $doctor = User::factory()->create(['clinic_id' => $clinic->id, 'role' => 'doctor']);
        $doctor->assignRole('doctor');
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $record = MedicalRecord::factory()->forPatient($patient)->confidential()->create([
            'doctor_id' => $doctor->id,
            'status' => MedicalRecord::STATUS_DRAFT,
        ]);

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        Livewire::actingAs($doctor)
            ->test(Edit::class, ['patient' => $patient, 'record' => $record])
            ->assertForbidden();
    }
}
