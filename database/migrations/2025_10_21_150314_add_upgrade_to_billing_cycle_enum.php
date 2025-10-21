<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE packages MODIFY COLUMN billing_cycle ENUM('one-time', 'monthly', 'quarterly', 'annual', 'upgrade') DEFAULT 'monthly'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE packages MODIFY COLUMN billing_cycle ENUM('one-time', 'monthly', 'quarterly', 'annual') DEFAULT 'monthly'");
    }
};
