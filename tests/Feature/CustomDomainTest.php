<?php

namespace Tests\Feature;

use App\Livewire\App\Settings\Index;
use App\Models\Clinic;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CustomDomainTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function makeEnterpriseClinicWithOwner(): array
    {
        $clinic = Clinic::factory()->create([
            'plan_type' => 'enterprise',
            'onboarding_completed_at' => now(),
        ]);
        $owner = User::factory()->create(['clinic_id' => $clinic->id]);
        $clinic->update(['owner_id' => $owner->id]);
        $owner->assignRole('owner');

        return [$clinic, $owner];
    }

    private function makeFreeClinicWithOwner(): array
    {
        $clinic = Clinic::factory()->create([
            'plan_type' => 'free',
            'is_manual_plan' => true,
            'onboarding_completed_at' => now(),
        ]);
        $owner = User::factory()->create(['clinic_id' => $clinic->id]);
        $clinic->update(['owner_id' => $owner->id]);
        $owner->assignRole('owner');

        return [$clinic, $owner];
    }

    #[Test]
    public function clinic_model_detects_custom_domain_not_verified(): void
    {
        [$clinic] = $this->makeEnterpriseClinicWithOwner();

        $clinic->update([
            'custom_domain' => 'reservas.example.com',
            'custom_domain_verified_at' => null,
        ]);

        $this->assertFalse($clinic->isCustomDomainVerified());
    }

    #[Test]
    public function clinic_model_detects_custom_domain_verified(): void
    {
        [$clinic] = $this->makeEnterpriseClinicWithOwner();

        $clinic->update([
            'custom_domain' => 'reservas.example.com',
            'custom_domain_verified_at' => now(),
        ]);

        $this->assertTrue($clinic->fresh()->isCustomDomainVerified());
    }

    #[Test]
    public function enterprise_owner_can_save_custom_domain(): void
    {
        [$clinic, $owner] = $this->makeEnterpriseClinicWithOwner();
        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->set('activeTab', 'public_page')
            ->set('customDomain', 'reservas.midinica.com')
            ->call('saveCustomDomain')
            ->assertHasNoErrors('customDomain');

        $this->assertDatabaseHas('clinics', [
            'id' => $clinic->id,
            'custom_domain' => 'reservas.midinica.com',
        ]);
    }

    #[Test]
    public function free_plan_cannot_save_custom_domain(): void
    {
        [$clinic, $owner] = $this->makeFreeClinicWithOwner();
        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->set('customDomain', 'reservas.midinica.com')
            ->call('saveCustomDomain')
            ->assertStatus(403);
    }

    #[Test]
    public function invalid_domain_format_fails_validation(): void
    {
        [$clinic, $owner] = $this->makeEnterpriseClinicWithOwner();
        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->set('activeTab', 'public_page')
            ->set('customDomain', 'not a valid domain!!')
            ->call('saveCustomDomain')
            ->assertHasErrors('customDomain');
    }

    #[Test]
    public function enterprise_owner_can_remove_custom_domain(): void
    {
        [$clinic, $owner] = $this->makeEnterpriseClinicWithOwner();

        $clinic->update([
            'custom_domain' => 'reservas.midinica.com',
            'custom_domain_verified_at' => now(),
            'custom_domain_txt_token' => 'controclinic-verify=abc123',
        ]);

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->set('activeTab', 'public_page')
            ->call('removeCustomDomain')
            ->assertSet('customDomain', '')
            ->assertSet('domainVerified', false)
            ->assertSet('domainTxtToken', null)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clinics', [
            'id' => $clinic->id,
            'custom_domain' => null,
            'custom_domain_verified_at' => null,
        ]);
    }

    #[Test]
    public function middleware_serves_booking_page_for_verified_custom_domain(): void
    {
        [$clinic] = $this->makeEnterpriseClinicWithOwner();

        $clinic->update([
            'custom_domain' => 'reservas.midinica.com',
            'custom_domain_verified_at' => now(),
            'public_portal_enabled' => true,
        ]);

        $response = $this->withServerVariables(['HTTP_HOST' => 'reservas.midinica.com'])
            ->get('/');

        // Should not redirect — serves the booking portal content
        $response->assertStatus(200);
    }

    #[Test]
    public function middleware_ignores_unverified_custom_domain(): void
    {
        [$clinic] = $this->makeEnterpriseClinicWithOwner();

        $clinic->update([
            'custom_domain' => 'reservas.midinica.com',
            'custom_domain_verified_at' => null, // Not verified
            'public_portal_enabled' => true,
        ]);

        $response = $this->withServerVariables(['HTTP_HOST' => 'reservas.midinica.com'])
            ->get('/');

        // Not verified — falls through to home page
        $response->assertStatus(200);
        // Home page is served, not the booking portal
        $response->assertViewIs('public.home');
    }
}
