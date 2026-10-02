<x-public-layout>
    @php
        $title = __('public.contact_title');
        $description = __('public.contact_description');
    @endphp

    <section class="pt-32 pb-20 lg:pt-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 min-w-0">
                {{-- Contact Info --}}
                <div class="min-w-0">
                    <h1 class="text-4xl font-bold text-gray-900 mb-6">
                        {{ __('public.contact_heading') }}
                    </h1>
                    <p class="text-xl text-gray-600 mb-10">
                        {{ __('public.contact_intro') }}
                    </p>

                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ __('public.contact_email_title') }}</h3>
                                <p class="text-gray-600">{{ __('public.contact_email_address') }}</p>
                                <p class="text-sm text-gray-500 mt-1">{{ __('public.contact_email_note') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ __('public.contact_chat_title') }}</h3>
                                <p class="text-gray-600">{{ __('public.contact_chat_hours') }}</p>
                                <p class="text-sm text-gray-500 mt-1">{{ __('public.contact_chat_note') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ __('public.contact_demo_title') }}</h3>
                                <p class="text-gray-600">{{ __('public.contact_demo_body') }}</p>
                                <p class="text-sm text-gray-500 mt-1">{{ __('public.contact_demo_note') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- FAQ Link --}}
                    <div class="mt-10 p-6 bg-gray-50 rounded-2xl">
                        <h3 class="font-semibold text-gray-900 mb-2">{{ __('public.contact_faq_title') }}</h3>
                        <p class="text-gray-600 mb-4">{{ __('public.contact_faq_body') }}</p>
                        <a href="{{ route('pricing') }}#faq" class="text-indigo-600 font-medium hover:text-indigo-700">
                            {{ __('public.contact_faq_link') }}
                        </a>
                    </div>
                </div>

                {{-- Contact Form --}}
                <div>
                    <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('public.contact_form_title') }}</h2>

                        <form action="#" method="POST" class="space-y-6">
                            @csrf
                            <div class="grid sm:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('public.contact_name') }}
                                    </label>
                                    <input type="text" id="name" name="name" required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                           placeholder="{{ __('public.contact_name_placeholder') }}">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('public.contact_email') }}
                                    </label>
                                    <input type="email" id="email" name="email" required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                           placeholder="{{ __('public.contact_email_placeholder') }}">
                                </div>
                            </div>

                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('public.contact_subject') }}
                                </label>
                                <select id="subject" name="subject"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                    <option value="">{{ __('public.contact_subject_placeholder') }}</option>
                                    <option value="sales" {{ request('subject') == 'enterprise' ? 'selected' : '' }}>{{ __('public.contact_subject_sales') }}</option>
                                    <option value="support">{{ __('public.contact_subject_support') }}</option>
                                    <option value="demo">{{ __('public.contact_subject_demo') }}</option>
                                    <option value="partnership">{{ __('public.contact_subject_partnership') }}</option>
                                    <option value="other">{{ __('public.contact_subject_other') }}</option>
                                </select>
                            </div>

                            <div>
                                <label for="clinic_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('public.contact_clinic') }} <span class="text-gray-400">{{ __('public.contact_clinic_optional') }}</span>
                                </label>
                                <input type="text" id="clinic_name" name="clinic_name"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                       placeholder="{{ __('public.contact_clinic_placeholder') }}">
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('public.contact_message') }}
                                </label>
                                <textarea id="message" name="message" rows="5" required
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors resize-none"
                                          placeholder="{{ __('public.contact_message_placeholder') }}"></textarea>
                            </div>

                            <button type="submit"
                                    class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors">
                                {{ __('public.contact_submit') }}
                            </button>

                            <p class="text-sm text-gray-500 text-center">
                                {{ __('public.contact_privacy_notice') }}
                                <a href="{{ route('privacy') }}" class="text-indigo-600 hover:underline">{{ __('public.contact_privacy_link') }}</a>.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-public-layout>
