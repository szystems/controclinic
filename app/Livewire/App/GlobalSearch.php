<?php

namespace App\Livewire\App;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\View\View;
use Livewire\Component;

class GlobalSearch extends Component
{
    public Clinic $currentClinic;

    public string $query = '';

    public function mount(Clinic $clinic): void
    {
        $this->currentClinic = $clinic;
    }

    public function getResultsProperty(): array
    {
        if (mb_strlen($this->query) < 2) {
            return [];
        }

        $results = [];
        $q = $this->query;
        $user = auth()->user();

        // Patients
        if ($user->can('patients.view')) {
            $patients = Patient::query()
                ->where('clinic_id', $this->currentClinic->id)
                ->where(function ($query) use ($q) {
                    $query->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('medical_record_number', 'like', "%{$q}%");
                })
                ->limit(5)
                ->get();

            foreach ($patients as $patient) {
                $results[] = [
                    'type' => 'patient',
                    'title' => $patient->full_name,
                    'subtitle' => $patient->email ?? $patient->phone ?? '',
                    'meta' => $patient->medical_record_number ?? '',
                    'url' => route('app.patients.show', [$this->currentClinic, $patient]),
                ];
            }
        }

        // Appointments
        if ($user->can('appointments.view')) {
            $appointments = Appointment::query()
                ->where('clinic_id', $this->currentClinic->id)
                ->where(function ($query) use ($q) {
                    $query->whereHas('patient', function ($sub) use ($q) {
                        $sub->where('first_name', 'like', "%{$q}%")
                            ->orWhere('last_name', 'like', "%{$q}%");
                    })->orWhere('reason', 'like', "%{$q}%");
                })
                ->with(['patient', 'doctor'])
                ->orderByDesc('appointment_date')
                ->limit(5)
                ->get();

            foreach ($appointments as $appointment) {
                $date = $appointment->appointment_date?->translatedFormat('d M Y') ?? '';
                $doctor = $appointment->doctor?->name ? ' · '.$appointment->doctor->name : '';

                $results[] = [
                    'type' => 'appointment',
                    'title' => $appointment->patient?->full_name ?? __('appointments.no_patient'),
                    'subtitle' => trim($date.$doctor),
                    'meta' => $appointment->status,
                    'url' => route('app.appointments.show', [$this->currentClinic, $appointment]),
                ];
            }
        }

        // Medical Records
        if ($user->can('records.view')) {
            $records = MedicalRecord::query()
                ->where('clinic_id', $this->currentClinic->id)
                ->where(function ($query) use ($q) {
                    $query->where('title', 'like', "%{$q}%")
                        ->orWhere('chief_complaint', 'like', "%{$q}%");
                })
                ->when(! $user->can('records.view_confidential'), fn ($query) => $query->where('is_confidential', false))
                ->with(['patient'])
                ->orderByDesc('created_at')
                ->limit(3)
                ->get();

            foreach ($records as $record) {
                if (! $record->patient) {
                    continue;
                }

                $results[] = [
                    'type' => 'record',
                    'title' => $record->title,
                    'subtitle' => $record->patient->full_name,
                    'meta' => $record->record_type,
                    'url' => route('app.records.show', [$this->currentClinic, $record->patient, $record]),
                ];
            }
        }

        return $results;
    }

    public function render(): View
    {
        return view('livewire.app.global-search', [
            'results' => $this->results,
        ]);
    }
}
