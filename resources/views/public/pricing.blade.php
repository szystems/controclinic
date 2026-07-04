<x-public-layout>
    @php
        $title = __('public.nav_pricing');
        $description = __('public.pricing_subtitle');
    @endphp

    {{-- Hero Section --}}
    <section class="pt-32 pb-16 lg:pt-40 bg-gradient-to-b from-indigo-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6">
                {{ __('public.pricing_title') }}
            </h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                {{ __('public.pricing_subtitle') }}
            </p>

            {{-- Billing Toggle --}}
            <div x-data="{ annual: true }" class="mt-10">
                <div class="inline-flex items-center bg-gray-100 rounded-full p-1">
                    <button @click="annual = false"
                            :class="annual ? 'text-gray-600' : 'bg-white text-gray-900 shadow'"
                            class="px-6 py-2 rounded-full text-sm font-medium transition-all">
                        {{ __('billing.monthly') }}
                    </button>
                    <button @click="annual = true"
                            :class="annual ? 'bg-white text-gray-900 shadow' : 'text-gray-600'"
                            class="px-6 py-2 rounded-full text-sm font-medium transition-all">
                        {{ __('billing.yearly') }}
                        <span class="ml-1 text-xs text-green-600 font-semibold">-20%</span>
                    </button>
                </div>

                {{-- Pricing Cards (Dynamic from DB) --}}
                <div class="mt-12 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-{{ max(1, min(4, $plans->count())) }} gap-6 lg:gap-8 max-w-7xl mx-auto min-w-0">
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

    {{-- Custom plan (replaces Enterprise card on public pricing) --}}
    <section class="py-16 bg-gray-50 border-y border-gray-100">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-3">{{ __('public.custom_plan_title') }}</h2>
            <p class="text-gray-600 mb-6">{{ __('public.custom_plan_body') }}</p>
            <a href="{{ route('contact', ['subject' => 'enterprise']) }}"
               class="inline-flex items-center justify-center px-6 py-3 bg-gray-900 hover:bg-gray-800 text-white font-semibold rounded-xl transition">
                {{ __('public.custom_plan_cta') }}
            </a>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section id="faq" class="py-20 bg-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-12">
                {{ __('public.pricing_faq_title') }}
            </h2>

            <div x-data="{ open: null }" class="space-y-4">
                @php
                    $faqs = [
                        ['q' => __('public.faq_change_plan_q'), 'a' => __('public.faq_change_plan_a')],
                        ['q' => __('public.faq_free_plan_q'), 'a' => __('public.faq_free_plan_a')],
                        ['q' => __('public.faq_taxes_q'), 'a' => __('public.faq_taxes_a')],
                        ['q' => __('public.faq_students_q'), 'a' => __('public.faq_students_a')],
                        ['q' => __('public.faq_cancel_q'), 'a' => __('public.faq_cancel_a')],
                        ['q' => __('public.faq_payment_q'), 'a' => __('public.faq_payment_a')],
                    ];
                @endphp

                @foreach($faqs as $index => $faq)
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <button @click="open = open === {{ $index }} ? null : {{ $index }}"
                                class="w-full flex items-center justify-between gap-3 p-4 sm:p-6 text-left">
                            <span class="font-medium text-gray-900 min-w-0 flex-1">{{ $faq['q'] }}</span>
                            <svg class="w-5 h-5 text-gray-500 shrink-0 transition-transform"
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
                {{ __('public.pricing_cta_title') }}
            </h2>
            <p class="text-xl text-indigo-100 mb-8">
                {{ __('public.pricing_cta_body') }}
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('contact') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white hover:bg-gray-100 text-indigo-600 font-semibold rounded-xl transition-all">
                    {{ __('public.pricing_cta_contact') }}
                </a>
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 bg-transparent hover:bg-indigo-500 text-white font-semibold rounded-xl border-2 border-white/30 transition-all">
                    {{ __('public.pricing_cta_register') }}
                </a>
            </div>
        </div>
    </section>
</x-public-layout>
