<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StaffOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_transfer_ownership_to_staff()
    {
        $clinic = Clinic::factory()->create();
        $owner = User::factory()->for($clinic)->create(['role' => 'owner']);
        $staff = User::factory()->for($clinic)->create(['role' => 'doctor']);
        $clinic->owner_id = $owner->id;
        $clinic->save();

        $this->actingAs($owner);

        Livewire::test('app.staff.edit', ['user' => $staff->id])
            ->call('transferOwnership')
            ->assertSet('ownershipTransferred', true);

        $clinic->refresh();
        $owner->refresh();
        $staff->refresh();

        $this->assertEquals($staff->id, $clinic->owner_id);
        $this->assertEquals('owner', $staff->role);
        $this->assertEquals('doctor', $owner->role);
    }

    public function test_non_owner_cannot_transfer_ownership()
    {
        $clinic = Clinic::factory()->create();
        $owner = User::factory()->for($clinic)->create(['role' => 'owner']);
        $staff = User::factory()->for($clinic)->create(['role' => 'doctor']);
        $otherStaff = User::factory()->for($clinic)->create(['role' => 'assistant']);
        $clinic->owner_id = $owner->id;
        $clinic->save();

        // staff (doctor) tries to transfer ownership editing another member — not allowed
        $this->actingAs($staff);

        Livewire::test('app.staff.edit', ['user' => $otherStaff->id])
            ->call('transferOwnership')
            ->assertNotSet('ownershipTransferred', true);

        $clinic->refresh();
        $this->assertEquals($owner->id, $clinic->owner_id);
    }

    public function test_owner_cannot_transfer_ownership_to_self()
    {
        $clinic = Clinic::factory()->create();
        $owner = User::factory()->for($clinic)->create(['role' => 'owner']);
        $clinic->owner_id = $owner->id;
        $clinic->save();

        $this->actingAs($owner);

        Livewire::test('app.staff.edit', ['user' => $owner->id])
            ->call('transferOwnership')
            ->assertNotSet('ownershipTransferred', true);

        $clinic->refresh();
        $this->assertEquals($owner->id, $clinic->owner_id);
    }
}
