<?php

namespace App\Livewire\App\Staff;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Spatie\Permission\Models\Role;

// ...el resto del código de la clase...

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

    public bool $resetLinkSent = false;

    public bool $ownershipTransferred = false;

    public string $transferToId = '';

    /** Direct (extra) permissions assigned beyond role-inherited ones. */
    public array $extraPermissions = [];

    /**
     * Catalog of all manageable permissions grouped by module.
     * Used to render toggles in the Edit view.
     */
    public const PERMISSION_CATALOG = [
        'patients' => ['patients.view', 'patients.create', 'patients.edit', 'patients.delete', 'patients.view_all', 'patients.export', 'patients.print'],
        'appointments' => ['appointments.view', 'appointments.create', 'appointments.edit', 'appointments.delete', 'appointments.view_all', 'appointments.export', 'appointments.print'],
        'records' => ['records.view', 'records.create', 'records.edit', 'records.delete', 'records.view_confidential', 'records.print'],
        'settings' => ['settings.view', 'settings.edit'],
        'users' => ['users.manage', 'users.print'],
        'billing' => ['billing.manage'],
        'reports' => ['reports.view', 'reports.export'],
    ];

    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:owner,doctor,assistant,secretary,receptionist'],
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
            $user->clinic_id !== auth()->user()->clinic_id,
            404
        );
        // Solo el propio owner puede ver su perfil de edición; otros no pueden editarlo
        if ($user->isOwner() && auth()->id() !== $user->id) {
            abort(403);
        }
        $this->member = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->role = $user->role;
        $this->specialties = is_array($user->specialties) ? implode(', ', $user->specialties) : '';
        $this->license_number = $user->license_number ?? '';
        $this->bio = $user->bio ?? '';
        $this->is_active = $user->is_active;
        // Cargar permisos directos (extra), excluyendo los heredados del rol.
        $this->extraPermissions = $user->getDirectPermissions()->pluck('name')->toArray();
    }

    public function save(): void
    {
        if (! auth()->user()->can('users.manage')) {
            $this->dispatch('notify', type: 'error', message: __('general.unauthorized'));

            return;
        }
        // Si el miembro es owner, forzar que su rol siempre sea owner (no se puede cambiar)
        if ($this->member->isOwner()) {
            $this->role = 'owner';
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
                $this->dispatch('notify', type: 'error', message: __('staff.doctor_limit_reached'));

                return;
            }
        } elseif ($this->role !== 'doctor' && $this->member->role === 'doctor') {
            // Changing from doctor to staff role
            if (! in_array($this->member->role, ['assistant', 'secretary', 'receptionist'])) {
                if (! $clinic->canAddStaff()) {
                    $this->dispatch('notify', type: 'error', message: __('staff.staff_limit_reached'));

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
        // Sync direct (extra) permissions — limpia los que ya vienen del rol para no duplicar.
        $rolePerms = $this->member->getRoleNames()
            ->flatMap(fn ($r) => Role::findByName($r)->permissions->pluck('name'))
            ->all();
        $extras = array_values(array_intersect(
            array_diff($this->extraPermissions, $rolePerms),
            $this->allCatalogPermissions()
        ));
        $previousExtras = $this->member->getDirectPermissions()->pluck('name')->sort()->values()->all();
        $this->member->syncPermissions($extras);
        $this->extraPermissions = $extras;

        // Activity Log si cambiaron los permisos directos
        $newExtras = collect($extras)->sort()->values()->all();
        if ($previousExtras !== $newExtras) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($this->member)
                ->withProperties([
                    'previous_permissions' => $previousExtras,
                    'new_permissions' => $newExtras,
                ])
                ->log('permissions_updated');
        }

        session()->flash('success', __('staff.updated_successfully'));
        $this->dispatch('notify', type: 'success', message: __('staff.updated_successfully'));
        $this->redirect(
            route('app.staff.index', $clinic->slug),
            navigate: true
        );
    }

    /** Restaura los permisos extra al estado base del rol (elimina todos los directos). */
    public function restoreRolePermissions(): void
    {
        if (! auth()->user()->can('users.manage')) {
            $this->dispatch('notify', type: 'error', message: __('general.unauthorized'));

            return;
        }
        $previous = $this->member->getDirectPermissions()->pluck('name')->sort()->values()->all();
        $this->member->syncPermissions([]);
        $this->extraPermissions = [];

        if ($previous) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($this->member)
                ->withProperties([
                    'previous_permissions' => $previous,
                    'new_permissions' => [],
                ])
                ->log('permissions_restored');
        }

        $this->dispatch('notify', type: 'success', message: __('staff.permissions_restored'));
    }

    public function sendResetPasswordLink()
    {
        // Solo owner o admin pueden forzar reset
        $user = auth()->user();
        if (! ($user->isOwner() || $user->isAdmin())) {
            $this->dispatch('notify', type: 'error', message: __('general.unauthorized'));

            return;
        }
        // No permitir reset al owner a sí mismo
        if ($this->member->id === $user->id) {
            $this->dispatch('notify', type: 'error', message: __('staff.cannot_reset_self'));

            return;
        }
        try {
            PasswordBroker::sendResetLink(['email' => $this->member->email]);
            $this->resetLinkSent = true;
            $this->dispatch('notify', type: 'success', message: __('staff.reset_link_sent'));
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: __('staff.reset_link_failed'));
        }
    }

    public function transferOwnership()
    {
        $user = auth()->user();
        $clinic = $this->member->clinic;
        // Solo el owner actual puede transferir ownership
        if (! ($user->isOwner() && $clinic->owner_id === $user->id)) {
            $this->dispatch('notify', type: 'error', message: __('staff.only_owner_can_transfer'));

            return;
        }

        // Determinar destinatario: si editando su propio perfil, usar $transferToId
        if ($this->member->id === $user->id) {
            if (empty($this->transferToId)) {
                $this->dispatch('notify', type: 'error', message: __('staff.transfer_select_first'));

                return;
            }
            $target = User::where('clinic_id', $clinic->id)
                ->where('id', $this->transferToId)
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->first();
            if (! $target) {
                $this->dispatch('notify', type: 'error', message: __('general.unauthorized'));

                return;
            }
        } else {
            $target = $this->member;
            // Solo se puede transferir a un doctor
            if ($target->role !== 'doctor') {
                $this->dispatch('notify', type: 'error', message: __('staff.transfer_only_to_doctor'));

                return;
            }
        }

        if ($target->id === $user->id) {
            $this->dispatch('notify', type: 'error', message: __('staff.cannot_transfer_to_self'));

            return;
        }

        try {
            DB::transaction(function () use ($clinic, $user, $target) {
                // Cambiar owner_id en la clínica
                $clinic->owner_id = $target->id;
                $clinic->save();
                // Actualizar roles
                $user->role = 'doctor';
                $user->save();
                $user->syncRoles(['doctor']);
                $target->role = 'owner';
                $target->save();
                $target->syncRoles(['owner']);

                // Registro explícito en Activity Log
                activity()
                    ->causedBy($user)
                    ->performedOn($clinic)
                    ->withProperties([
                        'previous_owner_id' => $user->id,
                        'previous_owner_name' => $user->name,
                        'new_owner_id' => $target->id,
                        'new_owner_name' => $target->name,
                    ])
                    ->log('ownership_transferred');
            });
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: __('staff.transfer_failed'));

            return;
        }

        $this->ownershipTransferred = true;
        $this->dispatch('notify', type: 'success', message: __('staff.ownership_transferred'));
    }

    public function getTransferCandidatesProperty()
    {
        // Solo doctores activos pueden recibir la propiedad
        return User::where('clinic_id', $this->member->clinic_id)
            ->where('id', '!=', $this->member->id)
            ->where('is_active', true)
            ->where('role', 'doctor')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);
    }

    public function render()
    {
        return view('livewire.app.staff.edit', [
            'permissionCatalog' => self::PERMISSION_CATALOG,
            'rolePermissions' => $this->rolePermissions(),
        ])->layout('layouts.app');
    }

    /** Permisos asociados al rol actualmente seleccionado en el formulario. */
    public function rolePermissions(): array
    {
        $role = Role::where('name', $this->role)->first();

        return $role ? $role->permissions->pluck('name')->all() : [];
    }

    /** Lista plana de todos los permisos definidos en el catálogo. */
    private function allCatalogPermissions(): array
    {
        return array_merge(...array_values(self::PERMISSION_CATALOG));
    }
}
