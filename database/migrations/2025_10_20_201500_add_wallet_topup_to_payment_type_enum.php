<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL, we need to alter the enum
        DB::statement("ALTER TABLE member_payments MODIFY COLUMN payment_type ENUM('wallet_topup', 'subscription', 'workshop', 'product', 'learning_pack', 'coaching', 'upgrade', 'other') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE member_payments MODIFY COLUMN payment_type ENUM('subscription', 'workshop', 'product', 'learning_pack', 'coaching', 'upgrade', 'other') NOT NULL");
    }
};
