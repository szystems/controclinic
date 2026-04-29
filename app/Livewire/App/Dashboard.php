<?php

namespace App\Livewire\App;

use App\Models\Appointment;
use App\Models\Clinic;
use Livewire\Component;

class Dashboard extends Component
{
    public Clinic $clinic;

    public function mount(Clinic $clinic): void
    {
        $this->clinic = $clinic;
    }

    public function getPatientsCountProperty(): int
    {
        return $this->clinic->patients()->count();
    }

    public function getAppointmentsThisMonthProperty(): int
    {
        return $this->clinic->appointments()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    public function getTodayAppointmentsProperty(): int
    {
        return $this->clinic->appointments()
            ->where('appointment_date', now()->toDateString())
            ->count();
    }

    public function getPendingTodayProperty(): int
    {
        return $this->clinic->appointments()
            ->where('appointment_date', now()->toDateString())
            ->where('status', Appointment::STATUS_SCHEDULED)
            ->count();
    }

    public function getCompletedTodayProperty(): int
    {
        return $this->clinic->appointments()
            ->where('appointment_date', now()->toDateString())
            ->where('status', Appointment::STATUS_COMPLETED)
            ->count();
    }

    public function getTodayScheduleProperty()
    {
        return $this->clinic->appointments()
            ->with('patient')
            ->where('appointment_date', now()->toDateString())
            ->orderBy('start_time')
            ->get();
    }

    public function getDoctorsCountProperty(): int
    {
        return $this->clinic->practitioners()->count();
    }

    public function getStaffCountProperty(): int
    {
        return $this->clinic->staff()->count();
    }

    public function getUsageStatsProperty(): array
    {
        $limits = $this->clinic->getPlanLimits();

        return [
            'patients' => [
                'current' => $this->patientsCount,
                'max' => $limits['max_patients'],
                'percentage' => $limits['max_patients'] ? min(100, round(($this->patientsCount / $limits['max_patients']) * 100)) : 0,
                'unlimited' => $limits['max_patients'] === null,
            ],
            'appointments' => [
                'current' => $this->appointmentsThisMonth,
                'max' => $limits['max_appointments_per_month'],
                'percentage' => $limits['max_appointments_per_month'] ? min(100, round(($this->appointmentsThisMonth / $limits['max_appointments_per_month']) * 100)) : 0,
                'unlimited' => $limits['max_appointments_per_month'] === null,
            ],
            'doctors' => [
                'current' => $this->doctorsCount,
                'max' => $limits['max_doctors'],
                'percentage' => $limits['max_doctors'] ? min(100, round(($this->doctorsCount / $limits['max_doctors']) * 100)) : 0,
                'unlimited' => $limits['max_doctors'] === null,
            ],
            'staff' => [
                'current' => $this->staffCount,
                'max' => $limits['max_staff'],
                'percentage' => $limits['max_staff'] !== null && $limits['max_staff'] > 0 ? min(100, round(($this->staffCount / $limits['max_staff']) * 100)) : ($limits['max_staff'] === 0 ? 100 : 0),
                'unlimited' => $limits['max_staff'] === null,
                'blocked' => $limits['max_staff'] === 0,
            ],
        ];
    }

    public function render()
    {
        return view('livewire.app.dashboard', [
            'patientsCount' => $this->patientsCount,
            'appointmentsThisMonth' => $this->appointmentsThisMonth,
            'todayAppointments' => $this->todayAppointments,
            'pendingToday' => $this->pendingToday,
            'completedToday' => $this->completedToday,
            'todaySchedule' => $this->todaySchedule,
            'usageStats' => $this->usageStats,
        ])->layout('layouts.app');
    }
}
