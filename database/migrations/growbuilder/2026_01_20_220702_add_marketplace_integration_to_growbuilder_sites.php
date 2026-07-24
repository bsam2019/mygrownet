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
        // Add marketplace integration to GrowBuilder sites
        Schema::table('growbuilder_sites', function (Blueprint $table) {
            $table->foreignId('marketplace_seller_id')->nullable()->after('user_id')->constrained('marketplace_sellers')->nullOnDelete();
            $table->boolean('marketplace_enabled')->default(false)->after('marketplace_seller_id');
            $table->timestamp('marketplace_linked_at')->nullable()->after('marketplace_enabled');
        });

        // Add GrowBuilder site reference to marketplace sellers
        Schema::table('marketplace_sellers', function (Blueprint $table) {
            $table->foreignId('growbuilder_site_id')->nullable()->after('user_id')->constrained('growbuilder_sites')->nullOnDelete();
        });

        // Add source tracking to marketplace orders
        Schema::table('marketplace_orders', function (Blueprint $table) {
            $table->string('source')->default('marketplace')->after('seller_id'); // 'marketplace' or 'growbuilder'
            $table->foreignId('source_site_id')->nullable()->after('source')->constrained('growbuilder_sites')->nullOnDelete();
        });

        // Add source tracking to marketplace products (optional - for future use)
        Schema::table('marketplace_products', function (Blueprint $table) {
            $table->string('source')->default('marketplace')->after('seller_id'); // 'marketplace' or 'growbuilder'
            $table->foreignId('source_site_id')->nullable()->after('source')->constrained('growbuilder_sites')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_products', function (Blueprint $table) {
            $table->dropForeign(['source_site_id']);
            $table->dropColumn(['source', 'source_site_id']);
        });

        Schema::table('marketplace_orders', function (Blueprint $table) {
            $table->dropForeign(['source_site_id']);
            $table->dropColumn(['source', 'source_site_id']);
        });

        Schema::table('marketplace_sellers', function (Blueprint $table) {
            $table->dropForeign(['growbuilder_site_id']);
            $table->dropColumn('growbuilder_site_id');
        });

        Schema::table('growbuilder_sites', function (Blueprint $table) {
            $table->dropForeign(['marketplace_seller_id']);
            $table->dropColumn(['marketplace_seller_id', 'marketplace_enabled', 'marketplace_linked_at']);
        });
    }
};
