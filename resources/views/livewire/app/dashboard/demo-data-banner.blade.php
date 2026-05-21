<div>
    {{-- Show "Load demo" card only when clinic is empty and no demo data exists --}}
    @if($isEmpty && ! $hasDemo)
        <div class="mb-6 rounded-xl border border-dashed border-indigo-300 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/20 p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-800 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-indigo-900 dark:text-indigo-100">
                        {{ __('demo_data.load_title') }}
                    </h3>
                    <p class="mt-1 text-sm text-indigo-700 dark:text-indigo-300">
                        {{ __('demo_data.load_subtitle') }}
                    </p>
                    <ul class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-indigo-600 dark:text-indigo-400">
                        <li class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ __('demo_data.includes_patients') }}
                        </li>
                        <li class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ __('demo_data.includes_appointments') }}
                        </li>
                        <li class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ __('demo_data.includes_records') }}
                        </li>
                        <li class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ __('demo_data.includes_invoices') }}
                        </li>
                    </ul>
                </div>
                <button
                    wire:click="loadDemo"
                    wire:loading.attr="disabled"
                    wire:target="loadDemo"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition">
                    <span wire:loading.remove wire:target="loadDemo">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </span>
                    <span wire:loading wire:target="loadDemo">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                    </span>
                    {{ __('demo_data.load_btn') }}
                </button>
            </div>
        </div>
    @endif

    {{-- Banner shown while demo data is active --}}
    @if($hasDemo)
        <div class="mb-6 rounded-xl border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-4">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="flex-1 text-sm text-amber-800 dark:text-amber-200">
                    <span class="font-semibold">{{ __('demo_data.active_label') }}</span>
                    {{ __('demo_data.active_desc') }}
                </p>
                <button
                    wire:click="clearDemo"
                    wire:loading.attr="disabled"
                    wire:target="clearDemo"
                    wire:confirm="{{ __('demo_data.clear_confirm') }}"
                    class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 disabled:opacity-60 text-white text-xs font-medium rounded-lg transition">
                    <span wire:loading.remove wire:target="clearDemo">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </span>
                    <span wire:loading wire:target="clearDemo">
                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                    </span>
                    {{ __('demo_data.clear_btn') }}
                </button>
            </div>
        </div>
    @endif
</div>
