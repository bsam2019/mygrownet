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
        Schema::table('announcements', function (Blueprint $table) {
            // Add user_id for user-specific announcements (nullable for platform-wide announcements)
            $table->foreignId('user_id')->nullable()->after('created_by')->constrained()->onDelete('cascade');
            
            // Add index for faster queries
            $table->index(['user_id', 'is_active', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'is_active', 'expires_at']);
            $table->dropColumn('user_id');
        });
    }
};
