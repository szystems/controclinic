<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class StaffPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_send_reset_link_to_staff()
    {
        Password::shouldReceive('sendResetLink')->once()->andReturn(Password::RESET_LINK_SENT);
        $clinic = Clinic::factory()->create();
        $owner = User::factory()->for($clinic)->create(['role' => 'owner']);
        $staff = User::factory()->for($clinic)->create(['role' => 'doctor']);
        $clinic->owner_id = $owner->id;
        $clinic->save();
        $this->actingAs($owner);
        Livewire::test('app.staff.edit', ['user' => $staff->id])
            ->call('sendResetPasswordLink')
            ->assertSet('resetLinkSent', true);
    }

    public function test_non_owner_cannot_send_reset_link()
    {
        Password::shouldReceive('sendResetLink')->never();
        $clinic = Clinic::factory()->create();
        $owner = User::factory()->for($clinic)->create(['role' => 'owner']);
        $staff = User::factory()->for($clinic)->create(['role' => 'doctor']);
        $otherStaff = User::factory()->for($clinic)->create(['role' => 'assistant']);
        $clinic->owner_id = $owner->id;
        $clinic->save();
        // staff (doctor) tries to send reset to another member — not allowed (only owner can)
        $this->actingAs($staff);
        Livewire::test('app.staff.edit', ['user' => $otherStaff->id])
            ->call('sendResetPasswordLink')
            ->assertNotSet('resetLinkSent', true);
    }

    public function test_owner_cannot_send_reset_link_to_self()
    {
        Password::shouldReceive('sendResetLink')->never();
        $clinic = Clinic::factory()->create();
        $owner = User::factory()->for($clinic)->create(['role' => 'owner']);
        $clinic->owner_id = $owner->id;
        $clinic->save();
        $this->actingAs($owner);
        Livewire::test('app.staff.edit', ['user' => $owner->id])
            ->call('sendResetPasswordLink')
            ->assertNotSet('resetLinkSent', true);
    }
}
