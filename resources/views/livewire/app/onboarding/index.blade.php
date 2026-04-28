<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ __('onboarding.welcome', ['name' => $clinic->name]) }}
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ __('onboarding.subtitle') }}
            </p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                @for ($i = 1; $i <= $totalSteps; $i++)
                    <div class="flex items-center {{ $i < $totalSteps ? 'flex-1' : '' }}">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 text-sm font-semibold transition-colors
                            {{ $i < $currentStep ? 'bg-primary text-white border-primary' : '' }}
                            {{ $i === $currentStep ? 'bg-primary text-white border-primary ring-4 ring-primary/20' : '' }}
                            {{ $i > $currentStep ? 'bg-white dark:bg-gray-800 text-gray-400 dark:text-gray-500 border-gray-300 dark:border-gray-600' : '' }}">
                            @if ($i < $currentStep)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            @else
                                {{ $i }}
                            @endif
                        </div>
                        @if ($i < $totalSteps)
                            <div class="flex-1 h-1 mx-2 rounded {{ $i < $currentStep ? 'bg-primary' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                        @endif
                    </div>
                @endfor
            </div>
            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>{{ __('onboarding.step_clinic') }}</span>
                <span>{{ __('onboarding.step_localization') }}</span>
                <span>{{ __('onboarding.step_branding') }}</span>
                <span>{{ __('onboarding.step_schedule') }}</span>
                <span>{{ __('onboarding.step_plan') }}</span>
            </div>
        </div>

        <!-- Card -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 sm:p-8">

            {{-- Step 1: Clinic Info --}}
            @if ($currentStep === 1)
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">
                        {{ __('onboarding.clinic_info_title') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                        {{ __('onboarding.clinic_info_description') }}
                    </p>

                    <div class="space-y-4">
                        <!-- Phone with country code -->
                        <div>
                            <x-input-label :value="__('onboarding.phone')" />
                            <div class="flex gap-2 mt-1">
                                <select wire:model="phone_country" class="w-32 shrink-0 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
                                    @foreach (\App\Livewire\App\Onboarding\Index::PHONE_CODES as $code => $data)
                                        <option value="{{ $code }}">{{ $data['flag'] }} {{ $data['code'] }}</option>
                                    @endforeach
                                </select>
                                <x-text-input wire:model="phone_number" class="flex-1" type="tel" placeholder="1234-5678" />
                            </div>
                            <x-input-error :messages="$errors->get('phone_number')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="address" :value="__('onboarding.address')" />
                            <x-text-input wire:model="address" id="address" class="block mt-1 w-full" type="text" />
                            <x-input-error :messages="$errors->get('address')" class="mt-1" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="city" :value="__('onboarding.city')" />
                                <x-text-input wire:model="city" id="city" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('city')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="country" :value="__('onboarding.country')" />
                                <select wire:model.live="country" id="country" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    @foreach (\App\Livewire\App\Onboarding\Index::PHONE_CODES as $code => $data)
                                        <option value="{{ $code }}">{{ $data['flag'] }} {{ $data['name'] }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('country')" class="mt-1" />
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Step 2: Localization --}}
            @if ($currentStep === 2)
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">
                        {{ __('onboarding.localization_title') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                        {{ __('onboarding.localization_description') }}
                    </p>

                    <div class="space-y-4">
                        <div>
                            <x-input-label for="timezone" :value="__('onboarding.timezone')" />
                            <select wire:model="timezone" id="timezone" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="America/Guatemala">America/Guatemala (GMT-6)</option>
                                <option value="America/Mexico_City">America/Mexico_City (GMT-6)</option>
                                <option value="America/Bogota">America/Bogota (GMT-5)</option>
                                <option value="America/Lima">America/Lima (GMT-5)</option>
                                <option value="America/Santiago">America/Santiago (GMT-4)</option>
                                <option value="America/Argentina/Buenos_Aires">America/Buenos_Aires (GMT-3)</option>
                                <option value="America/Sao_Paulo">America/Sao_Paulo (GMT-3)</option>
                                <option value="America/New_York">America/New_York (GMT-5)</option>
                                <option value="America/Chicago">America/Chicago (GMT-6)</option>
                                <option value="America/Denver">America/Denver (GMT-7)</option>
                                <option value="America/Los_Angeles">America/Los_Angeles (GMT-8)</option>
                                <option value="Europe/Madrid">Europe/Madrid (GMT+1)</option>
                                <option value="America/Costa_Rica">America/Costa_Rica (GMT-6)</option>
                                <option value="America/Panama">America/Panama (GMT-5)</option>
                                <option value="America/Tegucigalpa">America/Tegucigalpa (GMT-6)</option>
                                <option value="America/El_Salvador">America/El_Salvador (GMT-6)</option>
                                <option value="America/Managua">America/Managua (GMT-6)</option>
                                <option value="America/Santo_Domingo">America/Santo_Domingo (GMT-4)</option>
                                <option value="America/Caracas">America/Caracas (GMT-4)</option>
                                <option value="America/Guayaquil">America/Guayaquil (GMT-5)</option>
                                <option value="America/La_Paz">America/La_Paz (GMT-4)</option>
                                <option value="America/Asuncion">America/Asuncion (GMT-4)</option>
                                <option value="America/Montevideo">America/Montevideo (GMT-3)</option>
                                <option value="America/Toronto">America/Toronto (GMT-5)</option>
                            </select>
                            <x-input-error :messages="$errors->get('timezone')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="currency" :value="__('onboarding.currency')" />
                            <select wire:model="currency" id="currency" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="USD">USD - Dólar Estadounidense</option>
                                <option value="GTQ">GTQ - Quetzal</option>
                                <option value="MXN">MXN - Peso Mexicano</option>
                                <option value="COP">COP - Peso Colombiano</option>
                                <option value="ARS">ARS - Peso Argentino</option>
                                <option value="CLP">CLP - Peso Chileno</option>
                                <option value="PEN">PEN - Sol Peruano</option>
                                <option value="EUR">EUR - Euro</option>
                                <option value="CAD">CAD - Dólar Canadiense</option>
                                <option value="CRC">CRC - Colón Costarricense</option>
                                <option value="PAB">PAB - Balboa Panameño</option>
                                <option value="HNL">HNL - Lempira</option>
                                <option value="DOP">DOP - Peso Dominicano</option>
                                <option value="BOB">BOB - Boliviano</option>
                                <option value="PYG">PYG - Guaraní</option>
                                <option value="UYU">UYU - Peso Uruguayo</option>
                            </select>
                            <x-input-error :messages="$errors->get('currency')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="locale" :value="__('onboarding.language')" />
                            <select wire:model="locale" id="locale" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="es">🇪🇸 Español</option>
                                <option value="en">🇺🇸 English</option>
                            </select>
                            <x-input-error :messages="$errors->get('locale')" class="mt-1" />
                        </div>
                    </div>
                </div>
            @endif

            {{-- Step 3: Branding --}}
            @if ($currentStep === 3)
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">
                        {{ __('onboarding.branding_title') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                        {{ __('onboarding.branding_description') }}
                    </p>

                    <div class="space-y-6">
                        <div>
                            <x-input-label for="primary_color" :value="__('onboarding.primary_color')" />
                            <div class="flex items-center gap-3 mt-1">
                                <input wire:model.live="primary_color" type="color" id="primary_color" class="h-10 w-14 rounded border border-gray-300 dark:border-gray-600 cursor-pointer" />
                                <x-text-input wire:model.live="primary_color" class="w-32" type="text" maxlength="7" />
                            </div>
                            <x-input-error :messages="$errors->get('primary_color')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="secondary_color" :value="__('onboarding.secondary_color')" />
                            <div class="flex items-center gap-3 mt-1">
                                <input wire:model.live="secondary_color" type="color" id="secondary_color" class="h-10 w-14 rounded border border-gray-300 dark:border-gray-600 cursor-pointer" />
                                <x-text-input wire:model.live="secondary_color" class="w-32" type="text" maxlength="7" />
                            </div>
                            <x-input-error :messages="$errors->get('secondary_color')" class="mt-1" />
                        </div>

                        <!-- Preview -->
                        <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('onboarding.preview') }}</p>
                            <div class="flex gap-3">
                                <button type="button" class="px-4 py-2 rounded-lg text-white text-sm font-medium" style="background-color: {{ $primary_color }}">
                                    {{ __('onboarding.primary_button') }}
                                </button>
                                <button type="button" class="px-4 py-2 rounded-lg text-white text-sm font-medium" style="background-color: {{ $secondary_color }}">
                                    {{ __('onboarding.secondary_button') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Step 4: Working Hours --}}
            @if ($currentStep === 4)
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">
                        {{ __('onboarding.schedule_title') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                        {{ __('onboarding.schedule_description') }}
                    </p>

                    <div class="space-y-6">
                        <!-- Weekday selection -->
                        <div>
                            <x-input-label :value="__('onboarding.weekday_schedule')" />
                            <div class="flex flex-wrap gap-2 mt-2">
                                @php
                                    $weekdays = [
                                        1 => __('onboarding.day_mon'),
                                        2 => __('onboarding.day_tue'),
                                        3 => __('onboarding.day_wed'),
                                        4 => __('onboarding.day_thu'),
                                        5 => __('onboarding.day_fri'),
                                    ];
                                @endphp
                                @foreach ($weekdays as $value => $label)
                                    <label class="inline-flex items-center px-3 py-2 rounded-lg border cursor-pointer transition text-sm
                                        {{ in_array($value, $working_days) ? 'bg-primary/10 border-primary text-primary font-medium' : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400' }}">
                                        <input type="checkbox" wire:model.live="working_days" value="{{ $value }}" class="sr-only" />
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('working_days')" class="mt-1" />
                        </div>

                        <!-- Shift type toggle -->
                        <div>
                            <x-input-label :value="__('onboarding.shift_type')" />
                            <div class="flex gap-2 mt-2">
                                <button type="button" wire:click="$set('has_split_shift', true)"
                                    class="px-4 py-2 rounded-lg border text-sm font-medium transition
                                    {{ $has_split_shift ? 'bg-primary text-white border-primary' : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400' }}">
                                    {{ __('onboarding.split_shift') }}
                                </button>
                                <button type="button" wire:click="$set('has_split_shift', false)"
                                    class="px-4 py-2 rounded-lg border text-sm font-medium transition
                                    {{ !$has_split_shift ? 'bg-primary text-white border-primary' : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400' }}">
                                    {{ __('onboarding.continuous_shift') }}
                                </button>
                            </div>
                        </div>

                        <!-- Weekday shifts -->
                        <div class="grid {{ $has_split_shift ? 'grid-cols-1 md:grid-cols-2' : 'grid-cols-1' }} gap-4">
                            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                    {{ $has_split_shift ? __('onboarding.shift_morning') : __('onboarding.working_hours') }}
                                </p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <x-input-label for="weekday_shift1_start" :value="__('onboarding.start_time')" class="text-xs" />
                                        <x-text-input wire:model="weekday_shift1_start" id="weekday_shift1_start" class="block mt-1 w-full" type="time" />
                                        <x-input-error :messages="$errors->get('weekday_shift1_start')" class="mt-1" />
                                    </div>
                                    <div>
                                        <x-input-label for="weekday_shift1_end" :value="__('onboarding.end_time')" class="text-xs" />
                                        <x-text-input wire:model="weekday_shift1_end" id="weekday_shift1_end" class="block mt-1 w-full" type="time" />
                                        <x-input-error :messages="$errors->get('weekday_shift1_end')" class="mt-1" />
                                    </div>
                                </div>
                            </div>

                            @if ($has_split_shift)
                                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('onboarding.shift_afternoon') }}</p>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <x-input-label for="weekday_shift2_start" :value="__('onboarding.start_time')" class="text-xs" />
                                            <x-text-input wire:model="weekday_shift2_start" id="weekday_shift2_start" class="block mt-1 w-full" type="time" />
                                            <x-input-error :messages="$errors->get('weekday_shift2_start')" class="mt-1" />
                                        </div>
                                        <div>
                                            <x-input-label for="weekday_shift2_end" :value="__('onboarding.end_time')" class="text-xs" />
                                            <x-text-input wire:model="weekday_shift2_end" id="weekday_shift2_end" class="block mt-1 w-full" type="time" />
                                            <x-input-error :messages="$errors->get('weekday_shift2_end')" class="mt-1" />
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Weekend toggle -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model.live="works_weekends" class="rounded border-gray-300 dark:border-gray-600 text-primary focus:ring-primary" />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('onboarding.works_weekends') }}</span>
                            </label>
                        </div>

                        @if ($works_weekends)
                            <!-- Weekend days -->
                            <div>
                                <x-input-label :value="__('onboarding.weekend_days_label')" />
                                <div class="flex gap-2 mt-2">
                                    <label class="inline-flex items-center px-3 py-2 rounded-lg border cursor-pointer transition text-sm
                                        {{ in_array(6, $weekend_days) ? 'bg-primary/10 border-primary text-primary font-medium' : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400' }}">
                                        <input type="checkbox" wire:model.live="weekend_days" value="6" class="sr-only" />
                                        {{ __('onboarding.day_sat') }}
                                    </label>
                                    <label class="inline-flex items-center px-3 py-2 rounded-lg border cursor-pointer transition text-sm
                                        {{ in_array(0, $weekend_days) ? 'bg-primary/10 border-primary text-primary font-medium' : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400' }}">
                                        <input type="checkbox" wire:model.live="weekend_days" value="0" class="sr-only" />
                                        {{ __('onboarding.day_sun') }}
                                    </label>
                                </div>
                                <x-input-error :messages="$errors->get('weekend_days')" class="mt-1" />
                            </div>

                            <!-- Weekend shift type -->
                            <div>
                                <div class="flex gap-2">
                                    <button type="button" wire:click="$set('weekend_has_split_shift', false)"
                                        class="px-4 py-2 rounded-lg border text-sm font-medium transition
                                        {{ !$weekend_has_split_shift ? 'bg-primary text-white border-primary' : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400' }}">
                                        {{ __('onboarding.morning_only') }}
                                    </button>
                                    <button type="button" wire:click="$set('weekend_has_split_shift', true)"
                                        class="px-4 py-2 rounded-lg border text-sm font-medium transition
                                        {{ $weekend_has_split_shift ? 'bg-primary text-white border-primary' : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400' }}">
                                        {{ __('onboarding.split_shift') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Weekend shifts -->
                            <div class="grid {{ $weekend_has_split_shift ? 'grid-cols-1 md:grid-cols-2' : 'grid-cols-1 md:grid-cols-1 max-w-sm' }} gap-4">
                                <div class="p-4 rounded-lg bg-amber-50 dark:bg-amber-900/20">
                                    <p class="text-sm font-medium text-amber-700 dark:text-amber-300 mb-3">
                                        {{ $weekend_has_split_shift ? __('onboarding.shift_morning') : __('onboarding.weekend_schedule') }}
                                    </p>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <x-input-label for="weekend_shift1_start" :value="__('onboarding.start_time')" class="text-xs" />
                                            <x-text-input wire:model="weekend_shift1_start" id="weekend_shift1_start" class="block mt-1 w-full" type="time" />
                                            <x-input-error :messages="$errors->get('weekend_shift1_start')" class="mt-1" />
                                        </div>
                                        <div>
                                            <x-input-label for="weekend_shift1_end" :value="__('onboarding.end_time')" class="text-xs" />
                                            <x-text-input wire:model="weekend_shift1_end" id="weekend_shift1_end" class="block mt-1 w-full" type="time" />
                                            <x-input-error :messages="$errors->get('weekend_shift1_end')" class="mt-1" />
                                        </div>
                                    </div>
                                </div>

                                @if ($weekend_has_split_shift)
                                    <div class="p-4 rounded-lg bg-amber-50 dark:bg-amber-900/20">
                                        <p class="text-sm font-medium text-amber-700 dark:text-amber-300 mb-3">{{ __('onboarding.shift_afternoon') }}</p>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <x-input-label for="weekend_shift2_start" :value="__('onboarding.start_time')" class="text-xs" />
                                                <x-text-input wire:model="weekend_shift2_start" id="weekend_shift2_start" class="block mt-1 w-full" type="time" />
                                                <x-input-error :messages="$errors->get('weekend_shift2_start')" class="mt-1" />
                                            </div>
                                            <div>
                                                <x-input-label for="weekend_shift2_end" :value="__('onboarding.end_time')" class="text-xs" />
                                                <x-text-input wire:model="weekend_shift2_end" id="weekend_shift2_end" class="block mt-1 w-full" type="time" />
                                                <x-input-error :messages="$errors->get('weekend_shift2_end')" class="mt-1" />
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Step 5: Plan Selection (Dynamic from DB) --}}
            @if ($currentStep === 5)
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">
                        {{ __('onboarding.choose_plan_title') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                        {{ __('onboarding.choose_plan_description') }}
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($this->plans as $plan)
                            <x-plan-card
                                :plan="$plan"
                                context="onboarding"
                                :selected="$selectedPlan === $plan->slug"
                                onSelect="selectPlan"
                            />
                        @endforeach
                    </div>

                    @if ($selectedPlan !== 'free')
                        <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                            <p class="text-sm text-amber-700 dark:text-amber-300">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ __('onboarding.paid_plan_notice') }}
                            </p>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Navigation Buttons -->
            <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <div>
                    @if ($currentStep > 1)
                        <button wire:click="previousStep" type="button" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            {{ __('onboarding.back') }}
                        </button>
                    @else
                        <button wire:click="skipOnboarding" type="button" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition">
                            {{ __('onboarding.skip') }}
                        </button>
                    @endif
                </div>

                <div>
                    @if ($currentStep < $totalSteps)
                        <button wire:click="nextStep" type="button" class="inline-flex items-center px-6 py-2.5 text-sm font-medium text-white bg-primary hover:bg-primary-hover rounded-lg transition">
                            {{ __('onboarding.next') }}
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    @else
                        <button wire:click="completeOnboarding" type="button" class="inline-flex items-center px-6 py-2.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition">
                            {{ __('onboarding.start_using') }}
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
