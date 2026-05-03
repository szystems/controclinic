<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            // FK nullable al catálogo — null = ítem manual, set = ítem desde catálogo
            // Forward-compat: Level C usará esta FK para descontar stock automáticamente
            $table->foreignUuid('catalog_item_id')
                ->nullable()
                ->after('id')
                ->constrained('service_catalog')
                ->nullOnDelete();

            $table->index('catalog_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['catalog_item_id']);
            $table->dropColumn('catalog_item_id');
        });
    }
};
