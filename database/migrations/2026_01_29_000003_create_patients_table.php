<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla de pacientes - compartidos por clínica
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('clinic_id');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');

            // Doctor primario asignado
            $table->foreignId('primary_doctor_id')->nullable()->constrained('users')->nullOnDelete();

            // Número de expediente único por clínica
            $table->string('medical_record_number')->nullable();

            // Información personal
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_secondary')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('id_type')->nullable(); // DPI, Pasaporte, etc.
            $table->string('id_number')->nullable();

            // Dirección
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('GT');

            // Información médica básica
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
            $table->text('allergies')->nullable();
            $table->text('chronic_conditions')->nullable();
            $table->text('current_medications')->nullable();

            // Contacto de emergencia
            $table->json('emergency_contacts')->nullable();

            // Seguro médico
            $table->json('insurance_info')->nullable();

            // Notas y preferencias
            $table->text('notes')->nullable();
            $table->json('preferences')->nullable();

            // Estado
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_visit_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->unique(['clinic_id', 'medical_record_number']);
            $table->index(['clinic_id', 'is_active']);
            $table->index(['clinic_id', 'last_name', 'first_name']);
            $table->index(['clinic_id', 'primary_doctor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
