<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-start justify-between mb-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ __('auth.2fa_title') }}
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('auth.2fa_description') }}
            </p>
        </div>
        <span @class([
            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
            'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' => $twoFactorEnabled,
            'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' => ! $twoFactorEnabled,
        ])>
            {{ $twoFactorEnabled ? __('auth.2fa_status_enabled') : __('auth.2fa_status_disabled') }}
        </span>
    </div>

    {{-- SETUP FLOW: QR Code display --}}
    @if ($showingQrCode)
        <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
            <p class="text-sm text-amber-800 dark:text-amber-200 mb-4">
                {{ __('auth.2fa_scan_qr') }}
            </p>

            <div class="flex justify-center mb-4">
                <div class="p-3 bg-white rounded-lg border border-gray-200 inline-block">
                    {!! $this->qrCodeSvg !!}
                </div>
            </div>

            <p class="text-xs text-center text-gray-500 dark:text-gray-400 mb-1">
                {{ __('auth.2fa_manual_key') }}
            </p>
            <p class="text-center font-mono text-sm font-semibold tracking-widest text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded">
                {{ $this->twoFactorSecret }}
            </p>
        </div>
    @endif

    {{-- SETUP FLOW: Confirm with code --}}
    @if ($showingConfirmation)
        <div class="mb-4">
            <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                {{ __('auth.2fa_confirm_label') }}
            </label>
            <div class="flex gap-2">
                <input wire:model="code"
                    id="code"
                    type="text"
                    inputmode="numeric"
                    maxlength="6"
                    placeholder="000000"
                    autocomplete="one-time-code"
                    class="block w-40 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm font-mono tracking-widest focus:ring-2 focus:ring-indigo-500">
                <button wire:click="confirmTwoFactor"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    {{ __('auth.2fa_confirm') }}
                </button>
                <button wire:click="cancelEnable"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none transition">
                    {{ __('general.cancel') }}
                </button>
            </div>
            @error('code')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    @endif

    {{-- RECOVERY CODES --}}
    @if ($showingRecoveryCodes && $twoFactorEnabled)
        <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('auth.2fa_recovery_codes') }}
                </p>
                <button wire:click="regenerateRecoveryCodes"
                    wire:confirm="{{ __('auth.2fa_regenerate_confirm') }}"
                    class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                    {{ __('auth.2fa_regenerate') }}
                </button>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                {{ __('auth.2fa_recovery_hint_save') }}
            </p>
            <div class="grid grid-cols-2 gap-1">
                @foreach ($this->recoveryCodes as $recoveryCode)
                    <code class="font-mono text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-2 py-1 rounded text-gray-800 dark:text-gray-200">
                        {{ $recoveryCode }}
                    </code>
                @endforeach
            </div>
        </div>
    @endif

    {{-- DISABLE FORM --}}
    @if ($showingDisableForm)
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <p class="text-sm text-red-800 dark:text-red-200 mb-3">
                {{ __('auth.2fa_disable_confirm') }}
            </p>
            <div class="flex gap-2 items-start">
                <div class="flex-1">
                    <input wire:model="confirmingPassword"
                        type="password"
                        placeholder="{{ __('auth.password') }}"
                        class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-red-500">
                    @error('confirmingPassword')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <button wire:click="disableTwoFactor"
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none transition">
                    {{ __('auth.2fa_disable') }}
                </button>
                <button wire:click="$set('showingDisableForm', false)"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none transition">
                    {{ __('general.cancel') }}
                </button>
            </div>
        </div>
    @endif

    {{-- ACTION BUTTONS --}}
    @if (! $showingConfirmation && ! $showingDisableForm)
        <div class="flex flex-wrap gap-2">
            @if (! $twoFactorEnabled)
                <button wire:click="enableTwoFactor"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    {{ __('auth.2fa_enable') }}
                </button>
            @else
                <button wire:click="showRecoveryCodes"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none transition">
                    {{ $showingRecoveryCodes ? __('auth.2fa_hide_codes') : __('auth.2fa_show_codes') }}
                </button>

                <button wire:click="$set('showingDisableForm', true)"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-red-300 dark:border-red-600 rounded-lg font-semibold text-xs text-red-600 dark:text-red-400 uppercase tracking-widest hover:bg-red-50 dark:hover:bg-red-900/30 focus:outline-none transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    {{ __('auth.2fa_disable') }}
                </button>
            @endif
        </div>
    @endif
</div>
