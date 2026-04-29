<div class="py-6" x-data="calendarApp()" x-init="init()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="sm:flex sm:items-center sm:justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ __('appointments.calendar') }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('appointments.calendar_hint') }}
                </p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-2">
                <a href="{{ route('app.appointments.index', ['clinic' => $clinicSlug]) }}"
                   class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                    {{ __('appointments.list_view') }}
                </a>
                @can('appointments.create')
                    @if($clinic->canAddAppointmentThisMonth())
                        <a href="{{ route('app.appointments.create', ['clinic' => $clinicSlug]) }}"
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('appointments.new_appointment') }}
                        </a>
                    @else
                        <x-upgrade-nudge type="button" :clinic-slug="$clinicSlug" />
                    @endif
                @endcan
            </div>
        </div>

        {{-- Doctor filter chips --}}
        @if($doctors->count() > 0)
        <div class="mb-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mr-2">
                    {{ __('appointments.filter_by_doctor') }}:
                </span>

                <button type="button"
                        wire:click="clearDoctorFilter"
                        @class([
                            'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium transition',
                            'bg-indigo-600 text-white' => empty($selectedDoctors),
                            'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200' => !empty($selectedDoctors),
                        ])>
                    {{ __('appointments.all_doctors') }}
                </button>

                @foreach($doctors as $doc)
                    @php
                        $isSelected = in_array((string)$doc['id'], $selectedDoctors, true);
                    @endphp
                    <button type="button"
                            wire:click="toggleDoctor({{ $doc['id'] }})"
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium transition border-2"
                            style="border-color: {{ $doc['color'] }}; background-color: {{ $isSelected ? $doc['color'] : 'transparent' }}; color: {{ $isSelected ? '#fff' : $doc['color'] }};">
                        <span class="w-2 h-2 rounded-full mr-2" style="background-color: {{ $doc['color'] }}; @if($isSelected) background-color: #fff; @endif"></span>
                        {{ $doc['name'] }}
                    </button>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Status legend --}}
        <div class="mb-4 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
            <span>{{ __('appointments.status_legend') }}:</span>
            <span class="inline-flex items-center"><span class="inline-block w-3 h-3 rounded-sm mr-1" style="background:#16a34a"></span>{{ __('appointments.status_confirmed') }}</span>
            <span class="inline-flex items-center"><span class="inline-block w-3 h-3 rounded-sm mr-1" style="background:#4b5563"></span>{{ __('appointments.status_scheduled') }}</span>
            <span class="inline-flex items-center"><span class="inline-block w-3 h-3 rounded-sm mr-1" style="background:#ea580c"></span>{{ __('appointments.status_in_progress') }}</span>
            <span class="inline-flex items-center"><span class="inline-block w-3 h-3 rounded-sm mr-1" style="background:#0d9488"></span>{{ __('appointments.status_completed') }}</span>
            <span class="inline-flex items-center"><span class="inline-block w-3 h-3 rounded-sm mr-1" style="background:#9ca3af"></span>{{ __('appointments.status_cancelled') }}</span>
        </div>

        {{-- Calendar container --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div wire:ignore x-ref="calendar" class="fc-controclinic" style="min-height: 650px;"></div>
        </div>

        {{-- Toast --}}
        <div x-show="toast.show"
             x-transition
             @class([
                 'fixed bottom-6 right-6 px-4 py-3 rounded-lg shadow-lg text-white text-sm z-50',
             ])
             :class="toast.success ? 'bg-green-600' : 'bg-red-600'"
             style="display:none;">
            <span x-text="toast.message"></span>
        </div>
    </div>

    <style>
        .fc-controclinic .fc-toolbar-title { font-size: 1.125rem; font-weight: 600; }
        .fc-controclinic .fc-button-primary { background: #4f46e5; border-color: #4f46e5; }
        .fc-controclinic .fc-button-primary:hover,
        .fc-controclinic .fc-button-primary:not(:disabled).fc-button-active { background: #4338ca; border-color: #4338ca; }
        .fc-event-cancelled { opacity: 0.55; text-decoration: line-through; }
        .dark .fc-controclinic { color: #e5e7eb; }
        .dark .fc-controclinic .fc-col-header-cell-cushion,
        .dark .fc-controclinic .fc-daygrid-day-number,
        .dark .fc-controclinic .fc-toolbar-title,
        .dark .fc-controclinic .fc-list-day-cushion a,
        .dark .fc-controclinic .fc-list-event-title a { color: #e5e7eb; }
        .dark .fc-controclinic .fc-theme-standard td,
        .dark .fc-controclinic .fc-theme-standard th,
        .dark .fc-controclinic .fc-scrollgrid,
        .dark .fc-controclinic .fc-list { border-color: #374151; }
        .dark .fc-controclinic .fc-list-day-cushion,
        .dark .fc-controclinic .fc-day-other,
        .dark .fc-controclinic .fc-list-event:hover td { background: #1f2937; }
        .dark .fc-controclinic .fc-day-today { background: rgba(79, 70, 229, 0.12) !important; }
    </style>

    <script>
        function calendarApp() {
            return {
                calendar: null,
                toast: { show: false, success: true, message: '' },

                init() {
                    if (!window.FullCalendar) {
                        console.error('FullCalendar bundle missing');
                        return;
                    }

                    const locale = (document.documentElement.lang || 'es').startsWith('en') ? 'en' : 'es';
                    const canWrite = @json($clinic->canWrite() && auth()->user()?->can('appointments.edit') ? true : false);

                    this.calendar = new window.FullCalendar.Calendar(this.$refs.calendar, {
                        initialView: '{{ $initialView }}',
                        locale: window.FullCalendar.locales[locale] || window.FullCalendar.locales.es,
                        timeZone: '{{ $clinic->timezone ?? config('app.timezone') }}',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
                        },
                        buttonText: {
                            today: @json(__('appointments.today')),
                            month: @json(__('appointments.month_view')),
                            week:  @json(__('appointments.week_view')),
                            day:   @json(__('appointments.day_view')),
                            list:  @json(__('appointments.list_view')),
                        },
                        height: 'auto',
                        nowIndicator: true,
                        slotMinTime: '07:00:00',
                        slotMaxTime: '21:00:00',
                        editable: canWrite,
                        eventStartEditable: canWrite,
                        eventDurationEditable: false,
                        selectable: canWrite,
                        navLinks: true,
                        dayMaxEvents: true,

                        events: (info, success, failure) => {
                            this.$wire.fetchEvents(info.startStr, info.endStr)
                                .then((events) => success(events || []))
                                .catch(() => failure());
                        },

                        eventDidMount: (info) => {
                            const time = info.event.extendedProps.time;
                            const doctor = info.event.extendedProps.doctor;
                            if (time || doctor) {
                                info.el.setAttribute('title', `${time || ''} · ${doctor || ''} · ${info.event.title}`.trim());
                            }
                        },

                        eventClick: (info) => {
                            // Use Livewire SPA navigation when possible
                            if (info.event.url && window.Livewire) {
                                info.jsEvent.preventDefault();
                                window.Livewire.navigate(info.event.url);
                            }
                        },

                        dateClick: (info) => {
                            if (!canWrite) return;
                            const url = '{{ route('app.appointments.create', ['clinic' => $clinicSlug]) }}'
                                + '?date=' + encodeURIComponent(info.dateStr.substring(0, 10))
                                + (info.date.getHours() ? '&time=' + info.date.toTimeString().slice(0, 5) : '');
                            if (window.Livewire) {
                                window.Livewire.navigate(url);
                            } else {
                                window.location.href = url;
                            }
                        },

                        eventDrop: (info) => {
                            if (!canWrite) { info.revert(); return; }
                            const start = info.event.start.toISOString();
                            const end = info.event.end ? info.event.end.toISOString() : null;
                            this.$wire.rescheduleEvent(info.event.id, start, end)
                                .then((res) => {
                                    if (!res || !res.success) {
                                        info.revert();
                                        this.showToast(false, (res && res.message) || 'Error');
                                    } else {
                                        this.showToast(true, res.message);
                                    }
                                })
                                .catch(() => { info.revert(); this.showToast(false, 'Error'); });
                        },
                    });

                    this.calendar.render();

                    // Refresh events when filters change
                    Livewire.on('calendar-refresh', () => {
                        if (this.calendar) this.calendar.refetchEvents();
                    });
                },

                showToast(success, message) {
                    this.toast = { show: true, success, message };
                    setTimeout(() => this.toast.show = false, 3500);
                },
            }
        }
    </script>
</div>
