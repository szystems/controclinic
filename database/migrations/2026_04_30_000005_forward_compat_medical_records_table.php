<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Forward-Compat Migration — medical_records
 *
 * Reserva columnas para enmiendas formales, plantillas SOAP,
 * firma digital del doctor y trazabilidad de IA.
 *
 * @see .context/TASKS.md — Bloque 0.2 Forward-Compat DB
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            // Si este registro es enmienda formal de otro (status='amended' ya existe).
            // Self-FK nullable; nullOnDelete por seguridad legal.
            $table->uuid('amendment_of_id')->nullable()->after('appointment_id');
            $table->foreign('amendment_of_id')
                ->references('id')->on('medical_records')
                ->nullOnDelete();

            // Plantilla SOAP usada (cuando se implementen). UUID sin FK todavía.
            $table->uuid('template_id')->nullable()->after('amendment_of_id');

            // Firma digital del doctor (timestamp + hash de integridad).
            $table->timestamp('signed_at')->nullable()->after('finalized_at');
            $table->string('signature_hash', 128)->nullable()->after('signed_at');

            // IA: registros generados o asistidos por modelos.
            $table->boolean('ai_generated')->default(false)->after('signature_hash');
            $table->json('ai_metadata')->nullable()->after('ai_generated');

            $table->index('amendment_of_id');
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropForeign(['amendment_of_id']);
            $table->dropIndex(['amendment_of_id']);
            $table->dropColumn([
                'amendment_of_id',
                'template_id',
                'signed_at',
                'signature_hash',
                'ai_generated',
                'ai_metadata',
            ]);
        });
    }
};
