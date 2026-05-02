<?php

namespace Tests\Feature;

use App\Livewire\Profile\TwoFactor;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private function createOwner(): User
    {
        $clinic = Clinic::factory()->onboarded()->create();

        return User::factory()->owner()->create(['clinic_id' => $clinic->id]);
    }

    // -----------------------------------------------------------------------
    // Middleware & Challenge Flow
    // -----------------------------------------------------------------------

    /** @test */
    public function unauthenticated_user_is_not_redirected_to_2fa_challenge(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function user_without_2fa_enabled_accesses_app_normally(): void
    {
        $owner = $this->createOwner();

        $this->actingAs($owner)
            ->withSession(['two_factor_verified' => false])
            ->get(route('dashboard'))
            ->assertRedirect(); // Redirects to clinic dashboard, not 2FA challenge
    }

    /** @test */
    public function user_with_2fa_enabled_is_redirected_to_challenge(): void
    {
        $owner = $this->createOwner();
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $owner->forceFill([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->actingAs($owner)->get(route('dashboard'));

        $response->assertRedirect(route('two-factor.challenge'));
    }

    /** @test */
    public function user_with_2fa_verified_in_session_is_not_redirected(): void
    {
        $owner = $this->createOwner();
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $owner->forceFill([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->actingAs($owner)
            ->withSession(['two_factor_verified' => true])
            ->get(route('dashboard'));

        // Should NOT redirect to 2FA challenge (redirects to clinic dashboard instead)
        $this->assertNotEquals(route('two-factor.challenge'), $response->headers->get('Location'));
    }

    /** @test */
    public function challenge_page_validates_correct_totp_code(): void
    {
        $owner = $this->createOwner();
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $owner->forceFill([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => json_encode([]),
        ])->save();

        $validCode = $google2fa->getCurrentOtp($secret);

        $this->actingAs($owner);

        Livewire::test('pages.auth.two-factor-challenge')
            ->set('code', $validCode)
            ->call('challenge')
            ->assertHasNoErrors();

        $this->assertTrue(session('two_factor_verified', false));
    }

    /** @test */
    public function challenge_rejects_invalid_totp_code(): void
    {
        $owner = $this->createOwner();
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $owner->forceFill([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($owner);

        Livewire::test('pages.auth.two-factor-challenge')
            ->set('code', '000000')
            ->call('challenge')
            ->assertHasErrors(['code']);
    }

    /** @test */
    public function challenge_accepts_valid_recovery_code(): void
    {
        $owner = $this->createOwner();
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $codes = ['ABCDE-FGHIJ', 'AAAAA-BBBBB'];
        $owner->forceFill([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => json_encode($codes),
        ])->save();

        $this->actingAs($owner);

        Livewire::test('pages.auth.two-factor-challenge')
            ->set('usingRecoveryCode', true)
            ->set('recoveryCode', 'ABCDE-FGHIJ')
            ->call('challenge')
            ->assertHasNoErrors();

        $this->assertTrue(session('two_factor_verified', false));

        // Recovery code should be invalidated (single use)
        $owner->refresh();
        $remaining = json_decode($owner->two_factor_recovery_codes, true);
        $this->assertCount(1, $remaining);
        $this->assertNotContains('ABCDE-FGHIJ', $remaining);
    }

    // -----------------------------------------------------------------------
    // Profile TwoFactor Component
    // -----------------------------------------------------------------------

    /** @test */
    public function profile_2fa_component_renders_for_authenticated_user(): void
    {
        $owner = $this->createOwner();

        $this->actingAs($owner);

        Livewire::test(TwoFactor::class)
            ->assertSet('twoFactorEnabled', false)
            ->assertSee(__('auth.2fa_enable'));
    }

    /** @test */
    public function user_can_initiate_2fa_setup(): void
    {
        $owner = $this->createOwner();

        $this->actingAs($owner);

        Livewire::test(TwoFactor::class)
            ->call('enableTwoFactor')
            ->assertSet('showingQrCode', true)
            ->assertSet('showingConfirmation', true);

        $owner->refresh();
        $this->assertNotNull($owner->two_factor_secret);
        $this->assertNull($owner->two_factor_confirmed_at);
    }

    /** @test */
    public function user_can_confirm_2fa_with_valid_code(): void
    {
        $owner = $this->createOwner();
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $owner->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => json_encode([]),
        ])->save();

        $this->actingAs($owner);

        $validCode = $google2fa->getCurrentOtp($secret);

        Livewire::test(TwoFactor::class)
            ->set('showingQrCode', true)
            ->set('showingConfirmation', true)
            ->set('code', $validCode)
            ->call('confirmTwoFactor')
            ->assertSet('twoFactorEnabled', true)
            ->assertSet('showingRecoveryCodes', true)
            ->assertHasNoErrors();

        $owner->refresh();
        $this->assertNotNull($owner->two_factor_confirmed_at);
        $this->assertTrue($owner->two_factor_enabled);
    }

    /** @test */
    public function user_cannot_confirm_2fa_with_invalid_code(): void
    {
        $owner = $this->createOwner();
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $owner->forceFill(['two_factor_secret' => $secret])->save();

        $this->actingAs($owner);

        Livewire::test(TwoFactor::class)
            ->set('showingConfirmation', true)
            ->set('code', '000000')
            ->call('confirmTwoFactor')
            ->assertHasErrors(['code']);
    }

    /** @test */
    public function user_can_disable_2fa_with_correct_password(): void
    {
        $owner = $this->createOwner();
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $owner->forceFill([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($owner);

        Livewire::test(TwoFactor::class)
            ->set('showingDisableForm', true)
            ->set('confirmingPassword', 'password')
            ->call('disableTwoFactor')
            ->assertSet('twoFactorEnabled', false)
            ->assertHasNoErrors();

        $owner->refresh();
        $this->assertFalse($owner->two_factor_enabled);
        $this->assertNull($owner->two_factor_secret);
    }

    /** @test */
    public function user_cannot_disable_2fa_with_wrong_password(): void
    {
        $owner = $this->createOwner();
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $owner->forceFill([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($owner);

        Livewire::test(TwoFactor::class)
            ->set('showingDisableForm', true)
            ->set('confirmingPassword', 'wrong-password')
            ->call('disableTwoFactor')
            ->assertHasErrors(['confirmingPassword']);

        $owner->refresh();
        $this->assertTrue($owner->two_factor_enabled);
    }

    /** @test */
    public function user_can_regenerate_recovery_codes(): void
    {
        $owner = $this->createOwner();
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $originalCodes = json_encode(['OLD-CODE1', 'OLD-CODE2']);
        $owner->forceFill([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $originalCodes,
        ])->save();

        $this->actingAs($owner);

        Livewire::test(TwoFactor::class)
            ->call('regenerateRecoveryCodes')
            ->assertSet('showingRecoveryCodes', true);

        $owner->refresh();
        $newCodes = json_decode($owner->two_factor_recovery_codes, true);
        $this->assertCount(8, $newCodes);
        $this->assertNotContains('OLD-CODE1', $newCodes);
    }

    /** @test */
    public function canceling_setup_clears_unconfirmed_secret(): void
    {
        $owner = $this->createOwner();

        $this->actingAs($owner);

        Livewire::test(TwoFactor::class)
            ->call('enableTwoFactor')
            ->call('cancelEnable')
            ->assertSet('showingQrCode', false);

        $owner->refresh();
        $this->assertNull($owner->two_factor_secret);
    }
}
