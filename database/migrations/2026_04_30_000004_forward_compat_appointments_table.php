<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Forward-Compat Migration — appointments
 *
 * Reserva columnas para facturación de consultas, sucursales, telemedicina,
 * confirmación por link, formularios pre-consulta y trazabilidad de reagendamientos.
 *
 * @see .context/TASKS.md — Bloque 0.2 Forward-Compat DB
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Sucursal donde ocurre la cita (en v1 = clinic_id).
            // FK a clinics, nullOnDelete — si se borra la sucursal queda como "sin sucursal".
            $table->uuid('branch_id')->nullable()->after('clinic_id');
            $table->foreign('branch_id')->references('id')->on('clinics')->nullOnDelete();

            // Facturación (Bloque 1 — Facturación v1).
            // decimal(12,2) soporta hasta 9,999,999,999.99 — suficiente para cualquier moneda.
            $table->decimal('consultation_price', 12, 2)->nullable()->after('duration_minutes');
            $table->decimal('consultation_discount', 12, 2)->nullable()->after('consultation_price');
            $table->boolean('is_billable')->default(true)->after('consultation_discount');

            // Confirmación por link sin login (SMS/WhatsApp/Email).
            $table->string('confirmation_token', 64)->nullable()->unique()->after('reminder_sent_at');
            $table->string('confirmed_via', 20)->nullable()->after('confirmation_token');

            // Telemedicina.
            $table->string('telemedicine_link')->nullable()->after('room');
            $table->string('telemedicine_provider', 30)->nullable()->after('telemedicine_link');

            // Formulario pre-consulta llenado por el paciente. UUID sin FK aún
            // (la tabla pre_consultation_forms se creará en su momento).
            $table->uuid('pre_consultation_form_id')->nullable()->after('telemedicine_provider');

            // Trazabilidad de reagendamientos: cita padre cuando esta es un reagendamiento.
            $table->uuid('parent_appointment_id')->nullable()->after('pre_consultation_form_id');
            $table->foreign('parent_appointment_id')
                ->references('id')->on('appointments')
                ->nullOnDelete();

            // Origen de creación: staff/portal/api/import (auditoría y métricas).
            $table->string('created_via', 20)->default('staff')->after('parent_appointment_id');

            // Índices para queries futuras.
            $table->index('branch_id');
            $table->index(['clinic_id', 'is_billable']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['parent_appointment_id']);
            $table->dropIndex(['branch_id']);
            $table->dropIndex(['clinic_id', 'is_billable']);
            $table->dropUnique(['confirmation_token']);
            $table->dropColumn([
                'branch_id',
                'consultation_price',
                'consultation_discount',
                'is_billable',
                'confirmation_token',
                'confirmed_via',
                'telemedicine_link',
                'telemedicine_provider',
                'pre_consultation_form_id',
                'parent_appointment_id',
                'created_via',
            ]);
        });
    }
};
