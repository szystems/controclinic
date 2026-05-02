<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <a href="{{ route('app.appointments.index', ['clinic' => $currentClinic->slug]) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            {{ __('appointments.title') }}
                        </a>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="ml-2 text-gray-900 dark:text-white font-medium">{{ __('appointments.new_appointment') }}</span>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('appointments.new_appointment') }}
            </h1>
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

        {{-- Form --}}
        <form wire:submit="save" class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                {{-- Patient Selection --}}
                <div class="mb-6">
                    <label for="patientSearch" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('appointments.patient') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="relative" x-data="{ open: @entangle('showPatientDropdown') }">
                        <input wire:model.live.debounce.300ms="patientSearch"
                               type="text"
                               id="patientSearch"
                               placeholder="{{ __('appointments.search_patient') }}"
                               autocomplete="off"
                               class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('patient_id') border-red-500 @enderror">

                        {{-- Dropdown --}}
                        @if($showPatientDropdown && $patients->count() > 0)
                        <div class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 max-h-60 overflow-y-auto">
                            @foreach($patients as $patient)
                            <button type="button"
                                    wire:click="selectPatient('{{ $patient->id }}')"
                                    class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center space-x-3">
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                    <span class="text-indigo-600 dark:text-indigo-400 font-medium text-xs">{{ $patient->initials }}</span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $patient->full_name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $patient->phone }}</div>
                                </div>
                            </button>
                            @endforeach
                        </div>
                        @endif

                        @if($patient_id)
                        <input type="hidden" wire:model="patient_id">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        @endif
                    </div>
                    @error('patient_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Doctor Selection --}}
                <div class="mb-6">
                    <label for="doctor_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('appointments.doctor') }} <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="doctor_id"
                            id="doctor_id"
                            class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('doctor_id') border-red-500 @enderror">
                        <option value="">{{ __('appointments.select_doctor') }}</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                        @endforeach
                    </select>
                    @error('doctor_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Appointment Type --}}
                <div class="mb-6">
                    <label for="appointment_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('appointments.type') }} <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="appointment_type"
                            id="appointment_type"
                            class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date and Time --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label for="appointment_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('appointments.date') }} <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="appointment_date"
                               type="date"
                               id="appointment_date"
                               class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('appointment_date') border-red-500 @enderror">
                        @error('appointment_date')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('appointments.start_time') }} <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="start_time"
                               type="time"
                               id="start_time"
                               class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('start_time') border-red-500 @enderror">
                        @error('start_time')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('appointments.duration') }}
                        </label>
                        <select wire:model="duration_minutes"
                                id="duration_minutes"
                                class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="15">15 {{ __('appointments.minutes') }}</option>
                            <option value="30">30 {{ __('appointments.minutes') }}</option>
                            <option value="45">45 {{ __('appointments.minutes') }}</option>
                            <option value="60">60 {{ __('appointments.minutes') }}</option>
                            <option value="90">90 {{ __('appointments.minutes') }}</option>
                            <option value="120">120 {{ __('appointments.minutes') }}</option>
                        </select>
                    </div>
                </div>

                {{-- Reason --}}
                <div class="mb-6">
                    <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('appointments.reason') }}
                    </label>
                    <input wire:model="reason"
                           type="text"
                           id="reason"
                           class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                {{-- Symptoms --}}
                <div class="mb-6">
                    <label for="symptoms" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('appointments.symptoms') }}
                    </label>
                    <textarea wire:model="symptoms"
                              id="symptoms"
                              rows="2"
                              class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                </div>

                {{-- Notes --}}
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('appointments.notes') }}
                    </label>
                    <textarea wire:model="notes"
                              id="notes"
                              rows="2"
                              class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                </div>

                {{-- Room --}}
                <div>
                    <label for="room" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('appointments.room') }}
                    </label>
                    <input wire:model="room"
                           type="text"
                           id="room"
                           class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
            </div>

            {{-- Billing section (only when billing_enabled) --}}
            @if($billingEnabled)
            <div class="rounded-xl border border-emerald-200 dark:border-emerald-700/50 bg-emerald-50/60 dark:bg-emerald-900/20 p-4 space-y-4">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">{{ __('appointments.billing_section') }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Precio --}}
                    <div>
                        <label for="consultation_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('appointments.consultation_price') }}
                        </label>
                        <div class="flex rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500">
                            <span class="flex items-center px-3 bg-gray-50 dark:bg-gray-700 border-r border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 text-sm select-none">{{ $currency }}</span>
                            <input wire:model="consultation_price"
                                   type="number" id="consultation_price" min="0" step="0.01"
                                   placeholder="0.00"
                                   class="flex-1 min-w-0 py-2 px-3 border-0 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none sm:text-sm">
                        </div>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('appointments.price_optional') }}</p>
                        @error('consultation_price') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                    {{-- Descuento --}}
                    <div>
                        <label for="consultation_discount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('appointments.consultation_discount') }}
                        </label>
                        <div class="flex rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500">
                            <span class="flex items-center px-3 bg-gray-50 dark:bg-gray-700 border-r border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 text-sm select-none">{{ $currency }}</span>
                            <input wire:model="consultation_discount"
                                   type="number" id="consultation_discount" min="0" step="0.01"
                                   placeholder="0.00"
                                   class="flex-1 min-w-0 py-2 px-3 border-0 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none sm:text-sm">
                        </div>
                        @error('consultation_discount') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
                {{-- Is billable toggle --}}
                <div class="flex items-center gap-3">
                    <button type="button" role="switch" wire:click="$toggle('is_billable')"
                            class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $is_billable ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600' }}">
                        <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow transition duration-200 ease-in-out {{ $is_billable ? 'translate-x-4' : 'translate-x-0' }}"></span>
                    </button>
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('appointments.is_billable_hint') }}</span>
                </div>
            </div>
            @endif

            {{-- Staff confirmation hint --}}
            <div class="rounded-lg bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 p-4">
                <div class="flex gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-blue-800 dark:text-blue-200">{!! __('appointments.create_staff_hint') !!}</p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end space-x-3">
                <a href="{{ route('app.appointments.index', ['clinic' => $currentClinic->slug]) }}"
                   class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('general.cancel') }}
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('general.save') }}
                </button>
            </div>
        </form>
    </div>
</div>
