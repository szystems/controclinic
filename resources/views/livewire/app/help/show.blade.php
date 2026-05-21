<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Back link --}}
        <div class="mb-6">
            <a
                href="{{ route('app.help.index', ['clinic' => app('current_clinic')->slug]) }}"
                wire:navigate
                class="inline-flex items-center gap-1.5 text-sm text-indigo-600 dark:text-indigo-400 hover:underline"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ __('help.back_to_help') }}
            </a>
        </div>

        {{-- Article card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            {{-- Card header --}}
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-indigo-50 dark:bg-indigo-900/20">
                <div class="flex items-center gap-3">
                    <div class="shrink-0 w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h1 class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ __("help.modules.{$module}.title") }}
                    </h1>
                </div>
            </div>

            {{-- Summary --}}
            <div class="px-6 py-5">
                <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
                    {{ __("help.modules.{$module}.summary") }}
                </p>

                {{-- Tips --}}
                @php $tips = __("help.modules.{$module}.tips"); @endphp
                @if(is_array($tips) && count($tips))
                    <h2 class="mt-6 text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide">
                        {{ __('help.tips_title') }}
                    </h2>
                    <ul class="mt-3 space-y-2">
                        @foreach($tips as $tip)
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="mt-0.5 shrink-0 w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>{{ $tip }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
