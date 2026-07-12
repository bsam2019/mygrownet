<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cms_stock_locations')) {
            Schema::table('cms_stock_locations', function (Blueprint $table) {
                if (Schema::hasColumn('cms_stock_locations', 'location_code')) {
                    $table->renameColumn('location_code', 'code');
                }
                if (Schema::hasColumn('cms_stock_locations', 'location_name')) {
                    $table->renameColumn('location_name', 'name');
                }
                if (Schema::hasColumn('cms_stock_locations', 'location_type')) {
                    $table->renameColumn('location_type', 'type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cms_stock_locations')) {
            Schema::table('cms_stock_locations', function (Blueprint $table) {
                if (Schema::hasColumn('cms_stock_locations', 'code')) {
                    $table->renameColumn('code', 'location_code');
                }
                if (Schema::hasColumn('cms_stock_locations', 'name')) {
                    $table->renameColumn('name', 'location_name');
                }
                if (Schema::hasColumn('cms_stock_locations', 'type')) {
                    $table->renameColumn('type', 'location_type');
                }
            });
        }
    }
};
