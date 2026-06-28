<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-help-banner module="appointments" />
        {{-- Header --}}
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ __('appointments.title') }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('appointments.todays_appointments') }}: {{ $appointments->where('appointment_date', today())->count() }}
                </p>
            </div>
            @canany(['appointments.create', 'appointments.export', 'appointments.print'])
            <div class="mt-4 sm:mt-0 flex space-x-2">
                @canany(['appointments.export', 'appointments.print'])
                <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                    <button type="button" @click="open = !open"
                            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        {{ __('general.export') }}
                        <svg class="w-3 h-3 ml-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.39a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                    </button>
                    <div x-show="open" x-transition x-cloak
                         class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg ring-1 ring-black/5 z-20 overflow-hidden">
                        @can('appointments.export')
                        <button type="button" wire:click="exportCsv" @click="open = false"
                                class="w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                            {{ __('general.export_csv') }}
                        </button>
                        @endcan
                        @can('appointments.print')
                        <button type="button" wire:click="exportPdf" @click="open = false"
                                class="w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <svg class="w-4 h-4 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                            {{ __('general.export_pdf') }}
                        </button>
                        @endcan
                    </div>
                </div>
                @endcanany
                @can('appointments.create')
                @if($currentClinic->canAddAppointmentThisMonth())
                    <a href="{{ route('app.appointments.create', ['clinic' => $currentClinic->slug]) }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('appointments.new_appointment') }}
                    </a>
                @else
                    <x-upgrade-nudge type="button" :clinic-slug="$currentClinic->slug" />
                @endif
                @endcan
            </div>
            @endcanany
        </div>

        {{-- Flash Messages --}}
        @if (session()->has('success'))
        <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/50 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm font-medium text-green-800 dark:text-green-200">
                    {{ session('success') }}
                </p>
            </div>
        </div>
        @endif

        @if (session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/50 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm font-medium text-red-800 dark:text-red-200">
                    {{ session('error') }}
                </p>
            </div>
        </div>
        @endif

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                {{-- Date single day (default: today) --}}
                <div>
                    <label for="dateFilter" class="sr-only">{{ __('appointments.date') }}</label>
                    <input wire:model.live="dateFilter"
                           type="date"
                           id="dateFilter"
                           class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                {{-- Search --}}
                <div class="md:col-span-2">
                    <label for="search" class="sr-only">{{ __('general.search') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search"
                               type="text"
                               id="search"
                               placeholder="{{ __('appointments.search_patient') }}"
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>

                {{-- Status Filter --}}
                <div>
                    <label for="status" class="sr-only">{{ __('appointments.status') }}</label>
                    <select wire:model.live="status"
                            id="status"
                            class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">{{ __('appointments.all_statuses') }}</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Doctor Filter --}}
                <div>
                    <label for="doctorId" class="sr-only">{{ __('appointments.doctor') }}</label>
                    <select wire:model.live="doctorId"
                            id="doctorId"
                            class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">{{ __('appointments.all_doctors') }}</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date range from --}}
                <div>
                    <label for="dateFrom" class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('appointments.date_from') }}</label>
                    <input wire:model.live="dateFrom"
                           type="date"
                           id="dateFrom"
                           class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                {{-- Date range to --}}
                <div>
                    <label for="dateTo" class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('appointments.date_to') }}</label>
                    <input wire:model.live="dateTo"
                           type="date"
                           id="dateTo"
                           class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                {{-- Created Via Filter --}}
                <div>
                    <label for="createdViaFilter" class="sr-only">{{ __('appointments.created_via') }}</label>
                    <select wire:model.live="createdViaFilter"
                            id="createdViaFilter"
                            class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">{{ __('appointments.all_origins') }}</option>
                        <option value="web">{{ __('appointments.origin_web') }}</option>
                        <option value="app">{{ __('appointments.origin_app') }}</option>
                        <option value="phone">{{ __('appointments.origin_phone') }}</option>
                        <option value="walkin">{{ __('appointments.origin_walkin') }}</option>
                    </select>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="mt-4 flex flex-wrap gap-2">
                <button wire:click="showToday"
                        class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-lg {{ $dateFilter === now()->toDateString() ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }} hover:bg-indigo-100 dark:hover:bg-indigo-900">
                    {{ __('appointments.today') }}
                </button>
                <button wire:click="clearFilters"
                        class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ __('general.clear_filters') }}
                </button>
            </div>
        </div>

        {{-- Skeleton mientras carga --}}
        <x-skeleton-table wire:loading :rows="8" :cols="6" :header="false" class="mt-0" />

        {{-- Table --}}
        <div wire:loading.remove class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            @if($appointments->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ $dateFrom || $dateTo ? __('appointments.date_time') : __('appointments.time') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('appointments.patient') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('appointments.doctor') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('appointments.type') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('appointments.price') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('appointments.invoiced') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('appointments.status') }}
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">{{ __('general.actions') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($appointments as $appointment)
                        <tr class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors" @click="window.location.href='{{ route('app.appointments.show', ['clinic' => $currentClinic->slug, 'appointment' => $appointment->id]) }}'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($dateFrom || $dateTo)
                                <div class="text-xs font-medium text-indigo-600 dark:text-indigo-400">
                                    {{ $appointment->appointment_date->isoFormat('ddd D MMM') }}
                                </div>
                                @endif
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    @if($appointment->start_time)
                                        {{ \Carbon\Carbon::parse($appointment->start_time)->format($currentClinic->timeFormat()) }}
                                    @else
                                        <span class="text-gray-400">--:--</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $appointment->duration_minutes }} {{ __('appointments.minutes') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                            <span class="text-indigo-600 dark:text-indigo-400 font-medium text-sm">
                                                {{ $appointment->patient->initials ?? '?' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $appointment->patient->full_name ?? '-' }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $appointment->patient->phone ?? '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ $appointment->doctor->name ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900 dark:text-white">
                                    {{ $appointment->type_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-700 dark:text-gray-300">
                                @if($appointment->consultation_price)
                                    {{ number_format($appointment->consultation_price, 2) }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                @if($appointment->invoice)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 dark:bg-green-900/40">
                                        <svg class="w-3.5 h-3.5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                        'indigo' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
                                        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'green' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'gray' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                        'red' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                        'orange' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$appointment->status_color] ?? $statusColors['gray'] }}">
                                    {{ $appointment->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <x-table-row-menu>

                                        {{-- Sección: Ver / Editar --}}
                                        <div class="py-1">
                                            <a href="{{ route('app.appointments.show', ['clinic' => $currentClinic->slug, 'appointment' => $appointment->id]) }}"
                                               class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                {{ __('general.view') }}
                                            </a>
                                            @if($appointment->isEditable())
                                                @can('appointments.edit')
                                                <a href="{{ route('app.appointments.edit', ['clinic' => $currentClinic->slug, 'appointment' => $appointment->id]) }}"
                                                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                    <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    {{ __('general.edit') }}
                                                </a>
                                                @endcan
                                            @endif
                                        </div>

                                        {{-- Sección: Cambios de estado / workflow --}}
                                        @php $hasWorkflow = in_array($appointment->status, ['scheduled', 'confirmed', 'waiting', 'in_progress']); @endphp
                                        @if($hasWorkflow)
                                        <div class="py-1">
                                            @if($appointment->status === 'scheduled')
                                            <button type="button" wire:click="confirmAppointment('{{ $appointment->id }}')" @click="open=false"
                                                    class="w-full flex items-center gap-2 px-4 py-2 text-sm text-indigo-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                {{ __('appointments.confirm') }}
                                            </button>
                                            @endif
                                            @if(in_array($appointment->status, ['scheduled', 'confirmed']))
                                            <button type="button" wire:click="checkIn('{{ $appointment->id }}')" @click="open=false"
                                                    class="w-full flex items-center gap-2 px-4 py-2 text-sm text-yellow-700 dark:text-yellow-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/30">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                </svg>
                                                {{ __('appointments.check_in') }}
                                            </button>
                                            @endif
                                            @if($appointment->status === 'waiting')
                                            <button type="button" wire:click="startConsultation('{{ $appointment->id }}')" @click="open=false"
                                                    class="w-full flex items-center gap-2 px-4 py-2 text-sm text-green-700 dark:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/30">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ __('appointments.start_consultation') }}
                                            </button>
                                            @endif
                                            @if($appointment->status === 'in_progress')
                                            <button type="button" wire:click="completeAppointment('{{ $appointment->id }}')" @click="open=false"
                                                    class="w-full flex items-center gap-2 px-4 py-2 text-sm text-green-700 dark:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/30">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ __('appointments.complete') }}
                                            </button>
                                            @endif
                                        </div>
                                        @endif

                                        {{-- Sección: Recordatorios --}}
                                        @if(in_array($appointment->status, ['scheduled', 'confirmed']))
                                            @can('appointments.edit')
                                            @if($appointment->patient?->email || $appointment->patient?->phone)
                                            <div class="py-1">
                                                @if($appointment->patient?->email)
                                                <button type="button" wire:click="sendEmailReminder('{{ $appointment->id }}')" wire:confirm="{{ __('appointments.confirm_send_reminder') }}" @click="open=false"
                                                        class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                    </svg>
                                                    {{ __('appointments.send_email_reminder') }}
                                                </button>
                                                @endif
                                                @if($appointment->patient?->phone)
                                                @php
                                                    $idxLocalPhone = preg_replace('/[^0-9]/', '', $appointment->patient->phone);
                                                    $idxCode       = preg_replace('/[^0-9]/', '', $appointment->patient->phone_country_code
                                                        ?? $currentClinic->settings['phone_country_code'] ?? '');
                                                    $idxPhone = $idxCode . $idxLocalPhone;
                                                    $idxDate  = $appointment->appointment_date ? $appointment->appointment_date->translatedFormat('l d \d\e F') : '';
                                                    $idxTime  = $appointment->start_time ? \Carbon\Carbon::parse($appointment->start_time)->format('H:i') : '';
                                                    $idxMsg   = __('appointments.whatsapp_reminder_message', [
                                                        'patient' => $appointment->patient->first_name,
                                                        'doctor'  => $appointment->doctor?->name ?? '',
                                                        'date'    => $idxDate,
                                                        'time'    => $idxTime,
                                                        'clinic'  => $currentClinic->name,
                                                    ]);
                                                @endphp
                                                <a href="https://wa.me/{{ $idxPhone }}?text={{ rawurlencode($idxMsg) }}"
                                                   target="_blank" rel="noopener noreferrer" @click="open=false"
                                                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                    </svg>
                                                    {{ __('appointments.send_whatsapp_reminder') }}
                                                </a>
                                                @endif
                                            </div>
                                            @endif
                                            @endcan
                                        @endif

                                        {{-- Sección: Cancelar --}}
                                        @if($appointment->isCancellable())
                                            @can('appointments.delete')
                                            <div class="py-1">
                                                <button type="button" wire:click="cancelAppointment('{{ $appointment->id }}')" wire:confirm="{{ __('appointments.confirm_cancel') }}" @click="open=false"
                                                        class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                    {{ __('appointments.cancel') }}
                                                </button>
                                            </div>
                                            @endcan
                                        @endif
                                </x-table-row-menu>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $appointments->links() }}
            </div>
            @else
            {{-- Empty State --}}
            <x-empty-state
                icon="calendar"
                :title="__('appointments.no_appointments')"
                :description="__('appointments.no_appointments_description')"
                :bullets="[__('appointments.empty_state_bullet_1'), __('appointments.empty_state_bullet_2'), __('appointments.empty_state_bullet_3')]"
                :cta-text="__('appointments.new_appointment')"
                :cta-route="route('app.appointments.create', ['clinic' => $currentClinic->slug])"
                cta-permission="appointments.create"
            />
            @endif
        </div>
    </div>
</div>
