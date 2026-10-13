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
        $response->assertSee(__('profile.fields.name'));
    }

    public function test_profile_activity_hides_internal_fields(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->for($clinic)->create(['role' => 'owner']);
        $clinic->owner_id = $user->id;
        $clinic->save();

        Activity::create([
            'log_name' => 'default',
            'description' => 'created',
            'subject_type' => \App\Models\Invoice::class,
            'subject_id' => '01a123ca-2035-73b7-8763-369143f2a756',
            'causer_type' => User::class,
            'causer_id' => $user->id,
            'properties' => [
                'attributes' => [
                    'status' => 'pending',
                    'total' => '0.00',
                    'currency' => 'CAD',
                    'issued_at' => '2026-10-10T00:00:00.000000Z',
                    'clinic_id' => $clinic->id,
                    'patient_id' => '01a123ca-2035-73b7-8763-369143f2a756',
                    'created_by' => $user->id,
                    'is_demo' => null,
                    'notes' => null,
                ],
            ],
        ]);

        $this->actingAs($user)
            ->get(route('app.profile', ['clinic' => $clinic->slug]))
            ->assertOk()
            ->assertSee(__('profile.fields.status'))
            ->assertSee(__('invoices.status_pending'))
            ->assertSee('CAD')
            ->assertDontSee('clinic_id')
            ->assertDontSee('patient_id')
            ->assertDontSee('created_by')
            ->assertDontSee('is_demo');
    }
}
