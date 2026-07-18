<?php

namespace App\Domain\GrowNet\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowNetUser extends Model
{
    protected $fillable = [
        'user_id',
        'referrer_id', 'referral_code', 'referral_count', 'last_referral_at',
        'direct_referrals', 'rank', 'matrix_position', 'network_path', 'network_level',
        'current_professional_level', 'level_achieved_at', 'is_currently_active',
        'balance', 'total_earnings', 'total_investment_amount',
        'total_referral_earnings', 'total_profit_earnings', 'bonus_balance',
        'current_team_volume', 'current_personal_volume', 'current_team_depth',
        'active_referrals_count',
        'monthly_subscription_fee', 'subscription_start_date', 'subscription_end_date',
        'subscription_status',
        'tier_upgraded_at', 'tier_history',
        'loyalty_points', 'loyalty_points_awarded_total', 'loyalty_points_withdrawn_total',
        'lgr_custom_withdrawable_percentage', 'lgr_withdrawal_blocked', 'lgr_restriction_reason',
        'wallet_policy_accepted', 'wallet_policy_accepted_at',
        'verification_level', 'verification_completed_at',
        'daily_withdrawal_used', 'daily_withdrawal_reset_date',
        'has_starter_kit', 'starter_kit_tier', 'starter_kit_purchased_at',
        'starter_kit_terms_accepted', 'starter_kit_terms_accepted_at',
        'starter_kit_shop_credit', 'starter_kit_credit_expiry', 'library_access_until',
        'loan_balance', 'loan_limit', 'total_loan_issued', 'total_loan_repaid',
        'loan_issued_at', 'loan_issued_by', 'loan_notes',
        'life_points', 'bonus_points', 'points_last_reset_at',
        'current_streak_months', 'longest_streak_months', 'performance_tier',
        'courses_completed_count', 'days_active_count',
    ];

    protected function casts(): array
    {
        return [
            'matrix_position' => 'array',
            'tier_history' => 'array',
            'last_referral_at' => 'datetime',
            'tier_upgraded_at' => 'datetime',
            'level_achieved_at' => 'datetime',
            'wallet_policy_accepted_at' => 'datetime',
            'verification_completed_at' => 'datetime',
            'starter_kit_purchased_at' => 'datetime',
            'starter_kit_terms_accepted_at' => 'datetime',
            'loan_issued_at' => 'datetime',
            'points_last_reset_at' => 'datetime',
            'library_access_until' => 'datetime',
            'subscription_start_date' => 'date',
            'subscription_end_date' => 'date',
            'starter_kit_credit_expiry' => 'date',
            'daily_withdrawal_reset_date' => 'date',
            'is_currently_active' => 'boolean',
            'lgr_withdrawal_blocked' => 'boolean',
            'wallet_policy_accepted' => 'boolean',
            'has_starter_kit' => 'boolean',
            'starter_kit_terms_accepted' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }
}
