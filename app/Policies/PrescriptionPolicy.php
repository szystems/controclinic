<?php

namespace App\Policies;

use App\Models\Prescription;
use App\Models\User;

class PrescriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('prescriptions.view');
    }

    public function view(User $user, Prescription $prescription): bool
    {
        return $user->can('prescriptions.view')
            && $user->clinic_id === $prescription->clinic_id;
    }

    public function create(User $user): bool
    {
        return $user->can('prescriptions.create');
    }

    public function update(User $user, Prescription $prescription): bool
    {
        return $user->can('prescriptions.edit')
            && $user->clinic_id === $prescription->clinic_id
            && $prescription->status === Prescription::STATUS_DRAFT;
    }

    public function delete(User $user, Prescription $prescription): bool
    {
        return $user->can('prescriptions.delete')
            && $user->clinic_id === $prescription->clinic_id
            && in_array($prescription->status, [Prescription::STATUS_DRAFT, Prescription::STATUS_CANCELLED]);
    }

    public function issue(User $user, Prescription $prescription): bool
    {
        return $user->can('prescriptions.edit')
            && $user->clinic_id === $prescription->clinic_id
            && $prescription->status === Prescription::STATUS_DRAFT;
    }

    public function cancel(User $user, Prescription $prescription): bool
    {
        return $user->can('prescriptions.edit')
            && $user->clinic_id === $prescription->clinic_id
            && in_array($prescription->status, [Prescription::STATUS_DRAFT, Prescription::STATUS_ISSUED]);
    }

    public function print(User $user, Prescription $prescription): bool
    {
        return $user->can('prescriptions.print')
            && $user->clinic_id === $prescription->clinic_id;
    }
}
