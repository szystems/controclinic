<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('general.dashboard') }} - {{ $clinic->name }}
            </h2>
            <div class="flex items-center gap-2">
                @php
                    $isCourtesyFree = $clinic->plan_type === 'free' && $clinic->is_manual_plan;
                    $isPaidPlan = $clinic->plan_type !== 'free';
                    $accessLevel = $clinic->accessLevel();
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @if($accessLevel === \App\Models\Clinic::ACCESS_FULL) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                    @else bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200
                    @endif">
                    {{ $clinic->plan?->name ?? __('admin.plan_type_' . $clinic->plan_type) }}
                </span>
                {{-- Upgrade button: hide if courtesy free or already in read-only (global banner already shows it) --}}
                @if(($clinic->plan_type === 'free' && ! $clinic->is_manual_plan && $accessLevel === \App\Models\Clinic::ACCESS_FULL) || $clinic->plan_type === 'solo')
                    @if(auth()->user()->hasRole('owner'))
                        <a href="{{ route('app.billing.index', $clinic->slug) }}" wire:navigate
                           class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gradient-to-r from-indigo-500 to-purple-500 text-white hover:from-indigo-600 hover:to-purple-600 transition shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                            {{ __('general.upgrade') }}
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Usage limit banner: only for FREE plan in FULL access (courtesy) reaching real usage limits.\n                 If access is read_only/billing_only the global x-account-status-banner already covers it. --}}
            @if($clinic->plan_type === 'free' && $clinic->accessLevel() === \App\Models\Clinic::ACCESS_FULL)
                @php
                    $nearLimit = ($usageStats['patients']['percentage'] >= 80 || $usageStats['appointments']['percentage'] >= 80);
                    $atLimit = ($usageStats['patients']['percentage'] >= 100 || $usageStats['appointments']['percentage'] >= 100);
                @endphp
                @if($atLimit)
                    <div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-4">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                    {{ __('general.limit_reached_title') }}
                                </p>
                                <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                                    {{ __('general.limit_reached_description') }}
                                </p>
                            </div>
                            <a href="{{ route('app.billing.index', $clinic->slug) }}" wire:navigate
                               class="ml-4 flex-shrink-0 inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                                {{ __('general.upgrade_now') }}
                            </a>
                        </div>
                    </div>
                @elseif($nearLimit)
                    <div class="mb-6 rounded-lg bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 p-4">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-amber-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                    {{ __('general.near_limit_title') }}
                                </p>
                                <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                                    {{ __('general.near_limit_description') }}
                                </p>
                            </div>
                            <a href="{{ route('app.billing.index', $clinic->slug) }}" wire:navigate
                               class="ml-4 flex-shrink-0 inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition">
                                {{ __('general.view_plans') }}
                            </a>
                        </div>
                    </div>
                @endif
            @endif

            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                {{-- Pacientes --}}
                <a href="{{ route('app.patients.index', $clinic->slug) }}" wire:navigate
                   class="block bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md hover:-translate-y-0.5 transition group">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3 group-hover:scale-105 transition">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        {{ __('patients.title') }}
                                    </dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-white">
                                        {{ $patientsCount }}
                                    </dd>
                                </dl>
                            </div>
                            <svg class="h-5 w-5 text-gray-300 dark:text-gray-600 group-hover:text-indigo-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>

                {{-- Citas Hoy --}}
                <a href="{{ route('app.appointments.index', $clinic->slug) }}?date={{ now()->toDateString() }}" wire:navigate
                   class="block bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md hover:-translate-y-0.5 transition group">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3 group-hover:scale-105 transition">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        {{ __('appointments.todays_appointments') }}
                                    </dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-white">
                                        {{ $todayAppointments }}
                                    </dd>
                                </dl>
                            </div>
                            <svg class="h-5 w-5 text-gray-300 dark:text-gray-600 group-hover:text-indigo-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>

                {{-- Citas Pendientes --}}
                <a href="{{ route('app.appointments.index', $clinic->slug) }}?status=scheduled&date={{ now()->toDateString() }}" wire:navigate
                   class="block bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md hover:-translate-y-0.5 transition group">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3 group-hover:scale-105 transition">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        {{ __('appointments.pending_today') }}
                                    </dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-white">
                                        {{ $pendingToday }}
                                    </dd>
                                </dl>
                            </div>
                            <svg class="h-5 w-5 text-gray-300 dark:text-gray-600 group-hover:text-indigo-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>

                {{-- Completadas Hoy --}}
                <a href="{{ route('app.appointments.index', $clinic->slug) }}?status=completed&date={{ now()->toDateString() }}" wire:navigate
                   class="block bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md hover:-translate-y-0.5 transition group">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3 group-hover:scale-105 transition">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        {{ __('appointments.completed_today') }}
                                    </dt>
                                    <dd class="text-2xl font-semibold text-gray-900 dark:text-white">
                                        {{ $completedToday }}
                                    </dd>
                                </dl>
                            </div>
                            <svg class="h-5 w-5 text-gray-300 dark:text-gray-600 group-hover:text-indigo-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Quick Actions --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                @if(! $clinic->canAddAppointmentThisMonth())
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-2 border-dashed border-amber-300 dark:border-amber-700">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-gray-300 dark:bg-gray-600 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('appointments.new_appointment') }}</h3>
                                <p class="text-xs text-amber-600 dark:text-amber-400">{{ __('general.appointments_limit_reached') }}</p>
                                <x-upgrade-nudge type="inline" :clinic-slug="$clinic->slug" />
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('app.appointments.create', $clinic->slug) }}" wire:navigate
                       class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('appointments.new_appointment') }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('general.schedule_appointment') }}</p>
                            </div>
                        </div>
                    </a>
                @endif

                @if(! $clinic->canAddPatient())
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-2 border-dashed border-amber-300 dark:border-amber-700">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-gray-300 dark:bg-gray-600 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('patients.new_patient') }}</h3>
                                <p class="text-xs text-amber-600 dark:text-amber-400">{{ __('general.patients_limit_reached') }}</p>
                                <x-upgrade-nudge type="inline" :clinic-slug="$clinic->slug" />
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('app.patients.create', $clinic->slug) }}" wire:navigate
                       class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-teal-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('patients.new_patient') }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('general.register_patient') }}</p>
                            </div>
                        </div>
                    </a>
                @endif

                <a href="{{ route('app.appointments.calendar', $clinic->slug) }}" wire:navigate
                   class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-orange-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('appointments.calendar') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('general.view_full_calendar') }}</p>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Reports quick action (only for users with permission) --}}
            @can('reports.view')
            <div class="grid grid-cols-1 mb-8">
                <a href="{{ route('app.reports', $clinic->slug) }}" wire:navigate
                   class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 border border-indigo-100 dark:border-indigo-800/50 overflow-hidden shadow-sm sm:rounded-lg p-5 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-600 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h13M9 11V5h13m-13 6h13M3 5h2m-2 6h2m-2 6h2"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('reports.title') }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('reports.subtitle') }}</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            </div>
            @endcan

            {{-- Usage Stats + Today's Schedule --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                {{-- Plan Usage --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('general.plan_usage') }}
                            </h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 capitalize">
                                {{ $clinic->plan_type }}
                            </span>
                        </div>

                        <div class="space-y-4">
                            {{-- Patients usage --}}
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('patients.title') }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ $usageStats['patients']['current'] }} / {{ $usageStats['patients']['unlimited'] ? '∞' : $usageStats['patients']['max'] }}
                                    </span>
                                </div>
                                @if(!$usageStats['patients']['unlimited'])
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="h-2 rounded-full transition-all duration-300 {{ $usageStats['patients']['percentage'] >= 100 ? 'bg-red-500' : ($usageStats['patients']['percentage'] >= 80 ? 'bg-amber-500' : 'bg-blue-500') }}"
                                             style="width: {{ min(100, $usageStats['patients']['percentage']) }}%"></div>
                                    </div>
                                @else
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="h-2 rounded-full bg-green-500" style="width: 100%"></div>
                                    </div>
                                @endif
                            </div>

                            {{-- Appointments this month --}}
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('general.appointments_this_month') }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ $usageStats['appointments']['current'] }} / {{ $usageStats['appointments']['unlimited'] ? '∞' : $usageStats['appointments']['max'] }}
                                    </span>
                                </div>
                                @if(!$usageStats['appointments']['unlimited'])
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="h-2 rounded-full transition-all duration-300 {{ $usageStats['appointments']['percentage'] >= 100 ? 'bg-red-500' : ($usageStats['appointments']['percentage'] >= 80 ? 'bg-amber-500' : 'bg-green-500') }}"
                                             style="width: {{ min(100, $usageStats['appointments']['percentage']) }}%"></div>
                                    </div>
                                @else
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="h-2 rounded-full bg-green-500" style="width: 100%"></div>
                                    </div>
                                @endif
                            </div>

                            {{-- Doctors --}}
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('general.doctors') }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ $usageStats['doctors']['current'] }} / {{ $usageStats['doctors']['unlimited'] ? '∞' : $usageStats['doctors']['max'] }}
                                    </span>
                                </div>
                                @if(!$usageStats['doctors']['unlimited'])
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="h-2 rounded-full transition-all duration-300 {{ $usageStats['doctors']['percentage'] >= 100 ? 'bg-red-500' : ($usageStats['doctors']['percentage'] >= 80 ? 'bg-amber-500' : 'bg-purple-500') }}"
                                             style="width: {{ min(100, $usageStats['doctors']['percentage']) }}%"></div>
                                    </div>
                                @else
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="h-2 rounded-full bg-green-500" style="width: 100%"></div>
                                    </div>
                                @endif
                            </div>

                            {{-- Staff --}}
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('general.staff') }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        @if($usageStats['staff']['blocked'])
                                            {{ $usageStats['staff']['current'] }} / 0
                                        @else
                                            {{ $usageStats['staff']['current'] }} / {{ $usageStats['staff']['unlimited'] ? '∞' : $usageStats['staff']['max'] }}
                                        @endif
                                    </span>
                                </div>
                                @if($usageStats['staff']['blocked'])
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="h-2 rounded-full bg-gray-400" style="width: 100%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('general.upgrade_to_add_staff') }}</p>
                                @elseif(!$usageStats['staff']['unlimited'])
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="h-2 rounded-full transition-all duration-300 {{ $usageStats['staff']['percentage'] >= 100 ? 'bg-red-500' : ($usageStats['staff']['percentage'] >= 80 ? 'bg-amber-500' : 'bg-teal-500') }}"
                                             style="width: {{ min(100, $usageStats['staff']['percentage']) }}%"></div>
                                    </div>
                                @else
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="h-2 rounded-full bg-green-500" style="width: 100%"></div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($clinic->plan_type === 'free')
                            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                @php
                                    $remainingPatients = $usageStats['patients']['unlimited'] ? null : max(0, $usageStats['patients']['max'] - $usageStats['patients']['current']);
                                    $remainingAppts = $usageStats['appointments']['unlimited'] ? null : max(0, $usageStats['appointments']['max'] - $usageStats['appointments']['current']);
                                @endphp
                                @if($remainingPatients !== null || $remainingAppts !== null)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                        @if($remainingPatients !== null && $remainingPatients <= 5)
                                            {{ __('general.patients_remaining', ['count' => $remainingPatients]) }}
                                        @endif
                                        @if($remainingAppts !== null && $remainingAppts <= 2)
                                            @if($remainingPatients !== null && $remainingPatients <= 5) · @endif
                                            {{ __('general.appointments_remaining', ['count' => $remainingAppts]) }}
                                        @endif
                                    </p>
                                @endif
                                <x-upgrade-nudge type="inline" :clinic-slug="$clinic->slug" :message="__('general.unlock_unlimited')" />
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Citas de Hoy --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('appointments.todays_appointments') }}
                            </h3>
                            <a href="{{ route('app.appointments.index', $clinic->slug) }}?date={{ now()->toDateString() }}" wire:navigate
                               class="text-xs font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                {{ __('general.view_all') }} →
                            </a>
                        </div>

                        {{-- Sparkline: últimos 14 días --}}
                        <div class="mb-4 pb-4 border-b border-gray-100 dark:border-gray-700">
                            <p class="text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1">{{ __('general.last_14_days') }}</p>
                            <div class="relative h-12">
                                <canvas id="dashboard-sparkline"></canvas>
                            </div>
                        </div>

                        @if($todaySchedule->count() > 0)
                            <div class="space-y-2">
                                @foreach($todaySchedule as $appointment)
                                    <a href="{{ route('app.appointments.show', [$clinic->slug, $appointment->id]) }}" wire:navigate
                                       class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                        <div class="flex items-center min-w-0">
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-blue-500 text-white font-semibold">
                                                    {{ $appointment->patient->initials ?? 'XX' }}
                                                </span>
                                            </div>
                                            <div class="ml-3 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                    {{ $appointment->patient->full_name ?? __('patients.title') }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                    {{ $appointment->start_time ?? '00:00' }} · {{ $appointment->reason ?? __('appointments.consultation') }}
                                                </p>
                                            </div>
                                        </div>
                                        <span class="ml-3 flex-shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $appointment->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                               ($appointment->status === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                               ($appointment->status === 'in_progress' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                               'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200')) }}">
                                            {{ $appointment->status_label ?? $appointment->status }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('appointments.no_appointments_today') }}
                                </p>
                                <a href="{{ route('app.appointments.create', $clinic->slug) }}" wire:navigate
                                   class="mt-3 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-500">
                                    {{ __('appointments.new_appointment') }} →
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Próximas citas (7 días) + Cumpleaños del mes --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                {{-- Upcoming appointments --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('general.upcoming_appointments') }}
                            </h3>
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ __('general.next_7_days') }}</span>
                        </div>

                        @if($upcomingAppointments->count() > 0)
                            <div class="space-y-2">
                                @foreach($upcomingAppointments as $appt)
                                    <a href="{{ route('app.appointments.show', [$clinic->slug, $appt->id]) }}" wire:navigate
                                       class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                        <div class="flex items-center min-w-0">
                                            <div class="flex-shrink-0 text-center w-12">
                                                <p class="text-[10px] uppercase font-medium text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($appt->appointment_date)->isoFormat('ddd') }}</p>
                                                <p class="text-lg font-bold text-gray-900 dark:text-white leading-none">{{ \Carbon\Carbon::parse($appt->appointment_date)->format('d') }}</p>
                                            </div>
                                            <div class="ml-3 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                    {{ $appt->patient->full_name ?? '—' }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                    {{ $appt->start_time ?? '—' }}
                                                    @if($appt->doctor) · {{ $appt->doctor->name }} @endif
                                                </p>
                                            </div>
                                        </div>
                                        <svg class="ml-2 h-4 w-4 text-gray-300 dark:text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-6">{{ __('general.no_upcoming_appointments') }}</p>
                        @endif
                    </div>
                </div>

                {{-- Birthdays this month --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                🎂 {{ __('general.birthdays_this_month') }}
                            </h3>
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ now()->translatedFormat('F') }}</span>
                        </div>

                        @if($birthdaysThisMonth->count() > 0)
                            <div class="space-y-2">
                                @foreach($birthdaysThisMonth as $patient)
                                    @php
                                        $bd = \Carbon\Carbon::parse($patient->birth_date);
                                        $isToday = $bd->day === now()->day;
                                    @endphp
                                    <a href="{{ route('app.patients.show', [$clinic->slug, $patient->id]) }}" wire:navigate
                                       class="flex items-center justify-between p-3 rounded-lg transition
                                       {{ $isToday ? 'bg-pink-50 dark:bg-pink-900/30 hover:bg-pink-100 dark:hover:bg-pink-900/50 border border-pink-200 dark:border-pink-800' : 'bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600' }}">
                                        <div class="flex items-center min-w-0">
                                            <div class="flex-shrink-0 text-center w-12">
                                                <p class="text-[10px] uppercase font-medium text-gray-500 dark:text-gray-400">{{ $bd->isoFormat('MMM') }}</p>
                                                <p class="text-lg font-bold {{ $isToday ? 'text-pink-600 dark:text-pink-400' : 'text-gray-900 dark:text-white' }} leading-none">{{ $bd->format('d') }}</p>
                                            </div>
                                            <div class="ml-3 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                    {{ trim($patient->first_name.' '.$patient->last_name) }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ __('general.turns_age', ['age' => now()->year - $bd->year]) }}
                                                    @if($isToday) · <span class="text-pink-600 dark:text-pink-400 font-medium">{{ __('general.today') }}</span> @endif
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-6">{{ __('general.no_birthdays_this_month') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Clinic Info --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('general.clinic_info') }}
                    </h3>
                    <dl class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('general.current_plan') }}</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ $clinic->plan_type }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('general.timezone_label') }}</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $clinic->timezone ?? 'UTC' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('general.currency_label') }}</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $clinic->currency ?? 'USD' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('general.public_portal') }}</dt>
                            <dd class="text-sm font-medium {{ ($clinic->public_portal_enabled ?? false) ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">
                                {{ ($clinic->public_portal_enabled ?? false) ? __('general.active') : __('general.inactive') }}
                            </dd>
                        </div>
                    </dl>
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('app.settings', $clinic->slug) }}" wire:navigate
                           class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                            {{ __('general.configure_clinic') }} →
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Sparkline data + renderer --}}
    <script id="dashboard-sparkline-data" type="application/json">@json($last14DaysSeries)</script>
    <script>
        (function() {
            function renderSparkline() {
                const el = document.getElementById('dashboard-sparkline');
                const dataEl = document.getElementById('dashboard-sparkline-data');
                if (!el || !dataEl || !window.Chart) return;
                if (el.__chart) { el.__chart.destroy(); }
                let data;
                try { data = JSON.parse(dataEl.textContent); } catch (e) { return; }
                el.__chart = new window.Chart(el, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.values,
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99,102,241,0.18)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 0,
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        animation: false,
                        plugins: { legend: { display: false }, tooltip: {
                            displayColors: false,
                            callbacks: { title: (items) => items[0].label }
                        } },
                        scales: { x: { display: false }, y: { display: false, beginAtZero: true } },
                        elements: { point: { hoverRadius: 3 } },
                    }
                });
            }
            // Try immediately and again on Livewire navigation
            if (window.Chart) renderSparkline(); else setTimeout(renderSparkline, 300);
            document.addEventListener('livewire:navigated', renderSparkline);
        })();
    </script>
</div>
