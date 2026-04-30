<div id="reports-root" class="py-6" x-data="reportsPage()" x-init="init()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('reports.title') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('reports.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-2 no-print">
                <button
                    type="button"
                    onclick="printReportPdf()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V4h12v5m-12 0H5a2 2 0 00-2 2v6h4m10-8h1a2 2 0 012 2v6h-4m-10 0h12v3H6v-3z"/>
                    </svg>
                    {{ __('reports.print_pdf') }}
                </button>

                @can('reports.export')
                <button
                    wire:click="exportCsv"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-50">
                    <svg wire:loading.remove wire:target="exportCsv" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <svg wire:loading wire:target="exportCsv" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    {{ __('reports.export_csv') }}
                </button>
                @endcan
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6 no-print">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                {{-- Period --}}
                <div class="col-span-2 md:col-span-1 lg:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('reports.period') }}</label>
                    <select wire:model.live="period"
                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                        <option value="today">{{ __('reports.period_today') }}</option>
                        <option value="this_week">{{ __('reports.period_this_week') }}</option>
                        <option value="this_month">{{ __('reports.period_this_month') }}</option>
                        <option value="last_month">{{ __('reports.period_last_month') }}</option>
                        <option value="this_quarter">{{ __('reports.period_this_quarter') }}</option>
                        <option value="this_year">{{ __('reports.period_this_year') }}</option>
                        <option value="custom">{{ __('reports.period_custom') }}</option>
                    </select>
                </div>

                {{-- Custom date range (shown only when custom is selected) --}}
                @if($period === 'custom')
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('reports.date_from') }}</label>
                    <input type="date" wire:model.live="dateFrom"
                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('reports.date_to') }}</label>
                    <input type="date" wire:model.live="dateTo"
                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                </div>
                @endif

                {{-- Doctor filter --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('general.doctor') }}</label>
                    <select wire:model.live="doctorFilter"
                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                        <option value="">{{ __('reports.all_doctors') }}</option>
                        @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status filter --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('general.status') }}</label>
                    <select wire:model.live="statusFilter"
                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                        <option value="">{{ __('reports.all_statuses') }}</option>
                        @foreach($this->statuses() as $s)
                        <option value="{{ $s }}">{{ __('reports.status_' . str_replace('_', '', str_replace('-', '', $s))) ?? $s }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Type filter --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('appointments.type') }}</label>
                    <select wire:model.live="typeFilter"
                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                        <option value="">{{ __('reports.all_types') }}</option>
                        @foreach($this->types() as $t)
                        <option value="{{ $t }}">{{ __('reports.type_' . str_replace('_', '', $t)) ?? $t }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($doctorFilter || $statusFilter || $typeFilter || $period !== 'this_month')
            <div class="mt-3 flex justify-end">
                <button type="button" wire:click="clearFilters"
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ __('reports.clear_filters') }}
                </button>
            </div>
            @endif
        </div>

        {{-- Loading overlay --}}
        <div wire:loading.flex class="fixed inset-0 bg-black/10 z-50 items-center justify-center no-print">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-xl flex items-center gap-3">
                <svg class="h-5 w-5 animate-spin text-primary" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('general.loading') }}</span>
            </div>
        </div>

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            {{-- Total --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('reports.total_appointments') }}</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalAppointments }}</p>
                <div class="mt-1">@include('livewire.app.reports._delta', ['delta' => $deltas['total']])</div>
            </div>
            {{-- Completed --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('reports.completed') }}</p>
                <p class="mt-1 text-3xl font-bold text-green-600 dark:text-green-400">{{ $completedAppointments }}</p>
                <div class="mt-1">@include('livewire.app.reports._delta', ['delta' => $deltas['completed']])</div>
            </div>
            {{-- Cancelled --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('reports.cancelled') }}</p>
                <p class="mt-1 text-3xl font-bold text-red-500 dark:text-red-400">{{ $cancelledAppointments }}</p>
                <div class="mt-1">@include('livewire.app.reports._delta', ['delta' => $deltas['cancelled'], 'invert' => true])</div>
            </div>
            {{-- No show --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('reports.no_show') }}</p>
                <p class="mt-1 text-3xl font-bold text-amber-500 dark:text-amber-400">{{ $noShowAppointments }}</p>
                <div class="mt-1">@include('livewire.app.reports._delta', ['delta' => $deltas['no_show'], 'invert' => true])</div>
            </div>
            {{-- Completion rate --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('reports.completion_rate') }}</p>
                <p class="mt-1 text-3xl font-bold text-primary">{{ $completionRate }}%</p>
                <div class="mt-1">@include('livewire.app.reports._delta', ['delta' => $deltas['rate']])</div>
            </div>
            {{-- New patients --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('reports.new_patients') }}</p>
                <p class="mt-1 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $newPatients }}</p>
                <div class="mt-1">@include('livewire.app.reports._delta', ['delta' => $deltas['new_patients']])</div>
            </div>
        </div>

        {{-- Secondary KPI row --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('reports.avg_duration') }}</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $averageDuration }} <span class="text-sm font-normal text-gray-500 dark:text-gray-400">{{ __('reports.minutes') }}</span>
                </p>
                <p class="mt-0.5 text-[11px] text-gray-400 dark:text-gray-500">{{ __('reports.avg_duration_help') }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 md:col-span-2">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('reports.previous_period_compare') }}</p>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                    {{ __('reports.previous_period_help') }}
                </p>
            </div>
        </div>

        {{-- Charts row 1: Appointments by day (full width) --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('reports.chart_appointments_by_day') }}</h2>
            <div class="relative h-48">
                <canvas id="chartByDay" x-ref="chartByDay"></canvas>
                @if($totalAppointments === 0)
                <div class="absolute inset-0 flex items-center justify-center">
                    <p class="text-sm text-gray-400 dark:text-gray-500">{{ __('reports.no_data') }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Charts row 2: Status + Type + Patients by month --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            {{-- Status --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('reports.chart_appointments_by_status') }}</h2>
                <div class="relative h-52">
                    <canvas id="chartByStatus" x-ref="chartByStatus"></canvas>
                    @if($totalAppointments === 0)
                    <div class="absolute inset-0 flex items-center justify-center">
                        <p class="text-sm text-gray-400 dark:text-gray-500">{{ __('reports.no_data') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Type --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('reports.chart_appointments_by_type') }}</h2>
                <div class="relative h-52">
                    <canvas id="chartByType" x-ref="chartByType"></canvas>
                    @if($totalAppointments === 0)
                    <div class="absolute inset-0 flex items-center justify-center">
                        <p class="text-sm text-gray-400 dark:text-gray-500">{{ __('reports.no_data') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- New patients by month --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('reports.chart_new_patients_by_month') }}</h2>
                <div class="relative h-52">
                    <canvas id="chartPatientsByMonth" x-ref="chartPatientsByMonth"></canvas>
                </div>
            </div>
        </div>

        {{-- Chart data as JSON for Alpine/JS --}}
        <script id="chart-data" type="application/json">
        {
            "byDay":    @json(json_decode($appointmentsByDay)),
            "byStatus": @json(json_decode($appointmentsByStatus)),
            "byType":   @json(json_decode($appointmentsByType)),
            "byMonth":  @json(json_decode($newPatientsByMonth))
        }
        </script>

        {{-- Top Doctors --}}
        @if(count($topDoctors) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('reports.top_doctors') }}</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-xs text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left font-medium py-2 pr-4">#</th>
                            <th class="text-left font-medium py-2 pr-4">{{ __('general.doctor') }}</th>
                            <th class="text-right font-medium py-2 pr-4">{{ __('reports.total_appointments') }}</th>
                            <th class="text-right font-medium py-2 pr-4">{{ __('reports.completed') }}</th>
                            <th class="text-right font-medium py-2">{{ __('reports.completion_rate') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topDoctors as $i => $doc)
                        <tr class="border-b border-gray-100 dark:border-gray-700/50 last:border-b-0">
                            <td class="py-2 pr-4 text-gray-500 dark:text-gray-400">{{ $i + 1 }}</td>
                            <td class="py-2 pr-4 font-medium text-gray-900 dark:text-white">{{ $doc['name'] }}</td>
                            <td class="py-2 pr-4 text-right tabular-nums text-gray-700 dark:text-gray-200">{{ $doc['total'] }}</td>
                            <td class="py-2 pr-4 text-right tabular-nums text-green-600 dark:text-green-400">{{ $doc['completed'] }}</td>
                            <td class="py-2 text-right tabular-nums font-medium text-primary">{{ $doc['rate'] }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Print-only report header --}}
        @php
            $clinicLogo = $clinic->branding['logo'] ?? null;
            $clinicLogoUrl = $clinicLogo ? asset('storage/'.ltrim($clinicLogo, '/')) : null;
            $clinicContact = collect([$clinic->phone ?? null, $clinic->email ?? null])->filter()->implode(' · ');
            $clinicAddress = $clinic->address ?? null;
            $activeFilters = [];
            if ($doctorFilter) {
                $doc = collect($doctors)->firstWhere('id', (int) $doctorFilter);
                $activeFilters[] = __('general.doctor').': '.($doc->name ?? $doctorFilter);
            }
            if ($statusFilter) { $activeFilters[] = __('general.status').': '.__('reports.status_'.$statusFilter); }
            if ($typeFilter) { $activeFilters[] = __('appointments.type').': '.__('reports.type_'.str_replace('_', '', $typeFilter)); }
        @endphp
        <div id="print-report-header" class="hidden">
            <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:2px solid #4f46e5;padding-bottom:10px;margin-bottom:14px;gap:12px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    @if($clinicLogoUrl)
                        <img src="{{ $clinicLogoUrl }}" alt="{{ $clinic->name }}" style="height:48px;width:auto;max-width:160px;object-fit:contain;">
                    @endif
                    <div>
                        <div style="font-size:18px;font-weight:700;color:#111827;line-height:1.2;">{{ $clinic->name }}</div>
                        <div style="font-size:12px;color:#6b7280;margin-top:2px;">{{ __('reports.title') }} — {{ __('reports.subtitle') }}</div>
                        @if($clinicContact)
                            <div style="font-size:10px;color:#9ca3af;margin-top:1px;">{{ $clinicContact }}</div>
                        @endif
                    </div>
                </div>
                <div style="text-align:right;font-size:11px;color:#6b7280;line-height:1.5;">
                    <div><strong>{{ $dateFrom }}</strong> – <strong>{{ $dateTo }}</strong></div>
                    <div>{{ __('reports.generated_at') }}: {{ now()->format('d/m/Y H:i') }}</div>
                    @if(count($activeFilters))
                        <div style="font-size:10px;color:#9ca3af;margin-top:2px;max-width:300px;">{{ __('reports.filters') }}: {{ implode(' · ', $activeFilters) }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

<style>
@page {
    size: A4 landscape;
    margin: 12mm 10mm;
}

@media print {
    /* ── Hide layout chrome ── */
    nav,
    .account-status-banner,
    [class*="bg-amber-50"] {
        display: none !important;
    }
    /* Fixed-position elements (toasts, loading overlay, etc.) */
    .fixed, [class*="fixed"] {
        display: none !important;
    }

    /* ── Hide interactive elements ── */
    .no-print {
        display: none !important;
    }

    /* ── Page background ── */
    html, body {
        background: #ffffff !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* ── Remove outer layout spacing ── */
    body > div.min-h-screen {
        background: #ffffff !important;
        min-height: unset !important;
    }

    #reports-root {
        padding: 0 !important;
    }

    #reports-root .max-w-7xl {
        max-width: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* ── Show print-only header ── */
    #print-report-header {
        display: block !important;
    }

    /* ── Cards: no shadows, preserve borders ── */
    .shadow-sm {
        box-shadow: none !important;
    }
    .rounded-xl {
        border-radius: 6px !important;
        page-break-inside: avoid;
        break-inside: avoid;
    }

    /* ── Stat cards: force 3-col grid on print ── */
    #reports-root .grid.grid-cols-2 {
        display: grid !important;
        grid-template-columns: repeat(6, 1fr) !important;
        gap: 8px !important;
    }

    /* ── Charts grid: 2 equal columns (mejor lectura en A4 landscape) ── */
    #reports-root .lg\\:grid-cols-3 {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 8px !important;
    }

    /* Top doctors table on print: keep together */
    table { font-size: 10px !important; }
    h1 { font-size: 16px !important; }
    h2 { font-size: 11px !important; }

    /* ── Chart canvas/images height ── */
    #reports-root canvas,
    #reports-root .chart-print-img {
        max-height: 200px !important;
        height: 200px !important;
    }

    /* ── Chart containers: fixed height so images fit ── */
    #reports-root .h-48 {
        height: 200px !important;
    }
    #reports-root .h-52 {
        height: 200px !important;
    }
}
</style>

<script>
const STATUS_COLORS = {
    scheduled:   '#6366f1',
    confirmed:   '#3b82f6',
    waiting:     '#f59e0b',
    in_progress: '#8b5cf6',
    completed:   '#10b981',
    cancelled:   '#ef4444',
    no_show:     '#6b7280',
};

const TYPE_COLORS = ['#6366f1','#3b82f6','#10b981','#f59e0b','#ef4444'];

function getChartData() {
    const el = document.getElementById('chart-data');
    return el ? JSON.parse(el.textContent) : null;
}

function isDark() {
    return document.documentElement.classList.contains('dark');
}

function textColor() {
    return isDark() ? '#9ca3af' : '#6b7280';
}

function gridColor() {
    return isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
}

let charts = {};

function destroyAll() {
    Object.values(charts).forEach(c => c?.destroy());
    charts = {};
}

function buildCharts() {
    destroyAll();
    const data = getChartData();
    if (!data) return;
    const ChartJs = window.Chart;
    if (!ChartJs) return; // wait for Vite bundle to load

    const text = textColor();
    const grid = gridColor();

    // --- Line chart: by day ---
    const ctxDay = document.getElementById('chartByDay');
    if (ctxDay && data.byDay?.labels?.length) {
        charts.byDay = new ChartJs(ctxDay, {
            type: 'line',
            data: {
                labels: data.byDay.labels,
                datasets: [{
                    label: '',
                    data: data.byDay.values,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.12)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: data.byDay.labels.length > 30 ? 0 : 3,
                    pointHoverRadius: 5,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                animation: false,
                plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
                scales: {
                    x: { grid: { color: grid }, ticks: { color: text, maxTicksLimit: 10 } },
                    y: { grid: { color: grid }, ticks: { color: text, stepSize: 1, precision: 0 }, beginAtZero: true }
                }
            }
        });
    }

    // --- Doughnut chart: by status ---
    const ctxStatus = document.getElementById('chartByStatus');
    if (ctxStatus && data.byStatus?.labels?.length) {
        charts.byStatus = new ChartJs(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: data.byStatus.labels,
                datasets: [{
                    data: data.byStatus.values,
                    backgroundColor: data.byStatus.colors || data.byStatus.labels.map(() => '#9ca3af'),
                    borderWidth: 2,
                    borderColor: isDark() ? '#1f2937' : '#ffffff',
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                animation: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: text, boxWidth: 10, padding: 10, font: { size: 11 } } }
                }
            }
        });
    }

    // --- Doughnut chart: by type ---
    const ctxType = document.getElementById('chartByType');
    if (ctxType && data.byType?.labels?.length) {
        charts.byType = new ChartJs(ctxType, {
            type: 'doughnut',
            data: {
                labels: data.byType.labels,
                datasets: [{
                    data: data.byType.values,
                    backgroundColor: TYPE_COLORS,
                    borderWidth: 2,
                    borderColor: isDark() ? '#1f2937' : '#ffffff',
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                animation: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: text, boxWidth: 10, padding: 10, font: { size: 11 } } }
                }
            }
        });
    }

    // --- Bar chart: new patients by month ---
    const ctxPm = document.getElementById('chartPatientsByMonth');
    if (ctxPm && data.byMonth?.labels?.length) {
        charts.byMonth = new ChartJs(ctxPm, {
            type: 'bar',
            data: {
                labels: data.byMonth.labels,
                datasets: [{
                    label: '',
                    data: data.byMonth.values,
                    backgroundColor: 'rgba(59,130,246,0.7)',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                animation: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: text } },
                    y: { grid: { color: grid }, ticks: { color: text, stepSize: 1, precision: 0 }, beginAtZero: true }
                }
            }
        });
    }
}

// ── Canvas → Image conversion for print ──────────────────────────
// Strategy: render each chart on an OFFSCREEN canvas with fixed dimensions
// (independent from responsive viewport changes during print preview).
let _printReplacements = [];

function chartConfigFor(chartKey, data) {
    const text = '#374151';
    const grid = 'rgba(0,0,0,0.08)';
    const ChartJs = window.Chart;
    if (!ChartJs) return null;

    if (chartKey === 'byDay' && data.byDay?.labels?.length) {
        return {
            type: 'line',
            data: {
                labels: data.byDay.labels,
                datasets: [{
                    label: '',
                    data: data.byDay.values,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.20)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: data.byDay.labels.length > 30 ? 0 : 3,
                    pointBackgroundColor: '#6366f1',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: false, maintainAspectRatio: false, animation: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: grid }, ticks: { color: text, maxTicksLimit: 10 } },
                    y: { grid: { color: grid }, ticks: { color: text, stepSize: 1, precision: 0 }, beginAtZero: true }
                }
            }
        };
    }
    if (chartKey === 'byStatus' && data.byStatus?.labels?.length) {
        return {
            type: 'doughnut',
            data: {
                labels: data.byStatus.labels,
                datasets: [{
                    data: data.byStatus.values,
                    backgroundColor: data.byStatus.colors || data.byStatus.labels.map(() => '#9ca3af'),
                    borderWidth: 2, borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: false, maintainAspectRatio: false, animation: false,
                cutout: '65%',
                plugins: { legend: { position: 'bottom', labels: { color: text, boxWidth: 10, padding: 8, font: { size: 11 } } } }
            }
        };
    }
    if (chartKey === 'byType' && data.byType?.labels?.length) {
        return {
            type: 'doughnut',
            data: {
                labels: data.byType.labels,
                datasets: [{
                    data: data.byType.values,
                    backgroundColor: TYPE_COLORS,
                    borderWidth: 2, borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: false, maintainAspectRatio: false, animation: false,
                cutout: '65%',
                plugins: { legend: { position: 'bottom', labels: { color: text, boxWidth: 10, padding: 8, font: { size: 11 } } } }
            }
        };
    }
    if (chartKey === 'byMonth' && data.byMonth?.labels?.length) {
        return {
            type: 'bar',
            data: {
                labels: data.byMonth.labels,
                datasets: [{
                    label: '',
                    data: data.byMonth.values,
                    backgroundColor: 'rgba(59,130,246,0.7)',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: false, maintainAspectRatio: false, animation: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: text } },
                    y: { grid: { color: grid }, ticks: { color: text, stepSize: 1, precision: 0 }, beginAtZero: true }
                }
            }
        };
    }
    return null;
}

/**
 * Render a chart on an offscreen canvas (fixed size, no responsive resize)
 * and return its dataURL — completely independent from the visible chart.
 */
function renderOffscreen(chartKey, width = 900, height = 320) {
    const data = getChartData();
    if (!data) return null;
    const cfg = chartConfigFor(chartKey, data);
    if (!cfg) return null;

    const off = document.createElement('canvas');
    off.width = width;
    off.height = height;
    // Paint white background first so PNG isn't transparent
    const ctx = off.getContext('2d');
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, width, height);

    let dataUrl = null;
    try {
        const c = new window.Chart(off, cfg);
        c.update('none');
        c.draw();
        dataUrl = off.toDataURL('image/png');
        c.destroy();
    } catch (e) {
        console.warn('Offscreen render failed for', chartKey, e);
        return null;
    }
    return dataUrl;
}

const CANVAS_TO_KEY = {
    chartByDay: { key: 'byDay', w: 1400, h: 360 },
    chartByStatus: { key: 'byStatus', w: 600, h: 480 },
    chartByType: { key: 'byType', w: 600, h: 480 },
    chartPatientsByMonth: { key: 'byMonth', w: 600, h: 360 },
};

function canvasesToImages() {
    if (_printReplacements.length > 0) return; // already converted
    document.querySelectorAll('#reports-root canvas').forEach(canvas => {
        const meta = CANVAS_TO_KEY[canvas.id];
        if (!meta) return;
        const dataUrl = renderOffscreen(meta.key, meta.w, meta.h);
        if (!dataUrl || dataUrl.length < 200) return;
        const img = new Image();
        img.src = dataUrl;
        img.className = 'chart-print-img';
        img.style.cssText = `width:100%;height:${canvas.offsetHeight || 200}px;display:block;object-fit:contain;`;
        canvas.parentNode.insertBefore(img, canvas);
        canvas.style.display = 'none';
        _printReplacements.push({ canvas, img });
    });
}

function restoreCanvases() {
    _printReplacements.forEach(({ canvas, img }) => {
        canvas.style.display = '';
        img.remove();
    });
    _printReplacements = [];
}

// ── JS-driven hide/restore of layout chrome ──
// Some browsers (notably Safari) ignore @media print on attribute selectors.
// We force visibility off via inline style and restore after print.
let _hiddenForPrint = [];
function hideForPrint() {
    if (_hiddenForPrint.length > 0) return;
    const selectors = [
        'nav',
        '[class*="account-status"]',
        '[class*="upgrade-nudge"]',
        '[role="alert"]',
        '#reports-root .no-print',
    ];
    document.querySelectorAll(selectors.join(',')).forEach(el => {
        _hiddenForPrint.push({ el, prev: el.style.display });
        el.style.display = 'none';
    });
}
function restoreAfterPrint() {
    _hiddenForPrint.forEach(({ el, prev }) => { el.style.display = prev || ''; });
    _hiddenForPrint = [];
}

window.addEventListener('beforeprint', () => { canvasesToImages(); hideForPrint(); });
window.addEventListener('afterprint', () => { restoreCanvases(); restoreAfterPrint(); });

// Ensure Chart.js is loaded and visible charts are instantiated before printing
function whenChartsReady(maxWaitMs = 1500) {
    return new Promise(resolve => {
        const start = Date.now();
        (function check() {
            const chartsCount = Object.values(charts).filter(Boolean).length;
            if (window.Chart && chartsCount > 0) return resolve(true);
            if (Date.now() - start > maxWaitMs) return resolve(false);
            setTimeout(check, 80);
        })();
    });
}

async function printReportPdf() {
    await whenChartsReady();
    canvasesToImages();
    hideForPrint();
    // Give the browser two frames to actually paint the inserted <img> tags.
    requestAnimationFrame(() => requestAnimationFrame(() => {
        setTimeout(() => window.print(), 100);
    }));
}

window.printReportPdf = printReportPdf;

// Expose to Alpine
window.reportsPage = function() {
    return {
        init() {
            // Chart.js (app.js) is a module — may load slightly after Alpine init.
            // Try immediately; if Chart is not ready yet, retry once after short delay.
            if (window.Chart) {
                buildCharts();
            } else {
                setTimeout(() => buildCharts(), 300);
            }
        },
        refreshCharts() {
            // Livewire has re-rendered — rebuild charts from fresh JSON
            this.$nextTick(() => buildCharts());
        }
    };
};

document.addEventListener('livewire:init', () => {
    // Livewire 3: redraw charts after each successful component commit.
    Livewire.hook('commit', ({ component, succeed }) => {
        succeed(() => {
            if (component?.name?.includes('reports')) {
                requestAnimationFrame(() => buildCharts());
            }
        });
    });
});
</script>
</div>
