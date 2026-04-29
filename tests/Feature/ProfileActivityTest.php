<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ProfileActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_activity_log_is_displayed()
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->for($clinic)->create(['role' => 'owner']);
        $clinic->owner_id = $user->id;
        $clinic->save();
        $activity = Activity::create([
            'log_name' => 'default',
            'description' => 'updated profile',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'causer_type' => User::class,
            'causer_id' => $user->id,
            'properties' => ['attributes' => ['name' => 'Nuevo Nombre']],
        ]);
        $this->actingAs($user);
        $response = $this->get(route('app.profile', ['clinic' => $clinic->slug]));
        $response->assertSee('updated profile');
        $response->assertSee('Nuevo Nombre');
    }
}
