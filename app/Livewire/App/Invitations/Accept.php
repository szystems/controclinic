<?php

namespace App\Livewire\App\Invitations;

use App\Models\ClinicInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Accept extends Component
{
    public ClinicInvitation $invitation;

    public string $password = '';

    public string $password_confirmation = '';

    public bool $invalid = false;

    public function mount(string $token): void
    {
        $invitation = ClinicInvitation::where('token', $token)
            ->with(['clinic', 'inviter'])
            ->first();

        if (! $invitation) {
            $this->invalid = true;
            $this->invitation = new ClinicInvitation;

            return;
        }

        $this->invitation = $invitation;

        if ($invitation->isAccepted() || $invitation->isCancelled() || $invitation->isExpired()) {
            $this->invalid = true;
        }
    }

    protected function rules(): array
    {
        return [
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function accept(): void
    {
        if ($this->invalid || ! $this->invitation->isPending()) {
            session()->flash('error', __('invitations.invalid_token'));

            return;
        }

        $this->validate();

        // Check if user already exists in this clinic
        $exists = User::where('clinic_id', $this->invitation->clinic_id)
            ->where('email', $this->invitation->email)
            ->exists();

        if ($exists) {
            session()->flash('error', __('invitations.email_already_registered'));

            return;
        }

        DB::transaction(function () {
            $user = User::create([
                'name' => $this->invitation->name,
                'email' => $this->invitation->email,
                'password' => $this->password,
                'clinic_id' => $this->invitation->clinic_id,
                'role' => $this->invitation->role,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $user->assignRole($this->invitation->role);

            $this->invitation->update(['accepted_at' => now()]);
        });

        session()->flash('success', __('invitations.accept_success'));

        $this->redirect(route('login'), navigate: true);
    }

    public function render()
    {
        return view('livewire.app.invitations.accept')
            ->layout('layouts.guest');
    }
}
