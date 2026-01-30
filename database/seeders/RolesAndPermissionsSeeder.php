<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Crear roles y permisos del sistema
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ==================== PERMISSIONS ====================

        // Pacientes
        Permission::create(['name' => 'patients.view']);
        Permission::create(['name' => 'patients.create']);
        Permission::create(['name' => 'patients.edit']);
        Permission::create(['name' => 'patients.delete']);
        Permission::create(['name' => 'patients.view_all']); // Ver todos, no solo asignados

        // Citas
        Permission::create(['name' => 'appointments.view']);
        Permission::create(['name' => 'appointments.create']);
        Permission::create(['name' => 'appointments.edit']);
        Permission::create(['name' => 'appointments.delete']);
        Permission::create(['name' => 'appointments.view_all']);

        // Historiales médicos
        Permission::create(['name' => 'records.view']);
        Permission::create(['name' => 'records.create']);
        Permission::create(['name' => 'records.edit']);
        Permission::create(['name' => 'records.delete']);
        Permission::create(['name' => 'records.view_confidential']);

        // Configuración
        Permission::create(['name' => 'settings.view']);
        Permission::create(['name' => 'settings.edit']);
        Permission::create(['name' => 'users.manage']);
        Permission::create(['name' => 'billing.manage']);

        // ==================== ROLES ====================

        // Owner - Acceso completo
        $owner = Role::create(['name' => 'owner']);
        $owner->givePermissionTo(Permission::all());

        // Doctor - Acceso médico completo
        $doctor = Role::create(['name' => 'doctor']);
        $doctor->givePermissionTo([
            'patients.view', 'patients.create', 'patients.edit',
            'appointments.view', 'appointments.create', 'appointments.edit',
            'records.view', 'records.create', 'records.edit',
            'settings.view',
        ]);

        // Asistente - Apoyo al doctor
        $assistant = Role::create(['name' => 'assistant']);
        $assistant->givePermissionTo([
            'patients.view', 'patients.create', 'patients.edit',
            'appointments.view', 'appointments.create', 'appointments.edit',
        ]);

        // Secretaria - Gestión de citas
        $secretary = Role::create(['name' => 'secretary']);
        $secretary->givePermissionTo([
            'patients.view', 'patients.create',
            'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.view_all',
        ]);

        // Recepcionista - Check-in básico
        $receptionist = Role::create(['name' => 'receptionist']);
        $receptionist->givePermissionTo([
            'patients.view',
            'appointments.view', 'appointments.edit',
        ]);

        // Admin - Configuración
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'patients.view', 'patients.view_all',
            'appointments.view', 'appointments.view_all',
            'settings.view', 'settings.edit',
            'users.manage', 'billing.manage',
        ]);

        $this->command->info('✅ Roles y permisos creados exitosamente');
    }
}
