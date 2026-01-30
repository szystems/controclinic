<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $clinic->name ?? 'Clínica' }} - ControClinic</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-50 dark:bg-gray-900">

    {{-- Header --}}
    <header class="bg-white dark:bg-gray-800 shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $clinic->name ?? 'Clínica Demo' }}
                    </h1>
                    @if($clinic->address ?? false)
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $clinic->address }}, {{ $clinic->city }}
                        </p>
                    @endif
                </div>
                <div class="text-right">
                    @if($clinic->phone ?? false)
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            {{ $clinic->phone }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Información de la Clínica --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden mb-8">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    Bienvenido a {{ $clinic->name ?? 'nuestra clínica' }}
                </h2>
                <p class="text-gray-600 dark:text-gray-300">
                    Reserve su cita de manera fácil y rápida. Seleccione el doctor y horario que mejor le convenga.
                </p>
            </div>
        </div>

        {{-- Booking Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Doctores disponibles --}}
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Nuestros Doctores
                    </h3>

                    <div class="space-y-4">
                        {{-- Doctor Card Example --}}
                        <div class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-indigo-500 text-white font-semibold">
                                    JD
                                </span>
                            </div>
                            <div class="ml-4 flex-1">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Dr. Juan Demo</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Medicina General</p>
                            </div>
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Disponible
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Booking Form --}}
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 sticky top-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Reservar Cita
                    </h3>

                    <form class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Nombre Completo
                            </label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Teléfono
                            </label>
                            <input type="tel" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Email (opcional)
                            </label>
                            <input type="email" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Fecha preferida
                            </label>
                            <input type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Motivo de la consulta
                            </label>
                            <textarea rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                        </div>

                        <button type="submit" class="w-full py-3 px-4 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition">
                            Solicitar Cita
                        </button>

                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                            Recibirá confirmación por teléfono o email
                        </p>
                    </form>
                </div>
            </div>
        </div>

    </main>

    {{-- Footer --}}
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $clinic->name ?? 'Clínica' }} © {{ date('Y') }}
                </p>
                <p class="text-sm text-gray-400 dark:text-gray-500">
                    Powered by <a href="https://controclinic.com" class="text-indigo-600 hover:text-indigo-500">ControClinic</a>
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
