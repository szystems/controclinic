<?php

namespace Tests\Feature;

use App\Livewire\App\Appointments\Index as AppointmentsIndex;
use App\Livewire\App\Appointments\Show as AppointmentsShow;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AppointmentsExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function makeClinicWithUser(string $role = 'owner'): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id]);
        $user->assignRole($role);

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        return [$clinic, $user];
    }

    private function makeAppointment(Clinic $clinic, array $attrs = []): Appointment
    {
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $doctor = User::factory()->create(['clinic_id' => $clinic->id]);
        $doctor->assignRole('doctor');

        return Appointment::factory()->create(array_merge([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->toDateString(),
        ], $attrs));
    }

    private function downloadContent($component): string
    {
        $download = data_get($component->effects, 'download');
        $this->assertNotNull($download, 'Expected a file download effect.');

        return base64_decode($download['content']);
    }

    // ==================== CSV ====================

    public function test_owner_can_export_appointments_csv(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        $a = $this->makeAppointment($clinic);
        $patient = $a->patient;
        $patient->update(['first_name' => 'Lucia', 'last_name' => 'Test']);

        $component = Livewire::actingAs($owner)
            ->test(AppointmentsIndex::class, ['clinic' => $clinic])
            ->call('exportCsv')
            ->assertFileDownloaded(null, null, 'text/csv; charset=UTF-8');

        $content = $this->downloadContent($component);
        $this->assertStringContainsString('Lucia', $content);
    }

    public function test_receptionist_cannot_export_csv(): void
    {
        [$clinic, $user] = $this->makeClinicWithUser('receptionist');

        Livewire::actingAs($user)
            ->test(AppointmentsIndex::class, ['clinic' => $clinic])
            ->call('exportCsv')
            ->assertForbidden();
    }

    public function test_csv_export_isolated_to_clinic(): void
    {
        [$clinicA, $ownerA] = $this->makeClinicWithUser('owner');
        $clinicB = Clinic::factory()->onboarded()->create();

        // Bind to clinicA first so its patient/appointment factories work
        app()->instance('current_clinic', $clinicA);
        $aA = $this->makeAppointment($clinicA);
        $aA->patient->update(['first_name' => 'TenantA', 'last_name' => 'Patient']);

        // Bind to clinicB to create its data
        app()->instance('current_clinic', $clinicB);
        $aB = $this->makeAppointment($clinicB);
        $aB->patient->update(['first_name' => 'TenantB', 'last_name' => 'Patient']);

        // Re-bind to clinicA for the export
        app()->instance('current_clinic', $clinicA);
        view()->share('currentClinic', $clinicA);

        $component = Livewire::actingAs($ownerA)
            ->test(AppointmentsIndex::class, ['clinic' => $clinicA])
            ->set('dateFilter', '')
            ->call('exportCsv')
            ->assertFileDownloaded();

        $content = $this->downloadContent($component);
        $this->assertStringContainsString('TenantA', $content);
        $this->assertStringNotContainsString('TenantB', $content);
    }

    // ==================== PDF (list / agenda) ====================

    public function test_owner_can_export_appointments_pdf(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        $this->makeAppointment($clinic);

        Livewire::actingAs($owner)
            ->test(AppointmentsIndex::class, ['clinic' => $clinic])
            ->call('exportPdf')
            ->assertFileDownloaded(null, null, 'application/pdf');
    }

    public function test_receptionist_cannot_export_appointments_pdf_when_no_print_permission(): void
    {
        // Receptionist has appointments.print in the default seeder—
        // verify a role without it (e.g. assistant) is still ok at index PDF
        // (assistant has appts.print). To keep this test useful, we revoke
        // the print permission temporarily.
        [$clinic, $user] = $this->makeClinicWithUser('receptionist');
        $user->roles->first()->revokePermissionTo('appointments.print');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Livewire::actingAs($user->fresh())
            ->test(AppointmentsIndex::class, ['clinic' => $clinic])
            ->call('exportPdf')
            ->assertForbidden();
    }

    // ==================== PDF (Show — voucher) ====================

    public function test_owner_can_export_appointment_voucher_pdf(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        $a = $this->makeAppointment($clinic);

        Livewire::actingAs($owner)
            ->test(AppointmentsShow::class, ['clinic' => $clinic, 'appointment' => $a])
            ->call('exportPdf')
            ->assertFileDownloaded(null, null, 'application/pdf');
    }

    public function test_receptionist_can_print_voucher_for_their_clinic(): void
    {
        // receptionist tiene appointments.print en seeder
        [$clinic, $user] = $this->makeClinicWithUser('receptionist');
        $a = $this->makeAppointment($clinic);

        Livewire::actingAs($user)
            ->test(AppointmentsShow::class, ['clinic' => $clinic, 'appointment' => $a])
            ->call('exportPdf')
            ->assertFileDownloaded(null, null, 'application/pdf');
    }
}
