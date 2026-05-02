<?php

namespace App\Livewire\App\Appointments;

use App\Jobs\SendAppointmentNotification;
use App\Models\Appointment;
use App\Models\AppointmentComment;
use App\Models\Clinic;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;

class Show extends Component
{
    public Clinic $currentClinic;

    public Appointment $appointment;

    public bool $showCancelModal = false;

    public string $cancellationReason = '';

    public string $newComment = '';

    public function mount(Clinic $clinic, Appointment $appointment): void
    {
        $this->currentClinic = $clinic;
        $this->appointment = $appointment->load(['patient', 'doctor', 'createdBy', 'comments.user']);

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

    public function sendEmailReminder(): void
    {
        if (! auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $patient = $this->appointment->patient;

        if (! $patient?->email) {
            session()->flash('error', __('appointments.reminder_no_email'));

            return;
        }

        SendAppointmentNotification::dispatch($this->appointment->id, SendAppointmentNotification::TYPE_REMINDER);

        session()->flash('success', __('appointments.reminder_sent'));
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

    public function addComment(): void
    {
        if (! auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $this->validate(['newComment' => ['required', 'string', 'max:2000']]);

        AppointmentComment::create([
            'appointment_id' => $this->appointment->id,
            'user_id' => auth()->id(),
            'clinic_id' => $this->currentClinic->id,
            'body' => trim($this->newComment),
        ]);

        $this->newComment = '';
        $this->appointment->load('comments.user');
    }

    public function deleteComment(string $commentId): void
    {
        if (! auth()->user()->can('appointments.edit')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $comment = AppointmentComment::query()
            ->forClinic($this->currentClinic->id)
            ->where('id', $commentId)
            ->first();

        if (! $comment) {
            return; // silently ignore — not found in this clinic
        }

        // Only the author or owner/admin can delete
        if ($comment->user_id !== auth()->id() && ! auth()->user()->hasAnyRole(['owner', 'admin'])) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $comment->delete();
        $this->appointment->load('comments.user');
    }

    public function render()
    {
        return view('livewire.app.appointments.show')
            ->layout('layouts.app');
    }

    public function exportPdf()
    {
        abort_unless(auth()->user()->can('appointments.print'), 403);

        $pdf = Pdf::loadView('pdf.appointments.show', [
            'clinic' => $this->currentClinic,
            'appointment' => $this->appointment,
        ])->setPaper('a4', 'portrait');

        $patientSlug = preg_replace('/\s+/', '-', strtolower(trim(
            ($this->appointment->patient?->first_name ?? '').' '.($this->appointment->patient?->last_name ?? '')
        ))) ?: 'paciente';

        $filename = 'cita-'.$this->appointment->appointment_date?->format('Ymd').'-'.$patientSlug.'.pdf';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
