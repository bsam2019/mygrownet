<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add unread tracking and rating fields to support_tickets
        Schema::table('support_tickets', function (Blueprint $table) {
            // Track when user/admin last viewed the ticket
            $table->timestamp('user_last_read_at')->nullable()->after('closed_at');
            $table->timestamp('admin_last_read_at')->nullable()->after('user_last_read_at');
            
            // Rating feedback (if not exists)
            if (!Schema::hasColumn('support_tickets', 'satisfaction_rating')) {
                $table->tinyInteger('satisfaction_rating')->nullable()->after('admin_last_read_at');
            }
            $table->text('rating_feedback')->nullable()->after('satisfaction_rating');
            $table->timestamp('rated_at')->nullable()->after('rating_feedback');
        });

        // Add read tracking to comments
        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->boolean('read_by_user')->default(false)->after('is_internal');
            $table->boolean('read_by_admin')->default(false)->after('read_by_user');
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn(['user_last_read_at', 'admin_last_read_at', 'rating_feedback', 'rated_at']);
        });

        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->dropColumn(['read_by_user', 'read_by_admin']);
        });
    }
};
