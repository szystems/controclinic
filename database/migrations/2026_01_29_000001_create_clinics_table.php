<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla principal de tenants (clínicas)
     */
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('GT');
            $table->string('timezone')->default('America/Guatemala');
            $table->string('currency')->default('USD');
            $table->string('locale')->default('es');

            // Subscription & Plan
            $table->enum('plan_type', ['free', 'solo', 'group', 'enterprise'])->default('free');
            $table->enum('status', ['active', 'suspended', 'cancelled', 'trial'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();

            // Settings JSON
            $table->json('settings')->nullable();
            $table->json('branding')->nullable();

            // Portal público
            $table->boolean('public_portal_enabled')->default(true);
            $table->string('public_portal_slug')->unique()->nullable();

            // Límites según plan (null = ilimitado)
            $table->integer('max_patients')->nullable()->default(25);
            $table->integer('max_appointments_per_month')->nullable()->default(5);
            $table->integer('max_doctors')->nullable()->default(1);
            $table->integer('max_staff')->nullable()->default(0);
            $table->bigInteger('storage_used_bytes')->default(0);
            $table->bigInteger('max_storage_bytes')->nullable()->default(524288000);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};
