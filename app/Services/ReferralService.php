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

    /**
     * Get comprehensive referral statistics for the Team page
     */
    public function getReferralStatistics(User $user)
    {
        $referrals = $user->referrals();
        
        return [
            'total_referrals_count' => $referrals->count(),
            'active_referrals_count' => $referrals->whereHas('subscriptions', function ($query) {
                $query->where('status', 'active');
            })->count(),
            'total_commission_earned' => $user->referralCommissions()->where('status', 'paid')->sum('amount'),
            'monthly_commission' => $user->referralCommissions()
                ->where('status', 'paid')
                ->whereMonth('processed_at', now()->month)
                ->sum('amount'),
            'pending_commission' => $user->referralCommissions()->where('status', 'pending')->sum('amount'),
            'pending_transactions_count' => $user->referralCommissions()->where('status', 'pending')->count(),
            'matrix_earnings' => 0, // Placeholder for matrix earnings
            'matrix_positions_filled' => 0, // Placeholder
        ];
    }

    /**
     * Get earnings breakdown by level
     */
    public function getEarningsBreakdown(User $user)
    {
        $byLevel = [];
        for ($level = 1; $level <= 7; $level++) {
            $levelCommissions = $user->referralCommissions()
                ->where('level', $level)
                ->where('status', 'paid');
            
            $byLevel[] = [
                'level' => $level,
                'amount' => $levelCommissions->sum('amount'),
                'count' => $levelCommissions->count(),
            ];
        }

        return [
            'by_level' => $byLevel,
            'direct_referrals' => $user->referralCommissions()->where('level', 1)->where('status', 'paid')->sum('amount'),
            'spillover' => 0, // Placeholder
            'matrix_bonuses' => 0, // Placeholder
            'reinvestment_bonuses' => 0, // Placeholder
            'total' => $user->referralCommissions()->where('status', 'paid')->sum('amount'),
        ];
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(User $user)
    {
        $totalReferrals = $user->referrals()->count();
        $activeReferrals = $user->referrals()->whereHas('subscriptions', function ($query) {
            $query->where('status', 'active');
        })->count();

        return [
            'conversion_rate' => $totalReferrals > 0 ? ($activeReferrals / $totalReferrals) * 100 : 0,
            'average_investment' => $user->referrals()->withSum('subscriptions', 'amount')->get()->avg('subscriptions_sum_amount') ?? 0,
            'retention_rate' => $activeReferrals > 0 ? ($activeReferrals / $totalReferrals) * 100 : 0,
            'growth_rate' => 0, // Placeholder - would need historical data
        ];
    }

    /**
     * Get recent activity
     */
    public function getRecentActivity(User $user)
    {
        $activities = [];
        
        // Recent referrals
        $recentReferrals = $user->referrals()->latest()->take(5)->get();
        foreach ($recentReferrals as $referral) {
            $activities[] = [
                'id' => $referral->id,
                'type' => 'referral',
                'description' => "{$referral->name} joined your team",
                'created_at' => $referral->created_at->toISOString(),
            ];
        }
        
        // Recent commissions
        $recentCommissions = $user->referralCommissions()->latest()->take(5)->get();
        foreach ($recentCommissions as $commission) {
            $activities[] = [
                'id' => $commission->id,
                'type' => 'commission',
                'description' => "Earned commission from Level {$commission->level}",
                'amount' => $commission->amount,
                'created_at' => $commission->created_at->toISOString(),
            ];
        }
        
        // Sort by date and limit
        usort($activities, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($activities, 0, 10);
    }

    /**
     * Get tier distribution of referrals
     */
    public function getTierDistribution(User $user)
    {
        // For MyGrowNet, we'll use professional levels instead of investment tiers
        $distribution = $user->referrals()
            ->select('current_professional_level', DB::raw('count(*) as count'))
            ->groupBy('current_professional_level')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => ucfirst($item->current_professional_level),
                    'count' => $item->count,
                    'total_investment' => 0, // Placeholder
                ];
            });
        
        return $distribution->toArray();
    }

    /**
     * Get matrix data (placeholder for now)
     */
    public function getMatrixData(User $user)
    {
        return [
            'root' => [
                'id' => $user->id,
                'name' => $user->name,
                'level' => $user->current_professional_level,
            ],
            'levels' => [
                'level_1' => [],
                'level_2' => [],
                'level_3' => [],
            ],
        ];
    }

    /**
     * Get spillover info (placeholder)
     */
    public function getSpilloverInfo(User $user)
    {
        return [
            'has_opportunities' => false,
            'available_slots' => 0,
        ];
    }

    /**
     * Get matrix stats (placeholder)
     */
    public function getMatrixStats(User $user)
    {
        return [
            'level_1_count' => $user->referrals()->count(),
            'level_2_count' => 0,
            'level_3_count' => 0,
            'total_earnings' => $user->referralCommissions()->where('status', 'paid')->sum('amount'),
            'filled_positions' => $user->referrals()->count(),
            'total_positions' => 3,
        ];
    }

    /**
     * Get spillover data (placeholder)
     */
    public function getSpilloverData(User $user)
    {
        return [];
    }

    /**
     * Get level 1 referrals
     */
    public function getLevel1Referrals(User $user)
    {
        return $user->referrals()->get()->map(function ($referral) {
            return [
                'id' => $referral->id,
                'name' => $referral->name,
                'email' => $referral->email,
                'level' => $referral->current_professional_level,
                'joined_at' => $referral->created_at->toISOString(),
            ];
        })->toArray();
    }

    /**
     * Get spillover placements (placeholder)
     */
    public function getSpilloverPlacements(User $user)
    {
        return [];
    }

    /**
     * Get spillover history (placeholder)
     */
    public function getSpilloverHistory(User $user)
    {
        return [];
    }

    /**
     * Get spillover opportunities (placeholder)
     */
    public function getSpilloverOpportunities(User $user)
    {
        return [];
    }

    /**
     * Get spillover stats (placeholder)
     */
    public function getSpilloverStats(User $user)
    {
        return [];
    }

    /**
     * Get code stats
     */
    public function getCodeStats(User $user)
    {
        return [
            'uses_count' => $user->referrals()->count(),
            'successful_registrations' => $user->referrals()->count(),
            'active_investors' => $user->referrals()->whereHas('subscriptions', function ($query) {
                $query->where('status', 'active');
            })->count(),
            'total_earnings' => $user->referralCommissions()->where('status', 'paid')->sum('amount'),
        ];
    }

    /**
     * Get link stats (placeholder)
     */
    public function getLinkStats(User $user)
    {
        return [
            'clicks' => 0,
            'conversion_rate' => 0,
        ];
    }

    /**
     * Get message templates
     */
    public function getMessageTemplates()
    {
        return [
            [
                'id' => 1,
                'title' => 'Join MyGrowNet',
                'description' => 'Invite friends to join the platform',
                'message' => 'Join me on MyGrowNet and start your journey to financial growth! Use my referral code {referral_code} or click here: {referral_link}',
            ],
            [
                'id' => 2,
                'title' => 'Opportunity Sharing',
                'description' => 'Share the opportunity',
                'message' => 'I\'ve been growing with MyGrowNet and I think you\'d love it too! Join using my code {referral_code} and let\'s grow together: {referral_link}',
            ],
            [
                'id' => 3,
                'title' => 'Professional Invitation',
                'description' => 'Professional invitation message',
                'message' => 'I\'d like to invite you to MyGrowNet, a platform for community empowerment and growth. Use referral code {referral_code} to get started: {referral_link}',
            ],
        ];
    }
} 