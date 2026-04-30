<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Forward-Compat Migration — clinics
 *
 * Reserva columnas para features futuras del roadmap (sucursales, FE oficial, retención GDPR).
 * Todas las columnas son nullable o tienen default seguro; ninguna data viva se ve afectada.
 *
 * @see .context/TASKS.md — Bloque 0.2 Forward-Compat DB
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            // Sucursales: una clínica puede ser sede de otra (parent).
            // ON DELETE: nullOnDelete — no queremos perder sucursales si se elimina la matriz.
            $table->uuid('parent_clinic_id')->nullable()->after('id');
            $table->foreign('parent_clinic_id')
                ->references('id')->on('clinics')
                ->nullOnDelete();

            // Identidad legal para futura facturación electrónica oficial (NIT/RUC/RFC/RTN/etc.)
            $table->string('legal_entity_id')->nullable()->after('country');

            // Política de retención de datos (GDPR/HIPAA). NULL = ilimitado.
            $table->unsignedSmallInteger('data_retention_years')->nullable()->after('legal_entity_id');

            $table->index('parent_clinic_id');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropForeign(['parent_clinic_id']);
            $table->dropIndex(['parent_clinic_id']);
            $table->dropColumn(['parent_clinic_id', 'legal_entity_id', 'data_retention_years']);
        });
    }
};
