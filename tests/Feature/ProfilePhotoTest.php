<?php

namespace Tests\Feature;

use App\Livewire\App\Profile\Index;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_store_and_remove_a_profile_photo(): void
    {
        Storage::fake('public');

        $clinic = Clinic::factory()->onboarded()->create();
        $owner = User::factory()->create([
            'clinic_id' => $clinic->id,
            'role' => User::ROLE_OWNER,
            'name' => 'Otto Foto',
            'is_active' => true,
        ]);

        $this->actingAs($owner);

        Livewire::test(Index::class)
            ->set('photo', UploadedFile::fake()->image('foto.jpg', 200, 200))
            ->assertHasNoErrors();

        $owner->refresh();
        $path = $owner->avatar;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);

        Livewire::test(Index::class)
            ->call('removePhoto')
            ->assertHasNoErrors();

        $this->assertNull($owner->fresh()->avatar);
        Storage::disk('public')->assertMissing($path);
    }
}
