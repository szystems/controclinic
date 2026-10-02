<?php

namespace Tests\Feature;

use App\Livewire\App\Patients\Edit;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PatientEditAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_receptionist_cannot_open_the_patient_edit_form(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        app()->instance('current_clinic', $clinic);
        $receptionist = User::factory()->create(['clinic_id' => $clinic->id])->assignRole('receptionist');
        $patient = Patient::factory()->create([
            'clinic_id' => $clinic->id,
            'internal_notes' => 'Nota interna reservada',
        ]);

        Livewire::actingAs($receptionist)
            ->test(Edit::class, ['patient' => $patient])
            ->assertForbidden();
    }

    public function test_doctor_can_open_the_patient_edit_form(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        app()->instance('current_clinic', $clinic);
        $doctor = User::factory()->create(['clinic_id' => $clinic->id])->assignRole('doctor');
        $patient = Patient::factory()->create([
            'clinic_id' => $clinic->id,
            'internal_notes' => 'Nota interna reservada',
        ]);
        view()->share('currentClinic', $clinic);

        Livewire::actingAs($doctor)
            ->test(Edit::class, ['patient' => $patient])
            ->assertSet('internal_notes', 'Nota interna reservada');
    }
}
