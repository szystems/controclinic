<div>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <a href="{{ route('app.patients.show', ['clinic' => $clinic->slug, 'patient' => $patient->id]) }}"
                   wire:navigate
                   class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                    {{ __('records.back_to_patient') }}
                </a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mt-1">
                    {{ __('records.medical_records') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('records.subtitle', ['patient' => $patient->full_name]) }}
                </p>
            </div>
            @can('records.create')
                @if($clinic->canWrite())
                    <a href="{{ route('app.records.create', ['clinic' => $clinic->slug, 'patient' => $patient->id]) }}"
                       wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('records.new_record') }}
                    </a>
                @else
                    <x-upgrade-nudge type="button" :clinic-slug="$clinic->slug" />
                @endif
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('records.filter_type') }}</label>
                        <select wire:model.live="typeFilter"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                            <option value="">{{ __('records.filter_all_types') }}</option>
                            @foreach($recordTypes as $type)
                                <option value="{{ $type }}">{{ __('records.type_' . $type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('records.filter_status') }}</label>
                        <select wire:model.live="statusFilter"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                            <option value="">{{ __('records.filter_all_statuses') }}</option>
                            @foreach($recordStatuses as $status)
                                <option value="{{ $status }}">{{ __('records.status_' . $status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        @if($typeFilter || $statusFilter)
                            <button type="button" wire:click="clearFilters"
                                    class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                ✕ {{ __('general.clear_filters') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Records List --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                @forelse($records as $record)
                    <a href="{{ route('app.records.show', ['clinic' => $clinic->slug, 'patient' => $patient->id, 'record' => $record->id]) }}"
                       wire:navigate
                       class="block p-4 border-b border-gray-100 dark:border-gray-700 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    <span class="text-xs px-2 py-0.5 rounded bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300">
                                        {{ __('records.type_' . $record->record_type) }}
                                    </span>
                                    @if($record->status !== \App\Models\MedicalRecord::STATUS_FINAL)
                                        <span class="text-xs px-2 py-0.5 rounded
                                            @if($record->status === \App\Models\MedicalRecord::STATUS_DRAFT) bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300
                                            @else bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                                            @endif">
                                            {{ __('records.status_' . $record->status) }}
                                        </span>
                                    @endif
                                    @if($record->is_confidential)
                                        <span class="text-xs px-2 py-0.5 rounded bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                            🔒
                                        </span>
                                    @endif
                                </div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $record->title ?: __('records.type_' . $record->record_type) }}
                                </h3>
                                @if($record->chief_complaint)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mt-1">
                                        {{ $record->chief_complaint }}
                                    </p>
                                @endif
                            </div>
                            <div class="text-right flex-shrink-0 text-xs text-gray-500 dark:text-gray-400">
                                <div>{{ $record->created_at->isoFormat('LL') }}</div>
                                <div class="mt-0.5">{{ $record->doctor?->name }}</div>
                                @if($record->status === \App\Models\MedicalRecord::STATUS_DRAFT)
                                    @can('records.edit')
                                        @if($clinic->canWrite())
                                            <a href="{{ route('app.records.edit', ['clinic' => $clinic->slug, 'patient' => $patient->id, 'record' => $record->id]) }}"
                                               wire:navigate
                                               onclick="event.stopPropagation()"
                                               class="inline-block mt-1 text-indigo-600 dark:text-indigo-400 hover:underline">
                                                {{ __('general.edit') }} →
                                            </a>
                                        @endif
                                    @endcan
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ __('records.no_records') }}</p>
                        @can('records.create')
                            @if($clinic->canWrite())
                                <a href="{{ route('app.records.create', ['clinic' => $clinic->slug, 'patient' => $patient->id]) }}"
                                   wire:navigate
                                   class="inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                    {{ __('records.create_first') }} →
                                </a>
                            @endif
                        @endcan
                    </div>
                @endforelse
            </div>

            @if($records->hasPages())
                <div class="mt-4">
                    {{ $records->links() }}
                </div>
            @endif

        </div>
    </div>
</div>
