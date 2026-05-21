<?php

namespace Tests\Feature;

use App\Livewire\App\Tour\Launcher;
use App\Models\Clinic;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TourTest extends TestCase
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

    // ─── Auto-start ──────────────────────────────────────────────────────────

    #[Test]
    public function tour_auto_starts_for_new_user_without_preferences(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();
        // New user has no preferences set — tour should auto-start
        $owner->update(['preferences' => []]);

        $component = Livewire::actingAs($owner)
            ->test(Launcher::class);

        $component->assertSet('autoStart', true);
        $component->assertSet('role', 'owner');
    }

    #[Test]
    public function tour_does_not_auto_start_if_already_completed(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();
        $owner->update(['preferences' => ['tour_completed_at' => now()->toIso8601String()]]);

        $component = Livewire::actingAs($owner)
            ->test(Launcher::class);

        $component->assertSet('autoStart', false);
    }

    #[Test]
    public function tour_does_not_auto_start_if_already_skipped(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();
        $owner->update(['preferences' => ['tour_skipped_at' => now()->toIso8601String()]]);

        Livewire::actingAs($owner)
            ->test(Launcher::class)
            ->assertSet('autoStart', false);
    }

    // ─── Complete ────────────────────────────────────────────────────────────

    #[Test]
    public function complete_tour_persists_timestamp_in_preferences(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();
        $owner->update(['preferences' => []]);

        Livewire::actingAs($owner)
            ->test(Launcher::class)
            ->call('completeTour');

        $owner->refresh();
        $this->assertNotEmpty($owner->preferences['tour_completed_at'] ?? null);
    }

    // ─── Skip ────────────────────────────────────────────────────────────────

    #[Test]
    public function skip_tour_persists_timestamp_in_preferences(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();
        $owner->update(['preferences' => []]);

        Livewire::actingAs($owner)
            ->test(Launcher::class)
            ->call('skipTour');

        $owner->refresh();
        $this->assertNotEmpty($owner->preferences['tour_skipped_at'] ?? null);
        // Once skipped, autoStart should be false
        $this->assertFalse(
            Livewire::actingAs($owner)->test(Launcher::class)->get('autoStart')
        );
    }

    // ─── Replay ──────────────────────────────────────────────────────────────

    #[Test]
    public function replay_tour_clears_timestamps_and_sets_auto_start(): void
    {
        [$clinic, $owner] = $this->makeClinicWithOwner();
        $owner->update([
            'preferences' => [
                'tour_completed_at' => now()->toIso8601String(),
                'tour_skipped_at' => now()->toIso8601String(),
            ],
        ]);

        Livewire::actingAs($owner)
            ->test(Launcher::class)
            ->assertSet('autoStart', false)
            ->call('replayTour')
            ->assertSet('autoStart', true);
        // Note: JS (handleReplay in tour.js) is responsible for starting the tour,
        // not the PHP component — so no 'startTour' dispatch expected here.

        $owner->refresh();
        $this->assertArrayNotHasKey('tour_completed_at', $owner->preferences ?? []);
        $this->assertArrayNotHasKey('tour_skipped_at', $owner->preferences ?? []);
    }
}
