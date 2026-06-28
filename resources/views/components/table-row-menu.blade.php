@props([
    'width' => 'w-52',
])

<div
    x-data="{
        open: false,
        dropUp: false,
        toggle() {
            this.open = !this.open;
            if (!this.open) {
                return;
            }
            this.$nextTick(() => {
                const trigger = this.$refs.trigger;
                const panel = this.$refs.panel;
                if (!trigger || !panel) {
                    return;
                }
                const rect = trigger.getBoundingClientRect();
                const panelHeight = panel.offsetHeight || 220;
                this.dropUp = (rect.bottom + panelHeight + 8) > window.innerHeight;
            });
        },
    }"
    class="relative inline-block text-left"
    @click.stop
>
    <button
        type="button"
        x-ref="trigger"
        @click="toggle()"
        @click.outside="open = false"
        class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-200 dark:hover:bg-gray-700 transition-colors"
    >
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 5a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 7a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 7a1.5 1.5 0 110-3 1.5 1.5 0 010 3z"/>
        </svg>
        <span class="sr-only">{{ __('general.actions') }}</span>
    </button>

    <div
        x-ref="panel"
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        :class="dropUp ? 'bottom-full mb-1' : 'top-full mt-1'"
        class="absolute right-0 z-50 {{ $width }} bg-white dark:bg-gray-800 rounded-lg shadow-lg ring-1 ring-black/10 divide-y divide-gray-100 dark:divide-gray-700"
        x-cloak
    >
        {{ $slot }}
    </div>
</div>
