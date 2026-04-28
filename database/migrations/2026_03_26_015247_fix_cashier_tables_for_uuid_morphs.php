<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clinic uses UUIDs, so Cashier's billable morph columns must be char(36).
     */
    public function up(): void
    {
        $tables = ['customers', 'subscriptions', 'transactions'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->char('billable_id', 36)->change();
            });
        }
    }

    public function down(): void
    {
        $tables = ['customers', 'subscriptions', 'transactions'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->unsignedBigInteger('billable_id')->change();
            });
        }
    }
};
