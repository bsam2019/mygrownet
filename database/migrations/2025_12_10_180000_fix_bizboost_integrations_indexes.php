<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing indexes to bizboost_integrations table
        // The table was created but indexes failed due to long name
        
        // Check if unique index exists
        $uniqueExists = DB::select("SHOW INDEX FROM bizboost_integrations WHERE Key_name = 'bb_integrations_unique'");
        if (empty($uniqueExists)) {
            Schema::table('bizboost_integrations', function (Blueprint $table) {
                $table->unique(['business_id', 'provider', 'provider_page_id'], 'bb_integrations_unique');
            });
        }
        
        // Check if status index exists
        $statusExists = DB::select("SHOW INDEX FROM bizboost_integrations WHERE Key_name = 'bb_integrations_status_idx'");
        if (empty($statusExists)) {
            Schema::table('bizboost_integrations', function (Blueprint $table) {
                $table->index(['business_id', 'provider', 'status'], 'bb_integrations_status_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::table('bizboost_integrations', function (Blueprint $table) {
            $table->dropUnique('bb_integrations_unique');
            $table->dropIndex('bb_integrations_status_idx');
        });
    }
};
