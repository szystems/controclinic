<?php

namespace App\Livewire\App\Appointments;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Calendar extends Component
{
    public Clinic $clinic;

    public string $clinicSlug = '';

    /** @var array<int, string> */
    public array $selectedDoctors = [];

    public string $initialView = 'dayGridMonth';

    public function mount(Clinic $clinic): void
    {
        $this->clinic = $clinic;
        $this->clinicSlug = $clinic->slug;
    }

    /**
     * Doctors list (for filter chips and color legend).
     *
     * @return Collection<int, array{id:int,name:string,color:string}>
     */
    public function getDoctorsProperty()
    {
        return User::where('clinic_id', $this->clinic->id)
            ->whereIn('role', ['doctor', 'owner'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'color' => $this->colorForDoctor($u->id),
            ]);
    }

    /**
     * Endpoint consumed by FullCalendar `events` callback.
     * Returns appointments for the visible range (start..end) for this clinic,
     * filtered by selected doctors when any are selected.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchEvents(string $start, string $end): array
    {
        $startDate = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->endOfDay();

        $query = Appointment::query()
            ->forClinic($this->clinic->id)
            ->with(['patient:id,first_name,last_name', 'doctor:id,name'])
            ->whereBetween('appointment_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if (! empty($this->selectedDoctors)) {
            $query->whereIn('doctor_id', $this->selectedDoctors);
        }

        return $query->get()->map(function (Appointment $a) {
            $date = $a->appointment_date->toDateString();

            $startTime = $a->start_time ? Carbon::parse($a->start_time)->format('H:i:s') : '09:00:00';
            $endTime = $a->end_time ? Carbon::parse($a->end_time)->format('H:i:s') : null;

            $startISO = $date.'T'.$startTime;
            $endISO = $endTime ? $date.'T'.$endTime : null;

            $patientName = $a->patient
                ? trim(($a->patient->first_name ?? '').' '.($a->patient->last_name ?? ''))
                : __('appointments.no_patient');

            $color = $this->colorForDoctor($a->doctor_id ?? 0);
            $statusColor = $this->statusColor($a->status);

            return [
                'id' => (string) $a->id,
                'title' => $patientName,
                'start' => $startISO,
                'end' => $endISO,
                'url' => route('app.appointments.show', [
                    'clinic' => $this->clinicSlug,
                    'appointment' => $a->id,
                ]),
                'backgroundColor' => $color,
                'borderColor' => $statusColor,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'doctor' => $a->doctor->name ?? '—',
                    'status' => $a->status,
                    'time' => $a->start_time ? Carbon::parse($a->start_time)->format('H:i') : '',
                ],
                'classNames' => $a->status === Appointment::STATUS_CANCELLED ? ['fc-event-cancelled'] : [],
            ];
        })->all();
    }

    /**
     * Drag & drop reschedule. Receives the new start (and optional end) ISO datetime
     * from FullCalendar and updates the appointment.
     */
    public function rescheduleEvent(string $id, string $start, ?string $end = null): array
    {
        if (! $this->clinic->canWrite()) {
            return ['success' => false, 'message' => __('general.action_not_allowed')];
        }

        if (! Auth::user()?->can('appointments.edit')) {
            return ['success' => false, 'message' => __('general.unauthorized')];
        }

        $appointment = Appointment::query()
            ->forClinic($this->clinic->id)
            ->find($id);

        if (! $appointment) {
            return ['success' => false, 'message' => __('appointments.not_found')];
        }

        $startCarbon = Carbon::parse($start);
        $endCarbon = $end ? Carbon::parse($end) : null;

        $appointment->update([
            'appointment_date' => $startCarbon->toDateString(),
            'start_time' => $startCarbon->format('H:i:s'),
            'end_time' => $endCarbon ? $endCarbon->format('H:i:s') : $appointment->end_time,
        ]);

        return ['success' => true, 'message' => __('appointments.rescheduled')];
    }

    public function toggleDoctor(int $doctorId): void
    {
        $key = (string) $doctorId;
        if (in_array($key, $this->selectedDoctors, true)) {
            $this->selectedDoctors = array_values(array_diff($this->selectedDoctors, [$key]));
        } else {
            $this->selectedDoctors[] = $key;
        }
        $this->dispatch('calendar-refresh');
    }

    public function clearDoctorFilter(): void
    {
        $this->selectedDoctors = [];
        $this->dispatch('calendar-refresh');
    }

    /**
     * Stable hash → palette index for consistent color per doctor.
     */
    protected function colorForDoctor(int $doctorId): string
    {
        $palette = [
            '#4f46e5', // indigo-600
            '#0891b2', // cyan-600
            '#16a34a', // green-600
            '#dc2626', // red-600
            '#ea580c', // orange-600
            '#9333ea', // purple-600
            '#db2777', // pink-600
            '#0d9488', // teal-600
            '#65a30d', // lime-600
            '#2563eb', // blue-600
        ];

        return $palette[$doctorId % count($palette)];
    }

    protected function statusColor(string $status): string
    {
        return match ($status) {
            Appointment::STATUS_CONFIRMED => '#16a34a',
            Appointment::STATUS_COMPLETED => '#0d9488',
            Appointment::STATUS_CANCELLED => '#9ca3af',
            Appointment::STATUS_NO_SHOW => '#dc2626',
            Appointment::STATUS_IN_PROGRESS => '#ea580c',
            Appointment::STATUS_WAITING => '#f59e0b',
            default => '#4b5563', // scheduled
        };
    }

    public function render()
    {
        return view('livewire.app.appointments.calendar', [
            'doctors' => $this->getDoctorsProperty(),
        ])->layout('layouts.app');
    }
}
