<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use PragmaRX\Google2FA\Google2FA;

new #[Layout('layouts.guest')] class extends Component
{
    public string $code = '';

    public string $recoveryCode = '';

    public bool $usingRecoveryCode = false;

    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: true);
        }

        // Already verified in this session
        if (session('two_factor_verified', false)) {
            $this->redirect(session()->pull('url.intended', route('dashboard')), navigate: false);
        }
    }

    public function challenge(): void
    {
        $user = Auth::user();

        if ($this->usingRecoveryCode) {
            $codes = json_decode($user->two_factor_recovery_codes, true) ?? [];
            $trimmed = trim($this->recoveryCode);
            $index = array_search($trimmed, $codes);

            if ($index === false) {
                $this->addError('recoveryCode', __('auth.2fa_invalid_recovery'));

                return;
            }

            // Invalidate the used recovery code (single use)
            unset($codes[$index]);
            $user->forceFill(['two_factor_recovery_codes' => json_encode(array_values($codes))])->save();
        } else {
            $this->validate(['code' => 'required|string|digits:6']);

            $google2fa = app(Google2FA::class);

            if (! $google2fa->verifyKey($user->two_factor_secret, $this->code)) {
                $this->addError('code', __('auth.2fa_invalid_code'));

                return;
            }
        }

        session(['two_factor_verified' => true]);

        $this->redirect(session()->pull('url.intended', route('dashboard')), navigate: false);
    }
}; ?>

<div>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        @if ($usingRecoveryCode)
            {{ __('auth.2fa_recovery_hint') }}
        @else
            {{ __('auth.2fa_hint') }}
        @endif
    </div>

    @if (! $usingRecoveryCode)
        <form wire:submit="challenge">
            <div>
                <x-input-label for="code" :value="__('auth.2fa_code')" />
                <x-text-input
                    wire:model="code"
                    id="code"
                    class="block mt-1 w-full"
                    type="text"
                    inputmode="numeric"
                    autofocus
                    autocomplete="one-time-code"
                    maxlength="6"
                />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <button type="button"
                    wire:click="$set('usingRecoveryCode', true)"
                    class="text-sm text-gray-600 dark:text-gray-400 underline hover:text-gray-900 dark:hover:text-gray-100">
                    {{ __('auth.2fa_use_recovery') }}
                </button>

                <x-primary-button class="ms-4">
                    {{ __('auth.2fa_verify') }}
                </x-primary-button>
            </div>
        </form>
    @else
        <form wire:submit="challenge">
            <div>
                <x-input-label for="recoveryCode" :value="__('auth.2fa_recovery_code')" />
                <x-text-input
                    wire:model="recoveryCode"
                    id="recoveryCode"
                    class="block mt-1 w-full"
                    type="text"
                    autofocus
                    autocomplete="one-time-code"
                />
                <x-input-error :messages="$errors->get('recoveryCode')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <button type="button"
                    wire:click="$set('usingRecoveryCode', false)"
                    class="text-sm text-gray-600 dark:text-gray-400 underline hover:text-gray-900 dark:hover:text-gray-100">
                    {{ __('auth.2fa_use_totp') }}
                </button>

                <x-primary-button class="ms-4">
                    {{ __('auth.2fa_verify') }}
                </x-primary-button>
            </div>
        </form>
    @endif
</div>
