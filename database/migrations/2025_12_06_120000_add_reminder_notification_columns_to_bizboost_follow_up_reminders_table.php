<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bizboost_follow_up_reminders', function (Blueprint $table) {
            // Add remind_at column for notification scheduling
            $table->timestamp('remind_at')->nullable()->after('due_time');
            
            // Add sent_at column to track when notification was sent
            $table->timestamp('sent_at')->nullable()->after('notification_sent');
            
            // Add notes column for failure reasons
            $table->text('notes')->nullable()->after('completion_notes');
        });

        // Update status enum to include 'sent' and 'failed'
        // For SQLite, we need to recreate the column
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN, so we work around it
            // The status will be handled at application level
        } else {
            DB::statement("ALTER TABLE bizboost_follow_up_reminders MODIFY COLUMN status ENUM('pending', 'sent', 'completed', 'cancelled', 'failed') DEFAULT 'pending'");
        }

        // Populate remind_at from due_date and due_time for existing records
        DB::table('bizboost_follow_up_reminders')
            ->whereNull('remind_at')
            ->update([
                'remind_at' => DB::raw("CONCAT(due_date, ' ', due_time)")
            ]);
    }

    public function down(): void
    {
        Schema::table('bizboost_follow_up_reminders', function (Blueprint $table) {
            $table->dropColumn(['remind_at', 'sent_at', 'notes']);
        });
    }
};
