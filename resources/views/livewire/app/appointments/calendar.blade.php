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
                <a href="{{ route('app.appointments.schedule', ['clinic' => $clinicSlug]) }}"
                   wire:navigate
                   class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                    {{ __('appointments.schedule_view_title') }}
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

        {{-- Popover de cita (acciones rápidas) --}}
        <div x-data="calendarPopover()"
             @appointment-popover.window="open($event.detail)"
             @click.away="close()"
             @keydown.escape.window="close()"
             x-show="show"
             x-cloak
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             :style="`position:fixed;left:${pos.x}px;top:${pos.y}px;z-index:9999;min-width:240px;max-width:280px`"
             class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 p-4">

            {{-- Header --}}
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate" x-text="event.title"></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        <span x-text="event.time"></span>
                        <span class="mx-1">·</span>
                        <span x-text="event.doctor"></span>
                    </p>
                </div>
                <button @click="close()" class="ml-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Actions --}}
            <div class="space-y-1.5">
                {{-- Ver cita --}}
                <a :href="event.url"
                   class="flex items-center gap-2 w-full px-3 py-1.5 rounded-lg text-xs font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/70 transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    {{ __('general.view') }}
                </a>

                {{-- Email recordatorio --}}
                <template x-if="event.email && canRemind">
                    <button @click="sendEmailReminder()"
                            :disabled="loading"
                            class="flex items-center gap-2 w-full px-3 py-1.5 rounded-lg text-xs font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/70 disabled:opacity-50 transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        {{ __('appointments.send_email_reminder') }}
                    </button>
                </template>

                {{-- WhatsApp --}}
                <template x-if="event.wa_url && canRemind">
                    <a :href="event.wa_url" target="_blank" rel="noopener noreferrer"
                       class="flex items-center gap-2 w-full px-3 py-1.5 rounded-lg text-xs font-medium text-green-700 dark:text-green-300 bg-green-50 dark:bg-green-900/40 hover:bg-green-100 dark:hover:bg-green-900/70 transition">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        {{ __('appointments.send_whatsapp_reminder') }}
                    </a>
                </template>
            </div>
        </div>
    </div>

    <style>
        /* Tipografía general escalada al tamaño de la app (text-sm Tailwind ~ 0.875rem) */
        .fc-controclinic { font-size: 0.8125rem; }
        .fc-controclinic .fc-toolbar { gap: 0.5rem; flex-wrap: wrap; }
        .fc-controclinic .fc-toolbar-title { font-size: 1rem; font-weight: 600; }
        .fc-controclinic .fc-col-header-cell-cushion { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; padding: 0.5rem 0.25rem; }
        .fc-controclinic .fc-daygrid-day-number { font-size: 0.75rem; padding: 0.25rem 0.4rem; }
        .fc-controclinic .fc-event { font-size: 0.7rem; line-height: 1.1rem; padding: 1px 4px; border-radius: 3px; }
        .fc-controclinic .fc-list-event-title,
        .fc-controclinic .fc-list-event-time { font-size: 0.8125rem; }

        /* --- Botones: paleta variada --- */
        /* Reset el morado por defecto de FC */
        .fc-controclinic .fc-button-primary {
            background: #ffffff;
            border: 1px solid #d1d5db;        /* gray-300 */
            color: #374151;                   /* gray-700 */
            box-shadow: none;
            text-transform: none;
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.375rem 0.75rem;
            line-height: 1rem;
        }
        .fc-controclinic .fc-button-primary:hover {
            background: #f3f4f6;              /* gray-100 */
            border-color: #9ca3af;            /* gray-400 */
            color: #111827;
        }
        .fc-controclinic .fc-button-primary:focus,
        .fc-controclinic .fc-button-primary:focus-visible {
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.35);
            outline: none;
        }
        .fc-controclinic .fc-button-primary:disabled {
            background: #f9fafb;
            color: #9ca3af;
            border-color: #e5e7eb;
        }
        /* Estado activo (vista seleccionada): morado de marca */
        .fc-controclinic .fc-button-primary:not(:disabled).fc-button-active,
        .fc-controclinic .fc-button-primary:not(:disabled):active {
            background: #4f46e5;              /* indigo-600 */
            border-color: #4338ca;
            color: #ffffff;
        }
        /* Botones prev/next: estilo icono */
        .fc-controclinic .fc-prev-button,
        .fc-controclinic .fc-next-button {
            background: #eef2ff;              /* indigo-50 */
            border-color: #c7d2fe;            /* indigo-200 */
            color: #4338ca;                   /* indigo-700 */
        }
        .fc-controclinic .fc-prev-button:hover,
        .fc-controclinic .fc-next-button:hover {
            background: #e0e7ff;              /* indigo-100 */
            border-color: #a5b4fc;
            color: #3730a3;
        }
        /* Botón "Hoy": estilo verde para destacarlo como acción contextual */
        .fc-controclinic .fc-today-button {
            background: #ecfdf5;              /* emerald-50 */
            border-color: #a7f3d0;            /* emerald-200 */
            color: #047857;                   /* emerald-700 */
            font-weight: 600;
        }
        .fc-controclinic .fc-today-button:hover {
            background: #d1fae5;
            border-color: #6ee7b7;
            color: #065f46;
        }
        .fc-controclinic .fc-today-button:disabled {
            background: #f3f4f6;
            border-color: #e5e7eb;
            color: #9ca3af;
        }

        /* Eventos cancelados visualmente atenuados */
        .fc-event-cancelled { opacity: 0.55; text-decoration: line-through; }

        /* --- Dark mode --- */
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

        .dark .fc-controclinic .fc-button-primary {
            background: #374151;              /* gray-700 */
            border-color: #4b5563;            /* gray-600 */
            color: #e5e7eb;
        }
        .dark .fc-controclinic .fc-button-primary:hover {
            background: #4b5563;
            border-color: #6b7280;
            color: #f9fafb;
        }
        .dark .fc-controclinic .fc-button-primary:not(:disabled).fc-button-active,
        .dark .fc-controclinic .fc-button-primary:not(:disabled):active {
            background: #6366f1;              /* indigo-500 */
            border-color: #4f46e5;
            color: #ffffff;
        }
        .dark .fc-controclinic .fc-prev-button,
        .dark .fc-controclinic .fc-next-button {
            background: #312e81;              /* indigo-900 */
            border-color: #4338ca;
            color: #c7d2fe;
        }
        .dark .fc-controclinic .fc-prev-button:hover,
        .dark .fc-controclinic .fc-next-button:hover {
            background: #3730a3;
            color: #e0e7ff;
        }
        .dark .fc-controclinic .fc-today-button {
            background: #064e3b;              /* emerald-900 */
            border-color: #047857;
            color: #6ee7b7;
        }
        .dark .fc-controclinic .fc-today-button:hover {
            background: #065f46;
            color: #a7f3d0;
        }
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
                            if (info.event.extendedProps.isUnavailability) return;
                            info.jsEvent.preventDefault();
                            const rect = info.el.getBoundingClientRect();
                            const popX = Math.min(rect.left, window.innerWidth - 296);
                            const popY = Math.min(rect.bottom + 6, window.innerHeight - 260);
                            window.dispatchEvent(new CustomEvent('appointment-popover', {
                                detail: {
                                    id:     info.event.id,
                                    title:  info.event.title,
                                    time:   info.event.extendedProps.time,
                                    doctor: info.event.extendedProps.doctor,
                                    status: info.event.extendedProps.status,
                                    email:  info.event.extendedProps.email,
                                    wa_url: info.event.extendedProps.wa_url,
                                    url:    info.event.url,
                                    x: popX,
                                    y: popY,
                                }
                            }));
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

                    // Toast from popover actions (e.g. email reminder)
                    window.addEventListener('calendar-toast', (e) => {
                        this.showToast(e.detail.success, e.detail.message);
                    });
                },

                showToast(success, message) {
                    this.toast = { show: true, success, message };
                    setTimeout(() => this.toast.show = false, 3500);
                },
            }
        }

        function calendarPopover() {
            return {
                show: false,
                loading: false,
                event: {},
                pos: { x: 0, y: 0 },
                canRemind: @json(auth()->user()?->can('appointments.edit') ?? false),

                open(detail) {
                    this.event = detail;
                    this.pos = { x: detail.x, y: detail.y };
                    this.loading = false;
                    this.show = true;
                },

                close() {
                    this.show = false;
                },

                async sendEmailReminder() {
                    if (this.loading) return;
                    this.loading = true;
                    try {
                        const res = await this.$wire.sendEmailReminder(this.event.id);
                        window.dispatchEvent(new CustomEvent('calendar-toast', { detail: res }));
                    } catch {
                        window.dispatchEvent(new CustomEvent('calendar-toast', {
                            detail: { success: false, message: 'Error' }
                        }));
                    } finally {
                        this.loading = false;
                        this.close();
                    }
                },
            }
        }
    </script>
</div>
