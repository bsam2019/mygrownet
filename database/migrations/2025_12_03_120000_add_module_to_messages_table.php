<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add module field to messages table for multi-app context support.
     * This allows the centralized messaging system to be used across all apps
     * (MyGrowNet, GrowFinance, GrowBiz, etc.) while maintaining context.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('module', 50)->default('mygrownet')->after('parent_id');
            $table->json('metadata')->nullable()->after('module'); // For app-specific data
            
            // Index for filtering by module
            $table->index(['recipient_id', 'module', 'is_read']);
            $table->index(['sender_id', 'module', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['recipient_id', 'module', 'is_read']);
            $table->dropIndex(['sender_id', 'module', 'created_at']);
            $table->dropColumn(['module', 'metadata']);
        });
    }
};
