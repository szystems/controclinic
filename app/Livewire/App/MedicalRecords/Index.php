<?php

namespace App\Livewire\App\MedicalRecords;

use App\Models\Clinic;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public Patient $patient;

    public Clinic $clinic;

    public string $typeFilter = '';

    public string $statusFilter = '';

    public function mount(Patient $patient): void
    {
        abort_if($patient->clinic_id !== app('current_clinic')->id, 404);
        abort_unless(auth()->user()->can('records.view'), 403);

        $this->patient = $patient;
        $this->clinic = app('current_clinic');
    }

    public function updating(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['typeFilter', 'statusFilter']);
        $this->resetPage();
    }

    public function getRecordsProperty()
    {
        $user = auth()->user();
        $canViewConfidential = $user->can('records.view_confidential');

        return MedicalRecord::query()
            ->where('clinic_id', $this->patient->clinic_id)
            ->where('patient_id', $this->patient->id)
            ->when(! $canViewConfidential, fn ($q) => $q->where('is_confidential', false))
            ->when($this->typeFilter, fn ($q) => $q->where('record_type', $this->typeFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->with('doctor:id,name')
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.app.medical-records.index', [
            'records' => $this->getRecordsProperty(),
            'recordTypes' => $this->recordTypes(),
            'recordStatuses' => $this->recordStatuses(),
        ]);
    }

    private function recordTypes(): array
    {
        return [
            MedicalRecord::TYPE_CONSULTATION,
            MedicalRecord::TYPE_DIAGNOSIS,
            MedicalRecord::TYPE_PRESCRIPTION,
            MedicalRecord::TYPE_LAB_RESULT,
            MedicalRecord::TYPE_IMAGING,
            MedicalRecord::TYPE_PROCEDURE,
            MedicalRecord::TYPE_SURGERY,
            MedicalRecord::TYPE_REFERRAL,
            MedicalRecord::TYPE_FOLLOW_UP_NOTE,
            MedicalRecord::TYPE_VITAL_SIGNS,
            MedicalRecord::TYPE_VACCINATION,
            MedicalRecord::TYPE_OTHER,
        ];
    }

    private function recordStatuses(): array
    {
        return [
            MedicalRecord::STATUS_DRAFT,
            MedicalRecord::STATUS_FINAL,
            MedicalRecord::STATUS_AMENDED,
        ];
    }
}
