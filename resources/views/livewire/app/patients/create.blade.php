<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-6">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <a href="{{ route('app.patients.index', ['clinic' => $currentClinic->slug]) }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                            {{ __('patients.title') }}
                        </a>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="ml-2 text-gray-700 dark:text-gray-300">{{ __('patients.new_patient') }}</span>
                    </li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('patients.new_patient') }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('patients.create_description') }}
            </p>
        </div>

        {{-- Flash Messages --}}
        @if (session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/50 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm font-medium text-red-800 dark:text-red-200">
                    {{ session('error') }}
                </p>
            </div>
        </div>
        @endif

        <form wire:submit="save">
            {{-- Basic Information --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ __('patients.basic_info') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- First Name --}}
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.first_name') }} <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="first_name" type="text" id="first_name"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('first_name') border-red-500 @enderror">
                        @error('first_name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Last Name --}}
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.last_name') }} <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="last_name" type="text" id="last_name"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('last_name') border-red-500 @enderror">
                        @error('last_name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.email') }}
                        </label>
                        <input wire:model="email" type="email" id="email"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('email') border-red-500 @enderror">
                        @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.phone') }} <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="phone" type="tel" id="phone"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('phone') border-red-500 @enderror">
                        @error('phone')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Secondary Phone --}}
                    <div>
                        <label for="phone_secondary" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.phone_secondary') }}
                        </label>
                        <input wire:model="phone_secondary" type="tel" id="phone_secondary"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    {{-- Birth Date --}}
                    <div>
                        <label for="birth_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.birth_date') }}
                        </label>
                        <input wire:model="birth_date" type="date" id="birth_date"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('birth_date') border-red-500 @enderror">
                        @error('birth_date')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Gender --}}
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.gender') }}
                        </label>
                        <select wire:model="gender" id="gender"
                                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">{{ __('general.select') }}</option>
                            <option value="male">{{ __('patients.male') }}</option>
                            <option value="female">{{ __('patients.female') }}</option>
                            <option value="other">{{ __('patients.other') }}</option>
                        </select>
                    </div>

                    {{-- Primary Doctor --}}
                    <div>
                        <label for="primary_doctor_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.primary_doctor') }}
                        </label>
                        <select wire:model="primary_doctor_id" id="primary_doctor_id"
                                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">{{ __('general.select') }}</option>
                            @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Identification --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ __('patients.identification') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="id_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.id_type') }}
                        </label>
                        <select wire:model="id_type" id="id_type"
                                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">{{ __('general.select') }}</option>
                            <option value="DPI">DPI</option>
                            <option value="Pasaporte">{{ __('patients.passport') }}</option>
                            <option value="Licencia">{{ __('patients.license') }}</option>
                            <option value="Otro">{{ __('general.other') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="id_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.id_number') }}
                        </label>
                        <input wire:model="id_number" type="text" id="id_number"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ __('patients.address') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.street_address') }}
                        </label>
                        <input wire:model="address" type="text" id="address"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.city') }}
                        </label>
                        <input wire:model="city" type="text" id="city"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.state') }}
                        </label>
                        <input wire:model="state" type="text" id="state"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.postal_code') }}
                        </label>
                        <input wire:model="postal_code" type="text" id="postal_code"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.country') }}
                        </label>
                        <select wire:model="country" id="country"
                                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="GT">Guatemala</option>
                            <option value="MX">México</option>
                            <option value="SV">El Salvador</option>
                            <option value="HN">Honduras</option>
                            <option value="NI">Nicaragua</option>
                            <option value="CR">Costa Rica</option>
                            <option value="PA">Panamá</option>
                            <option value="CO">Colombia</option>
                            <option value="ES">España</option>
                            <option value="US">Estados Unidos</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Medical Information --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ __('patients.medical_info') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="blood_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.blood_type') }}
                        </label>
                        <select wire:model="blood_type" id="blood_type"
                                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">{{ __('general.select') }}</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label for="allergies" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.allergies') }}
                        </label>
                        <textarea wire:model="allergies" id="allergies" rows="2"
                                  placeholder="{{ __('patients.allergies_placeholder') }}"
                                  class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label for="chronic_conditions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.chronic_conditions') }}
                        </label>
                        <textarea wire:model="chronic_conditions" id="chronic_conditions" rows="2"
                                  placeholder="{{ __('patients.chronic_conditions_placeholder') }}"
                                  class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label for="current_medications" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.current_medications') }}
                        </label>
                        <textarea wire:model="current_medications" id="current_medications" rows="2"
                                  placeholder="{{ __('patients.current_medications_placeholder') }}"
                                  class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    </div>
                </div>
            </div>

            {{-- Emergency Contact --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ __('patients.emergency_contact') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="emergency_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.contact_name') }}
                        </label>
                        <input wire:model="emergency_name" type="text" id="emergency_name"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="emergency_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.contact_phone') }}
                        </label>
                        <input wire:model="emergency_phone" type="tel" id="emergency_phone"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="emergency_relationship" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.relationship') }}
                        </label>
                        <input wire:model="emergency_relationship" type="text" id="emergency_relationship"
                               placeholder="{{ __('patients.relationship_placeholder') }}"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>
            </div>

            {{-- Insurance --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ __('patients.insurance') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="insurance_provider" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.insurance_provider') }}
                        </label>
                        <input wire:model="insurance_provider" type="text" id="insurance_provider"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="insurance_policy_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('patients.policy_number') }}
                        </label>
                        <input wire:model="insurance_policy_number" type="text" id="insurance_policy_number"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ __('patients.additional_notes') }}
                </h2>
                <div>
                    <textarea wire:model="notes" id="notes" rows="3"
                              class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end space-x-3">
                <a href="{{ route('app.patients.index', ['clinic' => $currentClinic->slug]) }}"
                   class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('general.cancel') }}
                </a>
                <button type="submit"
                        class="btn-primary"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed">
                    <span wire:loading.remove wire:target="save">{{ __('general.save') }}</span>
                    <span wire:loading wire:target="save">{{ __('general.saving') }}...</span>
                </button>
            </div>
        </form>
    </div>
</div>
