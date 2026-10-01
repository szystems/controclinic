<?php

namespace Tests\Feature;

use App\Jobs\SendAppointmentNotification;
use App\Livewire\App\Appointments\Index as AppointmentsIndex;
use App\Livewire\App\Appointments\Show as AppointmentsShow;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AppointmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return array{0: Clinic, 1: User, 2: Patient}
     */
    private function makeContext(): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id, 'role' => 'doctor']);
        $user->assignRole('doctor');
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        return [$clinic, $user, $patient];
    }

    public function test_a_completed_appointment_cannot_be_confirmed_or_marked_no_show(): void
    {
        [$clinic, $user, $patient] = $this->makeContext();
        $appointment = Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $user->id,
            'status' => Appointment::STATUS_COMPLETED,
            'appointment_date' => $clinic->localNow()->toDateString(),
        ]);

        Livewire::actingAs($user)
            ->test(AppointmentsShow::class, ['clinic' => $clinic, 'appointment' => $appointment])
            ->call('confirmAppointment')
            ->call('markNoShow');

        $this->assertSame(Appointment::STATUS_COMPLETED, $appointment->fresh()->status);
    }

    public function test_check_in_from_the_list_does_not_send_a_confirmation_email(): void
    {
        Bus::fake();
        [$clinic, $user, $patient] = $this->makeContext();
        $appointment = Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $user->id,
            'status' => Appointment::STATUS_SCHEDULED,
            'appointment_date' => $clinic->localNow()->toDateString(),
            'start_time' => '10:00:00',
        ]);

        Livewire::actingAs($user)
            ->test(AppointmentsIndex::class, ['clinic' => $clinic])
            ->call('checkIn', $appointment->id);

        $this->assertSame(Appointment::STATUS_WAITING, $appointment->fresh()->status);
        Bus::assertNotDispatched(SendAppointmentNotification::class);
    }

    public function test_check_in_refuses_an_appointment_from_another_day(): void
    {
        [$clinic, $user, $patient] = $this->makeContext();
        $appointment = Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $user->id,
            'status' => Appointment::STATUS_CONFIRMED,
            'appointment_date' => $clinic->localNow()->subDay()->toDateString(),
        ]);

        Livewire::actingAs($user)
            ->test(AppointmentsIndex::class, ['clinic' => $clinic])
            ->call('checkIn', $appointment->id);

        $this->assertSame(Appointment::STATUS_CONFIRMED, $appointment->fresh()->status);
    }
}
