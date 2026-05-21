<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->text('public_description')->nullable()->after('public_portal_slug');
            $table->string('public_cover_image_url')->nullable()->after('public_description');
            $table->json('public_services')->nullable()->after('public_cover_image_url');
            $table->boolean('public_show_doctors')->default(true)->after('public_services');
            $table->string('public_seo_title')->nullable()->after('public_show_doctors');
            $table->string('public_seo_description', 320)->nullable()->after('public_seo_title');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn([
                'public_description',
                'public_cover_image_url',
                'public_services',
                'public_show_doctors',
                'public_seo_title',
                'public_seo_description',
            ]);
        });
    }
};
