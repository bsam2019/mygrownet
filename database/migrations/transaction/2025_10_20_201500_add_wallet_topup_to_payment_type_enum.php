<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        // For MySQL, we need to alter the enum
        DB::statement("ALTER TABLE member_payments MODIFY COLUMN payment_type ENUM('wallet_topup', 'subscription', 'workshop', 'product', 'learning_pack', 'coaching', 'upgrade', 'other') NOT NULL");
    }

    public function down(): void
    {
        // Check if we're using SQLite
        if (DB::connection()->getDriverName() === 'sqlite') {
            // No changes needed for SQLite
            return;
        }

        DB::statement("ALTER TABLE member_payments MODIFY COLUMN payment_type ENUM('subscription', 'workshop', 'product', 'learning_pack', 'coaching', 'upgrade', 'other') NOT NULL");
    }
};
