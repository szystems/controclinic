<?php

namespace Tests\Feature;

use App\Livewire\App\Appointments\Create;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CrossClinicReferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_appointment_rejects_a_patient_from_another_clinic(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $clinic = Clinic::factory()->onboarded()->create();
        $other = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->owner()->create(['clinic_id' => $clinic->id]);
        $foreign = Patient::factory()->create(['clinic_id' => $other->id]);

        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('patient_id', $foreign->id)
            ->set('doctor_id', $owner->id)
            ->set('appointment_type', 'scheduled')
            ->set('appointment_date', now()->addDay()->toDateString())
            ->set('start_time', '10:00')
            ->set('duration_minutes', 30)
            ->call('save')
            ->assertHasErrors('patient_id');

        $this->assertSame(0, Appointment::count());
    }
}
