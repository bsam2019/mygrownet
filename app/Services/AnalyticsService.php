<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get comprehensive member performance analytics
     */
    public function getMemberPerformance(User $user): array
    {
        return Cache::remember("analytics.performance.{$user->id}", 3600, function () use ($user) {
            return [
                'earnings' => $this->getEarningsBreakdown($user),
                'network' => $this->getNetworkMetrics($user),
                'growth' => $this->getGrowthTrends($user),
                'engagement' => $this->getEngagementMetrics($user),
                'health_score' => $this->calculateHealthScore($user),
                'vs_peers' => $this->compareWithPeers($user),
            ];
        });
    }
    
    /**
     * Get earnings breakdown by source
     */
    public function getEarningsBreakdown(User $user): array
    {
        // Referral commissions
        $referralEarnings = (float) $user->referralCommissions()
            ->where('status', 'paid')
            ->sum('amount');
            
        // LGR profit sharing
        $lgrEarnings = (float) $user->profitShares()->sum('amount');
        
        // Level bonuses
        $levelBonuses = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'credit')
            ->where('description', 'like', '%bonus%')
            ->sum('amount');
        
        // Other earnings
        $otherEarnings = (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('transaction_type', 'credit')
            ->whereNotIn('description', ['Referral Commission', 'LGR Profit Share'])
            ->where('description', 'not like', '%bonus%')
            ->sum('amount');
        
        $total = $referralEarnings + $lgrEarnings + $levelBonuses + $otherEarnings;
        
        return [
            'total' => $total,
            'by_source' => [
                'referral_commissions' => $referralEarnings,
                'lgr_profit_sharing' => $lgrEarnings,
                'level_bonuses' => $levelBonuses,
                'other' => $otherEarnings,
            ],
        ];
    }
    
    /**
     * Get network metrics
     */
    public function getNetworkMetrics(User $user): array
    {
        // Get total network size from UserNetwork table (includes all levels)
        $totalSize = DB::table('user_networks')
            ->where('referrer_id', $user->id)
            ->count();
        
        // Count direct referrals
        $directReferrals = $user->directReferrals()->count();
        
        // Active members are those with starter kits (from the entire network)
        $activeCount = DB::table('user_networks')
            ->join('users', 'user_networks.user_id', '=', 'users.id')
            ->where('user_networks.referrer_id', $user->id)
            ->whereNotNull('users.has_starter_kit')
            ->where('users.has_starter_kit', true)
            ->count();
        
        return [
            'total_size' => $totalSize,
            'active_count' => $activeCount,
            'direct_referrals' => $directReferrals,
            'active_percentage' => $totalSize > 0 ? round(($activeCount / $totalSize) * 100, 1) : 0,
        ];
    }
    
    /**
     * Get growth trends
     */
    public function getGrowthTrends(User $user): array
    {
        $last30Days = $user->directReferrals()
            ->where('created_at', '>', now()->subDays(30))
            ->count();
        
        $previous30Days = $user->directReferrals()
            ->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
            ->count();
        
        $growthRate = $previous30Days > 0 
            ? round((($last30Days - $previous30Days) / $previous30Days) * 100, 1)
            : 0;
        
        return [
            'last_30_days' => $last30Days,
            'previous_30_days' => $previous30Days,
            'growth_rate' => $growthRate,
        ];
    }
    
    /**
     * Get engagement metrics
     */
    public function getEngagementMetrics(User $user): array
    {
        $lastLogin = $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never';
        $loginCount = DB::table('analytics_events')
            ->where('user_id', $user->id)
            ->where('event_type', 'login')
            ->where('created_at', '>', now()->subDays(30))
            ->count();
        
        return [
            'last_login' => $lastLogin,
            'login_count_30d' => $loginCount,
            'is_active' => $user->is_currently_active ?? false,
        ];
    }
    
    /**
     * Calculate health score (0-100)
     */
    public function calculateHealthScore(User $user): int
    {
        $score = 0;
        
        // Active status (30 points)
        if ($user->is_currently_active) {
            $score += 30;
        }
        
        // Has starter kit (20 points)
        if ($user->has_starter_kit) {
            $score += 20;
        }
        
        // Network size (25 points)
        $networkSize = $user->referral_count ?? 0;
        if ($networkSize > 0) {
            $score += min(25, $networkSize * 2);
        }
        
        // Active network percentage (25 points) - active = has starter kit
        $activeCount = $user->directReferrals()->whereNotNull('has_starter_kit')->where('has_starter_kit', true)->count();
        $totalSize = $user->referral_count ?? 0;
        if ($totalSize > 0) {
            $activePercentage = ($activeCount / $totalSize) * 100;
            $score += round($activePercentage * 0.25);
        }
        
        return min(100, $score);
    }
    
    /**
     * Compare with peers
     */
    public function compareWithPeers(User $user): ?array
    {
        $tier = $user->starter_kit_tier ?? 'none';
        
        if ($tier === 'none') {
            return null;
        }
        
        // Get all peers with same tier
        $peers = User::where('starter_kit_tier', $tier)
            ->where('id', '!=', $user->id)
            ->where('has_starter_kit', true)
            ->get();
        
        if ($peers->count() === 0) {
            // No peers to compare with, return middle percentiles
            return [
                'tier' => $tier,
                'earnings_percentile' => 50,
                'network_percentile' => 50,
                'growth_percentile' => 50,
                'peer_count' => 0,
            ];
        }
        
        // Calculate user's earnings
        $userEarnings = $this->getEarningsBreakdown($user)['total'];
        
        // Get peer earnings and calculate percentile
        $peerEarnings = $peers->map(function($peer) {
            return $this->getEarningsBreakdown($peer)['total'];
        })->sort()->values();
        
        // Calculate how many peers have lower earnings
        $lowerEarningsCount = $peerEarnings->filter(function($earnings) use ($userEarnings) {
            return $earnings < $userEarnings;
        })->count();
        
        $earningsPercentile = $peers->count() > 0
            ? round(($lowerEarningsCount / $peers->count()) * 100, 0)
            : 50;
        
        // Get user's network size
        $userNetwork = DB::table('user_networks')
            ->where('referrer_id', $user->id)
            ->count();
        
        // Get peer network sizes
        $peerNetworks = $peers->map(function($peer) {
            return DB::table('user_networks')
                ->where('referrer_id', $peer->id)
                ->count();
        })->sort()->values();
        
        // Calculate how many peers have smaller networks
        $lowerNetworkCount = $peerNetworks->filter(function($network) use ($userNetwork) {
            return $network < $userNetwork;
        })->count();
        
        $networkPercentile = $peers->count() > 0
            ? round(($lowerNetworkCount / $peers->count()) * 100, 0)
            : 50;
        
        // Calculate growth percentile (based on last 30 days)
        $userGrowth = $user->directReferrals()
            ->where('created_at', '>', now()->subDays(30))
            ->count();
        
        $peerGrowth = $peers->map(function($peer) {
            return $peer->directReferrals()
                ->where('created_at', '>', now()->subDays(30))
                ->count();
        })->sort()->values();
        
        $lowerGrowthCount = $peerGrowth->filter(function($growth) use ($userGrowth) {
            return $growth < $userGrowth;
        })->count();
        
        $growthPercentile = $peers->count() > 0
            ? round(($lowerGrowthCount / $peers->count()) * 100, 0)
            : 50;
        
        return [
            'tier' => $tier,
            'earnings_percentile' => $earningsPercentile,
            'network_percentile' => $networkPercentile,
            'growth_percentile' => $growthPercentile,
            'peer_count' => $peers->count(),
        ];
    }
    
    /**
     * Track analytics event
     */
    public function trackEvent(User $user, string $eventType, ?array $metadata = null): void
    {
        DB::table('analytics_events')->insert([
            'user_id' => $user->id,
            'event_type' => $eventType,
            'metadata' => $metadata ? json_encode($metadata) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
