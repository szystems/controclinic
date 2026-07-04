@props(['variant' => 'public'])

@php
    $appName = app_setting('branding.app_name', 'ControClinic');
    $logoUrl = app_setting('branding.logo_url');
    $supportEmail = app_setting('legal.support_email', 'soporte@controclinic.com');
    $termsUrl = app_setting('legal.terms_url', '/terms');
    $privacyUrl = app_setting('legal.privacy_url', '/privacy');
@endphp

@if ($variant === 'app')
    <footer class="border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-center text-xs text-gray-500 dark:text-gray-400">
                {{ __('public.app_footer_brand', ['app' => $appName]) }}
            </p>
        </div>
    </footer>
@else
    <footer class="bg-gray-900 text-gray-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="col-span-2 md:col-span-1">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2 mb-4">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-8 w-auto brightness-0 invert" />
                        @else
                            <svg class="w-8 h-8 text-indigo-500" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="32" height="32" rx="8" fill="currentColor"/>
                                <path d="M16 8v16M8 16h16" stroke="white" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            <span class="text-xl font-bold text-white">{{ $appName }}</span>
                        @endif
                    </a>
                    <p class="text-sm">{{ __('public.footer_tagline') }}</p>
                    <p class="text-xs text-gray-500 mt-3">{{ __('public.footer_brand_line', ['app' => $appName]) }}</p>
                </div>

                <div>
                    <h4 class="text-white font-semibold mb-4">{{ __('public.footer_product') }}</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('home') }}#features" class="hover:text-white transition-colors">{{ __('public.footer_features') }}</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-white transition-colors">{{ __('public.footer_pricing') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('public.footer_integrations') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('public.footer_updates') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-semibold mb-4">{{ __('public.footer_resources') }}</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('public.footer_help') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('public.footer_guides') }}</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">{{ __('public.footer_blog') }}</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition-colors">{{ __('public.footer_contact') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-semibold mb-4">{{ __('public.footer_legal') }}</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ $privacyUrl }}" class="hover:text-white transition-colors">{{ __('public.footer_privacy') }}</a></li>
                        <li><a href="{{ $termsUrl }}" class="hover:text-white transition-colors">{{ __('public.footer_terms') }}</a></li>
                        <li><a href="mailto:{{ $supportEmail }}" class="hover:text-white transition-colors">{{ $supportEmail }}</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-gray-800 text-center md:text-left">
                <p class="text-sm">
                    {{ __('public.footer_copyright', ['year' => date('Y'), 'app' => $appName]) }}
                </p>
            </div>
        </div>
    </footer>
@endif
