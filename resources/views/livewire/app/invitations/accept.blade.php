<div>
    @if($invalid)
        {{-- Invalid/Expired Invitation --}}
        <div class="text-center py-4">
            <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
            <h2 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
                {{ __('invitations.invalid_token') }}
            </h2>
            <div class="mt-6">
                <a href="{{ route('login') }}"
                   class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">
                    {{ __('general.login') }} &rarr;
                </a>
            </div>
        </div>
    @else
        {{-- Valid Invitation - Accept Form --}}
        <div class="mb-6 text-center">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ __('invitations.accept_title') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('invitations.accept_subtitle', ['clinic' => $invitation->clinic->name]) }}
            </p>
        </div>

        {{-- Invitation Details --}}
        <div class="mb-6 rounded-lg bg-gray-50 dark:bg-gray-700/50 p-4">
            <div class="text-sm">
                <p class="font-medium text-gray-900 dark:text-white">{{ $invitation->name }}</p>
                <p class="text-gray-500 dark:text-gray-400">{{ $invitation->email }}</p>
                <p class="mt-2 text-gray-600 dark:text-gray-300">
                    {{ __('invitations.accept_welcome', ['role' => __('staff.role_' . $invitation->role)]) }}
                </p>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/50 p-4">
            <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
        </div>
        @endif

        <form wire:submit="accept">
            {{-- Password --}}
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('invitations.accept_set_password') }}
                </label>
                <input wire:model="password" type="password" id="password"
                       class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('staff.password_help') }}</p>
                @error('password') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            {{-- Password Confirmation --}}
            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('staff.password_confirmation') }}
                </label>
                <input wire:model="password_confirmation" type="password" id="password_confirmation"
                       class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full px-4 py-2 text-sm font-medium text-white bg-primary hover:bg-primary-hover rounded-lg transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                <span wire:loading.remove wire:target="accept">{{ __('invitations.accept_button') }}</span>
                <span wire:loading wire:target="accept">{{ __('general.loading') }}</span>
            </button>
        </form>
    @endif
</div>
