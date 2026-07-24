<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Wallet policy acceptance
            $table->boolean('wallet_policy_accepted')->default(false)->after('email_verified_at');
            $table->timestamp('wallet_policy_accepted_at')->nullable()->after('wallet_policy_accepted');
            
            // Bonus/Rewards balance (separate from cash balance)
            $table->decimal('bonus_balance', 10, 2)->default(0)->after('wallet_policy_accepted_at');
            $table->decimal('loyalty_points', 10, 2)->default(0)->after('bonus_balance');
            
            // Verification level for transaction limits
            $table->enum('verification_level', ['basic', 'enhanced', 'premium'])->default('basic')->after('loyalty_points');
            $table->timestamp('verification_completed_at')->nullable()->after('verification_level');
            
            // Daily withdrawal tracking
            $table->decimal('daily_withdrawal_used', 10, 2)->default(0)->after('verification_completed_at');
            $table->date('daily_withdrawal_reset_date')->nullable()->after('daily_withdrawal_used');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'wallet_policy_accepted',
                'wallet_policy_accepted_at',
                'bonus_balance',
                'loyalty_points',
                'verification_level',
                'verification_completed_at',
                'daily_withdrawal_used',
                'daily_withdrawal_reset_date',
            ]);
        });
    }
};
