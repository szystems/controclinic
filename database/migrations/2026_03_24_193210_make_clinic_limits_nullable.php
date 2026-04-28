<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->integer('max_patients')->nullable()->default(25)->change();
            $table->integer('max_appointments_per_month')->nullable()->default(5)->change();
            $table->integer('max_doctors')->nullable()->default(1)->change();
            $table->integer('max_staff')->nullable()->default(0)->change();
            $table->bigInteger('max_storage_bytes')->nullable()->default(524288000)->change();
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->integer('max_patients')->default(25)->change();
            $table->integer('max_appointments_per_month')->default(5)->change();
            $table->integer('max_doctors')->default(1)->change();
            $table->integer('max_staff')->default(0)->change();
            $table->bigInteger('max_storage_bytes')->default(524288000)->change();
        });
    }
};
