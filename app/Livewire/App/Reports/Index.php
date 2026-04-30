<?php

namespace App\Livewire\App\Reports;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class Index extends Component
{
    public Clinic $clinic;

    public string $period = 'this_month';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $doctorFilter = '';

    public string $statusFilter = '';

    public string $typeFilter = '';

    public function mount(Clinic $clinic): void
    {
        abort_unless(auth()->user()->can('reports.view'), 403);
        abort_if(auth()->user()->clinic_id !== $clinic->id, 403);

        $this->clinic = $clinic;
        $this->applyPeriodDates();
    }

    public function updatedPeriod(): void
    {
        if ($this->period !== 'custom') {
            $this->applyPeriodDates();
        }
    }

    private function applyPeriodDates(): void
    {
        $now = Carbon::now();

        match ($this->period) {
            'today' => [$this->dateFrom, $this->dateTo] = [$now->toDateString(), $now->toDateString()],
            'this_week' => [$this->dateFrom, $this->dateTo] = [$now->startOfWeek()->toDateString(), $now->copy()->endOfWeek()->toDateString()],
            'this_month' => [$this->dateFrom, $this->dateTo] = [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
            'last_month' => [$this->dateFrom, $this->dateTo] = [$now->copy()->subMonth()->startOfMonth()->toDateString(), $now->copy()->subMonth()->endOfMonth()->toDateString()],
            'this_quarter' => [$this->dateFrom, $this->dateTo] = [$now->copy()->startOfQuarter()->toDateString(), $now->copy()->endOfQuarter()->toDateString()],
            'this_year' => [$this->dateFrom, $this->dateTo] = [$now->copy()->startOfYear()->toDateString(), $now->copy()->endOfYear()->toDateString()],
            default => null,
        };
    }

    // ==================== BASE QUERY ====================

    private function baseQuery()
    {
        $query = Appointment::query()
            ->withoutGlobalScope('clinic')
            ->where('clinic_id', $this->clinic->id)
            ->whereDate('appointment_date', '>=', $this->dateFrom)
            ->whereDate('appointment_date', '<=', $this->dateTo);

        if ($this->doctorFilter) {
            $query->where('doctor_id', $this->doctorFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->typeFilter) {
            $query->where('appointment_type', $this->typeFilter);
        }

        return $query;
    }

    // ==================== SUMMARY STATS ====================

    public function totalAppointments(): int
    {
        return $this->baseQuery()->count();
    }

    public function completedAppointments(): int
    {
        return $this->baseQuery()->where('status', 'completed')->count();
    }

    public function cancelledAppointments(): int
    {
        return $this->baseQuery()->where('status', 'cancelled')->count();
    }

    public function noShowAppointments(): int
    {
        return $this->baseQuery()->where('status', 'no_show')->count();
    }

    public function completionRate(): float
    {
        $total = $this->totalAppointments();
        if ($total === 0) {
            return 0;
        }

        return round(($this->completedAppointments() / $total) * 100, 1);
    }

    public function newPatients(): int
    {
        return Patient::query()
            ->where('clinic_id', $this->clinic->id)
            ->whereBetween('created_at', [
                $this->dateFrom.' 00:00:00',
                $this->dateTo.' 23:59:59',
            ])
            ->count();
    }

    // ==================== CHART DATA ====================

    public function appointmentsByDay(): string
    {
        $from = Carbon::parse($this->dateFrom);
        $to = Carbon::parse($this->dateTo);

        // Limit to 90 days to keep chart readable
        if ($from->diffInDays($to) > 90) {
            $from = $to->copy()->subDays(89);
        }

        $data = Appointment::query()
            ->where('clinic_id', $this->clinic->id)
            ->whereBetween('appointment_date', [$from->toDateString(), $to->toDateString()])
            ->when($this->doctorFilter, fn ($q) => $q->where('doctor_id', $this->doctorFilter))
            ->selectRaw('appointment_date, count(*) as total')
            ->groupBy('appointment_date')
            ->orderBy('appointment_date')
            ->pluck('total', 'appointment_date')
            ->toArray();

        // Fill all days in range
        $labels = [];
        $values = [];
        $current = $from->copy();
        while ($current->lte($to)) {
            $key = $current->toDateString();
            $labels[] = $current->format('d/m');
            $values[] = $data[$key] ?? 0;
            $current->addDay();
        }

        return json_encode(['labels' => $labels, 'values' => $values]);
    }

    public function appointmentsByStatus(): string
    {
        $statuses = ['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show', 'waiting', 'in_progress'];

        $data = Appointment::query()
            ->where('clinic_id', $this->clinic->id)
            ->whereBetween('appointment_date', [$this->dateFrom, $this->dateTo])
            ->when($this->doctorFilter, fn ($q) => $q->where('doctor_id', $this->doctorFilter))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $labels = [];
        $values = [];
        foreach ($statuses as $status) {
            if (isset($data[$status]) && $data[$status] > 0) {
                $labels[] = $status;
                $values[] = $data[$status];
            }
        }

        return json_encode(['labels' => $labels, 'values' => $values]);
    }

    public function newPatientsByMonth(): string
    {
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $months->push(Carbon::now()->subMonths($i));
        }

        $isMysql = \Illuminate\Support\Facades\DB::getDriverName() === 'mysql';
        $monthExpr = $isMysql
            ? "DATE_FORMAT(created_at, '%Y-%m')"
            : "strftime('%Y-%m', created_at)";

        $data = Patient::query()
            ->where('clinic_id', $this->clinic->id)
            ->where('created_at', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->selectRaw("{$monthExpr} as month, count(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $labels = $months->map(fn ($m) => $m->format('M Y'))->values()->toArray();
        $values = $months->map(fn ($m) => $data[$m->format('Y-m')] ?? 0)->values()->toArray();

        return json_encode(['labels' => $labels, 'values' => $values]);
    }

    public function appointmentsByType(): string
    {
        $data = Appointment::query()
            ->where('clinic_id', $this->clinic->id)
            ->whereBetween('appointment_date', [$this->dateFrom, $this->dateTo])
            ->when($this->doctorFilter, fn ($q) => $q->where('doctor_id', $this->doctorFilter))
            ->selectRaw('appointment_type, count(*) as total')
            ->groupBy('appointment_type')
            ->pluck('total', 'appointment_type')
            ->toArray();

        return json_encode([
            'labels' => array_keys($data),
            'values' => array_values($data),
        ]);
    }

    // ==================== FILTERS ====================

    public function doctors()
    {
        return User::where('clinic_id', $this->clinic->id)
            ->whereIn('role', ['owner', 'doctor'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function statuses(): array
    {
        return ['scheduled', 'confirmed', 'waiting', 'in_progress', 'completed', 'cancelled', 'no_show'];
    }

    public function types(): array
    {
        return ['scheduled', 'walk_in', 'emergency', 'follow_up', 'telemedicine'];
    }

    // ==================== EXPORT ====================

    public function exportCsv()
    {
        abort_unless(auth()->user()->can('reports.export'), 403);

        $appointments = $this->baseQuery()
            ->with(['patient', 'doctor'])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->get();

        $filename = 'citas-'.$this->dateFrom.'-'.$this->dateTo.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($appointments) {
            $handle = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                __('reports.col_date'),
                __('reports.col_time'),
                __('reports.col_patient'),
                __('reports.col_doctor'),
                __('reports.col_type'),
                __('reports.col_status'),
                __('reports.col_duration'),
                __('reports.col_reason'),
            ]);

            foreach ($appointments as $appointment) {
                fputcsv($handle, [
                    $appointment->appointment_date,
                    $appointment->start_time,
                    $appointment->patient?->full_name ?? '—',
                    $appointment->doctor?->name ?? '—',
                    $appointment->appointment_type,
                    $appointment->status,
                    $appointment->duration_minutes,
                    $appointment->reason ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        // Call methods directly so values are always evaluated with the
        // current filter state in this render cycle.
        return view('livewire.app.reports.index', [
            'totalAppointments' => $this->totalAppointments(),
            'completedAppointments' => $this->completedAppointments(),
            'cancelledAppointments' => $this->cancelledAppointments(),
            'noShowAppointments' => $this->noShowAppointments(),
            'completionRate' => $this->completionRate(),
            'newPatients' => $this->newPatients(),
            'appointmentsByDay' => $this->appointmentsByDay(),
            'appointmentsByStatus' => $this->appointmentsByStatus(),
            'appointmentsByType' => $this->appointmentsByType(),
            'newPatientsByMonth' => $this->newPatientsByMonth(),
            'doctors' => $this->doctors(),
        ])->layout('layouts.app', [
            'header' => __('reports.title'),
        ]);
    }
}
