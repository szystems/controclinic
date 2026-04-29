<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalRecord>
 */
class MedicalRecordFactory extends Factory
{
    protected $model = MedicalRecord::class;

    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'patient_id' => Patient::factory(),
            'doctor_id' => User::factory(),
            'appointment_id' => null,
            'record_type' => MedicalRecord::TYPE_CONSULTATION,
            'title' => $this->faker->sentence(4),
            'content' => null,
            'chief_complaint' => $this->faker->sentence(),
            'present_illness' => $this->faker->paragraph(),
            'physical_examination' => $this->faker->paragraph(),
            'assessment' => $this->faker->paragraph(),
            'plan' => $this->faker->paragraph(),
            'vital_signs' => null,
            'diagnoses' => null,
            'prescriptions' => null,
            'attachments' => null,
            'is_confidential' => false,
            'visible_to_roles' => null,
            'status' => MedicalRecord::STATUS_FINAL,
            'finalized_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => MedicalRecord::STATUS_DRAFT,
            'finalized_at' => null,
        ]);
    }

    public function consultation(): static
    {
        return $this->state(fn () => [
            'record_type' => MedicalRecord::TYPE_CONSULTATION,
        ]);
    }

    public function prescription(): static
    {
        return $this->state(fn () => [
            'record_type' => MedicalRecord::TYPE_PRESCRIPTION,
            'prescriptions' => [
                [
                    'drug' => 'Ibuprofeno 400mg',
                    'dosage' => '1 tab cada 8h',
                    'duration' => '5 días',
                ],
            ],
        ]);
    }

    public function withVitalSigns(): static
    {
        return $this->state(fn () => [
            'vital_signs' => [
                'temperature' => 36.8,
                'heart_rate' => 78,
                'blood_pressure' => '120/80',
                'respiratory_rate' => 16,
                'oxygen_saturation' => 98,
                'weight' => 70.5,
                'height' => 175,
            ],
        ]);
    }

    public function confidential(): static
    {
        return $this->state(fn () => [
            'is_confidential' => true,
            'visible_to_roles' => ['owner', 'doctor'],
        ]);
    }

    public function forPatient(Patient $patient): static
    {
        return $this->state(fn () => [
            'clinic_id' => $patient->clinic_id,
            'patient_id' => $patient->id,
        ]);
    }

    public function forAppointment(Appointment $appointment): static
    {
        return $this->state(fn () => [
            'clinic_id' => $appointment->clinic_id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->id,
        ]);
    }
}
