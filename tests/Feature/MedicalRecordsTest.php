<?php

namespace Tests\Feature;

use App\Livewire\App\MedicalRecords\Create;
use App\Livewire\App\MedicalRecords\Index;
use App\Livewire\App\MedicalRecords\Show;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MedicalRecordsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @return array{0: Clinic, 1: User, 2: Patient} */
    private function makeContext(array $clinicState = [], string $role = 'doctor'): array
    {
        $clinic = Clinic::factory()->onboarded()->create($clinicState);

        $user = User::factory()->create(['clinic_id' => $clinic->id]);
        $user->assignRole($role);

        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);

        return [$clinic, $user, $patient];
    }

    private function bindClinic(Clinic $clinic): void
    {
        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);
    }

    public function test_index_renders_with_records_for_authorized_user(): void
    {
        [$clinic, $user, $patient] = $this->makeContext();
        MedicalRecord::factory()->count(3)->forPatient($patient)->create([
            'doctor_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/patients/{$patient->id}/records")
            ->assertOk()
            ->assertSeeLivewire(Index::class);
    }

    public function test_index_forbids_users_without_records_view_permission(): void
    {
        [$clinic, , $patient] = $this->makeContext();
        $stranger = User::factory()->create(['clinic_id' => $clinic->id]);
        // No role assigned -> no permissions

        $this->actingAs($stranger)
            ->get("/app/{$clinic->slug}/patients/{$patient->id}/records")
            ->assertForbidden();
    }

    public function test_create_persists_final_record(): void
    {
        [$clinic, $user, $patient] = $this->makeContext();
        $this->bindClinic($clinic);

        Livewire::actingAs($user)
            ->withQueryParams([])
            ->test(Create::class, ['patient' => $patient])
            ->set('recordType', MedicalRecord::TYPE_CONSULTATION)
            ->set('title', 'Consulta inicial')
            ->set('chiefComplaint', 'Dolor abdominal')
            ->set('vitalSigns.temperature', '37.5')
            ->set('diagnoses', [['code' => 'K30', 'description' => 'Dispepsia']])
            ->set('prescriptions', [['drug' => 'Omeprazol', 'dosage' => '20mg', 'duration' => '14 días', 'notes' => '']])
            ->call('saveFinal')
            ->assertHasNoErrors();

        $record = MedicalRecord::query()->where('clinic_id', $clinic->id)->first();
        $this->assertNotNull($record);
        $this->assertSame(MedicalRecord::STATUS_FINAL, $record->status);
        $this->assertNotNull($record->finalized_at);
        $this->assertSame('37.5', $record->vital_signs['temperature']);
        $this->assertCount(1, $record->diagnoses);
        $this->assertCount(1, $record->prescriptions);
    }

    public function test_create_persists_draft_record(): void
    {
        [$clinic, $user, $patient] = $this->makeContext();
        $this->bindClinic($clinic);

        Livewire::actingAs($user)
            ->test(Create::class, ['patient' => $patient])
            ->set('recordType', MedicalRecord::TYPE_CONSULTATION)
            ->set('chiefComplaint', 'WIP')
            ->call('saveDraft')
            ->assertHasNoErrors();

        $record = MedicalRecord::query()->where('clinic_id', $clinic->id)->firstOrFail();
        $this->assertSame(MedicalRecord::STATUS_DRAFT, $record->status);
        $this->assertNull($record->finalized_at);
    }

    public function test_show_blocks_cross_tenant_access(): void
    {
        [$clinicA, $userA] = $this->makeContext();
        [, , $patientB] = $this->makeContext();
        $recordB = MedicalRecord::factory()->forPatient($patientB)->create();

        // userA tries to view recordB under clinicA's slug -> 404 (patient belongs to other clinic)
        $this->actingAs($userA)
            ->get("/app/{$clinicA->slug}/patients/{$patientB->id}/records/{$recordB->id}")
            ->assertNotFound();
    }

    public function test_confidential_record_hidden_without_permission(): void
    {
        [$clinic, $user, $patient] = $this->makeContext();
        // doctor role has records.view but NOT records.view_confidential by default
        $record = MedicalRecord::factory()->confidential()->forPatient($patient)->create();

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/patients/{$patient->id}/records/{$record->id}")
            ->assertForbidden();
    }

    public function test_edit_only_allowed_on_drafts(): void
    {
        [$clinic, $user, $patient] = $this->makeContext();
        $finalized = MedicalRecord::factory()->forPatient($patient)->create([
            'status' => MedicalRecord::STATUS_FINAL,
            'finalized_at' => now(),
        ]);

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/patients/{$patient->id}/records/{$finalized->id}/edit")
            ->assertRedirect(route('app.records.show', [
                'clinic' => $clinic->slug,
                'patient' => $patient->id,
                'record' => $finalized->id,
            ]));

        $draft = MedicalRecord::factory()->draft()->forPatient($patient)->create();

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/patients/{$patient->id}/records/{$draft->id}/edit")
            ->assertOk();
    }

    public function test_create_and_edit_blocked_when_clinic_is_read_only(): void
    {
        [$clinic, $user, $patient] = $this->makeContext([
            'status' => 'trial',
            'trial_ends_at' => now()->subDay(),
        ]);

        $billingUrl = route('app.billing.index', $clinic->slug);

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/patients/{$patient->id}/records/create")
            ->assertRedirect($billingUrl);

        $draft = MedicalRecord::factory()->draft()->forPatient($patient)->create();

        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/patients/{$patient->id}/records/{$draft->id}/edit")
            ->assertRedirect($billingUrl);

        // Read still allowed
        $this->actingAs($user)
            ->get("/app/{$clinic->slug}/patients/{$patient->id}/records")
            ->assertOk();
    }

    public function test_create_pre_fills_from_appointment_query_param(): void
    {
        [$clinic, $user, $patient] = $this->makeContext();
        $this->bindClinic($clinic);
        $appointment = Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->withQueryParams(['appointment_id' => $appointment->id])
            ->test(Create::class, ['patient' => $patient])
            ->assertSet('appointmentId', $appointment->id);
    }

    public function test_delete_requires_permission(): void
    {
        [$clinic, $user, $patient] = $this->makeContext();
        $this->bindClinic($clinic);
        $record = MedicalRecord::factory()->forPatient($patient)->create();

        // doctor role does NOT have records.delete by default
        Livewire::actingAs($user)
            ->test(Show::class, [
                'patient' => $patient,
                'record' => $record,
            ])
            ->call('delete');

        $this->assertDatabaseHas('medical_records', ['id' => $record->id]);

        // Owner role has all permissions
        $owner = User::factory()->create(['clinic_id' => $clinic->id]);
        $owner->assignRole('owner');

        Livewire::actingAs($owner)
            ->test(Show::class, [
                'patient' => $patient,
                'record' => $record,
            ])
            ->call('delete')
            ->assertRedirect();

        $this->assertSoftDeleted('medical_records', ['id' => $record->id]);
    }
}
