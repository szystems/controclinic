<?php

namespace App\Livewire\App\Invitations;

use App\Models\ClinicInvitation;
use App\Models\User;
use Illuminate\Database\QueryException;
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

        // One email can belong to one account. A duplicate insert is a 500.
        $exists = User::where('email', $this->invitation->email)->exists();

        if ($exists) {
            session()->flash('error', __('invitations.email_already_registered'));

            return;
        }

        try {
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
        } catch (QueryException $e) {
            if ((string) $e->getCode() === '23000') {
                session()->flash('error', __('invitations.email_already_registered'));

                return;
            }

            throw $e;
        }

        session()->flash('success', __('invitations.accept_success'));

        $this->redirect(route('login'), navigate: true);
    }

    public function render()
    {
        return view('livewire.app.invitations.accept')
            ->layout('layouts.guest');
    }
}
