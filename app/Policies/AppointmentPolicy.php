<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    private function sameTenant(User $user, Appointment $appointment): bool
    {
        return $user->clinic_id === $appointment->clinic_id;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('appointments.view');
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $this->sameTenant($user, $appointment) && $user->can('appointments.view');
    }

    public function create(User $user): bool
    {
        return $user->can('appointments.create');
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $this->sameTenant($user, $appointment) && $user->can('appointments.edit');
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $this->sameTenant($user, $appointment) && $user->can('appointments.delete');
    }

    public function export(User $user): bool
    {
        return $user->can('appointments.export');
    }

    public function print(User $user): bool
    {
        return $user->can('appointments.print');
    }
}
