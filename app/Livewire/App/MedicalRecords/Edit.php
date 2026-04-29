<?php

namespace App\Livewire\App\MedicalRecords;

use App\Models\MedicalRecord;
use App\Models\Patient;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Edit extends Component
{
    public Patient $patient;

    public MedicalRecord $record;

    public string $recordType = MedicalRecord::TYPE_CONSULTATION;

    public string $title = '';

    public string $chiefComplaint = '';

    public string $presentIllness = '';

    public string $physicalExamination = '';

    public string $assessment = '';

    public string $plan = '';

    public array $vitalSigns = [
        'temperature' => '',
        'heart_rate' => '',
        'blood_pressure' => '',
        'respiratory_rate' => '',
        'oxygen_saturation' => '',
        'weight' => '',
        'height' => '',
    ];

    public array $diagnoses = [];

    public array $prescriptions = [];

    public bool $isConfidential = false;

    public function mount(Patient $patient, MedicalRecord $record): void
    {
        abort_if($patient->clinic_id !== app('current_clinic')->id, 404);
        abort_if($record->clinic_id !== $patient->clinic_id, 404);
        abort_if($record->patient_id !== $patient->id, 404);
        abort_unless(auth()->user()->can('records.edit'), 403);

        if ($record->status !== MedicalRecord::STATUS_DRAFT) {
            session()->flash('error', __('records.cannot_edit_finalized'));
            redirect()->route('app.records.show', [
                'clinic' => app('current_clinic')->slug,
                'patient' => $patient->id,
                'record' => $record->id,
            ]);

            return;
        }

        $this->patient = $patient;
        $this->record = $record;
        $this->recordType = $record->record_type;
        $this->title = (string) $record->title;
        $this->chiefComplaint = (string) $record->chief_complaint;
        $this->presentIllness = (string) $record->present_illness;
        $this->physicalExamination = (string) $record->physical_examination;
        $this->assessment = (string) $record->assessment;
        $this->plan = (string) $record->plan;
        $this->vitalSigns = array_merge($this->vitalSigns, $record->vital_signs ?? []);
        $this->diagnoses = $record->diagnoses ?? [];
        $this->prescriptions = $record->prescriptions ?? [];
        $this->isConfidential = (bool) $record->is_confidential;
    }

    public function addDiagnosis(): void
    {
        $this->diagnoses[] = ['code' => '', 'description' => ''];
    }

    public function removeDiagnosis(int $index): void
    {
        unset($this->diagnoses[$index]);
        $this->diagnoses = array_values($this->diagnoses);
    }

    public function addPrescription(): void
    {
        $this->prescriptions[] = ['drug' => '', 'dosage' => '', 'duration' => '', 'notes' => ''];
    }

    public function removePrescription(int $index): void
    {
        unset($this->prescriptions[$index]);
        $this->prescriptions = array_values($this->prescriptions);
    }

    public function saveDraft()
    {
        return $this->save(MedicalRecord::STATUS_DRAFT);
    }

    public function saveFinal()
    {
        return $this->save(MedicalRecord::STATUS_FINAL);
    }

    private function save(string $status)
    {
        if (! auth()->user()->can('records.edit')) {
            abort(403);
        }

        $data = $this->validate([
            'recordType' => ['required', 'string', 'max:50'],
            'title' => ['nullable', 'string', 'max:255'],
            'chiefComplaint' => ['nullable', 'string', 'max:2000'],
            'presentIllness' => ['nullable', 'string', 'max:5000'],
            'physicalExamination' => ['nullable', 'string', 'max:5000'],
            'assessment' => ['nullable', 'string', 'max:5000'],
            'plan' => ['nullable', 'string', 'max:5000'],
            'isConfidential' => ['boolean'],
        ]);

        $vitals = array_filter($this->vitalSigns, fn ($v) => $v !== '' && $v !== null);
        $diagnoses = array_values(array_filter(
            $this->diagnoses,
            fn ($d) => ! empty($d['description']) || ! empty($d['code'])
        ));
        $prescriptions = array_values(array_filter(
            $this->prescriptions,
            fn ($p) => ! empty($p['drug'])
        ));

        $this->record->update([
            'record_type' => $data['recordType'],
            'title' => $data['title'] ?: null,
            'chief_complaint' => $data['chiefComplaint'] ?: null,
            'present_illness' => $data['presentIllness'] ?: null,
            'physical_examination' => $data['physicalExamination'] ?: null,
            'assessment' => $data['assessment'] ?: null,
            'plan' => $data['plan'] ?: null,
            'vital_signs' => $vitals ?: null,
            'diagnoses' => $diagnoses ?: null,
            'prescriptions' => $prescriptions ?: null,
            'is_confidential' => (bool) ($data['isConfidential'] ?? false),
            'status' => $status,
            'finalized_at' => $status === MedicalRecord::STATUS_FINAL ? now() : null,
        ]);

        session()->flash('success', __('records.updated'));

        return redirect()->route('app.records.show', [
            'clinic' => app('current_clinic')->slug,
            'patient' => $this->patient->id,
            'record' => $this->record->id,
        ]);
    }

    public function getRecordTypesProperty(): array
    {
        return [
            MedicalRecord::TYPE_CONSULTATION,
            MedicalRecord::TYPE_FOLLOW_UP_NOTE,
            MedicalRecord::TYPE_PRESCRIPTION,
            MedicalRecord::TYPE_LAB_RESULT,
            MedicalRecord::TYPE_IMAGING,
            MedicalRecord::TYPE_PROCEDURE,
            MedicalRecord::TYPE_REFERRAL,
            MedicalRecord::TYPE_VITAL_SIGNS,
            MedicalRecord::TYPE_VACCINATION,
            MedicalRecord::TYPE_OTHER,
        ];
    }

    public function render()
    {
        return view('livewire.app.medical-records.edit');
    }
}
