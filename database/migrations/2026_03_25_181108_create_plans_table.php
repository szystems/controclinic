<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // "Solo", "Group"
            $table->string('slug')->unique();                // "solo", "group"
            $table->text('description')->nullable();

            // Limits (null = unlimited)
            $table->integer('max_patients')->nullable()->default(25);
            $table->integer('max_appointments_per_month')->nullable()->default(5);
            $table->integer('max_doctors')->nullable()->default(1);
            $table->integer('max_staff')->nullable()->default(0);
            $table->bigInteger('max_storage_bytes')->nullable()->default(524288000);

            // Features
            $table->json('features')->nullable();            // ["ai", "booking", ...]

            // Pricing
            $table->decimal('monthly_price', 8, 2)->nullable();  // null = contact/free
            $table->decimal('yearly_price', 8, 2)->nullable();

            // Paddle integration
            $table->string('paddle_monthly_price_id')->nullable();
            $table->string('paddle_yearly_price_id')->nullable();
            $table->string('paddle_product_id')->nullable();

            // Trial
            $table->integer('trial_days')->default(0);

            // Display
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_free')->default(false);
            $table->boolean('is_enterprise')->default(false); // contact sales

            $table->timestamps();
        });

        // Add plan_id to clinics
        Schema::table('clinics', function (Blueprint $table) {
            $table->unsignedBigInteger('plan_id')->nullable()->after('plan_type');
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn('plan_id');
        });
        Schema::dropIfExists('plans');
    }
};
