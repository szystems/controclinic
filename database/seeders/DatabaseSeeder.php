<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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

        // Crear clínica demo para desarrollo
        $this->call(DemoClinicSeeder::class);
    }
}
