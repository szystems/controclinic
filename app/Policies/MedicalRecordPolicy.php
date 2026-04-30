<?php

namespace App\Policies;

use App\Models\MedicalRecord;
use App\Models\User;

class MedicalRecordPolicy
{
    private function sameTenant(User $user, MedicalRecord $record): bool
    {
        return $user->clinic_id === $record->clinic_id;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('records.view');
    }

    public function view(User $user, MedicalRecord $record): bool
    {
        if (! $this->sameTenant($user, $record) || ! $user->can('records.view')) {
            return false;
        }

        if ($record->is_confidential && ! $user->can('records.view_confidential')) {
            return false;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('records.create');
    }

    public function update(User $user, MedicalRecord $record): bool
    {
        return $this->sameTenant($user, $record) && $user->can('records.edit');
    }

    public function delete(User $user, MedicalRecord $record): bool
    {
        return $this->sameTenant($user, $record) && $user->can('records.delete');
    }

    public function print(User $user, MedicalRecord $record): bool
    {
        if (! $this->sameTenant($user, $record) || ! $user->can('records.print')) {
            return false;
        }

        if ($record->is_confidential && ! $user->can('records.view_confidential')) {
            return false;
        }

        return true;
    }
}
