<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('method', ['cash', 'card', 'transfer', 'insurance', 'other'])
                  ->default('cash');
            $table->string('reference')->nullable();  // nº transacción, voucher
            $table->text('notes')->nullable();
            $table->datetime('paid_at');

            $table->timestamps();

            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};
