<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'ControClinic' }} - Software para Clínicas Médicas</title>
    <meta name="description" content="{{ $description ?? 'ControClinic es el software de gestión para clínicas médicas más fácil de usar. Agenda citas, gestiona pacientes y haz crecer tu práctica médica.' }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $title ?? 'ControClinic' }} - Software para Clínicas Médicas">
    <meta property="og:description" content="{{ $description ?? 'El software de gestión para clínicas médicas más fácil de usar.' }}">
    <meta property="og:image" content="{{ asset('images/og-image.png') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ $title ?? 'ControClinic' }}">
    <meta property="twitter:description" content="{{ $description ?? 'El software de gestión para clínicas médicas más fácil de usar.' }}">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --color-primary: 79, 70, 229;
            --color-primary-hover: 67, 56, 202;
        }
    </style>
</head>
<body class="font-sans antialiased bg-white text-gray-900">
    <!-- Navigation -->
    <nav x-data="{ mobileMenuOpen: false }" class="fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center space-x-2">
                    <svg class="w-8 h-8 text-indigo-600" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="32" height="32" rx="8" fill="currentColor"/>
                        <path d="M16 8v16M8 16h16" stroke="white" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    <span class="text-xl font-bold text-gray-900">ControClinic</span>
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}#features" class="text-gray-600 hover:text-indigo-600 transition-colors">Características</a>
                    <a href="{{ route('pricing') }}" class="text-gray-600 hover:text-indigo-600 transition-colors">Precios</a>
                    <a href="{{ route('home') }}#testimonials" class="text-gray-600 hover:text-indigo-600 transition-colors">Testimonios</a>
                    <a href="{{ route('contact') }}" class="text-gray-600 hover:text-indigo-600 transition-colors">Contacto</a>
                </div>

                <!-- Auth Buttons -->
                <div class="hidden md:flex items-center space-x-4">
                    @auth
                        <a href="{{ route('app.dashboard', ['clinic' => auth()->user()->clinic->slug ?? 'demo']) }}" class="text-gray-600 hover:text-indigo-600 transition-colors font-medium">
                            Mi Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-indigo-600 transition-colors font-medium">
                            Iniciar Sesión
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all">
                            Prueba Gratis
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
                    <a href="{{ route('home') }}#features" class="text-gray-600 hover:text-indigo-600 transition-colors">Características</a>
                    <a href="{{ route('pricing') }}" class="text-gray-600 hover:text-indigo-600 transition-colors">Precios</a>
                    <a href="{{ route('home') }}#testimonials" class="text-gray-600 hover:text-indigo-600 transition-colors">Testimonios</a>
                    <a href="{{ route('contact') }}" class="text-gray-600 hover:text-indigo-600 transition-colors">Contacto</a>
                    <hr class="border-gray-100">
                    @auth
                        <a href="{{ route('app.dashboard', ['clinic' => auth()->user()->clinic->slug ?? 'demo']) }}" class="text-indigo-600 font-medium">
                            Ir al Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 font-medium">Iniciar Sesión</a>
                        <a href="{{ route('register') }}" class="inline-flex justify-center items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors">
                            Prueba Gratis
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <!-- Brand -->
                <div class="col-span-2 md:col-span-1">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2 mb-4">
                        <svg class="w-8 h-8 text-indigo-500" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="32" height="32" rx="8" fill="currentColor"/>
                            <path d="M16 8v16M8 16h16" stroke="white" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                        <span class="text-xl font-bold text-white">ControClinic</span>
                    </a>
                    <p class="text-sm">
                        Software de gestión para clínicas médicas. Simple, potente y accesible.
                    </p>
                </div>

                <!-- Product -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Producto</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('home') }}#features" class="hover:text-white transition-colors">Características</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-white transition-colors">Precios</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Integraciones</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Actualizaciones</a></li>
                    </ul>
                </div>

                <!-- Resources -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Recursos</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">Centro de Ayuda</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Guías</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Blog</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition-colors">Contacto</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">Privacidad</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Términos de Uso</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Cookies</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">HIPAA</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-gray-800 flex flex-col md:flex-row justify-between items-center">
                <p class="text-sm">
                    © {{ date('Y') }} ControClinic. Todos los derechos reservados.
                </p>
                <div class="flex items-center space-x-4 mt-4 md:mt-0">
                    <!-- Social Links -->
                    <a href="#" class="hover:text-white transition-colors" aria-label="Twitter">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/></svg>
                    </a>
                    <a href="#" class="hover:text-white transition-colors" aria-label="LinkedIn">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                    </a>
                    <a href="#" class="hover:text-white transition-colors" aria-label="Facebook">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="#" class="hover:text-white transition-colors" aria-label="Instagram">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- x-cloak style -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
