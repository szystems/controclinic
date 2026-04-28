<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Actualizar tabla users para multi-tenancy
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Relación con clínica (tenant)
            $table->uuid('clinic_id')->nullable()->after('id');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');

            // Rol principal del usuario
            $table->enum('role', ['owner', 'doctor', 'assistant', 'secretary', 'receptionist', 'admin'])->default('doctor')->after('email');

            // Información adicional
            $table->string('phone')->nullable()->after('role');
            $table->string('avatar')->nullable();
            $table->string('locale')->default('es');
            $table->string('timezone')->nullable();

            // Para doctores
            $table->json('specialties')->nullable(); // Especialidades médicas
            $table->text('bio')->nullable();
            $table->string('license_number')->nullable(); // Número de colegiado
            $table->json('working_hours')->nullable(); // Horarios de trabajo

            // Estado y seguridad
            $table->boolean('is_active')->default(true);
            $table->boolean('two_factor_enabled')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();

            // Soft deletes
            $table->softDeletes();

            // Índices
            $table->index(['clinic_id', 'role']);
            $table->index(['clinic_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropColumn([
                'clinic_id', 'role', 'phone', 'avatar', 'locale', 'timezone',
                'specialties', 'bio', 'license_number', 'working_hours',
                'is_active', 'two_factor_enabled', 'last_login_at', 'last_login_ip',
                'deleted_at',
            ]);
        });
    }
};
