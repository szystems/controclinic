<?php

namespace App\Livewire\App\Appointments;

use App\Models\Appointment;
use App\Models\Clinic;
use Livewire\Component;

class Show extends Component
{
    public Clinic $currentClinic;

    public Appointment $appointment;

    public bool $showCancelModal = false;

    public string $cancellationReason = '';

    public function mount(Clinic $clinic, Appointment $appointment): void
    {
        $this->currentClinic = $clinic;
        $this->appointment = $appointment->load(['patient', 'doctor', 'createdBy']);

        // Verify appointment belongs to clinic
        if ($appointment->clinic_id !== $clinic->id) {
            abort(404);
        }
    }

    // Workflow Actions
    public function confirmAppointment(): void
    {
        if (! auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $this->appointment->confirm();
        $this->appointment->refresh();
        session()->flash('success', __('appointments.appointment_confirmed'));
    }

    public function checkIn(): void
    {
        if (! auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        // Si está scheduled, primero confirmar
        if ($this->appointment->status === Appointment::STATUS_SCHEDULED) {
            $this->appointment->confirm();
        }

        $this->appointment->checkIn();
        $this->appointment->refresh();
        session()->flash('success', __('appointments.check_in').' ✓');
    }

    public function startConsultation(): void
    {
        if (! auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        if (! $this->appointment->canStart()) {
            session()->flash('error', __('general.action_not_allowed'));

            return;
        }

        $this->appointment->start();
        $this->appointment->refresh();
        session()->flash('success', __('appointments.start_consultation').' ✓');
    }

    public function completeAppointment(): void
    {
        if (! auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        if (! $this->appointment->canComplete()) {
            session()->flash('error', __('general.action_not_allowed'));

            return;
        }

        $this->appointment->complete();
        $this->appointment->refresh();
        session()->flash('success', __('appointments.appointment_updated'));
    }

    public function openCancelModal(): void
    {
        $this->showCancelModal = true;
    }

    public function closeCancelModal(): void
    {
        $this->showCancelModal = false;
        $this->cancellationReason = '';
    }

    public function cancelAppointment(): void
    {
        if (! auth()->user()->can('appointments.delete')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        if (! $this->appointment->isCancellable()) {
            session()->flash('error', __('general.action_not_allowed'));

            return;
        }

        $this->appointment->cancel($this->cancellationReason ?: null);
        $this->appointment->refresh();
        $this->closeCancelModal();
        session()->flash('success', __('appointments.appointment_cancelled'));
    }

    public function markNoShow(): void
    {
        if (! auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $this->appointment->markAsNoShow();
        $this->appointment->refresh();
        session()->flash('success', __('appointments.appointment_updated'));
    }

    public function render()
    {
        return view('livewire.app.appointments.show')
            ->layout('layouts.app');
    }
}
