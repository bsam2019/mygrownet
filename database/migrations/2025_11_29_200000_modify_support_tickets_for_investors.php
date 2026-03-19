<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify support_tickets table to support investor tickets
        Schema::table('support_tickets', function (Blueprint $table) {
            // Make user_id nullable for investor tickets (they use investor_account_id instead)
            $table->unsignedBigInteger('user_id')->nullable()->change();
            
            // Add investor_account_id for investor tickets (only if it doesn't exist)
            if (!Schema::hasColumn('support_tickets', 'investor_account_id')) {
                $table->unsignedBigInteger('investor_account_id')->nullable()->after('user_id');
                $table->index('investor_account_id');
            }
            
            // Add source field to identify where ticket came from (only if it doesn't exist)
            if (!Schema::hasColumn('support_tickets', 'source')) {
                $table->string('source', 50)->default('member')->after('category');
                $table->index('source');
            }
        });

        // Change category from ENUM to VARCHAR to allow more flexibility
        // Check if we're using SQLite
        if (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite doesn't have ENUM types, so no need to change anything
            // The column is already TEXT/VARCHAR
        } else {
            // First check if it's an ENUM for MySQL
            $columnType = DB::select("SHOW COLUMNS FROM support_tickets WHERE Field = 'category'")[0]->Type ?? '';
            
            if (str_contains($columnType, 'enum')) {
                // Change ENUM to VARCHAR
                DB::statement("ALTER TABLE support_tickets MODIFY COLUMN category VARCHAR(50) NOT NULL DEFAULT 'general'");
            }
        }

        // Modify ticket_comments table to support investor comments
        Schema::table('ticket_comments', function (Blueprint $table) {
            // Make user_id nullable for investor comments
            $table->unsignedBigInteger('user_id')->nullable()->change();
            
            // Add investor_account_id for investor comments (only if it doesn't exist)
            if (!Schema::hasColumn('ticket_comments', 'investor_account_id')) {
                $table->unsignedBigInteger('investor_account_id')->nullable()->after('user_id');
            }
            
            // Add author_type to distinguish between user types (only if it doesn't exist)
            if (!Schema::hasColumn('ticket_comments', 'author_type')) {
                $table->string('author_type', 20)->default('user')->after('investor_account_id');
            }
            
            // Add author_name for display purposes (only if it doesn't exist)
            if (!Schema::hasColumn('ticket_comments', 'author_name')) {
                $table->string('author_name')->nullable()->after('author_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->dropColumn(['investor_account_id', 'author_type', 'author_name']);
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropIndex(['investor_account_id']);
            $table->dropIndex(['source']);
            $table->dropColumn(['investor_account_id', 'source']);
        });
    }
};
