<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Flash --}}
        @if(session()->has('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-200">
            {{ session('success') }}
        </div>
        @endif

        {{-- Breadcrumb --}}
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <li>
                    <a href="{{ route('app.prescriptions.index', ['clinic' => $currentClinic->slug]) }}"
                       class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('prescriptions.prescriptions') }}</a>
                </li>
                <li><span class="mx-1">/</span></li>
                <li class="text-gray-900 dark:text-white font-medium">{{ $prescription->folio ?? $prescription->id }}</li>
            </ol>
        </nav>

        {{-- Botón volver al paciente --}}
        <div class="mb-4">
            <a href="{{ route('app.patients.show', ['clinic' => $currentClinic->slug, 'patient' => $prescription->patient_id]) }}?tab=recetas"
               class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ $prescription->patient->full_name }}
            </a>
        </div>

        {{-- Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        @php $colors = ['draft'=>'gray','issued'=>'blue','dispensed'=>'green','cancelled'=>'red']; $c = $colors[$prescription->status] ?? 'gray'; @endphp
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $prescription->folio ? __('prescriptions.folio').' '.$prescription->folio : __('prescriptions.draft') }}
                        </h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-100 text-{{ $c }}-800 dark:bg-{{ $c }}-900/30 dark:text-{{ $c }}-300">
                            {{ $prescription->status_label }}
                        </span>
                        @if($prescription->is_expired)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300">
                            {{ __('prescriptions.expired') }}
                        </span>
                        @endif
                    </div>
                    <dl class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-2 text-sm">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('prescriptions.patient') }}</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">
                                <a href="{{ route('app.patients.show', ['clinic' => $currentClinic->slug, 'patient' => $prescription->patient_id]) }}"
                                   class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $prescription->patient->full_name }}</a>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('prescriptions.doctor') }}</dt>
                            <dd class="text-gray-900 dark:text-white">{{ $prescription->doctor->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('prescriptions.issued_at') }}</dt>
                            <dd class="text-gray-900 dark:text-white">{{ $prescription->issued_at?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('prescriptions.valid_until') }}</dt>
                            <dd class="{{ $prescription->is_expired ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-900 dark:text-white' }}">
                                {{ $prescription->valid_until?->format('d/m/Y') ?? '—' }}
                            </dd>
                        </div>
                        @if($prescription->medicalRecord)
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('prescriptions.from_record') }}</dt>
                            <dd class="text-gray-900 dark:text-white">
                                <a href="{{ route('app.records.show', ['clinic' => $currentClinic->slug, 'patient' => $prescription->patient_id, 'record' => $prescription->medicalRecord->id]) }}"
                                   class="hover:text-indigo-600 dark:hover:text-indigo-400 text-xs">
                                    {{ __('prescriptions.view_record') }} →
                                </a>
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>

                {{-- Action buttons --}}
                <div class="flex flex-wrap gap-2 flex-shrink-0">
                    @can('print', $prescription)
                    <a href="{{ route('app.prescriptions.pdf', ['clinic' => $currentClinic->slug, 'prescription' => $prescription->id]) }}"
                       target="_blank"
                       class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                        <svg class="w-4 h-4 mr-1.5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                        </svg>
                        PDF
                    </a>
                    @endcan

                    @if($prescription->status === 'draft')
                    @can('issue', $prescription)
                    @if($currentClinic->canWrite())
                    <button wire:click="issue" type="button"
                            class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-lg text-xs font-semibold text-white hover:bg-blue-700 transition">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('prescriptions.issue_prescription') }}
                    </button>
                    @endif
                    @endcan

                    @can('update', $prescription)
                    @if($currentClinic->canWrite())
                    <a href="{{ route('app.prescriptions.edit', ['clinic' => $currentClinic->slug, 'prescription' => $prescription->id]) }}"
                       class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        {{ __('general.edit') }}
                    </a>
                    @endif
                    @endcan
                    @endif

                    @can('cancel', $prescription)
                    @if(in_array($prescription->status, ['draft', 'issued']))
                    <button wire:click="confirmCancel" type="button"
                            class="inline-flex items-center px-3 py-2 border border-red-300 dark:border-red-700 rounded-lg text-xs font-semibold text-red-700 dark:text-red-400 bg-white dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        {{ __('prescriptions.cancel_prescription') }}
                    </button>
                    @endif
                    @endcan
                </div>
            </div>
        </div>

        {{-- Diagnóstico / notas --}}
        @if($prescription->diagnosis || $prescription->notes)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            @if($prescription->diagnosis)
            <div class="mb-3">
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('prescriptions.diagnosis') }}</h3>
                <p class="text-sm text-gray-900 dark:text-white">{{ $prescription->diagnosis }}</p>
            </div>
            @endif
            @if($prescription->notes)
            <div>
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('prescriptions.notes') }}</h3>
                <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $prescription->notes }}</p>
            </div>
            @endif
        </div>
        @endif

        {{-- Medicamentos --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('prescriptions.medications') }}</h2>
            </div>
            @if($prescription->items->isEmpty())
            <p class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ __('prescriptions.no_medications') }}</p>
            @else
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($prescription->items as $item)
                <div class="px-6 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $item->order + 1 }}. {{ $item->medication_name }}
                                </p>
                                @if($item->presentation)
                                <span class="text-xs text-gray-500 dark:text-gray-400">({{ $item->presentation }})</span>
                                @endif
                                @if($item->is_controlled)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 font-medium">
                                    ⚠ {{ __('prescriptions.controlled') }}
                                </span>
                                @endif
                            </div>
                            <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                @if($item->dose) <span>{{ __('prescriptions.dose') }}: <strong class="text-gray-700 dark:text-gray-200">{{ $item->dose }}</strong></span> @endif
                                @if($item->frequency) <span>{{ __('prescriptions.frequency') }}: <strong class="text-gray-700 dark:text-gray-200">{{ $item->frequency }}</strong></span> @endif
                                @if($item->duration) <span>{{ __('prescriptions.duration') }}: <strong class="text-gray-700 dark:text-gray-200">{{ $item->duration }}</strong></span> @endif
                                @if($item->route) <span>{{ __('prescriptions.route') }}: <strong class="text-gray-700 dark:text-gray-200">{{ __('prescriptions.route_'.$item->route) }}</strong></span> @endif
                                @if($item->quantity) <span>{{ __('prescriptions.quantity') }}: <strong class="text-gray-700 dark:text-gray-200">{{ $item->quantity }}</strong></span> @endif
                            </div>
                            @if($item->instructions)
                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300 italic">{{ $item->instructions }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Notas internas --}}
        @can('patients.edit')
        @if($prescription->internal_notes)
        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-700 p-4 mb-6">
            <h3 class="text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wide mb-1 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                {{ __('prescriptions.internal_notes') }}
            </h3>
            <p class="text-sm text-amber-800 dark:text-amber-300">{{ $prescription->internal_notes }}</p>
        </div>
        @endif
        @endcan
    </div>

    {{-- Cancel Modal --}}
    @if($showCancelModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75" wire:click="$set('showCancelModal', false)"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl px-4 pt-5 pb-4 text-left shadow-xl sm:my-8 sm:align-middle sm:max-w-sm sm:w-full sm:p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">{{ __('prescriptions.cancel_prescription') }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('prescriptions.confirm_cancel_message') }}</p>
                <div class="flex gap-3 justify-end">
                    <button wire:click="$set('showCancelModal', false)" type="button"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        {{ __('general.cancel') }}
                    </button>
                    <button wire:click="cancel" type="button"
                            class="px-4 py-2 bg-red-600 border border-transparent rounded-lg text-sm font-semibold text-white hover:bg-red-700">
                        {{ __('prescriptions.yes_cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
