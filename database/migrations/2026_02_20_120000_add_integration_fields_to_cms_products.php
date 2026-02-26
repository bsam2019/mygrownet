<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cms_products')) {
            Schema::table('cms_products', function (Blueprint $table) {
                $table->boolean('sync_to_market')->default(false)->after('is_active');
                $table->timestamp('last_synced_at')->nullable()->after('sync_to_market');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cms_products')) {
            Schema::table('cms_products', function (Blueprint $table) {
                $table->dropColumn(['sync_to_market', 'last_synced_at']);
            });
        }
    }
};
