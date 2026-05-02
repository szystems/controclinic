<?php

namespace Tests\Feature;

use App\Livewire\App\Appointments\Create;
use App\Livewire\App\Schedule\Index as ScheduleIndex;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DoctorUnavailability;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DoctorScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @return array{0: Clinic, 1: User, 2: Patient} */
    private function makeContext(string $role = 'doctor'): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id]);
        $user->assignRole($role);
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        return [$clinic, $user, $patient];
    }

    // ==================== CRUD ====================

    public function test_doctor_can_see_schedule_page(): void
    {
        [$clinic, $doctor] = $this->makeContext('doctor');
        $this->actingAs($doctor);

        Livewire::test(ScheduleIndex::class, ['clinic' => $clinic])
            ->assertStatus(200);
    }

    public function test_receptionist_cannot_access_schedule(): void
    {
        [$clinic, $receptionist] = $this->makeContext('receptionist');
        $this->actingAs($receptionist);

        Livewire::test(ScheduleIndex::class, ['clinic' => $clinic])
            ->assertForbidden();
    }

    public function test_doctor_can_create_unavailability(): void
    {
        [$clinic, $doctor] = $this->makeContext('doctor');
        $this->actingAs($doctor);

        Livewire::test(ScheduleIndex::class, ['clinic' => $clinic])
            ->call('openCreate')
            ->set('date_from', '2027-03-01')
            ->set('date_to', '2027-03-05')
            ->set('all_day', true)
            ->set('reason', 'Vacaciones')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('doctor_unavailabilities', [
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
            'reason' => 'Vacaciones',
        ]);
    }

    public function test_owner_can_create_unavailability_for_doctor(): void
    {
        [$clinic, $doctor] = $this->makeContext('doctor');
        $owner = User::factory()->create(['clinic_id' => $clinic->id]);
        $owner->assignRole('owner');

        $this->actingAs($owner);
        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        Livewire::test(ScheduleIndex::class, ['clinic' => $clinic])
            ->set('selectedDoctorId', $doctor->id)
            ->call('openCreate')
            ->set('date_from', '2027-04-01')
            ->set('date_to', '2027-04-01')
            ->set('all_day', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('doctor_unavailabilities', [
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
        ]);
    }

    public function test_doctor_can_delete_own_unavailability(): void
    {
        [$clinic, $doctor] = $this->makeContext('doctor');
        $this->actingAs($doctor);

        $block = DoctorUnavailability::create([
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
            'date_from' => '2027-05-10',
            'date_to' => '2027-05-12',
            'all_day' => true,
            'created_by' => $doctor->id,
        ]);

        Livewire::test(ScheduleIndex::class, ['clinic' => $clinic])
            ->call('delete', $block->id)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('doctor_unavailabilities', ['id' => $block->id]);
    }

    public function test_unavailability_is_scoped_to_clinic(): void
    {
        [$clinicA, $doctorA] = $this->makeContext('doctor');
        $clinicB = Clinic::factory()->onboarded()->create();
        $doctorB = User::factory()->create(['clinic_id' => $clinicB->id]);
        $doctorB->assignRole('doctor');

        // Create block for clinic B
        $block = DoctorUnavailability::create([
            'clinic_id' => $clinicB->id,
            'doctor_id' => $doctorB->id,
            'date_from' => '2027-06-01',
            'date_to' => '2027-06-01',
            'all_day' => true,
            'created_by' => $doctorB->id,
        ]);

        $this->actingAs($doctorA);

        // Doctor A cannot delete block from clinic B
        Livewire::test(ScheduleIndex::class, ['clinic' => $clinicA])
            ->call('delete', $block->id);

        $this->assertNotSoftDeleted('doctor_unavailabilities', ['id' => $block->id]);
    }

    // ==================== APPOINTMENT CONFLICT ====================

    public function test_create_appointment_blocked_by_all_day_unavailability(): void
    {
        [$clinic, $doctor, $patient] = $this->makeContext('doctor');
        $this->actingAs($doctor);

        DoctorUnavailability::create([
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
            'date_from' => '2027-07-10',
            'date_to' => '2027-07-10',
            'all_day' => true,
            'created_by' => $doctor->id,
        ]);

        Livewire::actingAs($doctor)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('patient_id', $patient->id)
            ->set('doctor_id', $doctor->id)
            ->set('appointment_date', '2027-07-10')
            ->set('start_time', '10:00')
            ->set('duration_minutes', 30)
            ->set('appointment_type', 'scheduled')
            ->call('save');

        $this->assertEquals(0, Appointment::count());
    }

    public function test_partial_block_blocks_overlapping_slot(): void
    {
        [$clinic, $doctor, $patient] = $this->makeContext('doctor');
        $this->actingAs($doctor);

        DoctorUnavailability::create([
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
            'date_from' => '2027-08-01',
            'date_to' => '2027-08-01',
            'all_day' => false,
            'time_from' => '09:00',
            'time_to' => '12:00',
            'created_by' => $doctor->id,
        ]);

        // Slot 10:00–10:30 overlaps block 09:00–12:00
        Livewire::actingAs($doctor)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('patient_id', $patient->id)
            ->set('doctor_id', $doctor->id)
            ->set('appointment_date', '2027-08-01')
            ->set('start_time', '10:00')
            ->set('duration_minutes', 30)
            ->set('appointment_type', 'scheduled')
            ->call('save');

        $this->assertEquals(0, Appointment::count());
    }

    public function test_partial_block_allows_non_overlapping_slot(): void
    {
        [$clinic, $doctor, $patient] = $this->makeContext('doctor');
        $this->actingAs($doctor);

        DoctorUnavailability::create([
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
            'date_from' => '2027-08-05',
            'date_to' => '2027-08-05',
            'all_day' => false,
            'time_from' => '09:00',
            'time_to' => '12:00',
            'created_by' => $doctor->id,
        ]);

        // Slot 14:00–14:30 does NOT overlap
        Livewire::actingAs($doctor)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('patient_id', $patient->id)
            ->set('doctor_id', $doctor->id)
            ->set('appointment_date', '2027-08-05')
            ->set('start_time', '14:00')
            ->set('duration_minutes', 30)
            ->set('appointment_type', 'scheduled')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(1, Appointment::count());
    }
}
