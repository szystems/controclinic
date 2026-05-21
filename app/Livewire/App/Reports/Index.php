<?php

namespace App\Livewire\App\Reports;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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

    /**
     * Build base query honoring tenant + active filters.
     * If $previousPeriod is true, swap dateFrom/dateTo for the equivalent prior window
     * (same length, immediately preceding the current range).
     */
    private function baseQuery(bool $previousPeriod = false)
    {
        [$from, $to] = $previousPeriod ? $this->previousPeriodRange() : [$this->dateFrom, $this->dateTo];

        $query = Appointment::query()
            ->withoutGlobalScope('clinic')
            ->where('clinic_id', $this->clinic->id)
            ->whereDate('appointment_date', '>=', $from)
            ->whereDate('appointment_date', '<=', $to);

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

    /**
     * Return [from, to] for the period immediately preceding the current one,
     * with the same number of days (inclusive).
     */
    private function previousPeriodRange(): array
    {
        $from = Carbon::parse($this->dateFrom);
        $to = Carbon::parse($this->dateTo);
        $days = $from->diffInDays($to) + 1;

        $prevTo = $from->copy()->subDay();
        $prevFrom = $prevTo->copy()->subDays($days - 1);

        return [$prevFrom->toDateString(), $prevTo->toDateString()];
    }

    /**
     * Compute % delta from previous to current. Returns null when previous is 0
     * (so we can show "—" instead of a misleading number).
     */
    private function deltaPercent(int|float $current, int|float $previous): ?float
    {
        if ($previous === 0 || $previous === 0.0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
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

    /**
     * Average completed appointment duration in minutes (current period).
     */
    public function averageDuration(): int
    {
        $avg = $this->baseQuery()
            ->where('status', 'completed')
            ->avg('duration_minutes');

        return (int) round($avg ?? 0);
    }

    /**
     * KPIs for the previous period (same length immediately before).
     * Used to show deltas next to current KPIs.
     */
    public function previousStats(): array
    {
        $prev = $this->baseQuery(previousPeriod: true);
        $total = (clone $prev)->count();
        $completed = (clone $prev)->where('status', 'completed')->count();
        $cancelled = (clone $prev)->where('status', 'cancelled')->count();
        $noShow = (clone $prev)->where('status', 'no_show')->count();
        $rate = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;

        // New patients in previous range
        [$pFrom, $pTo] = $this->previousPeriodRange();
        $newPatients = Patient::query()
            ->where('clinic_id', $this->clinic->id)
            ->whereBetween('created_at', [$pFrom.' 00:00:00', $pTo.' 23:59:59'])
            ->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'no_show' => $noShow,
            'rate' => $rate,
            'new_patients' => $newPatients,
        ];
    }

    /**
     * Compute deltas (% change) for each KPI vs previous period.
     * Returns null when previous value was zero to avoid misleading %.
     *
     * @return array<string, float|null>
     */
    public function deltas(): array
    {
        $prev = $this->previousStats();

        return [
            'total' => $this->deltaPercent($this->totalAppointments(), $prev['total']),
            'completed' => $this->deltaPercent($this->completedAppointments(), $prev['completed']),
            'cancelled' => $this->deltaPercent($this->cancelledAppointments(), $prev['cancelled']),
            'no_show' => $this->deltaPercent($this->noShowAppointments(), $prev['no_show']),
            'rate' => $this->deltaPercent($this->completionRate(), $prev['rate']),
            'new_patients' => $this->deltaPercent($this->newPatients(), $prev['new_patients']),
        ];
    }

    /**
     * Top doctors by appointment volume in the current filtered period.
     * Returns max 5 rows: [name, total, completed, completion_rate].
     *
     * @return array<int, array{id:int,name:string,total:int,completed:int,rate:float}>
     */
    public function topDoctors(): array
    {
        $rows = $this->baseQuery()
            ->whereNotNull('doctor_id')
            ->selectRaw('doctor_id, count(*) as total, sum(case when status = ? then 1 else 0 end) as completed', ['completed'])
            ->groupBy('doctor_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $names = User::whereIn('id', $rows->pluck('doctor_id'))->pluck('name', 'id');

        return $rows->map(function ($r) use ($names) {
            $total = (int) $r->total;
            $completed = (int) $r->completed;

            return [
                'id' => (int) $r->doctor_id,
                'name' => $names[$r->doctor_id] ?? '—',
                'total' => $total,
                'completed' => $completed,
                'rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0.0,
            ];
        })->all();
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

        // Use DATE() alias to get raw string keys, avoiding Eloquent date-cast objects as array keys
        $data = $this->baseQuery()
            ->whereDate('appointment_date', '>=', $from->toDateString())
            ->whereDate('appointment_date', '<=', $to->toDateString())
            ->selectRaw('DATE(appointment_date) as day, count(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
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

        $colors = [
            'scheduled' => '#6366f1',
            'confirmed' => '#3b82f6',
            'waiting' => '#f59e0b',
            'in_progress' => '#8b5cf6',
            'completed' => '#10b981',
            'cancelled' => '#ef4444',
            'no_show' => '#6b7280',
        ];

        $data = $this->baseQuery()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $labels = [];
        $values = [];
        $bgColors = [];
        foreach ($statuses as $status) {
            if (isset($data[$status]) && $data[$status] > 0) {
                $labels[] = __('reports.status_'.str_replace('_', '', $status));
                $values[] = $data[$status];
                $bgColors[] = $colors[$status] ?? '#9ca3af';
            }
        }

        return json_encode(['labels' => $labels, 'values' => $values, 'colors' => $bgColors]);
    }

    public function newPatientsByMonth(): string
    {
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $months->push(Carbon::now()->subMonths($i));
        }

        $isMysql = DB::getDriverName() === 'mysql';
        $monthExpr = $isMysql
            ? "DATE_FORMAT(created_at, '%Y-%m')"
            : "strftime('%Y-%m', created_at)";

        $data = Patient::query()
            ->withoutGlobalScope('clinic')
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
        $data = $this->baseQuery()
            ->selectRaw('appointment_type, count(*) as total')
            ->groupBy('appointment_type')
            ->pluck('total', 'appointment_type')
            ->toArray();

        $labels = array_map(
            fn ($t) => __('reports.type_'.str_replace('_', '', $t)),
            array_keys($data)
        );

        return json_encode([
            'labels' => $labels,
            'values' => array_values($data),
        ]);
    }

    // ==================== BILLING BASE QUERY ====================

    /**
     * Base query for invoices in the current period (tenant-scoped).
     * Excludes draft and cancelled invoices.
     * If $previousPeriod is true, uses the prior date window.
     */
    private function invoiceBaseQuery(bool $previousPeriod = false)
    {
        [$from, $to] = $previousPeriod ? $this->previousPeriodRange() : [$this->dateFrom, $this->dateTo];

        $query = Invoice::query()
            ->withoutGlobalScope('clinic')
            ->where('clinic_id', $this->clinic->id)
            ->whereNotIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_CANCELLED])
            ->whereDate('issued_at', '>=', $from)
            ->whereDate('issued_at', '<=', $to);

        if ($this->doctorFilter) {
            $query->where('doctor_id', $this->doctorFilter);
        }

        return $query;
    }

    /**
     * Base query for invoice payments in the current period.
     */
    private function paymentBaseQuery(bool $previousPeriod = false)
    {
        [$from, $to] = $previousPeriod ? $this->previousPeriodRange() : [$this->dateFrom, $this->dateTo];

        $query = InvoicePayment::query()
            ->whereHas('invoice', function ($q) {
                $q->withoutGlobalScope('clinic')->where('clinic_id', $this->clinic->id);
            })
            ->whereDate('paid_at', '>=', $from)
            ->whereDate('paid_at', '<=', $to);

        if ($this->doctorFilter) {
            $query->whereHas('invoice', function ($q) {
                $q->withoutGlobalScope('clinic')->where('doctor_id', $this->doctorFilter);
            });
        }

        return $query;
    }

    // ==================== BILLING STATS ====================

    public function totalInvoiced(): float
    {
        return (float) $this->invoiceBaseQuery()->sum('total');
    }

    public function totalCollected(): float
    {
        return (float) $this->paymentBaseQuery()->sum('amount');
    }

    public function pendingRevenue(): float
    {
        return (float) Invoice::query()
            ->withoutGlobalScope('clinic')
            ->where('clinic_id', $this->clinic->id)
            ->whereIn('status', [Invoice::STATUS_PENDING, Invoice::STATUS_PARTIAL])
            ->sum(DB::raw('total - paid_amount'));
    }

    public function averageTicket(): float
    {
        $avg = $this->invoiceBaseQuery()
            ->where('total', '>', 0)
            ->avg('total');

        return round((float) ($avg ?? 0), 2);
    }

    public function revenueDelta(): array
    {
        $prevInvoiced = (float) $this->invoiceBaseQuery(true)->sum('total');
        $prevCollected = (float) $this->paymentBaseQuery(true)->sum('amount');

        return [
            'invoiced' => $this->deltaPercent($this->totalInvoiced(), $prevInvoiced),
            'collected' => $this->deltaPercent($this->totalCollected(), $prevCollected),
        ];
    }

    /**
     * Revenue grouped by doctor for the current period.
     *
     * @return array<int, array{name: string, invoiced: float, collected: float, invoice_count: int}>
     */
    public function revenueByDoctor(): array
    {
        $rows = $this->invoiceBaseQuery()
            ->whereNotNull('doctor_id')
            ->selectRaw('doctor_id, count(*) as invoice_count, sum(total) as invoiced, sum(paid_amount) as collected')
            ->groupBy('doctor_id')
            ->orderByDesc('invoiced')
            ->limit(10)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $names = User::whereIn('id', $rows->pluck('doctor_id'))->pluck('name', 'id');

        return $rows->map(fn ($r) => [
            'name' => $names[$r->doctor_id] ?? '—',
            'invoiced' => (float) $r->invoiced,
            'collected' => (float) $r->collected,
            'invoice_count' => (int) $r->invoice_count,
        ])->all();
    }

    /**
     * Revenue grouped by payment method for the current period.
     *
     * @return array<int, array{method: string, label: string, amount: float, count: int}>
     */
    public function revenueByPaymentMethod(): array
    {
        $rows = $this->paymentBaseQuery()
            ->selectRaw('method, count(*) as payment_count, sum(amount) as amount')
            ->groupBy('method')
            ->orderByDesc('amount')
            ->get();

        return $rows->map(fn ($r) => [
            'method' => $r->method,
            'label' => __('invoices.payment_method_'.$r->method),
            'amount' => (float) $r->amount,
            'count' => (int) $r->payment_count,
        ])->all();
    }

    /**
     * Daily revenue (collected payments) for the current period — for the chart.
     */
    public function revenueByDay(): string
    {
        $from = Carbon::parse($this->dateFrom);
        $to = Carbon::parse($this->dateTo);

        if ($from->diffInDays($to) > 90) {
            $from = $to->copy()->subDays(89);
        }

        $isMysql = DB::getDriverName() === 'mysql';
        $dateExpr = $isMysql ? 'DATE(paid_at)' : 'date(paid_at)';

        $data = $this->paymentBaseQuery()
            ->selectRaw("{$dateExpr} as day, sum(amount) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $labels = [];
        $values = [];
        $current = $from->copy();
        while ($current->lte($to)) {
            $key = $current->toDateString();
            $labels[] = $current->format('d/m');
            $values[] = round((float) ($data[$key] ?? 0), 2);
            $current->addDay();
        }

        return json_encode(['labels' => $labels, 'values' => $values]);
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

    public function clearFilters(): void
    {
        $this->doctorFilter = '';
        $this->statusFilter = '';
        $this->typeFilter = '';
        $this->period = 'this_month';
        $this->applyPeriodDates();
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

        // Pre-compute summary stats so the file is self-describing
        $totals = [
            'total' => $appointments->count(),
            'completed' => $appointments->where('status', 'completed')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
            'no_show' => $appointments->where('status', 'no_show')->count(),
        ];

        $doctorName = $this->doctorFilter
            ? optional(User::find($this->doctorFilter))->name ?? '—'
            : __('reports.all_doctors');

        $statusLabel = $this->statusFilter
            ? __('reports.status_'.str_replace('_', '', $this->statusFilter))
            : __('reports.all_statuses');

        $typeLabel = $this->typeFilter
            ? __('reports.type_'.str_replace('_', '', $this->typeFilter))
            : __('reports.all_types');

        $filename = 'citas-'.$this->dateFrom.'-'.$this->dateTo.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($appointments, $totals, $doctorName, $statusLabel, $typeLabel) {
            $handle = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // ── Filter header block ──
            fputcsv($handle, [__('reports.title').' — '.$this->clinic->name]);
            fputcsv($handle, [__('reports.generated_at'), now()->format('d/m/Y H:i')]);
            fputcsv($handle, [__('reports.period'), $this->dateFrom.' → '.$this->dateTo]);
            fputcsv($handle, [__('general.doctor'), $doctorName]);
            fputcsv($handle, [__('general.status'), $statusLabel]);
            fputcsv($handle, [__('appointments.type'), $typeLabel]);
            fputcsv($handle, []);

            // ── Column headers ──
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
                    __('reports.type_'.str_replace('_', '', $appointment->appointment_type)),
                    __('reports.status_'.str_replace('_', '', $appointment->status)),
                    $appointment->duration_minutes,
                    $appointment->reason ?? '',
                ]);
            }

            // ── Summary footer ──
            fputcsv($handle, []);
            fputcsv($handle, [__('reports.total_appointments'), $totals['total']]);
            fputcsv($handle, [__('reports.completed'), $totals['completed']]);
            fputcsv($handle, [__('reports.cancelled'), $totals['cancelled']]);
            fputcsv($handle, [__('reports.no_show'), $totals['no_show']]);

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
            'averageDuration' => $this->averageDuration(),
            'deltas' => $this->deltas(),
            'topDoctors' => $this->topDoctors(),
            'appointmentsByDay' => $this->appointmentsByDay(),
            'appointmentsByStatus' => $this->appointmentsByStatus(),
            'appointmentsByType' => $this->appointmentsByType(),
            'newPatientsByMonth' => $this->newPatientsByMonth(),
            'doctors' => $this->doctors(),
            // Billing
            'billingEnabled' => $this->clinic->billingEnabled(),
            'totalInvoiced' => $this->clinic->billingEnabled() ? $this->totalInvoiced() : null,
            'totalCollected' => $this->clinic->billingEnabled() ? $this->totalCollected() : null,
            'pendingRevenue' => $this->clinic->billingEnabled() ? $this->pendingRevenue() : null,
            'averageTicket' => $this->clinic->billingEnabled() ? $this->averageTicket() : null,
            'revenueDelta' => $this->clinic->billingEnabled() ? $this->revenueDelta() : [],
            'revenueByDoctor' => $this->clinic->billingEnabled() ? $this->revenueByDoctor() : [],
            'revenueByPaymentMethod' => $this->clinic->billingEnabled() ? $this->revenueByPaymentMethod() : [],
            'revenueByDay' => $this->clinic->billingEnabled() ? $this->revenueByDay() : json_encode(['labels' => [], 'values' => []]),
        ])->layout('layouts.app', [
            'header' => __('reports.title'),
        ]);
    }
}
