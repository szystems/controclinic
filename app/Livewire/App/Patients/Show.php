<?php

namespace App\Livewire\App\Patients;

use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;

class Show extends Component
{
    public Patient $patient;

    public bool $showDeleteModal = false;

    public function mount(Patient $patient): void
    {
        // Tenant isolation
        abort_if($patient->clinic_id !== app('current_clinic')->id, 404);

        $this->patient = $patient->load(['primaryDoctor', 'appointments', 'medicalRecords']);
    }

    public function confirmDelete(): void
    {
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
    }

    public function deletePatient()
    {
        if (! auth()->user()->can('patients.delete')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $this->patient->delete();

        session()->flash('success', __('patients.deleted_successfully'));

        return redirect()->route('app.patients.index', ['clinic' => auth()->user()->clinic->slug]);
    }

    public function toggleStatus(): void
    {
        if (! auth()->user()->can('patients.edit')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $this->patient->update(['is_active' => ! $this->patient->is_active]);
        $this->patient->refresh();

        session()->flash('success', __('patients.status_updated'));
    }

    public function getUpcomingAppointmentsProperty()
    {
        return $this->patient->appointments()
            ->where('appointment_date', '>=', now()->toDateString())
            ->whereNotIn('status', ['cancelled', 'completed', 'no_show'])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->limit(5)
            ->get();
    }

    public function getRecentRecordsProperty()
    {
        return $this->patient->medicalRecords()
            ->with('doctor')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function exportPdf()
    {
        abort_unless(auth()->user()->can('patients.print'), 403);

        $appointments = $this->patient->appointments()
            ->with('doctor')
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->limit(20)
            ->get();

        $pdf = Pdf::loadView('pdf.patients.show', [
            'clinic' => $this->patient->clinic,
            'patient' => $this->patient,
            'appointments' => $appointments,
        ])->setPaper('a4', 'portrait');

        $filename = 'paciente-'.preg_replace('/[^A-Za-z0-9_-]/', '_', strtolower(trim($this->patient->first_name.'-'.$this->patient->last_name))).'.pdf';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    public function render()
    {
        return view('livewire.app.patients.show', [
            'upcomingAppointments' => $this->upcomingAppointments,
            'recentRecords' => $this->recentRecords,
        ])->layout('layouts.app');
    }
}
