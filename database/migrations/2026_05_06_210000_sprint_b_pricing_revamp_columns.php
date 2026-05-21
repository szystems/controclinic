<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint B — Pricing revamp + Partners/Referrals forward-compat
 *
 * Aditiva. Todas las columnas son nullable o tienen default seguro.
 *
 * - `plans`: campos para landing pública (privado, requiere código, highlights, CTA custom).
 * - `clinics`: columnas reservadas para programa de Partners (Modelo A) y Referrals (Modelo B)
 *   que se implementan post-launch en BLOQUE 1. Sin FK a `partners` porque la tabla aún no existe.
 *
 * @see .context/TASKS.md — Sprint B + BLOQUE 1
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── plans ────────────────────────────────────────────────────────────
        Schema::table('plans', function (Blueprint $table) {
            // Plan privado (no listado en /pricing público; solo accesible por enlace directo o asignación).
            $table->boolean('is_private')->default(false)->after('is_enterprise');

            // Plan requiere código de invitación/partner para suscribirse.
            $table->boolean('requires_code')->default(false)->after('is_private');

            // Bullets destacados para mostrar en la landing (sobreescribe display_features si presente).
            $table->json('highlight_features')->nullable()->after('features');

            // CTA custom (texto botón + url destino). Útil para Enterprise → mailto/form, o partners.
            $table->string('cta_text')->nullable()->after('paddle_product_id');
            $table->string('cta_url')->nullable()->after('cta_text');

            $table->index('is_private');
        });

        // ─── clinics ──────────────────────────────────────────────────────────
        // Columnas reservadas para programa de Partners B2B (Modelo A) y Referrals (Modelo B).
        // Implementación completa diferida a BLOQUE 1 post-launch.
        Schema::table('clinics', function (Blueprint $table) {
            // Modelo A (Partners B2B):
            // FK a tabla `partners` que se creará en BLOQUE 1. Por ahora solo columna sin FK.
            $table->uuid('partner_id')->nullable()->after('plan_id');

            // FK a tabla `partner_codes` (códigos de invitación con metadata de campaña).
            $table->uuid('partner_code_id')->nullable()->after('partner_id');

            // Timestamp de atribución (cuándo se vinculó la clínica al partner; sirve para periodos de comisión).
            $table->timestamp('partner_attributed_at')->nullable()->after('partner_code_id');

            // Modelo B (Referrals clínica→clínica):
            // Código único que esta clínica comparte para referir a otras.
            $table->string('referral_code')->nullable()->unique()->after('partner_attributed_at');

            // FK a la clínica que refirió (self-reference). nullOnDelete para preservar registro si la
            // refiriente se elimina.
            $table->uuid('referred_by_clinic_id')->nullable()->after('referral_code');
            $table->foreign('referred_by_clinic_id')
                ->references('id')->on('clinics')
                ->nullOnDelete();

            $table->index('partner_id');
            $table->index('referred_by_clinic_id');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropForeign(['referred_by_clinic_id']);
            $table->dropIndex(['partner_id']);
            $table->dropIndex(['referred_by_clinic_id']);
            $table->dropUnique(['referral_code']);
            $table->dropColumn([
                'partner_id',
                'partner_code_id',
                'partner_attributed_at',
                'referral_code',
                'referred_by_clinic_id',
            ]);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropIndex(['is_private']);
            $table->dropColumn([
                'is_private',
                'requires_code',
                'highlight_features',
                'cta_text',
                'cta_url',
            ]);
        });
    }
};
