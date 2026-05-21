<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-8 text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-100 dark:bg-indigo-900/40 mb-4">
                <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('help.title') }}</h1>
            <p class="mt-1 text-gray-500 dark:text-gray-400 text-sm">{{ __('help.subtitle') }}</p>
        </div>

        {{-- Search --}}
        <div class="mb-8" x-data="{ query: '' }">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input
                    x-model="query"
                    type="search"
                    placeholder="{{ __('help.search_placeholder') }}"
                    class="block w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                />
            </div>

            {{-- Module grid (filtered by search) --}}
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach(\App\Livewire\App\Help\Index::modules() as $mod)
                    @php
                        $title   = __("help.modules.{$mod}.title");
                        $summary = __("help.modules.{$mod}.summary");
                    @endphp
                    <div
                        x-show="!query || '{{ strtolower($title . ' ' . $summary) }}'.includes(query.toLowerCase())"
                        x-transition
                    >
                        <a
                            href="{{ route('app.help.show', ['clinic' => app('current_clinic')->slug, 'module' => $mod]) }}"
                            wire:navigate
                            class="group flex gap-4 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-indigo-400 dark:hover:border-indigo-500 hover:shadow-sm transition"
                        >
                            <div class="shrink-0 w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/50 transition">
                                <svg class="w-5 h-5 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-sm text-gray-800 dark:text-gray-200 group-hover:text-indigo-700 dark:group-hover:text-indigo-300 transition">
                                    {{ $title }}
                                </p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                                    {{ $summary }}
                                </p>
                            </div>
                            <div class="ml-auto shrink-0 self-center text-gray-300 dark:text-gray-600 group-hover:text-indigo-400 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            {{-- No results --}}
            <p
                x-show="query && !document.querySelector('[x-show]:not([style*=\'display: none\'])')"
                x-cloak
                class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400"
                x-text="@js(__('help.no_results', ['query' => ''])). replace(':query', query)"
            ></p>
        </div>
    </div>
</div>
