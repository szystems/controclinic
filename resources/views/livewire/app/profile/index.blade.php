<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
            {{ __('profile.title') }}
        </h1>

        <form wire:submit.prevent="updateProfile" class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('profile.personal_info') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('profile.name') }} <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="name" type="text" id="name" class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('profile.email') }} <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="email" type="email" id="email" class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('email') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('profile.phone') }}
                        </label>
                        <input wire:model="phone" type="text" id="phone" class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('phone') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="locale" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('profile.locale') }}
                        </label>
                        <input wire:model="locale" type="text" id="locale" class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('locale') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('profile.timezone') }}
                        </label>
                        <input wire:model="timezone" type="text" id="timezone" class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('timezone') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('profile.professional_info') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="specialties" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('profile.specialties') }}
                        </label>
                        <input wire:model="specialties" type="text" id="specialties" class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('specialties') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="license_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('profile.license_number') }}
                        </label>
                        <input wire:model="license_number" type="text" id="license_number" class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('license_number') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('profile.bio') }}
                        </label>
                        <textarea wire:model="bio" id="bio" rows="3" class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                        @error('bio') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-4">
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary hover:bg-primary-hover rounded-lg transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    <span wire:loading.remove wire:target="updateProfile">{{ __('general.save') }}</span>
                    <span wire:loading wire:target="updateProfile">{{ __('general.saving') }}...</span>
                </button>
            </div>
        </form>
        <div class="mt-10">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('profile.change_password') }}</h2>
            <form wire:submit.prevent="updatePassword" class="space-y-6">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('profile.current_password') }}
                    </label>
                    <input wire:model="current_password" type="password" id="current_password" class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('current_password') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('profile.new_password') }}
                    </label>
                    <input wire:model="password" type="password" id="password" class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('password') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('profile.password_confirmation') }}
                    </label>
                    <input wire:model="password_confirmation" type="password" id="password_confirmation" class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('password_confirmation') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center justify-end gap-4">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary hover:bg-primary-hover rounded-lg transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        <span wire:loading.remove wire:target="updatePassword">{{ __('general.save') }}</span>
                        <span wire:loading wire:target="updatePassword">{{ __('general.saving') }}...</span>
                    </button>
                </div>
            </form>
        </div>

        @include('livewire.app.profile.activity-log', ['activities' => $activities])
    </div>
</div>
