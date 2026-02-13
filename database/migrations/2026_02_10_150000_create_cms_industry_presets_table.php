<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cms_industry_presets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->json('roles')->nullable(); // Predefined roles
            $table->json('expense_categories')->nullable(); // Predefined expense categories
            $table->json('job_types')->nullable(); // Common job types
            $table->json('inventory_categories')->nullable(); // Inventory categories
            $table->json('asset_types')->nullable(); // Asset types
            $table->json('default_settings')->nullable(); // Default company settings
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_industry_presets');
    }
};
