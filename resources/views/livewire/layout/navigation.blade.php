<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public $clinicSlug;

    public function mount(): void
    {
        $this->clinicSlug = auth()->user()->clinic?->slug ?? 'demo';
    }

    /**
     * Save theme preference to database.
     */
    public function updateTheme(string $theme): void
    {
        $theme = in_array($theme, ['light', 'dark']) ? $theme : 'light';
        auth()->user()->update(['theme' => $theme]);
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

@php
    $clinicSlug = $clinicSlug;

    $primaryNav = [
        [
            'route' => 'app.dashboard',
            'active' => fn () => request()->routeIs('app.dashboard'),
            'label' => __('general.dashboard'),
            'group' => 'main',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        ],
        [
            'route' => 'app.patients.index',
            'active' => fn () => request()->routeIs('app.patients.*'),
            'label' => __('general.patients'),
            'group' => 'main',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z"/>',
        ],
        [
            'route' => 'app.appointments.index',
            'active' => fn () => request()->routeIs('app.appointments.index') || request()->routeIs('app.appointments.show') || request()->routeIs('app.appointments.create') || request()->routeIs('app.appointments.edit'),
            'label' => __('general.appointments'),
            'group' => 'main',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
        ],
        [
            'route' => 'app.appointments.calendar',
            'active' => fn () => request()->routeIs('app.appointments.calendar'),
            'label' => __('general.calendar'),
            'group' => 'main',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
        ],
    ];

    if (auth()->user()->can('users.manage')) {
        $primaryNav[] = [
            'route' => 'app.staff.index',
            'active' => fn () => request()->routeIs('app.staff.*'),
            'label' => __('general.staff'),
            'group' => 'team',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
        ];
    }

    if (auth()->user()->can('reports.view')) {
        $primaryNav[] = [
            'route' => 'app.reports',
            'active' => fn () => request()->routeIs('app.reports'),
            'label' => __('general.reports'),
            'group' => 'team',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        ];
    }

    $groupedNav = collect($primaryNav)->groupBy('group');
@endphp

<nav x-data="{ open: false }"
     x-effect="document.body.classList.toggle('overflow-hidden', open)"
     class="sticky top-0 z-30 bg-white/95 dark:bg-gray-800/95 backdrop-blur supports-[backdrop-filter]:bg-white/80 dark:supports-[backdrop-filter]:bg-gray-800/80 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('app.dashboard', $clinicSlug) }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <div class="hidden space-x-1 lg:space-x-6 md:-my-px md:ms-6 lg:ms-10 md:flex">
                    @foreach ($primaryNav as $item)
                        @php $active = $item['active'](); @endphp
                        <x-nav-link :href="route($item['route'], $clinicSlug)" :active="$active" wire:navigate :title="$item['label']">
                            <span class="inline-flex items-center gap-1.5 px-2 lg:px-0">
                                <svg class="w-5 h-5 lg:w-4 lg:h-4 shrink-0 {{ $active ? 'text-indigo-600 dark:text-indigo-300' : 'text-gray-400 dark:text-gray-500' }}"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    {!! $item['icon'] !!}
                                </svg>
                                <span class="hidden lg:inline">{{ $item['label'] }}</span>
                            </span>
                        </x-nav-link>
                    @endforeach
                </div>
            </div>

            <!-- Settings Icon & User Dropdown -->
            <div class="hidden md:flex md:items-center md:ms-6 md:gap-2">
                <!-- Theme Toggle -->
                <button
                    x-data="{ dark: localStorage.getItem('theme') === 'dark' }"
                    x-on:click="
                        dark = !dark;
                        var theme = dark ? 'dark' : 'light';
                        localStorage.setItem('theme', theme);
                        document.documentElement.classList.toggle('dark', dark);
                        $wire.updateTheme(theme);
                    "
                    type="button"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                    :title="dark ? '{{ __('general.light_mode') }}' : '{{ __('general.dark_mode') }}'"
                >
                    <!-- Sun icon (shown in dark mode) -->
                    <svg x-show="dark" class="w-5 h-5 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <!-- Moon icon (shown in light mode) -->
                    <svg x-show="!dark" class="w-5 h-5 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>

                <!-- Language Switcher -->
                <x-dropdown align="right" width="32">
                    <x-slot name="trigger">
                        <button class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition flex items-center gap-1" title="{{ __('general.language') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                            </svg>
                            <span class="text-xs font-medium uppercase">{{ app()->getLocale() }}</span>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <a href="{{ route('lang.switch', 'es') }}" class="block px-4 py-2 text-sm {{ app()->getLocale() === 'es' ? 'bg-gray-100 dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            🇪🇸 Español
                        </a>
                        <a href="{{ route('lang.switch', 'en') }}" class="block px-4 py-2 text-sm {{ app()->getLocale() === 'en' ? 'bg-gray-100 dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            🇺🇸 English
                        </a>
                    </x-slot>
                </x-dropdown>

                <!-- Settings Link -->
                <a href="{{ route('app.settings', $clinicSlug) }}" wire:navigate
                   class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition {{ request()->routeIs('app.settings') ? 'bg-gray-100 dark:bg-gray-700 text-indigo-600 dark:text-indigo-400' : '' }}"
                   title="{{ __('general.settings') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </a>

                <!-- User Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('general.profile') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('app.billing.index', $clinicSlug)" wire:navigate>
                            {{ __('general.billing') }}
                        </x-dropdown-link>

                        @if(auth()->user()->is_super_admin)
                            <div class="border-t border-gray-200 dark:border-gray-600"></div>
                            <x-dropdown-link :href="route('admin.dashboard')" wire:navigate>
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ __('admin.admin_panel') }}
                                </span>
                            </x-dropdown-link>
                        @endif

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('general.logout') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center md:hidden">
                <button @click="open = true"
                        :aria-expanded="open.toString()"
                        aria-controls="mobile-drawer"
                        aria-label="{{ __('general.open_menu') }}"
                        class="inline-flex items-center justify-center p-2 md:ms-1 rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Drawer (overlay slide-in, teleported to body to escape sticky stacking context) -->
    <template x-teleport="body">
    <div x-cloak
         x-show="open"
         x-transition.opacity
         class="fixed inset-0 z-50 lg:hidden"
         @keydown.escape.window="open = false"
         id="mobile-drawer"
         role="dialog"
         aria-modal="true">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-900/60 dark:bg-black/70"
             @click="open = false"></div>

        <!-- Panel -->
        <aside x-show="open"
               x-cloak
               x-transition:enter="transform transition ease-out duration-200"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transform transition ease-in duration-150"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               class="absolute inset-y-0 left-0 w-[85vw] max-w-xs bg-white dark:bg-gray-800 shadow-2xl flex flex-col overflow-y-auto">

            <!-- Drawer header -->
            <div class="flex items-center justify-between px-4 h-16 border-b border-gray-100 dark:border-gray-700 shrink-0">
                <a href="{{ route('app.dashboard', $clinicSlug) }}" wire:navigate @click="open = false" class="flex items-center">
                    <x-application-logo class="block h-8 w-auto fill-current text-gray-800 dark:text-gray-200" />
                </a>
                <button @click="open = false"
                        aria-label="{{ __('general.close_menu') }}"
                        class="p-2 -mr-2 rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Primary navigation grouped -->
            <nav class="flex-1 px-2 py-4 space-y-6">
                @foreach (['main' => __('general.nav_main'), 'team' => __('general.nav_team')] as $groupKey => $groupLabel)
                    @if ($groupedNav->has($groupKey))
                        <div>
                            <p class="px-3 mb-2 text-[11px] font-semibold tracking-wider uppercase text-gray-400 dark:text-gray-500">
                                {{ $groupLabel }}
                            </p>
                            <div class="space-y-1">
                                @foreach ($groupedNav->get($groupKey) as $item)
                                    @php $active = $item['active'](); @endphp
                                    <a href="{{ route($item['route'], $clinicSlug) }}"
                                       wire:navigate
                                       @click="open = false"
                                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                                              {{ $active
                                                    ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-200 ring-1 ring-inset ring-indigo-200 dark:ring-indigo-700'
                                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60' }}">
                                        <svg class="w-5 h-5 shrink-0 {{ $active ? 'text-indigo-600 dark:text-indigo-300' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300' }}"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            {!! $item['icon'] !!}
                                        </svg>
                                        <span>{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </nav>

            <!-- Account section -->
            <div class="border-t border-gray-100 dark:border-gray-700 px-2 py-4 space-y-4 shrink-0">
                <!-- User identity -->
                <div class="px-3">
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                         x-data="{{ json_encode(['name' => auth()->user()->name]) }}"
                         x-text="name"
                         x-on:profile-updated.window="name = $event.detail.name"></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ auth()->user()->email }}</div>
                </div>

                <!-- Language Switcher -->
                <div class="px-3">
                    <p class="text-[11px] font-semibold tracking-wider uppercase text-gray-400 dark:text-gray-500 mb-2">{{ __('general.language') }}</p>
                    <div class="flex gap-2">
                        <a href="{{ route('lang.switch', 'es') }}"
                           class="flex-1 text-center px-3 py-1.5 rounded-md text-sm border {{ app()->getLocale() === 'es' ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-200 border-indigo-200 dark:border-indigo-700 font-medium' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600' }}">
                            🇪🇸 ES
                        </a>
                        <a href="{{ route('lang.switch', 'en') }}"
                           class="flex-1 text-center px-3 py-1.5 rounded-md text-sm border {{ app()->getLocale() === 'en' ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-200 border-indigo-200 dark:border-indigo-700 font-medium' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600' }}">
                            🇺🇸 EN
                        </a>
                    </div>
                </div>

                <!-- Theme Toggle -->
                <div class="px-3" x-data="{ dark: localStorage.getItem('theme') === 'dark' }">
                    <p class="text-[11px] font-semibold tracking-wider uppercase text-gray-400 dark:text-gray-500 mb-2">{{ __('general.theme') }}</p>
                    <div class="flex gap-2">
                        <button type="button"
                                x-on:click="dark = false; localStorage.setItem('theme', 'light'); document.documentElement.classList.remove('dark'); $wire.updateTheme('light')"
                                :class="!dark ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-200 border-indigo-200 dark:border-indigo-700 font-medium' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600'"
                                class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-md text-sm border">
                            ☀️ {{ __('general.light') }}
                        </button>
                        <button type="button"
                                x-on:click="dark = true; localStorage.setItem('theme', 'dark'); document.documentElement.classList.add('dark'); $wire.updateTheme('dark')"
                                :class="dark ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-200 border-indigo-200 dark:border-indigo-700 font-medium' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600'"
                                class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-md text-sm border">
                            🌙 {{ __('general.dark') }}
                        </button>
                    </div>
                </div>

                <!-- Account links -->
                <div>
                    <p class="px-3 mb-2 text-[11px] font-semibold tracking-wider uppercase text-gray-400 dark:text-gray-500">{{ __('general.nav_account') }}</p>
                    <div class="space-y-1">
                        @php
                            $accountLinks = [
                                [
                                    'href' => route('app.settings', $clinicSlug),
                                    'label' => __('general.settings'),
                                    'active' => request()->routeIs('app.settings'),
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
                                ],
                                [
                                    'href' => route('profile'),
                                    'label' => __('general.profile'),
                                    'active' => request()->routeIs('profile'),
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
                                ],
                                [
                                    'href' => route('app.billing.index', $clinicSlug),
                                    'label' => __('general.billing'),
                                    'active' => request()->routeIs('app.billing.*'),
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h2m4 0h4M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/>',
                                ],
                            ];
                        @endphp
                        @foreach ($accountLinks as $link)
                            <a href="{{ $link['href'] }}"
                               wire:navigate
                               @click="open = false"
                               class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                                      {{ $link['active']
                                            ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-200 ring-1 ring-inset ring-indigo-200 dark:ring-indigo-700'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60' }}">
                                <svg class="w-5 h-5 shrink-0 {{ $link['active'] ? 'text-indigo-600 dark:text-indigo-300' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300' }}"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    {!! $link['icon'] !!}
                                </svg>
                                <span>{{ $link['label'] }}</span>
                            </a>
                        @endforeach

                        @if(auth()->user()->is_super_admin)
                            <a href="{{ route('admin.dashboard') }}"
                               wire:navigate
                               @click="open = false"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60">
                                <span class="px-1.5 py-0.5 text-[10px] font-bold bg-red-600 text-white rounded">ADMIN</span>
                                <span>{{ __('admin.admin_panel') }}</span>
                            </a>
                        @endif

                        <button wire:click="logout"
                                @click="open = false"
                                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>{{ __('general.logout') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </aside>
    </div>
    </template>
</nav>
