<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_profile_route_redirects_to_clinic_profile(): void
    {
        $clinic = Clinic::factory()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertRedirect(route('app.profile', ['clinic' => $clinic->slug]));
    }
}
