<?php

namespace Tests\Feature;

use App\Livewire\App\Onboarding\Index;
use App\Models\Clinic;
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

    private function makeClinicWithOwner(): array
    {
        $clinic = Clinic::factory()->create(['onboarding_completed_at' => null]);
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
}
