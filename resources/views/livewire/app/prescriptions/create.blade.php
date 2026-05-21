<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumb --}}
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <li><a href="{{ route('app.prescriptions.index', ['clinic' => $currentClinic->slug]) }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('prescriptions.prescriptions') }}</a></li>
                <li><span class="mx-1">/</span></li>
                <li class="text-gray-900 dark:text-white font-medium">{{ __('prescriptions.new_prescription') }}</li>
            </ol>
        </nav>

        {{-- Botón volver al paciente (solo cuando viene pre-cargado desde Patient Show) --}}
        @if($selectedPatientName && $patientId)
        <div class="mb-4">
            <a href="{{ route('app.patients.show', ['clinic' => $currentClinic->slug, 'patient' => $patientId]) }}?tab=recetas"
               class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ $selectedPatientName }}
            </a>
        </div>
        @endif

        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ __('prescriptions.new_prescription') }}</h1>

        <div class="space-y-6">
            {{-- Datos generales --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('prescriptions.general_data') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Paciente (búsqueda en vivo) --}}
                    <div class="sm:col-span-2"
                         x-data="{ open: false }"
                         x-on:click.outside="open = false">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('prescriptions.patient') }} <span class="text-red-500">*</span>
                        </label>

                        {{-- Paciente ya seleccionado --}}
                        @if($selectedPatientName)
                        <div class="flex items-center justify-between rounded-lg border border-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 dark:border-indigo-600 px-3 py-2 text-sm">
                            <span class="font-medium text-gray-900 dark:text-white">{{ $selectedPatientName }}</span>
                            <button type="button" wire:click="clearPatient"
                                    class="ml-2 text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        @else
                        {{-- Input de búsqueda --}}
                        <div class="relative">
                            <input type="text"
                                   wire:model.live.debounce.300ms="patientSearch"
                                   x-on:focus="open = true"
                                   x-on:input="open = true"
                                   placeholder="{{ __('prescriptions.search_patient_placeholder') }}…"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   autocomplete="off"/>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>

                            {{-- Resultados --}}
                            @if($searchResults->isNotEmpty())
                            <div x-show="open"
                                 class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                @foreach($searchResults as $p)
                                <button type="button"
                                        wire:click="selectPatient('{{ $p->id }}', '{{ addslashes($p->full_name) }}')"
                                        x-on:click="open = false"
                                        class="w-full text-left px-4 py-2 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $p->full_name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 space-x-2">
                                        @if($p->phone)<span>{{ $p->phone }}</span>@endif
                                        @if($p->email)<span>{{ $p->email }}</span>@endif
                                    </div>
                                </button>
                                @endforeach
                            </div>
                            @elseif(strlen(trim($patientSearch)) >= 2)
                            <div x-show="open"
                                 class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('prescriptions.no_patient_found') }}
                            </div>
                            @endif
                        </div>
                        @endif

                        @error('patientId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Diagnóstico --}}
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('prescriptions.diagnosis') }}</label>
                        <textarea wire:model.lazy="diagnosis" rows="2"
                                  placeholder="{{ __('prescriptions.diagnosis_placeholder') }}"
                                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    {{-- Fecha emisión --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('prescriptions.issued_at') }}</label>
                        <input type="date" wire:model.lazy="issuedAt"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                    </div>

                    {{-- Válida hasta --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('prescriptions.valid_until') }}</label>
                        <input type="date" wire:model.lazy="validUntil"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                    </div>

                    {{-- Instrucciones generales --}}
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('prescriptions.notes') }}</label>
                        <textarea wire:model.lazy="notes" rows="2"
                                  placeholder="{{ __('prescriptions.notes_placeholder') }}"
                                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    {{-- Notas internas --}}
                    @can('patients.edit')
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('prescriptions.internal_notes') }}</label>
                        <textarea wire:model.lazy="internalNotes" rows="2"
                                  placeholder="{{ __('prescriptions.internal_notes_hint') }}"
                                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    @endcan
                </div>
            </div>

            {{-- Medicamentos --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('prescriptions.medications') }}</h2>
                @error('items') <p class="mb-3 text-sm text-red-500">{{ $message }}</p> @enderror
                @include('livewire.app.prescriptions._item_repeater')
            </div>

            {{-- Acciones --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <a href="{{ route('app.prescriptions.index', ['clinic' => $currentClinic->slug]) }}"
                   class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    {{ __('general.cancel') }}
                </a>
                <div class="flex gap-3 w-full sm:w-auto">
                    <button type="button" wire:click="save(false)"
                            class="flex-1 sm:flex-none inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                        {{ __('prescriptions.save_draft') }}
                    </button>
                    <button type="button" wire:click="save(true)"
                            class="flex-1 sm:flex-none inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg text-sm font-semibold text-white hover:bg-indigo-700 transition">
                        {{ __('prescriptions.issue_prescription') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
