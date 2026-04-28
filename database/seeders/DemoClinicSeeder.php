<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoClinicSeeder extends Seeder
{
    /**
     * Crear una clínica demo para desarrollo
     */
    public function run(): void
    {
        // Crear clínica demo
        $clinic = Clinic::create([
            'id' => Str::uuid(),
            'name' => 'Clínica Demo',
            'slug' => 'demo',
            'email' => 'demo@controclinic.com',
            'phone' => '+502 1234-5678',
            'address' => '6ta Avenida 10-50 Zona 1',
            'city' => 'Guatemala',
            'country' => 'GT',
            'timezone' => 'America/Guatemala',
            'currency' => 'USD',
            'locale' => 'es',
            'plan_type' => 'solo',
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(30),
            'public_portal_enabled' => true,
            'public_portal_slug' => 'demo',
            'max_patients' => 999999, // "Ilimitado" para plan Solo
            'max_appointments_per_month' => 999999,
            'max_doctors' => 1,
            'max_staff' => 1,
            'settings' => Clinic::getDefaultSettings(),
            'branding' => ['primary_color' => '#4f46e5', 'secondary_color' => '#10b981'],
        ]);

        // Crear usuario owner/doctor
        $owner = User::create([
            'name' => 'Dr. Juan Demo',
            'email' => 'doctor@controclinic.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'clinic_id' => $clinic->id,
            'role' => 'owner',
            'phone' => '+502 5555-1234',
            'locale' => 'es',
            'timezone' => 'America/Guatemala',
            'specialties' => ['Medicina General', 'Medicina Interna'],
            'bio' => 'Médico con más de 10 años de experiencia en medicina general.',
            'license_number' => 'COL-12345',
            'working_hours' => User::getDefaultWorkingHours(),
            'is_active' => true,
        ]);
        $owner->assignRole('owner');

        // Link owner to clinic and mark onboarding as completed
        $clinic->update([
            'owner_id' => $owner->id,
            'onboarding_completed_at' => now(),
        ]);

        // Crear asistente
        $assistant = User::create([
            'name' => 'María Asistente',
            'email' => 'asistente@controclinic.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'clinic_id' => $clinic->id,
            'role' => 'assistant',
            'phone' => '+502 5555-5678',
            'locale' => 'es',
            'timezone' => 'America/Guatemala',
            'is_active' => true,
        ]);
        $assistant->assignRole('assistant');

        // Bind clinic into container so BelongsToClinic trait fills clinic_id
        app()->instance('current_clinic', $clinic);

        // Demo patients
        $patients = Patient::factory()
            ->count(8)
            ->create([
                'clinic_id' => $clinic->id,
                'primary_doctor_id' => $owner->id,
            ]);

        // Demo appointments: spread across past, today and next two weeks
        foreach ($patients as $i => $patient) {
            Appointment::factory()->create([
                'clinic_id' => $clinic->id,
                'patient_id' => $patient->id,
                'doctor_id' => $owner->id,
                'appointment_date' => now()->subDays($i + 1)->toDateString(),
                'status' => Appointment::STATUS_COMPLETED,
            ]);
            Appointment::factory()->create([
                'clinic_id' => $clinic->id,
                'patient_id' => $patient->id,
                'doctor_id' => $owner->id,
                'appointment_date' => now()->addDays($i)->toDateString(),
                'status' => Appointment::STATUS_CONFIRMED,
            ]);
        }

        app()->forgetInstance('current_clinic');

        $this->command->info('✅ Clínica Demo creada exitosamente');
        $this->command->info('   Email: doctor@controclinic.com');
        $this->command->info('   Password: password');
        $this->command->info('   URL: /app/demo');
    }
}
