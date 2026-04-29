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

<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
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

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('app.dashboard', $clinicSlug)" :active="request()->routeIs('app.dashboard')" wire:navigate>
                        {{ __('general.dashboard') }}
                    </x-nav-link>

                    <x-nav-link :href="route('app.patients.index', $clinicSlug)" :active="request()->routeIs('app.patients.*')" wire:navigate>
                        {{ __('general.patients') }}
                    </x-nav-link>

                    <x-nav-link :href="route('app.appointments.index', $clinicSlug)" :active="request()->routeIs('app.appointments.*')" wire:navigate>
                        {{ __('general.appointments') }}
                    </x-nav-link>

                    @can('users.manage')
                    <x-nav-link :href="route('app.staff.index', $clinicSlug)" :active="request()->routeIs('app.staff.*')" wire:navigate>
                        {{ __('general.staff') }}
                    </x-nav-link>
                    @endcan

                    @can('reports.view')
                    <x-nav-link :href="route('app.reports', $clinicSlug)" :active="request()->routeIs('app.reports')" wire:navigate>
                        {{ __('general.reports') }}
                    </x-nav-link>
                    @endcan
                </div>
            </div>

            <!-- Settings Icon & User Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 sm:gap-2">
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
                    class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                    :title="dark ? '{{ __('general.light_mode') }}' : '{{ __('general.dark_mode') }}'"
                >
                    <!-- Sun icon (shown in dark mode) -->
                    <svg x-show="dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <!-- Moon icon (shown in light mode) -->
                    <svg x-show="!dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('app.dashboard', $clinicSlug)" :active="request()->routeIs('app.dashboard')" wire:navigate>
                {{ __('general.dashboard') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('app.patients.index', $clinicSlug)" :active="request()->routeIs('app.patients.*')" wire:navigate>
                {{ __('general.patients') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('app.appointments.index', $clinicSlug)" :active="request()->routeIs('app.appointments.*')" wire:navigate>
                {{ __('general.appointments') }}
            </x-responsive-nav-link>

            @can('users.manage')
            <x-responsive-nav-link :href="route('app.staff.index', $clinicSlug)" :active="request()->routeIs('app.staff.*')" wire:navigate>
                {{ __('general.staff') }}
            </x-responsive-nav-link>
            @endcan

            @can('reports.view')
            <x-responsive-nav-link :href="route('app.reports', $clinicSlug)" :active="request()->routeIs('app.reports')" wire:navigate>
                {{ __('general.reports') }}
            </x-responsive-nav-link>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Language Switcher (Responsive) -->
                <div class="px-4 py-2">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('general.language') }}</p>
                    <div class="flex gap-2">
                        <a href="{{ route('lang.switch', 'es') }}"
                           class="flex items-center px-3 py-1.5 rounded-md text-sm {{ app()->getLocale() === 'es' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 font-medium' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                            🇪🇸 ES
                        </a>
                        <a href="{{ route('lang.switch', 'en') }}"
                           class="flex items-center px-3 py-1.5 rounded-md text-sm {{ app()->getLocale() === 'en' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 font-medium' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                            🇺🇸 EN
                        </a>
                    </div>
                </div>

                <!-- Theme Toggle (Responsive) -->
                <div class="px-4 py-2">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('general.theme') }}</p>
                    <div class="flex gap-2"
                         x-data="{ dark: localStorage.getItem('theme') === 'dark' }">
                        <button x-on:click="dark = false; localStorage.setItem('theme', 'light'); document.documentElement.classList.remove('dark'); $wire.updateTheme('light')"
                                :class="!dark ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 font-medium' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                class="flex items-center px-3 py-1.5 rounded-md text-sm">
                            ☀️ {{ __('general.light') }}
                        </button>
                        <button x-on:click="dark = true; localStorage.setItem('theme', 'dark'); document.documentElement.classList.add('dark'); $wire.updateTheme('dark')"
                                :class="dark ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 font-medium' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                class="flex items-center px-3 py-1.5 rounded-md text-sm">
                            🌙 {{ __('general.dark') }}
                        </button>
                    </div>
                </div>

                <x-responsive-nav-link :href="route('app.settings', $clinicSlug)" :active="request()->routeIs('app.settings')" wire:navigate>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ __('general.settings') }}
                    </span>
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('general.profile') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('app.billing.index', $clinicSlug)" :active="request()->routeIs('app.billing.*')" wire:navigate>
                    {{ __('general.billing') }}
                </x-responsive-nav-link>

                @if(auth()->user()->is_super_admin)
                    <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                    <x-responsive-nav-link :href="route('admin.dashboard')" wire:navigate>
                        <span class="flex items-center gap-2">
                            <span class="px-1.5 py-0.5 text-xs font-bold bg-red-600 text-white rounded">ADMIN</span>
                            {{ __('admin.admin_panel') }}
                        </span>
                    </x-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('general.logout') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
