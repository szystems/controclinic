<x-public-layout>
    @php
        $title = 'Contacto';
        $description = 'Ponte en contacto con nosotros. Estamos aquí para ayudarte.';
    @endphp

    <section class="pt-32 pb-20 lg:pt-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16">
                {{-- Contact Info --}}
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-6">
                        ¿Cómo podemos ayudarte?
                    </h1>
                    <p class="text-xl text-gray-600 mb-10">
                        Ya sea que tengas preguntas sobre nuestros planes, necesites soporte técnico o quieras agendar una demo, estamos aquí para ti.
                    </p>

                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Email</h3>
                                <p class="text-gray-600">soporte@controclinic.com</p>
                                <p class="text-sm text-gray-500 mt-1">Respondemos en menos de 24 horas</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Chat en vivo</h3>
                                <p class="text-gray-600">Lunes a Viernes, 9am - 6pm</p>
                                <p class="text-sm text-gray-500 mt-1">Horario de Ciudad de México (GMT-6)</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Demo personalizada</h3>
                                <p class="text-gray-600">Agenda una videollamada</p>
                                <p class="text-sm text-gray-500 mt-1">Te mostramos cómo funciona en 15 minutos</p>
                            </div>
                        </div>
                    </div>

                    {{-- FAQ Link --}}
                    <div class="mt-10 p-6 bg-gray-50 rounded-2xl">
                        <h3 class="font-semibold text-gray-900 mb-2">¿Buscas respuestas rápidas?</h3>
                        <p class="text-gray-600 mb-4">Revisa nuestra sección de preguntas frecuentes.</p>
                        <a href="{{ route('pricing') }}#faq" class="text-indigo-600 font-medium hover:text-indigo-700">
                            Ver FAQ →
                        </a>
                    </div>
                </div>

                {{-- Contact Form --}}
                <div>
                    <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Envíanos un mensaje</h2>

                        <form action="#" method="POST" class="space-y-6">
                            @csrf
                            <div class="grid sm:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nombre
                                    </label>
                                    <input type="text" id="name" name="name" required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                           placeholder="Tu nombre">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                        Email
                                    </label>
                                    <input type="email" id="email" name="email" required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                           placeholder="tu@email.com">
                                </div>
                            </div>

                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                                    Asunto
                                </label>
                                <select id="subject" name="subject"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                    <option value="">Selecciona un asunto</option>
                                    <option value="sales" {{ request('subject') == 'enterprise' ? 'selected' : '' }}>Ventas / Precios</option>
                                    <option value="support">Soporte técnico</option>
                                    <option value="demo">Solicitar demo</option>
                                    <option value="partnership">Alianzas / Partnership</option>
                                    <option value="other">Otro</option>
                                </select>
                            </div>

                            <div>
                                <label for="clinic_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre de la clínica <span class="text-gray-400">(opcional)</span>
                                </label>
                                <input type="text" id="clinic_name" name="clinic_name"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                       placeholder="Mi Clínica">
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                                    Mensaje
                                </label>
                                <textarea id="message" name="message" rows="5" required
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors resize-none"
                                          placeholder="¿En qué podemos ayudarte?"></textarea>
                            </div>

                            <button type="submit"
                                    class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors">
                                Enviar mensaje
                            </button>

                            <p class="text-sm text-gray-500 text-center">
                                Al enviar, aceptas nuestra
                                <a href="#" class="text-indigo-600 hover:underline">política de privacidad</a>.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-public-layout>
