<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL, we need to alter the enum column to add 'deleted' status
        DB::statement("ALTER TABLE growbuilder_sites MODIFY COLUMN status ENUM('draft', 'published', 'suspended', 'deleted') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        // Remove 'deleted' status from enum
        // Note: This will fail if any rows have status = 'deleted'
        DB::statement("ALTER TABLE growbuilder_sites MODIFY COLUMN status ENUM('draft', 'published', 'suspended') NOT NULL DEFAULT 'draft'");
    }
};
