<?php

namespace Tests\Feature;

use App\Livewire\App\Patients\Index as PatientsIndex;
use App\Livewire\App\Patients\Show as PatientsShow;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PatientTagsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function makeClinicWithUser(string $role = 'owner'): array
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id]);
        $user->assignRole($role);

        app()->instance('current_clinic', $clinic);
        view()->share('currentClinic', $clinic);

        return [$clinic, $user];
    }

    private function makeTag(Clinic $clinic, array $attrs = []): Tag
    {
        return Tag::create(array_merge([
            'clinic_id' => $clinic->id,
            'name' => 'VIP',
            'color' => 'purple',
            'category' => Tag::CATEGORY_PATIENT,
        ], $attrs));
    }

    // ==================== ASSIGN TAG ====================

    public function test_owner_can_assign_tag_to_patient(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $tag = $this->makeTag($clinic);

        Livewire::actingAs($owner)
            ->test(PatientsShow::class, ['patient' => $patient])
            ->call('toggleTag', $tag->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag->id,
            'taggable_id' => $patient->id,
            'taggable_type' => Patient::class,
        ]);
    }

    public function test_doctor_can_assign_tag_to_patient(): void
    {
        [$clinic, $doctor] = $this->makeClinicWithUser('doctor');
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $tag = $this->makeTag($clinic);

        Livewire::actingAs($doctor)
            ->test(PatientsShow::class, ['patient' => $patient])
            ->call('toggleTag', $tag->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('taggables', ['tag_id' => $tag->id, 'taggable_id' => $patient->id]);
    }

    public function test_assistant_can_assign_tag_to_patient(): void
    {
        [$clinic, $assistant] = $this->makeClinicWithUser('assistant');
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $tag = $this->makeTag($clinic);

        Livewire::actingAs($assistant)
            ->test(PatientsShow::class, ['patient' => $patient])
            ->call('toggleTag', $tag->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('taggables', ['tag_id' => $tag->id, 'taggable_id' => $patient->id]);
    }

    public function test_receptionist_cannot_manage_tags(): void
    {
        [$clinic, $receptionist] = $this->makeClinicWithUser('receptionist');
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $tag = $this->makeTag($clinic);

        Livewire::actingAs($receptionist)
            ->test(PatientsShow::class, ['patient' => $patient])
            ->call('toggleTag', $tag->id);

        $this->assertDatabaseMissing('taggables', ['tag_id' => $tag->id, 'taggable_id' => $patient->id]);
    }

    public function test_toggle_tag_detaches_if_already_assigned(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $tag = $this->makeTag($clinic);

        $patient->tags()->attach($tag->id, ['tagged_by' => $owner->id, 'tagged_at' => now()]);
        $this->assertDatabaseHas('taggables', ['tag_id' => $tag->id, 'taggable_id' => $patient->id]);

        Livewire::actingAs($owner)
            ->test(PatientsShow::class, ['patient' => $patient])
            ->call('toggleTag', $tag->id);

        $this->assertDatabaseMissing('taggables', ['tag_id' => $tag->id, 'taggable_id' => $patient->id]);
    }

    public function test_create_and_assign_creates_new_tag(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);

        Livewire::actingAs($owner)
            ->test(PatientsShow::class, ['patient' => $patient])
            ->set('newTagName', 'Alergia')
            ->set('newTagColor', 'red')
            ->call('createAndAssignTag')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tags', ['clinic_id' => $clinic->id, 'name' => 'Alergia']);
        $tag = Tag::where('name', 'Alergia')->first();
        $this->assertDatabaseHas('taggables', ['tag_id' => $tag->id, 'taggable_id' => $patient->id]);
    }

    public function test_filter_by_tag_returns_correct_patients(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        $tag = $this->makeTag($clinic, ['name' => 'VIP']);

        $patientWithTag = Patient::factory()->create(['clinic_id' => $clinic->id, 'first_name' => 'Tagged']);
        $patientWithout = Patient::factory()->create(['clinic_id' => $clinic->id, 'first_name' => 'NoTag']);

        $patientWithTag->tags()->attach($tag->id, ['tagged_by' => $owner->id, 'tagged_at' => now()]);

        Livewire::actingAs($owner)
            ->test(PatientsIndex::class, ['clinic' => $clinic])
            ->set('filterTag', (string) $tag->id)
            ->assertSee($patientWithTag->first_name)
            ->assertDontSee($patientWithout->first_name);
    }

    public function test_cannot_assign_tag_from_another_clinic(): void
    {
        [$clinic, $owner] = $this->makeClinicWithUser('owner');
        $otherClinic = Clinic::factory()->onboarded()->create();
        $patient = Patient::factory()->create(['clinic_id' => $clinic->id]);
        $foreignTag = $this->makeTag($otherClinic, ['name' => 'Foreign']);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($owner)
            ->test(PatientsShow::class, ['patient' => $patient])
            ->call('toggleTag', $foreignTag->id);
    }
}
