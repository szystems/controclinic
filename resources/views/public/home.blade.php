<x-public-layout>
    {{-- Hero Section --}}
    <section class="relative pt-32 pb-20 lg:pt-40 lg:pb-32 overflow-hidden">
        {{-- Background gradient --}}
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 via-white to-purple-50"></div>
        <div class="absolute top-0 right-0 -translate-y-1/4 translate-x-1/4 w-96 h-96 bg-indigo-200 rounded-full blur-3xl opacity-30"></div>
        <div class="absolute bottom-0 left-0 translate-y-1/4 -translate-x-1/4 w-96 h-96 bg-purple-200 rounded-full blur-3xl opacity-30"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                {{-- Text Content --}}
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center px-4 py-2 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium mb-6">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Prueba gratis por 14 días
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight">
                        Gestiona tu clínica
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">
                            sin complicaciones
                        </span>
                    </h1>

                    <p class="mt-6 text-xl text-gray-600 max-w-2xl mx-auto lg:mx-0">
                        El software todo-en-uno para clínicas médicas. Agenda citas, gestiona pacientes, envía recordatorios y haz crecer tu práctica médica.
                    </p>

                    <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white text-lg font-semibold rounded-xl shadow-lg shadow-indigo-200 hover:shadow-xl hover:shadow-indigo-300 transition-all">
                            Comenzar Gratis
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                        <a href="#demo" class="inline-flex items-center justify-center px-8 py-4 bg-white hover:bg-gray-50 text-gray-700 text-lg font-semibold rounded-xl border-2 border-gray-200 hover:border-gray-300 transition-all">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                            </svg>
                            Ver Demo
                        </a>
                    </div>

                    {{-- Trust badges --}}
                    <div class="mt-12 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-sm text-gray-500">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Sin tarjeta de crédito
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Configuración en 5 min
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Cancela cuando quieras
                        </div>
                    </div>
                </div>

                {{-- Hero Image / Dashboard Preview --}}
                <div class="relative">
                    <div class="relative rounded-2xl shadow-2xl overflow-hidden bg-white border border-gray-200">
                        {{-- Browser mockup header --}}
                        <div class="flex items-center gap-2 px-4 py-3 bg-gray-100 border-b border-gray-200">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            <div class="flex-1 mx-4">
                                <div class="bg-white rounded-md px-3 py-1 text-sm text-gray-400 text-center">
                                    app.controclinic.com/demo/dashboard
                                </div>
                            </div>
                        </div>
                        {{-- Dashboard preview --}}
                        <div class="p-4 bg-gray-50">
                            <div class="space-y-4">
                                {{-- Top stats --}}
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="bg-white rounded-lg p-3 shadow-sm">
                                        <p class="text-xs text-gray-500">Citas Hoy</p>
                                        <p class="text-2xl font-bold text-gray-900">12</p>
                                    </div>
                                    <div class="bg-white rounded-lg p-3 shadow-sm">
                                        <p class="text-xs text-gray-500">Pacientes</p>
                                        <p class="text-2xl font-bold text-gray-900">248</p>
                                    </div>
                                    <div class="bg-white rounded-lg p-3 shadow-sm">
                                        <p class="text-xs text-gray-500">Este Mes</p>
                                        <p class="text-2xl font-bold text-indigo-600">$4,250</p>
                                    </div>
                                </div>
                                {{-- Calendar preview --}}
                                <div class="bg-white rounded-lg p-3 shadow-sm">
                                    <div class="flex items-center justify-between mb-3">
                                        <p class="text-sm font-semibold text-gray-900">Próximas Citas</p>
                                        <span class="text-xs text-indigo-600">Ver todas →</span>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-3 p-2 bg-indigo-50 rounded-lg">
                                            <div class="w-10 h-10 bg-indigo-200 rounded-full flex items-center justify-center text-indigo-700 font-bold text-sm">MR</div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900">María Rodríguez</p>
                                                <p class="text-xs text-gray-500">Consulta General • 9:00 AM</p>
                                            </div>
                                            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">Confirmada</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
                                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-600 font-bold text-sm">JL</div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900">Juan López</p>
                                                <p class="text-xs text-gray-500">Seguimiento • 10:30 AM</p>
                                            </div>
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-full">Pendiente</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Floating elements --}}
                    <div class="absolute -bottom-6 -left-6 bg-white rounded-xl shadow-lg p-4 border border-gray-100 animate-bounce" style="animation-duration: 3s;">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Cita confirmada</p>
                                <p class="text-xs text-gray-500">hace 2 minutos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Logos Section --}}
    <section class="py-16 bg-gray-50 border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500 mb-10">Más de 500 clínicas confían en ControClinic</p>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-8 items-center justify-items-center opacity-60">
                <div class="px-4 py-2">
                    <span class="text-xl font-bold text-gray-500">Clínica</span><span class="text-xl font-bold text-indigo-500">Plus</span>
                </div>
                <div class="px-4 py-2">
                    <span class="text-xl font-bold text-gray-500">Medi</span><span class="text-xl font-bold text-indigo-500">Care</span>
                </div>
                <div class="px-4 py-2">
                    <span class="text-xl font-bold text-gray-500">Salud</span><span class="text-xl font-bold text-indigo-500">Total</span>
                </div>
                <div class="px-4 py-2">
                    <span class="text-xl font-bold text-gray-500">Centro</span><span class="text-xl font-bold text-indigo-500">Médico</span>
                </div>
                <div class="px-4 py-2 col-span-2 md:col-span-1">
                    <span class="text-xl font-bold text-gray-500">Vita</span><span class="text-xl font-bold text-indigo-500">Clinic</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section id="features" class="py-20 lg:py-32 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">
                    Todo lo que necesitas para tu clínica
                </h2>
                <p class="mt-4 text-xl text-gray-600">
                    Desde la gestión de citas hasta historiales médicos, ControClinic tiene todo cubierto.
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                {{-- Feature 1: Appointments --}}
                <div class="group relative bg-white rounded-2xl p-8 border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 transition-colors">
                        <svg class="w-7 h-7 text-indigo-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Agenda Inteligente</h3>
                    <p class="text-gray-600">
                        Gestiona todas tus citas en un calendario visual. Arrastra, suelta y reprograma con facilidad.
                    </p>
                </div>

                {{-- Feature 2: Patients --}}
                <div class="group relative bg-white rounded-2xl p-8 border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-purple-600 transition-colors">
                        <svg class="w-7 h-7 text-purple-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Gestión de Pacientes</h3>
                    <p class="text-gray-600">
                        Fichas completas con información de contacto, historial médico y documentos importantes.
                    </p>
                </div>

                {{-- Feature 3: Reminders --}}
                <div class="group relative bg-white rounded-2xl p-8 border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-green-600 transition-colors">
                        <svg class="w-7 h-7 text-green-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Recordatorios Automáticos</h3>
                    <p class="text-gray-600">
                        Reduce las inasistencias con recordatorios por email, SMS o WhatsApp antes de cada cita.
                    </p>
                </div>

                {{-- Feature 4: Medical Records --}}
                <div class="group relative bg-white rounded-2xl p-8 border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-red-600 transition-colors">
                        <svg class="w-7 h-7 text-red-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Historiales Médicos</h3>
                    <p class="text-gray-600">
                        Mantén un registro completo de consultas, diagnósticos, tratamientos y evolución de cada paciente.
                    </p>
                </div>

                {{-- Feature 5: Multi-tenant --}}
                <div class="group relative bg-white rounded-2xl p-8 border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-yellow-500 transition-colors">
                        <svg class="w-7 h-7 text-yellow-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Multi-Clínica</h3>
                    <p class="text-gray-600">
                        Administra múltiples sucursales o consultorios desde una sola cuenta con datos completamente separados.
                    </p>
                </div>

                {{-- Feature 6: Customization --}}
                <div class="group relative bg-white rounded-2xl p-8 border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-pink-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-pink-600 transition-colors">
                        <svg class="w-7 h-7 text-pink-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Tu Marca, Tu Estilo</h3>
                    <p class="text-gray-600">
                        Personaliza colores, logo y dominio. Tus pacientes verán tu clínica, no nuestra plataforma.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- How it Works --}}
    <section class="py-20 lg:py-32 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">
                    Comienza en minutos
                </h2>
                <p class="mt-4 text-xl text-gray-600">
                    Configurar tu clínica nunca fue tan fácil
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                {{-- Step 1 --}}
                <div class="relative text-center">
                    <div class="w-16 h-16 bg-indigo-600 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-6">
                        1
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Crea tu cuenta</h3>
                    <p class="text-gray-600">
                        Regístrate gratis en menos de 2 minutos. Sin tarjeta de crédito.
                    </p>
                    {{-- Connector line --}}
                    <div class="hidden md:block absolute top-8 left-[60%] w-[80%] h-0.5 bg-indigo-200"></div>
                </div>

                {{-- Step 2 --}}
                <div class="relative text-center">
                    <div class="w-16 h-16 bg-indigo-600 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-6">
                        2
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Configura tu clínica</h3>
                    <p class="text-gray-600">
                        Agrega tu logo, horarios y personaliza tu espacio de trabajo.
                    </p>
                    {{-- Connector line --}}
                    <div class="hidden md:block absolute top-8 left-[60%] w-[80%] h-0.5 bg-indigo-200"></div>
                </div>

                {{-- Step 3 --}}
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-600 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-6">
                        3
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">¡Listo para usar!</h3>
                    <p class="text-gray-600">
                        Comienza a agendar citas y gestionar pacientes inmediatamente.
                    </p>
                </div>
            </div>

            <div class="text-center mt-12">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white text-lg font-semibold rounded-xl shadow-lg shadow-indigo-200 hover:shadow-xl transition-all">
                    Crear mi cuenta gratis
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Testimonials Section --}}
    <section id="testimonials" class="py-20 lg:py-32 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">
                    Lo que dicen nuestros usuarios
                </h2>
                <p class="mt-4 text-xl text-gray-600">
                    Médicos y clínicas de toda Latinoamérica confían en nosotros
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                {{-- Testimonial 1 --}}
                <div class="bg-gray-50 rounded-2xl p-8">
                    <div class="flex items-center gap-1 mb-4">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <p class="text-gray-600 mb-6">
                        "Antes perdía horas organizando citas en papel. Con ControClinic todo está en un solo lugar y mis pacientes reciben recordatorios automáticos. ¡Las inasistencias bajaron un 40%!"
                    </p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold">
                            DM
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Dra. María González</p>
                            <p class="text-sm text-gray-500">Medicina General • México</p>
                        </div>
                    </div>
                </div>

                {{-- Testimonial 2 --}}
                <div class="bg-gray-50 rounded-2xl p-8">
                    <div class="flex items-center gap-1 mb-4">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <p class="text-gray-600 mb-6">
                        "Tenemos 3 consultorios y antes era un caos coordinar. Ahora cada doctor tiene su agenda, todo sincronizado. La personalización con nuestro logo le da un toque muy profesional."
                    </p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 font-bold">
                            CR
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Centro Médico Rodríguez</p>
                            <p class="text-sm text-gray-500">3 doctores • Colombia</p>
                        </div>
                    </div>
                </div>

                {{-- Testimonial 3 --}}
                <div class="bg-gray-50 rounded-2xl p-8">
                    <div class="flex items-center gap-1 mb-4">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <p class="text-gray-600 mb-6">
                        "Lo probé gratis durante 2 semanas y quedé encantado. El precio es muy accesible para un médico independiente como yo. El soporte siempre responde rápido."
                    </p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600 font-bold">
                            JP
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Dr. Juan Pablo Méndez</p>
                            <p class="text-sm text-gray-500">Pediatría • Argentina</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-20 lg:py-32 bg-gradient-to-br from-indigo-600 to-indigo-800 relative overflow-hidden">
        {{-- Background decoration --}}
        <div class="absolute top-0 left-0 w-72 h-72 bg-indigo-500 rounded-full blur-3xl opacity-30 -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500 rounded-full blur-3xl opacity-20 translate-x-1/3 translate-y-1/3"></div>

        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-6">
                ¿Listo para modernizar tu clínica?
            </h2>
            <p class="text-xl text-indigo-100 mb-10 max-w-2xl mx-auto">
                Únete a cientos de médicos que ya optimizaron su práctica con ControClinic. Comienza gratis hoy.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white hover:bg-gray-100 text-indigo-600 text-lg font-semibold rounded-xl shadow-lg transition-all">
                    Comenzar Prueba Gratis
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="{{ route('pricing') }}" class="inline-flex items-center justify-center px-8 py-4 bg-transparent hover:bg-indigo-500 text-white text-lg font-semibold rounded-xl border-2 border-white/30 hover:border-transparent transition-all">
                    Ver Precios
                </a>
            </div>
            <p class="mt-6 text-indigo-200 text-sm">
                14 días gratis • Sin tarjeta de crédito • Cancela cuando quieras
            </p>
        </div>
    </section>
</x-public-layout>
