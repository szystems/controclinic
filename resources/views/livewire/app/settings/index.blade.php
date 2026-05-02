<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('settings.title') }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('settings.subtitle') }}
            </p>
        </div>

        {{-- Flash Messages --}}
        @if (session()->has('success'))
        <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/50 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm font-medium text-green-800 dark:text-green-200">
                    {{ session('success') }}
                </p>
            </div>
        </div>
        @endif

        {{-- Mobile: Horizontal Tabs with scroll --}}
        <div class="lg:hidden mb-4">
            <nav class="flex space-x-1 overflow-x-auto pb-2 scrollbar-hide">
                <button wire:click="setTab('general')" class="flex-shrink-0 px-3 py-2 text-sm font-medium rounded-lg {{ $activeTab === 'general' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700' }}">
                    General
                </button>
                <button wire:click="setTab('localization')" class="flex-shrink-0 px-3 py-2 text-sm font-medium rounded-lg {{ $activeTab === 'localization' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700' }}">
                    Localización
                </button>
                <button wire:click="setTab('appointments')" class="flex-shrink-0 px-3 py-2 text-sm font-medium rounded-lg {{ $activeTab === 'appointments' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700' }}">
                    Citas
                </button>
                <button wire:click="setTab('notifications')" class="flex-shrink-0 px-3 py-2 text-sm font-medium rounded-lg {{ $activeTab === 'notifications' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700' }}">
                    Notificaciones
                </button>
                <button wire:click="setTab('billing')" class="flex-shrink-0 px-3 py-2 text-sm font-medium rounded-lg {{ $activeTab === 'billing' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700' }}">
                    Facturación
                </button>
                <button wire:click="setTab('branding')" class="flex-shrink-0 px-3 py-2 text-sm font-medium rounded-lg {{ $activeTab === 'branding' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700' }}">
                    Marca
                </button>
                @if(auth()->id() === $clinic->owner_id)
                <button wire:click="setTab('data')" class="flex-shrink-0 px-3 py-2 text-sm font-medium rounded-lg {{ $activeTab === 'data' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700' }}">
                    {{ __('settings.data.tab') }}
                </button>
                @endif
            </nav>
        </div>

        <div class="flex gap-6">
            {{-- Desktop: Sidebar Tabs (hidden on mobile) --}}
            <div class="hidden lg:block w-48 flex-shrink-0">
                <nav class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden sticky top-6">
                    <button wire:click="setTab('general')"
                            class="w-full flex items-center px-3 py-2.5 text-left text-sm font-medium {{ $activeTab === 'general' ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border-l-4 border-indigo-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border-l-4 border-transparent' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        General
                    </button>
                    <button wire:click="setTab('localization')"
                            class="w-full flex items-center px-3 py-2.5 text-left text-sm font-medium {{ $activeTab === 'localization' ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border-l-4 border-indigo-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border-l-4 border-transparent' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Localización
                    </button>
                    <button wire:click="setTab('appointments')"
                            class="w-full flex items-center px-3 py-2.5 text-left text-sm font-medium {{ $activeTab === 'appointments' ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border-l-4 border-indigo-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border-l-4 border-transparent' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Citas
                    </button>
                    <button wire:click="setTab('notifications')"
                            class="w-full flex items-center px-3 py-2.5 text-left text-sm font-medium {{ $activeTab === 'notifications' ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border-l-4 border-indigo-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border-l-4 border-transparent' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Notificaciones
                    </button>
                    <button wire:click="setTab('billing')"
                            class="w-full flex items-center px-3 py-2.5 text-left text-sm font-medium {{ $activeTab === 'billing' ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border-l-4 border-indigo-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border-l-4 border-transparent' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                        </svg>
                        Facturación
                    </button>
                    <button wire:click="setTab('branding')"
                            class="w-full flex items-center px-3 py-2.5 text-left text-sm font-medium {{ $activeTab === 'branding' ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border-l-4 border-indigo-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border-l-4 border-transparent' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                        Marca
                    </button>
                    @if(auth()->id() === $clinic->owner_id)
                    <button wire:click="setTab('data')"
                            class="w-full flex items-center px-3 py-2.5 text-left text-sm font-medium {{ $activeTab === 'data' ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border-l-4 border-indigo-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border-l-4 border-transparent' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        {{ __('settings.data.tab') }}
                    </button>
                    @endif
                </nav>
            </div>

            {{-- Content Area --}}
            <div class="flex-1 min-w-0">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">

                    {{-- General Tab --}}
                    @if($activeTab === 'general')
                    <form wire:submit="saveGeneral">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('settings.general.title') }}</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('settings.general.description') }}</p>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.general.name') }} *</label>
                                    <input type="text" wire:model="name" id="name" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.general.email') }} *</label>
                                    <input type="email" wire:model="email" id="email" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('email') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.general.phone') }}</label>
                                    <input type="text" wire:model="phone" id="phone" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('phone') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="website" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.general.website') }}</label>
                                    <input type="url" wire:model="website" id="website" placeholder="https://" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('website') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.general.address') }}</label>
                                <input type="text" wire:model="address" id="address" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('address') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.general.city') }}</label>
                                    <input type="text" wire:model="city" id="city" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('city') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.general.country') }}</label>
                                    <select wire:model="country" id="country" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="GT">Guatemala</option>
                                        <option value="MX">México</option>
                                        <option value="US">Estados Unidos</option>
                                        <option value="ES">España</option>
                                        <option value="CO">Colombia</option>
                                        <option value="AR">Argentina</option>
                                        <option value="CL">Chile</option>
                                        <option value="PE">Perú</option>
                                    </select>
                                    @error('country') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.general.description') }}</label>
                                <textarea wire:model="description" id="description" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('settings.general.description_placeholder') }}"></textarea>
                                @error('description') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                                <svg wire:loading wire:target="saveGeneral" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('general.save') }}
                            </button>
                        </div>
                    </form>
                    @endif

                    {{-- Localization Tab --}}
                    @if($activeTab === 'localization')
                    <form wire:submit="saveLocalization">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('settings.localization.title') }}</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('settings.localization.description') }}</p>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="locale" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.localization.language') }}</label>
                                    <select wire:model="locale" id="locale" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="es">Español</option>
                                        <option value="en">English</option>
                                    </select>
                                    @error('locale') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.localization.timezone') }}</label>
                                    <select wire:model="timezone" id="timezone" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <optgroup label="🇨🇦 Canadá">
                                            <option value="America/Vancouver">Vancouver / Victoria (GMT-8)</option>
                                            <option value="America/Edmonton">Edmonton / Calgary (GMT-7)</option>
                                            <option value="America/Winnipeg">Winnipeg (GMT-6)</option>
                                            <option value="America/Toronto">Toronto (GMT-5)</option>
                                            <option value="America/Halifax">Halifax (GMT-4)</option>
                                            <option value="America/St_Johns">St. John's (GMT-3:30)</option>
                                        </optgroup>
                                        <optgroup label="🇺🇸 Estados Unidos">
                                            <option value="America/Los_Angeles">Los Ángeles / Seattle (GMT-8)</option>
                                            <option value="America/Denver">Denver (GMT-7)</option>
                                            <option value="America/Phoenix">Phoenix (GMT-7)</option>
                                            <option value="America/Chicago">Chicago (GMT-6)</option>
                                            <option value="America/New_York">Nueva York (GMT-5)</option>
                                            <option value="Pacific/Honolulu">Hawái (GMT-10)</option>
                                            <option value="America/Anchorage">Alaska (GMT-9)</option>
                                        </optgroup>
                                        <optgroup label="🇲🇽 México">
                                            <option value="America/Mexico_City">Ciudad de México (GMT-6)</option>
                                            <option value="America/Tijuana">Tijuana (GMT-8)</option>
                                            <option value="America/Cancun">Cancún (GMT-5)</option>
                                            <option value="America/Hermosillo">Hermosillo (GMT-7)</option>
                                        </optgroup>
                                        <optgroup label="🌎 Centroamérica">
                                            <option value="America/Guatemala">Guatemala (GMT-6)</option>
                                            <option value="America/El_Salvador">El Salvador (GMT-6)</option>
                                            <option value="America/Tegucigalpa">Honduras (GMT-6)</option>
                                            <option value="America/Managua">Nicaragua (GMT-6)</option>
                                            <option value="America/Costa_Rica">Costa Rica (GMT-6)</option>
                                            <option value="America/Panama">Panamá (GMT-5)</option>
                                        </optgroup>
                                        <optgroup label="🌎 Caribe">
                                            <option value="America/Havana">Cuba (GMT-5)</option>
                                            <option value="America/Santo_Domingo">Rep. Dominicana (GMT-4)</option>
                                            <option value="America/Puerto_Rico">Puerto Rico (GMT-4)</option>
                                        </optgroup>
                                        <optgroup label="🌎 Sudamérica">
                                            <option value="America/Bogota">Colombia (GMT-5)</option>
                                            <option value="America/Lima">Perú (GMT-5)</option>
                                            <option value="America/Guayaquil">Ecuador (GMT-5)</option>
                                            <option value="America/Caracas">Venezuela (GMT-4)</option>
                                            <option value="America/La_Paz">Bolivia (GMT-4)</option>
                                            <option value="America/Santiago">Chile (GMT-3)</option>
                                            <option value="America/Argentina/Buenos_Aires">Argentina (GMT-3)</option>
                                            <option value="America/Sao_Paulo">Brasil - São Paulo (GMT-3)</option>
                                            <option value="America/Montevideo">Uruguay (GMT-3)</option>
                                            <option value="America/Asuncion">Paraguay (GMT-4)</option>
                                        </optgroup>
                                        <optgroup label="🇪🇺 Europa">
                                            <option value="Europe/Madrid">España (GMT+1)</option>
                                            <option value="Europe/London">Reino Unido (GMT+0)</option>
                                            <option value="Europe/Paris">Francia (GMT+1)</option>
                                            <option value="Europe/Berlin">Alemania (GMT+1)</option>
                                            <option value="Europe/Rome">Italia (GMT+1)</option>
                                            <option value="Europe/Lisbon">Portugal (GMT+0)</option>
                                        </optgroup>
                                    </select>
                                    @error('timezone') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.localization.currency') }}</label>
                                    <select wire:model="currency" id="currency" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <optgroup label="Norteamérica">
                                            <option value="USD">USD - Dólar estadounidense ($)</option>
                                            <option value="CAD">CAD - Dólar canadiense ($)</option>
                                            <option value="MXN">MXN - Peso mexicano ($)</option>
                                        </optgroup>
                                        <optgroup label="Centroamérica">
                                            <option value="GTQ">GTQ - Quetzal guatemalteco (Q)</option>
                                            <option value="HNL">HNL - Lempira hondureño (L)</option>
                                            <option value="NIO">NIO - Córdoba nicaragüense (C$)</option>
                                            <option value="CRC">CRC - Colón costarricense (₡)</option>
                                            <option value="PAB">PAB - Balboa panameño (B/.)</option>
                                        </optgroup>
                                        <optgroup label="Caribe">
                                            <option value="DOP">DOP - Peso dominicano (RD$)</option>
                                            <option value="CUP">CUP - Peso cubano ($)</option>
                                        </optgroup>
                                        <optgroup label="Sudamérica">
                                            <option value="COP">COP - Peso colombiano ($)</option>
                                            <option value="PEN">PEN - Sol peruano (S/)</option>
                                            <option value="CLP">CLP - Peso chileno ($)</option>
                                            <option value="ARS">ARS - Peso argentino ($)</option>
                                            <option value="VES">VES - Bolívar venezolano (Bs)</option>
                                            <option value="BOB">BOB - Boliviano (Bs)</option>
                                            <option value="PYG">PYG - Guaraní paraguayo (₲)</option>
                                            <option value="UYU">UYU - Peso uruguayo ($)</option>
                                            <option value="BRL">BRL - Real brasileño (R$)</option>
                                        </optgroup>
                                        <optgroup label="Europa">
                                            <option value="EUR">EUR - Euro (€)</option>
                                            <option value="GBP">GBP - Libra esterlina (£)</option>
                                        </optgroup>
                                    </select>
                                    @error('currency') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="date_format" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.localization.date_format') }}</label>
                                    <select wire:model="date_format" id="date_format" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="d/m/Y">DD/MM/AAAA (29/01/2026)</option>
                                        <option value="m/d/Y">MM/DD/AAAA (01/29/2026)</option>
                                        <option value="Y-m-d">AAAA-MM-DD (2026-01-29)</option>
                                        <option value="d-m-Y">DD-MM-AAAA (29-01-2026)</option>
                                    </select>
                                    @error('date_format') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="time_format" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.localization.time_format') }}</label>
                                    <select wire:model="time_format" id="time_format" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="24h">24 horas (14:30)</option>
                                        <option value="12h">12 horas (2:30 PM)</option>
                                    </select>
                                    @error('time_format') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="phone_country_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.localization.phone_country_code') }}</label>
                                    <select wire:model="phone_country_code" id="phone_country_code" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <optgroup label="🌎 Centroamérica">
                                            <option value="502">🇬🇹 +502 Guatemala</option>
                                            <option value="503">🇸🇻 +503 El Salvador</option>
                                            <option value="504">🇭🇳 +504 Honduras</option>
                                            <option value="505">🇳🇮 +505 Nicaragua</option>
                                            <option value="506">🇨🇷 +506 Costa Rica</option>
                                            <option value="507">🇵🇦 +507 Panamá</option>
                                        </optgroup>
                                        <optgroup label="🌎 Norteamérica">
                                            <option value="52">🇲🇽 +52 México</option>
                                            <option value="1">🇺🇸 +1 EE.UU. / Canadá</option>
                                        </optgroup>
                                        <optgroup label="🌎 Sudamérica">
                                            <option value="57">🇨🇴 +57 Colombia</option>
                                            <option value="51">🇵🇪 +51 Perú</option>
                                            <option value="56">🇨🇱 +56 Chile</option>
                                            <option value="54">🇦🇷 +54 Argentina</option>
                                            <option value="55">🇧🇷 +55 Brasil</option>
                                            <option value="58">🇻🇪 +58 Venezuela</option>
                                            <option value="591">🇧🇴 +591 Bolivia</option>
                                            <option value="595">🇵🇾 +595 Paraguay</option>
                                            <option value="598">🇺🇾 +598 Uruguay</option>
                                            <option value="593">🇪🇨 +593 Ecuador</option>
                                        </optgroup>
                                        <optgroup label="🌎 Caribe">
                                            <option value="1809">🇩🇴 +1809 Rep. Dominicana</option>
                                            <option value="53">🇨🇺 +53 Cuba</option>
                                        </optgroup>
                                        <optgroup label="🇪🇺 Europa">
                                            <option value="34">🇪🇸 +34 España</option>
                                            <option value="44">🇬🇧 +44 Reino Unido</option>
                                            <option value="33">🇫🇷 +33 Francia</option>
                                            <option value="49">🇩🇪 +49 Alemania</option>
                                        </optgroup>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('settings.localization.phone_country_code_hint') }}</p>
                                    @error('phone_country_code') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                                <svg wire:loading wire:target="saveLocalization" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('general.save') }}
                            </button>
                        </div>
                    </form>
                    @endif

                    {{-- Appointments Tab --}}
                    @if($activeTab === 'appointments')
                    <form wire:submit="saveAppointments">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('settings.appointments.title') }}</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('settings.appointments.description') }}</p>
                        </div>
                        <div class="p-6 space-y-6">
                            {{-- Working Hours --}}
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">{{ __('settings.appointments.working_hours') }}</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="working_hours_start" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.appointments.start_time') }}</label>
                                        <input type="time" wire:model="working_hours_start" id="working_hours_start" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @error('working_hours_start') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="working_hours_end" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.appointments.end_time') }}</label>
                                        <input type="time" wire:model="working_hours_end" id="working_hours_end" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @error('working_hours_end') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('settings.appointments.working_days') }}</label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach([0 => 'Dom', 1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb'] as $day => $label)
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" wire:model="working_days" value="{{ $day }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                    @error('working_days') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- Appointment Settings --}}
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">{{ __('settings.appointments.duration_settings') }}</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="appointment_duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.appointments.default_duration') }}</label>
                                        <select wire:model="appointment_duration" id="appointment_duration" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="15">15 minutos</option>
                                            <option value="20">20 minutos</option>
                                            <option value="30">30 minutos</option>
                                            <option value="45">45 minutos</option>
                                            <option value="60">1 hora</option>
                                            <option value="90">1 hora 30 minutos</option>
                                            <option value="120">2 horas</option>
                                        </select>
                                        @error('appointment_duration') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="appointment_buffer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.appointments.buffer_time') }}</label>
                                        <select wire:model="appointment_buffer" id="appointment_buffer" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="0">Sin tiempo buffer</option>
                                            <option value="5">5 minutos</option>
                                            <option value="10">10 minutos</option>
                                            <option value="15">15 minutos</option>
                                            <option value="30">30 minutos</option>
                                        </select>
                                        @error('appointment_buffer') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Online Booking --}}
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">{{ __('settings.appointments.online_booking') }}</h3>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="allow_online_booking" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.appointments.allow_online') }}</label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('settings.appointments.allow_online_desc') }}</p>
                                        </div>
                                        <button type="button" wire:click="$toggle('allow_online_booking')" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $allow_online_booking ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                                            <span class="translate-x-0 pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $allow_online_booking ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label for="require_booking_confirmation" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.appointments.require_confirmation') }}</label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('settings.appointments.require_confirmation_desc') }}</p>
                                        </div>
                                        <button type="button" wire:click="$toggle('require_booking_confirmation')" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $require_booking_confirmation ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                                            <span class="translate-x-0 pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $require_booking_confirmation ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                                    <div>
                                        <label for="min_booking_notice" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.appointments.min_notice') }}</label>
                                        <div class="mt-1 flex rounded-lg shadow-sm">
                                            <input type="number" wire:model="min_booking_notice" id="min_booking_notice" min="0" max="168" class="block w-full rounded-l-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                                            <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 text-sm">horas</span>
                                        </div>
                                        @error('min_booking_notice') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="max_booking_advance" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.appointments.max_advance') }}</label>
                                        <div class="mt-1 flex rounded-lg shadow-sm">
                                            <input type="number" wire:model="max_booking_advance" id="max_booking_advance" min="1" max="365" class="block w-full rounded-l-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                                            <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 text-sm">días</span>
                                        </div>
                                        @error('max_booking_advance') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="cancellation_notice" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.appointments.cancellation_notice') }}</label>
                                        <div class="mt-1 flex rounded-lg shadow-sm">
                                            <input type="number" wire:model="cancellation_notice" id="cancellation_notice" min="0" max="168" class="block w-full rounded-l-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                                            <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 text-sm">horas</span>
                                        </div>
                                        @error('cancellation_notice') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                                <svg wire:loading wire:target="saveAppointments" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('general.save') }}
                            </button>
                        </div>
                    </form>
                    @endif

                    {{-- Notifications Tab --}}
                    @if($activeTab === 'notifications')
                    <form wire:submit="saveNotifications">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('settings.notifications.title') }}</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('settings.notifications.description') }}</p>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="flex items-center justify-between py-4 border-b border-gray-200 dark:border-gray-700">
                                <div>
                                    <label class="text-sm font-medium text-gray-900 dark:text-white">{{ __('settings.notifications.send_confirmations') }}</label>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('settings.notifications.send_confirmations_desc') }}</p>
                                </div>
                                <button type="button" wire:click="$toggle('send_confirmations')" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $send_confirmations ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $send_confirmations ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </div>
                            <div class="flex items-center justify-between py-4 border-b border-gray-200 dark:border-gray-700">
                                <div>
                                    <label class="text-sm font-medium text-gray-900 dark:text-white">{{ __('settings.notifications.send_reminders') }}</label>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('settings.notifications.send_reminders_desc') }}</p>
                                </div>
                                <button type="button" wire:click="$toggle('send_reminders')" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $send_reminders ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $send_reminders ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </div>
                            @if($send_reminders)
                            <div class="pl-4 border-l-2 border-indigo-200 dark:border-indigo-800">
                                <label for="reminder_hours_before" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.notifications.reminder_time') }}</label>
                                <div class="mt-1 flex rounded-lg shadow-sm max-w-xs">
                                    <input type="number" wire:model="reminder_hours_before" id="reminder_hours_before" min="1" max="168" class="block w-full rounded-l-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                                    <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 text-sm">{{ __('settings.notifications.hours_before') }}</span>
                                </div>
                                @error('reminder_hours_before') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>
                            @endif
                        </div>
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                                <svg wire:loading wire:target="saveNotifications" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('general.save') }}
                            </button>
                        </div>
                    </form>
                    @endif

                    {{-- Billing Tab --}}
                    @if($activeTab === 'billing')
                    <form wire:submit="saveBilling">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('settings.billing.title') }}</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('settings.billing.description') }}</p>
                        </div>
                        <div class="p-6 space-y-6">
                            {{-- billing_enabled toggle --}}
                            <div class="flex items-start gap-4 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ __('settings.billing.billing_enabled') }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ __('settings.billing.billing_enabled_hint') }}</p>
                                </div>
                                <button
                                    type="button"
                                    role="switch"
                                    :aria-checked="$wire.billing_enabled ? 'true' : 'false'"
                                    wire:click="$toggle('billing_enabled')"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 {{ $billing_enabled ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600' }}"
                                >
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $billing_enabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="tax_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.billing.tax_id') }}</label>
                                    <input type="text" wire:model="tax_id" id="tax_id" placeholder="NIT / RFC / RUC" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('tax_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="legal_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.billing.legal_name') }}</label>
                                    <input type="text" wire:model="legal_name" id="legal_name" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('legal_name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <label for="billing_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.billing.address') }}</label>
                                <textarea wire:model="billing_address" id="billing_address" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                @error('billing_address') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                                <svg wire:loading wire:target="saveBilling" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('general.save') }}
                            </button>
                        </div>
                    </form>
                    @endif

                    {{-- Branding Tab --}}
                    @if($activeTab === 'branding')
                    <form wire:submit="saveBranding">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('settings.branding.title') }}</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('settings.branding.description') }}</p>
                        </div>
                        <div class="p-6 space-y-6">
                            {{-- Logo Upload --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('settings.branding.logo') }}</label>
                                <div class="flex items-center space-x-6">
                                    <div class="flex-shrink-0">
                                        @if($logo)
                                            <img src="{{ $logo->temporaryUrl() }}" alt="Preview" class="h-20 w-20 object-contain rounded-lg border border-gray-200 dark:border-gray-700">
                                        @elseif($currentLogo)
                                            <img src="{{ Storage::url($currentLogo) }}" alt="Logo actual" class="h-20 w-20 object-contain rounded-lg border border-gray-200 dark:border-gray-700">
                                        @else
                                            <div class="h-20 w-20 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center">
                                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <input type="file" wire:model="logo" id="logo" accept="image/*" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900 dark:file:text-indigo-300">
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PNG, JPG, GIF hasta 2MB</p>
                                        @error('logo') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                        @if($currentLogo)
                                        <button type="button" wire:click="removeLogo" class="mt-2 text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                            {{ __('settings.branding.remove_logo') }}
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Colors --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="primary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.branding.primary_color') }}</label>
                                    <div class="mt-1 flex items-center space-x-3">
                                        <input type="color" wire:model="primary_color" id="primary_color" class="h-10 w-14 rounded border border-gray-300 dark:border-gray-600 cursor-pointer">
                                        <input type="text" wire:model="primary_color" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 uppercase" maxlength="7">
                                    </div>
                                    @error('primary_color') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="secondary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('settings.branding.secondary_color') }}</label>
                                    <div class="mt-1 flex items-center space-x-3">
                                        <input type="color" wire:model="secondary_color" id="secondary_color" class="h-10 w-14 rounded border border-gray-300 dark:border-gray-600 cursor-pointer">
                                        <input type="text" wire:model="secondary_color" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 uppercase" maxlength="7">
                                    </div>
                                    @error('secondary_color') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- Preview --}}
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('settings.branding.preview') }}</h4>
                                <div class="flex items-center space-x-4">
                                    <button type="button" class="px-4 py-2 rounded-lg text-white text-sm font-medium" style="background-color: {{ $primary_color }}">
                                        Botón Primario
                                    </button>
                                    <button type="button" class="px-4 py-2 rounded-lg text-white text-sm font-medium" style="background-color: {{ $secondary_color }}">
                                        Botón Secundario
                                    </button>
                                    <span class="text-sm font-medium" style="color: {{ $primary_color }}">Texto destacado</span>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                                <svg wire:loading wire:target="saveBranding" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('general.save') }}
                            </button>
                        </div>
                    </form>
                    @endif

                </div>

                    @if($activeTab === 'data' && auth()->id() === $clinic->owner_id)
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('settings.data.title') }}</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('settings.data.subtitle') }}</p>

                        <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex">
                                <svg class="h-5 w-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700 dark:text-blue-300">{{ __('settings.data.info') }}</p>
                                    <ul class="mt-2 text-sm text-blue-600 dark:text-blue-400 list-disc list-inside space-y-0.5">
                                        <li>{{ __('settings.data.file_patients') }}</li>
                                        <li>{{ __('settings.data.file_appointments') }}</li>
                                        <li>{{ __('settings.data.file_records') }}</li>
                                        <li>{{ __('settings.data.file_staff') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button wire:click="exportData"
                                    wire:loading.attr="disabled"
                                    wire:target="exportData"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg wire:loading.remove wire:target="exportData" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <svg wire:loading wire:target="exportData" class="animate-spin w-4 h-4 mr-2 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('settings.data.export_btn') }}
                            </button>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('settings.data.export_hint') }}</p>
                        </div>
                    </div>
                    @endif

            </div>
        </div>
    </div>
</div>
