<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Facades\Activity;

class SeedDemoData extends Command
{
    protected $signature = 'clinic:seed-demo
                            {clinic : Clinic slug or UUID}
                            {--clear : Remove all demo data instead of creating}';

    protected $description = 'Seed (or clear) demo data for a clinic';

    public function handle(): int
    {
        $identifier = $this->argument('clinic');
        $clinic = Clinic::where('slug', $identifier)
            ->orWhere('id', $identifier)
            ->first();

        if (! $clinic) {
            $this->error("Clinic '{$identifier}' not found.");

            return self::FAILURE;
        }

        if ($this->option('clear')) {
            return $this->clearDemoData($clinic);
        }

        return $this->createDemoData($clinic);
    }

    private function createDemoData(Clinic $clinic): int
    {
        // Check if demo data already exists
        if (Patient::where('clinic_id', $clinic->id)->where('is_demo', true)->exists()) {
            $this->warn('Demo data already exists for this clinic. Use --clear first.');

            return self::FAILURE;
        }

        $doctor = User::where('clinic_id', $clinic->id)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['owner', 'doctor']))
            ->first();

        if (! $doctor) {
            $this->error('No doctor found in this clinic.');

            return self::FAILURE;
        }

        $this->info("Seeding demo data for clinic: {$clinic->name}");

        DB::transaction(function () use ($clinic, $doctor) {
            $patients = $this->createPatients($clinic, $doctor);
            $appointments = $this->createAppointments($clinic, $doctor, $patients);
            $records = $this->createMedicalRecords($clinic, $doctor, $patients, $appointments);
            $this->createInvoices($clinic, $doctor, $patients, $appointments);
            $this->createPrescriptions($clinic, $doctor, $patients, $records);

            activity()
                ->causedBy($doctor)
                ->performedOn($clinic)
                ->withProperties([
                    'patients' => count($patients),
                    'appointments' => count($appointments),
                    'records' => count($records),
                ])
                ->log('demo_data_loaded');
        });

        $this->info('Demo data created successfully.');

        return self::SUCCESS;
    }

    private function clearDemoData(Clinic $clinic): int
    {
        $this->info("Clearing demo data for clinic: {$clinic->name}");

        DB::transaction(function () use ($clinic) {
            $patientIds = Patient::where('clinic_id', $clinic->id)
                ->where('is_demo', true)
                ->pluck('id');

            // Delete related records first (avoid FK violations)
            Prescription::where('clinic_id', $clinic->id)->where('is_demo', true)->forceDelete();
            Invoice::where('clinic_id', $clinic->id)->where('is_demo', true)->forceDelete();
            MedicalRecord::where('clinic_id', $clinic->id)->where('is_demo', true)->forceDelete();
            Appointment::where('clinic_id', $clinic->id)->where('is_demo', true)->forceDelete();
            Patient::where('clinic_id', $clinic->id)->where('is_demo', true)->forceDelete();

            // Activity log
            $actor = User::where('clinic_id', $clinic->id)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['owner', 'doctor']))
                ->first();

            if ($actor) {
                activity()
                    ->causedBy($actor)
                    ->performedOn($clinic)
                    ->withProperties(['patient_ids' => $patientIds->toArray()])
                    ->log('demo_data_cleared');
            }
        });

        $this->info('Demo data cleared.');

        return self::SUCCESS;
    }

    /** @return Patient[] */
    private function createPatients(Clinic $clinic, User $doctor): array
    {
        $data = [
            ['first_name' => 'Ana',       'last_name' => 'García',    'email' => 'ana.garcia.demo@example.com',    'gender' => 'female', 'birth_date' => '1985-03-12', 'blood_type' => 'A+'],
            ['first_name' => 'Carlos',    'last_name' => 'Martínez',  'email' => 'carlos.m.demo@example.com',      'gender' => 'male',   'birth_date' => '1978-07-22', 'blood_type' => 'O+'],
            ['first_name' => 'Sofía',     'last_name' => 'López',     'email' => 'sofia.lopez.demo@example.com',   'gender' => 'female', 'birth_date' => '1992-11-05', 'blood_type' => 'B+'],
            ['first_name' => 'Miguel',    'last_name' => 'Rodríguez', 'email' => 'miguel.r.demo@example.com',      'gender' => 'male',   'birth_date' => '1965-01-30', 'blood_type' => 'AB-'],
            ['first_name' => 'Valentina', 'last_name' => 'Torres',    'email' => 'valentina.t.demo@example.com',   'gender' => 'female', 'birth_date' => '2001-09-18', 'blood_type' => 'O-'],
        ];

        $patients = [];
        foreach ($data as $i => $d) {
            $patients[] = Patient::create(array_merge($d, [
                'clinic_id' => $clinic->id,
                'primary_doctor_id' => $doctor->id,
                'medical_record_number' => 'DEMO-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'phone' => '+1 555-'.str_pad(1000 + $i, 4, '0', STR_PAD_LEFT),
                'city' => 'Ciudad de México',
                'country' => 'MX',
                'is_active' => true,
                'is_demo' => true,
            ]));
        }

        return $patients;
    }

    /** @return Appointment[] */
    private function createAppointments(Clinic $clinic, User $doctor, array $patients): array
    {
        $types = ['scheduled', 'follow_up', 'walk_in'];
        $statuses = ['completed', 'completed', 'completed', 'scheduled', 'cancelled'];
        $reasons = [
            'Dolor de cabeza persistente',
            'Control de presión arterial',
            'Revisión general anual',
            'Fatiga y mareos',
            'Seguimiento post-tratamiento',
            'Dolor lumbar',
            'Alergia estacional',
            'Revisión de análisis de laboratorio',
            'Consulta de primera vez',
            'Control de diabetes',
        ];

        $appointments = [];
        $base = now()->subDays(30);

        for ($i = 0; $i < 10; $i++) {
            $patient = $patients[$i % count($patients)];
            $date = $base->copy()->addDays($i * 3);
            $status = $statuses[$i % count($statuses)];

            $appointments[] = Appointment::create([
                'clinic_id' => $clinic->id,
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'created_by' => $doctor->id,
                'appointment_type' => $types[$i % count($types)],
                'appointment_date' => $date->toDateString(),
                'start_time' => $date->setTime(9 + ($i % 8), 0)->toDateTimeString(),
                'end_time' => $date->copy()->addMinutes(30)->toDateTimeString(),
                'duration_minutes' => 30,
                'status' => $status,
                'reason' => $reasons[$i],
                'is_billable' => true,
                'consultation_price' => 500.00,
                'completed_at' => $status === 'completed' ? $date->toDateTimeString() : null,
                'cancelled_at' => $status === 'cancelled' ? $date->toDateTimeString() : null,
                'is_demo' => true,
            ]);
        }

        return $appointments;
    }

    /** @return MedicalRecord[] */
    private function createMedicalRecords(Clinic $clinic, User $doctor, array $patients, array $appointments): array
    {
        $records = [];
        $completedAppts = array_filter($appointments, fn ($a) => $a->status === 'completed');
        $completedAppts = array_values($completedAppts);

        $notes = [
            ['chief_complaint' => 'Cefalea tensional', 'assessment' => 'Cefalea de tipo tensional sin signos de alarma.', 'plan' => 'Analgésicos PRN, técnicas de relajación, seguimiento en 4 semanas.'],
            ['chief_complaint' => 'Hipertensión arterial', 'assessment' => 'HTA grado 1 bien controlada con medicación actual.', 'plan' => 'Continuar tratamiento, dieta hiposódica, control en 3 meses.'],
            ['chief_complaint' => 'Revisión anual', 'assessment' => 'Paciente en buen estado de salud general. Parámetros dentro de rangos normales.', 'plan' => 'Próxima revisión en 12 meses. Solicitar análisis de sangre preventivos.'],
        ];

        foreach (array_slice($completedAppts, 0, 3) as $idx => $appt) {
            $note = $notes[$idx % count($notes)];
            $records[] = MedicalRecord::create([
                'clinic_id' => $clinic->id,
                'patient_id' => $appt->patient_id,
                'doctor_id' => $doctor->id,
                'appointment_id' => $appt->id,
                'record_type' => 'consultation',
                'title' => $note['chief_complaint'],
                'chief_complaint' => $note['chief_complaint'],
                'assessment' => $note['assessment'],
                'plan' => $note['plan'],
                'vital_signs' => ['blood_pressure' => '120/80', 'heart_rate' => 72, 'temperature' => 36.5, 'weight' => 70],
                'status' => 'final',
                'finalized_at' => $appt->appointment_date,
                'is_demo' => true,
            ]);
        }

        return $records;
    }

    private function createInvoices(Clinic $clinic, User $doctor, array $patients, array $appointments): void
    {
        $completedAppts = array_filter($appointments, fn ($a) => $a->status === 'completed');
        $completedAppts = array_values($completedAppts);

        foreach (array_slice($completedAppts, 0, 2) as $idx => $appt) {
            $invoice = Invoice::create([
                'clinic_id' => $clinic->id,
                'patient_id' => $appt->patient_id,
                'doctor_id' => $doctor->id,
                'appointment_id' => $appt->id,
                'created_by' => $doctor->id,
                'invoice_number' => 'DEMO-'.str_pad($idx + 1, 6, '0', STR_PAD_LEFT),
                'issued_at' => $appt->appointment_date,
                'due_at' => Carbon::parse($appt->appointment_date)->addDays(15),
                'status' => $idx === 0 ? 'paid' : 'pending',
                'subtotal' => 500.00,
                'discount_amount' => 0,
                'tax_amount' => 80.00,
                'total' => 580.00,
                'paid_amount' => $idx === 0 ? 580.00 : 0,
                'currency' => 'MXN',
                'notes' => 'Consulta médica general (demo)',
                'is_demo' => true,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Consulta médica general',
                'quantity' => 1,
                'unit_price' => 500.00,
                'discount' => 0,
                'tax_rate' => 16,
                'subtotal' => 500.00,
                'total' => 580.00,
            ]);
        }
    }

    private function createPrescriptions(Clinic $clinic, User $doctor, array $patients, array $records): void
    {
        if (empty($records)) {
            return;
        }

        $record = $records[0];

        $prescription = Prescription::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $record->patient_id,
            'doctor_id' => $doctor->id,
            'medical_record_id' => $record->id,
            'status' => 'issued',
            'issued_at' => $record->finalized_at ?? now(),
            'valid_until' => Carbon::parse($record->finalized_at ?? now())->addDays(30),
            'diagnosis' => $record->chief_complaint,
            'notes' => 'Receta de demostración',
            'folio' => 'DEMO-RX-001',
            'is_demo' => true,
        ]);

        PrescriptionItem::create([
            'prescription_id' => $prescription->id,
            'medication_name' => 'Ibuprofeno 400mg',
            'active_ingredient' => 'Ibuprofeno',
            'presentation' => 'Tabletas',
            'dose' => '400mg',
            'frequency' => 'Cada 8 horas con alimentos',
            'duration' => '5 días',
            'instructions' => 'Suspender si hay malestar gástrico',
            'quantity' => 15,
            'is_controlled' => false,
        ]);
    }
}
