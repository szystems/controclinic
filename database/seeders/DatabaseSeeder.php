<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear roles y permisos primero
        $this->call(RolesAndPermissionsSeeder::class);

        // Crear planes
        $this->call(PlansSeeder::class);

        // Super administrador de la plataforma
        User::firstOrCreate(
            ['email' => 'admin@controclinic.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_super_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Super Admin: admin@controclinic.com / password');

        // Crear clínica demo para desarrollo
        $this->call(DemoClinicSeeder::class);

        // Configuración global de la plataforma
        $this->call(AppSettingsSeeder::class);
    }
}
