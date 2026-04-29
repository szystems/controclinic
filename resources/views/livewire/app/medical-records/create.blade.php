<div>
    <x-slot name="header">
        <div>
            <a href="{{ route('app.records.index', ['clinic' => $clinic->slug, 'patient' => $patient->id]) }}"
               wire:navigate
               class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                {{ __('records.back_to_records') }}
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mt-1">
                {{ __('records.new_record') }}
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $patient->full_name }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form wire:submit.prevent="saveFinal" class="space-y-6">

                {{-- General --}}
                <section class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('records.form_section_general') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('records.field_record_type') }} *</label>
                            <select wire:model="recordType" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                                @foreach($this->recordTypes as $type)
                                    <option value="{{ $type }}">{{ __('records.type_' . $type) }}</option>
                                @endforeach
                            </select>
                            @error('recordType') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('records.field_title') }}</label>
                            <input type="text" wire:model="title" placeholder="{{ __('records.field_title_placeholder') }}"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                        </div>
                    </div>
                </section>

                {{-- SOAP --}}
                <section class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('records.form_section_clinical') }}</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('records.field_chief_complaint') }}</label>
                            <textarea wire:model="chiefComplaint" rows="2"
                                      class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('records.field_present_illness') }}</label>
                            <textarea wire:model="presentIllness" rows="3"
                                      class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('records.field_physical_examination') }}</label>
                            <textarea wire:model="physicalExamination" rows="3"
                                      class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('records.field_assessment') }}</label>
                            <textarea wire:model="assessment" rows="3"
                                      class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('records.field_plan') }}</label>
                            <textarea wire:model="plan" rows="3"
                                      class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"></textarea>
                        </div>
                    </div>
                </section>

                {{-- Vital signs --}}
                <section class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('records.form_section_vital_signs') }}</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach([
                            'temperature', 'heart_rate', 'blood_pressure', 'respiratory_rate',
                            'oxygen_saturation', 'weight', 'height',
                        ] as $key)
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('records.field_' . $key) }}</label>
                                <input type="text" wire:model="vitalSigns.{{ $key }}"
                                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- Diagnoses --}}
                <section class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('records.form_section_diagnoses') }}</h3>
                        <button type="button" wire:click="addDiagnosis" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                            {{ __('records.add_diagnosis') }}
                        </button>
                    </div>
                    @if(empty($diagnoses))
                        <p class="text-xs text-gray-500 dark:text-gray-400">—</p>
                    @else
                        <div class="space-y-2">
                            @foreach($diagnoses as $i => $dx)
                                <div class="flex gap-2 items-start" wire:key="dx-{{ $i }}">
                                    <input type="text" wire:model="diagnoses.{{ $i }}.code"
                                           placeholder="{{ __('records.field_diagnosis_code') }}"
                                           class="w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                                    <input type="text" wire:model="diagnoses.{{ $i }}.description"
                                           placeholder="{{ __('records.field_diagnosis_description') }}"
                                           class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                                    <button type="button" wire:click="removeDiagnosis({{ $i }})"
                                            class="px-2 text-rose-500 hover:text-rose-700 text-sm">✕</button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                {{-- Prescriptions --}}
                <section class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('records.form_section_prescriptions') }}</h3>
                        <button type="button" wire:click="addPrescription" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                            {{ __('records.add_prescription') }}
                        </button>
                    </div>
                    @if(empty($prescriptions))
                        <p class="text-xs text-gray-500 dark:text-gray-400">—</p>
                    @else
                        <div class="space-y-3">
                            @foreach($prescriptions as $i => $rx)
                                <div class="border-l-4 border-indigo-300 dark:border-indigo-700 pl-3 space-y-2" wire:key="rx-{{ $i }}">
                                    <div class="flex items-start gap-2">
                                        <input type="text" wire:model="prescriptions.{{ $i }}.drug"
                                               placeholder="{{ __('records.field_prescription_drug') }}"
                                               class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                                        <button type="button" wire:click="removePrescription({{ $i }})"
                                                class="px-2 text-rose-500 hover:text-rose-700 text-sm">✕</button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" wire:model="prescriptions.{{ $i }}.dosage"
                                               placeholder="{{ __('records.field_prescription_dosage') }}"
                                               class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                                        <input type="text" wire:model="prescriptions.{{ $i }}.duration"
                                               placeholder="{{ __('records.field_prescription_duration') }}"
                                               class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                                    </div>
                                    <input type="text" wire:model="prescriptions.{{ $i }}.notes"
                                           placeholder="{{ __('records.field_prescription_notes') }}"
                                           class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                {{-- Confidentiality --}}
                <section class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <label class="inline-flex items-start gap-2">
                        <input type="checkbox" wire:model="isConfidential"
                               class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 mt-0.5">
                        <span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('records.field_is_confidential') }}</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">{{ __('records.field_is_confidential_help') }}</span>
                        </span>
                    </label>
                </section>

                {{-- Buttons --}}
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('app.records.index', ['clinic' => $clinic->slug, 'patient' => $patient->id]) }}"
                       wire:navigate
                       class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:underline">
                        {{ __('records.cancel') }}
                    </a>
                    <button type="button" wire:click="saveDraft"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm rounded-md hover:bg-gray-300 dark:hover:bg-gray-600">
                        {{ __('records.save_draft') }}
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                        {{ __('records.save_final') }}
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
