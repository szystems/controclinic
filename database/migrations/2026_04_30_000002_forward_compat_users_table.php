<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Forward-Compat Migration — users
 *
 * Reserva columnas para 2FA (Fortify-compatible), firma digital de doctores,
 * tracking de presencia y preferencias UX.
 *
 * Nota: `two_factor_enabled` y `locale` ya existen en users (no duplicar).
 *
 * @see .context/TASKS.md — Bloque 0.2 Forward-Compat DB
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 2FA (compatible con laravel/fortify y jetstream).
            // Encriptados a nivel modelo via cast 'encrypted'.
            $table->text('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');

            // Firma digital del doctor (path en storage). Usada en PDFs de recetas/notas.
            $table->string('signature_path')->nullable()->after('avatar');

            // Última actividad detectada — para "online ahora" y auditoría de sesiones.
            $table->timestamp('last_seen_at')->nullable()->after('last_login_at');

            // Preferencias UX por usuario (vista calendario, atajos, columnas de tablas).
            // Distinto a `preferences` de paciente; aquí es para la app.
            $table->json('preferences')->nullable()->after('working_hours');

            // Aceptación de Términos y Privacidad (compliance v1).
            $table->timestamp('terms_accepted_at')->nullable()->after('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'signature_path',
                'last_seen_at',
                'preferences',
                'terms_accepted_at',
            ]);
        });
    }
};
