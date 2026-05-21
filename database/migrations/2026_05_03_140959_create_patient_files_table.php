<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('medical_record_id')->nullable()->nullOnDelete()->constrained();
            $table->foreignId('uploaded_by_user_id')->nullable()->nullOnDelete()->constrained('users');
            $table->string('category')->default('other'); // lab|image|report|prescription|consent|other
            $table->string('name');                       // nombre descriptivo
            $table->string('original_filename');
            $table->string('disk_path');                  // ruta interna, nunca URL pública
            $table->string('disk', 20)->default('local');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'patient_id']);
            $table->index(['clinic_id', 'medical_record_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_files');
    }
};
