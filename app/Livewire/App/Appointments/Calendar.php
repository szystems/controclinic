<?php

namespace App\Livewire\App\Appointments;

use App\Jobs\SendAppointmentNotification;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DoctorUnavailability;
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
            ->with(['patient:id,first_name,last_name,phone,phone_country_code,email', 'doctor:id,name'])
            ->whereBetween('appointment_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if (! empty($this->selectedDoctors)) {
            $query->whereIn('doctor_id', $this->selectedDoctors);
        }

        $appointmentEvents = $query->get()->map(function (Appointment $a) {
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
                    'email' => $a->patient->email ?? '',
                    'phone' => $a->patient->phone ?? '',
                    'wa_url' => (function () use ($a, $patientName): string {
                        $localPhone = preg_replace('/[^0-9]/', '', $a->patient->phone ?? '');
                        if (! $localPhone) {
                            return '';
                        }
                        $code = preg_replace('/[^0-9]/', '', $a->patient->phone_country_code
                            ?? $this->clinic->settings['phone_country_code'] ?? '');
                        $phone = $code.$localPhone;
                        $date = $a->appointment_date->translatedFormat('l d \d\e F');
                        $time = $a->start_time ? Carbon::parse($a->start_time)->format('H:i') : '';
                        $msg = __('appointments.whatsapp_reminder_message', [
                            'patient' => $a->patient->first_name ?? $patientName,
                            'doctor' => $a->doctor->name ?? '',
                            'date' => $date,
                            'time' => $time,
                            'clinic' => $this->clinic->name,
                        ]);

                        return 'https://wa.me/'.$phone.'?text='.rawurlencode($msg);
                    })(),
                ],
                'classNames' => $a->status === Appointment::STATUS_CANCELLED ? ['fc-event-cancelled'] : [],
            ];
        })->all();

        // Unavailability background events
        $unavailQuery = DoctorUnavailability::query()
            ->forClinic($this->clinic->id)
            ->overlapping($startDate->toDateString(), $endDate->toDateString());

        if (! empty($this->selectedDoctors)) {
            $unavailQuery->whereIn('doctor_id', $this->selectedDoctors);
        }

        $unavailEvents = $unavailQuery->with('doctor:id,name')->get()->map(function (DoctorUnavailability $block) {
            $endDateExclusive = $block->date_to->copy()->addDay()->toDateString();

            if (! $block->all_day && $block->date_from->eq($block->date_to)) {
                $start = $block->date_from->toDateString().'T'.($block->time_from ?? '00:00');
                $end = $block->date_to->toDateString().'T'.($block->time_to ?? '23:59');
            } else {
                $start = $block->date_from->toDateString();
                $end = $endDateExclusive;
            }

            $title = $block->reason ?: ($block->doctor?->name ?? __('schedule.title'));

            return [
                'id' => 'unavail-'.$block->id,
                'title' => $title,
                'start' => $start,
                'end' => $end,
                'display' => 'background',
                'backgroundColor' => '#fee2e2',
                'classNames' => ['unavailability-block'],
                'extendedProps' => [
                    'isUnavailability' => true,
                    'doctor' => $block->doctor?->name ?? '—',
                ],
            ];
        })->all();

        return array_merge($appointmentEvents, $unavailEvents);
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

        // Calculate new start/end times
        $newDate = $startCarbon->toDateString();
        $newStartTime = $startCarbon->format('H:i:s');

        if ($endCarbon) {
            $newEndTime = $endCarbon->format('H:i:s');
        } elseif ($appointment->start_time && $appointment->end_time) {
            // Preserve original duration
            $durationMinutes = Carbon::parse($appointment->start_time)
                ->diffInMinutes(Carbon::parse($appointment->end_time));
            $newEndTime = $startCarbon->copy()->addMinutes($durationMinutes)->format('H:i:s');
        } else {
            $newEndTime = $startCarbon->copy()->addMinutes(30)->format('H:i:s');
        }

        // Check for scheduling conflicts
        $hasConflict = Appointment::query()
            ->forClinic($this->clinic->id)
            ->forDoctor((int) $appointment->doctor_id)
            ->forDate($newDate)
            ->active()
            ->where('id', '!=', $appointment->id)
            ->where(function ($q) use ($newStartTime, $newEndTime) {
                $q->where('start_time', '<', $newEndTime)
                    ->where('end_time', '>', $newStartTime);
            })
            ->exists();

        if ($hasConflict) {
            return ['success' => false, 'message' => __('appointments.conflict_detected')];
        }

        $appointment->update([
            'appointment_date' => $newDate,
            'start_time' => $newStartTime,
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

    public function sendEmailReminder(string $id): array
    {
        if (! Auth::user()?->can('appointments.edit')) {
            return ['success' => false, 'message' => __('general.unauthorized')];
        }

        $appointment = Appointment::with('patient')->find($id);

        if (! $appointment || $appointment->clinic_id !== $this->clinic->id) {
            return ['success' => false, 'message' => __('appointments.not_found')];
        }

        if (! $appointment->patient?->email) {
            return ['success' => false, 'message' => __('appointments.reminder_no_email')];
        }

        SendAppointmentNotification::dispatch($appointment->id, SendAppointmentNotification::TYPE_REMINDER);

        return ['success' => true, 'message' => __('appointments.reminder_sent')];
    }

    public function render()
    {
        return view('livewire.app.appointments.calendar', [
            'doctors' => $this->getDoctorsProperty(),
        ])->layout('layouts.app');
    }
}
