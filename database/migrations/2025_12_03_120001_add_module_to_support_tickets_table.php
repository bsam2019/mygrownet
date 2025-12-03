<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add module field to support_tickets table for multi-app context support.
     * This allows the centralized support system to be used across all apps
     * (MyGrowNet, GrowFinance, GrowBiz, etc.) while maintaining context.
     */
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('support_tickets', 'module')) {
                $table->string('module', 50)->default('mygrownet')->after('source');
                $table->json('metadata')->nullable()->after('module'); // For app-specific data
                
                // Index for filtering by module
                $table->index(['user_id', 'module', 'status']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('support_tickets', 'module')) {
                $table->dropIndex(['user_id', 'module', 'status']);
                $table->dropColumn(['module', 'metadata']);
            }
        });
    }
};
