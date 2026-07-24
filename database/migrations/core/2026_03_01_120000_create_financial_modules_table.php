<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_modules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Unique module identifier (e.g., grownet, growbuilder)');
            $table->string('name', 100)->comment('Human-readable module name');
            $table->text('description')->nullable();
            $table->boolean('is_revenue_module')->default(true)->comment('Does this module generate revenue?');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable()->comment('Module-specific settings');
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->index('code');
            $table->index('is_active');
        });

        // Note: Modules are seeded via FinancialModulesSeeder
        // Run: php artisan db:seed --class=FinancialModulesSeeder
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_modules');
    }
};
