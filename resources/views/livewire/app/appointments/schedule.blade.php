<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Header --}}
            <div class="sm:flex sm:items-center sm:justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ __('appointments.schedule_view_title') }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('appointments.schedule_view_hint') }}
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 flex flex-wrap gap-2">
                    <a href="{{ route('app.appointments.calendar', ['clinic' => $clinicSlug]) }}"
                       wire:navigate
                       class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ __('appointments.calendar_view') }}
                    </a>
                    @can('appointments.create')
                        @if($clinic->canAddAppointmentThisMonth())
                            <a href="{{ route('app.appointments.create', ['clinic' => $clinicSlug, 'date' => $selectedDate]) }}"
                               wire:navigate
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
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
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mr-1">
                        {{ __('appointments.filter_by_doctor') }}:
                    </span>
                    @foreach($doctors as $doc)
                        @php $hidden = in_array((string)$doc['id'], $hiddenDoctors, true); @endphp
                        <button type="button"
                                wire:click="toggleDoctor({{ $doc['id'] }})"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium transition border-2"
                                style="border-color: {{ $doc['color'] }}; background-color: {{ $hidden ? 'transparent' : $doc['color'] }}; color: {{ $hidden ? $doc['color'] : '#fff' }}; opacity: {{ $hidden ? '0.5' : '1' }};">
                            <span class="w-2 h-2 rounded-full mr-2" style="background-color: {{ $hidden ? $doc['color'] : '#fff' }};"></span>
                            {{ $doc['name'] }}
                        </button>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Date navigation --}}
            <div class="mb-4 flex items-center justify-between bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 px-4 py-3">
                <button wire:click="previousDay"
                        class="p-1.5 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <div class="flex items-center gap-3">
                    <button wire:click="goToToday"
                            class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline px-2 py-1 rounded hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition">
                        {{ __('appointments.today') }}
                    </button>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white capitalize">
                        {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d \d\e F \d\e Y') }}
                    </h2>
                </div>

                <button wire:click="nextDay"
                        class="p-1.5 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            {{-- Grid --}}
            @if($doctors->count() === 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-10 text-center">
                    <svg class="mx-auto w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('appointments.no_doctors_registered') }}</p>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-auto" style="max-height: calc(100vh - 300px); min-height: 400px;">
                        <table class="w-full border-collapse text-sm" style="min-width: {{ 80 + $doctors->reject(fn($d) => in_array((string)$d['id'], $hiddenDoctors, true))->count() * 160 }}px;">
                            {{-- Doctor header --}}
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="sticky top-0 left-0 z-20 w-16 min-w-[4rem] bg-gray-50 dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 px-2 py-3 text-xs font-medium text-gray-400 dark:text-gray-500 text-center">
                                        {{ __('appointments.time') }}
                                    </th>
                                    @foreach($doctors as $doc)
                                        @if(!in_array((string)$doc['id'], $hiddenDoctors, true))
                                            <th class="sticky top-0 z-10 min-w-[160px] px-3 py-3 text-center border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <span class="inline-block w-2.5 h-2.5 rounded-full flex-shrink-0"
                                                          style="background-color: {{ $doc['color'] }};"></span>
                                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200 truncate max-w-[120px]">
                                                        {{ $doc['name'] }}
                                                    </span>
                                                </div>
                                                @php
                                                    $count = count($appointments[$doc['id']] ?? []);
                                                @endphp
                                                @if($count > 0)
                                                    <div class="mt-0.5 text-xs font-medium" style="color: {{ $doc['color'] }};">
                                                        {{ trans_choice('appointments.appointment_count', $count, ['count' => $count]) }}
                                                    </div>
                                                @endif
                                            </th>
                                        @endif
                                    @endforeach
                                </tr>
                            </thead>

                            {{-- Time slot rows --}}
                            <tbody>
                                @foreach($timeSlots as $slotIndex => $slot)
                                    @php
                                        $isHour = (int) substr($slot, 3, 2) === 0; // :00 slots
                                        $nextSlot = $timeSlots[$slotIndex + 1] ?? '21:00';
                                    @endphp
                                    <tr class="{{ $isHour ? 'border-t-2 border-gray-200 dark:border-gray-600' : 'border-t border-gray-100 dark:border-gray-700/50' }}">
                                        {{-- Time label --}}
                                        <td class="sticky left-0 z-10 w-16 min-w-[4rem] bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 px-2 py-0 text-center align-top pt-1">
                                            @if($isHour)
                                                <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ $slot }}</span>
                                            @else
                                                <span class="text-[10px] text-gray-400 dark:text-gray-600">{{ $slot }}</span>
                                            @endif
                                        </td>

                                        {{-- Doctor cells --}}
                                        @foreach($doctors as $doc)
                                            @if(!in_array((string)$doc['id'], $hiddenDoctors, true))
                                                @php
                                                    $cellAppts = collect($appointments[$doc['id']] ?? [])
                                                        ->filter(function ($a) use ($slot, $nextSlot) {
                                                            if (! $a->start_time) {
                                                                return false;
                                                            }
                                                            $t = $a->start_time->format('H:i');
                                                            return $t >= $slot && $t < $nextSlot;
                                                        });

                                                    $canCreate = auth()->user()->can('appointments.create')
                                                        && $clinic->canAddAppointmentThisMonth()
                                                        && $clinic->canWrite();

                                                    $createUrl = route('app.appointments.create', [
                                                        'clinic'     => $clinicSlug,
                                                        'date'       => $selectedDate,
                                                        'time'       => $slot,
                                                        'doctor_id'  => $doc['id'],
                                                    ]);
                                                @endphp
                                                <td class="min-w-[160px] border-r border-gray-200 dark:border-gray-700 p-1 align-top relative group"
                                                    style="min-height: 52px;">
                                                    @forelse($cellAppts as $appt)
                                                        @php
                                                            $patientName = $appt->patient
                                                                ? trim(($appt->patient->first_name ?? '').' '.($appt->patient->last_name ?? ''))
                                                                : __('appointments.no_patient');
                                                            $statusBg = match($appt->status) {
                                                                'confirmed'   => '#dcfce7',
                                                                'waiting'     => '#fef9c3',
                                                                'in_progress' => '#eef2ff',
                                                                'completed'   => '#ccfbf1',
                                                                'cancelled'   => '#f3f4f6',
                                                                'no_show'     => '#fee2e2',
                                                                default       => '#f8fafc',
                                                            };
                                                            $statusBgDark = match($appt->status) {
                                                                'confirmed'   => 'rgba(21,128,61,0.2)',
                                                                'waiting'     => 'rgba(161,98,7,0.2)',
                                                                'in_progress' => 'rgba(67,56,202,0.2)',
                                                                'completed'   => 'rgba(13,148,136,0.2)',
                                                                'cancelled'   => 'rgba(107,114,128,0.15)',
                                                                'no_show'     => 'rgba(185,28,28,0.2)',
                                                                default       => 'rgba(99,102,241,0.1)',
                                                            };
                                                        @endphp
                                                        <a href="{{ route('app.appointments.show', ['clinic' => $clinicSlug, 'appointment' => $appt->id]) }}"
                                                           wire:navigate
                                                           class="block rounded mb-0.5 px-2 py-1 text-xs hover:opacity-90 transition {{ $appt->status === 'cancelled' ? 'opacity-50' : '' }}"
                                                           style="border-left: 3px solid {{ $doc['color'] }}; background-color: {{ $statusBg }};">
                                                            <span class="font-semibold text-gray-800 truncate block leading-tight">{{ $patientName }}</span>
                                                            <span class="text-gray-500 text-[10px]">
                                                                {{ $appt->start_time?->format('H:i') }}
                                                                @if($appt->end_time) – {{ $appt->end_time->format('H:i') }}@endif
                                                            </span>
                                                        </a>
                                                    @empty
                                                        @if($canCreate)
                                                            <a href="{{ $createUrl }}"
                                                               wire:navigate
                                                               class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                                <span class="w-5 h-5 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 dark:text-gray-500 text-base leading-none">+</span>
                                                            </a>
                                                        @endif
                                                    @endforelse
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Status legend --}}
                <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                    <span>{{ __('appointments.status_legend') }}:</span>
                    <span class="inline-flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm" style="background:#dcfce7; border-left: 3px solid #16a34a;"></span>{{ __('appointments.status_confirmed') }}</span>
                    <span class="inline-flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm" style="background:#f8fafc; border-left: 3px solid #4b5563;"></span>{{ __('appointments.status_scheduled') }}</span>
                    <span class="inline-flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm" style="background:#eef2ff; border-left: 3px solid #4f46e5;"></span>{{ __('appointments.status_in_progress') }}</span>
                    <span class="inline-flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm" style="background:#ccfbf1; border-left: 3px solid #0d9488;"></span>{{ __('appointments.status_completed') }}</span>
                    <span class="inline-flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm" style="background:#f3f4f6; border-left: 3px solid #9ca3af;"></span>{{ __('appointments.status_cancelled') }}</span>
                </div>
            @endif

        </div>
    </div>
</div>
