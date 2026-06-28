<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Edit extends Component
{
    public User $user;

    public string $name = '';

    public string $email = '';

    public bool $is_active = true;

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(User $user): void
    {
        abort_unless($user->is_super_admin, 404);

        $this->authorize('update', $user);

        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->is_active = $user->is_active;
    }

    public function save(): void
    {
        $this->authorize('update', $this->user);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user->id)],
            'is_active' => ['boolean'],
        ];

        if ($this->password !== '') {
            $rules['password'] = ['required', 'string', Password::defaults(), 'confirmed'];
        }

        $validated = $this->validate($rules);

        $emailChanged = $this->user->email !== $validated['email'];

        $this->user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $validated['is_active'],
        ]);

        if ($emailChanged) {
            $this->user->markEmailAsVerified();
        }

        if (! empty($validated['password'] ?? null)) {
            $this->user->password = Hash::make($validated['password']);
        }

        $this->user->save();

        session()->flash('success', __('admin.super_admin_updated'));

        $this->redirect(route('admin.users.index'), navigate: true);
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->user);

        $this->user->delete();

        session()->flash('success', __('admin.super_admin_deleted'));

        $this->redirect(route('admin.users.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.users.edit', [
            'canDelete' => auth()->user()->can('delete', $this->user),
        ])->layout('layouts.admin');
    }
}
