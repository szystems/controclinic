<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sistema de etiquetas reutilizable (polymorphic).
 *
 * Permite etiquetar Patient / Appointment / MedicalRecord con tags
 * tipo VIP, alergia, moroso, urgente, etc., sin agregar JSON adhoc en cada modelo.
 *
 * Tablas:
 *   - tags: catálogo por clínica (nombre, color, categoría).
 *   - taggables: pivote polimórfica (tag_id + taggable_type + taggable_id).
 *
 * @see .context/TASKS.md — Bloque 1 Etiquetas en pacientes
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->uuid('clinic_id');
            $table->foreign('clinic_id')->references('id')->on('clinics')->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 20)->default('gray'); // tailwind color name
            $table->string('category', 30)->default('general'); // patient|appointment|record|general
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['clinic_id', 'name']);
            $table->index(['clinic_id', 'category']);
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->uuidMorphs('taggable'); // taggable_type + taggable_id (UUID)
            $table->foreignId('tagged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tagged_at')->useCurrent();

            $table->primary(['tag_id', 'taggable_type', 'taggable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
    }
};
