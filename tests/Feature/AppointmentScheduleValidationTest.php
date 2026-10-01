<?php

namespace Tests\Feature;

use App\Livewire\App\Appointments\Create;
use App\Livewire\App\Appointments\Edit;
use App\Livewire\App\Reports\Index as ReportsIndex;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AppointmentScheduleValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_create_uses_the_clinic_day_and_rejects_a_time_that_ends_tomorrow(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-02 06:00:00', 'UTC'));

        try {
            [$clinic, $owner, $doctor, $patient] = $this->makeContext('America/Vancouver');

            $component = Livewire::actingAs($owner)->test(Create::class, ['clinic' => $clinic])
                ->set('patient_id', $patient->id)
                ->set('doctor_id', (string) $doctor->id)
                ->set('duration_minutes', 30)
                ->set('start_time', '10:00')
                ->set('appointment_date', '2026-10-01');

            $component->call('save')->assertHasNoErrors(['appointment_date', 'start_time']);

            $component->set('appointment_date', '2026-09-30')
                ->call('save')
                ->assertHasErrors(['appointment_date']);

            $component->set('appointment_date', '2026-10-01')
                ->set('start_time', 'tarde')
                ->call('save')
                ->assertHasErrors(['start_time']);

            $component->set('start_time', '23:30')
                ->set('duration_minutes', 60)
                ->call('save')
                ->assertHasErrors(['start_time']);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_edit_keeps_an_existing_past_date_and_rejects_another_past_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-02 06:00:00', 'UTC'));

        try {
            [$clinic, $owner, $doctor, $patient] = $this->makeContext('America/Vancouver');

            $appointment = Appointment::create([
                'clinic_id' => $clinic->id,
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'appointment_type' => Appointment::TYPE_SCHEDULED,
                'appointment_date' => '2026-09-20',
                'start_time' => '10:00',
                'end_time' => '10:30',
                'duration_minutes' => 30,
                'status' => Appointment::STATUS_SCHEDULED,
            ]);

            app()->instance('current_clinic', $clinic);

            $component = Livewire::actingAs($owner)->test(Edit::class, [
                'clinic' => $clinic,
                'appointment' => $appointment,
            ]);

            $component->set('notes', 'Sin cambio de fecha')
                ->call('save')
                ->assertHasNoErrors(['appointment_date', 'start_time']);

            $component->set('appointment_date', '2026-09-21')
                ->call('save')
                ->assertHasErrors(['appointment_date']);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_report_dates_must_use_a_calendar_day(): void
    {
        [$clinic, $owner] = $this->makeContext('America/Mexico_City');

        $component = Livewire::actingAs($owner)->test(ReportsIndex::class, ['clinic' => $clinic]);

        $component->set('period', 'custom')
            ->set('dateFrom', 'not-a-date')
            ->assertHasErrors(['dateFrom']);

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', (string) $component->get('dateFrom'));
    }

    /** @return array{0: Clinic, 1: User, 2: User, 3: Patient} */
    private function makeContext(string $timezone): array
    {
        $clinic = Clinic::factory()->onboarded()->create([
            'timezone' => $timezone,
            'max_appointments_per_month' => 20,
        ]);

        $owner = User::factory()->owner()->create([
            'clinic_id' => $clinic->id,
        ]);

        $doctor = User::factory()->doctor()->create([
            'clinic_id' => $clinic->id,
        ]);

        $patient = Patient::factory()->create([
            'clinic_id' => $clinic->id,
        ]);

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        return [$clinic, $owner, $doctor, $patient];
    }
}
