@props([
    'module',          // e.g. 'patients', 'appointments', 'invoices'...
    'helpRoute' => null, // optional: route to full help article
])
@php
    $title   = __("help.modules.{$module}.title");
    $summary = __("help.modules.{$module}.summary");
    $tips    = __("help.modules.{$module}.tips", []);
    $lsKey   = "cc_help_dismissed_{$module}_" . auth()->id();
    try {
        $clinic  = app('current_clinic');
        $helpUrl = $helpRoute ?? route('app.help.show', ['clinic' => $clinic->slug, 'module' => $module]);
    } catch (\Throwable) {
        $helpUrl = $helpRoute ?? '#';
    }
@endphp

<div
    x-data="{ show: true }"
    x-init="show = !localStorage.getItem(@js($lsKey))"
    x-show="show"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
    class="mb-5 rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/40 px-4 py-3"
    role="note"
    aria-label="{{ __('help.how_it_works', ['module' => $title]) }}"
>
    <div class="flex items-start gap-3">
        {{-- Icon --}}
        <div class="shrink-0 mt-0.5 text-blue-500 dark:text-blue-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        {{-- Content --}}
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-0.5">
                {{ __('help.how_it_works', ['module' => $title]) }}
            </p>
            <p class="text-sm text-blue-700 dark:text-blue-400 leading-relaxed">
                {{ $summary }}
            </p>

            @if(count($tips) > 0)
                <ul class="mt-2 space-y-0.5">
                    @foreach($tips as $tip)
                        <li class="flex items-start gap-1.5 text-xs text-blue-600 dark:text-blue-400">
                            <svg class="w-3.5 h-3.5 mt-0.5 shrink-0 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            {{ $tip }}
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="mt-2 flex items-center gap-3">
                <a href="{{ $helpUrl }}" wire:navigate
                   class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">
                    {{ __('help.view_help') }} →
                </a>
            </div>
        </div>

        {{-- Dismiss button --}}
        <button
            type="button"
            @click="show = false; localStorage.setItem(@js($lsKey), '1')"
            class="shrink-0 -mt-0.5 -mr-1 p-1 rounded text-blue-400 dark:text-blue-500 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition"
            :title="@js(__('help.dismiss'))"
            aria-label="{{ __('help.dismiss') }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
