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
        // Add BizBoost integration to marketplace_sellers
        Schema::table('marketplace_sellers', function (Blueprint $table) {
            $table->unsignedBigInteger('bizboost_business_id')->nullable()->after('user_id');
            $table->boolean('is_bizboost_synced')->default(false)->after('bizboost_business_id');
            
            $table->foreign('bizboost_business_id')
                  ->references('id')
                  ->on('bizboost_businesses')
                  ->onDelete('set null');
            
            $table->index('bizboost_business_id');
        });

        // Add BizBoost integration to marketplace_products
        Schema::table('marketplace_products', function (Blueprint $table) {
            $table->unsignedBigInteger('bizboost_product_id')->nullable()->after('seller_id');
            $table->boolean('is_bizboost_synced')->default(false)->after('bizboost_product_id');
            
            $table->foreign('bizboost_product_id')
                  ->references('id')
                  ->on('bizboost_products')
                  ->onDelete('cascade');
            
            $table->index('bizboost_product_id');
        });

        // Add marketplace integration to bizboost_businesses
        Schema::table('bizboost_businesses', function (Blueprint $table) {
            $table->unsignedBigInteger('marketplace_seller_id')->nullable()->after('marketplace_listed_at');
            $table->boolean('marketplace_sync_enabled')->default(true)->after('marketplace_seller_id');
            $table->timestamp('marketplace_synced_at')->nullable()->after('marketplace_sync_enabled');
            
            $table->foreign('marketplace_seller_id')
                  ->references('id')
                  ->on('marketplace_sellers')
                  ->onDelete('set null');
            
            $table->index('marketplace_seller_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_sellers', function (Blueprint $table) {
            $table->dropForeign(['bizboost_business_id']);
            $table->dropColumn(['bizboost_business_id', 'is_bizboost_synced']);
        });

        Schema::table('marketplace_products', function (Blueprint $table) {
            $table->dropForeign(['bizboost_product_id']);
            $table->dropColumn(['bizboost_product_id', 'is_bizboost_synced']);
        });

        Schema::table('bizboost_businesses', function (Blueprint $table) {
            $table->dropForeign(['marketplace_seller_id']);
            $table->dropColumn(['marketplace_seller_id', 'marketplace_sync_enabled', 'marketplace_synced_at']);
        });
    }
};
