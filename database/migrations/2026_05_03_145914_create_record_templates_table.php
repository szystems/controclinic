<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('record_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->nullOnDelete()->constrained('users');
            $table->string('name');
            $table->string('specialty')->nullable();
            $table->string('record_type')->default('consultation');
            $table->string('chief_complaint')->nullable();
            $table->text('present_illness')->nullable();
            $table->text('physical_examination')->nullable();
            $table->text('assessment')->nullable();
            $table->text('plan')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'record_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('record_templates');
    }
};
