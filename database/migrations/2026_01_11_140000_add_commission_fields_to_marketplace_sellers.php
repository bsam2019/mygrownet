<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_sellers', function (Blueprint $table) {
            // Commission rate (percentage, e.g., 10.00 for 10%)
            $table->decimal('commission_rate', 5, 2)->default(10.00)->after('trust_level');
            
            // Performance metrics for automatic tier calculation
            $table->unsignedInteger('completed_orders')->default(0)->after('total_orders');
            $table->unsignedBigInteger('total_sales_amount')->default(0)->after('completed_orders'); // In ngwee
            $table->decimal('dispute_rate', 5, 2)->default(0)->after('total_sales_amount'); // Percentage
            $table->decimal('cancellation_rate', 5, 2)->default(0)->after('dispute_rate'); // Percentage
            $table->decimal('response_rate', 5, 2)->default(100)->after('cancellation_rate'); // Percentage
            $table->timestamp('tier_calculated_at')->nullable()->after('response_rate');
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_sellers', function (Blueprint $table) {
            $table->dropColumn([
                'commission_rate',
                'completed_orders',
                'total_sales_amount',
                'dispute_rate',
                'cancellation_rate',
                'response_rate',
                'tier_calculated_at',
            ]);
        });
    }
};
