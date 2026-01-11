<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change status from ENUM to VARCHAR to support more statuses
        // MySQL doesn't easily allow adding values to ENUM
        Schema::table('marketplace_products', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->change();
        });
    }

    public function down(): void
    {
        // Revert to ENUM (note: this may fail if there are values not in the enum)
        DB::statement("ALTER TABLE marketplace_products MODIFY COLUMN status ENUM('draft', 'pending', 'active', 'rejected', 'suspended', 'changes_requested') DEFAULT 'draft'");
    }
};
