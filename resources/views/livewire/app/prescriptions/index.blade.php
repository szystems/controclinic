<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('prescriptions.prescriptions') }}</h1>
            </div>
            @can('prescriptions.create')
            @if($currentClinic->canWrite())
            <a href="{{ route('app.prescriptions.create', ['clinic' => $currentClinic->slug]) }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('prescriptions.new_prescription') }}
            </a>
            @endif
            @endcan
        </div>

        {{-- Flash --}}
        @if(session()->has('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-200">
            {{ session('success') }}
        </div>
        @endif

        {{-- Filtros --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <input type="text" wire:model.live.debounce.300ms="search"
                           placeholder="{{ __('prescriptions.search_patient') }}…"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                </div>
                <div>
                    <select wire:model.live="filterStatus"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">{{ __('prescriptions.all_statuses') }}</option>
                        @foreach($statuses as $s)
                        <option value="{{ $s }}">{{ __('prescriptions.status_' . $s) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if($prescriptions->isEmpty())
            <x-empty-state
                icon="prescription"
                :title="__('prescriptions.no_prescriptions')"
                :description="__('prescriptions.no_prescriptions_description')"
                :bullets="[__('prescriptions.empty_state_bullet_1'), __('prescriptions.empty_state_bullet_2'), __('prescriptions.empty_state_bullet_3')]"
                :cta-text="__('prescriptions.new_prescription')"
                :cta-route="route('app.prescriptions.create', ['clinic' => $currentClinic->slug])"
                cta-permission="prescriptions.create"
            />
            @else
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('prescriptions.folio') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('prescriptions.patient') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('prescriptions.doctor') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('prescriptions.issued_at') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('prescriptions.valid_until') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('prescriptions.status') }}</th>
                        <th class="relative px-6 py-3"><span class="sr-only">{{ __('general.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($prescriptions as $rx)
                    @php
                        $colors = ['draft'=>'gray','issued'=>'blue','dispensed'=>'green','cancelled'=>'red'];
                        $color = $colors[$rx->status] ?? 'gray';
                        $showUrl = route('app.prescriptions.show', ['clinic' => $currentClinic->slug, 'prescription' => $rx->id]);
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors"
                        onclick="window.location.href='{{ $showUrl }}'">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-semibold text-gray-900 dark:text-white">
                            {{ $rx->folio ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                            {{ $rx->patient->full_name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $rx->doctor->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $rx->issued_at ? $rx->issued_at->format('d/m/Y') : '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $rx->is_expired ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ $rx->valid_until ? $rx->valid_until->format('d/m/Y') : '—' }}
                            @if($rx->is_expired) <span class="text-xs">({{ __('prescriptions.expired') }})</span> @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-300">
                                {{ $rx->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" onclick="event.stopPropagation()">
                            <a href="{{ $showUrl }}"
                               title="{{ __('general.view') }}"
                               class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 inline-flex items-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $prescriptions->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
