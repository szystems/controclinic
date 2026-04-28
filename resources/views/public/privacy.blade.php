<x-public-layout>
    <section class="py-20 bg-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">{{ __('legal.privacy.title') }}</h1>
            <p class="text-sm text-gray-500 mb-10">{{ __('legal.last_updated') }}: 28 abr 2026</p>

            <div class="prose prose-indigo max-w-none">
                <p class="text-gray-600">{{ __('legal.privacy.intro') }}</p>

                <h2>{{ __('legal.privacy.s1_title') }}</h2>
                <p>{{ __('legal.privacy.s1_body') }}</p>

                <h2>{{ __('legal.privacy.s2_title') }}</h2>
                <p>{{ __('legal.privacy.s2_body') }}</p>

                <h2>{{ __('legal.privacy.s3_title') }}</h2>
                <p>{{ __('legal.privacy.s3_body') }}</p>

                <h2>{{ __('legal.privacy.s4_title') }}</h2>
                <p>{{ __('legal.privacy.s4_body') }}</p>

                <h2>{{ __('legal.privacy.s5_title') }}</h2>
                <p>{{ __('legal.privacy.s5_body') }}</p>

                <p class="mt-12 text-sm text-gray-500">{{ __('legal.contact_us') }} <a href="{{ route('contact') }}" class="text-indigo-600 hover:underline">{{ __('legal.contact_link') }}</a>.</p>
            </div>
        </div>
    </section>
</x-public-layout>
