<?php

namespace Tests\Feature;

use App\Livewire\App\Prescriptions\Create as PrescriptionsCreate;
use App\Livewire\App\Prescriptions\Edit as PrescriptionsEdit;
use App\Livewire\App\Prescriptions\Index as PrescriptionsIndex;
use App\Livewire\App\Prescriptions\Show as PrescriptionsShow;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PrescriptionsTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ────────────────────────────────────────────────────

    private function makeClinic(): Clinic
    {
        return Clinic::factory()->onboarded()->create();
    }

    private function makeOwner(Clinic $clinic): User
    {
        return User::factory()->owner()->create(['clinic_id' => $clinic->id]);
    }

    private function makeDoctor(Clinic $clinic): User
    {
        return User::factory()->create(['clinic_id' => $clinic->id])->assignRole('doctor');
    }

    private function makePatient(Clinic $clinic): Patient
    {
        return Patient::factory()->create(['clinic_id' => $clinic->id]);
    }

    private function bindClinic(Clinic $clinic): void
    {
        app()->instance('current_clinic', $clinic);
    }

    private function makeDraft(Clinic $clinic, Patient $patient, User $doctor, array $overrides = []): Prescription
    {
        $rx = Prescription::create(array_merge([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => Prescription::STATUS_DRAFT,
            'issued_at' => now()->toDateString(),
            'diagnosis' => 'Gripe',
        ], $overrides));

        PrescriptionItem::create([
            'prescription_id' => $rx->id,
            'order' => 0,
            'medication_name' => 'Paracetamol',
            'dose' => '500mg',
            'frequency' => 'cada 8h',
            'duration' => '3 días',
            'quantity' => 9,
            'is_controlled' => false,
        ]);

        return $rx;
    }

    // ─── INDEX ───────────────────────────────────────────────────────

    public function test_index_requires_prescriptions_view_permission(): void
    {
        $clinic = $this->makeClinic();
        $this->makeOwner($clinic);
        $this->bindClinic($clinic);

        $receptionist = User::factory()->create(['clinic_id' => $clinic->id])->syncRoles(['receptionist']);

        Livewire::actingAs($receptionist)
            ->test(PrescriptionsIndex::class, ['clinic' => $clinic])
            ->assertStatus(403);
    }

    public function test_doctor_can_list_prescriptions(): void
    {
        $clinic = $this->makeClinic();
        $doctor = $this->makeDoctor($clinic);
        $patient = $this->makePatient($clinic);
        $this->makeDraft($clinic, $patient, $doctor);
        $this->bindClinic($clinic);

        Livewire::actingAs($doctor)
            ->test(PrescriptionsIndex::class, ['clinic' => $clinic])
            ->assertStatus(200)
            ->assertSee($patient->last_name);
    }

    public function test_index_isolates_by_clinic(): void
    {
        $clinic1 = $this->makeClinic();
        $doctor1 = $this->makeDoctor($clinic1);
        $patient1 = $this->makePatient($clinic1);
        $this->makeDraft($clinic1, $patient1, $doctor1);

        $clinic2 = $this->makeClinic();
        $doctor2 = $this->makeDoctor($clinic2);
        $patient2 = $this->makePatient($clinic2);
        $this->makeDraft($clinic2, $patient2, $doctor2);
        $this->bindClinic($clinic1);

        Livewire::actingAs($doctor1)
            ->test(PrescriptionsIndex::class, ['clinic' => $clinic1])
            ->assertSee($patient1->last_name)
            ->assertDontSee($patient2->last_name);
    }

    // ─── CREATE ──────────────────────────────────────────────────────

    public function test_assistant_cannot_access_create(): void
    {
        $clinic = $this->makeClinic();
        $this->makeOwner($clinic);
        $this->bindClinic($clinic);
        $assistant = User::factory()->create(['clinic_id' => $clinic->id])->syncRoles(['assistant']);

        Livewire::actingAs($assistant)
            ->test(PrescriptionsCreate::class, ['clinic' => $clinic])
            ->assertStatus(403);
    }

    public function test_doctor_can_create_draft_prescription(): void
    {
        $clinic = $this->makeClinic();
        $doctor = $this->makeDoctor($clinic);
        $patient = $this->makePatient($clinic);
        $this->bindClinic($clinic);

        Livewire::actingAs($doctor)
            ->test(PrescriptionsCreate::class, ['clinic' => $clinic])
            ->set('patientId', $patient->id)
            ->set('diagnosis', 'Faringitis')
            ->set('issuedAt', now()->toDateString())
            ->set('items', [[
                'medication_name' => 'Amoxicilina',
                'active_ingredient' => 'Amoxicilina',
                'presentation' => 'cápsulas 500mg',
                'dose' => '1 cápsula',
                'frequency' => 'cada 8h',
                'duration' => '7 días',
                'route' => 'oral',
                'instructions' => 'Con alimentos',
                'quantity' => 21,
                'is_controlled' => false,
            ]])
            ->call('save')
            ->assertRedirect();

        $this->assertDatabaseHas('prescriptions', [
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => Prescription::STATUS_DRAFT,
            'diagnosis' => 'Faringitis',
        ]);

        $rx = Prescription::where('patient_id', $patient->id)->first();
        $this->assertNotNull($rx);
        $this->assertDatabaseHas('prescription_items', [
            'prescription_id' => $rx->id,
            'medication_name' => 'Amoxicilina',
            'quantity' => 21,
        ]);
    }

    public function test_can_issue_prescription_on_save(): void
    {
        $clinic = $this->makeClinic();
        $doctor = $this->makeDoctor($clinic);
        $patient = $this->makePatient($clinic);
        $this->bindClinic($clinic);

        Livewire::actingAs($doctor)
            ->test(PrescriptionsCreate::class, ['clinic' => $clinic])
            ->set('patientId', $patient->id)
            ->set('issuedAt', now()->toDateString())
            ->set('items', [[
                'medication_name' => 'Ibuprofeno',
                'dose' => '400mg', 'frequency' => 'cada 8h',
                'duration' => '5 días', 'quantity' => 15,
                'active_ingredient' => '', 'presentation' => '', 'route' => '',
                'instructions' => '', 'is_controlled' => false,
            ]])
            ->call('save', true)
            ->assertRedirect();

        $this->assertDatabaseHas('prescriptions', [
            'patient_id' => $patient->id,
            'status' => Prescription::STATUS_ISSUED,
        ]);
    }

    public function test_issue_generates_folio_and_qr(): void
    {
        $clinic = $this->makeClinic();
        $doctor = $this->makeDoctor($clinic);
        $patient = $this->makePatient($clinic);
        $rx = $this->makeDraft($clinic, $patient, $doctor);

        $rx->issue();
        $rx->refresh();

        $this->assertNotNull($rx->folio);
        $this->assertNotNull($rx->qr_payload);
        $this->assertStringStartsWith('RX-', $rx->folio);
        $this->assertEquals(Prescription::STATUS_ISSUED, $rx->status);
    }

    public function test_folio_increments_per_clinic(): void
    {
        $clinic = $this->makeClinic();
        $doctor = $this->makeDoctor($clinic);
        $patient = $this->makePatient($clinic);

        $rx1 = $this->makeDraft($clinic, $patient, $doctor);
        $rx2 = $this->makeDraft($clinic, $patient, $doctor);

        $rx1->issue();
        $rx2->issue();

        $this->assertNotEquals($rx1->fresh()->folio, $rx2->fresh()->folio);
    }

    public function test_folio_does_not_bleed_across_clinics(): void
    {
        $clinic1 = $this->makeClinic();
        $doctor1 = $this->makeDoctor($clinic1);
        $patient1 = $this->makePatient($clinic1);

        $clinic2 = $this->makeClinic();
        $doctor2 = $this->makeDoctor($clinic2);
        $patient2 = $this->makePatient($clinic2);

        $rx1 = $this->makeDraft($clinic1, $patient1, $doctor1);
        $rx2 = $this->makeDraft($clinic2, $patient2, $doctor2);

        $rx1->issue();
        $rx2->issue();

        // Ambos reciben RX-0001 (independiente por clínica)
        $this->assertEquals('RX-0001', $rx1->fresh()->folio);
        $this->assertEquals('RX-0001', $rx2->fresh()->folio);
    }

    // ─── SHOW / ACCIONES ─────────────────────────────────────────────

    public function test_doctor_can_issue_from_show(): void
    {
        $clinic = $this->makeClinic();
        $doctor = $this->makeDoctor($clinic);
        $patient = $this->makePatient($clinic);
        $rx = $this->makeDraft($clinic, $patient, $doctor);
        $this->bindClinic($clinic);

        Livewire::actingAs($doctor)
            ->test(PrescriptionsShow::class, ['clinic' => $clinic, 'prescription' => $rx])
            ->call('issue')
            ->assertHasNoErrors();

        $this->assertEquals(Prescription::STATUS_ISSUED, $rx->fresh()->status);
    }

    public function test_doctor_can_cancel_issued_prescription(): void
    {
        $clinic = $this->makeClinic();
        $doctor = $this->makeDoctor($clinic);
        $patient = $this->makePatient($clinic);
        $rx = $this->makeDraft($clinic, $patient, $doctor);
        $rx->issue();
        $this->bindClinic($clinic);

        Livewire::actingAs($doctor)
            ->test(PrescriptionsShow::class, ['clinic' => $clinic, 'prescription' => $rx])
            ->call('confirmCancel')
            ->assertSet('showCancelModal', true)
            ->call('cancel')
            ->assertSet('showCancelModal', false)
            ->assertHasNoErrors();

        $this->assertEquals(Prescription::STATUS_CANCELLED, $rx->fresh()->status);
    }

    // ─── EDIT ────────────────────────────────────────────────────────

    public function test_cannot_edit_issued_prescription(): void
    {
        $clinic = $this->makeClinic();
        $doctor = $this->makeDoctor($clinic);
        $patient = $this->makePatient($clinic);
        $rx = $this->makeDraft($clinic, $patient, $doctor);
        $rx->issue();
        $this->bindClinic($clinic);

        Livewire::actingAs($doctor)
            ->test(PrescriptionsEdit::class, ['clinic' => $clinic, 'prescription' => $rx])
            ->assertStatus(403);
    }

    public function test_edit_updates_items(): void
    {
        $clinic = $this->makeClinic();
        $doctor = $this->makeDoctor($clinic);
        $patient = $this->makePatient($clinic);
        $rx = $this->makeDraft($clinic, $patient, $doctor);
        $this->bindClinic($clinic);

        Livewire::actingAs($doctor)
            ->test(PrescriptionsEdit::class, ['clinic' => $clinic, 'prescription' => $rx])
            ->set('diagnosis', 'Actualizado')
            ->set('items', [[
                'medication_name' => 'Naproxeno',
                'dose' => '250mg',
                'frequency' => 'cada 12h',
                'duration' => '5 días',
                'quantity' => 10,
                'active_ingredient' => '',
                'presentation' => '',
                'route' => 'oral',
                'instructions' => '',
                'is_controlled' => false,
            ]])
            ->call('save')
            ->assertRedirect();

        $this->assertDatabaseHas('prescriptions', [
            'id' => $rx->id,
            'diagnosis' => 'Actualizado',
        ]);

        $this->assertDatabaseHas('prescription_items', [
            'prescription_id' => $rx->id,
            'medication_name' => 'Naproxeno',
        ]);
    }

    // ─── SEGURIDAD ───────────────────────────────────────────────────

    public function test_prescription_not_accessible_from_other_clinic(): void
    {
        $clinic1 = $this->makeClinic();
        $doctor1 = $this->makeDoctor($clinic1);
        $patient1 = $this->makePatient($clinic1);
        $rx = $this->makeDraft($clinic1, $patient1, $doctor1);

        $clinic2 = $this->makeClinic();
        $doctor2 = $this->makeDoctor($clinic2);
        $this->bindClinic($clinic2);

        // doctor2 no pertenece a clinic1 → la policy devuelve false → 403
        Livewire::actingAs($doctor2)
            ->test(PrescriptionsShow::class, ['clinic' => $clinic2, 'prescription' => $rx])
            ->assertStatus(403);
    }
}
