<?php

namespace App\Policies;

use App\Models\User;

class SuperAdminPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->is_super_admin;
    }

    public function create(User $actor): bool
    {
        return $actor->is_super_admin;
    }

    public function update(User $actor, User $superAdmin): bool
    {
        return $actor->is_super_admin && $superAdmin->is_super_admin;
    }

    public function delete(User $actor, User $superAdmin): bool
    {
        if (! $actor->is_super_admin || ! $superAdmin->is_super_admin) {
            return false;
        }

        if ($actor->id === $superAdmin->id) {
            return false;
        }

        return User::query()
            ->where('is_super_admin', true)
            ->count() > 1;
    }
}
