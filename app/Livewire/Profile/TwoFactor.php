<?php

namespace App\Livewire\Profile;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use PragmaRX\Google2FA\Google2FA;

class TwoFactor extends Component
{
    public bool $showingQrCode = false;

    public bool $showingConfirmation = false;

    public bool $showingRecoveryCodes = false;

    public bool $showingDisableForm = false;

    public string $code = '';

    public string $confirmingPassword = '';

    public bool $twoFactorEnabled = false;

    public function mount(): void
    {
        $this->twoFactorEnabled = (bool) Auth::user()->two_factor_confirmed_at;
    }

    public function enableTwoFactor(): void
    {
        $user = Auth::user();

        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => json_encode($this->generateRecoveryCodes()),
            'two_factor_confirmed_at' => null,
            'two_factor_enabled' => false,
        ])->save();

        $this->showingQrCode = true;
        $this->showingConfirmation = true;
        $this->showingRecoveryCodes = false;
    }

    public function confirmTwoFactor(): void
    {
        $this->validate(['code' => 'required|string|digits:6']);

        $user = Auth::user();
        $google2fa = app(Google2FA::class);

        if (! $google2fa->verifyKey($user->two_factor_secret, $this->code)) {
            $this->addError('code', __('auth.2fa_invalid_code'));

            return;
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_enabled' => true,
        ])->save();

        $this->twoFactorEnabled = true;
        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = true;
        $this->code = '';
    }

    public function cancelEnable(): void
    {
        $user = Auth::user();

        if (! $user->two_factor_confirmed_at) {
            $user->forceFill([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_enabled' => false,
            ])->save();
        }

        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->code = '';
    }

    public function disableTwoFactor(): void
    {
        $user = Auth::user();

        if (! Hash::check($this->confirmingPassword, $user->password)) {
            $this->addError('confirmingPassword', __('auth.password'));

            return;
        }

        $user->forceFill([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        // Clear the 2FA session flag so the user isn't locked out
        session()->forget('two_factor_verified');

        $this->twoFactorEnabled = false;
        $this->showingDisableForm = false;
        $this->confirmingPassword = '';
    }

    public function showRecoveryCodes(): void
    {
        $this->showingRecoveryCodes = ! $this->showingRecoveryCodes;
    }

    public function regenerateRecoveryCodes(): void
    {
        Auth::user()->forceFill([
            'two_factor_recovery_codes' => json_encode($this->generateRecoveryCodes()),
        ])->save();

        $this->showingRecoveryCodes = true;
    }

    public function getQrCodeSvgProperty(): string
    {
        $user = Auth::user();

        if (! $user->two_factor_secret) {
            return '';
        }

        $google2fa = app(Google2FA::class);

        $url = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->two_factor_secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(192),
            new SvgImageBackEnd
        );

        return (new Writer($renderer))->writeString($url);
    }

    public function getRecoveryCodesProperty(): array
    {
        $raw = Auth::user()->two_factor_recovery_codes;

        if (! $raw) {
            return [];
        }

        return json_decode($raw, true) ?? [];
    }

    public function getTwoFactorSecretProperty(): string
    {
        return Auth::user()->two_factor_secret ?? '';
    }

    protected function generateRecoveryCodes(): array
    {
        return collect()->times(8, fn () => strtoupper(Str::random(5)).'-'.strtoupper(Str::random(5)))->toArray();
    }

    public function render()
    {
        return view('livewire.profile.two-factor');
    }
}
