<?php

namespace App\Domain\Investor\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Platform Metrics Service
 * 
 * Domain service for calculating and retrieving platform metrics for investors
 */
class PlatformMetricsService
{
    /**
     * Get public-facing platform metrics
     * 
     * These metrics are cached for performance and shown to potential investors
     */
    public function getPublicMetrics(): array
    {
        return Cache::remember('investor.public_metrics', 3600, function () {
            return [
                'totalMembers' => $this->getTotalMembers(),
                'monthlyRevenue' => $this->getMonthlyRevenue(),
                'activeRate' => $this->getActiveRate(),
                'retention' => $this->getRetentionRate(),
                'revenueGrowth' => $this->getRevenueGrowthData(),
            ];
        });
    }

    /**
     * Get total member count
     */
    private function getTotalMembers(): int
    {
        // Count all users (you can filter out admins if needed)
        return DB::table('users')->count();
    }

    /**
     * Get monthly recurring revenue
     */
    private function getMonthlyRevenue(): float
    {
        // Try package_subscriptions first (newer table)
        if (Schema::hasTable('package_subscriptions')) {
            $revenue = DB::table('package_subscriptions')
                ->join('packages', 'package_subscriptions.package_id', '=', 'packages.id')
                ->where('package_subscriptions.status', 'active')
                ->where(function($query) {
                    // Check if end_date exists and is in the future, or if it's null (no expiry)
                    $query->where('package_subscriptions.end_date', '>', now())
                          ->orWhereNull('package_subscriptions.end_date');
                })
                ->sum('packages.price');
            
            return round($revenue, 2);
        }
        
        // Fallback to subscriptions table
        if (Schema::hasTable('subscriptions')) {
            $revenue = DB::table('subscriptions')
                ->where('status', 'active')
                ->where(function($query) {
                    $query->where('expires_at', '>', now())
                          ->orWhereNull('expires_at');
                })
                ->sum('amount');
            
            return round($revenue, 2);
        }

        return 0;
    }

    /**
     * Get active member rate (percentage)
     */
    private function getActiveRate(): float
    {
        $totalMembers = $this->getTotalMembers();
        
        if ($totalMembers === 0) {
            return 0;
        }

        // Active = logged in within last 30 days
        // Check if last_login_at column exists, otherwise use updated_at
        $activeMembers = DB::table('users')
            ->where(function($query) {
                if (Schema::hasColumn('users', 'last_login_at')) {
                    $query->where('last_login_at', '>=', now()->subDays(30));
                } else {
                    $query->where('updated_at', '>=', now()->subDays(30));
                }
            })
            ->count();

        return round(($activeMembers / $totalMembers) * 100, 1);
    }

    /**
     * Get member retention rate (percentage)
     */
    private function getRetentionRate(): float
    {
        // Calculate retention: members with active subscriptions
        $totalMembers = $this->getTotalMembers();
        
        if ($totalMembers === 0) {
            return 0;
        }

        // Try package_subscriptions first
        if (Schema::hasTable('package_subscriptions')) {
            $activeSubscriptions = DB::table('package_subscriptions')
                ->where('status', 'active')
                ->where(function($query) {
                    $query->where('end_date', '>', now())
                          ->orWhereNull('end_date');
                })
                ->distinct('user_id')
                ->count('user_id');
            
            return round(($activeSubscriptions / $totalMembers) * 100, 1);
        }
        
        // Fallback to subscriptions table
        if (Schema::hasTable('subscriptions')) {
            $activeSubscriptions = DB::table('subscriptions')
                ->where('status', 'active')
                ->where(function($query) {
                    $query->where('expires_at', '>', now())
                          ->orWhereNull('expires_at');
                })
                ->distinct('user_id')
                ->count('user_id');
            
            return round(($activeSubscriptions / $totalMembers) * 100, 1);
        }

        return 0;
    }

    /**
     * Get revenue growth data for chart (last 12 months)
     */
    private function getRevenueGrowthData(): array
    {
        $months = [];
        $data = [];

        // Get last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M');

            $monthRevenue = 0;

            // Try package_subscriptions first
            if (Schema::hasTable('package_subscriptions')) {
                $monthRevenue = DB::table('package_subscriptions')
                    ->join('packages', 'package_subscriptions.package_id', '=', 'packages.id')
                    ->whereYear('package_subscriptions.created_at', $date->year)
                    ->whereMonth('package_subscriptions.created_at', $date->month)
                    ->sum('packages.price');
            } elseif (Schema::hasTable('subscriptions')) {
                // Fallback to subscriptions table
                $monthRevenue = DB::table('subscriptions')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('amount');
            }

            $data[] = round($monthRevenue, 0);
        }

        return [
            'labels' => $months,
            'data' => $data
        ];
    }

    /**
     * Clear metrics cache (call when data changes)
     */
    public function clearCache(): void
    {
        Cache::forget('investor.public_metrics');
    }
}
