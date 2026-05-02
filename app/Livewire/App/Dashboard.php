<?php

namespace App\Livewire\App;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public Clinic $clinic;

    public function mount(Clinic $clinic): void
    {
        $this->clinic = $clinic;
    }

    /**
     * Returns true when the current user is a pure doctor (not owner/admin).
     * In this mode, appointment stats are scoped to the doctor's own schedule.
     */
    public function getIsPersonalizedForDoctorProperty(): bool
    {
        $user = Auth::user();

        return $user
            && $user->hasRole('doctor')
            && ! $user->hasAnyRole(['owner', 'admin']);
    }

    /**
     * Base query for appointment stats — scoped to doctor when personalised.
     */
    private function appointmentsBaseQuery()
    {
        $query = Appointment::query()->forClinic($this->clinic->id);

        if ($this->isPersonalizedForDoctor) {
            $query->where('doctor_id', Auth::id());
        }

        return $query;
    }

    public function getPatientsCountProperty(): int
    {
        return $this->clinic->patients()->count();
    }

    public function getAppointmentsThisMonthProperty(): int
    {
        return $this->appointmentsBaseQuery()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    public function getTodayAppointmentsProperty(): int
    {
        return $this->appointmentsBaseQuery()
            ->whereDate('appointment_date', now()->toDateString())
            ->count();
    }

    public function getPendingTodayProperty(): int
    {
        return $this->appointmentsBaseQuery()
            ->whereDate('appointment_date', now()->toDateString())
            ->where('status', Appointment::STATUS_SCHEDULED)
            ->count();
    }

    public function getCompletedTodayProperty(): int
    {
        return $this->appointmentsBaseQuery()
            ->whereDate('appointment_date', now()->toDateString())
            ->where('status', Appointment::STATUS_COMPLETED)
            ->count();
    }

    public function getTodayScheduleProperty()
    {
        return $this->appointmentsBaseQuery()
            ->with('patient')
            ->whereDate('appointment_date', now()->toDateString())
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Próximas 5 citas (excluyendo hoy) en los próximos 7 días.
     */
    public function getUpcomingAppointmentsProperty()
    {
        $tomorrow = now()->addDay()->toDateString();
        $weekAhead = now()->addDays(7)->toDateString();

        return $this->appointmentsBaseQuery()
            ->with(['patient', 'doctor'])
            ->whereDate('appointment_date', '>=', $tomorrow)
            ->whereDate('appointment_date', '<=', $weekAhead)
            ->whereIn('status', [Appointment::STATUS_SCHEDULED, Appointment::STATUS_CONFIRMED])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->limit(5)
            ->get();
    }

    /**
     * Cumpleaños de pacientes en el mes actual (próximos primero, hasta 5).
     */
    public function getBirthdaysThisMonthProperty()
    {
        $month = now()->month;
        $isMysql = DB::getDriverName() === 'mysql';
        $monthExpr = $isMysql ? 'MONTH(birth_date)' : "CAST(strftime('%m', birth_date) AS INTEGER)";
        $dayExpr = $isMysql ? 'DAY(birth_date)' : "CAST(strftime('%d', birth_date) AS INTEGER)";

        $today = (int) now()->day;

        return Patient::query()
            ->where('clinic_id', $this->clinic->id)
            ->whereNotNull('birth_date')
            ->whereRaw("{$monthExpr} = ?", [$month])
            ->orderByRaw("CASE WHEN {$dayExpr} >= ? THEN 0 ELSE 1 END", [$today])
            ->orderByRaw($dayExpr)
            ->limit(5)
            ->get(['id', 'first_name', 'last_name', 'birth_date']);
    }

    /**
     * Serie de citas creadas en los últimos 14 días (para sparkline).
     *
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    public function getLast14DaysSeriesProperty(): array
    {
        $start = now()->subDays(13)->startOfDay();
        $rows = $this->appointmentsBaseQuery()
            ->whereDate('appointment_date', '>=', $start->toDateString())
            ->selectRaw('DATE(appointment_date) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $labels = [];
        $values = [];
        for ($i = 0; $i < 14; $i++) {
            $d = $start->copy()->addDays($i);
            $labels[] = $d->format('d/m');
            $values[] = (int) ($rows[$d->toDateString()] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
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
            'upcomingAppointments' => $this->upcomingAppointments,
            'birthdaysThisMonth' => $this->birthdaysThisMonth,
            'last14DaysSeries' => $this->last14DaysSeries,
            'usageStats' => $this->usageStats,
            'isPersonalizedForDoctor' => $this->isPersonalizedForDoctor,
        ])->layout('layouts.app');
    }
}
