<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('admin.edit_super_admin') }}: {{ $user->name }}
            </h2>
            <a href="{{ route('admin.users.index') }}" wire:navigate
               class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                ← {{ __('admin.back_to_super_admins') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form wire:submit="save" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.name') }}</label>
                    <input type="text" wire:model="name" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.email') }}</label>
                    <input type="email" wire:model="email" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model="is_active" id="is_active" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">{{ __('admin.account_active') }}</label>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">{{ __('admin.reset_password') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('admin.reset_password_help') }}</p>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.new_password') }}</label>
                            <input type="password" wire:model="password" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('admin.confirm_password') }}</label>
                            <input type="password" wire:model="password_confirmation" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition">
                        <span wire:loading.remove wire:target="save">{{ __('general.save') }}</span>
                        <span wire:loading wire:target="save">{{ __('general.saving') }}...</span>
                    </button>
                </div>
            </form>

            @if($canDelete)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 border border-red-200 dark:border-red-900/50">
                    <h3 class="text-sm font-medium text-red-700 dark:text-red-400 mb-2">{{ __('admin.delete_super_admin') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ __('admin.delete_super_admin_help') }}</p>
                    <button type="button" wire:click="delete" wire:confirm="{{ __('admin.confirm_delete_super_admin') }}"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition">
                        {{ __('admin.delete_super_admin') }}
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
