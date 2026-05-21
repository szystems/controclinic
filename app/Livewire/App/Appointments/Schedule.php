<?php

namespace App\Livewire\App\Appointments;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Schedule extends Component
{
    public Clinic $clinic;

    public string $clinicSlug = '';

    public string $selectedDate = '';

    /** @var array<int, string> Doctor IDs currently hidden */
    public array $hiddenDoctors = [];

    public function mount(Clinic $clinic): void
    {
        abort_unless(auth()->user()->can('appointments.view'), 403);

        $this->clinic = $clinic;
        $this->clinicSlug = $clinic->slug;
        $this->selectedDate = now()->setTimezone($clinic->timezone)->toDateString();
    }

    public function previousDay(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->subDay()->toDateString();
    }

    public function nextDay(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->addDay()->toDateString();
    }

    public function goToToday(): void
    {
        $this->selectedDate = now()->setTimezone($this->clinic->timezone)->toDateString();
    }

    public function toggleDoctor(int $doctorId): void
    {
        $key = (string) $doctorId;
        if (in_array($key, $this->hiddenDoctors, true)) {
            $this->hiddenDoctors = array_values(array_diff($this->hiddenDoctors, [$key]));
        } else {
            $this->hiddenDoctors[] = $key;
        }
    }

    /**
     * All active doctors in this clinic.
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
     * Appointments for the selected day, grouped by doctor_id.
     * Only visible (non-hidden) doctors are included.
     *
     * @return array<int, Appointment[]>
     */
    public function getAppointmentsProperty(): array
    {
        $visibleDoctorIds = $this->doctors
            ->pluck('id')
            ->reject(fn ($id) => in_array((string) $id, $this->hiddenDoctors, true))
            ->values()
            ->all();

        if (empty($visibleDoctorIds)) {
            return [];
        }

        return Appointment::query()
            ->forClinic($this->clinic->id)
            ->whereDate('appointment_date', $this->selectedDate)
            ->whereIn('doctor_id', $visibleDoctorIds)
            ->with(['patient:id,first_name,last_name'])
            ->orderBy('start_time')
            ->get()
            ->groupBy('doctor_id')
            ->map(fn ($group) => $group->all())
            ->all();
    }

    /**
     * 30-minute time slots from 07:00 to 20:30 (inclusive).
     *
     * @return string[]
     */
    public function getTimeSlotsProperty(): array
    {
        $slots = [];
        for ($minutes = 7 * 60; $minutes <= 20 * 60 + 30; $minutes += 30) {
            $slots[] = sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
        }

        return $slots;
    }

    /**
     * Stable hash → palette colour per doctor ID.
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

    public function render()
    {
        return view('livewire.app.appointments.schedule', [
            'doctors' => $this->doctors,
            'appointments' => $this->appointments,
            'timeSlots' => $this->timeSlots,
        ]);
    }
}
