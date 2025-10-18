<?php

namespace App\Services;

use App\Models\InvestmentTier;
use App\Models\User;

class EarningsProjectionService
{
    /**
     * Calculate earnings projection for a specific tier and scenario
     */
    public function calculateProjection(
        string $tierName,
        int $activeReferrals = 5,
        int $networkDepth = 3,
        int $months = 12
    ): array {
        // Validate tier name
        $validTiers = ['Bronze', 'Silver', 'Gold', 'Diamond', 'Elite'];
        if (!in_array($tierName, $validTiers)) {
            throw new \InvalidArgumentException("Invalid tier: {$tierName}");
        }

        $monthlyProjections = [];
        $totalEarnings = 0;

        for ($month = 1; $month <= $months; $month++) {
            $monthlyEarning = $this->calculateMonthlyEarnings($tierName, $activeReferrals, $networkDepth, $month);
            $monthlyProjections[] = $monthlyEarning;
            $totalEarnings += $monthlyEarning['total'];
        }

        return [
            'tier' => $tierName,
            'scenario' => [
                'active_referrals' => $activeReferrals,
                'network_depth' => $networkDepth,
                'months' => $months
            ],
            'monthly_projections' => $monthlyProjections,
            'total_earnings' => $totalEarnings,
            'average_monthly' => $totalEarnings / $months,
            'income_breakdown' => $this->getIncomeBreakdown($tierName, $activeReferrals, $networkDepth)
        ];
    }

    /**
     * Calculate monthly earnings for a specific month
     */
    private function calculateMonthlyEarnings(string $tierName, int $activeReferrals, int $networkDepth, int $month): array
    {
        // Base monthly subscription share
        $subscriptionShare = $this->getMonthlyShare($tierName);
        
        // Multilevel commissions (25% of total income)
        $multilevelCommissions = $this->calculateMultilevelCommissions($tierName, $activeReferrals, $networkDepth);
        
        // Team volume bonuses (15% of total income)
        $teamVolumeBonus = $this->calculateTeamVolumeBonus($tierName, $activeReferrals, $month);
        
        // Profit sharing (20% of total income) - quarterly/annual
        $profitSharing = $this->calculateProfitSharing($tierName, $month);
        
        // Achievement bonuses (10% of total income) - one-time bonuses
        $achievementBonus = $this->calculateAchievementBonus($tierName, $month);

        $total = $subscriptionShare + $multilevelCommissions + $teamVolumeBonus + $profitSharing + $achievementBonus;

        return [
            'month' => $month,
            'subscription_share' => $subscriptionShare,
            'multilevel_commissions' => $multilevelCommissions,
            'team_volume_bonus' => $teamVolumeBonus,
            'profit_sharing' => $profitSharing,
            'achievement_bonus' => $achievementBonus,
            'total' => $total
        ];
    }

    /**
     * Get monthly subscription share based on tier
     */
    private function getMonthlyShare(string $tierName): float
    {
        $shares = [
            'Bronze' => 50,
            'Silver' => 150,
            'Gold' => 300,
            'Diamond' => 500,
            'Elite' => 700
        ];

        return $shares[$tierName] ?? 0;
    }

    /**
     * Calculate multilevel commissions
     */
    private function calculateMultilevelCommissions(string $tierName, int $activeReferrals, int $networkDepth): float
    {
        $commissionRates = [1 => 0.12, 2 => 0.06, 3 => 0.04, 4 => 0.02, 5 => 0.01];
        $averagePackageValue = $this->getAveragePackageValue($tierName);
        
        $totalCommissions = 0;
        
        for ($level = 1; $level <= min($networkDepth, 5); $level++) {
            $referralsAtLevel = $this->calculateReferralsAtLevel($activeReferrals, $level);
            $commissionRate = $commissionRates[$level];
            $levelCommissions = $referralsAtLevel * $averagePackageValue * $commissionRate;
            $totalCommissions += $levelCommissions;
        }

        return $totalCommissions;
    }

    /**
     * Calculate team volume bonus
     */
    private function calculateTeamVolumeBonus(string $tierName, int $activeReferrals, int $month): float
    {
        $teamVolume = $this->estimateTeamVolume($tierName, $activeReferrals, $month);
        $bonusRate = $this->getTeamVolumeBonusRate($tierName);
        
        return $teamVolume * $bonusRate;
    }

    /**
     * Calculate profit sharing (quarterly/annual)
     */
    private function calculateProfitSharing(string $tierName, int $month): float
    {
        
        // Quarterly profit sharing for Gold+
        if (in_array($tierName, ['Gold', 'Diamond', 'Elite']) && $month % 3 === 0) {
            return $this->getQuarterlyProfitShare($tierName);
        }
        
        // Annual profit sharing for Diamond+
        if (in_array($tierName, ['Diamond', 'Elite']) && $month === 12) {
            return $this->getAnnualProfitShare($tierName);
        }
        
        return 0;
    }

    /**
     * Calculate achievement bonuses (one-time)
     */
    private function calculateAchievementBonus(string $tierName, int $month): float
    {
        
        // Achievement bonuses are typically awarded once when tier is reached
        if ($month === 1) {
            $bonuses = [
                'Silver' => 500,
                'Gold' => 2000,
                'Diamond' => 5000,
                'Elite' => 10000
            ];
            
            return $bonuses[$tierName] ?? 0;
        }
        
        return 0;
    }

    /**
     * Get income breakdown percentages
     */
    private function getIncomeBreakdown(string $tierName, int $activeReferrals, int $networkDepth): array
    {
        return [
            'multilevel_commissions' => 25, // 25% of total income
            'team_volume_bonuses' => 15,    // 15% of total income
            'subscription_shares' => 30,     // 30% of total income
            'profit_sharing' => 20,          // 20% of total income
            'achievement_bonuses' => 10      // 10% of total income
        ];
    }

    /**
     * Helper methods for calculations
     */
    private function getAveragePackageValue(string $tierName): float
    {
        $values = [
            'Bronze' => 150,
            'Silver' => 300,
            'Gold' => 500,
            'Diamond' => 1000,
            'Elite' => 1500
        ];

        return $values[$tierName] ?? 150;
    }

    private function calculateReferralsAtLevel(int $activeReferrals, int $level): int
    {
        // More conservative growth model to prevent memory issues
        if ($level === 1) {
            return $activeReferrals;
        }
        
        // Each level has 2x the previous level, capped at reasonable limits
        $referrals = $activeReferrals * pow(2, $level - 1);
        
        // Cap at 1000 referrals per level to prevent memory issues
        return min((int) $referrals, 1000);
    }

    private function estimateTeamVolume(string $tierName, int $activeReferrals, int $month): float
    {
        $baseVolume = $this->getAveragePackageValue($tierName) * $activeReferrals;
        $growthFactor = 1 + (min($month, 24) * 0.1); // 10% growth per month, capped at 24 months
        
        return $baseVolume * $growthFactor;
    }

    private function getTeamVolumeBonusRate(string $tierName): float
    {
        $rates = [
            'Bronze' => 0.00,
            'Silver' => 0.02,
            'Gold' => 0.05,
            'Diamond' => 0.07,
            'Elite' => 0.10
        ];

        return $rates[$tierName] ?? 0;
    }

    private function getQuarterlyProfitShare(string $tierName): float
    {
        // Estimated quarterly profit sharing amounts
        $shares = [
            'Gold' => 1000,
            'Diamond' => 2500,
            'Elite' => 5000
        ];

        return $shares[$tierName] ?? 0;
    }

    private function getAnnualProfitShare(string $tierName): float
    {
        // Estimated annual profit sharing amounts
        $shares = [
            'Diamond' => 5000,
            'Elite' => 12000
        ];

        return $shares[$tierName] ?? 0;
    }

    /**
     * Get realistic earning ranges for all tiers
     */
    public function getEarningRanges(): array
    {
        return [
            'Bronze' => ['min' => 150, 'max' => 500],
            'Silver' => ['min' => 800, 'max' => 3000],
            'Gold' => ['min' => 2500, 'max' => 8000],
            'Diamond' => ['min' => 7500, 'max' => 25000],
            'Elite' => ['min' => 20000, 'max' => 75000]
        ];
    }

    /**
     * Generate multiple scenarios for comparison
     */
    public function generateScenarios(string $tierName): array
    {
        $scenarios = [
            'conservative' => ['referrals' => 3, 'depth' => 2],
            'realistic' => ['referrals' => 5, 'depth' => 3],
            'optimistic' => ['referrals' => 10, 'depth' => 4]
        ];

        $results = [];
        
        foreach ($scenarios as $scenarioName => $params) {
            $results[$scenarioName] = $this->calculateProjection(
                $tierName,
                $params['referrals'],
                $params['depth'],
                12
            );
        }

        return $results;
    }
}