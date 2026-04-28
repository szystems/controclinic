@props(['clinic', 'title' => null])

@php
    $branding = $clinic->branding ?? [];
    $primary = $branding['primary_color'] ?? '#4f46e5';
    $secondary = $branding['secondary_color'] ?? '#10b981';
    $logo = $branding['logo'] ?? null;
    // Convert hex to RGB for CSS variables
    $hex = ltrim($primary, '#');
    if (strlen($hex) === 6) {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    } else {
        $r = 79; $g = 70; $b = 229;
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="index,follow">

    <title>{{ $title ?? __('booking.page_title', ['clinic' => $clinic->name]) }}</title>
    <meta name="description" content="{{ __('booking.page_title', ['clinic' => $clinic->name]) }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $clinic->name }}">
    <meta property="og:description" content="{{ __('booking.page_title', ['clinic' => $clinic->name]) }}">
    @if($logo)
        <meta property="og:image" content="{{ asset('storage/' . $logo) }}">
    @endif

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --color-primary: {{ $r }}, {{ $g }}, {{ $b }};
            --clinic-primary: {{ $primary }};
            --clinic-secondary: {{ $secondary }};
        }
        .btn-clinic-primary {
            background-color: var(--clinic-primary);
            color: white;
            transition: filter 0.15s ease;
        }
        .btn-clinic-primary:hover:not(:disabled) {
            filter: brightness(0.92);
        }
        .btn-clinic-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .text-clinic-primary { color: var(--clinic-primary); }
        .border-clinic-primary { border-color: var(--clinic-primary); }
        .ring-clinic-primary:focus { --tw-ring-color: var(--clinic-primary); }
        .bg-clinic-primary-soft { background-color: rgba({{ $r }}, {{ $g }}, {{ $b }}, 0.08); }
    </style>

    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900 min-h-screen flex flex-col">

    {{-- Header --}}
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    @if($logo)
                        <img src="{{ asset('storage/' . $logo) }}" alt="{{ $clinic->name }}" class="h-12 w-12 object-contain rounded-lg">
                    @else
                        <div class="h-12 w-12 rounded-lg flex items-center justify-center text-white font-bold text-lg" style="background-color: {{ $primary }}">
                            {{ strtoupper(substr($clinic->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 leading-tight">{{ $clinic->name }}</h1>
                        @if($clinic->city)
                            <p class="text-sm text-gray-500">{{ $clinic->city }}{{ $clinic->country ? ', ' . $clinic->country : '' }}</p>
                        @endif
                    </div>
                </div>

                {{-- Language switcher --}}
                <div class="flex items-center space-x-2 text-sm">
                    <a href="{{ route('lang.switch', 'es') }}" class="px-2 py-1 rounded {{ app()->getLocale() === 'es' ? 'font-semibold text-clinic-primary' : 'text-gray-500 hover:text-gray-700' }}">ES</a>
                    <span class="text-gray-300">|</span>
                    <a href="{{ route('lang.switch', 'en') }}" class="px-2 py-1 rounded {{ app()->getLocale() === 'en' ? 'font-semibold text-clinic-primary' : 'text-gray-500 hover:text-gray-700' }}">EN</a>
                </div>
            </div>
        </div>
    </header>

    {{-- Main --}}
    <main class="flex-1 max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-sm text-gray-500">
                <p>{{ $clinic->name }} © {{ date('Y') }}</p>
                <p>
                    {{ __('booking.powered_by') }}
                    <a href="{{ route('home') }}" class="text-clinic-primary font-medium hover:underline">ControClinic</a>
                </p>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
