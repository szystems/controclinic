<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('admin.platform_settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Notificación flash --}}
            <x-action-message class="text-green-600 dark:text-green-400 text-sm font-medium" on="notify" />

            {{-- ================================================================
                 BRANDING
            ================================================================ --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('admin.branding') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('admin.branding_description') }}
                    </p>
                </div>

                <form wire:submit="saveBranding" class="p-6 space-y-4">
                    {{-- App Name --}}
                    <div>
                        <x-input-label for="branding_app_name" :value="__('admin.app_name')" />
                        <x-text-input
                            id="branding_app_name"
                            wire:model="branding_app_name"
                            type="text"
                            class="mt-1 block w-full"
                            maxlength="60"
                        />
                        <x-input-error :messages="$errors->get('branding_app_name')" class="mt-2" />
                    </div>

                    {{-- Logo URL --}}
                    <div>
                        <x-input-label for="branding_logo_url" :value="__('admin.logo_url')" />
                        <x-text-input
                            id="branding_logo_url"
                            wire:model="branding_logo_url"
                            type="url"
                            class="mt-1 block w-full"
                            placeholder="https://..."
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('admin.logo_url_help') }}</p>
                        <x-input-error :messages="$errors->get('branding_logo_url')" class="mt-2" />
                    </div>

                    {{-- Primary Color --}}
                    <div>
                        <x-input-label for="branding_primary_color" :value="__('admin.primary_color')" />
                        <div class="mt-1 flex items-center gap-3">
                            <input
                                id="branding_primary_color_picker"
                                type="color"
                                wire:model="branding_primary_color"
                                class="h-10 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                            />
                            <x-text-input
                                id="branding_primary_color"
                                wire:model="branding_primary_color"
                                type="text"
                                class="block w-36"
                                placeholder="#2563eb"
                                maxlength="7"
                            />
                        </div>
                        <x-input-error :messages="$errors->get('branding_primary_color')" class="mt-2" />
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-primary-button wire:loading.attr="disabled">
                            <span wire:loading wire:target="saveBranding" class="mr-2">
                                <svg class="animate-spin h-4 w-4 inline" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 0v4a8 8 0 00-8 8H0z"></path>
                                </svg>
                            </span>
                            {{ __('admin.save_branding') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>

            {{-- ================================================================
                 LEGAL
            ================================================================ --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('admin.legal') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('admin.legal_description') }}
                    </p>
                </div>

                <form wire:submit="saveLegal" class="p-6 space-y-4">
                    <div>
                        <x-input-label for="legal_terms_url" :value="__('admin.terms_url')" />
                        <x-text-input
                            id="legal_terms_url"
                            wire:model="legal_terms_url"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="/terms"
                        />
                        <x-input-error :messages="$errors->get('legal_terms_url')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="legal_privacy_url" :value="__('admin.privacy_url')" />
                        <x-text-input
                            id="legal_privacy_url"
                            wire:model="legal_privacy_url"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="/privacy"
                        />
                        <x-input-error :messages="$errors->get('legal_privacy_url')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="legal_support_email" :value="__('admin.support_email')" />
                        <x-text-input
                            id="legal_support_email"
                            wire:model="legal_support_email"
                            type="email"
                            class="mt-1 block w-full"
                            placeholder="soporte@controclinic.com"
                        />
                        <x-input-error :messages="$errors->get('legal_support_email')" class="mt-2" />
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-primary-button wire:loading.attr="disabled">
                            <span wire:loading wire:target="saveLegal" class="mr-2">
                                <svg class="animate-spin h-4 w-4 inline" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 0v4a8 8 0 00-8 8H0z"></path>
                                </svg>
                            </span>
                            {{ __('admin.save_legal') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>

            {{-- ================================================================
                 DEFAULTS
            ================================================================ --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('admin.defaults') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('admin.defaults_description') }}
                    </p>
                </div>

                <form wire:submit="saveDefaults" class="p-6 space-y-4">
                    {{-- Locale --}}
                    <div>
                        <x-input-label for="defaults_locale" :value="__('admin.default_locale')" />
                        <select
                            id="defaults_locale"
                            wire:model="defaults_locale"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        >
                            <option value="es">Español</option>
                            <option value="en">English</option>
                        </select>
                        <x-input-error :messages="$errors->get('defaults_locale')" class="mt-2" />
                    </div>

                    {{-- Timezone --}}
                    <div>
                        <x-input-label for="defaults_timezone" :value="__('admin.default_timezone')" />
                        <x-text-input
                            id="defaults_timezone"
                            wire:model="defaults_timezone"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="America/Bogota"
                        />
                        <x-input-error :messages="$errors->get('defaults_timezone')" class="mt-2" />
                    </div>

                    {{-- Currency --}}
                    <div>
                        <x-input-label for="defaults_currency" :value="__('admin.default_currency')" />
                        <x-text-input
                            id="defaults_currency"
                            wire:model="defaults_currency"
                            type="text"
                            class="mt-1 block w-full uppercase"
                            placeholder="USD"
                            maxlength="3"
                        />
                        <x-input-error :messages="$errors->get('defaults_currency')" class="mt-2" />
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-primary-button wire:loading.attr="disabled">
                            <span wire:loading wire:target="saveDefaults" class="mr-2">
                                <svg class="animate-spin h-4 w-4 inline" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 0v4a8 8 0 00-8 8H0z"></path>
                                </svg>
                            </span>
                            {{ __('admin.save_defaults') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>

            {{-- ================================================================
                 FEATURE FLAGS
            ================================================================ --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('admin.feature_flags') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('admin.feature_flags_description') }}
                    </p>
                </div>

                <form wire:submit="saveFeatures" class="p-6 space-y-4">
                    @foreach([
                        ['field' => 'features_registration_open',     'label' => 'registration_open',     'desc' => 'registration_open_description'],
                        ['field' => 'features_portal_enabled',        'label' => 'portal_enabled',        'desc' => 'portal_enabled_description'],
                        ['field' => 'features_telemedicine_enabled',  'label' => 'telemedicine_enabled',  'desc' => 'telemedicine_enabled_description'],
                        ['field' => 'features_ai_enabled',            'label' => 'ai_enabled',            'desc' => 'ai_enabled_description'],
                        ['field' => 'features_maintenance_mode',      'label' => 'maintenance_mode',      'desc' => 'maintenance_mode_description'],
                    ] as $toggle)
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ __('admin.' . $toggle['label']) }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ __('admin.' . $toggle['desc']) }}
                                </p>
                            </div>
                            <label
                                x-data="{ on: @entangle($toggle['field']) }"
                                class="relative inline-flex items-center cursor-pointer ml-4 flex-shrink-0"
                            >
                                <input
                                    type="checkbox"
                                    wire:model="{{ $toggle['field'] }}"
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 dark:peer-focus:ring-indigo-600 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                    @endforeach

                    <div class="flex justify-end pt-2">
                        <x-primary-button wire:loading.attr="disabled">
                            <span wire:loading wire:target="saveFeatures" class="mr-2">
                                <svg class="animate-spin h-4 w-4 inline" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 0v4a8 8 0 00-8 8H0z"></path>
                                </svg>
                            </span>
                            {{ __('admin.save_features') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
