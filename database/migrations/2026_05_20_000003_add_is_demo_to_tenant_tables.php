<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['patients', 'appointments', 'medical_records', 'invoices', 'prescriptions'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->boolean('is_demo')->default(false)->after('clinic_id');
            });
        }
    }

    public function down(): void
    {
        $tables = ['patients', 'appointments', 'medical_records', 'invoices', 'prescriptions'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('is_demo');
            });
        }
    }
};
