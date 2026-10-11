<?php

namespace Tests\Feature;

use App\Livewire\App\Onboarding\Index;
use App\Models\Clinic;
use App\Models\Plan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function makeClinicWithOwner(array $clinicAttributes = []): array
    {
        $clinic = Clinic::factory()->create(array_merge(['onboarding_completed_at' => null], $clinicAttributes));
        $owner = User::factory()->create(['clinic_id' => $clinic->id]);
        $clinic->update(['owner_id' => $owner->id]);
        $owner->assignRole('owner');

        return [$clinic, $owner];
    }

    #[Test]
    public function onboarding_renders_step_1(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();
        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->assertStatus(200)
            ->assertSet('currentStep', 1)
            ->assertSee(__('onboarding.step_clinic'));
    }

    #[Test]
    public function owner_can_skip_step(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();
        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->set('currentStep', 2)
            ->call('skipStep')
            ->assertSet('currentStep', 3);
    }

    #[Test]
    public function skip_step_does_not_advance_past_last_step(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();
        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->set('currentStep', 5)
            ->call('skipStep')
            ->assertSet('currentStep', 5);
    }

    #[Test]
    public function owner_can_upload_logo_in_step_3(): void
    {
        Storage::fake('public');
        [$clinic, $owner] = $this->makeClinicWithOwner();
        app()->instance('current_clinic', $clinic);

        $logo = UploadedFile::fake()->image('logo.png', 200, 200);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->set('currentStep', 3)
            ->set('logo', $logo)
            ->call('nextStep')
            ->assertSet('currentStep', 4)
            ->assertSet('logo', null);

        $clinic->refresh();
        $this->assertNotNull($clinic->branding['logo'] ?? null);
        Storage::disk('public')->assertExists($clinic->branding['logo']);
    }

    #[Test]
    public function owner_can_remove_logo(): void
    {
        Storage::fake('public');
        [$clinic, $owner] = $this->makeClinicWithOwner();

        // Store a fake logo first
        $path = "clinics/{$clinic->id}/branding/logo.png";
        Storage::disk('public')->put($path, 'fake-content');
        $clinic->update(['branding' => ['logo' => $path, 'primary_color' => '#4f46e5', 'secondary_color' => '#10b981']]);

        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->assertSet('currentLogo', $path)
            ->call('removeLogo')
            ->assertSet('currentLogo', null);

        $clinic->refresh();
        $this->assertNull($clinic->branding['logo'] ?? null);
        Storage::disk('public')->assertMissing($path);
    }

    #[Test]
    public function logo_validation_rejects_non_image(): void
    {
        Storage::fake('public');
        [$clinic, $owner] = $this->makeClinicWithOwner();
        app()->instance('current_clinic', $clinic);

        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->set('currentStep', 3)
            ->set('logo', $file)
            ->call('nextStep')
            ->assertHasErrors(['logo']);
    }

    #[Test]
    public function owner_can_complete_onboarding(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();
        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->call('completeOnboarding');

        $clinic->refresh();
        $this->assertNotNull($clinic->onboarding_completed_at);
    }

    #[Test]
    public function owner_can_advance_from_step_1_to_step_2(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner([
            'country' => 'CA',
            'timezone' => 'America/Toronto',
            'currency' => 'CAD',
            'locale' => 'en',
        ]);
        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->set('phone_country', 'CA')
            ->set('phone_number', '6045551234')
            ->set('address', '123 Main St')
            ->set('city', 'Vancouver')
            ->set('country', 'CA')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('currentStep', 2)
            ->assertSet('timezone', 'America/Toronto');

        $clinic->refresh();
        $this->assertSame('CA', $clinic->country);
        $this->assertSame('+1 6045551234', $clinic->phone);
    }

    #[Test]
    public function doctor_cannot_open_or_change_onboarding(): void
    {
        [$clinic] = $this->makeClinicWithOwner(['city' => 'Langford']);
        $doctor = User::factory()->create(['clinic_id' => $clinic->id]);
        $doctor->assignRole('doctor');
        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($doctor)
            ->test(Index::class, ['clinic' => $clinic])
            ->assertForbidden();

        $clinic->refresh();
        $this->assertSame('Langford', $clinic->city);
        $this->assertNull($clinic->onboarding_completed_at);
    }

    #[Test]
    public function finished_onboarding_redirects_the_owner_to_the_dashboard(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner([
            'onboarding_completed_at' => now(),
            'city' => 'Langford',
        ]);
        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->assertRedirect(route('app.dashboard', $clinic->slug));

        $clinic->refresh();
        $this->assertSame('Langford', $clinic->city);
    }

    #[Test]
    public function plan_step_shows_public_plans_and_hides_a_private_plan_until_the_code_is_redeemed(): void
    {
        Plan::create(['name' => 'Free', 'slug' => 'free', 'sort_order' => 1, 'is_active' => true, 'is_free' => true]);
        Plan::create(['name' => 'Solo', 'slug' => 'solo', 'monthly_price' => '29.00', 'sort_order' => 2, 'is_active' => true]);
        Plan::create([
            'name' => 'Friendly',
            'slug' => 'friendly',
            'monthly_price' => '9.00',
            'sort_order' => 5,
            'is_active' => true,
            'is_private' => true,
            'requires_code' => true,
            'access_code' => 'CC-TEST',
        ]);

        [$clinic, $owner] = $this->makeClinicWithOwner(['plan_type' => 'free']);
        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->set('currentStep', 5)
            ->assertSee('Free')
            ->assertSee('Solo')
            ->assertSee(__('onboarding.continue_free'))
            ->assertDontSee('Friendly')
            ->set('promoCode', 'cc-test')
            ->call('redeemPromoCode')
            ->assertHasNoErrors()
            ->assertSee('Friendly');

        $this->assertTrue($clinic->fresh()->hasUnlockedPlan(Plan::where('slug', 'friendly')->first()));
    }

    #[Test]
    public function invalid_plan_code_does_not_unlock_a_private_plan(): void
    {
        Plan::create([
            'name' => 'Friendly',
            'slug' => 'friendly',
            'sort_order' => 5,
            'is_active' => true,
            'is_private' => true,
            'requires_code' => true,
            'access_code' => 'CC-TEST',
        ]);

        [$clinic, $owner] = $this->makeClinicWithOwner(['plan_type' => 'free']);
        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->set('promoCode', 'WRONG')
            ->call('redeemPromoCode')
            ->assertHasErrors(['promoCode']);

        $this->assertFalse($clinic->fresh()->hasUnlockedPlan(Plan::where('slug', 'friendly')->first()));
    }

    #[Test]
    public function finishing_onboarding_keeps_the_free_plan(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner(['plan_type' => 'free']);
        app()->instance('current_clinic', $clinic);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->set('selectedPlan', 'solo')
            ->call('completeOnboarding');

        $clinic->refresh();
        $this->assertNotNull($clinic->onboarding_completed_at);
        $this->assertSame('free', $clinic->plan_type);
        $this->assertArrayNotHasKey('desired_plan', $clinic->settings ?? []);
    }

    #[Test]
    public function choosing_a_paid_plan_without_a_price_does_not_change_the_free_plan(): void
    {
        Plan::create(['name' => 'Solo', 'slug' => 'solo', 'monthly_price' => '29.00', 'sort_order' => 2, 'is_active' => true]);

        [$clinic, $owner] = $this->makeClinicWithOwner(['plan_type' => 'free']);
        app()->instance('current_clinic', $clinic);

        config([
            'cashier.api_key' => 'test-key',
            'cashier.client_side_token' => 'test-token',
            'cashier.prices.solo.monthly' => null,
        ]);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->call('selectPlan', 'solo')
            ->assertNotDispatched('open-paddle-checkout')
            ->assertSet('selectedPlan', 'free');

        $clinic->refresh();
        $this->assertSame('free', $clinic->plan_type);
        $this->assertNull($clinic->onboarding_completed_at);
    }
}
