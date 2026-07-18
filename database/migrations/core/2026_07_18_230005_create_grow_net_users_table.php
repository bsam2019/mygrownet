<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grow_net_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Referral / network
            $table->foreignId('referrer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('referral_code')->nullable()->unique();
            $table->integer('referral_count')->default(0);
            $table->timestamp('last_referral_at')->nullable();
            $table->integer('direct_referrals')->default(0);
            $table->string('rank')->nullable();
            $table->json('matrix_position')->nullable();
            $table->string('network_path')->nullable();
            $table->integer('network_level')->default(0);
            $table->string('current_professional_level')->nullable();
            $table->timestamp('level_achieved_at')->nullable();
            $table->boolean('is_currently_active')->default(true);
            // Financial
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('total_earnings', 15, 2)->default(0);
            $table->decimal('total_investment_amount', 15, 2)->default(0);
            $table->decimal('total_referral_earnings', 15, 2)->default(0);
            $table->decimal('total_profit_earnings', 15, 2)->default(0);
            $table->decimal('bonus_balance', 15, 2)->default(0);
            // Team volume
            $table->decimal('current_team_volume', 15, 2)->default(0);
            $table->decimal('current_personal_volume', 15, 2)->default(0);
            $table->integer('current_team_depth')->default(0);
            $table->integer('active_referrals_count')->default(0);
            // Subscription
            $table->decimal('monthly_subscription_fee', 8, 2)->default(0);
            $table->date('subscription_start_date')->nullable();
            $table->date('subscription_end_date')->nullable();
            $table->string('subscription_status')->nullable();
            // Tier
            $table->timestamp('tier_upgraded_at')->nullable();
            $table->json('tier_history')->nullable();
            // Loyalty points (LGR)
            $table->decimal('loyalty_points', 15, 2)->default(0);
            $table->decimal('loyalty_points_awarded_total', 15, 2)->default(0);
            $table->decimal('loyalty_points_withdrawn_total', 15, 2)->default(0);
            $table->decimal('lgr_custom_withdrawable_percentage', 5, 2)->nullable();
            $table->boolean('lgr_withdrawal_blocked')->default(false);
            $table->text('lgr_restriction_reason')->nullable();
            // Wallet / verification
            $table->boolean('wallet_policy_accepted')->default(false);
            $table->timestamp('wallet_policy_accepted_at')->nullable();
            $table->integer('verification_level')->default(0);
            $table->timestamp('verification_completed_at')->nullable();
            $table->decimal('daily_withdrawal_used', 15, 2)->default(0);
            $table->date('daily_withdrawal_reset_date')->nullable();
            // Starter kit
            $table->boolean('has_starter_kit')->default(false);
            $table->string('starter_kit_tier')->nullable();
            $table->timestamp('starter_kit_purchased_at')->nullable();
            $table->boolean('starter_kit_terms_accepted')->default(false);
            $table->timestamp('starter_kit_terms_accepted_at')->nullable();
            $table->decimal('starter_kit_shop_credit', 10, 2)->default(0);
            $table->date('starter_kit_credit_expiry')->nullable();
            $table->timestamp('library_access_until')->nullable();
            // Loan
            $table->decimal('loan_balance', 15, 2)->default(0);
            $table->decimal('loan_limit', 15, 2)->default(0);
            $table->decimal('total_loan_issued', 15, 2)->default(0);
            $table->decimal('total_loan_repaid', 15, 2)->default(0);
            $table->timestamp('loan_issued_at')->nullable();
            $table->foreignId('loan_issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('loan_notes')->nullable();
            // Points system
            $table->decimal('life_points', 10, 2)->default(0);
            $table->decimal('bonus_points', 10, 2)->default(0);
            $table->timestamp('points_last_reset_at')->nullable();
            $table->integer('current_streak_months')->default(0);
            $table->integer('longest_streak_months')->default(0);
            $table->string('performance_tier')->nullable();
            $table->integer('courses_completed_count')->default(0);
            $table->integer('days_active_count')->default(0);
            // Timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grow_net_users');
    }
};
