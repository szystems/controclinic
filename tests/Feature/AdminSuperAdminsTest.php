<?php

namespace Tests\Feature;

use App\Livewire\Admin\Profile\Index as AdminProfile;
use App\Livewire\Admin\Users\Create;
use App\Livewire\Admin\Users\Edit;
use App\Livewire\Admin\Users\Index;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSuperAdminsTest extends TestCase
{
    use RefreshDatabase;

    private function createSuperAdmin(array $attrs = []): User
    {
        $clinic = Clinic::factory()->onboarded()->create();

        return User::factory()->create(array_merge([
            'clinic_id' => $clinic->id,
            'is_super_admin' => true,
        ], $attrs));
    }

    public function test_super_admins_index_lists_only_super_admins(): void
    {
        $admin = $this->createSuperAdmin(['name' => 'Platform Admin']);
        $clinic = Clinic::factory()->onboarded()->create();
        User::factory()->create(['clinic_id' => $clinic->id, 'is_super_admin' => false, 'name' => 'Clinic User']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->assertSee('Platform Admin')
            ->assertDontSee('Clinic User');
    }

    public function test_super_admin_can_be_created(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(Create::class)
            ->set('name', 'New Admin')
            ->set('email', 'newadmin@controclinic.com')
            ->set('password', 'SecurePass123!')
            ->set('password_confirmation', 'SecurePass123!')
            ->call('save')
            ->assertRedirect(route('admin.users.index'));

        $created = User::where('email', 'newadmin@controclinic.com')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->is_super_admin);
        $this->assertTrue($created->is_active);
        $this->assertNotNull($created->email_verified_at);
    }

    public function test_super_admin_can_be_updated(): void
    {
        $admin = $this->createSuperAdmin();
        $target = User::factory()->create([
            'clinic_id' => null,
            'is_super_admin' => true,
            'name' => 'Other Admin',
            'email' => 'other@controclinic.com',
        ]);

        Livewire::actingAs($admin)
            ->test(Edit::class, ['user' => $target])
            ->set('name', 'Updated Admin')
            ->set('is_active', false)
            ->call('save')
            ->assertRedirect(route('admin.users.index'));

        $target->refresh();
        $this->assertEquals('Updated Admin', $target->name);
        $this->assertFalse($target->is_active);
    }

    public function test_super_admin_password_can_be_reset_by_another_admin(): void
    {
        $admin = $this->createSuperAdmin();
        $target = User::factory()->create([
            'clinic_id' => null,
            'is_super_admin' => true,
            'password' => Hash::make('OldPassword123!'),
        ]);

        Livewire::actingAs($admin)
            ->test(Edit::class, ['user' => $target])
            ->set('password', 'NewPassword123!')
            ->set('password_confirmation', 'NewPassword123!')
            ->call('save');

        $target->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $target->password));
    }

    public function test_super_admin_cannot_delete_self(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(Edit::class, ['user' => $admin])
            ->call('delete')
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
    }

    public function test_super_admin_cannot_delete_last_super_admin(): void
    {
        $admin = $this->createSuperAdmin();

        Livewire::actingAs($admin)
            ->test(Edit::class, ['user' => $admin])
            ->assertSet('user.id', $admin->id);

        $this->assertFalse($admin->can('delete', $admin));
    }

    public function test_super_admin_can_delete_when_more_than_one_exists(): void
    {
        $admin = $this->createSuperAdmin();
        $target = User::factory()->create([
            'clinic_id' => null,
            'is_super_admin' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(Edit::class, ['user' => $target])
            ->call('delete')
            ->assertRedirect(route('admin.users.index'));

        $this->assertSoftDeleted('users', ['id' => $target->id]);
    }

    public function test_admin_profile_updates_password(): void
    {
        $admin = $this->createSuperAdmin([
            'password' => Hash::make('CurrentPass123!'),
        ]);

        Livewire::actingAs($admin)
            ->test(AdminProfile::class)
            ->set('current_password', 'CurrentPass123!')
            ->set('password', 'NewSecure456!')
            ->set('password_confirmation', 'NewSecure456!')
            ->call('updatePassword');

        $admin->refresh();
        $this->assertTrue(Hash::check('NewSecure456!', $admin->password));
    }

    public function test_regular_user_cannot_access_super_admins_index(): void
    {
        $clinic = Clinic::factory()->onboarded()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id, 'is_super_admin' => false]);

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_non_super_admin_edit_route_returns_404(): void
    {
        $admin = $this->createSuperAdmin();
        $clinicUser = User::factory()->create(['clinic_id' => Clinic::factory()->create()->id, 'is_super_admin' => false]);

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $clinicUser))
            ->assertNotFound();
    }
}
