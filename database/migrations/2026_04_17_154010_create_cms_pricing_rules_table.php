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
        Schema::create('cms_pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained('cms_companies')->onDelete('cascade');
            
            // Selling prices per m² (what customer pays)
            $table->decimal('sliding_window_rate', 10, 2)->default(0);
            $table->decimal('casement_window_rate', 10, 2)->default(0);
            $table->decimal('sliding_door_rate', 10, 2)->default(0);
            $table->decimal('hinged_door_rate', 10, 2)->default(0);
            $table->decimal('other_rate', 10, 2)->default(0);
            
            // Internal costs per m² (for profit calculation - NOT shown to client)
            $table->decimal('material_cost_per_m2', 10, 2)->default(0);
            $table->decimal('labour_cost_per_m2', 10, 2)->default(0);
            $table->decimal('overhead_cost_per_m2', 10, 2)->default(0);
            
            // Profit rules
            $table->decimal('minimum_profit_percent', 5, 2)->default(20.00);
            
            // Tax
            $table->decimal('tax_rate', 5, 2)->default(16.00);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_pricing_rules');
    }
};
