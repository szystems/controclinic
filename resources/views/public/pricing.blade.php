<x-public-layout>
    @php
        $title = 'Precios';
        $description = 'Planes flexibles para clínicas de todos los tamaños. Desde consultorios individuales hasta hospitales.';
    @endphp

    {{-- Hero Section --}}
    <section class="pt-32 pb-16 lg:pt-40 bg-gradient-to-b from-indigo-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6">
                Planes simples, precios transparentes
            </h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Elige el plan que mejor se adapte a tu clínica. Todos incluyen 14 días de prueba gratis.
            </p>

            {{-- Billing Toggle --}}
            <div x-data="{ annual: true }" class="mt-10">
                <div class="inline-flex items-center bg-gray-100 rounded-full p-1">
                    <button @click="annual = false"
                            :class="annual ? 'text-gray-600' : 'bg-white text-gray-900 shadow'"
                            class="px-6 py-2 rounded-full text-sm font-medium transition-all">
                        Mensual
                    </button>
                    <button @click="annual = true"
                            :class="annual ? 'bg-white text-gray-900 shadow' : 'text-gray-600'"
                            class="px-6 py-2 rounded-full text-sm font-medium transition-all">
                        Anual
                        <span class="ml-1 text-xs text-green-600 font-semibold">-20%</span>
                    </button>
                </div>

                {{-- Pricing Cards (Dynamic from DB) --}}
                <div class="mt-12 grid lg:grid-cols-{{ $plans->count() }} gap-8 max-w-7xl mx-auto">
                    @foreach ($plans as $plan)
                        <x-plan-card :plan="$plan" context="pricing" />
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Features Comparison Table (Dynamic from DB) --}}
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-12">
                {{ __('features.comparison_title') }}
            </h2>
            <x-plan-comparison :plans="$plans" />
        </div>
    </section>

    {{-- Add-ons Section --}}
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-2xl font-bold text-gray-900">Add-ons opcionales</h2>
                <p class="text-gray-600 mt-2">Amplía tu plan según tus necesidades</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900">Doctor adicional</h3>
                    <p class="text-2xl font-bold text-gray-900 mt-2">+$15<span class="text-sm text-gray-500 font-normal">/mes</span></p>
                </div>

                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900">Asistente adicional</h3>
                    <p class="text-2xl font-bold text-gray-900 mt-2">+$5<span class="text-sm text-gray-500 font-normal">/mes</span></p>
                </div>

                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900">SMS/WhatsApp</h3>
                    <p class="text-2xl font-bold text-gray-900 mt-2">+$10<span class="text-sm text-gray-500 font-normal">/mes</span></p>
                </div>

                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900">Dominio personalizado</h3>
                    <p class="text-2xl font-bold text-gray-900 mt-2">+$25<span class="text-sm text-gray-500 font-normal">/mes</span></p>
                </div>
            </div>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section class="py-20 bg-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-12">
                Preguntas frecuentes
            </h2>

            <div x-data="{ open: null }" class="space-y-4">
                @php
                    $faqs = [
                        [
                            'q' => '¿Puedo cambiar de plan en cualquier momento?',
                            'a' => 'Sí, puedes actualizar o degradar tu plan cuando quieras. Los cambios se aplican inmediatamente y el cobro se prorratea según los días restantes del período.'
                        ],
                        [
                            'q' => '¿Qué pasa cuando termina mi prueba gratuita?',
                            'a' => 'Después de 14 días, puedes continuar con el plan Free (con límites) o elegir un plan de pago. No perderás ningún dato.'
                        ],
                        [
                            'q' => '¿Los precios incluyen impuestos?',
                            'a' => 'Los precios mostrados no incluyen impuestos locales. El impuesto aplicable se calculará según tu ubicación al momento del pago.'
                        ],
                        [
                            'q' => '¿Ofrecen descuentos para estudiantes?',
                            'a' => 'Sí, ofrecemos 50% de descuento para estudiantes de medicina con correo institucional válido. Contáctanos para más información.'
                        ],
                        [
                            'q' => '¿Cómo funciona la cancelación?',
                            'a' => 'Puedes cancelar tu suscripción en cualquier momento desde tu panel de configuración. Seguirás teniendo acceso hasta el final del período pagado.'
                        ],
                        [
                            'q' => '¿Qué métodos de pago aceptan?',
                            'a' => 'Aceptamos tarjetas de crédito y débito (Visa, Mastercard, American Express) a través de Paddle, nuestra plataforma segura de pagos.'
                        ],
                    ];
                @endphp

                @foreach($faqs as $index => $faq)
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <button @click="open = open === {{ $index }} ? null : {{ $index }}"
                                class="w-full flex items-center justify-between p-6 text-left">
                            <span class="font-medium text-gray-900">{{ $faq['q'] }}</span>
                            <svg class="w-5 h-5 text-gray-500 transition-transform"
                                 :class="open === {{ $index }} ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open === {{ $index }}"
                             x-collapse
                             class="px-6 pb-6 text-gray-600">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-20 bg-indigo-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">
                ¿Tienes más preguntas?
            </h2>
            <p class="text-xl text-indigo-100 mb-8">
                Nuestro equipo está listo para ayudarte a elegir el plan perfecto para tu clínica.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('contact') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white hover:bg-gray-100 text-indigo-600 font-semibold rounded-xl transition-all">
                    Contactar Ventas
                </a>
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 bg-transparent hover:bg-indigo-500 text-white font-semibold rounded-xl border-2 border-white/30 transition-all">
                    Comenzar Prueba Gratis
                </a>
            </div>
        </div>
    </section>
</x-public-layout>
