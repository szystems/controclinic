<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de configuración global de la plataforma ControClinic
 * (no por-clínica; las settings por clínica viven en clinics.settings JSON).
 *
 * Permite migrar gradualmente hardcodes a settings sin nuevas migraciones cada vez:
 * branding global, SMTP override, feature flags, mantenimiento, etc.
 *
 * Uso típico:
 *   AppSettings::set('branding.logo_url', '/storage/logo.png');
 *   AppSettings::get('billing.default_currency', 'USD');
 *
 * @see .context/TASKS.md — Bloque 0.2 Forward-Compat DB
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->default('general')->index();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->string('type', 20)->default('string'); // string|integer|boolean|json|encrypted
            $table->boolean('is_encrypted')->default(false);
            $table->boolean('is_public')->default(false); // si puede leerse desde frontend
            $table->text('description')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
