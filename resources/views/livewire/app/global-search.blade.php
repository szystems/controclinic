<div
    x-data="{ open: false }"
    @keydown.ctrl.k.prevent.window="open = true; $nextTick(() => $refs.searchInput?.focus())"
    @keydown.meta.k.prevent.window="open = true; $nextTick(() => $refs.searchInput?.focus())"
    @keydown.escape.window="if (open) { open = false; $wire.set('query', ''); }"
    @open-global-search.window="open = true; $nextTick(() => $refs.searchInput?.focus())"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/40 dark:bg-black/60 z-40 backdrop-blur-sm"
        @click="open = false; $wire.set('query', '')"
        aria-hidden="true"
    ></div>

    {{-- Modal --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
        class="fixed inset-x-4 top-16 md:inset-x-auto md:left-1/2 md:-translate-x-1/2 md:w-full md:max-w-2xl z-50 bg-white dark:bg-gray-800 rounded-xl shadow-2xl ring-1 ring-black/10 dark:ring-white/10 overflow-hidden"
        role="dialog"
        aria-modal="true"
        :aria-label="'{{ __('search.title') }}'"
    >
        {{-- Search input header --}}
        <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 dark:border-gray-700">
            <div class="flex-shrink-0">
                <svg wire:loading.remove wire:target="query" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <svg wire:loading wire:target="query" class="w-5 h-5 text-indigo-400 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>

            <input
                x-ref="searchInput"
                wire:model.live.debounce.300ms="query"
                type="search"
                autocomplete="off"
                spellcheck="false"
                placeholder="{{ __('search.placeholder') }}"
                class="flex-1 text-sm bg-transparent border-none outline-none text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
            >

            <kbd class="hidden sm:inline-flex items-center px-1.5 py-0.5 text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 rounded border border-gray-300 dark:border-gray-600 font-mono flex-shrink-0">
                ESC
            </kbd>
        </div>

        {{-- Results area --}}
        <div class="max-h-[28rem] overflow-y-auto" id="global-search-results">
            @if(mb_strlen($query) < 2)
                {{-- Hint state --}}
                <div class="flex flex-col items-center justify-center py-10 px-4 text-center">
                    <svg class="w-8 h-8 text-gray-200 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <p class="text-sm text-gray-400 dark:text-gray-500">{{ __('search.hint') }}</p>
                </div>

            @elseif(count($results) === 0)
                {{-- No results --}}
                <div class="flex flex-col items-center justify-center py-10 px-4 text-center">
                    <svg class="w-8 h-8 text-gray-200 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('search.no_results', ['query' => e($query)]) }}
                    </p>
                </div>

            @else
                @php
                    $grouped = collect($results)->groupBy('type');
                    $isFirstGroup = true;
                @endphp

                @foreach(['patient', 'appointment', 'record'] as $typeKey)
                    @if($grouped->has($typeKey))
                        @if(!$isFirstGroup)
                            <div class="mx-4 border-t border-gray-100 dark:border-gray-700"></div>
                        @endif
                        @php $isFirstGroup = false; @endphp

                        <div>
                            {{-- Group header --}}
                            <div class="px-4 pt-3 pb-1">
                                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                                    {{ __('search.type_' . $typeKey) }}
                                </span>
                            </div>

                            {{-- Items --}}
                            @foreach($grouped[$typeKey] as $item)
                                <a
                                    href="{{ $item['url'] }}"
                                    @click="open = false"
                                    class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group"
                                >
                                    {{-- Icon --}}
                                    @if($typeKey === 'patient')
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    @elseif($typeKey === 'appointment')
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                    @endif

                                    {{-- Text --}}
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                            {{ $item['title'] }}
                                        </p>
                                        @if(!empty($item['subtitle']))
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $item['subtitle'] }}</p>
                                        @endif
                                    </div>

                                    {{-- Meta --}}
                                    @if(!empty($item['meta']))
                                        <span class="text-xs text-gray-400 dark:text-gray-500 flex-shrink-0 font-mono">
                                            {{ $item['meta'] }}
                                        </span>
                                    @endif

                                    <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            @endif
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between px-4 py-2 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/80">
            <span class="text-xs text-gray-400 dark:text-gray-500">{{ __('search.footer_tip') }}</span>
            <div class="flex items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500">
                <kbd class="inline-flex items-center px-1.5 py-0.5 bg-white dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600 font-mono">↵</kbd>
                <span>{{ __('search.footer_navigate') }}</span>
            </div>
        </div>
    </div>
</div>
