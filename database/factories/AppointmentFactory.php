<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $date = fake()->dateTimeBetween('now', '+30 days');

        return [
            'clinic_id' => Clinic::factory(),
            'patient_id' => Patient::factory(),
            'doctor_id' => User::factory(),
            'appointment_type' => 'scheduled',
            'appointment_date' => $date->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '10:30',
            'duration_minutes' => 30,
            'status' => 'scheduled',
            'reason' => fake()->sentence(),
        ];
    }
}
