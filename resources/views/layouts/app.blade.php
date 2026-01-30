<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

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
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <livewire:layout.navigation />

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
    </body>
</html>
