<?php

namespace App\Livewire\App\MedicalRecords;

use App\Models\MedicalRecord;
use App\Models\Patient;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public Patient $patient;

    public MedicalRecord $record;

    public function mount(Patient $patient, MedicalRecord $record): void
    {
        abort_if($patient->clinic_id !== app('current_clinic')->id, 404);
        abort_if($record->clinic_id !== $patient->clinic_id, 404);
        abort_if($record->patient_id !== $patient->id, 404);
        abort_unless(auth()->user()->can('records.view'), 403);

        if ($record->is_confidential && ! auth()->user()->can('records.view_confidential')) {
            abort(403, __('records.confidential_hidden'));
        }

        $this->patient = $patient;
        $this->record = $record->load(['doctor', 'appointment']);
    }

    public function delete()
    {
        if (! auth()->user()->can('records.delete')) {
            session()->flash('error', __('records.permission_denied'));

            return;
        }

        $clinicSlug = app('current_clinic')->slug;
        $patientId = $this->patient->id;

        $this->record->delete();
        session()->flash('success', __('records.deleted'));

        return redirect()->route('app.records.index', ['clinic' => $clinicSlug, 'patient' => $patientId]);
    }

    public function render()
    {
        return view('livewire.app.medical-records.show');
    }
}
