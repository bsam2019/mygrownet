<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tier Configuration Table
        Schema::create('starter_kit_tier_configs', function (Blueprint $table) {
            $table->id();
            $table->string('tier_key')->unique(); // lite, basic, growth_plus, pro
            $table->string('tier_name'); // Lite, Basic, Growth Plus, Pro
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('storage_gb');
            $table->decimal('earning_potential_percentage', 5, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('tier_key');
            $table->index('is_active');
            $table->index('sort_order');
        });

        // Tier Benefits Pivot Table
        Schema::create('starter_kit_tier_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tier_config_id')->constrained('starter_kit_tier_configs')->onDelete('cascade');
            $table->foreignId('benefit_id')->constrained('benefits')->onDelete('cascade');
            $table->boolean('is_included')->default(true);
            $table->integer('limit_value')->nullable(); // For storage GB, etc.
            $table->timestamps();

            $table->unique(['tier_config_id', 'benefit_id']);
            $table->index('tier_config_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('starter_kit_tier_benefits');
        Schema::dropIfExists('starter_kit_tier_configs');
    }
};
