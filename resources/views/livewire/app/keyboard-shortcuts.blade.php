{{--
    F.10 — Atajos de teclado globales
    g+p → Pacientes | g+a → Citas | g+c → Calendario | g+i → Facturas | g+r → Reportes | g+d → Dashboard
    ? → Modal de atajos
--}}
<div
    x-data="{
        open: false,
        gMode: false,
        gTimer: null,

        {{-- Mapa de navegación filtrado por permisos del usuario (generado en PHP) --}}
        navMap: @js(
            collect($shortcuts['navigate'])
                ->mapWithKeys(fn ($s) => [
                    substr(strrchr($s['key'], ' '), 1) => $s['url']
                ])
                ->all()
        ),

        init() {
            window.addEventListener('keydown', (e) => this.handleKey(e));
        },

        isEditable(el) {
            const tag = el.tagName.toLowerCase();
            return tag === 'input' || tag === 'textarea' || tag === 'select' || el.isContentEditable;
        },

        handleKey(e) {
            if (this.isEditable(e.target)) return;
            if (e.ctrlKey || e.metaKey || e.altKey) return;

            const key = e.key;

            if (key === '?') {
                e.preventDefault();
                this.open = !this.open;
                return;
            }

            if (key === 'Escape' && this.open) {
                this.open = false;
                return;
            }

            if (key === 'g') {
                e.preventDefault();
                this.gMode = true;
                clearTimeout(this.gTimer);
                this.gTimer = setTimeout(() => { this.gMode = false; }, 1500);
                return;
            }

            if (this.gMode) {
                clearTimeout(this.gTimer);
                this.gMode = false;
                if (this.navMap[key]) {
                    e.preventDefault();
                    window.location.href = this.navMap[key];
                }
            }
        },
    }"
    x-init="init()"
>
    {{-- Modal de atajos --}}
    <div
        x-show="open"
        x-transition
        x-cloak
        @click.self="open = false"
        class="fixed inset-0 z-[9998] bg-black/50 flex items-center justify-center p-4"
    >
        <div
            @click.stop
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                    {{ __('shortcuts.modal_title') }}
                </h2>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-5">
                {{-- Navegación --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3">
                        {{ __('shortcuts.section_navigate') }}
                    </p>
                    <ul class="space-y-2">
                        @foreach($shortcuts['navigate'] as $s)
                            <li class="flex items-center justify-between">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $s['label'] }}</span>
                                <span class="flex gap-1">
                                    @foreach(explode(' ', $s['key']) as $k)
                                        <kbd class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-xs font-mono font-semibold text-gray-700 dark:text-gray-200">{{ $k }}</kbd>
                                    @endforeach
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Otros --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3">
                        {{ __('shortcuts.section_other') }}
                    </p>
                    <ul class="space-y-2">
                        <li class="flex items-center justify-between">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('shortcuts.show_shortcuts') }}</span>
                            <kbd class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-xs font-mono font-semibold text-gray-700 dark:text-gray-200">?</kbd>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('shortcuts.close') }}</span>
                            <kbd class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-xs font-mono font-semibold text-gray-700 dark:text-gray-200">Esc</kbd>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-400 dark:text-gray-500 text-center">
                    {{ __('shortcuts.hint') }}
                </p>
            </div>
        </div>
    </div>
</div>

