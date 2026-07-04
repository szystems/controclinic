<x-public-layout>
    {{-- Hero Section --}}
    <section class="relative pt-32 pb-20 lg:pt-40 lg:pb-32">
        {{-- Background (clipped separately so content is not cut off on mobile) --}}
        <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 via-white to-purple-50"></div>
            <div class="absolute top-0 right-0 -translate-y-1/4 translate-x-1/4 w-72 sm:w-96 h-72 sm:h-96 bg-indigo-200 rounded-full blur-3xl opacity-30"></div>
            <div class="absolute bottom-0 left-0 translate-y-1/4 -translate-x-1/4 w-72 sm:w-96 h-72 sm:h-96 bg-purple-200 rounded-full blur-3xl opacity-30"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center min-w-0">
                {{-- Text Content --}}
                <div class="min-w-0 w-full text-center lg:text-left">
                    <div class="inline-flex max-w-full flex-wrap items-center justify-center lg:justify-start gap-x-2 gap-y-1 px-3 sm:px-4 py-2 bg-indigo-100 text-indigo-700 rounded-full text-xs sm:text-sm font-medium mb-6">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('public.freemium_badge') }}
                    </div>

                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight break-words">
                        {{ __('public.home_hero_title') }}
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">
                            {{ __('public.home_hero_highlight') }}
                        </span>
                    </h1>

                    <p class="mt-6 text-lg sm:text-xl text-gray-600 max-w-2xl mx-auto lg:mx-0">
                        {{ __('public.home_hero_subtitle') }}
                    </p>

                    <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ route('register') }}" class="inline-flex w-full sm:w-auto items-center justify-center px-6 sm:px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white text-base sm:text-lg font-semibold rounded-xl shadow-lg shadow-indigo-200 hover:shadow-xl hover:shadow-indigo-300 transition-all">
                            {{ __('public.home_cta_start') }}
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                        <a href="#demo" class="inline-flex w-full sm:w-auto items-center justify-center px-6 sm:px-8 py-4 bg-white hover:bg-gray-50 text-gray-700 text-base sm:text-lg font-semibold rounded-xl border-2 border-gray-200 hover:border-gray-300 transition-all">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                            </svg>
                            {{ __('public.home_cta_demo') }}
                        </a>
                    </div>

                    {{-- Trust badges --}}
                    <div class="mt-12 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-sm text-gray-500">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ __('public.home_trust_no_card') }}
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ __('public.home_trust_setup') }}
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ __('public.home_trust_forever') }}
                        </div>
                    </div>
                </div>

                {{-- Hero Image / Dashboard Preview --}}
                <div id="demo" class="relative min-w-0 w-full max-w-full">
                    <div class="relative w-full max-w-full rounded-2xl shadow-2xl overflow-hidden bg-white border border-gray-200">
                        {{-- Browser mockup header --}}
                        <div class="flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-3 bg-gray-100 border-b border-gray-200 min-w-0">
                            <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-red-400 shrink-0"></div>
                            <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-yellow-400 shrink-0"></div>
                            <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-green-400 shrink-0"></div>
                            <div class="min-w-0 flex-1 mx-1 sm:mx-4">
                                <div class="bg-white rounded-md px-2 sm:px-3 py-1 text-[10px] sm:text-sm text-gray-400 text-center truncate">
                                    app.controclinic.com/demo
                                </div>
                            </div>
                        </div>
                        {{-- Dashboard preview --}}
                        <div class="p-3 sm:p-4 bg-gray-50">
                            <div class="space-y-3 sm:space-y-4">
                                {{-- Top stats --}}
                                <div class="grid grid-cols-3 gap-2 sm:gap-3">
                                    <div class="bg-white rounded-lg p-2 sm:p-3 shadow-sm min-w-0">
                                        <p class="text-[10px] sm:text-xs text-gray-500 truncate">Citas Hoy</p>
                                        <p class="text-lg sm:text-2xl font-bold text-gray-900">12</p>
                                    </div>
                                    <div class="bg-white rounded-lg p-2 sm:p-3 shadow-sm min-w-0">
                                        <p class="text-[10px] sm:text-xs text-gray-500 truncate">Pacientes</p>
                                        <p class="text-lg sm:text-2xl font-bold text-gray-900">248</p>
                                    </div>
                                    <div class="bg-white rounded-lg p-2 sm:p-3 shadow-sm min-w-0">
                                        <p class="text-[10px] sm:text-xs text-gray-500 truncate">Este Mes</p>
                                        <p class="text-lg sm:text-2xl font-bold text-indigo-600">$4,250</p>
                                    </div>
                                </div>
                                {{-- Calendar preview --}}
                                <div class="bg-white rounded-lg p-2 sm:p-3 shadow-sm">
                                    <div class="flex items-center justify-between gap-2 mb-3 min-w-0">
                                        <p class="text-xs sm:text-sm font-semibold text-gray-900 truncate">Próximas Citas</p>
                                        <span class="text-[10px] sm:text-xs text-indigo-600 shrink-0">Ver todas →</span>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 sm:gap-3 p-2 bg-indigo-50 rounded-lg min-w-0">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-indigo-200 rounded-full flex items-center justify-center text-indigo-700 font-bold text-xs sm:text-sm shrink-0">MR</div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs sm:text-sm font-medium text-gray-900 truncate">María Rodríguez</p>
                                                <p class="text-[10px] sm:text-xs text-gray-500 truncate">Consulta General • 9:00 AM</p>
                                            </div>
                                            <span class="hidden sm:inline-flex px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full shrink-0">Confirmada</span>
                                        </div>
                                        <div class="flex items-center gap-2 sm:gap-3 p-2 bg-gray-50 rounded-lg min-w-0">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-600 font-bold text-xs sm:text-sm shrink-0">JL</div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs sm:text-sm font-medium text-gray-900 truncate">Juan López</p>
                                                <p class="text-[10px] sm:text-xs text-gray-500 truncate">Seguimiento • 10:30 AM</p>
                                            </div>
                                            <span class="hidden sm:inline-flex px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-full shrink-0">Pendiente</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Floating elements (desktop only — avoids horizontal bleed on mobile) --}}
                    <div class="hidden sm:block absolute -bottom-6 -left-6 bg-white rounded-xl shadow-lg p-4 border border-gray-100 animate-bounce" style="animation-duration: 3s;">
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

    {{-- Features Section (v1) --}}
    <section id="features" class="py-20 lg:py-32 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">
                    {{ __('public.home_features_title') }}
                </h2>
                <p class="mt-4 text-xl text-gray-600">
                    {{ __('public.home_features_subtitle') }}
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="group relative bg-white rounded-2xl p-8 border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 transition-colors">
                        <svg class="w-7 h-7 text-indigo-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ __('public.home_feat_calendar_title') }}</h3>
                    <p class="text-gray-600">{{ __('public.home_feat_calendar_body') }}</p>
                </div>

                <div class="group relative bg-white rounded-2xl p-8 border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-purple-600 transition-colors">
                        <svg class="w-7 h-7 text-purple-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ __('public.home_feat_patients_title') }}</h3>
                    <p class="text-gray-600">{{ __('public.home_feat_patients_body') }}</p>
                </div>

                <div class="group relative bg-white rounded-2xl p-8 border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-red-600 transition-colors">
                        <svg class="w-7 h-7 text-red-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ __('public.home_feat_records_title') }}</h3>
                    <p class="text-gray-600">{{ __('public.home_feat_records_body') }}</p>
                </div>

                <div class="group relative bg-white rounded-2xl p-8 border border-gray-200 hover:border-indigo-200 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-6 group-hover:bg-green-600 transition-colors">
                        <svg class="w-7 h-7 text-green-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ __('public.home_feat_prescriptions_title') }}</h3>
                    <p class="text-gray-600">{{ __('public.home_feat_prescriptions_body') }}</p>
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

    {{-- CTA Section --}}
    <section class="py-20 lg:py-32 bg-gradient-to-br from-indigo-600 to-indigo-800 relative">
        {{-- Background decoration --}}
        <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
            <div class="absolute top-0 left-0 w-72 h-72 bg-indigo-500 rounded-full blur-3xl opacity-30 -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-72 sm:w-96 h-72 sm:h-96 bg-purple-500 rounded-full blur-3xl opacity-20 translate-x-1/3 translate-y-1/3"></div>
        </div>

        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-6">
                {{ __('public.home_cta_title') }}
            </h2>
            <p class="text-xl text-indigo-100 mb-10 max-w-2xl mx-auto">
                {{ __('public.home_cta_body') }}
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white hover:bg-gray-100 text-indigo-600 text-lg font-semibold rounded-xl shadow-lg transition-all">
                    {{ __('public.home_cta_button') }}
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="{{ route('pricing') }}" class="inline-flex items-center justify-center px-8 py-4 bg-transparent hover:bg-indigo-500 text-white text-lg font-semibold rounded-xl border-2 border-white/30 hover:border-transparent transition-all">
                    {{ __('public.home_cta_pricing') }}
                </a>
            </div>
            <p class="mt-6 text-indigo-200 text-sm">
                {{ __('public.freemium_subline') }}
            </p>
        </div>
    </section>
</x-public-layout>
