<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_unavailabilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->date('date_from');
            $table->date('date_to');
            $table->boolean('all_day')->default(true);
            $table->time('time_from')->nullable(); // null when all_day
            $table->time('time_to')->nullable();   // null when all_day
            $table->string('reason')->nullable();  // visible to staff only
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['clinic_id', 'doctor_id', 'date_from', 'date_to'], 'unavail_clinic_doctor_dates');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_unavailabilities');
    }
};
