<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings\Index as SettingsIndex;
use App\Models\AppSetting;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function createSuperAdmin(): User
    {
        $clinic = Clinic::factory()->onboarded()->create();

        return User::factory()->create([
            'clinic_id' => $clinic->id,
            'is_super_admin' => true,
        ]);
    }

    private function createRegularUser(): User
    {
        $clinic = Clinic::factory()->onboarded()->create();

        return User::factory()->create([
            'clinic_id' => $clinic->id,
            'is_super_admin' => false,
        ]);
    }

    // ==================== ACCESS CONTROL ====================

    public function test_regular_user_cannot_access_settings(): void
    {
        $this->actingAs($this->createRegularUser())
            ->get(route('admin.settings'))
            ->assertForbidden();
    }

    public function test_super_admin_can_access_settings(): void
    {
        $this->actingAs($this->createSuperAdmin())
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSeeLivewire(SettingsIndex::class);
    }

    // ==================== MODEL HELPERS ====================

    public function test_app_setting_get_returns_default_when_missing(): void
    {
        $value = AppSetting::get('nonexistent.key', 'fallback');

        $this->assertSame('fallback', $value);
    }

    public function test_app_setting_set_creates_record(): void
    {
        AppSetting::set('branding.app_name', 'TestApp');

        $this->assertDatabaseHas('app_settings', [
            'key' => 'branding.app_name',
            'group' => 'branding',
        ]);

        $this->assertSame('TestApp', AppSetting::get('branding.app_name'));
    }

    public function test_app_setting_set_updates_existing_record(): void
    {
        AppSetting::set('branding.app_name', 'First');
        AppSetting::set('branding.app_name', 'Second');

        $this->assertDatabaseCount('app_settings', 1);
        $this->assertSame('Second', AppSetting::get('branding.app_name'));
    }

    public function test_app_setting_set_tracks_updated_by(): void
    {
        $admin = $this->createSuperAdmin();

        AppSetting::set('branding.app_name', 'TestApp', $admin->id);

        $this->assertDatabaseHas('app_settings', [
            'key' => 'branding.app_name',
            'updated_by' => $admin->id,
        ]);
    }

    // ==================== LIVEWIRE COMPONENT ====================

    public function test_component_loads_with_default_values_when_no_settings_exist(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->assertSet('branding_app_name', 'ControClinic')
            ->assertSet('branding_primary_color', '#2563eb')
            ->assertSet('defaults_locale', 'es')
            ->assertSet('features_registration_open', true)
            ->assertSet('features_maintenance_mode', false);
    }

    public function test_component_loads_existing_settings(): void
    {
        AppSetting::set('branding.app_name', 'MiClinica');
        AppSetting::set('features.maintenance_mode', true);

        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->assertSet('branding_app_name', 'MiClinica')
            ->assertSet('features_maintenance_mode', true);
    }

    public function test_save_branding_persists_values(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('branding_app_name', 'NuevoNombre')
            ->set('branding_primary_color', '#ff5500')
            ->call('saveBranding')
            ->assertHasNoErrors();

        $this->assertSame('NuevoNombre', AppSetting::get('branding.app_name'));
        $this->assertSame('#ff5500', AppSetting::get('branding.primary_color'));
    }

    public function test_save_branding_validates_required_fields(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('branding_app_name', '')
            ->call('saveBranding')
            ->assertHasErrors(['branding_app_name' => 'required']);
    }

    public function test_save_branding_validates_color_format(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('branding_primary_color', 'invalid-color')
            ->call('saveBranding')
            ->assertHasErrors(['branding_primary_color']);
    }

    public function test_save_legal_persists_values(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('legal_support_email', 'test@example.com')
            ->set('legal_terms_url', '/terms')
            ->set('legal_privacy_url', '/privacy')
            ->call('saveLegal')
            ->assertHasNoErrors();

        $this->assertSame('test@example.com', AppSetting::get('legal.support_email'));
    }

    public function test_save_legal_validates_email(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('legal_support_email', 'not-an-email')
            ->call('saveLegal')
            ->assertHasErrors(['legal_support_email' => 'email']);
    }

    public function test_save_defaults_persists_locale(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('defaults_locale', 'en')
            ->set('defaults_timezone', 'UTC')
            ->set('defaults_currency', 'EUR')
            ->call('saveDefaults')
            ->assertHasNoErrors();

        $this->assertSame('en', AppSetting::get('defaults.locale'));
        $this->assertSame('EUR', AppSetting::get('defaults.currency'));
    }

    public function test_save_defaults_rejects_invalid_locale(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('defaults_locale', 'fr')
            ->call('saveDefaults')
            ->assertHasErrors(['defaults_locale']);
    }

    public function test_save_features_toggles_flags(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('features_maintenance_mode', true)
            ->set('features_ai_enabled', true)
            ->set('features_registration_open', false)
            ->call('saveFeatures')
            ->assertHasNoErrors();

        $this->assertTrue((bool) AppSetting::get('features.maintenance_mode'));
        $this->assertTrue((bool) AppSetting::get('features.ai_enabled'));
        $this->assertFalse((bool) AppSetting::get('features.registration_open'));
    }

    // ==================== LOGO UPLOAD ====================

    public function test_save_branding_accepts_png_logo_upload(): void
    {
        Storage::fake('public');

        $admin = $this->createSuperAdmin();
        $file = UploadedFile::fake()->image('logo.png', 200, 200);

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('logo_file', $file)
            ->call('saveBranding')
            ->assertHasNoErrors(['logo_file']);

        $storedUrl = AppSetting::get('branding.logo_url');
        $this->assertStringStartsWith('/storage/branding/', $storedUrl);
        Storage::disk('public')->assertExists('branding/'.basename($storedUrl));
    }

    public function test_save_branding_rejects_non_image_file(): void
    {
        Storage::fake('public');

        $admin = $this->createSuperAdmin();
        $file = UploadedFile::fake()->create('malware.php', 100, 'application/x-php');

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('logo_file', $file)
            ->call('saveBranding')
            ->assertHasErrors(['logo_file']);
    }

    public function test_save_branding_rejects_oversized_file(): void
    {
        Storage::fake('public');

        $admin = $this->createSuperAdmin();
        // 3 MB > límite de 2 MB
        $file = UploadedFile::fake()->image('big-logo.png')->size(3000);

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('logo_file', $file)
            ->call('saveBranding')
            ->assertHasErrors(['logo_file']);
    }

    public function test_remove_logo_clears_setting(): void
    {
        Storage::fake('public');

        $admin = $this->createSuperAdmin();
        // Simular un logo ya guardado como archivo local
        Storage::disk('public')->put('branding/logo.png', 'fake-content');
        AppSetting::set('branding.logo_url', '/storage/branding/logo.png');

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->call('removeLogo')
            ->assertHasNoErrors();

        $this->assertNull(AppSetting::get('branding.logo_url'));
        Storage::disk('public')->assertMissing('branding/logo.png');
    }

    public function test_uploading_new_logo_removes_old_local_file(): void
    {
        Storage::fake('public');

        $admin = $this->createSuperAdmin();
        Storage::disk('public')->put('branding/old-logo.png', 'old-content');
        AppSetting::set('branding.logo_url', '/storage/branding/old-logo.png');

        $newFile = UploadedFile::fake()->image('new-logo.png', 100, 100);

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('logo_file', $newFile)
            ->call('saveBranding')
            ->assertHasNoErrors();

        Storage::disk('public')->assertMissing('branding/old-logo.png');
        $newUrl = AppSetting::get('branding.logo_url');
        $this->assertStringStartsWith('/storage/branding/', $newUrl);
    }

    // ==================== FAVICON ====================

    public function test_favicon_png_upload_saves_url(): void
    {
        Storage::fake('public');

        $admin = $this->createSuperAdmin();
        $file = UploadedFile::fake()->image('icon.png', 32, 32);

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('branding_app_name', 'Demo')
            ->set('favicon_file', $file)
            ->call('saveBranding')
            ->assertHasNoErrors();

        $url = AppSetting::get('branding.favicon_url');
        $this->assertNotNull($url);
        $this->assertStringStartsWith('/storage/branding/', $url);
    }

    public function test_favicon_ico_upload_is_accepted(): void
    {
        Storage::fake('public');

        $admin = $this->createSuperAdmin();
        // .ico no es imagen estándar, creamos un fake con extensión ico
        $file = UploadedFile::fake()->create('favicon.ico', 1, 'image/x-icon');

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('branding_app_name', 'Demo')
            ->set('favicon_file', $file)
            ->call('saveBranding')
            ->assertHasNoErrors();
    }

    public function test_favicon_over_512kb_is_rejected(): void
    {
        Storage::fake('public');

        $admin = $this->createSuperAdmin();
        $file = UploadedFile::fake()->image('big-icon.png')->size(600); // 600 KB > 512 KB

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('favicon_file', $file)
            ->call('saveBranding')
            ->assertHasErrors(['favicon_file']);
    }

    public function test_remove_favicon_clears_setting_and_deletes_file(): void
    {
        Storage::fake('public');

        $admin = $this->createSuperAdmin();
        Storage::disk('public')->put('branding/favicon.png', 'fake-content');
        AppSetting::set('branding.favicon_url', '/storage/branding/favicon.png');

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->call('removeFavicon')
            ->assertHasNoErrors();

        $this->assertNull(AppSetting::get('branding.favicon_url'));
        Storage::disk('public')->assertMissing('branding/favicon.png');
    }

    // ==================== SEO ====================

    public function test_save_seo_persists_values(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('seo_meta_title', 'Mi Clínica — La mejor')
            ->set('seo_meta_description', 'Descripción de prueba con suficiente longitud para validar.')
            ->set('seo_google_analytics_id', 'G-ABC123XYZ')
            ->set('seo_gtm_id', 'GTM-ABCD123')
            ->call('saveSeo')
            ->assertHasNoErrors();

        $this->assertSame('Mi Clínica — La mejor', AppSetting::get('seo.meta_title'));
        $this->assertSame('G-ABC123XYZ', AppSetting::get('seo.google_analytics_id'));
        $this->assertSame('GTM-ABCD123', AppSetting::get('seo.gtm_id'));
    }

    public function test_save_seo_validates_required_meta_fields(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('seo_meta_title', '')
            ->set('seo_meta_description', '')
            ->call('saveSeo')
            ->assertHasErrors(['seo_meta_title', 'seo_meta_description']);
    }

    public function test_save_seo_validates_ga_id_format(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('seo_meta_title', 'Title')
            ->set('seo_meta_description', 'Description for SEO test long enough.')
            ->set('seo_google_analytics_id', 'invalid-id')
            ->call('saveSeo')
            ->assertHasErrors(['seo_google_analytics_id']);
    }

    public function test_save_seo_validates_gtm_id_format(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('seo_meta_title', 'Title')
            ->set('seo_meta_description', 'Description for SEO test long enough.')
            ->set('seo_gtm_id', 'wrong-format')
            ->call('saveSeo')
            ->assertHasErrors(['seo_gtm_id']);
    }

    public function test_save_seo_uploads_og_image(): void
    {
        Storage::fake('public');

        $admin = $this->createSuperAdmin();
        $file = UploadedFile::fake()->image('og.png', 1200, 630);

        Livewire::actingAs($admin)
            ->test(SettingsIndex::class)
            ->set('seo_meta_title', 'Title')
            ->set('seo_meta_description', 'Description for SEO test long enough.')
            ->set('og_image_file', $file)
            ->call('saveSeo')
            ->assertHasNoErrors();

        $url = AppSetting::get('seo.og_image_url');
        $this->assertNotNull($url);
        $this->assertStringStartsWith('/storage/branding/', $url);
        Storage::disk('public')->assertExists('branding/'.basename($url));
    }

    // ==================== HELPER FUNCTION ====================

    public function test_global_helper_app_setting_returns_value(): void
    {
        AppSetting::set('branding.app_name', 'HelperTest');

        $this->assertSame('HelperTest', app_setting('branding.app_name'));
    }

    public function test_global_helper_app_setting_returns_default_when_missing(): void
    {
        $this->assertSame('fallback', app_setting('nonexistent.key', 'fallback'));
    }

    // ==================== PUBLIC LAYOUT BRANDING ====================

    public function test_public_home_uses_dynamic_app_name(): void
    {
        AppSetting::set('branding.app_name', 'CustomBrand');
        AppSetting::clearCache();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('CustomBrand', false);
    }

    public function test_public_home_uses_dynamic_meta_description(): void
    {
        AppSetting::set('seo.meta_description', 'Mi descripción personalizada para SEO test.');
        AppSetting::clearCache();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Mi descripción personalizada para SEO test.', false);
    }

    public function test_public_home_includes_ga_when_configured(): void
    {
        AppSetting::set('seo.google_analytics_id', 'G-TESTID12345');
        AppSetting::clearCache();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('G-TESTID12345', false)
            ->assertSee('googletagmanager.com/gtag/js', false);
    }

    public function test_public_home_omits_ga_when_not_configured(): void
    {
        AppSetting::set('seo.google_analytics_id', null);
        AppSetting::clearCache();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('googletagmanager.com/gtag/js', false);
    }
}
