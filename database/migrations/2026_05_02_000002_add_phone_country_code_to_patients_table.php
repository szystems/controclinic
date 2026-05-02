<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('phone_country_code', 5)->nullable()->after('phone');
            $table->string('phone_country_code_secondary', 5)->nullable()->after('phone_secondary');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['phone_country_code', 'phone_country_code_secondary']);
        });
    }
};
