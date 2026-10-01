<?php

namespace Tests\Feature;

use App\Livewire\App\Dashboard;
use App\Livewire\App\Invoices\Create as InvoiceCreate;
use App\Livewire\App\Invoices\Index as InvoiceIndex;
use App\Livewire\App\Prescriptions\Create as PrescriptionCreate;
use App\Livewire\App\Schedule\Index as ScheduleIndex;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DoctorUnavailability;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ClinicLocalDateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_calendar_today_follows_the_clinic_not_utc(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-01 06:00:00', 'UTC'));

        $clinic = Clinic::factory()->onboarded()->create([
            'timezone' => 'America/Vancouver',
            'settings' => ['billing_enabled' => true],
        ]);
        $owner = User::factory()->create(['clinic_id' => $clinic->id]);
        $owner->assignRole('owner');
        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        $patient = Patient::factory()->create([
            'clinic_id' => $clinic->id,
            'first_name' => 'Octubre',
            'birth_date' => '1990-10-15',
        ]);
        Patient::factory()->create([
            'clinic_id' => $clinic->id,
            'first_name' => 'Noviembre',
            'birth_date' => '1990-11-15',
        ]);

        Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $owner->id,
            'appointment_date' => '2026-10-31',
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        $this->assertNotNull($patient->nextUpcomingAppointment);
        $this->assertCount(1, $patient->getUpcomingAppointments());

        $birthdays = Livewire::actingAs($owner)
            ->test(Dashboard::class, ['clinic' => $clinic])
            ->instance()
            ->getBirthdaysThisMonthProperty()
            ->pluck('first_name');

        $this->assertTrue($birthdays->contains('Octubre'));
        $this->assertFalse($birthdays->contains('Noviembre'));

        Livewire::actingAs($owner)
            ->test(InvoiceCreate::class, ['clinic' => $clinic])
            ->assertSet('issued_at', '2026-10-31');

        Livewire::actingAs($owner)
            ->test(PrescriptionCreate::class)
            ->assertSet('issuedAt', '2026-10-31');

        Invoice::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'invoice_number' => 'DUE-1031',
            'issued_at' => '2026-10-01',
            'due_at' => '2026-10-31',
            'currency' => 'USD',
            'status' => Invoice::STATUS_PENDING,
            'subtotal' => 100,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 100,
            'paid_amount' => 0,
        ]);

        Livewire::actingAs($owner)
            ->test(InvoiceIndex::class, ['clinic' => $clinic])
            ->set('filterOverdue', 'yes')
            ->assertDontSee('DUE-1031');

        $schedule = Livewire::actingAs($owner)
            ->test(ScheduleIndex::class, ['clinic' => $clinic]);

        DoctorUnavailability::create([
            'clinic_id' => $clinic->id,
            'doctor_id' => $schedule->get('selectedDoctorId'),
            'date_from' => '2026-10-31',
            'date_to' => '2026-10-31',
            'all_day' => true,
            'created_by' => $owner->id,
        ]);

        $this->assertCount(1, $schedule->instance()->getUnavailabilitiesProperty());
        $this->assertCount(0, $schedule->instance()->getPastUnavailabilitiesProperty());

        $current = Prescription::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $owner->id,
            'status' => Prescription::STATUS_ISSUED,
            'issued_at' => '2026-10-31',
            'valid_until' => '2026-10-31',
        ]);
        $expired = Prescription::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $owner->id,
            'status' => Prescription::STATUS_ISSUED,
            'issued_at' => '2026-09-01',
            'valid_until' => '2026-09-30',
        ]);

        $this->assertFalse($current->fresh()->is_expired);
        $this->assertTrue($expired->fresh()->is_expired);
    }
}
