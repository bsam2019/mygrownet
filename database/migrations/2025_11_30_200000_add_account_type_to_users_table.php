<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds account_type field to separate:
     * - member: Full MLM participant (joined via referral code)
     * - client: App-only user (shop, apps, venture builder - NO MLM)
     * - business: SME business owner (accounting, staff management tools)
     */
    public function up(): void
    {
        // Only add column if it doesn't exist
        if (!Schema::hasColumn('users', 'account_type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('account_type', ['member', 'client', 'business'])
                    ->default('client')
                    ->after('status')
                    ->comment('member=MLM participant, client=app user, business=SME tools');
            });

            // Migrate existing users: those with referrer_id are members, others are clients
            DB::statement("
                UPDATE users 
                SET account_type = CASE 
                    WHEN referrer_id IS NOT NULL THEN 'member'
                    ELSE 'client'
                END
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('account_type');
        });
    }
};
