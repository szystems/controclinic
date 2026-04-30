<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Admin - {{ config('app.name', 'ControClinic') }}</title>

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

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            {{-- Admin Navigation --}}
            <nav class="bg-gray-900 dark:bg-gray-950 border-b border-gray-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <div class="flex items-center">
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2">
                                <span class="text-xl font-bold text-white">ControClinic</span>
                                <span class="px-2 py-0.5 text-xs font-medium bg-red-600 text-white rounded">ADMIN</span>
                            </a>

                            <div class="hidden md:flex ml-10 items-baseline space-x-4">
                                <a href="{{ route('admin.dashboard') }}" wire:navigate
                                   class="{{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium">
                                    {{ __('admin.dashboard') }}
                                </a>
                                <a href="{{ route('admin.clinics.index') }}" wire:navigate
                                   class="{{ request()->routeIs('admin.clinics.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium">
                                    {{ __('admin.clinics') }}
                                </a>
                                <a href="{{ route('admin.plans.index') }}" wire:navigate
                                   class="{{ request()->routeIs('admin.plans.*') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium">
                                    {{ __('admin.plans') }}
                                </a>
                                <a href="{{ route('admin.settings') }}" wire:navigate
                                   class="{{ request()->routeIs('admin.settings') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium">
                                    {{ __('admin.settings') }}
                                </a>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            {{-- Theme Toggle --}}
                            <button
                                x-data="{ dark: localStorage.getItem('theme') === 'dark' }"
                                x-on:click="
                                    dark = !dark;
                                    var theme = dark ? 'dark' : 'light';
                                    localStorage.setItem('theme', theme);
                                    document.documentElement.classList.toggle('dark', dark);
                                "
                                class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 transition"
                                :title="dark ? '{{ __('general.light_mode') }}' : '{{ __('general.dark_mode') }}'"
                            >
                                <svg x-show="dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <svg x-show="!dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                </svg>
                            </button>

                            {{-- Language Switcher --}}
                            <div x-data="{ open: false }" class="relative">
                                <button x-on:click="open = !open" class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 transition flex items-center gap-1" title="{{ __('general.language') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                                    </svg>
                                    <span class="text-xs font-medium uppercase">{{ app()->getLocale() }}</span>
                                </button>
                                <div x-show="open" x-on:click.away="open = false" x-transition
                                     class="absolute right-0 mt-2 w-32 bg-white dark:bg-gray-700 rounded-md shadow-lg ring-1 ring-black/5 z-50">
                                    <a href="{{ route('lang.switch', 'es') }}" class="block px-4 py-2 text-sm rounded-t-md {{ app()->getLocale() === 'es' ? 'bg-gray-100 dark:bg-gray-600 text-indigo-600 dark:text-indigo-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600' }}">
                                        🇪🇸 Español
                                    </a>
                                    <a href="{{ route('lang.switch', 'en') }}" class="block px-4 py-2 text-sm rounded-b-md {{ app()->getLocale() === 'en' ? 'bg-gray-100 dark:bg-gray-600 text-indigo-600 dark:text-indigo-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600' }}">
                                        🇺🇸 English
                                    </a>
                                </div>
                            </div>

                            {{-- Volver a la clínica de pruebas --}}
                            @if(auth()->user()->clinic)
                            <a href="{{ route('app.dashboard', auth()->user()->clinic->slug) }}"
                               class="text-sm text-gray-400 hover:text-white transition whitespace-nowrap">
                                ← {{ __('admin.back_to_app') }}
                            </a>
                            @endif

                            {{-- User menu con logout --}}
                            <div x-data="{ open: false }" class="relative">
                                <button x-on:click="open = !open"
                                        class="flex items-center gap-2 text-sm text-gray-400 hover:text-white transition px-2 py-1 rounded-md hover:bg-gray-700">
                                    <div class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                    <span class="hidden md:inline">{{ auth()->user()->name }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="open" x-on:click.away="open = false" x-transition
                                     class="absolute right-0 mt-2 w-44 bg-white dark:bg-gray-700 rounded-md shadow-lg ring-1 ring-black/5 z-50 py-1">
                                    <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-600">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('general.signed_in_as') }}</p>
                                        <p class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate">{{ auth()->user()->email }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                                class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                            {{ __('general.logout') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            {{-- Page Heading --}}
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
                    <div class="rounded-md bg-green-50 dark:bg-green-900/30 p-4">
                        <p class="text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
                    </div>
                </div>
            @endif
            @if (session('error'))
                <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
                    <div class="rounded-md bg-red-50 dark:bg-red-900/30 p-4">
                        <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- Page Content --}}
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
