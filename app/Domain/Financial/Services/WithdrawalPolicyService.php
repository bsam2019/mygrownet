<?php

namespace App\Domain\Financial\Services;

use App\Models\User;
use App\Models\Investment;
use App\Models\WithdrawalRequest;
use App\Models\CommissionClawback;
use App\Models\ReferralCommission;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawalPolicyService
{
    /**
     * Validate withdrawal request with lock-in period checking
     *
     * @param User $user
     * @param float $amount
     * @param string $type
     * @return array
     */
    public function validateWithdrawal(User $user, float $amount, string $type = 'full'): array
    {
        // Get user's active investments
        $activeInvestments = Investment::where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        if ($activeInvestments->isEmpty()) {
            return [
                'valid' => false,
                'reason' => 'no_active_investments',
                'message' => 'No active investments found for withdrawal',
                'penalty_amount' => 0,
                'net_amount' => 0
            ];
        }

        // Check lock-in period for each investment
        $lockInViolations = [];
        $totalCurrentValue = 0;
        $totalPenalty = 0;

        foreach ($activeInvestments as $investment) {
            $currentValue = $investment->getCurrentValue();
            $totalCurrentValue += $currentValue;

            $lockInStatus = $investment->validateLockInPeriod();
            if ($lockInStatus['is_within_lock_in']) {
                $penalties = $investment->calculateWithdrawalPenalties();
                $lockInViolations[] = [
                    'investment_id' => $investment->id,
                    'amount' => $investment->amount,
                    'current_value' => $currentValue,
                    'lock_in_end' => $lockInStatus['lock_in_end_date'],
                    'months_remaining' => $lockInStatus['months_remaining'],
                    'penalty_amount' => $penalties['total_penalty']
                ];
                $totalPenalty += $penalties['total_penalty'];
            }
        }

        // Validate withdrawal amount
        if ($amount > $totalCurrentValue) {
            return [
                'valid' => false,
                'reason' => 'insufficient_balance',
                'message' => "Requested amount (K{$amount}) exceeds available balance (K{$totalCurrentValue})",
                'available_balance' => $totalCurrentValue,
                'penalty_amount' => 0,
                'net_amount' => 0
            ];
        }

        // Check withdrawal type specific validations
        $typeValidation = $this->validateWithdrawalType($user, $amount, $type, $totalCurrentValue);
        if (!$typeValidation['valid']) {
            return $typeValidation;
        }

        // Determine if withdrawal is allowed
        if (empty($lockInViolations)) {
            // No lock-in violations - withdrawal allowed without penalties
            return [
                'valid' => true,
                'reason' => 'withdrawal_allowed',
                'message' => 'Withdrawal approved - no lock-in period violations',
                'withdrawal_type' => $type,
                'penalty_amount' => 0,
                'net_amount' => $amount,
                'lock_in_violations' => []
            ];
        }

        // Lock-in violations exist - check if emergency withdrawal
        if ($type === 'emergency') {
            return [
                'valid' => true,
                'reason' => 'emergency_withdrawal_with_penalties',
                'message' => 'Emergency withdrawal approved with penalties',
                'withdrawal_type' => $type,
                'penalty_amount' => $totalPenalty,
                'net_amount' => max(0, $amount - $totalPenalty),
                'lock_in_violations' => $lockInViolations,
                'requires_approval' => true
            ];
        }

        // Regular withdrawal with lock-in violations - not allowed
        return [
            'valid' => false,
            'reason' => 'lock_in_period_violation',
            'message' => 'Withdrawal not allowed - investments still within lock-in period',
            'withdrawal_type' => $type,
            'penalty_amount' => $totalPenalty,
            'net_amount' => 0,
            'lock_in_violations' => $lockInViolations,
            'earliest_withdrawal_date' => collect($lockInViolations)->min('lock_in_end')
        ];
    }

    /**
     * Validate withdrawal type specific rules
     *
     * @param User $user
     * @param float $amount
     * @param string $type
     * @param float $totalCurrentValue
     * @return array
     */
    protected function validateWithdrawalType(User $user, float $amount, string $type, float $totalCurrentValue): array
    {
        switch ($type) {
            case 'partial':
                // Partial withdrawals limited to 50% of profits
                $totalInvested = $user->investments()->where('status', 'active')->sum('amount');
                $totalProfits = $totalCurrentValue - $totalInvested;
                $maxPartialWithdrawal = $totalProfits * 0.5;

                if ($amount > $maxPartialWithdrawal) {
                    return [
                        'valid' => false,
                        'reason' => 'partial_withdrawal_limit_exceeded',
                        'message' => "Partial withdrawal limited to 50% of profits (K{$maxPartialWithdrawal})",
                        'max_allowed' => $maxPartialWithdrawal,
                        'penalty_amount' => 0,
                        'net_amount' => 0
                    ];
                }
                break;

            case 'profits_only':
                $totalInvested = $user->investments()->where('status', 'active')->sum('amount');
                $totalProfits = $totalCurrentValue - $totalInvested;

                if ($amount > $totalProfits) {
                    return [
                        'valid' => false,
                        'reason' => 'insufficient_profits',
                        'message' => "Requested amount exceeds available profits (K{$totalProfits})",
                        'available_profits' => $totalProfits,
                        'penalty_amount' => 0,
                        'net_amount' => 0
                    ];
                }
                break;

            case 'capital':
                $totalInvested = $user->investments()->where('status', 'active')->sum('amount');

                if ($amount > $totalInvested) {
                    return [
                        'valid' => false,
                        'reason' => 'insufficient_capital',
                        'message' => "Requested amount exceeds invested capital (K{$totalInvested})",
                        'available_capital' => $totalInvested,
                        'penalty_amount' => 0,
                        'net_amount' => 0
                    ];
                }
                break;
        }

        return ['valid' => true];
    }

    /**
     * Calculate penalty based on withdrawal timing and type
     *
     * @param User $user
     * @param float $amount
     * @param string $type
     * @param Carbon $withdrawalDate
     * @return array
     */
    public function calculateWithdrawalPenalty(
        User $user, 
        float $amount, 
        string $type = 'full', 
        Carbon $withdrawalDate = null
    ): array {
        $withdrawalDate = $withdrawalDate ?? now();
        
        $activeInvestments = Investment::where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        if ($activeInvestments->isEmpty()) {
            return [
                'total_penalty' => 0,
                'penalty_breakdown' => [],
                'net_withdrawable' => 0,
                'penalty_applicable' => false
            ];
        }

        $totalPenalty = 0;
        $penaltyBreakdown = [];
        $totalCurrentValue = 0;

        foreach ($activeInvestments as $investment) {
            $currentValue = $investment->getCurrentValue();
            $totalCurrentValue += $currentValue;

            $penaltyData = $investment->calculateTimedWithdrawalPenalty($withdrawalDate);
            
            if ($penaltyData['penalty_applicable']) {
                $investmentPenalty = min($penaltyData['total_penalty_amount'], $currentValue);
                $totalPenalty += $investmentPenalty;

                $penaltyBreakdown[] = [
                    'investment_id' => $investment->id,
                    'investment_amount' => $investment->amount,
                    'current_value' => $currentValue,
                    'penalty_amount' => $investmentPenalty,
                    'penalty_tier' => $penaltyData['penalty_tier'],
                    'months_remaining' => $penaltyData['months_remaining'],
                    'profit_penalty' => $penaltyData['profit_penalty_amount'],
                    'capital_penalty' => $penaltyData['capital_penalty_amount']
                ];
            }
        }

        // Apply tier-specific penalty reductions
        $tier = $user->currentInvestmentTier;
        if ($tier) {
            $penaltyReduction = $tier->getWithdrawalPenaltyReduction();
            $totalPenalty *= (1 - $penaltyReduction);
        }

        $netWithdrawable = max(0, min($amount, $totalCurrentValue) - $totalPenalty);

        return [
            'total_penalty' => $totalPenalty,
            'penalty_breakdown' => $penaltyBreakdown,
            'net_withdrawable' => $netWithdrawable,
            'penalty_applicable' => $totalPenalty > 0,
            'tier_penalty_reduction' => $tier ? $tier->getWithdrawalPenaltyReduction() * 100 : 0,
            'withdrawal_date' => $withdrawalDate,
            'total_current_value' => $totalCurrentValue
        ];
    }

    /**
     * Process commission clawback for early withdrawals
     *
     * @param User $user
     * @param float $withdrawalAmount
     * @param string $withdrawalType
     * @return array
     */
    public function processCommissionClawback(User $user, float $withdrawalAmount, string $withdrawalType): array
    {
        // Get all commissions earned by referrers from this user's investments
        $commissionsToClawback = ReferralCommission::where('referred_id', $user->id)
            ->where('status', 'paid')
            ->with(['referrer', 'investment'])
            ->get();

        if ($commissionsToClawback->isEmpty()) {
            return [
                'clawback_applicable' => false,
                'total_clawback' => 0,
                'clawback_records' => [],
                'affected_referrers' => 0
            ];
        }

        $clawbackRecords = [];
        $totalClawback = 0;
        $affectedReferrers = [];

        try {
            DB::beginTransaction();

            foreach ($commissionsToClawback as $commission) {
                // Calculate clawback amount based on withdrawal percentage
                $investmentValue = $commission->investment->getCurrentValue();
                $withdrawalPercentage = $investmentValue > 0 ? min(1, $withdrawalAmount / $investmentValue) : 0;
                $clawbackAmount = $commission->amount * $withdrawalPercentage;

                if ($clawbackAmount > 0) {
                    // Create clawback record
                    $clawback = CommissionClawback::create([
                        'user_id' => $user->id,
                        'referrer_id' => $commission->referrer_id,
                        'original_commission_id' => $commission->id,
                        'investment_id' => $commission->investment_id,
                        'clawback_amount' => $clawbackAmount,
                        'withdrawal_amount' => $withdrawalAmount,
                        'withdrawal_type' => $withdrawalType,
                        'clawback_percentage' => $withdrawalPercentage * 100,
                        'status' => 'pending',
                        'created_at' => now()
                    ]);

                    // Update referrer's earnings
                    $referrer = $commission->referrer;
                    $referrer->decrement('total_referral_earnings', $clawbackAmount);

                    // Create transaction record for clawback
                    Transaction::create([
                        'user_id' => $referrer->id,
                        'type' => 'commission_clawback',
                        'amount' => -$clawbackAmount,
                        'status' => 'completed',
                        'description' => "Commission clawback due to early withdrawal by {$user->name}",
                        'reference_id' => $clawback->id,
                        'reference_type' => 'commission_clawback'
                    ]);

                    $clawbackRecords[] = [
                        'clawback_id' => $clawback->id,
                        'referrer_id' => $referrer->id,
                        'referrer_name' => $referrer->name,
                        'original_commission' => $commission->amount,
                        'clawback_amount' => $clawbackAmount,
                        'clawback_percentage' => $withdrawalPercentage * 100
                    ];

                    $totalClawback += $clawbackAmount;
                    $affectedReferrers[$referrer->id] = $referrer->name;

                    // Record activity for referrer
                    $referrer->recordActivity(
                        'commission_clawback',
                        "Commission clawback: K{$clawbackAmount} due to early withdrawal by {$user->name}"
                    );
                }
            }

            DB::commit();

            Log::info("Processed commission clawback: K{$totalClawback} from " . count($affectedReferrers) . " referrers for user {$user->id}");

            return [
                'clawback_applicable' => true,
                'total_clawback' => $totalClawback,
                'clawback_records' => $clawbackRecords,
                'affected_referrers' => count($affectedReferrers),
                'referrer_details' => $affectedReferrers
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to process commission clawback for user {$user->id}: " . $e->getMessage());

            return [
                'clawback_applicable' => false,
                'total_clawback' => 0,
                'clawback_records' => [],
                'affected_referrers' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate withdrawal eligibility and amount
     *
     * @param User $user
     * @param float $amount
     * @param string $type
     * @return array
     */
    public function validateWithdrawalEligibility(User $user, float $amount, string $type = 'full'): array
    {
        // Basic validation
        if ($amount <= 0) {
            return [
                'eligible' => false,
                'reason' => 'invalid_amount',
                'message' => 'Withdrawal amount must be greater than zero',
                'max_withdrawable' => 0
            ];
        }

        // Get user's investment summary
        $investmentSummary = $this->getUserInvestmentSummary($user);
        
        if ($investmentSummary['total_current_value'] <= 0) {
            return [
                'eligible' => false,
                'reason' => 'no_withdrawable_balance',
                'message' => 'No withdrawable balance available',
                'max_withdrawable' => 0
            ];
        }

        // Check type-specific limits
        $maxWithdrawable = $this->calculateMaxWithdrawableAmount($user, $type);
        
        if ($amount > $maxWithdrawable) {
            return [
                'eligible' => false,
                'reason' => 'amount_exceeds_limit',
                'message' => "Requested amount exceeds maximum withdrawable amount for {$type} withdrawal",
                'max_withdrawable' => $maxWithdrawable,
                'requested_amount' => $amount
            ];
        }

        // Check lock-in period restrictions
        $lockInCheck = $this->checkLockInRestrictions($user, $type);
        
        if (!$lockInCheck['allowed'] && $type !== 'emergency') {
            return [
                'eligible' => false,
                'reason' => 'lock_in_restriction',
                'message' => $lockInCheck['message'],
                'max_withdrawable' => 0,
                'lock_in_details' => $lockInCheck['details']
            ];
        }

        // Calculate penalties if applicable
        $penaltyData = $this->calculateWithdrawalPenalty($user, $amount, $type);
        
        return [
            'eligible' => true,
            'reason' => 'withdrawal_approved',
            'message' => 'Withdrawal request is eligible for processing',
            'max_withdrawable' => $maxWithdrawable,
            'requested_amount' => $amount,
            'penalty_data' => $penaltyData,
            'net_amount' => $penaltyData['net_withdrawable'],
            'requires_approval' => $type === 'emergency' || $penaltyData['penalty_applicable']
        ];
    }

    /**
     * Get user investment summary
     *
     * @param User $user
     * @return array
     */
    protected function getUserInvestmentSummary(User $user): array
    {
        $activeInvestments = Investment::where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        $totalInvested = $activeInvestments->sum('amount');
        $totalCurrentValue = $activeInvestments->sum(function ($investment) {
            return $investment->getCurrentValue();
        });
        $totalProfits = $totalCurrentValue - $totalInvested;

        return [
            'total_invested' => $totalInvested,
            'total_current_value' => $totalCurrentValue,
            'total_profits' => $totalProfits,
            'investment_count' => $activeInvestments->count(),
            'investments' => $activeInvestments
        ];
    }

    /**
     * Calculate maximum withdrawable amount by type
     *
     * @param User $user
     * @param string $type
     * @return float
     */
    protected function calculateMaxWithdrawableAmount(User $user, string $type): float
    {
        $summary = $this->getUserInvestmentSummary($user);

        return match($type) {
            'full' => $summary['total_current_value'],
            'partial' => $summary['total_profits'] * 0.5, // 50% of profits
            'profits_only' => $summary['total_profits'],
            'capital' => $summary['total_invested'],
            'emergency' => $summary['total_current_value'],
            default => 0
        };
    }

    /**
     * Check lock-in period restrictions
     *
     * @param User $user
     * @param string $type
     * @return array
     */
    protected function checkLockInRestrictions(User $user, string $type): array
    {
        $activeInvestments = Investment::where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        $lockedInvestments = [];
        $earliestUnlockDate = null;

        foreach ($activeInvestments as $investment) {
            if ($investment->isWithinLockInPeriod()) {
                $lockInEnd = $investment->getLockInEndDate();
                $lockedInvestments[] = [
                    'investment_id' => $investment->id,
                    'amount' => $investment->amount,
                    'lock_in_end' => $lockInEnd,
                    'months_remaining' => now()->diffInMonths($lockInEnd)
                ];

                if (!$earliestUnlockDate || $lockInEnd->lt($earliestUnlockDate)) {
                    $earliestUnlockDate = $lockInEnd;
                }
            }
        }

        if (empty($lockedInvestments)) {
            return [
                'allowed' => true,
                'message' => 'No lock-in restrictions',
                'details' => []
            ];
        }

        if ($type === 'emergency') {
            return [
                'allowed' => true,
                'message' => 'Emergency withdrawal allowed with penalties',
                'details' => $lockedInvestments
            ];
        }

        return [
            'allowed' => false,
            'message' => 'Withdrawal restricted due to lock-in period',
            'details' => $lockedInvestments,
            'earliest_unlock_date' => $earliestUnlockDate
        ];
    }

    /**
     * Create withdrawal request
     *
     * @param User $user
     * @param float $amount
     * @param string $type
     * @param string $reason
     * @return array
     */
    public function createWithdrawalRequest(User $user, float $amount, string $type, string $reason = ''): array
    {
        // Validate withdrawal eligibility
        $eligibility = $this->validateWithdrawalEligibility($user, $amount, $type);
        
        if (!$eligibility['eligible']) {
            return [
                'success' => false,
                'message' => $eligibility['message'],
                'withdrawal_request_id' => null,
                'eligibility_details' => $eligibility
            ];
        }

        try {
            DB::beginTransaction();

            // Create withdrawal request
            $withdrawalRequest = WithdrawalRequest::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'withdrawal_type' => $type,
                'reason' => $reason,
                'status' => $eligibility['requires_approval'] ? 'pending_approval' : 'pending',
                'penalty_amount' => $eligibility['penalty_data']['total_penalty'],
                'net_amount' => $eligibility['net_amount'],
                'requires_approval' => $eligibility['requires_approval'],
                'requested_at' => now(),
                'calculation_details' => [
                    'penalty_breakdown' => $eligibility['penalty_data']['penalty_breakdown'],
                    'max_withdrawable' => $eligibility['max_withdrawable'],
                    'tier_penalty_reduction' => $eligibility['penalty_data']['tier_penalty_reduction'] ?? 0
                ]
            ]);

            // Record activity
            $user->recordActivity(
                'withdrawal_requested',
                "Withdrawal request created: K{$amount} ({$type})" . 
                ($eligibility['requires_approval'] ? ' - Requires approval' : '')
            );

            DB::commit();

            Log::info("Withdrawal request created: ID {$withdrawalRequest->id}, User {$user->id}, Amount K{$amount}, Type {$type}");

            return [
                'success' => true,
                'message' => 'Withdrawal request created successfully',
                'withdrawal_request_id' => $withdrawalRequest->id,
                'status' => $withdrawalRequest->status,
                'net_amount' => $withdrawalRequest->net_amount,
                'penalty_amount' => $withdrawalRequest->penalty_amount,
                'requires_approval' => $withdrawalRequest->requires_approval
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create withdrawal request for user {$user->id}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to create withdrawal request: ' . $e->getMessage(),
                'withdrawal_request_id' => null
            ];
        }
    }

    /**
     * Get withdrawal policy summary for user
     *
     * @param User $user
     * @return array
     */
    public function getWithdrawalPolicySummary(User $user): array
    {
        $summary = $this->getUserInvestmentSummary($user);
        $lockInCheck = $this->checkLockInRestrictions($user, 'full');

        $withdrawalLimits = [
            'full' => $this->calculateMaxWithdrawableAmount($user, 'full'),
            'partial' => $this->calculateMaxWithdrawableAmount($user, 'partial'),
            'profits_only' => $this->calculateMaxWithdrawableAmount($user, 'profits_only'),
            'capital' => $this->calculateMaxWithdrawableAmount($user, 'capital'),
            'emergency' => $this->calculateMaxWithdrawableAmount($user, 'emergency')
        ];

        return [
            'user_id' => $user->id,
            'investment_summary' => $summary,
            'lock_in_status' => $lockInCheck,
            'withdrawal_limits' => $withdrawalLimits,
            'tier_benefits' => [
                'penalty_reduction' => $user->currentInvestmentTier?->getWithdrawalPenaltyReduction() * 100 ?? 0,
                'tier_name' => $user->currentInvestmentTier?->name
            ],
            'policy_rules' => [
                'lock_in_period' => '12 months from investment date',
                'partial_withdrawal_limit' => '50% of profits only',
                'emergency_withdrawal' => 'Allowed with penalties and approval',
                'penalty_structure' => 'Graduated based on remaining lock-in period'
            ]
        ];
    }
}