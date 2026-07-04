<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $appName = app_setting('branding.app_name', 'ControClinic');
        $logoUrl = app_setting('branding.logo_url');
        $primaryColor = app_setting('branding.primary_color', '#4f46e5');
        $defaultMetaTitle = app_setting('seo.meta_title', $appName.' — Software para Clínicas Médicas');
        $defaultMetaDesc = app_setting('seo.meta_description', 'Software de gestión para clínicas médicas. Agenda citas, gestiona pacientes y haz crecer tu práctica.');
        $ogImageUrl = app_setting('seo.og_image_url') ?: asset('images/og-image.png');
        $gaId = app_setting('seo.google_analytics_id');
        $gtmId = app_setting('seo.gtm_id');
        $supportEmail = app_setting('legal.support_email', 'soporte@controclinic.com');
        $termsUrl = app_setting('legal.terms_url', '/terms');
        $privacyUrl = app_setting('legal.privacy_url', '/privacy');

        // Convertir color primario hex → "r, g, b" para usar con CSS rgb()
        $hex = ltrim($primaryColor, '#');
        $primaryRgb = sprintf('%d, %d, %d', hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
    @endphp

    <title>{{ $title ?? $defaultMetaTitle }}</title>
    <meta name="description" content="{{ $description ?? $defaultMetaDesc }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $title ?? $defaultMetaTitle }}">
    <meta property="og:description" content="{{ $description ?? $defaultMetaDesc }}">
    <meta property="og:image" content="{{ $ogImageUrl }}">
    <meta property="og:site_name" content="{{ $appName }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ $title ?? $defaultMetaTitle }}">
    <meta property="twitter:description" content="{{ $description ?? $defaultMetaDesc }}">
    <meta property="twitter:image" content="{{ $ogImageUrl }}">

    <!-- Favicon -->
    @include('partials._head-branding')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --color-primary: {{ $primaryRgb }};
            --color-primary-hex: {{ $primaryColor }};
        }
    </style>

    @if($gtmId)
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ $gtmId }}');</script>
    @endif

    @if($gaId)
    <!-- Google Analytics 4 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $gaId }}');
    </script>
    @endif
</head>
<body class="font-sans antialiased bg-white text-gray-900 overflow-x-hidden">
    @if($gtmId)
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif
    <!-- Navigation -->
    <nav x-data="{ mobileMenuOpen: false }" class="fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center space-x-2">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-10 w-auto" />
                    @else
                        <svg class="w-8 h-8 text-indigo-600" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="32" height="32" rx="8" fill="currentColor"/>
                            <path d="M16 8v16M8 16h16" stroke="white" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                        <span class="text-xl font-bold text-gray-900">{{ $appName }}</span>
                    @endif
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}#features" class="text-gray-600 hover:text-indigo-600 transition-colors">{{ __('public.nav_features') }}</a>
                    <a href="{{ route('pricing') }}" class="text-gray-600 hover:text-indigo-600 transition-colors">{{ __('public.nav_pricing') }}</a>
                    <a href="{{ route('contact') }}" class="text-gray-600 hover:text-indigo-600 transition-colors">{{ __('public.nav_contact') }}</a>
                </div>

                <!-- Language Toggle + Auth Buttons -->
                <div class="hidden md:flex items-center space-x-4">
                    <!-- Language toggle -->
                    <div class="flex items-center text-sm font-medium">
                        <a href="{{ route('lang.switch', 'es') }}"
                           class="px-1.5 py-0.5 rounded transition-colors {{ app()->getLocale() === 'es' ? 'text-indigo-600 font-semibold' : 'text-gray-400 hover:text-gray-700' }}">
                            ES
                        </a>
                        <span class="text-gray-300 select-none">|</span>
                        <a href="{{ route('lang.switch', 'en') }}"
                           class="px-1.5 py-0.5 rounded transition-colors {{ app()->getLocale() === 'en' ? 'text-indigo-600 font-semibold' : 'text-gray-400 hover:text-gray-700' }}">
                            EN
                        </a>
                    </div>
                    @auth
                        <a href="{{ route('app.dashboard', ['clinic' => auth()->user()->clinic->slug ?? 'demo']) }}" class="text-gray-600 hover:text-indigo-600 transition-colors font-medium">
                            {{ __('public.nav_dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-indigo-600 transition-colors font-medium">
                            {{ __('public.nav_login') }}
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all">
                            {{ __('public.nav_start_free') }}
                        </a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span class="sr-only">Abrir menú</span>
                    <template x-if="!mobileMenuOpen">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </template>
                    <template x-if="mobileMenuOpen">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </template>
                </button>
            </div>

            <!-- Mobile menu -->
            <div x-show="mobileMenuOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="md:hidden py-4 bg-white border-t border-gray-200"
                 style="display: none;">
                <div class="flex flex-col space-y-4">
                    <a href="{{ route('home') }}#features" class="text-gray-600 hover:text-indigo-600 transition-colors">{{ __('public.nav_features') }}</a>
                    <a href="{{ route('pricing') }}" class="text-gray-600 hover:text-indigo-600 transition-colors">{{ __('public.nav_pricing') }}</a>
                    <a href="{{ route('contact') }}" class="text-gray-600 hover:text-indigo-600 transition-colors">{{ __('public.nav_contact') }}</a>
                    <hr class="border-gray-100">
                    @auth
                        <a href="{{ route('app.dashboard', ['clinic' => auth()->user()->clinic->slug ?? 'demo']) }}" class="text-indigo-600 font-medium">
                            {{ __('public.nav_go_dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 font-medium">{{ __('public.nav_login') }}</a>
                        <a href="{{ route('register') }}" class="inline-flex justify-center items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors">
                            {{ __('public.nav_start_free') }}
                        </a>
                    @endauth
                    <hr class="border-gray-100">
                    <!-- Language toggle mobile -->
                    <div class="flex items-center space-x-3 text-sm font-medium">
                        <span class="text-gray-500">Idioma:</span>
                        <a href="{{ route('lang.switch', 'es') }}"
                           class="px-2 py-1 rounded transition-colors {{ app()->getLocale() === 'es' ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-500 hover:text-gray-700' }}">
                            Español
                        </a>
                        <a href="{{ route('lang.switch', 'en') }}"
                           class="px-2 py-1 rounded transition-colors {{ app()->getLocale() === 'en' ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-500 hover:text-gray-700' }}">
                            English
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="min-w-0">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <x-site-footer />

    <!-- Cookie Banner (GDPR mínimo) -->
    <div
        x-data="{ show: !localStorage.getItem('cookie_consent') }"
        x-show="show"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-full opacity-0"
        class="fixed bottom-0 inset-x-0 z-50 p-4"
    >
        <div class="max-w-4xl mx-auto bg-gray-900 text-white rounded-xl shadow-2xl flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 sm:p-5">
            <div class="flex-1 text-sm text-gray-300">
                <span class="font-medium text-white">🍪 Cookies</span>
                —
                Usamos cookies esenciales para el funcionamiento del sitio. Al continuar navegando aceptas nuestra
                <a href="{{ route('privacy') }}" class="underline text-indigo-400 hover:text-indigo-300">Política de Privacidad</a>.
            </div>
            <div class="flex gap-2 shrink-0">
                <button
                    type="button"
                    x-on:click="localStorage.setItem('cookie_consent', '1'); show = false"
                    class="px-4 py-2 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition"
                >
                    Aceptar
                </button>
                <a
                    href="{{ route('privacy') }}"
                    class="px-4 py-2 text-sm font-medium bg-gray-700 hover:bg-gray-600 text-gray-200 rounded-lg transition"
                >
                    Más info
                </a>
            </div>
        </div>
    </div>

    <!-- x-cloak style -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
