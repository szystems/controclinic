<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    /**
     * Cualquier acción sobre un paciente requiere mismo tenant + permiso de Spatie.
     */
    private function sameTenant(User $user, Patient $patient): bool
    {
        return $user->clinic_id === $patient->clinic_id;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('patients.view');
    }

    public function view(User $user, Patient $patient): bool
    {
        return $this->sameTenant($user, $patient) && $user->can('patients.view');
    }

    public function create(User $user): bool
    {
        return $user->can('patients.create');
    }

    public function update(User $user, Patient $patient): bool
    {
        return $this->sameTenant($user, $patient) && $user->can('patients.edit');
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $this->sameTenant($user, $patient) && $user->can('patients.delete');
    }

    public function export(User $user): bool
    {
        return $user->can('patients.export');
    }

    public function print(User $user): bool
    {
        return $user->can('patients.print');
    }
}
