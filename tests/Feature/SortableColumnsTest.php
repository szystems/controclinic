<?php

namespace Tests\Feature;

use App\Livewire\App\Appointments\Index as AppointmentsIndex;
use App\Livewire\App\Patients\Index as PatientsIndex;
use App\Livewire\App\Staff\Index as StaffIndex;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SortableColumnsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_lists_ignore_a_sort_column_the_screen_does_not_offer(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->owner()->create(['clinic_id' => $clinic->id]);
        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        $staff = Livewire::actingAs($owner)->test(StaffIndex::class, ['clinic' => $clinic]);
        $staff->call('sortBy', 'password')->assertSet('sortField', 'name');
        $staffSql = $this->orderedSql(function () use ($staff) {
            $staff->set('sortField', 'password')->set('sortDirection', 'asc; drop table users');
        });
        $this->assertStringContainsString('`name`', $staffSql);
        $this->assertStringNotContainsString('password', $staffSql);

        $patients = Livewire::actingAs($owner)->test(PatientsIndex::class, ['clinic' => $clinic]);
        $patients->call('sortBy', 'email')->assertSet('sortField', 'created_at');
        $patients->call('sortBy', 'last_name')->assertSet('sortField', 'last_name')->assertSet('sortDirection', 'asc');
        $patients->set('sortField', 'password');
        $patientSql = $this->builderSql($patients->instance(), 'buildBaseQuery');
        $this->assertStringContainsString('order by `created_at`', $patientSql);
        $this->assertStringNotContainsString('password', $patientSql);

        $appointments = Livewire::actingAs($owner)->test(AppointmentsIndex::class, ['clinic' => $clinic]);
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $owner->id,
            'appointment_date' => $appointments->get('dateFilter'),
            'status' => Appointment::STATUS_SCHEDULED,
        ]);
        $appointments->call('sortBy', 'patient_id')->assertSet('sortField', 'appointment_date');
        $appointmentSql = $this->orderedSql(function () use ($appointments) {
            $appointments->set('sortField', 'notes')->set('sortDirection', 'sideways');
        });
        $this->assertStringContainsString('`appointment_date`', $appointmentSql);
        $this->assertStringNotContainsString('sideways', $appointmentSql);
        $this->assertStringNotContainsString('`notes`', $appointmentSql);
    }

    private function orderedSql(callable $callback): string
    {
        $sql = '';
        DB::listen(function ($query) use (&$sql) {
            if (str_contains(strtolower($query->sql), 'order by')) {
                $sql .= $query->sql."\n";
            }
        });
        $callback();

        return $sql;
    }

    private function builderSql(object $component, string $method): string
    {
        $reflection = new \ReflectionMethod($component, $method);
        $reflection->setAccessible(true);

        return strtolower($reflection->invoke($component)->toSql());
    }
}
