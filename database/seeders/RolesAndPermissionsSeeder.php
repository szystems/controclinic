<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Crear roles y permisos del sistema
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ==================== PERMISSIONS ====================

        $permissions = [
            // Pacientes
            'patients.view', 'patients.create', 'patients.edit', 'patients.delete', 'patients.view_all',
            'patients.export', 'patients.print',
            // Citas
            'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.delete', 'appointments.view_all',
            'appointments.export', 'appointments.print',
            // Historiales médicos
            'records.view', 'records.create', 'records.edit', 'records.delete', 'records.view_confidential',
            'records.print',
            // Configuración
            'settings.view', 'settings.edit', 'users.manage', 'users.print', 'billing.manage',
            // Reportes
            'reports.view', 'reports.export',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ==================== ROLES ====================

        // Owner - Acceso completo
        $owner = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner->syncPermissions(Permission::all());

        // Doctor - Acceso médico completo
        $doctor = Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']);
        $doctor->syncPermissions([
            'patients.view', 'patients.create', 'patients.edit',
            'patients.export', 'patients.print',
            'appointments.view', 'appointments.create', 'appointments.edit',
            'appointments.export', 'appointments.print',
            'records.view', 'records.create', 'records.edit', 'records.print',
            'settings.view',
            'reports.view',
        ]);

        // Asistente - Apoyo al doctor
        $assistant = Role::firstOrCreate(['name' => 'assistant', 'guard_name' => 'web']);
        $assistant->syncPermissions([
            'patients.view', 'patients.create', 'patients.edit',
            'patients.print',
            'appointments.view', 'appointments.create', 'appointments.edit',
            'appointments.print',
        ]);

        // Secretaria - Gestión de citas
        $secretary = Role::firstOrCreate(['name' => 'secretary', 'guard_name' => 'web']);
        $secretary->syncPermissions([
            'patients.view', 'patients.create', 'patients.print',
            'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.view_all',
            'appointments.export', 'appointments.print',
        ]);

        // Recepcionista - Check-in básico
        $receptionist = Role::firstOrCreate(['name' => 'receptionist', 'guard_name' => 'web']);
        $receptionist->syncPermissions([
            'patients.view',
            'appointments.view', 'appointments.edit', 'appointments.print',
        ]);

        // Admin - Configuración
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'patients.view', 'patients.view_all', 'patients.export', 'patients.print',
            'appointments.view', 'appointments.view_all', 'appointments.export', 'appointments.print',
            'settings.view', 'settings.edit',
            'users.manage', 'users.print', 'billing.manage',
            'reports.view', 'reports.export',
        ]);

        $this->command->info('✅ Roles y permisos creados exitosamente');
    }
}
