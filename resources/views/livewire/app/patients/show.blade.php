<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumb --}}
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="{{ route('app.patients.index', ['clinic' => $currentClinic->slug]) }}"
                       class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-sm">
                        {{ __('patients.title') }}
                    </a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $patient->full_name }}</span>
                </li>
            </ol>
        </nav>

        {{-- Flash Messages --}}
        @if (session()->has('success'))
        <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/50 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
        </div>
        @endif
        @if (session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/50 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9v4h2V9H9zm0 6v2h2v-2H9z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        {{-- ====== STICKY PATIENT HEADER ====== --}}
        <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6 overflow-hidden">
            <div class="px-6 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    {{-- Avatar + Info --}}
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 h-14 w-14 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                            <span class="text-indigo-600 dark:text-indigo-400 font-bold text-xl">{{ $patient->initials }}</span>
                        </div>
                        <div>
                            <div class="flex items-center flex-wrap gap-2">
                                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $patient->full_name }}</h1>
                                @if(!$patient->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    {{ __('patients.inactive') }}
                                </span>
                                @endif
                                @foreach($patient->tags->take(4) as $tag)
                                <span class="{{ $tag->badge_classes }} inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium">{{ $tag->name }}</span>
                                @endforeach
                                @if($patient->tags->count() > 4)
                                <span class="text-xs text-gray-400">+{{ $patient->tags->count() - 4 }}</span>
                                @endif
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
                                <span class="font-mono text-xs">{{ $patient->medical_record_number ?? 'N/A' }}</span>
                                @if($patient->birth_date)
                                <span>{{ $patient->age }} {{ __('patients.years_old') }}</span>
                                @endif
                                @if($patient->blood_type)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300">{{ $patient->blood_type }}</span>
                                @endif
                                @if($patient->allergies)
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-red-600 dark:text-red-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                    </svg>
                                    {{ __('patients.allergies') }}: {{ Str::limit($patient->allergies, 40) }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    {{-- Action buttons --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @can('patients.print')
                        <button type="button" wire:click="exportPdf"
                                class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600">
                            <svg class="w-4 h-4 mr-1.5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                            </svg>
                            PDF
                        </button>
                        @endcan
                        @can('patients.edit')
                        <a href="{{ route('app.patients.edit', ['clinic' => $currentClinic->slug, 'patient' => $patient->id]) }}"
                           class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            {{ __('general.edit') }}
                        </a>
                        @endcan
                        @can('appointments.create')
                        @if($currentClinic->canWrite())
                        <a href="{{ route('app.appointments.create', ['clinic' => $currentClinic->slug, 'patient' => $patient->id]) }}"
                           class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-lg text-xs font-semibold text-white hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ __('appointments.new_appointment') }}
                        </a>
                        @endif
                        @endcan
                    </div>
                </div>
            </div>

            {{-- ====== TAB NAV ====== --}}
            <div class="border-t border-gray-200 dark:border-gray-700 overflow-x-auto">
                <nav class="flex -mb-px" aria-label="Tabs">
                    @php
                        $tabs = [
                            'datos'       => ['label' => __('patients.tab_datos'),       'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                            'citas'       => ['label' => __('patients.tab_citas'),       'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'perm' => 'appointments.view'],
                            'historial'   => ['label' => __('patients.tab_historial'),   'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'perm' => 'records.view'],
                            'recetas'     => ['label' => __('patients.tab_recetas'),     'icon' => 'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'perm' => 'prescriptions.view'],
                            'archivos'    => ['label' => __('patients.tab_archivos'),    'icon' => 'M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13'],
                            'facturacion' => ['label' => __('patients.tab_facturacion'), 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'perm' => 'invoices.view'],
                            'notas'       => ['label' => __('patients.tab_notas'),       'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'perm' => 'patients.edit'],
                            'actividad'   => ['label' => __('patients.tab_actividad'),   'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'perm' => 'patients.view'],
                        ];
                    @endphp
                    @foreach($tabs as $key => $cfg)
                        @if(!isset($cfg['perm']) || auth()->user()->can($cfg['perm']))
                        <button wire:click="setTab('{{ $key }}')" type="button"
                                class="group inline-flex items-center gap-1.5 px-4 py-3 border-b-2 text-sm font-medium whitespace-nowrap transition-colors
                                    {{ $tab === $key
                                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600' }}">
                            <svg class="w-4 h-4 {{ $tab === $key ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $cfg['icon'] }}"/>
                            </svg>
                            {{ $cfg['label'] }}
                        </button>
                        @endif
                    @endforeach
                </nav>
            </div>
        </div>

        {{-- ====== TAB CONTENT ====== --}}

        {{-- ===== TAB: DATOS ===== --}}
        @if($tab === 'datos')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Contact Information --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('patients.contact_info') }}</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('patients.phone') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $patient->phone ?? '-' }}</dd>
                        </div>
                        @if($patient->phone_secondary)
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('patients.phone_secondary') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $patient->phone_secondary }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('patients.email') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $patient->email ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('patients.address') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                @if($patient->address)
                                    {{ $patient->address }}<br>
                                    {{ $patient->city }}{{ $patient->state ? ', '.$patient->state : '' }} {{ $patient->postal_code }}
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Medical Information --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('patients.medical_info') }}</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('patients.blood_type') }}</dt>
                            <dd class="mt-1">
                                @if($patient->blood_type)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">{{ $patient->blood_type }}</span>
                                @else
                                <span class="text-sm text-gray-900 dark:text-white">-</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('patients.gender') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $patient->gender ? __('patients.'.$patient->gender) : '-' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('patients.allergies') }}</dt>
                            <dd class="mt-1 text-sm">
                                @if($patient->allergies)
                                <span class="text-red-600 dark:text-red-400 font-medium">&#9888;&#65039; {{ $patient->allergies }}</span>
                                @else
                                <span class="text-gray-900 dark:text-white">{{ __('patients.no_known_allergies') }}</span>
                                @endif
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('patients.chronic_conditions') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $patient->chronic_conditions ?? '-' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('patients.current_medications') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $patient->current_medications ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Emergency Contact --}}
                @if($patient->emergency_contacts)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('patients.emergency_contact') }}</h2>
                    @foreach($patient->emergency_contacts as $contact)
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('patients.contact_name') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $contact['name'] ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('patients.contact_phone') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $contact['phone'] ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('patients.relationship') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $contact['relationship'] ?? '-' }}</dd>
                        </div>
                    </dl>
                    @endforeach
                </div>
                @endif

                {{-- Insurance --}}
                @if($patient->insurance_info)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('patients.insurance') }}</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('patients.insurance_provider') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $patient->insurance_info['provider'] ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('patients.policy_number') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">{{ $patient->insurance_info['policy_number'] ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Quick Info --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('patients.quick_info') }}</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('patients.birth_date') }}</dt>
                            <dd class="mt-0.5 text-sm text-gray-900 dark:text-white">{{ $currentClinic->formatDate($patient->birth_date) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('patients.id_number') }}</dt>
                            <dd class="mt-0.5 text-sm text-gray-900 dark:text-white">{{ $patient->id_type }}: {{ $patient->id_number ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('patients.primary_doctor') }}</dt>
                            <dd class="mt-0.5 text-sm text-gray-900 dark:text-white">{{ $patient->primaryDoctor->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('patients.last_visit') }}</dt>
                            <dd class="mt-0.5 text-sm text-gray-900 dark:text-white">
                                {{ $patient->last_visit_at ? $patient->last_visit_at->diffForHumans() : __('patients.never') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('patients.registered') }}</dt>
                            <dd class="mt-0.5 text-sm text-gray-900 dark:text-white">{{ $currentClinic->formatDate($patient->created_at) }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Tags --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
                     x-data="{ showTagPanel: @entangle('showTagPanel') }">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('patients.tags_section') }}</h3>
                        @can('tags.manage')
                        <button @click="showTagPanel = !showTagPanel" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                            {{ __('patients.manage_tags') }}
                        </button>
                        @endcan
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @forelse($patient->tags as $tag)
                        <span class="{{ $tag->badge_classes }} inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium">
                            {{ $tag->name }}
                            @can('tags.manage')
                            <button wire:click="toggleTag({{ $tag->id }})" class="ml-0.5 hover:opacity-75" title="{{ __('patients.remove_tag') }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                            @endcan
                        </span>
                        @empty
                        <p class="text-sm text-gray-400 dark:text-gray-500 italic">{{ __('patients.no_tags') }}</p>
                        @endforelse
                    </div>
                    @can('tags.manage')
                    <div x-show="showTagPanel" x-transition class="mt-4 space-y-3">
                        <hr class="border-gray-200 dark:border-gray-700">
                        @if($clinicTags->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($clinicTags as $tag)
                            <button wire:click="toggleTag({{ $tag->id }})"
                                    class="{{ in_array($tag->id, $assignedTagIds) ? $tag->badge_classes.' ring-2 ring-offset-1 ring-current' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }} inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium transition">
                                @if(in_array($tag->id, $assignedTagIds))
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                @endif
                                {{ $tag->name }}
                            </button>
                            @endforeach
                        </div>
                        @endif
                        <div class="space-y-2">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('patients.create_tag') }}</p>
                            <div class="flex gap-2">
                                <input wire:model="newTagName" type="text" placeholder="{{ __('patients.tag_name') }}"
                                       class="flex-1 min-w-0 text-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <select wire:model="newTagColor" class="text-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($tagColors as $color)
                                    <option value="{{ $color }}">{{ ucfirst($color) }}</option>
                                    @endforeach
                                </select>
                                <button wire:click="createAndAssignTag" class="px-2 py-1 bg-indigo-600 text-white text-xs rounded-md hover:bg-indigo-700 transition">+</button>
                            </div>
                            @error('newTagName') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    @endcan
                </div>

                {{-- Internal Notes (preview) --}}
                @can('patients.edit')
                @if($patient->internal_notes)
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl shadow-sm border border-amber-200 dark:border-amber-700 p-6">
                    <h3 class="text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        {{ __('patients.internal_notes') }}
                    </h3>
                    <p class="text-sm text-amber-800 dark:text-amber-300 whitespace-pre-wrap">{{ $patient->internal_notes }}</p>
                </div>
                @endif
                @endcan

                {{-- Actions --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('general.actions') }}</h3>
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
        @endif

        {{-- ===== TAB: CITAS ===== --}}
        @can('appointments.view')
        @if($tab === 'citas')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('patients.tab_citas') }}</h2>
                @can('appointments.create')
                @if($currentClinic->canWrite())
                <a href="{{ route('app.appointments.create', ['clinic' => $currentClinic->slug, 'patient' => $patient->id]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('appointments.new_appointment') }}
                </a>
                @endif
                @endcan
            </div>
            <div wire:loading.class="opacity-50" wire:target="setTab">
                @if($allAppointments && $allAppointments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('appointments.date') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('appointments.time') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('appointments.doctor') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('appointments.status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('appointments.invoiced') }}</th>
                                <th class="relative px-6 py-3"><span class="sr-only">{{ __('general.actions') }}</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($allAppointments as $appointment)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $appointment->appointment_date->isoFormat('ddd D MMM YYYY') }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $appointment->start_time ? \Carbon\Carbon::parse($appointment->start_time)->format($currentClinic->timeFormat()) : '--:--' }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $appointment->doctor->name ?? '-' }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    @php $colors = ['blue'=>'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200','indigo'=>'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200','yellow'=>'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200','green'=>'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200','gray'=>'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200','red'=>'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200','orange'=>'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200']; @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $colors[$appointment->status_color] ?? $colors['gray'] }}">
                                        {{ $appointment->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm">
                                    @if($appointment->invoice)
                                    <span class="text-green-600 dark:text-green-400">&#10003;</span>
                                    @else
                                    <span class="text-gray-300 dark:text-gray-600">&mdash;</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-right">
                                    <a href="{{ route('app.appointments.show', ['clinic' => $currentClinic->slug, 'appointment' => $appointment->id]) }}"
                                       class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">{{ __('general.view') }}</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $allAppointments->links() }}
                </div>
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('appointments.no_appointments') }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
        @endcan

        {{-- ===== TAB: HISTORIAL ===== --}}
        @can('records.view')
        @if($tab === 'historial')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('patients.tab_historial') }}</h2>
                @can('records.create')
                @if($currentClinic->canWrite())
                <a href="{{ route('app.records.create', ['clinic' => $currentClinic->slug, 'patient' => $patient->id]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-semibold rounded-lg hover:bg-emerald-700">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('records.new_record') }}
                </a>
                @endif
                @endcan
            </div>
            <div wire:loading.class="opacity-50" wire:target="setTab">
                @if($allRecords && $allRecords->count() > 0)
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($allRecords as $record)
                    <a href="{{ route('app.records.show', ['clinic' => $currentClinic->slug, 'patient' => $patient->id, 'record' => $record->id]) }}"
                       class="flex items-start gap-4 px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $record->title ?? __('records.'.$record->record_type) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $record->doctor->name ?? 'N/A' }} &middot; {{ $currentClinic->formatDate($record->created_at) }}</p>
                            @if($record->chief_complaint)
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 truncate">{{ $record->chief_complaint }}</p>
                            @endif
                        </div>
                        <div class="flex-shrink-0 flex items-center gap-2">
                            @if(is_array($record->attachments) && count($record->attachments) > 0)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                &#128206; {{ count($record->attachments) }}
                            </span>
                            @endif
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                    @endforeach
                </div>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $allRecords->links() }}
                </div>
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('patients.no_records') }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
        @endcan

        {{-- ===== TAB: ARCHIVOS ===== --}}
        @if($tab === 'archivos')
        @can('viewAny', \App\Models\PatientFile::class)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            @livewire('app.patients.files', ['clinic' => $currentClinic, 'patient' => $patient], key('patient-files-'.$patient->id))
        </div>
        @endcan
        @endif

        {{-- ===== TAB: FACTURACIÓN ===== --}}
        @can('invoices.view')
        @if($tab === 'facturacion')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('patients.tab_facturacion') }}</h2>
                @can('invoices.create')
                @if($currentClinic->canWrite())
                <a href="{{ route('app.invoices.create', ['clinic' => $currentClinic->slug, 'patient' => $patient->id]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('invoices.new_invoice') }}
                </a>
                @endif
                @endcan
            </div>
            <div wire:loading.class="opacity-50" wire:target="setTab">
                @if($invoices && $invoices->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('invoices.number') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('invoices.date') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('invoices.total') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('invoices.status') }}</th>
                                <th class="relative px-6 py-3"><span class="sr-only">{{ __('general.actions') }}</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($invoices as $invoice)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-3 whitespace-nowrap text-sm font-mono text-gray-900 dark:text-white">{{ $invoice->invoice_number ?? '-' }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $currentClinic->formatDate($invoice->issued_at ?? $invoice->created_at) }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ number_format($invoice->total, 2) }}</td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    @php
                                        $sc = match($invoice->status) { 'paid'=>'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200','pending'=>'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200','partial'=>'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200','overdue'=>'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',default=>'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sc }}">
                                        {{ __('invoices.status_'.$invoice->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-right">
                                    <a href="{{ route('app.invoices.show', ['clinic' => $currentClinic->slug, 'invoice' => $invoice->id]) }}"
                                       class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">{{ __('general.view') }}</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $invoices->links() }}
                </div>
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('invoices.no_invoices') }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
        @endcan

        {{-- ===== TAB: RECETAS ===== --}}
        @can('prescriptions.view')
        @if($tab === 'recetas')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('patients.tab_recetas') }}</h2>
                @can('prescriptions.create')
                @if($currentClinic->canWrite())
                <a href="{{ route('app.prescriptions.create', ['clinic' => $currentClinic->slug, 'patientId' => $patient->id]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('prescriptions.new_prescription') }}
                </a>
                @endif
                @endcan
            </div>
            <div wire:loading.class="opacity-50" wire:target="setTab">
                @if($prescriptions && $prescriptions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('prescriptions.folio') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('prescriptions.issued_at') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('prescriptions.doctor') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('prescriptions.status') }}</th>
                                <th class="relative px-6 py-3"><span class="sr-only">{{ __('general.actions') }}</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($prescriptions as $rx)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-3 whitespace-nowrap text-sm font-mono text-gray-900 dark:text-white">{{ $rx->folio ?? __('prescriptions.draft') }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $rx->issued_at?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $rx->doctor?->name ?? '—' }}</td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    @php $sc = ['draft'=>'bg-gray-100 text-gray-800','issued'=>'bg-blue-100 text-blue-800','dispensed'=>'bg-green-100 text-green-800','cancelled'=>'bg-red-100 text-red-800'][$rx->status] ?? 'bg-gray-100 text-gray-800'; @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sc }}">
                                        {{ $rx->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-right">
                                    <a href="{{ route('app.prescriptions.show', ['clinic' => $currentClinic->slug, 'prescription' => $rx->id]) }}"
                                       class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">{{ __('general.view') }}</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $prescriptions->links() }}
                </div>
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('prescriptions.no_prescriptions') }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
        @endcan

        {{-- ===== TAB: NOTAS INTERNAS ===== --}}
        @can('patients.edit')
        @if($tab === 'notas')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('patients.tab_notas') }}</h2>
            @if($patient->internal_notes)
            <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-700 p-4">
                <p class="text-sm text-amber-800 dark:text-amber-300 whitespace-pre-wrap">{{ $patient->internal_notes }}</p>
            </div>
            @else
            <div class="text-center py-10">
                <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('patients.no_internal_notes') }}</p>
                @if($currentClinic->canWrite())
                <a href="{{ route('app.patients.edit', ['clinic' => $currentClinic->slug, 'patient' => $patient->id]) }}"
                   class="mt-3 inline-flex items-center gap-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                    {{ __('patients.add_internal_notes') }}
                </a>
                @endif
            </div>
            @endif
        </div>
        @endif
        @endcan

        {{-- ===== TAB: ACTIVIDAD ===== --}}
        @can('patients.view')
        @if($tab === 'actividad')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('patients.tab_actividad') }}</h2>
            </div>
            <div wire:loading.class="opacity-50" wire:target="setTab">
                @if($activityLogs && $activityLogs->count() > 0)
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($activityLogs as $log)
                    <div class="flex items-start gap-4 px-6 py-3">
                        <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 dark:text-white">
                                <span class="font-medium">{{ $log->causer?->name ?? __('general.system') }}</span>
                                <span class="text-gray-500 dark:text-gray-400"> &mdash; {{ $log->description }}</span>
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-12">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('patients.no_activity') }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
        @endcan

    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity" wire:click="cancelDelete"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">{{ __('patients.delete_patient') }}</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('patients.confirm_delete_message', ['name' => $patient->full_name]) }}
                        </p>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button wire:click="deletePatient" type="button"
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ __('general.delete') }}
                    </button>
                    <button wire:click="cancelDelete" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 sm:mt-0 sm:w-auto sm:text-sm">
                        {{ __('general.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
