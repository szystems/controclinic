<?php

namespace App\Livewire\App\Appointments;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class Create extends Component
{
    public Clinic $currentClinic;

    // Form fields
    public string $patient_id = '';

    public string $doctor_id = '';

    public string $appointment_type = 'scheduled';

    public string $appointment_date = '';

    public string $start_time = '';

    public int $duration_minutes = 30;

    public string $reason = '';

    public string $symptoms = '';

    public string $notes = '';

    public string $room = '';

    // UI state
    public string $patientSearch = '';

    public bool $showPatientDropdown = false;

    protected function rules(): array
    {
        return [
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['required', 'exists:users,id'],
            'appointment_type' => ['required', 'in:scheduled,walk_in,emergency,follow_up,telemedicine'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required_unless:appointment_type,walk_in'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'reason' => ['nullable', 'string', 'max:500'],
            'symptoms' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'room' => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function messages(): array
    {
        return [
            'patient_id.required' => __('validation.required', ['attribute' => __('appointments.patient')]),
            'doctor_id.required' => __('validation.required', ['attribute' => __('appointments.doctor')]),
            'appointment_date.required' => __('validation.required', ['attribute' => __('appointments.date')]),
            'appointment_date.after_or_equal' => __('validation.after_or_equal', ['attribute' => __('appointments.date'), 'date' => __('appointments.today')]),
            'start_time.required_unless' => __('validation.required', ['attribute' => __('appointments.start_time')]),
        ];
    }

    public function mount(Clinic $clinic): void
    {
        $this->currentClinic = $clinic;
        $this->appointment_date = now()->toDateString();
        $this->duration_minutes = $clinic->settings['appointment_duration'] ?? 30;

        // Pre-fill from calendar dateClick (?date=YYYY-MM-DD&time=HH:MM)
        if (request()->filled('date')) {
            try {
                $this->appointment_date = Carbon::parse(request('date'))->toDateString();
            } catch (\Throwable) {
                // ignore invalid query value
            }
        }
        if (request()->filled('time')) {
            $time = request('time');
            if (preg_match('/^\d{2}:\d{2}$/', $time)) {
                $this->start_time = $time;
            }
        }

        // Pre-seleccionar doctor si solo hay uno
        $doctors = $this->doctors;
        if ($doctors->count() === 1) {
            $this->doctor_id = (string) $doctors->first()->id;
        }

        // Pre-seleccionar paciente si viene por query string
        if (request()->has('patient')) {
            $patient = Patient::find(request('patient'));
            if ($patient && $patient->clinic_id === $clinic->id) {
                $this->patient_id = $patient->id;
                $this->patientSearch = $patient->full_name;
            }
        }
    }

    public function getDoctorsProperty()
    {
        return User::where('clinic_id', $this->currentClinic->id)
            ->whereIn('role', ['doctor', 'owner'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getPatientsProperty()
    {
        if (strlen($this->patientSearch) < 2) {
            return collect();
        }

        return Patient::where('clinic_id', $this->currentClinic->id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('first_name', 'like', '%'.$this->patientSearch.'%')
                    ->orWhere('last_name', 'like', '%'.$this->patientSearch.'%')
                    ->orWhere('phone', 'like', '%'.$this->patientSearch.'%')
                    ->orWhere('email', 'like', '%'.$this->patientSearch.'%');
            })
            ->limit(10)
            ->get();
    }

    public function getTypesProperty(): array
    {
        return [
            Appointment::TYPE_SCHEDULED => __('appointments.scheduled'),
            Appointment::TYPE_WALK_IN => __('appointments.walk_in'),
            Appointment::TYPE_EMERGENCY => __('appointments.emergency'),
            Appointment::TYPE_FOLLOW_UP => __('appointments.follow_up'),
            Appointment::TYPE_TELEMEDICINE => __('appointments.telemedicine'),
        ];
    }

    public function updatedPatientSearch(): void
    {
        $this->showPatientDropdown = strlen($this->patientSearch) >= 2;
        if (strlen($this->patientSearch) < 2) {
            $this->patient_id = '';
        }
    }

    public function selectPatient(string $id): void
    {
        $patient = Patient::find($id);
        if ($patient) {
            $this->patient_id = $patient->id;
            $this->patientSearch = $patient->full_name;
            $this->showPatientDropdown = false;
        }
    }

    public function checkConflicts(): bool
    {
        if (! $this->doctor_id || ! $this->appointment_date || ! $this->start_time) {
            return false;
        }

        $endTime = Carbon::parse($this->start_time)->addMinutes($this->duration_minutes)->format('H:i');

        $conflicts = Appointment::query()
            ->forClinic($this->currentClinic->id)
            ->forDoctor((int) $this->doctor_id)
            ->forDate($this->appointment_date)
            ->active()
            ->where(function ($query) use ($endTime) {
                $query->where(function ($q) use ($endTime) {
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $this->start_time);
                });
            })
            ->exists();

        return $conflicts;
    }

    public function save()
    {
        if (! auth()->user()->can('appointments.create')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        if (! $this->currentClinic->canAddAppointmentThisMonth()) {
            session()->flash('error', __('appointments.limit_reached'));

            return;
        }

        $this->validate();

        // Check for conflicts
        if ($this->checkConflicts()) {
            session()->flash('error', __('appointments.conflict_detected'));

            return;
        }

        $endTime = null;
        if ($this->start_time) {
            $endTime = Carbon::parse($this->start_time)->addMinutes($this->duration_minutes)->format('H:i:s');
        }

        $appointment = Appointment::create([
            'clinic_id' => $this->currentClinic->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'created_by' => auth()->id(),
            'appointment_type' => $this->appointment_type,
            'appointment_date' => $this->appointment_date,
            'start_time' => $this->start_time ?: null,
            'end_time' => $endTime,
            'duration_minutes' => $this->duration_minutes,
            'status' => Appointment::STATUS_SCHEDULED,
            'reason' => $this->reason ?: null,
            'symptoms' => $this->symptoms ?: null,
            'notes' => $this->notes ?: null,
            'room' => $this->room ?: null,
        ]);

        session()->flash('success', __('appointments.appointment_created'));

        $this->dispatch('appointmentCreated');

        return redirect()->route('app.appointments.index', ['clinic' => $this->currentClinic->slug]);
    }

    public function render()
    {
        return view('livewire.app.appointments.create', [
            'doctors' => $this->doctors,
            'patients' => $this->patients,
            'types' => $this->types,
        ])->layout('layouts.app');
    }
}
