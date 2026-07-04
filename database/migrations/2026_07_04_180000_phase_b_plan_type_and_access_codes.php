<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('access_code', 64)->nullable()->after('requires_code');
            $table->index('access_code');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE clinics MODIFY plan_type VARCHAR(50) NOT NULL DEFAULT 'free'");
        } else {
            Schema::table('clinics', function (Blueprint $table) {
                $table->string('plan_type', 50)->default('free')->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropIndex(['access_code']);
            $table->dropColumn('access_code');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE clinics MODIFY plan_type ENUM('free','solo','group','enterprise') NOT NULL DEFAULT 'free'");
        }
    }
};
