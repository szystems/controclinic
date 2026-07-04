<?php

namespace App\Livewire\App\Settings;

use App\Models\Clinic;
use App\Models\MedicalRecord;
use App\Models\RecordTemplate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class RecordTemplates extends Component
{
    public Clinic $clinic;

    public string $clinicSlug = '';

    // Modal state
    public bool $showModal = false;

    public ?string $editingId = null;

    // Form fields
    public string $name = '';

    public string $specialty = '';

    public string $recordType = MedicalRecord::TYPE_CONSULTATION;

    public string $chiefComplaint = '';

    public string $presentIllness = '';

    public string $physicalExamination = '';

    public string $assessment = '';

    public string $plan = '';

    public bool $isDefault = false;

    // Delete confirm
    public ?string $confirmDeleteId = null;

    public bool $showSetupGuide = false;

    public function mount(): void
    {
        $this->clinic = app('current_clinic');
        $this->clinicSlug = $this->clinic->slug;
        abort_unless(auth()->user()->can('templates.manage'), 403);

        $this->showSetupGuide = request()->query('from') === 'setup';
    }

    // ==================== COMPUTED ====================

    public function getTemplatesProperty()
    {
        return RecordTemplate::query()
            ->where('clinic_id', $this->clinic->id)
            ->with('createdBy')
            ->orderBy('record_type')
            ->orderBy('name')
            ->get();
    }

    public function getRecordTypesProperty(): array
    {
        return [
            MedicalRecord::TYPE_CONSULTATION,
            MedicalRecord::TYPE_FOLLOW_UP_NOTE,
            MedicalRecord::TYPE_PRESCRIPTION,
            MedicalRecord::TYPE_PROCEDURE,
            MedicalRecord::TYPE_REFERRAL,
            MedicalRecord::TYPE_OTHER,
        ];
    }

    // ==================== OPEN / CLOSE ====================

    public function create(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
        $this->showSetupGuide = false;
    }

    public function createSuggested(): void
    {
        abort_unless(auth()->user()->can('templates.manage'), 403);

        if (RecordTemplate::where('clinic_id', $this->clinic->id)->exists()) {
            return;
        }

        RecordTemplate::create([
            'clinic_id' => $this->clinic->id,
            'created_by_user_id' => auth()->id(),
            'name' => __('templates.suggested_name'),
            'record_type' => MedicalRecord::TYPE_CONSULTATION,
            'chief_complaint' => __('templates.suggested_chief_complaint'),
            'present_illness' => __('templates.suggested_present_illness'),
            'physical_examination' => __('templates.suggested_physical_examination'),
            'assessment' => __('templates.suggested_assessment'),
            'plan' => __('templates.suggested_plan'),
            'is_default' => true,
        ]);

        $this->showSetupGuide = false;
        session()->flash('success', __('templates.suggested_created'));
    }

    public function dismissSetupGuide(): void
    {
        $this->showSetupGuide = false;
    }

    public function edit(string $id): void
    {
        $template = RecordTemplate::where('clinic_id', $this->clinic->id)->findOrFail($id);
        $this->editingId = $template->id;
        $this->name = $template->name;
        $this->specialty = $template->specialty ?? '';
        $this->recordType = $template->record_type;
        $this->chiefComplaint = $template->chief_complaint ?? '';
        $this->presentIllness = $template->present_illness ?? '';
        $this->physicalExamination = $template->physical_examination ?? '';
        $this->assessment = $template->assessment ?? '';
        $this->plan = $template->plan ?? '';
        $this->isDefault = $template->is_default;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    // ==================== SAVE ====================

    public function save(): void
    {
        abort_unless(auth()->user()->can('templates.manage'), 403);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'specialty' => ['nullable', 'string', 'max:100'],
            'recordType' => ['required', 'string', 'max:50'],
            'chiefComplaint' => ['nullable', 'string', 'max:2000'],
            'presentIllness' => ['nullable', 'string', 'max:5000'],
            'physicalExamination' => ['nullable', 'string', 'max:5000'],
            'assessment' => ['nullable', 'string', 'max:5000'],
            'plan' => ['nullable', 'string', 'max:5000'],
            'isDefault' => ['boolean'],
        ]);

        // If setting as default, unset others with same record_type
        if ($data['isDefault']) {
            RecordTemplate::where('clinic_id', $this->clinic->id)
                ->where('record_type', $data['recordType'])
                ->where('id', '!=', $this->editingId ?? '')
                ->update(['is_default' => false]);
        }

        $payload = [
            'clinic_id' => $this->clinic->id,
            'name' => $data['name'],
            'specialty' => $data['specialty'] ?: null,
            'record_type' => $data['recordType'],
            'chief_complaint' => $data['chiefComplaint'] ?: null,
            'present_illness' => $data['presentIllness'] ?: null,
            'physical_examination' => $data['physicalExamination'] ?: null,
            'assessment' => $data['assessment'] ?: null,
            'plan' => $data['plan'] ?: null,
            'is_default' => (bool) $data['isDefault'],
        ];

        if ($this->editingId) {
            $template = RecordTemplate::where('clinic_id', $this->clinic->id)->findOrFail($this->editingId);
            $template->update($payload);
            session()->flash('success', __('templates.updated'));
        } else {
            RecordTemplate::create(array_merge($payload, [
                'created_by_user_id' => auth()->id(),
            ]));
            session()->flash('success', __('templates.created'));
        }

        $this->closeModal();
    }

    // ==================== SETUP GUIDE ====================

    public function getShowSetupGuideBannerProperty(): bool
    {
        return $this->showSetupGuide && $this->templates->isEmpty();
    }

    // ==================== DELETE ====================

    public function confirmDelete(string $id): void
    {
        $this->confirmDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmDeleteId = null;
    }

    public function deleteTemplate(): void
    {
        abort_unless(auth()->user()->can('templates.manage'), 403);

        $template = RecordTemplate::where('clinic_id', $this->clinic->id)
            ->findOrFail($this->confirmDeleteId);

        $template->delete();
        $this->confirmDeleteId = null;
        session()->flash('success', __('templates.deleted'));
    }

    // ==================== HELPERS ====================

    private function resetForm(): void
    {
        $this->name = '';
        $this->specialty = '';
        $this->recordType = MedicalRecord::TYPE_CONSULTATION;
        $this->chiefComplaint = '';
        $this->presentIllness = '';
        $this->physicalExamination = '';
        $this->assessment = '';
        $this->plan = '';
        $this->isDefault = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.app.settings.record-templates');
    }
}
