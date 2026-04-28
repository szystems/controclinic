<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Breadcrumb --}}
        <nav class="mb-6">
            <a href="{{ route('app.staff.index', ['clinic' => $currentClinic->slug]) }}"
               wire:navigate
               class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ __('staff.back_to_list') }}
            </a>
        </nav>

        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
            {{ __('invitations.invite_member') }}
        </h1>

        {{-- Info Banner --}}
        <div class="mb-4 rounded-lg bg-blue-50 dark:bg-blue-900/50 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm font-medium text-blue-800 dark:text-blue-200">
                    {{ __('invitations.invite_description') }}
                </p>
            </div>
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

        <form wire:submit="save" class="space-y-6">
            {{-- Personal Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('staff.personal_info') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Name --}}
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('staff.name') }} <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="name" type="text" id="name"
                               class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('staff.email') }} <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="email" type="email" id="email"
                               class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('email') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Role & Access --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('staff.role_and_access') }}</h2>
                <div>
                    {{-- Role --}}
                    <div class="max-w-md">
                        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('staff.role') }} <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="role" id="role"
                                class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">{{ __('staff.select_role') }}</option>
                            @if($currentClinic->canAddDoctor())
                                <option value="doctor">{{ __('staff.role_doctor') }}</option>
                            @endif
                            @if($currentClinic->canAddStaff())
                                <option value="assistant">{{ __('staff.role_assistant') }}</option>
                                <option value="secretary">{{ __('staff.role_secretary') }}</option>
                                <option value="receptionist">{{ __('staff.role_receptionist') }}</option>
                            @endif
                        </select>
                        @error('role') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

                        @if(!$currentClinic->canAddDoctor() && !$currentClinic->canAddStaff())
                            <div class="mt-2">
                                <x-upgrade-nudge type="inline" :clinic-slug="$currentClinic->slug" />
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Permissions Preview --}}
            <x-role-permissions-preview :role="$role" />

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('app.staff.index', ['clinic' => $currentClinic->slug]) }}"
                   wire:navigate
                   class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    {{ __('general.cancel') }}
                </a>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-primary hover:bg-primary-hover rounded-lg transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    <span wire:loading.remove wire:target="save" class="inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        {{ __('invitations.invite_member') }}
                    </span>
                    <span wire:loading wire:target="save">{{ __('general.saving') }}...</span>
                </button>
            </div>
        </form>
    </div>
</div>
