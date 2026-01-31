<?php

namespace App\Livewire\App\Appointments;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination;

    public Clinic $currentClinic;
    public string $search = '';
    public string $status = '';
    public string $doctorId = '';
    public string $dateFilter = '';
    public string $sortField = 'appointment_date';
    public string $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'doctorId' => ['except' => ''],
        'dateFilter' => ['except' => ''],
    ];

    protected $listeners = [
        'appointmentCreated' => '$refresh',
        'appointmentUpdated' => '$refresh',
        'appointmentDeleted' => '$refresh',
    ];

    public function mount(Clinic $clinic): void
    {
        $this->currentClinic = $clinic;
        $this->dateFilter = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingDoctorId(): void
    {
        $this->resetPage();
    }

    public function updatingDateFilter(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'doctorId']);
        $this->dateFilter = now()->toDateString();
        $this->resetPage();
    }

    public function showToday(): void
    {
        $this->dateFilter = now()->toDateString();
        $this->resetPage();
    }

    public function getDoctorsProperty()
    {
        return User::where('clinic_id', $this->currentClinic->id)
            ->whereIn('role', ['doctor', 'owner'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getAppointmentsProperty()
    {
        return Appointment::query()
            ->forClinic($this->currentClinic->id)
            ->with(['patient', 'doctor'])
            ->when($this->search, function ($query) {
                $query->whereHas('patient', function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->doctorId, function ($query) {
                $query->where('doctor_id', $this->doctorId);
            })
            ->when($this->dateFilter, function ($query) {
                $query->forDate($this->dateFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('start_time', 'asc')
            ->paginate(15);
    }

    // Workflow Actions
    public function confirmAppointment(string $id): void
    {
        $appointment = Appointment::findOrFail($id);

        if (!auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));
            return;
        }

        $appointment->confirm();
        session()->flash('success', __('appointments.appointment_confirmed'));
    }

    public function checkIn(string $id): void
    {
        $appointment = Appointment::findOrFail($id);

        if (!auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));
            return;
        }

        if (!$appointment->canCheckIn() && $appointment->status !== Appointment::STATUS_SCHEDULED) {
            session()->flash('error', __('general.action_not_allowed'));
            return;
        }

        // Si está scheduled, primero confirmar
        if ($appointment->status === Appointment::STATUS_SCHEDULED) {
            $appointment->confirm();
        }

        $appointment->checkIn();
        session()->flash('success', __('appointments.check_in') . ' ✓');
    }

    public function startConsultation(string $id): void
    {
        $appointment = Appointment::findOrFail($id);

        if (!auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));
            return;
        }

        if (!$appointment->canStart()) {
            session()->flash('error', __('general.action_not_allowed'));
            return;
        }

        $appointment->start();
        session()->flash('success', __('appointments.start_consultation') . ' ✓');
    }

    public function completeAppointment(string $id): void
    {
        $appointment = Appointment::findOrFail($id);

        if (!auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));
            return;
        }

        if (!$appointment->canComplete()) {
            session()->flash('error', __('general.action_not_allowed'));
            return;
        }

        $appointment->complete();
        session()->flash('success', __('appointments.appointment_updated'));
    }

    public function cancelAppointment(string $id): void
    {
        $appointment = Appointment::findOrFail($id);

        if (!auth()->user()->can('appointments.delete')) {
            session()->flash('error', __('general.unauthorized'));
            return;
        }

        if (!$appointment->isCancellable()) {
            session()->flash('error', __('general.action_not_allowed'));
            return;
        }

        $appointment->cancel();
        session()->flash('success', __('appointments.appointment_cancelled'));
    }

    public function markNoShow(string $id): void
    {
        $appointment = Appointment::findOrFail($id);

        if (!auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));
            return;
        }

        $appointment->markAsNoShow();
        session()->flash('success', __('appointments.appointment_updated'));
    }

    public function render()
    {
        return view('livewire.app.appointments.index', [
            'appointments' => $this->appointments,
            'doctors' => $this->doctors,
            'statuses' => [
                Appointment::STATUS_SCHEDULED => __('appointments.status_scheduled'),
                Appointment::STATUS_CONFIRMED => __('appointments.status_confirmed'),
                Appointment::STATUS_WAITING => __('appointments.status_waiting'),
                Appointment::STATUS_IN_PROGRESS => __('appointments.status_in_progress'),
                Appointment::STATUS_COMPLETED => __('appointments.status_completed'),
                Appointment::STATUS_CANCELLED => __('appointments.status_cancelled'),
                Appointment::STATUS_NO_SHOW => __('appointments.status_no_show'),
            ],
        ])->layout('layouts.app');
    }
}
