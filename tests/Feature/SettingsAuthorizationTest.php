<?php

namespace Tests\Feature;

use App\Livewire\App\Settings\Index;
use App\Models\Clinic;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function clinicWith(string $role): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id, 'role' => $role]);
        $user->assignRole($role);
        $clinic->update(['owner_id' => $role === 'owner' ? $user->id : User::factory()->create(['clinic_id' => $clinic->id, 'role' => 'owner'])->id]);

        return [$clinic, $user];
    }

    public function test_receptionist_cannot_open_settings(): void
    {
        [$clinic, $user] = $this->clinicWith('receptionist');

        Livewire::actingAs($user)
            ->test(Index::class, ['clinic' => $clinic])
            ->assertForbidden();
    }

    public function test_doctor_can_view_settings_but_cannot_save_them(): void
    {
        [$clinic, $user] = $this->clinicWith('doctor');

        Livewire::actingAs($user)
            ->test(Index::class, ['clinic' => $clinic])
            ->call('saveGeneral')
            ->assertForbidden();
    }

    public function test_owner_can_save_general_settings(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->create(['clinic_id' => $clinic->id, 'role' => 'owner']);
        $owner->assignRole('owner');
        $clinic->update(['owner_id' => $owner->id]);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->set('name', 'Clinica Autorizada')
            ->call('saveGeneral')
            ->assertHasNoErrors();

        $this->assertSame('Clinica Autorizada', $clinic->fresh()->name);
    }
}
