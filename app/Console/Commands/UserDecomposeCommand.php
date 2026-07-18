<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UserDecomposeCommand extends Command
{
    protected $signature = 'user:decompose
        {--dry-run : Preview changes without writing to database}
    ';

    protected $description = 'Extract User fields into user_identities and grow_net_users tables';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $total = User::count();
        $this->info("Processing {$total} users...");

        $identityCreated = 0;
        $identitySkipped = 0;
        $grownetCreated = 0;
        $grownetSkipped = 0;

        $this->newLine();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        User::query()->each(function (User $user) use ($dryRun, &$identityCreated, &$identitySkipped, &$grownetCreated, &$grownetSkipped, $bar) {
            // --- User Identity ---
            if (!$user->identity) {
                if (!$dryRun) {
                    $user->identity()->create([
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'address' => $user->address,
                        'preferred_dashboard' => $user->preferred_dashboard ?? 'auto',
                        'user_currency' => $user->user_currency ?? 'ZMW',
                        'preferred_currency' => $user->preferred_currency ?? 'ZMW',
                        'fcm_token' => $user->fcm_token,
                        'preferences' => null,
                    ]);
                }
                $identityCreated++;
            } else {
                $identitySkipped++;
            }

            // --- GrowNet User ---
            if (!$user->growNetData) {
                if (!$dryRun) {
                    $user->growNetData()->create([
                        'referrer_id' => $user->referrer_id,
                        'referral_code' => $user->referral_code,
                        'referral_count' => $user->referral_count ?? 0,
                        'last_referral_at' => $user->last_referral_at,
                        'direct_referrals' => $user->direct_referrals ?? 0,
                        'rank' => $user->rank,
                        'matrix_position' => $user->matrix_position,
                        'network_path' => $user->network_path,
                        'network_level' => $user->network_level ?? 0,
                        'current_professional_level' => $user->current_professional_level,
                        'level_achieved_at' => $user->level_achieved_at,
                        'is_currently_active' => $user->is_currently_active ?? true,
                        'balance' => $user->balance ?? 0,
                        'total_earnings' => $user->total_earnings ?? 0,
                        'total_investment_amount' => $user->total_investment_amount ?? 0,
                        'total_referral_earnings' => $user->total_referral_earnings ?? 0,
                        'total_profit_earnings' => $user->total_profit_earnings ?? 0,
                        'bonus_balance' => $user->bonus_balance ?? 0,
                        'current_team_volume' => $user->current_team_volume ?? 0,
                        'current_personal_volume' => $user->current_personal_volume ?? 0,
                        'current_team_depth' => $user->current_team_depth ?? 0,
                        'active_referrals_count' => $user->active_referrals_count ?? 0,
                        'monthly_subscription_fee' => $user->monthly_subscription_fee ?? 0,
                        'subscription_start_date' => $user->subscription_start_date,
                        'subscription_end_date' => $user->subscription_end_date,
                        'subscription_status' => $user->subscription_status,
                        'tier_upgraded_at' => $user->tier_upgraded_at,
                        'tier_history' => $user->tier_history,
                        'loyalty_points' => $user->loyalty_points ?? 0,
                        'loyalty_points_awarded_total' => $user->loyalty_points_awarded_total ?? 0,
                        'loyalty_points_withdrawn_total' => $user->loyalty_points_withdrawn_total ?? 0,
                        'lgr_custom_withdrawable_percentage' => $user->lgr_custom_withdrawable_percentage,
                        'lgr_withdrawal_blocked' => $user->lgr_withdrawal_blocked ?? false,
                        'lgr_restriction_reason' => $user->lgr_restriction_reason,
                        'wallet_policy_accepted' => $user->wallet_policy_accepted ?? false,
                        'wallet_policy_accepted_at' => $user->wallet_policy_accepted_at,
                        'verification_level' => $user->verification_level ?? 0,
                        'verification_completed_at' => $user->verification_completed_at,
                        'daily_withdrawal_used' => $user->daily_withdrawal_used ?? 0,
                        'daily_withdrawal_reset_date' => $user->daily_withdrawal_reset_date,
                        'has_starter_kit' => $user->has_starter_kit ?? false,
                        'starter_kit_tier' => $user->starter_kit_tier,
                        'starter_kit_purchased_at' => $user->starter_kit_purchased_at,
                        'starter_kit_terms_accepted' => $user->starter_kit_terms_accepted ?? false,
                        'starter_kit_terms_accepted_at' => $user->starter_kit_terms_accepted_at,
                        'starter_kit_shop_credit' => $user->starter_kit_shop_credit ?? 0,
                        'starter_kit_credit_expiry' => $user->starter_kit_credit_expiry,
                        'library_access_until' => $user->library_access_until,
                        'loan_balance' => $user->loan_balance ?? 0,
                        'loan_limit' => $user->loan_limit ?? 0,
                        'total_loan_issued' => $user->total_loan_issued ?? 0,
                        'total_loan_repaid' => $user->total_loan_repaid ?? 0,
                        'loan_issued_at' => $user->loan_issued_at,
                        'loan_issued_by' => $user->loan_issued_by,
                        'loan_notes' => $user->loan_notes,
                        'life_points' => $user->life_points ?? 0,
                        'bonus_points' => $user->bonus_points ?? 0,
                        'points_last_reset_at' => $user->points_last_reset_at,
                        'current_streak_months' => $user->current_streak_months ?? 0,
                        'longest_streak_months' => $user->longest_streak_months ?? 0,
                        'performance_tier' => $user->performance_tier,
                        'courses_completed_count' => $user->courses_completed_count ?? 0,
                        'days_active_count' => $user->days_active_count ?? 0,
                    ]);
                }
                $grownetCreated++;
            } else {
                $grownetSkipped++;
            }

            $bar->advance();
        });

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Created', 'Skipped'],
            [
                ['User Identities', $identityCreated, $identitySkipped],
                ['GrowNet Users', $grownetCreated, $grownetSkipped],
            ]
        );

        return static::SUCCESS;
    }
}
