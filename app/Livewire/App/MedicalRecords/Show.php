<?php

namespace App\Livewire\App\MedicalRecords;

use App\Models\Clinic;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public Patient $patient;

    public MedicalRecord $record;

    public Clinic $clinic;

    public string $clinicSlug = '';

    public function mount(Patient $patient, MedicalRecord $record): void
    {
        abort_if($patient->clinic_id !== app('current_clinic')->id, 404);
        abort_if($record->clinic_id !== $patient->clinic_id, 404);
        abort_if($record->patient_id !== $patient->id, 404);
        abort_unless(auth()->user()->can('records.view'), 403);

        if ($record->is_confidential && ! auth()->user()->can('records.view_confidential')) {
            abort(403, __('records.confidential_hidden'));
        }

        $this->clinicSlug = app('current_clinic')->slug;
        $this->clinic = app('current_clinic');
        $this->patient = $patient;
        $this->record = $record->load(['doctor', 'appointment']);
    }

    public function delete()
    {
        if (! auth()->user()->can('records.delete')) {
            session()->flash('error', __('records.permission_denied'));

            return;
        }

        $clinicSlug = $this->clinicSlug;
        $patientId = $this->patient->id;

        $this->record->delete();
        session()->flash('success', __('records.deleted'));

        return redirect()->route('app.records.index', ['clinic' => $clinicSlug, 'patient' => $patientId]);
    }

    public function render()
    {
        return view('livewire.app.medical-records.show');
    }

    public function exportPdf()
    {
        abort_unless(auth()->user()->can('records.print'), 403);

        if ($this->record->is_confidential && ! auth()->user()->can('records.view_confidential')) {
            abort(403, __('records.confidential_hidden'));
        }

        $pdf = Pdf::loadView('pdf.records.show', [
            'clinic' => $this->clinic,
            'patient' => $this->patient,
            'record' => $this->record,
        ])->setPaper('a4', 'portrait');

        $patientSlug = preg_replace('/\s+/', '-', strtolower(trim(
            ($this->patient->first_name ?? '').' '.($this->patient->last_name ?? '')
        ))) ?: 'paciente';

        $filename = 'consulta-'.optional($this->record->created_at)->format('Ymd').'-'.$patientSlug.'.pdf';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    public function exportPrescriptionPdf()
    {
        abort_unless(auth()->user()->can('records.print'), 403);

        if ($this->record->is_confidential && ! auth()->user()->can('records.view_confidential')) {
            abort(403, __('records.confidential_hidden'));
        }

        if (empty($this->record->prescriptions)) {
            abort(404, __('records.no_prescriptions'));
        }

        $pdf = Pdf::loadView('pdf.records.prescription', [
            'clinic' => $this->clinic,
            'patient' => $this->patient,
            'record' => $this->record,
        ])->setPaper('a4', 'portrait');

        $patientSlug = preg_replace('/\s+/', '-', strtolower(trim(
            ($this->patient->first_name ?? '').' '.($this->patient->last_name ?? '')
        ))) ?: 'paciente';

        $filename = 'receta-'.optional($this->record->created_at)->format('Ymd').'-'.$patientSlug.'.pdf';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
