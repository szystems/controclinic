<?php

namespace App\Policies;

use App\Models\PatientFile;
use App\Models\User;

class PatientFilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('files.view');
    }

    public function view(User $user, PatientFile $file): bool
    {
        return $user->can('files.view')
            && $user->clinic_id === $file->clinic_id;
    }

    public function create(User $user): bool
    {
        return $user->can('files.upload');
    }

    public function delete(User $user, PatientFile $file): bool
    {
        return $user->can('files.delete')
            && $user->clinic_id === $file->clinic_id;
    }
}
