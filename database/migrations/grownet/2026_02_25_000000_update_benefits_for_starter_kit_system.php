<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add new columns to benefits table
        Schema::table('benefits', function (Blueprint $table) {
            $table->enum('benefit_type', ['starter_kit', 'monthly_service', 'physical_item'])->default('starter_kit')->after('category');
            $table->string('unit')->nullable()->after('description'); // e.g., 'GB', 'months', 'pieces'
            $table->integer('sort_order')->default(0)->after('is_coming_soon');
            $table->json('tier_allocations')->nullable()->after('sort_order'); // Storage allocations per tier
            
            $table->index('benefit_type');
            $table->index('sort_order');
        });

        // Add physical item tracking to starter_kit_benefits pivot
        Schema::table('starter_kit_benefits', function (Blueprint $table) {
            $table->enum('fulfillment_status', ['pending', 'issued', 'delivered'])->nullable()->after('limit_value');
            $table->timestamp('issued_at')->nullable()->after('fulfillment_status');
            $table->timestamp('delivered_at')->nullable()->after('issued_at');
            $table->text('fulfillment_notes')->nullable()->after('delivered_at');
            
            $table->index('fulfillment_status');
        });
    }

    public function down(): void
    {
        Schema::table('benefits', function (Blueprint $table) {
            $table->dropColumn(['benefit_type', 'unit', 'sort_order', 'tier_allocations']);
        });

        Schema::table('starter_kit_benefits', function (Blueprint $table) {
            $table->dropColumn(['fulfillment_status', 'issued_at', 'delivered_at', 'fulfillment_notes']);
        });
    }
};
