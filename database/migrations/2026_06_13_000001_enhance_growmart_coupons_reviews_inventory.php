<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('growmart_coupons', function (Blueprint $table) {
            $table->integer('buy_quantity')->nullable()->after('max_discount');
            $table->integer('get_quantity')->nullable()->after('buy_quantity');
        });

        Schema::table('growmart_reviews', function (Blueprint $table) {
            $table->boolean('is_verified_purchase')->default(false)->after('review_text');
            $table->integer('helpful_count')->default(0)->after('is_verified_purchase');
            $table->text('seller_response')->nullable()->after('helpful_count');
            $table->timestamp('seller_responded_at')->nullable()->after('seller_response');
        });

        Schema::table('growmart_inventory', function (Blueprint $table) {
            $table->timestamp('alert_sent_at')->nullable()->after('low_stock_threshold');
        });
    }

    public function down(): void
    {
        Schema::table('growmart_coupons', function (Blueprint $table) {
            $table->dropColumn(['buy_quantity', 'get_quantity']);
        });

        Schema::table('growmart_reviews', function (Blueprint $table) {
            $table->dropColumn(['is_verified_purchase', 'helpful_count', 'seller_response', 'seller_responded_at']);
        });

        Schema::table('growmart_inventory', function (Blueprint $table) {
            $table->dropColumn('alert_sent_at');
        });
    }
};
