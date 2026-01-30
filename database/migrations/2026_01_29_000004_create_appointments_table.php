<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sistema de citas flexible - 3 modalidades
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('clinic_id');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');

            // Relaciones
            $table->uuid('patient_id');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Tipo de cita
            $table->enum('appointment_type', [
                'scheduled',    // Cita programada tradicional
                'walk_in',      // Orden de llegada (ficha)
                'emergency',    // Emergencia
                'follow_up',    // Seguimiento
                'telemedicine'  // Telemedicina (futuro)
            ])->default('scheduled');

            // Fecha y hora
            $table->date('appointment_date');
            $table->time('start_time')->nullable(); // Null para walk-in
            $table->time('end_time')->nullable();
            $table->integer('duration_minutes')->default(30);

            // Para sistema de fichas (walk-in)
            $table->integer('queue_number')->nullable();
            $table->enum('queue_period', ['morning', 'afternoon', 'evening'])->nullable();

            // Estado
            $table->enum('status', [
                'scheduled',    // Programada
                'confirmed',    // Confirmada por paciente
                'waiting',      // En sala de espera
                'in_progress',  // En consulta
                'completed',    // Completada
                'cancelled',    // Cancelada
                'no_show'       // No se presentó
            ])->default('scheduled');

            // Motivo de la cita
            $table->string('reason')->nullable();
            $table->text('symptoms')->nullable();
            $table->text('notes')->nullable();

            // Check-in/out
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();

            // Recordatorios
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();

            // Recursos (sala, equipo)
            $table->string('room')->nullable();
            $table->json('resources')->nullable();

            // Recurrencia
            $table->boolean('is_recurring')->default(false);
            $table->uuid('recurring_pattern_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['clinic_id', 'appointment_date']);
            $table->index(['clinic_id', 'doctor_id', 'appointment_date']);
            $table->index(['clinic_id', 'patient_id']);
            $table->index(['clinic_id', 'status']);
            $table->index(['appointment_date', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
