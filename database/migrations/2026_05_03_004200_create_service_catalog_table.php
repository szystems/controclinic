<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_catalog', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('clinic_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->enum('type', ['service', 'product'])->default('service');
            $table->string('sku')->nullable();          // código interno opcional
            $table->text('description')->nullable();
            $table->decimal('default_price', 12, 2)->default(0);
            $table->decimal('tax_rate_override', 5, 2)->nullable(); // null = usa el default de la clínica
            $table->string('unit', 30)->default('unit'); // 'unit','hour','session','mg','ml'…
            $table->boolean('is_active')->default(true);

            // Forward-compat Level C — stock control (inactivo por defecto)
            $table->boolean('track_stock')->default(false);
            $table->decimal('stock_quantity', 12, 3)->nullable(); // stock actual
            $table->decimal('stock_alert_at', 12, 3)->nullable(); // alerta mínimo

            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'is_active']);
            $table->index(['clinic_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_catalog');
    }
};
