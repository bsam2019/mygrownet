<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Updates account_type enum to include all values from AccountType enum:
     * - member: MLM participant
     * - client: App user (no MLM)
     * - business: SME owner (CMS access)
     * - investor: Venture Builder investor
     * - employee: Internal staff
     */
    public function up(): void
    {
        // MySQL doesn't support ALTER ENUM directly, so we need to use MODIFY
        DB::statement("
            ALTER TABLE users 
            MODIFY COLUMN account_type ENUM('member', 'client', 'business', 'investor', 'employee', 'admin') 
            DEFAULT 'client'
            COMMENT 'Account type: member=MLM, client=app user, business=SME, investor=ventures, employee=staff, admin=legacy'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE users 
            MODIFY COLUMN account_type ENUM('member', 'client', 'business') 
            DEFAULT 'client'
        ");
    }
};
