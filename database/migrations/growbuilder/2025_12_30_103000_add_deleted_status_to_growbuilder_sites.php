<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if we're using SQLite
        if (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite doesn't support MODIFY COLUMN or ENUM
            // The column should already exist as TEXT from the original migration
            // No changes needed for SQLite as it stores enum values as text anyway
            return;
        }

        // For MySQL, we need to alter the enum column to add 'deleted' status
        DB::statement("ALTER TABLE growbuilder_sites MODIFY COLUMN status ENUM('draft', 'published', 'suspended', 'deleted') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        // Check if we're using SQLite
        if (DB::connection()->getDriverName() === 'sqlite') {
            // No changes needed for SQLite
            return;
        }

        // Remove 'deleted' status from enum
        // Note: This will fail if any rows have status = 'deleted'
        DB::statement("ALTER TABLE growbuilder_sites MODIFY COLUMN status ENUM('draft', 'published', 'suspended') NOT NULL DEFAULT 'draft'");
    }
};
