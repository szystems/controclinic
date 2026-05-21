<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumb --}}
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <li><a href="{{ route('app.prescriptions.index', ['clinic' => $currentClinic->slug]) }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('prescriptions.prescriptions') }}</a></li>
                <li><span class="mx-1">/</span></li>
                <li class="text-gray-900 dark:text-white font-medium">{{ $prescription->folio ?? __('prescriptions.edit_prescription') }}</li>
            </ol>
        </nav>

        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ __('prescriptions.edit_prescription') }}</h1>

        <div class="space-y-6">
            {{-- Datos generales --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('prescriptions.general_data') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Paciente (solo lectura en edit) --}}
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('prescriptions.patient') }}</label>
                        <p class="text-sm text-gray-900 dark:text-white font-medium py-2">{{ $prescription->patient->full_name }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('prescriptions.diagnosis') }}</label>
                        <textarea wire:model.lazy="diagnosis" rows="2"
                                  placeholder="{{ __('prescriptions.diagnosis_placeholder') }}"
                                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('prescriptions.issued_at') }}</label>
                        <input type="date" wire:model.lazy="issuedAt"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('prescriptions.valid_until') }}</label>
                        <input type="date" wire:model.lazy="validUntil"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('prescriptions.notes') }}</label>
                        <textarea wire:model.lazy="notes" rows="2"
                                  placeholder="{{ __('prescriptions.notes_placeholder') }}"
                                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

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
                <a href="{{ route('app.prescriptions.show', ['clinic' => $currentClinic->slug, 'prescription' => $prescription->id]) }}"
                   class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    {{ __('general.cancel') }}
                </a>
                <button type="button" wire:click="save"
                        class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg text-sm font-semibold text-white hover:bg-indigo-700 transition">
                    {{ __('general.save') }}
                </button>
            </div>
        </div>
    </div>
</div>
