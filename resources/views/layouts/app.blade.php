<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @include('partials._head-branding')

        <!-- Theme: apply before render to avoid flash -->
        <script>
            (function() {
                var theme = localStorage.getItem('theme') || '{{ auth()->user()?->theme ?? 'light' }}';
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            })();
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Dynamic Clinic Branding -->
        @php
            $clinic = request()->route('clinic');
            $branding = $clinic?->branding ?? [];
            $primaryColor = $branding['primary_color'] ?? '#4f46e5';
            $secondaryColor = $branding['secondary_color'] ?? '#10b981';

            // Convert hex to RGB
            $hexToRgb = function($hex) {
                $hex = ltrim($hex, '#');
                return implode(' ', [
                    hexdec(substr($hex, 0, 2)),
                    hexdec(substr($hex, 2, 2)),
                    hexdec(substr($hex, 4, 2))
                ]);
            };

            // Calculate darker shade for hover
            $darkenColor = function($hex, $percent = 15) {
                $hex = ltrim($hex, '#');
                $r = max(0, hexdec(substr($hex, 0, 2)) - (255 * $percent / 100));
                $g = max(0, hexdec(substr($hex, 2, 2)) - (255 * $percent / 100));
                $b = max(0, hexdec(substr($hex, 4, 2)) - (255 * $percent / 100));
                return implode(' ', [round($r), round($g), round($b)]);
            };
        @endphp
        <style>
            :root {
                --color-primary: {{ $hexToRgb($primaryColor) }};
                --color-primary-hover: {{ $darkenColor($primaryColor) }};
                --color-secondary: {{ $hexToRgb($secondaryColor) }};
            }
        </style>
        @paddleJS
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <livewire:layout.navigation />

            <!-- Account status banner (read-only / billing-only per ADR-008) -->
            <x-account-status-banner />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- Toast Notifications -->
        <div
            x-data="{
                toasts: [],
                add(toast) {
                    toast.id = Date.now();
                    this.toasts.push(toast);
                    setTimeout(() => this.remove(toast.id), 4000);
                },
                remove(id) {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }
            }"
            @notify.window="add($event.detail)"
            class="fixed bottom-4 right-4 z-50 space-y-2 pointer-events-none"
            style="min-width: 18rem; max-width: 24rem;"
        >
            <template x-for="toast in toasts" :key="toast.id">
                <div
                    x-show="true"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="pointer-events-auto flex items-start gap-3 rounded-lg px-4 py-3 shadow-lg border"
                    :class="{
                        'bg-green-50 dark:bg-green-900/80 border-green-200 dark:border-green-700 text-green-800 dark:text-green-100': toast.type === 'success',
                        'bg-red-50 dark:bg-red-900/80 border-red-200 dark:border-red-700 text-red-800 dark:text-red-100': toast.type === 'error',
                        'bg-yellow-50 dark:bg-yellow-900/80 border-yellow-200 dark:border-yellow-700 text-yellow-800 dark:text-yellow-100': toast.type === 'warning',
                        'bg-blue-50 dark:bg-blue-900/80 border-blue-200 dark:border-blue-700 text-blue-800 dark:text-blue-100': toast.type === 'info'
                    }"
                >
                    <!-- Icon -->
                    <svg x-show="toast.type === 'success'" class="h-5 w-5 mt-0.5 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <svg x-show="toast.type === 'error'" class="h-5 w-5 mt-0.5 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <svg x-show="toast.type === 'warning'" class="h-5 w-5 mt-0.5 shrink-0 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <svg x-show="toast.type === 'info'" class="h-5 w-5 mt-0.5 shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <!-- Message -->
                    <span class="text-sm font-medium" x-text="toast.message"></span>
                    <!-- Close -->
                    <button @click="remove(toast.id)" class="ml-auto shrink-0 opacity-60 hover:opacity-100 transition">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        {{-- Global Search Modal (Cmd+K / Ctrl+K) --}}
        @if($clinic)
            <livewire:app.global-search :clinic="$clinic" />
        @endif

        {{-- Interactive onboarding tour (F.4) --}}
        @auth
            <livewire:app.tour.launcher />
        @endauth

        {{-- Keyboard shortcuts (F.10) --}}
        @auth
            @php
                try { $__kbClinic = app('current_clinic'); } catch (\Throwable) { $__kbClinic = null; }
            @endphp
            @if($__kbClinic instanceof \App\Models\Clinic)
                <livewire:app.keyboard-shortcuts :clinic="$__kbClinic" />
            @endif
        @endauth

        {{-- NProgress-style global page progress bar (F.9) --}}
        <div
            x-data="{
                show: false,
                width: 0,
                timer: null,
                start() {
                    this.show = true;
                    this.width = 20;
                    clearInterval(this.timer);
                    this.timer = setInterval(() => {
                        if (this.width < 80) this.width += Math.random() * 12 + 3;
                    }, 200);
                },
                finish() {
                    clearInterval(this.timer);
                    this.width = 100;
                    setTimeout(() => { this.show = false; this.width = 0; }, 400);
                },
            }"
            x-init="
                document.addEventListener('livewire:request', () => start());
                document.addEventListener('livewire:response', () => finish());
                document.addEventListener('livewire:navigate', () => start());
                document.addEventListener('livewire:navigated', () => finish());
            "
            x-show="show"
            style="display:none"
            class="fixed top-0 left-0 right-0 z-[9999] h-1 pointer-events-none"
        >
            <div
                class="h-full bg-indigo-500 transition-[width] duration-200 ease-out"
                style="box-shadow: 0 0 8px 1px rgba(99,102,241,0.7)"
                :style="`width: ${width}%`"
            ></div>
        </div>

        {{-- Floating Help button (F.5) --}}
        @auth
            @php
                try { $__helpClinic = app('current_clinic'); } catch (\Throwable) { $__helpClinic = null; }
            @endphp
            @if($__helpClinic instanceof \App\Models\Clinic)
                <a
                    href="{{ route('app.help.index', ['clinic' => $__helpClinic->slug]) }}"
                    title="{{ __('help.title') }}"
                    class="md:hidden fixed bottom-5 right-5 z-40 flex items-center justify-center w-11 h-11 rounded-full bg-indigo-600 hover:bg-indigo-700 shadow-lg text-white transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="sr-only">{{ __('help.title') }}</span>
                </a>
            @endif
        @endauth
    </body>
</html>
