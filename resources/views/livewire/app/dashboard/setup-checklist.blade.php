<div x-data="{ dismissed: false }" x-show="!dismissed" x-init="
    window.addEventListener('setup-checklist-dismissed', () => dismissed = true)
">
    @php
        $gradientStyles = [
            0   => 'background: linear-gradient(to right, #9ca3af, #6b7280)',
            25  => 'background: linear-gradient(to right, #fbbf24, #f97316)',
            50  => 'background: linear-gradient(to right, #facc15, #f59e0b)',
            75  => 'background: linear-gradient(to right, #6366f1, #8b5cf6)',
            100 => 'background: linear-gradient(to right, #22c55e, #10b981)',
        ];
        $gradientKey   = collect(array_keys($gradientStyles))->last(fn($k) => $progressPercent >= $k);
        $gradientStyle = $gradientStyles[$gradientKey];

        $icons = [
            'photo'    => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
            'clock'    => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            'users'    => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
            'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            'user-add' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
            'globe'    => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ];
    @endphp

    <div class="mb-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="px-5 py-4 flex items-center justify-between cursor-pointer select-none"
             wire:click="toggleCollapse">
            <div class="flex items-center gap-3 min-w-0">
                {{-- Progress ring / badge --}}
                @if($isAllDone)
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                @else
                    <div class="flex-shrink-0 relative w-12 h-12">
                        <svg class="w-12 h-12 -rotate-90" viewBox="0 0 48 48">
                            <circle cx="24" cy="24" r="20" fill="none" stroke="currentColor"
                                    class="text-gray-200 dark:text-gray-700" stroke-width="3.5"/>
                            <circle cx="24" cy="24" r="20" fill="none"
                                    stroke="url(#prog-gradient)" stroke-width="3.5"
                                    stroke-linecap="round"
                                    stroke-dasharray="{{ round(2 * 3.14159 * 20) }}"
                                    stroke-dashoffset="{{ round(2 * 3.14159 * 20 * (1 - $progressPercent / 100)) }}"/>
                            <defs>
                                <linearGradient id="prog-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#6366f1"/>
                                    <stop offset="100%" stop-color="#8b5cf6"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-[11px] font-bold text-indigo-600 dark:text-indigo-400 leading-none">
                            {{ $completedCount }}/{{ $total }}
                        </span>
                    </div>
                @endif

                <div class="min-w-0">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white leading-tight">
                        @if($isAllDone)
                            🎉 {{ __('setup_checklist.all_done_title') }}
                        @else
                            {{ __('setup_checklist.title') }}
                        @endif
                    </h3>
                    @if(! $isAllDone)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ __('setup_checklist.subtitle', ['done' => $completedCount, 'total' => $total]) }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                {{-- Dismiss when all done --}}
                @if($isAllDone)
                    <button wire:click.stop="dismiss"
                            class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 px-2 py-1 rounded transition">
                        {{ __('general.dismiss') }}
                    </button>
                @endif
                {{-- Collapse toggle --}}
                <svg class="w-4 h-4 text-gray-400 transition-transform {{ $collapsed ? '' : 'rotate-180' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>

        {{-- Progress bar --}}
        @if(! $collapsed && ! $isAllDone)
        <div class="px-5 pb-1">
            <div class="h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500"
                     style="width: {{ $progressPercent }}%; {{ $gradientStyle }}"></div>
            </div>
        </div>
        @endif

        {{-- Steps list --}}
        @if(! $collapsed)
        <div class="px-5 pb-4 pt-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach(\App\Livewire\App\Dashboard\SetupChecklist::$STEPS as $key => $meta)
                    @php
                        $done = $stepsStatus[$key] ?? false;
                        $route = $routeForStep[$key] ?? '#';
                    @endphp
                    <a href="{{ $route }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition group
                           {{ $done
                               ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 cursor-default pointer-events-none'
                               : 'bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:text-indigo-700 dark:hover:text-indigo-300' }}">

                        {{-- Step icon --}}
                        <div class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center
                            {{ $done ? 'bg-green-100 dark:bg-green-900/40' : 'bg-white dark:bg-gray-700 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/40 border border-gray-200 dark:border-gray-600' }}">
                            @if($done)
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            @else
                                <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$meta['icon']] ?? '' }}"/>
                                </svg>
                            @endif
                        </div>

                        {{-- Label --}}
                        <span class="flex-1 font-medium text-xs leading-snug">
                            {{ __('setup_checklist.step_' . $key) }}
                        </span>

                        {{-- Arrow on pending --}}
                        @if(! $done)
                        <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
