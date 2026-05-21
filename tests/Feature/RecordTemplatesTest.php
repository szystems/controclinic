<?php

namespace Tests\Feature;

use App\Livewire\App\MedicalRecords\Create as CreateRecord;
use App\Livewire\App\Settings\RecordTemplates;
use App\Models\Clinic;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\RecordTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RecordTemplatesTest extends TestCase
{
    use RefreshDatabase;

    private function createClinicWithOwner(): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->owner()->create(['clinic_id' => $clinic->id]);

        return [$clinic, $owner];
    }

    private function createTemplate(Clinic $clinic, User $user, array $overrides = []): RecordTemplate
    {
        return RecordTemplate::create(array_merge([
            'clinic_id' => $clinic->id,
            'created_by_user_id' => $user->id,
            'name' => 'Consulta general',
            'record_type' => MedicalRecord::TYPE_CONSULTATION,
            'chief_complaint' => 'Motivo de prueba',
            'assessment' => 'Diagnóstico de prueba',
            'plan' => 'Plan de prueba',
            'is_default' => false,
        ], $overrides));
    }

    // ─── Access ───────────────────────────────────────────────────────────────

    public function test_owner_can_view_templates_page(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();

        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(RecordTemplates::class)
            ->assertStatus(200);
    }

    public function test_user_without_permission_cannot_access(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        $receptionist = User::factory()->create([
            'clinic_id' => $clinic->id,
            'role' => 'receptionist',
        ]);
        $receptionist->syncRoles('receptionist');

        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($receptionist)
            ->test(RecordTemplates::class)
            ->assertForbidden();
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function test_owner_can_create_template(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();

        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(RecordTemplates::class)
            ->call('create')
            ->set('name', 'Consulta pediátrica')
            ->set('recordType', MedicalRecord::TYPE_CONSULTATION)
            ->set('specialty', 'Pediatría')
            ->set('chiefComplaint', 'Fiebre')
            ->set('plan', 'Paracetamol 500mg')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('record_templates', [
            'clinic_id' => $clinic->id,
            'name' => 'Consulta pediátrica',
            'specialty' => 'Pediatría',
            'chief_complaint' => 'Fiebre',
        ]);
    }

    public function test_create_requires_name(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();

        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(RecordTemplates::class)
            ->call('create')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    // ─── Edit ────────────────────────────────────────────────────────────────

    public function test_owner_can_edit_template(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        $template = $this->createTemplate($clinic, $owner);

        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(RecordTemplates::class)
            ->call('edit', $template->id)
            ->set('name', 'Nombre actualizado')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('record_templates', [
            'id' => $template->id,
            'name' => 'Nombre actualizado',
        ]);
    }

    // ─── Default flag ────────────────────────────────────────────────────────

    public function test_setting_default_unsets_previous_default_of_same_type(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        $existing = $this->createTemplate($clinic, $owner, ['is_default' => true]);

        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(RecordTemplates::class)
            ->call('create')
            ->set('name', 'Nueva plantilla')
            ->set('recordType', MedicalRecord::TYPE_CONSULTATION)
            ->set('isDefault', true)
            ->call('save');

        $this->assertDatabaseHas('record_templates', ['id' => $existing->id, 'is_default' => false]);
        $this->assertDatabaseHas('record_templates', ['name' => 'Nueva plantilla', 'is_default' => true]);
    }

    // ─── Delete ──────────────────────────────────────────────────────────────

    public function test_owner_can_delete_template(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        $template = $this->createTemplate($clinic, $owner);

        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(RecordTemplates::class)
            ->call('confirmDelete', $template->id)
            ->call('deleteTemplate');

        $this->assertSoftDeleted('record_templates', ['id' => $template->id]);
    }

    // ─── Multi-tenant isolation ───────────────────────────────────────────────

    public function test_cannot_see_templates_from_another_clinic(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        [$otherClinic, $otherOwner] = $this->createClinicWithOwner();

        $otherTemplate = $this->createTemplate($otherClinic, $otherOwner, ['name' => 'Plantilla ajena']);

        app()->instance('current_clinic', $clinic);

        $component = Livewire::actingAs($owner)
            ->test(RecordTemplates::class);

        // Templates list should be empty for $clinic
        $this->assertCount(0, $component->get('templates'));
    }

    // ─── Load template into MedicalRecord/Create ─────────────────────────────

    public function test_template_loads_into_create_form(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $template = $this->createTemplate($clinic, $owner, [
            'chief_complaint' => 'Fiebre y tos',
            'plan' => 'Reposo 3 días',
        ]);

        // Bind current clinic
        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(CreateRecord::class, ['patient' => $patient])
            ->set('selectedTemplateId', $template->id)
            ->call('loadTemplate')
            ->assertSet('chiefComplaint', 'Fiebre y tos')
            ->assertSet('plan', 'Reposo 3 días');
    }

    public function test_cannot_load_template_from_another_clinic(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();
        [$otherClinic, $otherOwner] = $this->createClinicWithOwner();
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $otherTemplate = $this->createTemplate($otherClinic, $otherOwner, [
            'chief_complaint' => 'Datos ajenos',
        ]);

        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(CreateRecord::class, ['patient' => $patient])
            ->set('selectedTemplateId', $otherTemplate->id)
            ->call('loadTemplate')
            ->assertSet('chiefComplaint', ''); // Must stay empty
    }
}
