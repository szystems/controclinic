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

    public string $filterHasFiles = '';  // '' | 'yes' | 'no'

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
        $this->reset(['typeFilter', 'statusFilter', 'filterHasFiles']);
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
            ->when($this->filterHasFiles === 'yes', fn ($q) => $q
                ->whereNotNull('attachments')
                ->whereRaw("JSON_LENGTH(attachments) > 0")
            )
            ->when($this->filterHasFiles === 'no', fn ($q) => $q->where(function ($sub) {
                $sub->whereNull('attachments')
                    ->orWhereRaw("JSON_LENGTH(attachments) = 0");
            }))
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
        // STATUS_AMENDED is reserved for the future formal amendment flow
        // (see .context/TASKS.md → "Fase futura: Flujo de enmienda formal").
        // Hidden from filters until that workflow is implemented.
        return [
            MedicalRecord::STATUS_DRAFT,
            MedicalRecord::STATUS_FINAL,
        ];
    }
}
