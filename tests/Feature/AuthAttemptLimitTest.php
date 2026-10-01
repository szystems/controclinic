<?php

namespace Tests\Feature;

use App\Livewire\App\Appointments\Calendar;
use App\Livewire\App\Appointments\Index as AppointmentsIndex;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AuthAttemptLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Notification::fake();
    }

    public function test_registration_stops_after_three_attempts_from_the_same_ip(): void
    {
        foreach (range(1, 3) as $n) {
            auth()->logout();

            Volt::test('pages.auth.register')
                ->set('clinic_name', 'Clinic '.$n)
                ->set('name', 'Owner '.$n)
                ->set('email', "owner{$n}@example.com")
                ->set('password', 'password')
                ->set('password_confirmation', 'password')
                ->set('terms_accepted', true)
                ->call('register')
                ->assertHasNoErrors();
        }

        auth()->logout();

        $blocked = Volt::test('pages.auth.register')
            ->set('clinic_name', 'Clinic 4')
            ->set('name', 'Owner 4')
            ->set('email', 'owner4@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('terms_accepted', true)
            ->call('register');

        $blocked->assertHasErrors('email');
        $this->assertStringContainsString($this->throttlePrefix(), $blocked->errors()->first('email'));
        $this->assertDatabaseMissing('users', ['email' => 'owner4@example.com']);
    }

    public function test_password_reset_requests_stop_after_three_for_the_same_email(): void
    {
        $user = User::factory()->create();

        $form = Volt::test('pages.auth.forgot-password');

        foreach (range(1, 3) as $ignored) {
            $form->set('email', $user->email)->call('sendPasswordResetLink');
        }

        $form->set('email', $user->email)->call('sendPasswordResetLink');
        $this->assertStringContainsString($this->throttlePrefix(), (string) $form->errors()->first('email'));
    }

    public function test_password_reset_requests_stop_after_ten_from_the_same_ip(): void
    {
        $form = Volt::test('pages.auth.forgot-password');

        foreach (range(1, 10) as $n) {
            $form->set('email', "person{$n}@example.com")->call('sendPasswordResetLink');
        }

        $form->set('email', 'person11@example.com')->call('sendPasswordResetLink');
        $this->assertStringContainsString($this->throttlePrefix(), (string) $form->errors()->first('email'));
    }

    public function test_password_reset_form_stops_after_five_attempts_from_the_same_ip(): void
    {
        $form = Volt::test('pages.auth.reset-password', ['token' => 'not-a-real-token'])
            ->set('email', 'person@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        foreach (range(1, 5) as $ignored) {
            $form->call('resetPassword');
        }

        $form->call('resetPassword')->assertHasErrors('email');
        $this->assertStringContainsString($this->throttlePrefix(), $form->errors()->first('email'));
    }

    public function test_password_confirmation_stops_after_five_attempts(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $form = Volt::test('pages.auth.confirm-password')->set('password', 'wrong-password');

        foreach (range(1, 5) as $ignored) {
            $form->call('confirmPassword');
        }

        $form->call('confirmPassword')->assertHasErrors('password');
        $this->assertStringContainsString($this->throttlePrefix(), $form->errors()->first('password'));
    }

    public function test_two_factor_challenge_stops_after_five_attempts(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->owner()->create(['clinic_id' => $clinic->id]);
        $secret = app(Google2FA::class)->generateSecretKey();
        $owner->forceFill([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($owner);

        $form = Livewire::test('pages.auth.two-factor-challenge')->set('code', '000000');

        foreach (range(1, 5) as $ignored) {
            $form->call('challenge');
        }

        $form->call('challenge')->assertHasErrors('code');
        $this->assertStringContainsString($this->throttlePrefix(), $form->errors()->first('code'));
    }

    public function test_an_appointment_reminder_can_be_sent_once_every_ten_minutes(): void
    {
        Bus::fake();

        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->owner()->create(['clinic_id' => $clinic->id]);
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $appointment = Appointment::factory()->create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_id' => $owner->id,
        ]);
        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        Livewire::actingAs($owner)
            ->test(AppointmentsIndex::class, ['clinic' => $clinic])
            ->call('sendEmailReminder', $appointment->id)
            ->assertHasNoErrors();

        $second = Livewire::actingAs($owner)
            ->test(Calendar::class, ['clinic' => $clinic])
            ->instance()
            ->sendEmailReminder($appointment->id);

        $this->assertFalse($second['success']);
        $this->assertStringContainsString($this->throttlePrefix(), $second['message']);
    }

    private function throttlePrefix(): string
    {
        return explode(':seconds', __('auth.throttle'))[0];
    }
}
