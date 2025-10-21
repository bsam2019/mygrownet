<?php

namespace App\Services;

use App\Models\User;
use App\Models\Investment;
use App\Models\Subscription;
use App\Models\ReferralCommission;
use App\Events\UserReferred;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReferralService
{
    protected $directReferralRates = [
        'Basic' => 0.05,    // 5%
        'Starter' => 0.07,  // 7%
        'Builder' => 0.10,  // 10%
        'Leader' => 0.12,   // 12%
        'Elite' => 0.15     // 15%
    ];

    protected $level2ReferralRates = [
        'Starter' => 0.02,  // 2%
        'Builder' => 0.03,  // 3%
        'Leader' => 0.05,   // 5%
        'Elite' => 0.07     // 7%
    ];

    protected $level3ReferralRates = [
        'Builder' => 0.01,  // 1%
        'Leader' => 0.02,   // 2%
        'Elite' => 0.03     // 3%
    ];

    /**
     * Process referral commissions for a new investment (7 levels)
     * Note: This processes commissions through 7 levels of the referral chain
     * using the MyGrowNet 7-level commission structure
     */
    public function processReferralCommission(Investment $investment)
    {
        $user = $investment->user;
        $referrer = $user->referrer;

        if (!$referrer) {
            return;
        }

        DB::transaction(function () use ($investment, $user, $referrer) {
            $currentReferrer = $referrer;
            $directReferrer = $referrer; // Store for points event
            
            // Process commissions for up to 7 levels
            for ($level = 1; $level <= ReferralCommission::MAX_COMMISSION_LEVELS; $level++) {
                if (!$currentReferrer) {
                    break;
                }
                
                // Check if referrer is qualified to receive commission
                if ($this->isQualifiedForCommission($currentReferrer, $level)) {
                    $this->createCommission($investment, $currentReferrer, $user, $level);
                }
                
                // Move up the referral chain
                $currentReferrer = $currentReferrer->referrer;
            }

            // Fire event for points system (only once per referral, only for direct referrer)
            if ($investment->user->investments()->count() === 1) {
                event(new UserReferred($directReferrer, $user));
            }
        });
    }
    
    /**
     * Check if referrer is qualified to receive commission at this level
     */
    protected function isQualifiedForCommission(User $referrer, int $level): bool
    {
        // Basic qualification: user must be active
        if ($referrer->status !== 'active') {
            return false;
        }
        
        // Check if user has points record and meets monthly qualification
        if ($referrer->points) {
            // User must meet their monthly MAP requirement to receive commissions
            if (!$referrer->meetsMonthlyQualification()) {
                \Log::info("Referrer {$referrer->id} does not meet monthly qualification for level {$level}");
                return false;
            }
        }
        
        // Additional level-specific requirements can be added here
        // For example: higher levels might require minimum team size
        
        return true;
    }

    protected function createCommission(Investment $investment, User $referrer, User $referee, int $level)
    {
        // Use the commission rates from ReferralCommission model (7-level structure)
        $percentage = ReferralCommission::getCommissionRate($level);
        $amount = $investment->amount * ($percentage / 100);

        ReferralCommission::create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id,
            'investment_id' => $investment->id,
            'level' => $level,
            'amount' => $amount,
            'percentage' => $percentage,
            'status' => 'pending',
            'commission_type' => 'REFERRAL'
        ]);
        
        \Log::info("Commission created for level {$level}", [
            'referrer_id' => $referrer->id,
            'referee_id' => $referee->id,
            'level' => $level,
            'percentage' => $percentage,
            'amount' => $amount
        ]);
    }

    /**
     * Process referral commissions for a new subscription (7 levels)
     * This is the preferred method for MyGrowNet subscription-based model
     */
    public function processSubscriptionCommission(Subscription $subscription)
    {
        $user = $subscription->user;
        $referrer = $user->referrer;

        if (!$referrer) {
            return;
        }

        DB::transaction(function () use ($subscription, $user, $referrer) {
            $currentReferrer = $referrer;
            $directReferrer = $referrer; // Store for points event
            
            // Process commissions for up to 7 levels
            for ($level = 1; $level <= ReferralCommission::MAX_COMMISSION_LEVELS; $level++) {
                if (!$currentReferrer) {
                    break;
                }
                
                // Check if referrer is qualified to receive commission
                if ($this->isQualifiedForCommission($currentReferrer, $level)) {
                    $this->createSubscriptionCommission($subscription, $currentReferrer, $user, $level);
                }
                
                // Move up the referral chain
                $currentReferrer = $currentReferrer->referrer;
            }

            // Fire event for points system (only once per referral, only for direct referrer)
            if ($subscription->user->subscriptions()->count() === 1) {
                event(new UserReferred($directReferrer, $user));
            }
        });
    }

    /**
     * Create commission record for subscription
     */
    protected function createSubscriptionCommission(Subscription $subscription, User $referrer, User $referee, int $level)
    {
        // Use the commission rates from ReferralCommission model (7-level structure)
        $percentage = ReferralCommission::getCommissionRate($level);
        $amount = $subscription->amount * ($percentage / 100);

        ReferralCommission::create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id,
            'package_subscription_id' => $subscription->id,
            'level' => $level,
            'amount' => $amount,
            'percentage' => $percentage,
            'status' => 'pending',
            'commission_type' => 'REFERRAL',
            'package_type' => $subscription->package->name,
            'package_amount' => $subscription->amount
        ]);
        
        \Log::info("Subscription commission created for level {$level}", [
            'referrer_id' => $referrer->id,
            'referee_id' => $referee->id,
            'subscription_id' => $subscription->id,
            'level' => $level,
            'percentage' => $percentage,
            'amount' => $amount
        ]);
    }

    /**
     * Get referral statistics for a user
     */
    public function getUserReferralStats(User $user)
    {
        return [
            'total_referrals' => $user->referrals()->count(),
            'active_referrals' => $user->referrals()
                ->whereHas('investments', function ($query) {
                    $query->where('status', 'active');
                })->count(),
            'total_commission' => $user->referralCommissions()
                ->where('status', 'paid')
                ->sum('amount'),
            'pending_commission' => $user->referralCommissions()
                ->where('status', 'pending')
                ->sum('amount')
        ];
    }

    /**
     * Process pending commissions
     * @return array
     */
    public function processPendingCommissions()
    {
        $pendingCommissions = ReferralCommission::where('status', 'pending')->get();
        $processedCount = 0;
        $totalAmount = 0;
        
        foreach ($pendingCommissions as $commission) {
            try {
                DB::beginTransaction();
                
                // Update commission status
                $commission->update([
                    'status' => 'paid',
                    'processed_at' => now()
                ]);
                
                // Update user's total earnings
                $commission->referrer->increment('total_referral_earnings', $commission->amount);
                
                DB::commit();
                
                $processedCount++;
                $totalAmount += $commission->amount;
            } catch (\Exception $e) {
                DB::rollBack();
                // Log error but continue processing other commissions
                \Log::error('Failed to process commission: ' . $e->getMessage());
            }
        }

        return [
            'processed_count' => $processedCount,
            'total_amount' => $totalAmount
        ];
    }

    public function getAdminReferralStats()
    {
        return [
            'total_referrals' => User::whereNotNull('referrer_id')->count(),
            'active_referrals' => User::whereNotNull('referrer_id')
                ->whereHas('investments', function ($query) {
                    $query->where('status', 'active');
                })->count(),
            'total_commission_paid' => ReferralCommission::where('status', 'paid')->sum('amount'),
            'pending_commission' => ReferralCommission::where('status', 'pending')->sum('amount'),
            'total_investments_from_referrals' => Investment::whereHas('user', function ($query) {
                $query->whereNotNull('referrer_id');
            })->sum('amount')
        ];
    }

    public function getTopReferrers($limit = 10)
    {
        return User::select('users.*')
            ->selectRaw('COUNT(DISTINCT referred_users.id) as total_referrals')
            ->selectRaw('SUM(referral_commissions.amount) as total_commission')
            ->leftJoin('users as referred_users', 'users.id', '=', 'referred_users.referrer_id')
            ->leftJoin('referral_commissions', 'users.id', '=', 'referral_commissions.referrer_id')
            ->groupBy('users.id')
            ->orderBy('total_referrals', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getMonthlyReferralStats()
    {
        return ReferralCommission::selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                COUNT(*) as total_transactions,
                SUM(amount) as total_amount,
                COUNT(DISTINCT referrer_id) as unique_referrers
            ')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();
    }
} 