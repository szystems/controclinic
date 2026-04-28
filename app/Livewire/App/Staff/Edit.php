<?php

namespace App\Livewire\App\Staff;

use App\Models\User;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Edit extends Component
{
    public User $member;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $role = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $specialties = '';

    public string $license_number = '';

    public string $bio = '';

    public bool $is_active = true;

    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:doctor,assistant,secretary,receptionist'],
            'specialties' => ['nullable', 'string', 'max:500'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['boolean'],
        ];

        if ($this->password) {
            $rules['password'] = ['confirmed', Password::defaults()];
        }

        return $rules;
    }

    public function mount(User $user): void
    {
        // Tenant isolation: usuarios solo se editan dentro de su clínica
        abort_if(
            $user->clinic_id !== app('current_clinic')->id,
            404
        );

        $this->member = $user;

        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->role = $user->role;
        $this->specialties = is_array($user->specialties) ? implode(', ', $user->specialties) : '';
        $this->license_number = $user->license_number ?? '';
        $this->bio = $user->bio ?? '';
        $this->is_active = $user->is_active;
    }

    public function save(): void
    {
        if (! auth()->user()->can('users.manage')) {
            session()->flash('error', __('general.unauthorized'));

            return;
        }

        $this->validate();

        // Check if email already exists in this clinic (excluding current user)
        $exists = User::where('clinic_id', $this->member->clinic_id)
            ->where('email', $this->email)
            ->where('id', '!=', $this->member->id)
            ->exists();

        if ($exists) {
            $this->addError('email', __('staff.email_already_exists'));

            return;
        }

        // Check plan limits if role changed to doctor
        $clinic = $this->member->clinic;
        if ($this->role === 'doctor' && $this->member->role !== 'doctor') {
            if (! $clinic->canAddDoctor()) {
                session()->flash('error', __('staff.doctor_limit_reached'));

                return;
            }
        } elseif ($this->role !== 'doctor' && $this->member->role === 'doctor') {
            // Changing from doctor to staff role
            if (! in_array($this->member->role, ['assistant', 'secretary', 'receptionist'])) {
                if (! $clinic->canAddStaff()) {
                    session()->flash('error', __('staff.staff_limit_reached'));

                    return;
                }
            }
        }

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'role' => $this->role,
            'specialties' => $this->role === 'doctor' && $this->specialties
                ? array_map('trim', explode(',', $this->specialties))
                : null,
            'license_number' => $this->role === 'doctor' ? $this->license_number : null,
            'bio' => $this->bio ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = $this->password;
        }

        $this->member->update($data);

        // Sync role
        $this->member->syncRoles([$this->role]);

        session()->flash('success', __('staff.updated_successfully'));

        $this->redirect(
            route('app.staff.index', $clinic->slug),
            navigate: true
        );
    }

    public function render()
    {
        return view('livewire.app.staff.edit')
            ->layout('layouts.app');
    }
}
