<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Forward-Compat Migration — BLOQUE 2 (post-lanzamiento)
 *
 * Reserva columnas para features del BLOQUE 2 que requieren ALTER TABLE sobre tablas
 * existentes. Todas son nullable o tienen default seguro; ninguna data viva se ve afectada.
 *
 * Features cubiertas:
 * - SMS/WhatsApp: `patients.preferred_channel` + `clinics.sms_notifications_enabled`
 * - Recetas QR:   `medical_records.qr_payload`
 * - NPS:          `appointments.nps_sent_at`
 *
 * @see .context/TASKS.md — BLOQUE 2
 * @see .context/DATA_RETENTION.md
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── clinics ──────────────────────────────────────────────────────────
        Schema::table('clinics', function (Blueprint $table) {
            // Feature flag por clínica para notificaciones SMS/WhatsApp (default off).
            $table->boolean('sms_notifications_enabled')->default(false)->after('data_retention_years');

            // Proveedor SMS configurado (twilio | vonage | infobip | null = no configurado).
            $table->string('sms_provider', 30)->nullable()->after('sms_notifications_enabled');
        });

        // ─── patients ─────────────────────────────────────────────────────────
        Schema::table('patients', function (Blueprint $table) {
            // Canal preferido para recordatorios. NULL = hereda el default de la clínica (email).
            // Valores: email | sms | whatsapp | none
            $table->string('preferred_channel', 20)->nullable()->after('marketing_opt_in');
        });

        // ─── medical_records ──────────────────────────────────────────────────
        Schema::table('medical_records', function (Blueprint $table) {
            // Payload JSON para receta electrónica con QR verificable (BLOQUE 2).
            // Se genera al firmar digitalmente; codifica datos mínimos de la receta.
            $table->text('qr_payload')->nullable()->after('ai_metadata');
        });

        // ─── appointments ─────────────────────────────────────────────────────
        Schema::table('appointments', function (Blueprint $table) {
            // Timestamp de envío de encuesta NPS post-cita (para no reenviar).
            $table->timestamp('nps_sent_at')->nullable()->after('created_via');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn(['sms_notifications_enabled', 'sms_provider']);
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('preferred_channel');
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropColumn('qr_payload');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('nps_sent_at');
        });
    }
};
