<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('clinic_id');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');

            $table->uuid('patient_id');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');

            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');

            // Relación opcional con consulta
            $table->uuid('medical_record_id')->nullable();
            $table->foreign('medical_record_id')->references('id')->on('medical_records')->nullOnDelete();

            // Estado
            $table->enum('status', ['draft', 'issued', 'dispensed', 'cancelled'])->default('draft');

            // Fechas
            $table->date('issued_at')->nullable();
            $table->date('valid_until')->nullable();

            // Diagnóstico / notas
            $table->text('diagnosis')->nullable();
            $table->text('notes')->nullable();         // Instrucciones generales al paciente
            $table->text('internal_notes')->nullable(); // Solo visible para el personal

            // Verificación pública (Fase 2)
            $table->string('qr_payload', 128)->unique()->nullable();

            // Firma del doctor (Fase 2)
            $table->string('signature_path')->nullable();

            // Folio auto-incremental por clínica (ej: RX-0001)
            $table->string('folio', 20)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'patient_id']);
            $table->index(['clinic_id', 'doctor_id']);
            $table->index(['clinic_id', 'status']);
            $table->index(['clinic_id', 'issued_at']);
        });

        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('prescription_id');
            $table->foreign('prescription_id')->references('id')->on('prescriptions')->onDelete('cascade');

            $table->unsignedSmallInteger('order')->default(0); // Orden de aparición en la receta

            $table->string('medication_name');          // Nombre comercial o genérico
            $table->string('active_ingredient')->nullable(); // Principio activo
            $table->string('presentation')->nullable();  // Ej: "comprimidos 500mg"
            $table->string('dose')->nullable();          // Ej: "1 comprimido"
            $table->string('frequency')->nullable();     // Ej: "cada 8 horas"
            $table->string('duration')->nullable();      // Ej: "7 días"
            $table->string('route')->nullable();         // Ej: "oral", "tópico", "inyectable"
            $table->text('instructions')->nullable();    // Ej: "tomar con comida"
            $table->unsignedSmallInteger('quantity')->nullable(); // Cantidad a dispensar
            $table->boolean('is_controlled')->default(false); // Sustancia controlada

            $table->timestamps();

            $table->index(['prescription_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
    }
};
