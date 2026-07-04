<?php

namespace Tests\Feature\Auth;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_new_users_can_register(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('clinic_name', 'Test Clinic')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('terms_accepted', true);

        $component->call('register');

        $component->assertRedirect(route('verification.notice', absolute: false));

        $this->assertAuthenticated();

        $this->assertDatabaseHas('clinics', ['name' => 'Test Clinic']);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_registration_applies_detected_locale_defaults(): void
    {
        $this->withHeaders([
            'CF-IPCountry' => 'CA',
            'Accept-Language' => 'en-CA,en;q=0.9',
        ]);

        Volt::test('pages.auth.register')
            ->set('clinic_name', 'Canadian Clinic')
            ->set('name', 'Canadian Owner')
            ->set('email', 'canada@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('terms_accepted', true)
            ->call('register');

        $this->assertDatabaseHas('clinics', [
            'name' => 'Canadian Clinic',
            'country' => 'CA',
            'currency' => 'CAD',
            'locale' => 'en',
        ]);
    }
}
