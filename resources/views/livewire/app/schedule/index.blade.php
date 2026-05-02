<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ __('schedule.title') }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if($canManageOthers && $targetDoctor)
                        {{ __('schedule.managing_for', ['name' => $targetDoctor->name]) }}
                    @else
                        {{ __('schedule.subtitle') }}
                    @endif
                </p>
            </div>
            <div class="mt-4 sm:mt-0">
                @if(!$showForm)
                <button wire:click="openCreate"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('schedule.new_block') }}
                </button>
                @endif
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session()->has('success'))
        <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/50 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        {{-- Doctor selector (owner/admin only) --}}
        @if($canManageOthers && $doctors->count() > 1)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                {{ __('schedule.doctor_label') }}
            </label>
            <select wire:model.live="selectedDoctorId"
                class="block w-full sm:w-64 py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @foreach($doctors as $doc)
                <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Form (inline) --}}
        @if($showForm)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-indigo-200 dark:border-indigo-700 p-6 mb-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
                {{ $editingId ? __('schedule.form_title_edit') : __('schedule.form_title_create') }}
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Date from --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('schedule.date_from') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="date" wire:model="date_from"
                        class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('date_from')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                {{-- Date to --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('schedule.date_to') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="date" wire:model="date_to"
                        class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('date_to')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                {{-- All day toggle --}}
                <div class="sm:col-span-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model.live="all_day"
                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('schedule.all_day') }}
                        </span>
                    </label>
                </div>

                {{-- Partial hours --}}
                @if(!$all_day)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('schedule.time_from') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="time" wire:model="time_from"
                        class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('time_from')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('schedule.time_to') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="time" wire:model="time_to"
                        class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('time_to')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                @endif

                {{-- Reason --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('schedule.reason') }}
                    </label>
                    <input type="text" wire:model="reason"
                        placeholder="{{ __('schedule.reason_placeholder') }}"
                        class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('reason')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-4 flex items-center gap-3">
                <button wire:click="save"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    {{ __('schedule.save') }}
                </button>
                <button wire:click="cancelForm"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none transition">
                    {{ __('schedule.cancel') }}
                </button>
            </div>
        </div>
        @endif

        {{-- Upcoming blocks --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if($unavailabilities->count() > 0)
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($unavailabilities as $block)
                <li class="px-6 py-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        {{-- Icon --}}
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                        </div>
                        {{-- Info --}}
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $currentClinic->formatDate($block->date_from) }}
                                @if(!$block->date_from->eq($block->date_to))
                                    &ndash; {{ $currentClinic->formatDate($block->date_to) }}
                                @endif
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @if($block->all_day)
                                    {{ __('schedule.all_day_label') }}
                                @else
                                    {{ __('schedule.partial_label', ['from' => $block->time_from, 'to' => $block->time_to]) }}
                                @endif
                                @if($block->reason)
                                    &middot; {{ $block->reason }}
                                @endif
                            </p>
                        </div>
                    </div>
                    {{-- Actions --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button wire:click="openEdit('{{ $block->id }}')"
                            class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-800 dark:hover:text-yellow-300"
                            title="{{ __('general.edit') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button wire:click="delete('{{ $block->id }}')"
                            wire:confirm="{{ __('schedule.confirm_delete') }}"
                            class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
                            title="{{ __('schedule.delete') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </li>
                @endforeach
            </ul>
            @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('schedule.no_blocks') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('schedule.no_blocks_desc') }}</p>
                @if(!$showForm)
                <div class="mt-6">
                    <button wire:click="openCreate"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('schedule.new_block') }}
                    </button>
                </div>
                @endif
            </div>
            @endif
        </div>

        {{-- Past blocks (collapsed) --}}
        @if($pastUnavailabilities->count() > 0)
        <div class="mt-6" x-data="{ open: false }">
            <button @click="open = !open"
                class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                {{ __('general.past') }} ({{ $pastUnavailabilities->count() }})
            </button>
            <div x-show="open" x-collapse class="mt-3 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($pastUnavailabilities as $block)
                    <li class="px-6 py-3 flex items-center justify-between gap-4 opacity-60">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $currentClinic->formatDate($block->date_from) }}
                                @if(!$block->date_from->eq($block->date_to))
                                    &ndash; {{ $currentClinic->formatDate($block->date_to) }}
                                @endif
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                @if($block->all_day)
                                    {{ __('schedule.all_day_label') }}
                                @else
                                    {{ __('schedule.partial_label', ['from' => $block->time_from, 'to' => $block->time_to]) }}
                                @endif
                                @if($block->reason) &middot; {{ $block->reason }} @endif
                            </p>
                        </div>
                        <button wire:click="delete('{{ $block->id }}')"
                            wire:confirm="{{ __('schedule.confirm_delete') }}"
                            class="text-red-400 hover:text-red-600 dark:text-red-500 dark:hover:text-red-400 flex-shrink-0"
                            title="{{ __('schedule.delete') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

    </div>
</div>
