<?php

namespace Tests\Feature;

use App\Livewire\App\Settings\Index as SettingsIndex;
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

class SettingsPublicPageTest extends TestCase
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
        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->create(['clinic_id' => $clinic->id]);
        $clinic->update(['owner_id' => $owner->id]);
        $owner->assignRole('owner');

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        return [$clinic, $owner];
    }

    // ─── Render ──────────────────────────────────────────────────────────────

    #[Test]
    public function owner_can_render_settings_with_public_page_tab(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();

        Livewire::actingAs($owner)
            ->test(SettingsIndex::class, ['clinic' => $clinic])
            ->assertHasNoErrors()
            ->assertSet('activeTab', 'general');
    }

    // ─── Save public page ─────────────────────────────────────────────────────

    #[Test]
    public function owner_can_save_public_page_description_and_services(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();

        Livewire::actingAs($owner)
            ->test(SettingsIndex::class, ['clinic' => $clinic])
            ->set('public_description', 'Somos una clínica de prueba.')
            ->set('public_services', [
                ['title' => 'Consulta general', 'description' => 'Atención primaria', 'icon' => ''],
            ])
            ->call('savePublicPage')
            ->assertHasNoErrors();

        $clinic->refresh();
        $this->assertSame('Somos una clínica de prueba.', $clinic->public_description);
        $this->assertCount(1, $clinic->public_services);
        $this->assertSame('Consulta general', $clinic->public_services[0]['title']);
    }

    #[Test]
    public function owner_can_save_seo_fields(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();

        Livewire::actingAs($owner)
            ->test(SettingsIndex::class, ['clinic' => $clinic])
            ->set('public_seo_title', 'Mi clínica | Reserva online')
            ->set('public_seo_description', 'Reserva tu cita de forma fácil y rápida.')
            ->call('savePublicPage')
            ->assertHasNoErrors();

        $clinic->refresh();
        $this->assertSame('Mi clínica | Reserva online', $clinic->public_seo_title);
        $this->assertSame('Reserva tu cita de forma fácil y rápida.', $clinic->public_seo_description);
    }

    #[Test]
    public function owner_can_toggle_show_doctors(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();

        Livewire::actingAs($owner)
            ->test(SettingsIndex::class, ['clinic' => $clinic])
            ->set('public_show_doctors', false)
            ->call('savePublicPage')
            ->assertHasNoErrors();

        $this->assertFalse((bool) $clinic->fresh()->public_show_doctors);
    }

    #[Test]
    public function services_with_empty_title_are_filtered_out_on_save(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();

        Livewire::actingAs($owner)
            ->test(SettingsIndex::class, ['clinic' => $clinic])
            ->set('public_services', [
                ['title' => 'Radiología', 'description' => '', 'icon' => ''],
                ['title' => '',           'description' => 'vacío', 'icon' => ''],
            ])
            ->call('savePublicPage')
            ->assertHasNoErrors();

        $this->assertCount(1, $clinic->fresh()->public_services);
    }

    // ─── Validation ──────────────────────────────────────────────────────────

    #[Test]
    public function public_description_max_3000_chars_is_enforced(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();

        Livewire::actingAs($owner)
            ->test(SettingsIndex::class, ['clinic' => $clinic])
            ->set('public_description', str_repeat('a', 3001))
            ->call('savePublicPage')
            ->assertHasErrors(['public_description']);
    }

    #[Test]
    public function seo_title_max_70_chars_is_enforced(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();

        Livewire::actingAs($owner)
            ->test(SettingsIndex::class, ['clinic' => $clinic])
            ->set('public_seo_title', str_repeat('x', 71))
            ->call('savePublicPage')
            ->assertHasErrors(['public_seo_title']);
    }

    // ─── Permissions ─────────────────────────────────────────────────────────

    #[Test]
    public function non_owner_cannot_save_public_page(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $ownerUser = User::factory()->create(['clinic_id' => $clinic->id]);
        $clinic->update(['owner_id' => $ownerUser->id]);

        $doctor = User::factory()->create(['clinic_id' => $clinic->id]);
        $doctor->assignRole('doctor');

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        Livewire::actingAs($doctor)
            ->test(SettingsIndex::class, ['clinic' => $clinic])
            ->call('savePublicPage')
            ->assertForbidden();
    }

    // ─── Cover image ─────────────────────────────────────────────────────────

    #[Test]
    public function owner_can_upload_cover_image(): void
    {
        Storage::fake('public');
        [$clinic, $owner] = $this->makeClinicWithOwner();

        $file = UploadedFile::fake()->image('cover.jpg', 1200, 400);

        Livewire::actingAs($owner)
            ->test(SettingsIndex::class, ['clinic' => $clinic])
            ->set('public_cover_image', $file)
            ->call('savePublicPage')
            ->assertHasNoErrors();

        $this->assertNotNull($clinic->fresh()->public_cover_image_url);
        Storage::disk('public')->assertExists($clinic->fresh()->public_cover_image_url);
    }

    #[Test]
    public function owner_can_remove_cover_image(): void
    {
        Storage::fake('public');
        [$clinic, $owner] = $this->makeClinicWithOwner();

        // Simulate existing cover
        $path = 'clinics/'.$clinic->id.'/public/cover.jpg';
        Storage::disk('public')->put($path, 'fake-image');
        $clinic->update(['public_cover_image_url' => $path]);

        Livewire::actingAs($owner)
            ->test(SettingsIndex::class, ['clinic' => $clinic])
            ->call('removePublicCover')
            ->assertHasNoErrors();

        $this->assertNull($clinic->fresh()->public_cover_image_url);
        Storage::disk('public')->assertMissing($path);
    }

    #[Test]
    public function cover_image_must_be_an_image_file(): void
    {
        Storage::fake('public');
        [$clinic, $owner] = $this->makeClinicWithOwner();

        $file = UploadedFile::fake()->create('malware.pdf', 100, 'application/pdf');

        Livewire::actingAs($owner)
            ->test(SettingsIndex::class, ['clinic' => $clinic])
            ->set('public_cover_image', $file)
            ->call('savePublicPage')
            ->assertHasErrors(['public_cover_image']);
    }
}
