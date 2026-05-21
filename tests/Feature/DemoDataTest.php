<?php

namespace Tests\Feature;

use App\Livewire\App\Dashboard\DemoDataBanner;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DemoDataTest extends TestCase
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

    // ──────────────────────────────────────────────────────────────────────
    // Artisan command
    // ──────────────────────────────────────────────────────────────────────

    #[Test]
    public function seed_demo_command_creates_demo_data(): void
    {
        [$clinic] = $this->makeClinicWithOwner();

        $exitCode = Artisan::call('clinic:seed-demo', ['clinic' => $clinic->slug]);

        $this->assertSame(0, $exitCode);
        $this->assertDatabaseHas('patients', ['clinic_id' => $clinic->id, 'is_demo' => true]);
        $this->assertSame(5, Patient::where('clinic_id', $clinic->id)->where('is_demo', true)->count());
        $this->assertSame(10, Appointment::where('clinic_id', $clinic->id)->where('is_demo', true)->count());
        $this->assertSame(3, MedicalRecord::where('clinic_id', $clinic->id)->where('is_demo', true)->count());
        $this->assertSame(2, Invoice::where('clinic_id', $clinic->id)->where('is_demo', true)->count());
        $this->assertSame(1, Prescription::where('clinic_id', $clinic->id)->where('is_demo', true)->count());
    }

    #[Test]
    public function seed_demo_command_fails_if_already_seeded(): void
    {
        [$clinic] = $this->makeClinicWithOwner();

        Artisan::call('clinic:seed-demo', ['clinic' => $clinic->slug]);
        $exitCode = Artisan::call('clinic:seed-demo', ['clinic' => $clinic->slug]);

        $this->assertSame(1, $exitCode);
        // Should not duplicate data
        $this->assertSame(5, Patient::where('clinic_id', $clinic->id)->where('is_demo', true)->count());
    }

    #[Test]
    public function seed_demo_command_fails_for_unknown_clinic(): void
    {
        $exitCode = Artisan::call('clinic:seed-demo', ['clinic' => 'no-existe']);

        $this->assertSame(1, $exitCode);
    }

    #[Test]
    public function clear_demo_command_removes_only_demo_data(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();

        // Seed demo data
        Artisan::call('clinic:seed-demo', ['clinic' => $clinic->slug]);

        // Create a real (non-demo) patient
        $realPatient = Patient::factory()->create([
            'clinic_id' => $clinic->id,
            'is_demo' => false,
        ]);

        // Clear demo
        $exitCode = Artisan::call('clinic:seed-demo', ['clinic' => $clinic->slug, '--clear' => true]);

        $this->assertSame(0, $exitCode);
        $this->assertSame(0, Patient::where('clinic_id', $clinic->id)->where('is_demo', true)->count());
        $this->assertSame(0, Appointment::where('clinic_id', $clinic->id)->where('is_demo', true)->count());
        // Real patient must survive
        $this->assertDatabaseHas('patients', ['id' => $realPatient->id]);
    }

    #[Test]
    public function is_demo_flag_is_false_by_default_in_models(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();

        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);

        $this->assertFalse((bool) $patient->fresh()->is_demo);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Livewire component
    // ──────────────────────────────────────────────────────────────────────

    #[Test]
    public function banner_shows_load_option_when_clinic_is_empty(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        Livewire::actingAs($owner)
            ->test(DemoDataBanner::class, ['clinic' => $clinic])
            ->assertSet('isEmpty', true)
            ->assertSet('hasDemo', false);
    }

    #[Test]
    public function banner_shows_active_warning_after_loading_demo(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        Livewire::actingAs($owner)
            ->test(DemoDataBanner::class, ['clinic' => $clinic])
            ->call('loadDemo')
            ->assertSet('hasDemo', true);

        $this->assertSame(5, Patient::where('clinic_id', $clinic->id)->where('is_demo', true)->count());
    }

    #[Test]
    public function banner_clears_demo_data_on_clear_demo_call(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();

        Artisan::call('clinic:seed-demo', ['clinic' => $clinic->slug]);

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        Livewire::actingAs($owner)
            ->test(DemoDataBanner::class, ['clinic' => $clinic])
            ->assertSet('hasDemo', true)
            ->call('clearDemo')
            ->assertSet('hasDemo', false);

        $this->assertSame(0, Patient::where('clinic_id', $clinic->id)->where('is_demo', true)->count());
    }
}
