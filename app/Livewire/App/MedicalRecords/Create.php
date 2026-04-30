<?php

namespace App\Livewire\App\MedicalRecords;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Create extends Component
{
    public Patient $patient;

    public Clinic $clinic;

    public string $clinicSlug = '';

    public ?string $appointmentId = null;

    public string $recordType = MedicalRecord::TYPE_CONSULTATION;

    public string $title = '';

    public string $chiefComplaint = '';

    public string $presentIllness = '';

    public string $physicalExamination = '';

    public string $assessment = '';

    public string $plan = '';

    /** @var array<string,mixed> */
    public array $vitalSigns = [
        'temperature' => '',
        'heart_rate' => '',
        'blood_pressure' => '',
        'respiratory_rate' => '',
        'oxygen_saturation' => '',
        'weight' => '',
        'height' => '',
    ];

    /** @var array<int,array<string,string>> */
    public array $diagnoses = [];

    /** @var array<int,array<string,string>> */
    public array $prescriptions = [];

    public bool $isConfidential = false;

    public function mount(Patient $patient, ?string $appointmentId = null): void
    {
        abort_if($patient->clinic_id !== app('current_clinic')->id, 404);
        abort_unless(auth()->user()->can('records.create'), 403);

        $this->patient = $patient;
        $this->clinic = app('current_clinic');
        $this->clinicSlug = app('current_clinic')->slug;

        // Pre-fill from query string ?appointmentId=xxx
        $appointmentId = $appointmentId ?: request()->query('appointment_id');
        if ($appointmentId) {
            $appointment = Appointment::query()
                ->where('clinic_id', $patient->clinic_id)
                ->where('patient_id', $patient->id)
                ->find($appointmentId);
            if ($appointment) {
                $this->appointmentId = $appointment->id;
                $this->title = __('records.type_consultation').' — '.$appointment->appointment_date->isoFormat('LL');
            }
        }

        // Pre-select prescription type from query string ?type=prescription
        $requestedType = request()->query('type');
        if ($requestedType === MedicalRecord::TYPE_PRESCRIPTION) {
            $this->recordType = MedicalRecord::TYPE_PRESCRIPTION;
            $this->title = __('records.type_prescription').' — '.now()->isoFormat('LL');
            // Start with one empty prescription row to streamline UX
            if (empty($this->prescriptions)) {
                $this->prescriptions = [[
                    'drug' => '',
                    'dosage' => '',
                    'duration' => '',
                    'notes' => '',
                ]];
            }
        }
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
        if (! auth()->user()->can('records.create')) {
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

        $record = MedicalRecord::create([
            'clinic_id' => $this->patient->clinic_id,
            'patient_id' => $this->patient->id,
            'doctor_id' => auth()->id(),
            'appointment_id' => $this->appointmentId,
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

        session()->flash('success', __('records.created'));

        return redirect()->route('app.records.show', [
            'clinic' => $this->clinicSlug,
            'patient' => $this->patient->id,
            'record' => $record->id,
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
        return view('livewire.app.medical-records.create');
    }
}
