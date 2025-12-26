<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add metadata column to growbuilder_media for image optimization data
        Schema::table('growbuilder_media', function (Blueprint $table) {
            if (!Schema::hasColumn('growbuilder_media', 'metadata')) {
                $table->json('metadata')->nullable()->after('variants');
            }
        });

        // Add index on nav_order for better sorting performance
        Schema::table('growbuilder_pages', function (Blueprint $table) {
            $table->index(['site_id', 'nav_order'], 'growbuilder_pages_site_nav_order_index');
        });
    }

    public function down(): void
    {
        Schema::table('growbuilder_media', function (Blueprint $table) {
            if (Schema::hasColumn('growbuilder_media', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });

        Schema::table('growbuilder_pages', function (Blueprint $table) {
            $table->dropIndex('growbuilder_pages_site_nav_order_index');
        });
    }
};
