<?php

namespace App\Policies;

use App\Models\RecordTemplate;
use App\Models\User;

class RecordTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('templates.manage') || $user->can('templates.use');
    }

    public function view(User $user, RecordTemplate $template): bool
    {
        return $user->clinic_id === $template->clinic_id
            && ($user->can('templates.manage') || $user->can('templates.use'));
    }

    public function create(User $user): bool
    {
        return $user->can('templates.manage');
    }

    public function update(User $user, RecordTemplate $template): bool
    {
        return $user->clinic_id === $template->clinic_id
            && $user->can('templates.manage');
    }

    public function delete(User $user, RecordTemplate $template): bool
    {
        return $user->clinic_id === $template->clinic_id
            && $user->can('templates.manage');
    }
}
