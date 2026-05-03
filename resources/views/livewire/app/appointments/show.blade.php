<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <a href="{{ route('app.appointments.index', ['clinic' => $currentClinic->slug]) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            {{ __('appointments.title') }}
                        </a>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="ml-2 text-gray-900 dark:text-white font-medium">{{ __('appointments.appointment_details') }}</span>
                    </li>
                </ol>
            </nav>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Info --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Appointment Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('appointments.appointment_details') }}
                        </h2>
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
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$appointment->status_color] ?? $statusColors['gray'] }}">
                            {{ $appointment->status_label }}
                        </span>
                        @if($appointment->confirmed_via === 'link')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                {{ __('appointments.confirmed_via_link') }}
                            </span>
                        @endif

                        {{-- Origin badge --}}
                        @if($appointment->created_via)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                @if($appointment->created_via === 'public')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
                                    </svg>
                                @else
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                @endif
                                {{ __('appointments.created_via_' . $appointment->created_via) }}
                            </span>
                        @endif
                    </div>

                    {{-- Status context hint --}}
                    @if($appointment->created_via === 'public' && $appointment->status === 'scheduled')
                    <div class="px-6 pb-4">
                        <div class="rounded-md bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 p-3 flex gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs text-yellow-800 dark:text-yellow-200">{{ __('appointments.status_hint_scheduled_public') }}</p>
                        </div>
                    </div>
                    @elseif($appointment->confirmed_via === 'link')
                    <div class="px-6 pb-4">
                        <div class="rounded-md bg-teal-50 dark:bg-teal-900/30 border border-teal-200 dark:border-teal-700 p-3 flex gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs text-teal-800 dark:text-teal-200">{{ __('appointments.status_hint_confirmed_link') }}</p>
                        </div>
                    </div>
                    @elseif($appointment->created_via === 'staff' && $appointment->status === 'confirmed')
                    <div class="px-6 pb-4">
                        <div class="rounded-md bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 p-3 flex gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs text-green-800 dark:text-green-200">{{ __('appointments.status_hint_confirmed_staff') }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="p-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('appointments.date') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $appointment->appointment_date->isoFormat('dddd') }}, {{ $currentClinic->formatDate($appointment->appointment_date) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('appointments.time') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    @if($appointment->start_time)
                                        {{ \Carbon\Carbon::parse($appointment->start_time)->format($currentClinic->timeFormat()) }}
                                        @if($appointment->end_time)
                                            - {{ \Carbon\Carbon::parse($appointment->end_time)->format($currentClinic->timeFormat()) }}
                                        @endif
                                        <span class="text-gray-500 dark:text-gray-400">({{ $appointment->duration_minutes }} {{ __('appointments.minutes') }})</span>
                                    @else
                                        <span class="text-gray-400">--:--</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('appointments.type') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $appointment->type_label }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('appointments.doctor') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $appointment->doctor->name ?? '-' }}</dd>
                            </div>
                            @if($appointment->room)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('appointments.room') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $appointment->room }}</dd>
                            </div>
                            @endif
                            @if($appointment->reason)
                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('appointments.reason') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $appointment->reason }}</dd>
                            </div>
                            @endif
                            @if($appointment->symptoms)
                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('appointments.symptoms') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $appointment->symptoms }}</dd>
                            </div>
                            @endif
                            @if($appointment->notes)
                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('appointments.notes') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $appointment->notes }}</dd>
                            </div>
                            @endif
                        </dl>

                        {{-- Billing info --}}
                        @if($currentClinic->billingEnabled() && ($appointment->consultation_price !== null || $appointment->is_billable))
                        <div class="mt-6 pt-5 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('appointments.billing_section') }}</span>
                            </div>
                            <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                @if($appointment->consultation_price !== null)
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('appointments.consultation_price') }}</dt>
                                    <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $currentClinic->currency ?? 'USD' }} {{ number_format($appointment->consultation_price, 2) }}
                                    </dd>
                                </div>
                                @endif
                                @if($appointment->consultation_discount && $appointment->consultation_discount > 0)
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('appointments.consultation_discount') }}</dt>
                                    <dd class="mt-1 text-sm font-semibold text-red-600 dark:text-red-400">
                                        - {{ $currentClinic->currency ?? 'USD' }} {{ number_format($appointment->consultation_discount, 2) }}
                                    </dd>
                                </div>
                                @if($appointment->consultation_price !== null)
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('invoices.total') }}</dt>
                                    <dd class="mt-1 text-sm font-bold text-gray-900 dark:text-white">
                                        {{ $currentClinic->currency ?? 'USD' }} {{ number_format(max(0, $appointment->consultation_price - $appointment->consultation_discount), 2) }}
                                    </dd>
                                </div>
                                @endif
                                @endif
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('appointments.is_billable') }}</dt>
                                    <dd class="mt-1">
                                        @if($appointment->is_billable)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">✓ {{ __('appointments.is_billable_hint') }}</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">—</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Timeline --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Timeline</h3>
                    </div>
                    <div class="p-6">
                        <div class="flow-root">
                            <ul>
                                {{-- Created --}}
                                <li>
                                    <div class="relative {{ $appointment->checked_in_at || $appointment->started_at || $appointment->completed_at || $appointment->cancelled_at ? 'pb-8' : '' }}">
                                        @if($appointment->checked_in_at || $appointment->started_at || $appointment->completed_at || $appointment->cancelled_at)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                    <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        @if($appointment->createdBy)
                                                            {{ __('appointments.scheduled_by') }}
                                                            <span class="font-medium text-gray-900 dark:text-white">{{ $appointment->createdBy->name }}</span>
                                                        @else
                                                            {{ __('appointments.status_scheduled') }}
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ $currentClinic->formatDate($appointment->created_at, true) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                {{-- Checked In --}}
                                @if($appointment->checked_in_at)
                                <li>
                                    <div class="relative {{ $appointment->started_at || $appointment->completed_at || $appointment->cancelled_at ? 'pb-8' : '' }}">
                                        @if($appointment->started_at || $appointment->completed_at || $appointment->cancelled_at)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-yellow-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                    <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('appointments.checked_in_at') }}</p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ $currentClinic->formatDate($appointment->checked_in_at, true) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endif

                                {{-- Started --}}
                                @if($appointment->started_at)
                                <li>
                                    <div class="relative {{ $appointment->completed_at || $appointment->cancelled_at ? 'pb-8' : '' }}">
                                        @if($appointment->completed_at || $appointment->cancelled_at)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                    <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('appointments.started_at') }}</p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ $currentClinic->formatDate($appointment->started_at, true) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endif

                                {{-- Completed --}}
                                @if($appointment->completed_at)
                                <li>
                                    <div class="relative">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-gray-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                    <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('appointments.completed_at') }}</p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ $currentClinic->formatDate($appointment->completed_at, true) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endif

                                {{-- Cancelled --}}
                                @if($appointment->cancelled_at)
                                <li>
                                    <div class="relative">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                    <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ __('appointments.cancelled_at') }}
                                                        @if($appointment->cancellation_reason)
                                                            - {{ $appointment->cancellation_reason }}
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ $currentClinic->formatDate($appointment->cancelled_at, true) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Internal Comments --}}
                @can('appointments.edit')
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('appointments.internal_comments') }}</h3>
                    </div>
                    <div class="p-6">
                        {{-- Existing comments --}}
                        @if($appointment->comments->count() > 0)
                        <div class="space-y-4 mb-4">
                            @foreach($appointment->comments as $comment)
                            <div class="flex gap-3">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-xs font-medium text-indigo-700 dark:text-indigo-400">
                                    {{ strtoupper(substr($comment->user->name ?? '?', 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-xs font-medium text-gray-900 dark:text-white">{{ $comment->user->name ?? __('general.deleted_user') }}</p>
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                            @if($comment->user_id === auth()->id() || auth()->user()->hasAnyRole(['owner', 'admin']))
                                            <button wire:click="deleteComment('{{ $comment->id }}')"
                                                    wire:confirm="{{ __('appointments.confirm_delete_comment') }}"
                                                    class="text-red-400 hover:text-red-600 transition">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $comment->body }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-sm text-gray-400 dark:text-gray-500 italic mb-4">{{ __('appointments.no_comments') }}</p>
                        @endif

                        {{-- Add comment --}}
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <textarea wire:model="newComment"
                                      rows="2"
                                      placeholder="{{ __('appointments.add_comment_placeholder') }}"
                                      class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm resize-none"></textarea>
                            @error('newComment') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            <div class="mt-2 flex justify-end">
                                <button wire:click="addComment"
                                        wire:loading.attr="disabled"
                                        wire:target="addComment"
                                        class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-lg font-medium text-xs text-white hover:bg-indigo-700 transition disabled:opacity-50">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ __('appointments.add_comment') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endcan
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Patient Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('appointments.patient') }}</h3>
                    </div>
                    <div class="p-6">
                        @if($appointment->patient)
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 h-12 w-12 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                <span class="text-indigo-600 dark:text-indigo-400 font-medium text-lg">{{ $appointment->patient->initials }}</span>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $appointment->patient->full_name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $appointment->patient->phone }}</div>
                            </div>
                        </div>
                        <a href="{{ route('app.patients.show', ['clinic' => $currentClinic->slug, 'patient' => $appointment->patient->id]) }}"
                           class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-transparent rounded-lg font-medium text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            {{ __('general.view') }} {{ __('appointments.patient') }}
                        </a>
                        @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">-</p>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('general.actions') }}</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        {{-- Workflow Actions --}}
                        @if($appointment->status === 'scheduled')
                            <button wire:click="confirmAppointment"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('appointments.confirm') }}
                            </button>
                        @endif

                        @if(in_array($appointment->status, ['scheduled', 'confirmed']))
                            <button wire:click="checkIn"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-lg font-medium text-xs text-white uppercase tracking-widest hover:bg-yellow-600 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                {{ __('appointments.check_in') }}
                            </button>
                        @endif

                        @if($appointment->status === 'waiting')
                            <button wire:click="startConsultation"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ __('appointments.start_consultation') }}
                            </button>
                        @endif

                        @if($appointment->status === 'in_progress')
                            <button wire:click="completeAppointment"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ __('appointments.complete') }}
                            </button>
                        @endif

                        {{-- Edit --}}
                        @if($appointment->isEditable())
                            @can('appointments.edit')
                            <a href="{{ route('app.appointments.edit', ['clinic' => $currentClinic->slug, 'appointment' => $appointment->id]) }}"
                               class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-transparent rounded-lg font-medium text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                {{ __('general.edit') }}
                            </a>
                            @endcan
                        @endif

                        {{-- PDF Voucher --}}
                        @can('appointments.print')
                        <button type="button" wire:click="exportPdf"
                                class="w-full inline-flex justify-center items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-medium text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                            <svg class="w-4 h-4 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                            </svg>
                            {{ __('general.download_pdf') }}
                        </button>
                        @endcan

                        {{-- Recordatorios: Email + WhatsApp --}}
                        @if(in_array($appointment->status, ['scheduled', 'confirmed']))
                            @can('appointments.edit')

                            {{-- Email reminder --}}
                            @if($appointment->patient?->email)
                            <button type="button" wire:click="sendEmailReminder"
                                    wire:confirm="{{ __('appointments.confirm_send_reminder') }}"
                                    wire:loading.attr="disabled"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-700 disabled:opacity-50 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ __('appointments.send_email_reminder') }}
                            </button>
                            @endif

                            {{-- WhatsApp reminder --}}
                            @if($appointment->patient?->phone)
                            @php
                                $waLocalPhone = preg_replace('/[^0-9]/', '', $appointment->patient->phone);
                                $waCode       = preg_replace('/[^0-9]/', '', $appointment->patient->phone_country_code
                                    ?? $currentClinic->settings['phone_country_code'] ?? '');
                                $waPhone = $waCode . $waLocalPhone;
                                $waDate  = $appointment->appointment_date
                                    ? $appointment->appointment_date->translatedFormat('l d \d\e F \d\e Y')
                                    : '';
                                $waTime  = $appointment->start_time
                                    ? \Carbon\Carbon::parse($appointment->start_time)->format('H:i')
                                    : '';
                                $waMsg = __('appointments.whatsapp_reminder_message', [
                                    'patient' => $appointment->patient->first_name,
                                    'doctor'  => $appointment->doctor?->name ?? '',
                                    'date'    => $waDate,
                                    'time'    => $waTime,
                                    'clinic'  => $currentClinic->name,
                                ]);
                                $waUrl = 'https://wa.me/' . $waPhone . '?text=' . rawurlencode($waMsg);
                            @endphp
                            <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer"
                               class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-500 border border-transparent rounded-lg font-medium text-xs text-white uppercase tracking-widest hover:bg-green-600 transition">
                                {{-- WhatsApp icon --}}
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                {{ __('appointments.send_whatsapp_reminder') }}
                            </a>
                            @endif

                            @endcan
                        @endif

                        {{-- Create medical record from this appointment --}}
                        @can('records.create')
                            @if($currentClinic->canWrite())
                            <a href="{{ route('app.records.create', ['clinic' => $currentClinic->slug, 'patient' => $appointment->patient_id, 'appointment_id' => $appointment->id]) }}"
                               class="w-full inline-flex justify-center items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-medium text-xs text-white uppercase tracking-widest hover:bg-emerald-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ __('records.new_record') }}
                            </a>
                            @endif
                        @endcan

                        {{-- Facturas de esta cita --}}
                        @if($currentClinic->billingEnabled() && $appointment->is_billable)
                            @can('invoices.create')
                            @if($currentClinic->canWrite())
                            @php
                                $allInvoices = $appointment->invoices;
                                $activeInvoices = $allInvoices->filter(fn($inv) => $inv->status !== \App\Models\Invoice::STATUS_CANCELLED);
                                $cancelledInvoices = $allInvoices->filter(fn($inv) => $inv->status === \App\Models\Invoice::STATUS_CANCELLED);
                            @endphp

                            {{-- Botón por cada factura activa --}}
                            @foreach($activeInvoices as $activeInvoice)
                            <a href="{{ route('app.invoices.show', ['clinic' => $currentClinic->slug, 'invoice' => $activeInvoice->id]) }}"
                               class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                </svg>
                                {{ __('invoices.view_invoice') }}
                                @if($activeInvoices->count() > 1)
                                <span class="ml-1 opacity-75">#{{ $activeInvoice->invoice_number }}</span>
                                @endif
                            </a>
                            @endforeach

                            {{-- Generar factura solo si no hay ninguna activa --}}
                            @if($activeInvoices->isEmpty())
                            <a href="{{ route('app.invoices.create', ['clinic' => $currentClinic->slug, 'appointment' => $appointment->id]) }}"
                               class="w-full inline-flex justify-center items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-medium text-xs text-white uppercase tracking-widest hover:bg-emerald-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                </svg>
                                {{ __('invoices.create_invoice') }}
                            </a>
                            @endif

                            {{-- Enlaces discretos a facturas canceladas --}}
                            @if($cancelledInvoices->isNotEmpty())
                            <div class="w-full text-center space-y-1">
                                @foreach($cancelledInvoices as $cancelledInvoice)
                                <a href="{{ route('app.invoices.show', ['clinic' => $currentClinic->slug, 'invoice' => $cancelledInvoice->id]) }}"
                                   class="block text-xs text-gray-400 dark:text-gray-500 underline hover:text-gray-600 dark:hover:text-gray-400">
                                    {{ __('invoices.view_cancelled_invoice') }} #{{ $cancelledInvoice->invoice_number }}
                                </a>
                                @endforeach
                            </div>
                            @endif

                            @endif
                            @endcan
                        @endif

                        {{-- Cancel --}}
                        @if($appointment->isCancellable())
                            @can('appointments.delete')
                            <button wire:click="openCancelModal"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-medium text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                {{ __('appointments.cancel') }}
                            </button>
                            @endcan
                        @endif

                        {{-- No Show --}}
                        @if(in_array($appointment->status, ['scheduled', 'confirmed', 'waiting']))
                            @can('appointments.edit')
                            <button wire:click="markNoShow"
                                    wire:confirm="{{ __('appointments.confirm_no_show') }}"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-orange-600 border border-transparent rounded-lg font-medium text-xs text-white uppercase tracking-widest hover:bg-orange-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                {{ __('appointments.mark_no_show') }}
                            </button>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cancel Modal --}}
    @if($showCancelModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeCancelModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                {{ __('appointments.cancel') }}
                            </h3>
                            <div class="mt-4">
                                <label for="cancellationReason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('appointments.cancellation_reason') }}
                                </label>
                                <textarea wire:model="cancellationReason"
                                          id="cancellationReason"
                                          rows="3"
                                          class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="cancelAppointment"
                            type="button"
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ __('appointments.cancel') }}
                    </button>
                    <button wire:click="closeCancelModal"
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ __('general.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
