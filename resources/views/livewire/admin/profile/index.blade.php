<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('admin.my_account') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('general.signed_in_as') }}</p>
                <p class="text-lg font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $user->email }}</p>
            </div>

            <form wire:submit="updatePassword" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('profile.change_password') }}</h3>

                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('profile.current_password') }}</label>
                    <input wire:model="current_password" type="password" id="current_password" autocomplete="current-password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('current_password') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('profile.new_password') }}</label>
                    <input wire:model="password" type="password" id="password" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('password') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('profile.password_confirmation') }}</label>
                    <input wire:model="password_confirmation" type="password" id="password_confirmation" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition">
                                        <span wire:loading.remove wire:target="updatePassword">{{ __('profile.change_password') }}</span>
                        <span wire:loading wire:target="updatePassword">{{ __('general.saving') }}...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
