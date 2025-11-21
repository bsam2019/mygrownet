<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class PredictiveAnalyticsService
{
    public function predictEarnings(User $user, int $months = 12): array
    {
        $historicalEarnings = $this->getHistoricalEarnings($user, 90);
        $averageMonthly = $historicalEarnings['average_monthly'] ?? 0;
        $growthRate = $historicalEarnings['growth_rate'] ?? 0;
        
        $predictions = [];
        $currentAmount = $averageMonthly;
        
        for ($i = 1; $i <= $months; $i++) {
            $currentAmount = $currentAmount * (1 + ($growthRate / 100));
            $predictions[] = [
                'month' => $i,
                'predicted_amount' => round($currentAmount, 2),
                'confidence' => $this->calculateConfidence($i, $historicalEarnings),
            ];
        }
        
        return [
            'predictions' => $predictions,
            'total_predicted' => round(array_sum(array_column($predictions, 'predicted_amount')), 2),
            'based_on_days' => 90,
            'growth_rate' => $growthRate,
        ];
    }
    
    public function calculateGrowthPotential(User $user): array
    {
        $currentNetwork = $user->referral_count ?? 0;
        $activePercentage = $this->getActivePercentage($user);
        $currentTier = $user->starter_kit_tier;
        
        // Calculate potential based on network activation
        $potentialIfAllActive = $this->calculatePotentialEarnings($user, 100);
        $currentEarnings = $this->calculatePotentialEarnings($user, $activePercentage);
        $untappedPotential = $potentialIfAllActive - $currentEarnings;
        
        return [
            'current_network_size' => $currentNetwork,
            'active_percentage' => $activePercentage,
            'current_monthly_potential' => round($currentEarnings, 2),
            'full_activation_potential' => round($potentialIfAllActive, 2),
            'untapped_potential' => round($untappedPotential, 2),
            'growth_opportunities' => $this->identifyGrowthOpportunities($user),
        ];
    }
    
    public function calculateChurnRisk(User $user): array
    {
        $riskFactors = [];
        $riskScore = 0;
        
        // Check last login
        $daysSinceLogin = $user->last_login_at 
            ? now()->diffInDays($user->last_login_at) 
            : 999;
        
        if ($daysSinceLogin > 30) {
            $riskFactors[] = 'No login in 30+ days';
            $riskScore += 30;
        } elseif ($daysSinceLogin > 14) {
            $riskFactors[] = 'Infrequent logins';
            $riskScore += 15;
        }
        
        // Check network activity
        $activeReferrals = $user->directReferrals()
            ->where('is_currently_active', true)
            ->count();
        $totalReferrals = $user->referral_count ?? 1;
        $activePercentage = ($activeReferrals / $totalReferrals) * 100;
        
        if ($activePercentage < 30) {
            $riskFactors[] = 'Low network engagement';
            $riskScore += 25;
        }
        
        // Check earnings trend
        $earningsTrend = $this->getEarningsTrend($user, 90);
        if ($earningsTrend < 0) {
            $riskFactors[] = 'Declining earnings';
            $riskScore += 20;
        }
        
        // Check starter kit usage
        $kitUsage = DB::table('analytics_events')
            ->where('user_id', $user->id)
            ->where('event_type', 'starter_kit_access')
            ->where('created_at', '>', now()->subDays(30))
            ->count();
        
        if ($kitUsage === 0) {
            $riskFactors[] = 'No starter kit engagement';
            $riskScore += 15;
        }
        
        $riskLevel = $this->getRiskLevel($riskScore);
        
        return [
            'risk_score' => min($riskScore, 100),
            'risk_level' => $riskLevel,
            'risk_factors' => $riskFactors,
            'retention_actions' => $this->getRetentionActions($riskLevel),
        ];
    }
    
    public function getNextMilestone(User $user): ?array
    {
        $currentNetwork = $user->referral_count ?? 0;
        $milestones = [
            ['size' => 3, 'level' => 'Professional', 'reward' => 'Level 2 commissions'],
            ['size' => 9, 'level' => 'Senior', 'reward' => 'Level 3 commissions'],
            ['size' => 27, 'level' => 'Manager', 'reward' => 'Leadership bonus'],
            ['size' => 81, 'level' => 'Director', 'reward' => 'Director benefits'],
            ['size' => 243, 'level' => 'Executive', 'reward' => 'Executive perks'],
            ['size' => 729, 'level' => 'Ambassador', 'reward' => 'Ambassador status'],
        ];
        
        foreach ($milestones as $milestone) {
            if ($currentNetwork < $milestone['size']) {
                $progress = ($currentNetwork / $milestone['size']) * 100;
                $remaining = $milestone['size'] - $currentNetwork;
                
                return [
                    'milestone' => $milestone,
                    'current_progress' => round($progress, 1),
                    'remaining' => $remaining,
                    'estimated_days' => $this->estimateDaysToMilestone($user, $remaining),
                ];
            }
        }
        
        return null;
    }
    
    protected function getHistoricalEarnings(User $user, int $days): array
    {
        $earnings = DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'credit')
            ->where('created_at', '>', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        if ($earnings->isEmpty()) {
            return ['average_monthly' => 0, 'growth_rate' => 0];
        }
        
        $totalEarnings = $earnings->sum('total');
        $averageMonthly = ($totalEarnings / $days) * 30;
        
        // Calculate growth rate
        $firstHalf = $earnings->take(ceil($earnings->count() / 2))->sum('total');
        $secondHalf = $earnings->skip(ceil($earnings->count() / 2))->sum('total');
        $growthRate = $firstHalf > 0 ? (($secondHalf - $firstHalf) / $firstHalf) * 100 : 0;
        
        return [
            'average_monthly' => $averageMonthly,
            'growth_rate' => $growthRate,
            'data_points' => $earnings->count(),
        ];
    }
    
    protected function calculateConfidence(int $monthsAhead, array $historicalData): int
    {
        $dataPoints = $historicalData['data_points'] ?? 0;
        $baseConfidence = min(($dataPoints / 90) * 100, 100);
        $decayFactor = max(0, 100 - ($monthsAhead * 5));
        
        return (int) min(($baseConfidence * $decayFactor) / 100, 100);
    }
    
    protected function getActivePercentage(User $user): float
    {
        $total = $user->referral_count ?? 0;
        
        if ($total === 0) {
            return 0.0;
        }
        
        $active = $user->directReferrals()
            ->where('is_currently_active', true)
            ->count();
        
        return round(($active / $total) * 100, 1);
    }
    
    protected function calculatePotentialEarnings(User $user, float $activePercentage): float
    {
        $networkSize = $user->referral_count ?? 0;
        $tierMultiplier = $user->starter_kit_tier === 'premium' ? 1.5 : 1.0;
        
        // Base calculation: network size * active % * tier multiplier * average commission
        $baseEarnings = $networkSize * ($activePercentage / 100) * $tierMultiplier * 50;
        
        return $baseEarnings;
    }
    
    protected function identifyGrowthOpportunities(User $user): array
    {
        $opportunities = [];
        
        if ($user->starter_kit_tier === 'basic') {
            $opportunities[] = [
                'type' => 'upgrade',
                'title' => 'Upgrade to Premium',
                'potential_increase' => '50%',
            ];
        }
        
        $activePercentage = $this->getActivePercentage($user);
        if ($activePercentage < 70) {
            $opportunities[] = [
                'type' => 'activation',
                'title' => 'Activate Inactive Members',
                'potential_increase' => round((70 - $activePercentage) * 10) . '%',
            ];
        }
        
        return $opportunities;
    }
    
    protected function getEarningsTrend(User $user, int $days): float
    {
        $firstPeriod = DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'credit')
            ->whereBetween('created_at', [now()->subDays($days), now()->subDays($days / 2)])
            ->sum('amount');
        
        $secondPeriod = DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'credit')
            ->where('created_at', '>', now()->subDays($days / 2))
            ->sum('amount');
        
        if ($firstPeriod == 0) return 0;
        
        return (($secondPeriod - $firstPeriod) / $firstPeriod) * 100;
    }
    
    protected function getRiskLevel(int $score): string
    {
        if ($score >= 70) return 'high';
        if ($score >= 40) return 'medium';
        return 'low';
    }
    
    protected function getRetentionActions(string $riskLevel): array
    {
        $actions = [
            'high' => [
                'Send personalized re-engagement email',
                'Offer special incentive or bonus',
                'Schedule 1-on-1 support call',
            ],
            'medium' => [
                'Send reminder about unused benefits',
                'Highlight recent platform updates',
                'Suggest relevant learning resources',
            ],
            'low' => [
                'Continue regular engagement',
                'Share success stories',
                'Provide growth tips',
            ],
        ];
        
        return $actions[$riskLevel] ?? [];
    }
    
    protected function estimateDaysToMilestone(User $user, int $remaining): ?int
    {
        $recentGrowth = DB::table('users')
            ->where('referrer_id', $user->id)
            ->where('created_at', '>', now()->subDays(90))
            ->count();
        
        if ($recentGrowth === 0) return null;
        
        $dailyRate = $recentGrowth / 90;
        return $dailyRate > 0 ? (int) ceil($remaining / $dailyRate) : null;
    }
}
