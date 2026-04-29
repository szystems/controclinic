<?php

namespace Tests\Feature;

use App\Livewire\App\Invitations\Accept;
use App\Livewire\App\Staff\Create;
use App\Livewire\App\Staff\Index;
use App\Mail\ClinicInvitationMail;
use App\Models\Clinic;
use App\Models\ClinicInvitation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Mail::fake();
    }

    private function createClinicWithOwner(string $plan = 'free'): array
    {
        $clinic = Clinic::factory()->onboarded()->withPlan($plan)->create();
        $user = User::factory()->owner()->create(['clinic_id' => $clinic->id]);
        $user->assignRole('owner');
        app()->instance('current_clinic', $clinic);

        return [$clinic, $user];
    }

    private function createPendingInvitation(Clinic $clinic, User $inviter, array $overrides = []): ClinicInvitation
    {
        return ClinicInvitation::create(array_merge([
            'clinic_id' => $clinic->id,
            'email' => 'invited@test.com',
            'name' => 'Invited User',
            'role' => 'doctor',
            'token' => ClinicInvitation::generateToken(),
            'invited_by' => $inviter->id,
            'expires_at' => now()->addDays(7),
        ], $overrides));
    }

    // ===== Sending Invitations =====

    public function test_owner_can_send_invitation(): void
    {
        // group plan: max_doctors=5, owner occupies 1 slot — still can invite
        [$clinic, $owner] = $this->createClinicWithOwner('group');

        Livewire::actingAs($owner)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('name', 'Dr. New')
            ->set('email', 'dr.new@test.com')
            ->set('role', 'doctor')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('app.staff.index', $clinic->slug));

        $this->assertDatabaseHas('clinic_invitations', [
            'clinic_id' => $clinic->id,
            'email' => 'dr.new@test.com',
            'role' => 'doctor',
        ]);

        Mail::assertSent(ClinicInvitationMail::class, function ($mail) {
            return $mail->hasTo('dr.new@test.com');
        });
    }

    public function test_invitation_validates_required_fields(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        Livewire::actingAs($owner)
            ->test(Create::class, ['clinic' => $clinic])
            ->call('save')
            ->assertHasErrors(['name', 'email', 'role']);
    }

    public function test_cannot_invite_existing_clinic_member(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        User::factory()->doctor()->create([
            'clinic_id' => $clinic->id,
            'email' => 'existing@test.com',
        ]);

        Livewire::actingAs($owner)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('name', 'Existing')
            ->set('email', 'existing@test.com')
            ->set('role', 'doctor')
            ->call('save')
            ->assertHasErrors('email');

        Mail::assertNothingSent();
    }

    public function test_cannot_send_duplicate_pending_invitation(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $this->createPendingInvitation($clinic, $owner, [
            'email' => 'already@test.com',
        ]);

        Livewire::actingAs($owner)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('name', 'Duplicate')
            ->set('email', 'already@test.com')
            ->set('role', 'doctor')
            ->call('save')
            ->assertHasErrors('email');

        Mail::assertNothingSent();
    }

    public function test_cannot_invite_doctor_when_at_limit(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        // Solo plan: max_doctors = 1
        User::factory()->doctor()->create(['clinic_id' => $clinic->id]);

        Livewire::actingAs($owner)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('name', 'Extra Doctor')
            ->set('email', 'extra@test.com')
            ->set('role', 'doctor')
            ->call('save')
            ->assertNoRedirect();

        $this->assertDatabaseMissing('clinic_invitations', [
            'email' => 'extra@test.com',
        ]);

        Mail::assertNothingSent();
    }

    public function test_cannot_invite_staff_when_at_limit(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        // Solo plan: max_staff = 1
        User::factory()->assistant()->create(['clinic_id' => $clinic->id]);

        Livewire::actingAs($owner)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('name', 'Extra Staff')
            ->set('email', 'extra@test.com')
            ->set('role', 'assistant')
            ->call('save')
            ->assertNoRedirect();

        $this->assertDatabaseMissing('clinic_invitations', [
            'email' => 'extra@test.com',
        ]);

        Mail::assertNothingSent();
    }

    public function test_non_owner_cannot_send_invitation(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $doctor = User::factory()->doctor()->create(['clinic_id' => $clinic->id]);
        $doctor->assignRole('doctor');

        Livewire::actingAs($doctor)
            ->test(Create::class, ['clinic' => $clinic])
            ->set('name', 'Unauthorized')
            ->set('email', 'unauth@test.com')
            ->set('role', 'assistant')
            ->call('save')
            ->assertNoRedirect();

        $this->assertDatabaseMissing('clinic_invitations', ['email' => 'unauth@test.com']);
        Mail::assertNothingSent();
    }

    // ===== Accepting Invitations =====

    public function test_invitation_acceptance_page_renders(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $invitation = $this->createPendingInvitation($clinic, $owner);

        $this->get(route('invitations.accept', $invitation->token))
            ->assertOk();
    }

    public function test_expired_invitation_shows_invalid(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $invitation = $this->createPendingInvitation($clinic, $owner, [
            'expires_at' => now()->subDay(),
        ]);

        $this->get(route('invitations.accept', $invitation->token))
            ->assertOk()
            ->assertSee(__('invitations.invalid_token'));
    }

    public function test_user_can_accept_invitation(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $invitation = $this->createPendingInvitation($clinic, $owner, [
            'email' => 'newdoc@test.com',
            'name' => 'Dr. Accepted',
            'role' => 'doctor',
        ]);

        Livewire::test(Accept::class, ['token' => $invitation->token])
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('accept')
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'clinic_id' => $clinic->id,
            'email' => 'newdoc@test.com',
            'name' => 'Dr. Accepted',
            'role' => 'doctor',
        ]);

        $invitation->refresh();
        $this->assertNotNull($invitation->accepted_at);

        // Verify role was assigned
        $user = User::where('email', 'newdoc@test.com')->first();
        $this->assertTrue($user->hasRole('doctor'));
    }

    public function test_cannot_accept_expired_invitation(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $invitation = $this->createPendingInvitation($clinic, $owner, [
            'expires_at' => now()->subDay(),
        ]);

        Livewire::test(Accept::class, ['token' => $invitation->token])
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('accept');

        $this->assertDatabaseMissing('users', [
            'email' => 'invited@test.com',
        ]);
    }

    public function test_cannot_accept_cancelled_invitation(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $invitation = $this->createPendingInvitation($clinic, $owner, [
            'cancelled_at' => now(),
        ]);

        Livewire::test(Accept::class, ['token' => $invitation->token])
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('accept');

        $this->assertDatabaseMissing('users', [
            'email' => 'invited@test.com',
        ]);
    }

    public function test_cannot_accept_already_accepted_invitation(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $invitation = $this->createPendingInvitation($clinic, $owner, [
            'accepted_at' => now(),
        ]);

        Livewire::test(Accept::class, ['token' => $invitation->token])
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('accept');

        // Should not create a second user
        $this->assertEquals(0, User::where('email', 'invited@test.com')->count());
    }

    public function test_invalid_token_shows_error(): void
    {
        Livewire::test(Accept::class, ['token' => 'nonexistent-token'])
            ->assertSee(__('invitations.invalid_token'));
    }

    public function test_acceptance_validates_password(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $invitation = $this->createPendingInvitation($clinic, $owner);

        Livewire::test(Accept::class, ['token' => $invitation->token])
            ->call('accept')
            ->assertHasErrors(['password']);
    }

    // ===== Resend & Cancel =====

    public function test_owner_can_resend_invitation(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $invitation = $this->createPendingInvitation($clinic, $owner);
        $originalToken = $invitation->token;

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->call('resendInvitation', $invitation->id);

        $invitation->refresh();
        $this->assertNotEquals($originalToken, $invitation->token);
        $this->assertTrue($invitation->expires_at->isFuture());

        Mail::assertSent(ClinicInvitationMail::class);
    }

    public function test_owner_can_cancel_invitation(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $invitation = $this->createPendingInvitation($clinic, $owner);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->call('cancelInvitation', $invitation->id);

        $invitation->refresh();
        $this->assertNotNull($invitation->cancelled_at);
    }

    public function test_pending_invitations_shown_in_index(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $invitation = $this->createPendingInvitation($clinic, $owner, [
            'name' => 'Pending Person',
        ]);

        Livewire::actingAs($owner)
            ->test(Index::class, ['clinic' => $clinic])
            ->assertSee('Pending Person')
            ->assertSee(__('invitations.status_pending'));
    }

    public function test_non_owner_cannot_resend_invitation(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $doctor = User::factory()->doctor()->create(['clinic_id' => $clinic->id]);
        $doctor->assignRole('doctor');

        $invitation = $this->createPendingInvitation($clinic, $owner);
        $originalToken = $invitation->token;

        Livewire::actingAs($doctor)
            ->test(Index::class, ['clinic' => $clinic])
            ->call('resendInvitation', $invitation->id);

        $invitation->refresh();
        $this->assertEquals($originalToken, $invitation->token);
        Mail::assertNothingSent();
    }

    public function test_non_owner_cannot_cancel_invitation(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner('solo');

        $doctor = User::factory()->doctor()->create(['clinic_id' => $clinic->id]);
        $doctor->assignRole('doctor');

        $invitation = $this->createPendingInvitation($clinic, $owner);

        Livewire::actingAs($doctor)
            ->test(Index::class, ['clinic' => $clinic])
            ->call('cancelInvitation', $invitation->id);

        $invitation->refresh();
        $this->assertNull($invitation->cancelled_at);
    }

    // ===== Model Methods =====

    public function test_invitation_is_pending(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();

        $invitation = $this->createPendingInvitation($clinic, $owner);

        $this->assertTrue($invitation->isPending());
        $this->assertFalse($invitation->isExpired());
        $this->assertFalse($invitation->isAccepted());
        $this->assertFalse($invitation->isCancelled());
    }

    public function test_invitation_is_expired(): void
    {
        [$clinic, $owner] = $this->createClinicWithOwner();

        $invitation = $this->createPendingInvitation($clinic, $owner, [
            'expires_at' => now()->subDay(),
        ]);

        $this->assertFalse($invitation->isPending());
        $this->assertTrue($invitation->isExpired());
    }

    public function test_invitation_generate_token_is_unique(): void
    {
        $token1 = ClinicInvitation::generateToken();
        $token2 = ClinicInvitation::generateToken();

        $this->assertNotEquals($token1, $token2);
        $this->assertEquals(64, strlen($token1));
    }
}
