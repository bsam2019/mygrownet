<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lgr_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Package 1, Package 2, etc.
            $table->string('slug')->unique(); // package-1, package-2, etc.
            $table->decimal('package_amount', 10, 2); // Initial investment amount (300, 500, 1000, 2000)
            $table->decimal('daily_lgr_rate', 10, 2); // Daily LGR earning (10, 15, 30, 60)
            $table->integer('duration_days'); // Cycle duration (50, 70, 70, 70)
            $table->decimal('total_reward', 10, 2); // Total possible earnings (500, 1050, 2100, 4200)
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->json('features')->nullable(); // Additional features/benefits
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lgr_packages');
    }
};
