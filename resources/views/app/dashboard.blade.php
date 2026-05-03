<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('general.dashboard') }} - {{ $currentClinic->name ?? 'ControClinic' }}
            </h2>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                {{ ucfirst($currentClinic->plan_type ?? 'free') }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                {{-- Pacientes --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
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
                                        {{ $patientsCount ?? 0 }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Citas Hoy --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
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
                                        {{ $todayAppointments ?? 0 }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Citas Pendientes --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
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
                                        {{ $pendingToday ?? 0 }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Completadas Hoy --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
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
                                        {{ $completedToday ?? 0 }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <a href="{{ route('app.appointments.create', ['clinic' => $currentClinic->slug ?? 'demo']) }}"
                   class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('appointments.new_appointment') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Agendar una nueva cita</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('app.patients.create', ['clinic' => $currentClinic->slug ?? 'demo']) }}"
                   class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-teal-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('patients.new_patient') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Registrar nuevo paciente</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('app.appointments.calendar', ['clinic' => $currentClinic->slug ?? 'demo']) }}"
                   class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-orange-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('appointments.calendar') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Ver calendario completo</p>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Today's Schedule & Recent Activity --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Citas de Hoy --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('appointments.todays_appointments') }}
                        </h3>

                        @if(isset($todaySchedule) && count($todaySchedule) > 0)
                            <div class="space-y-3">
                                @foreach($todaySchedule as $appointment)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-blue-500 text-white font-semibold">
                                                    {{ $appointment->patient->initials ?? 'XX' }}
                                                </span>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $appointment->patient->full_name ?? 'Paciente' }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $appointment->start_time ?? '00:00' }} - {{ $appointment->reason ?? 'Consulta' }}
                                                </p>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $appointment->status_color ?? 'gray' }}-100 text-{{ $appointment->status_color ?? 'gray' }}-800">
                                            {{ $appointment->status_label ?? $appointment->status }}
                                        </span>
                                    </div>
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
                                <a href="{{ route('app.appointments.create', ['clinic' => $currentClinic->slug ?? 'demo']) }}"
                                   class="mt-3 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-500">
                                    {{ __('appointments.new_appointment') }} →
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Información de la Clínica --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Información de la Clínica
                        </h3>

                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Plan actual</dt>
                                <dd class="text-sm font-medium capitalize
                                    @if($currentClinic->accessLevel() === \App\Models\Clinic::ACCESS_FULL) text-gray-900 dark:text-white
                                    @else text-amber-700 dark:text-amber-400
                                    @endif">
                                    {{ $currentClinic->plan_type ?? 'Free' }}
                                    @if($currentClinic->plan_type === 'free' && ! $currentClinic->is_manual_plan)
                                        <span class="ml-1 text-xs font-normal">({{ __('billing.plan_status_inactive') }})</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Pacientes registrados</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $patientsCount ?? 0 }} / {{ $currentClinic->max_patients == 999999 ? '∞' : $currentClinic->max_patients ?? 25 }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Zona horaria</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $currentClinic->timezone ?? 'America/Guatemala' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Moneda</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $currentClinic->currency ?? 'USD' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Portal público</dt>
                                <dd class="text-sm font-medium text-green-600 dark:text-green-400">
                                    @if($currentClinic->public_portal_enabled ?? false)
                                        Activo
                                    @else
                                        Inactivo
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('app.settings.index', ['clinic' => $currentClinic->slug ?? 'demo']) }}"
                               class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                Configurar clínica →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
