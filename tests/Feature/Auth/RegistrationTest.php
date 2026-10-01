<?php

namespace Tests\Feature\Auth;

use App\Models\Clinic;
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

    public function test_array_fields_are_rejected_without_crashing(): void
    {
        $component = Volt::test('pages.auth.register');

        $component->set('email', ['not-an-email']);
        $component->assertHasErrors('email');
        $this->assertSame('', $component->get('email'));

        $component->set('name', ['not-a-name']);
        $component->assertHasErrors('name');
        $this->assertSame('', $component->get('name'));

        $component->set('terms_accepted', ['yes']);
        $component->assertHasNoErrors('terms_accepted');
        $this->assertFalse($component->get('terms_accepted'));
    }

    public function test_clinic_name_without_letters_gets_a_usable_slug(): void
    {
        Volt::test('pages.auth.register')
            ->set('clinic_name', '!!!')
            ->set('name', 'Owner')
            ->set('email', 'symbols@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('terms_accepted', true)
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('verification.notice', absolute: false));

        $clinic = Clinic::where('email', 'symbols@example.com')->first();

        $this->assertNotNull($clinic);
        $this->assertMatchesRegularExpression('/^clinica-[a-z0-9]{6}$/', $clinic->slug);
    }

    public function test_slug_skips_a_trashed_clinic_with_the_same_name(): void
    {
        $existing = Clinic::factory()->create(['slug' => 'acme']);
        $existing->delete();

        Volt::test('pages.auth.register')
            ->set('clinic_name', 'Acme')
            ->set('name', 'Owner')
            ->set('email', 'acme@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('terms_accepted', true)
            ->call('register')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clinics', [
            'email' => 'acme@example.com',
            'slug' => 'acme-1',
        ]);
    }
}
