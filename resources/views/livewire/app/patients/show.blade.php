<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-6">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <a href="{{ route('app.patients.index', ['clinic' => $currentClinic->slug]) }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                            {{ __('patients.title') }}
                        </a>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="ml-2 text-gray-700 dark:text-gray-300">{{ $patient->full_name }}</span>
                    </li>
                </ol>
            </nav>

            <div class="sm:flex sm:items-center sm:justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0 h-16 w-16">
                        <div class="h-16 w-16 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                            <span class="text-indigo-600 dark:text-indigo-400 font-bold text-xl">
                                {{ $patient->initials }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                            {{ $patient->full_name }}
                            @if(!$patient->is_active)
                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                {{ __('patients.inactive') }}
                            </span>
                            @endif
                        </h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('patients.medical_record') }}: <span class="font-mono">{{ $patient->medical_record_number ?? 'N/A' }}</span>
                            @if($patient->birth_date)
                            · {{ $patient->age }} {{ __('patients.years_old') }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    @can('patients.print')
                    <button type="button" wire:click="exportPdf"
                       class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600">
                        <svg class="w-4 h-4 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                        </svg>
                        PDF
                    </button>
                    @endcan
                    @can('patients.edit')
                    <a href="{{ route('app.patients.edit', ['clinic' => $currentClinic->slug, 'patient' => $patient->id]) }}"
                       class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        {{ __('general.edit') }}
                    </a>
                    @endcan
                    <a href="{{ route('app.appointments.create', ['clinic' => $currentClinic->slug, 'patient' => $patient->id]) }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ __('appointments.new_appointment') }}
                    </a>
                </div>
            </div>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Contact Information --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        {{ __('patients.contact_info') }}
                    </h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('patients.phone') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $patient->phone ?? '-' }}</dd>
                        </div>
                        @if($patient->phone_secondary)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('patients.phone_secondary') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $patient->phone_secondary }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('patients.email') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $patient->email ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('patients.address') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                @if($patient->address)
                                {{ $patient->address }}<br>
                                {{ $patient->city }}{{ $patient->state ? ', ' . $patient->state : '' }} {{ $patient->postal_code }}
                                @else
                                -
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Medical Information --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        {{ __('patients.medical_info') }}
                    </h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('patients.blood_type') }}</dt>
                            <dd class="mt-1">
                                @if($patient->blood_type)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                    {{ $patient->blood_type }}
                                </span>
                                @else
                                <span class="text-sm text-gray-900 dark:text-white">-</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('patients.gender') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                @if($patient->gender)
                                {{ __('patients.' . $patient->gender) }}
                                @else
                                -
                                @endif
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('patients.allergies') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                @if($patient->allergies)
                                <span class="text-red-600 dark:text-red-400">⚠️ {{ $patient->allergies }}</span>
                                @else
                                {{ __('patients.no_known_allergies') }}
                                @endif
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('patients.chronic_conditions') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $patient->chronic_conditions ?? '-' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('patients.current_medications') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $patient->current_medications ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Recent Medical Records --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ __('patients.recent_records') }}
                        </h2>
                        <div class="flex items-center gap-3">
                            @can('records.create')
                                @if($currentClinic->canWrite())
                                <a href="{{ route('app.records.create', ['clinic' => $currentClinic->slug, 'patient' => $patient->id]) }}"
                                   class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline">
                                    + {{ __('records.new_record') }}
                                </a>
                                @endif
                            @endcan
                            <a href="{{ route('app.records.index', ['clinic' => $currentClinic->slug, 'patient' => $patient->id]) }}"
                               class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900">
                                {{ __('general.view_all') }}
                            </a>
                        </div>
                    </div>
                    @if($recentRecords->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentRecords as $record)
                        <a href="{{ route('app.records.show', ['clinic' => $currentClinic->slug, 'patient' => $patient->id, 'record' => $record->id]) }}"
                           class="flex items-start space-x-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $record->title ?? __('records.' . $record->record_type) }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $record->doctor->name ?? 'N/A' }} · {{ $record->created_at->format('d/m/Y') }}
                                </p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                        {{ __('patients.no_records') }}
                    </p>
                    @endif
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Quick Info --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">
                        {{ __('patients.quick_info') }}
                    </h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('patients.birth_date') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $patient->birth_date ? $patient->birth_date->format('d/m/Y') : '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('patients.id_number') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $patient->id_type }}: {{ $patient->id_number ?? '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('patients.primary_doctor') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $patient->primaryDoctor->name ?? '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('patients.last_visit') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $patient->last_visit_at ? $patient->last_visit_at->diffForHumans() : __('patients.never') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('patients.registered') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $patient->created_at->format('d/m/Y') }}
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Emergency Contact --}}
                @if($patient->emergency_contacts)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">
                        {{ __('patients.emergency_contact') }}
                    </h3>
                    @foreach($patient->emergency_contacts as $contact)
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('patients.contact_name') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $contact['name'] ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('patients.contact_phone') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $contact['phone'] ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('patients.relationship') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $contact['relationship'] ?? '-' }}</dd>
                        </div>
                    </dl>
                    @endforeach
                </div>
                @endif

                {{-- Insurance --}}
                @if($patient->insurance_info)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">
                        {{ __('patients.insurance') }}
                    </h3>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('patients.insurance_provider') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $patient->insurance_info['provider'] ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('patients.policy_number') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">{{ $patient->insurance_info['policy_number'] ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
                @endif

                {{-- Upcoming Appointments --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">
                        {{ __('appointments.upcoming') }}
                    </h3>
                    @if($upcomingAppointments->count() > 0)
                    <div class="space-y-3">
                        @foreach($upcomingAppointments as $appointment)
                        <a href="{{ route('app.appointments.show', ['clinic' => $currentClinic->slug, 'appointment' => $appointment->id]) }}"
                           wire:navigate
                           class="flex items-center space-x-3 p-2 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900 flex flex-col items-center justify-center">
                                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $appointment->appointment_date->format('d') }}</span>
                                    <span class="text-xs text-indigo-600 dark:text-indigo-400">{{ $appointment->appointment_date->format('M') }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $appointment->start_time ? \Carbon\Carbon::parse($appointment->start_time)->format('H:i') : 'TBD' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $appointment->doctor->name ?? 'N/A' }}
                                </p>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                        {{ __('appointments.no_upcoming') }}
                    </p>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">
                        {{ __('general.actions') }}
                    </h3>
                    <div class="space-y-2">
                        @can('patients.edit')
                        <button wire:click="toggleStatus"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-medium text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600">
                            @if($patient->is_active)
                            <svg class="w-4 h-4 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                            {{ __('patients.deactivate') }}
                            @else
                            <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('patients.activate') }}
                            @endif
                        </button>
                        @endcan

                        @can('patients.delete')
                        <button wire:click="confirmDelete"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-700 rounded-lg font-medium text-sm text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            {{ __('patients.delete_patient') }}
                        </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity" wire:click="cancelDelete"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                            {{ __('patients.delete_patient') }}
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('patients.confirm_delete_message', ['name' => $patient->full_name]) }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button wire:click="deletePatient" type="button"
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ __('general.delete') }}
                    </button>
                    <button wire:click="cancelDelete" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        {{ __('general.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
