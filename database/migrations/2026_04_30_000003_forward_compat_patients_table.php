<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Forward-Compat Migration — patients
 *
 * Reserva columnas para portal del paciente, importación, consentimiento digital,
 * marketing y notas internas (no visibles en historial clínico).
 *
 * Nota: `preferences` ya existe en patients (preferencias UX del paciente, no del staff).
 *
 * @see .context/TASKS.md — Bloque 0.2 Forward-Compat DB
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Notas internas del staff (no aparecen en historial clínico ni en PDFs del paciente).
            $table->text('internal_notes')->nullable()->after('notes');

            // Acceso al portal del paciente (cuando se implemente). FK a users.
            // ON DELETE: nullOnDelete — si se borra la cuenta, no se borra al paciente.
            $table->foreignId('portal_user_id')->nullable()->after('preferences')
                ->constrained('users')->nullOnDelete();

            // ID externo para evitar duplicados al importar desde otro sistema.
            $table->string('external_id')->nullable()->after('portal_user_id');

            // Consentimiento informado digital firmado (timestamp de aceptación).
            $table->timestamp('consent_signed_at')->nullable()->after('external_id');

            // Opt-in marketing (campañas email/SMS futuras). Default false por GDPR.
            $table->boolean('marketing_opt_in')->default(false)->after('consent_signed_at');

            // Índices para queries frecuentes futuras.
            $table->index(['clinic_id', 'external_id']);
            $table->index(['clinic_id', 'portal_user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['portal_user_id']);
            $table->dropIndex(['clinic_id', 'external_id']);
            $table->dropIndex(['clinic_id', 'portal_user_id']);
            $table->dropColumn([
                'internal_notes',
                'portal_user_id',
                'external_id',
                'consent_signed_at',
                'marketing_opt_in',
            ]);
        });
    }
};
