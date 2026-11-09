<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PlatformAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_closed_registration_does_not_create_a_clinic(): void
    {
        AppSetting::set('features.registration_open', false);

        Volt::test('pages.auth.register')
            ->set('clinic_name', 'Clinica Cerrada')
            ->set('name', 'Ana Ruiz')
            ->set('email', 'ana@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('terms_accepted', true)
            ->call('register')
            ->assertHasErrors(['email']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_maintenance_mode_lets_health_login_and_super_admin_through(): void
    {
        AppSetting::set('features.maintenance_mode', true);

        $this->get('/')->assertStatus(503);
        $this->get('/login')->assertOk();
        $this->get('/health')->assertOk();
        $this->get('/up')->assertOk();

        $clinic = Clinic::factory()->onboarded()->create();
        $admin = User::factory()->create([
            'clinic_id' => $clinic->id,
            'is_super_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }
}
