<?php

namespace Tests\Feature;

use App\Livewire\App\Patients\Create;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PatientCountryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_canadian_clinic_starts_a_new_patient_in_canada(): void
    {
        app()->setLocale('en');

        $clinic = Clinic::factory()->onboarded()->create(['country' => 'CA']);
        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);
        $owner = User::factory()->create(['clinic_id' => $clinic->id])->assignRole('owner');

        Livewire::actingAs($owner)
            ->test(Create::class, ['clinic' => $clinic])
            ->assertSet('country', 'CA')
            ->assertSee('Canada')
            ->assertSee('US/Canada')
            ->assertDontSee('Estados Unidos')
            ->assertDontSee('EE.UU./Canadá');
    }
}
