<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Historial médico compartido por clínica
     */
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('clinic_id');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');

            // Relaciones
            $table->uuid('patient_id');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->uuid('appointment_id')->nullable();
            $table->foreign('appointment_id')->references('id')->on('appointments')->nullOnDelete();

            // Tipo de registro
            $table->enum('record_type', [
                'consultation',     // Consulta general
                'diagnosis',        // Diagnóstico
                'prescription',     // Receta médica
                'lab_result',       // Resultado de laboratorio
                'imaging',          // Imagenología
                'procedure',        // Procedimiento
                'surgery',          // Cirugía
                'referral',         // Referencia a otro doctor
                'follow_up_note',   // Nota de seguimiento
                'vital_signs',      // Signos vitales
                'vaccination',      // Vacunación
                'other',
            ])->default('consultation');

            // Contenido principal
            $table->string('title')->nullable();
            $table->longText('content')->nullable(); // JSON o texto según tipo

            // Campos estructurados para consulta
            $table->text('chief_complaint')->nullable();        // Motivo de consulta
            $table->text('present_illness')->nullable();        // Enfermedad actual
            $table->text('physical_examination')->nullable();   // Examen físico
            $table->text('assessment')->nullable();             // Evaluación
            $table->text('plan')->nullable();                   // Plan de tratamiento

            // Signos vitales (si aplica)
            $table->json('vital_signs')->nullable();

            // Diagnósticos (CIE-10)
            $table->json('diagnoses')->nullable();

            // Prescripciones
            $table->json('prescriptions')->nullable();

            // Archivos adjuntos
            $table->json('attachments')->nullable();

            // Confidencialidad
            $table->boolean('is_confidential')->default(false);
            $table->json('visible_to_roles')->nullable(); // Roles que pueden ver

            // Estado
            $table->enum('status', ['draft', 'final', 'amended', 'deleted'])->default('final');
            $table->timestamp('finalized_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['clinic_id', 'patient_id']);
            $table->index(['clinic_id', 'patient_id', 'record_type']);
            $table->index(['clinic_id', 'doctor_id']);
            $table->index(['clinic_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
