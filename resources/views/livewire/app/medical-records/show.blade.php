<div>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <a href="{{ route('app.records.index', ['clinic' => $clinic->slug, 'patient' => $patient->id]) }}"
                   wire:navigate
                   class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                    {{ __('records.back_to_records') }}
                </a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mt-1">
                    {{ $record->title ?: __('records.type_' . $record->record_type) }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $patient->full_name }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if($record->status === \App\Models\MedicalRecord::STATUS_DRAFT)
                    @can('records.edit')
                        @if($clinic->canWrite())
                            <a href="{{ route('app.records.edit', ['clinic' => $clinic->slug, 'patient' => $patient->id, 'record' => $record->id]) }}"
                               wire:navigate
                               class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-md hover:bg-indigo-700">
                                {{ __('records.edit_record') }}
                            </a>
                        @endif
                    @endcan
                @endif
                @can('records.delete')
                    <button type="button"
                            wire:click="delete"
                            wire:confirm="{{ __('records.confirm_delete') }}"
                            class="inline-flex items-center px-3 py-1.5 bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 text-xs font-medium rounded-md hover:bg-rose-100 dark:hover:bg-rose-900/50">
                        {{ __('general.delete') }}
                    </button>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if($record->status === \App\Models\MedicalRecord::STATUS_FINAL)
                <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200 text-sm rounded-lg p-3 flex items-start gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ __('records.finalized_notice') }}</span>
                </div>
            @endif

            {{-- Metadata --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center gap-2 flex-wrap mb-4">
                    <span class="text-xs px-2 py-0.5 rounded bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300">
                        {{ __('records.type_' . $record->record_type) }}
                    </span>
                    <span class="text-xs px-2 py-0.5 rounded
                        @if($record->status === \App\Models\MedicalRecord::STATUS_FINAL) bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300
                        @elseif($record->status === \App\Models\MedicalRecord::STATUS_DRAFT) bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300
                        @else bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                        @endif">
                        {{ __('records.status_' . $record->status) }}
                    </span>
                    @if($record->is_confidential)
                        <span class="text-xs px-2 py-0.5 rounded bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                            🔒 {{ __('records.field_is_confidential') }}
                        </span>
                    @endif
                </div>
                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('records.created_by') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $record->doctor?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('records.created_at') }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $record->created_at->isoFormat('LLL') }}</dd>
                    </div>
                    @if($record->finalized_at)
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('records.finalized_at') }}</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ $record->finalized_at->isoFormat('LLL') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Clinical (SOAP) --}}
            @if($record->chief_complaint || $record->present_illness || $record->physical_examination || $record->assessment || $record->plan)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('records.form_section_clinical') }}</h3>
                    <dl class="space-y-4 text-sm">
                        @foreach([
                            'chief_complaint' => 'field_chief_complaint',
                            'present_illness' => 'field_present_illness',
                            'physical_examination' => 'field_physical_examination',
                            'assessment' => 'field_assessment',
                            'plan' => 'field_plan',
                        ] as $field => $label)
                            @if($record->$field)
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('records.' . $label) }}</dt>
                                    <dd class="text-gray-900 dark:text-gray-200 whitespace-pre-line">{{ $record->$field }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                </div>
            @endif

            {{-- Vital signs --}}
            @if(! empty($record->vital_signs))
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('records.form_section_vital_signs') }}</h3>
                    <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                        @foreach($record->vital_signs as $key => $value)
                            @if($value !== null && $value !== '')
                                <div>
                                    <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('records.field_' . $key) }}</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white">{{ $value }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                </div>
            @endif

            {{-- Diagnoses --}}
            @if(! empty($record->diagnoses))
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('records.form_section_diagnoses') }}</h3>
                    <ul class="space-y-2 text-sm">
                        @foreach($record->diagnoses as $dx)
                            <li class="flex gap-3 items-start">
                                @if(! empty($dx['code']))
                                    <span class="font-mono text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-700">{{ $dx['code'] }}</span>
                                @endif
                                <span class="text-gray-900 dark:text-gray-200">{{ $dx['description'] ?? '' }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Prescriptions --}}
            @if(! empty($record->prescriptions))
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('records.form_section_prescriptions') }}</h3>
                    <ul class="space-y-3 text-sm">
                        @foreach($record->prescriptions as $rx)
                            <li class="border-l-4 border-indigo-300 dark:border-indigo-700 pl-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $rx['drug'] ?? '—' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 space-x-2">
                                    @if(! empty($rx['dosage'])) <span>{{ __('records.field_prescription_dosage') }}: {{ $rx['dosage'] }}</span> @endif
                                    @if(! empty($rx['duration'])) <span>· {{ __('records.field_prescription_duration') }}: {{ $rx['duration'] }}</span> @endif
                                </div>
                                @if(! empty($rx['notes']))
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $rx['notes'] }}</div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    </div>
</div>
