<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table): void {
            $table->string('custom_domain')->unique()->nullable()->after('public_seo_description');
            $table->timestamp('custom_domain_verified_at')->nullable()->after('custom_domain');
            $table->string('custom_domain_txt_token', 64)->nullable()->after('custom_domain_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table): void {
            $table->dropUnique(['custom_domain']);
            $table->dropColumn(['custom_domain', 'custom_domain_verified_at', 'custom_domain_txt_token']);
        });
    }
};
