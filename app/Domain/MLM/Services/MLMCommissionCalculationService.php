<?php

declare(strict_types=1);

namespace App\Domain\MLM\Services;

use App\Models\User;
use App\Models\ReferralCommission;
use App\Models\UserNetwork;
use App\Models\TeamVolume;
use Illuminate\Support\Collection;
use DateTimeImmutable;

class MLMCommissionCalculationService
{
    /**
     * MyGrowNet five-level commission rates
     */
    public const COMMISSION_RATES = [
        1 => 12.0, // Level 1: 12%
        2 => 6.0,  // Level 2: 6%
        3 => 4.0,  // Level 3: 4%
        4 => 2.0,  // Level 4: 2%
        5 => 1.0,  // Level 5: 1%
    ];

    /**
     * Calculate multilevel commissions for a package purchase
     */
    public function calculateMultilevelCommissions(
        User $purchaser,
        float $packageAmount,
        string $packageType = 'subscription'
    ): Collection {
        $commissions = collect();
        
        // Get upline referrers up to 5 levels
        $uplineReferrers = UserNetwork::getUplineReferrers($purchaser->id, 5);
        
        foreach ($uplineReferrers as $referrerData) {
            $referrer = User::find($referrerData['user_id']);
            $level = $referrerData['level'];
            
            if (!$referrer || !$referrer->hasActiveSubscription()) {
                continue;
            }
            
            $commissionAmount = $this->calculateCommissionAmount($packageAmount, $level);
            
            if ($commissionAmount > 0) {
                $commissions->push([
                    'referrer_id' => $referrer->id,
                    'referred_id' => $purchaser->id,
                    'level' => $level,
                    'amount' => $commissionAmount,
                    'percentage' => self::COMMISSION_RATES[$level],
                    'commission_type' => ReferralCommission::COMMISSION_TYPES['REFERRAL'],
                    'package_type' => $packageType,
                    'package_amount' => $packageAmount,
                    'status' => 'pending'
                ]);
            }
        }
        
        return $commissions;
    }

    /**
     * Calculate team volume bonus for a user
     */
    public function calculateTeamVolumeBonus(User $user, TeamVolume $teamVolume): float
    {
        $volume = $teamVolume->team_volume;
        
        // Performance bonus rates based on team volume
        if ($volume >= 100000) {
            return $volume * 0.10; // 10% for K100,000+
        } elseif ($volume >= 50000) {
            return $volume * 0.07; // 7% for K50,000+
        } elseif ($volume >= 25000) {
            return $volume * 0.05; // 5% for K25,000+
        } elseif ($volume >= 10000) {
            return $volume * 0.02; // 2% for K10,000+
        }
        
        return 0;
    }

    /**
     * Create team volume bonus commission
     */
    public function createTeamVolumeBonus(User $user, TeamVolume $teamVolume): ?ReferralCommission
    {
        $bonusAmount = $this->calculateTeamVolumeBonus($user, $teamVolume);
        
        if ($bonusAmount <= 0) {
            return null;
        }
        
        return ReferralCommission::create([
            'referrer_id' => $user->id,
            'referred_id' => $user->id, // Self-referencing for team volume bonus
            'level' => 0, // Special level for team volume bonuses
            'amount' => $bonusAmount,
            'percentage' => $this->getTeamVolumePercentage($teamVolume->team_volume),
            'commission_type' => ReferralCommission::COMMISSION_TYPES['TEAM_VOLUME'],
            'package_type' => 'team_volume_bonus',
            'package_amount' => $teamVolume->team_volume,
            'team_volume' => $teamVolume->team_volume,
            'personal_volume' => $teamVolume->personal_volume,
            'status' => 'pending'
        ]);
    }

    /**
     * Create performance bonus commission (MyGrowNet enhancement)
     */
    public function createPerformanceBonus(User $user, TeamVolume $teamVolume): array
    {
        $bonuses = [];
        
        // Team volume performance bonus
        $teamVolumeBonus = $this->calculateTeamVolumePerformanceBonus($teamVolume->team_volume);
        if ($teamVolumeBonus > 0) {
            $bonuses[] = ReferralCommission::create([
                'referrer_id' => $user->id,
                'referred_id' => $user->id,
                'level' => 0,
                'amount' => $teamVolumeBonus,
                'percentage' => $this->getPerformanceBonusPercentage($teamVolume->team_volume),
                'commission_type' => 'PERFORMANCE',
                'package_type' => 'team_volume_performance_bonus',
                'package_amount' => $teamVolume->team_volume,
                'team_volume' => $teamVolume->team_volume,
                'personal_volume' => $teamVolume->personal_volume,
                'status' => 'pending'
            ]);
        }

        // Leadership bonus (if eligible)
        $tier = $user->membershipTier;
        if ($tier && $tier->leadership_bonus_eligible) {
            $leadershipBonus = $this->calculateLeadershipBonus($user, $teamVolume, $tier);
            if ($leadershipBonus > 0) {
                $bonuses[] = ReferralCommission::create([
                    'referrer_id' => $user->id,
                    'referred_id' => $user->id,
                    'level' => 0,
                    'amount' => $leadershipBonus,
                    'percentage' => $this->getLeadershipBonusPercentage($user, $tier),
                    'commission_type' => 'PERFORMANCE',
                    'package_type' => 'leadership_performance_bonus',
                    'package_amount' => $teamVolume->team_volume,
                    'team_volume' => $teamVolume->team_volume,
                    'personal_volume' => $teamVolume->personal_volume,
                    'status' => 'pending'
                ]);
            }
        }

        return $bonuses;
    }

    /**
     * Calculate team volume performance bonus (MyGrowNet thresholds)
     */
    public function calculateTeamVolumePerformanceBonus(float $teamVolume): float
    {
        // MyGrowNet performance bonus thresholds
        if ($teamVolume >= 100000) {
            return $teamVolume * 0.10; // 10% for K100,000+
        } elseif ($teamVolume >= 50000) {
            return $teamVolume * 0.07; // 7% for K50,000+
        } elseif ($teamVolume >= 25000) {
            return $teamVolume * 0.05; // 5% for K25,000+
        } elseif ($teamVolume >= 10000) {
            return $teamVolume * 0.02; // 2% for K10,000+
        }
        
        return 0;
    }

    /**
     * Calculate leadership bonus
     */
    public function calculateLeadershipBonus(User $user, TeamVolume $teamVolume, $tier): float
    {
        if (!$tier->leadership_bonus_eligible) {
            return 0;
        }

        $leadershipLevel = $this->assessLeadershipLevel($user, $teamVolume, $tier);
        $rates = [
            'elite_leader' => 3.0,
            'diamond_leader' => 2.5,
            'gold_leader' => 2.0,
            'developing_leader' => 1.0
        ];

        $rate = $rates[$leadershipLevel] ?? 0;
        return $teamVolume->team_volume * ($rate / 100);
    }

    /**
     * Assess leadership level for bonus calculation
     */
    private function assessLeadershipLevel(User $user, TeamVolume $teamVolume, $tier): string
    {
        $activeReferrals = $teamVolume->active_referrals_count;
        $teamDepth = $teamVolume->team_depth;
        $volume = $teamVolume->team_volume;

        // Elite tier with exceptional performance
        if ($tier->name === 'Elite' && $activeReferrals >= 75 && $teamDepth >= 5 && $volume >= 200000) {
            return 'elite_leader';
        }

        // Diamond tier with strong performance
        if ($tier->name === 'Diamond' && $activeReferrals >= 40 && $teamDepth >= 4 && $volume >= 75000) {
            return 'diamond_leader';
        }

        // Gold tier with good performance
        if ($tier->name === 'Gold' && $activeReferrals >= 20 && $teamDepth >= 3 && $volume >= 25000) {
            return 'gold_leader';
        }

        // Developing leaders
        if (in_array($tier->name, ['Gold', 'Diamond', 'Elite']) && $activeReferrals >= 15 && $teamDepth >= 3) {
            return 'developing_leader';
        }

        return 'no_leadership_bonus';
    }

    /**
     * Get performance bonus percentage
     */
    private function getPerformanceBonusPercentage(float $volume): float
    {
        if ($volume >= 100000) return 10.0;
        if ($volume >= 50000) return 7.0;
        if ($volume >= 25000) return 5.0;
        if ($volume >= 10000) return 2.0;
        return 0.0;
    }

    /**
     * Get leadership bonus percentage
     */
    private function getLeadershipBonusPercentage(User $user, $tier): float
    {
        $teamVolume = $user->getCurrentTeamVolume();
        if (!$teamVolume) return 0;

        $leadershipLevel = $this->assessLeadershipLevel($user, $teamVolume, $tier);
        $rates = [
            'elite_leader' => 3.0,
            'diamond_leader' => 2.5,
            'gold_leader' => 2.0,
            'developing_leader' => 1.0
        ];

        return $rates[$leadershipLevel] ?? 0;
    }

    /**
     * Get team volume percentage for bonus calculation
     */
    private function getTeamVolumePercentage(float $volume): float
    {
        if ($volume >= 100000) {
            return 10.0;
        } elseif ($volume >= 50000) {
            return 7.0;
        } elseif ($volume >= 25000) {
            return 5.0;
        } elseif ($volume >= 10000) {
            return 2.0;
        }
        
        return 0.0;
    }

    /**
     * Process commission payments for pending commissions
     */
    public function processCommissionPayments(): array
    {
        $pendingCommissions = ReferralCommission::pending()
            ->with(['referrer', 'referee'])
            ->get();
        
        $processed = [];
        $failed = [];
        
        foreach ($pendingCommissions as $commission) {
            try {
                if ($this->isEligibleForPayment($commission)) {
                    $this->payCommission($commission);
                    $processed[] = $commission->id;
                } else {
                    $failed[] = [
                        'commission_id' => $commission->id,
                        'reason' => 'Not eligible for payment'
                    ];
                }
            } catch (\Exception $e) {
                $failed[] = [
                    'commission_id' => $commission->id,
                    'reason' => $e->getMessage()
                ];
            }
        }
        
        return [
            'processed' => $processed,
            'failed' => $failed,
            'total_processed' => count($processed),
            'total_failed' => count($failed)
        ];
    }

    /**
     * Update team volumes for all users
     */
    public function updateTeamVolumes(): void
    {
        $users = User::whereHas('directReferrals')->get();
        
        foreach ($users as $user) {
            $this->updateUserTeamVolume($user);
        }
    }

    /**
     * Update team volume for a specific user
     */
    public function updateUserTeamVolume(User $user): TeamVolume
    {
        $currentMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        
        // Get or create team volume record for current month
        $teamVolume = TeamVolume::firstOrCreate([
            'user_id' => $user->id,
            'period_start' => $currentMonth->toDateString(),
            'period_end' => $endOfMonth->toDateString(),
        ]);
        
        // Calculate personal volume (user's own purchases this month)
        $personalVolume = $this->calculatePersonalVolume($user, $currentMonth, $endOfMonth);
        
        // Calculate team volume (all downline purchases this month)
        $networkVolume = $this->calculateNetworkVolume($user, $currentMonth, $endOfMonth);
        
        // Count active referrals
        $activeReferrals = $this->countActiveReferrals($user);
        
        // Calculate team depth
        $teamDepth = $this->calculateTeamDepth($user);
        
        $teamVolume->update([
            'personal_volume' => $personalVolume,
            'team_volume' => $networkVolume + $personalVolume,
            'team_depth' => $teamDepth,
            'active_referrals_count' => $activeReferrals,
        ]);
        
        // Create team volume bonus if eligible
        $this->createTeamVolumeBonus($user, $teamVolume);
        
        return $teamVolume;
    }

    /**
     * Calculate commission amount for a specific level
     */
    private function calculateCommissionAmount(float $packageAmount, int $level): float
    {
        $rate = self::COMMISSION_RATES[$level] ?? 0.0;
        return $packageAmount * ($rate / 100);
    }

    /**
     * Check if commission is eligible for payment
     */
    private function isEligibleForPayment(ReferralCommission $commission): bool
    {
        return $commission->status === 'pending' && 
               $commission->referrer && 
               $commission->referrer->hasActiveSubscription() &&
               $commission->amount > 0;
    }

    /**
     * Pay commission to referrer
     */
    private function payCommission(ReferralCommission $commission): void
    {
        // Update referrer's balance
        $commission->referrer->increment('balance', $commission->amount);
        $commission->referrer->increment('total_referral_earnings', $commission->amount);
        
        // Mark commission as paid
        $commission->update([
            'status' => 'paid',
            'paid_at' => now()
        ]);
        
        // Record activity
        $commission->referrer->recordActivity(
            'commission_received',
            "Received K{$commission->amount} commission from {$commission->referee->name}"
        );
    }

    /**
     * Calculate personal volume for a user in a period
     */
    private function calculatePersonalVolume(User $user, $startDate, $endDate): float
    {
        // Calculate personal volume from subscriptions and package purchases
        $subscriptionVolume = $this->calculateSubscriptionVolume($user, $startDate, $endDate);
        $packageVolume = $this->calculatePackageVolume($user, $startDate, $endDate);
        
        return $subscriptionVolume + $packageVolume;
    }

    /**
     * Calculate subscription volume for a user in a period
     */
    private function calculateSubscriptionVolume(User $user, $startDate, $endDate): float
    {
        // Get user's tier and calculate monthly subscription amount
        $membershipTier = $user->membershipTier;
        if (!$membershipTier) {
            return 0;
        }
        
        // For MyGrowNet, subscription amounts are:
        // Bronze: K150, Silver: K300, Gold: K500, Diamond: K1000, Elite: K1500
        $subscriptionAmounts = [
            'Bronze' => 150,
            'Silver' => 300,
            'Gold' => 500,
            'Diamond' => 1000,
            'Elite' => 1500,
        ];
        
        return $subscriptionAmounts[$membershipTier->name] ?? 0;
    }

    /**
     * Calculate package volume for a user in a period
     */
    private function calculatePackageVolume(User $user, $startDate, $endDate): float
    {
        // Calculate volume from package purchases in the period
        return ReferralCommission::where('referred_id', $user->id)
            ->where('commission_type', 'REFERRAL')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('package_amount') ?? 0;
    }

    /**
     * Calculate network volume for a user in a period
     */
    private function calculateNetworkVolume(User $user, $startDate, $endDate): float
    {
        $networkMembers = UserNetwork::getNetworkMembers($user->id, 5);
        $totalVolume = 0;
        
        foreach ($networkMembers as $level => $members) {
            foreach ($members as $memberData) {
                $member = $memberData['user'] ?? User::find($memberData['user_id']);
                if ($member) {
                    $personalVolume = $this->calculatePersonalVolume($member, $startDate, $endDate);
                    $totalVolume += $personalVolume;
                }
            }
        }
        
        return $totalVolume;
    }

    /**
     * Count active referrals for a user
     */
    private function countActiveReferrals(User $user): int
    {
        return $user->directReferrals()
            ->where('status', 'active')
            ->count();
    }

    /**
     * Calculate team depth for a user
     */
    private function calculateTeamDepth(User $user): int
    {
        $maxDepth = 0;
        $networkMembers = UserNetwork::getNetworkMembers($user->id, 5);
        
        foreach ($networkMembers as $level => $members) {
            if (!empty($members)) {
                $maxDepth = max($maxDepth, $level);
            }
        }
        
        return $maxDepth;
    }
}