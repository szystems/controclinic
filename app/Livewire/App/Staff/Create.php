<?php

namespace App\Livewire\App\Staff;

use App\Mail\ClinicInvitationMail;
use App\Models\Clinic;
use App\Models\ClinicInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class Create extends Component
{
    public Clinic $currentClinic;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $role = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:doctor,assistant,secretary,receptionist'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => __('validation.required', ['attribute' => __('staff.name')]),
            'email.required' => __('validation.required', ['attribute' => __('staff.email')]),
            'email.email' => __('validation.email', ['attribute' => __('staff.email')]),
            'role.required' => __('validation.required', ['attribute' => __('staff.role')]),
        ];
    }

    public function mount(Clinic $clinic): void
    {
        $this->currentClinic = $clinic;
    }

    public function save(): void
    {
        if (! auth()->user()->can('users.manage')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $this->validate();

        // Check if email already exists in this clinic
        $exists = User::where('clinic_id', $this->currentClinic->id)
            ->where('email', $this->email)
            ->exists();

        if ($exists) {
            $this->addError('email', __('staff.email_already_exists'));

            return;
        }

        // Check for duplicate pending invitation
        $pendingExists = ClinicInvitation::where('clinic_id', $this->currentClinic->id)
            ->where('email', $this->email)
            ->pending()
            ->exists();

        if ($pendingExists) {
            $this->addError('email', __('invitations.duplicate_pending'));

            return;
        }

        // Check plan limits
        if ($this->role === 'doctor') {
            if (! $this->currentClinic->canAddDoctor()) {
                session()->flash('error', __('staff.doctor_limit_reached'));

                return;
            }
        } else {
            if (! $this->currentClinic->canAddStaff()) {
                session()->flash('error', __('staff.staff_limit_reached'));

                return;
            }
        }

        $invitation = ClinicInvitation::create([
            'clinic_id' => $this->currentClinic->id,
            'email' => $this->email,
            'name' => $this->name,
            'role' => $this->role,
            'token' => ClinicInvitation::generateToken(),
            'invited_by' => auth()->id(),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($this->email)
            ->locale($this->currentClinic->locale ?? config('app.locale'))
            ->send(new ClinicInvitationMail($invitation));

        session()->flash('success', __('invitations.invitation_sent'));

        $this->redirect(
            route('app.staff.index', $this->currentClinic->slug),
            navigate: true
        );
    }

    public function render()
    {
        return view('livewire.app.staff.create')
            ->layout('layouts.app');
    }
}
