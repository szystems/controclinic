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
                        <span class="ml-2 text-gray-900 dark:text-white font-medium">{{ __('appointments.edit_appointment') }}</span>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('appointments.edit_appointment') }}
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

        {{-- Not Editable Warning --}}
        @if(!$appointment->isEditable())
        <div class="mb-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/50 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm font-medium text-yellow-800 dark:text-yellow-200">
                    {{ __('general.action_not_allowed') }} - {{ __('appointments.status') }}: {{ $appointment->status_label }}
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
                    <div class="relative">
                        <input wire:model.live.debounce.300ms="patientSearch"
                               type="text"
                               id="patientSearch"
                               placeholder="{{ __('appointments.search_patient') }}"
                               autocomplete="off"
                               @if(!$appointment->isEditable()) disabled @endif
                               class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:opacity-50 @error('patient_id') border-red-500 @enderror">

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
                            @if(!$appointment->isEditable()) disabled @endif
                            class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:opacity-50 @error('doctor_id') border-red-500 @enderror">
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
                            @if(!$appointment->isEditable()) disabled @endif
                            class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:opacity-50">
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
                               @if(!$appointment->isEditable()) disabled @endif
                               class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:opacity-50 @error('appointment_date') border-red-500 @enderror">
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
                               @if(!$appointment->isEditable()) disabled @endif
                               class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:opacity-50 @error('start_time') border-red-500 @enderror">
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
                                @if(!$appointment->isEditable()) disabled @endif
                                class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm disabled:opacity-50">
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

            {{-- Actions --}}
            <div class="flex justify-end space-x-3">
                <a href="{{ route('app.appointments.index', ['clinic' => $currentClinic->slug]) }}"
                   class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('general.cancel') }}
                </a>
                @if($appointment->isEditable())
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('general.save') }}
                </button>
                @endif
            </div>
        </form>
    </div>
</div>
