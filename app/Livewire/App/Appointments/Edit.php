<?php

namespace App\Livewire\App\Appointments;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DoctorUnavailability;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class Edit extends Component
{
    public Clinic $currentClinic;

    public Appointment $appointment;

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

    // Billing
    public ?string $consultation_price = null;

    public ?string $consultation_discount = null;

    public bool $is_billable = true;

    // UI state
    public string $patientSearch = '';

    public bool $showPatientDropdown = false;

    public bool $doctorUnavailableConflict = false;

    protected function rules(): array
    {
        return [
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['required', 'exists:users,id'],
            'appointment_type' => ['required', 'in:scheduled,walk_in,emergency,follow_up,telemedicine'],
            'appointment_date' => ['required', 'date'],
            'start_time' => ['required_unless:appointment_type,walk_in'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'reason' => ['nullable', 'string', 'max:500'],
            'symptoms' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'room' => ['nullable', 'string', 'max:50'],
            // Billing
            'consultation_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'consultation_discount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'is_billable' => ['boolean'],
        ];
    }

    public function mount(Clinic $clinic, Appointment $appointment): void
    {
        $this->currentClinic = $clinic;
        $this->appointment = $appointment;

        // Verify appointment belongs to clinic
        if ($appointment->clinic_id !== $clinic->id) {
            abort(404);
        }

        // Load data from appointment
        $this->patient_id = $appointment->patient_id;
        $this->doctor_id = (string) $appointment->doctor_id;
        $this->appointment_type = $appointment->appointment_type;
        $this->appointment_date = $appointment->appointment_date->format('Y-m-d');
        $this->start_time = $appointment->start_time ? Carbon::parse($appointment->start_time)->format('H:i') : '';
        $this->duration_minutes = $appointment->duration_minutes;
        $this->reason = $appointment->reason ?? '';
        $this->symptoms = $appointment->symptoms ?? '';
        $this->notes = $appointment->notes ?? '';
        $this->room = $appointment->room ?? '';

        // Billing
        $this->consultation_price = $appointment->consultation_price !== null ? (string) $appointment->consultation_price : null;
        $this->consultation_discount = $appointment->consultation_discount !== null ? (string) $appointment->consultation_discount : null;
        $this->is_billable = $appointment->is_billable ?? true;

        // Set patient search
        if ($appointment->patient) {
            $this->patientSearch = $appointment->patient->full_name;
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
            ->where('id', '!=', $this->appointment->id) // Exclude current appointment
            ->where(function ($query) use ($endTime) {
                $query->where(function ($q) use ($endTime) {
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $this->start_time);
                });
            })
            ->exists();

        if ($conflicts) {
            return true;
        }

        // Check doctor unavailabilities
        $unavailabilities = DoctorUnavailability::query()
            ->forClinic($this->currentClinic->id)
            ->forDoctor((int) $this->doctor_id)
            ->forDate($this->appointment_date)
            ->get();

        foreach ($unavailabilities as $block) {
            if ($block->blocksSlot($this->appointment_date, $this->start_time, $endTime)) {
                $this->doctorUnavailableConflict = true;

                return true;
            }
        }

        return false;
    }

    public function save()
    {
        if (! auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        if (! $this->appointment->isEditable()) {
            session()->flash('error', __('general.action_not_allowed'));

            return;
        }

        $this->validate();

        // Check for conflicts
        $this->doctorUnavailableConflict = false;
        if ($this->checkConflicts()) {
            $msg = $this->doctorUnavailableConflict
                ? __('schedule.doctor_unavailable')
                : __('appointments.conflict_detected');
            session()->flash('error', $msg);

            return;
        }

        $endTime = null;
        if ($this->start_time) {
            $endTime = Carbon::parse($this->start_time)->addMinutes($this->duration_minutes)->format('H:i:s');
        }

        $this->appointment->update([
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'appointment_type' => $this->appointment_type,
            'appointment_date' => $this->appointment_date,
            'start_time' => $this->start_time ?: null,
            'end_time' => $endTime,
            'duration_minutes' => $this->duration_minutes,
            'reason' => $this->reason ?: null,
            'symptoms' => $this->symptoms ?: null,
            'notes' => $this->notes ?: null,
            'room' => $this->room ?: null,
            'consultation_price' => $this->currentClinic->billingEnabled() ? ($this->consultation_price !== '' ? $this->consultation_price : null) : null,
            'consultation_discount' => $this->currentClinic->billingEnabled() ? ($this->consultation_discount !== '' ? $this->consultation_discount : null) : null,
            'is_billable' => $this->currentClinic->billingEnabled() ? $this->is_billable : true,
        ]);

        session()->flash('success', __('appointments.appointment_updated'));

        $this->dispatch('appointmentUpdated');

        return redirect()->route('app.appointments.index', ['clinic' => $this->currentClinic->slug]);
    }

    public function render()
    {
        return view('livewire.app.appointments.edit', [
            'doctors' => $this->doctors,
            'patients' => $this->patients,
            'types' => $this->types,
            'billingEnabled' => $this->currentClinic->billingEnabled(),
            'currency' => $this->currentClinic->currency ?? 'USD',
        ])->layout('layouts.app');
    }
}
