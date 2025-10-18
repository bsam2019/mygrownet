<?php

namespace App\Services;

use App\Models\PhysicalRewardAllocation;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AssetIncomeTrackingService
{
    /**
     * Record income generated from an asset
     */
    public function recordIncome(
        PhysicalRewardAllocation $allocation,
        float $amount,
        string $source = null,
        ?Carbon $date = null
    ): bool {
        if (!$allocation->physicalReward->income_generating) {
            return false;
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Income amount must be positive');
        }

        try {
            DB::beginTransaction();

            $allocation->recordIncomeGenerated($amount);

            // Create income record for detailed tracking
            $this->createIncomeRecord($allocation, $amount, $source, $date ?? now());

            // Update asset appreciation if applicable
            $this->updateAssetAppreciation($allocation);

            DB::commit();

            Log::info("Asset income recorded", [
                'allocation_id' => $allocation->id,
                'user_id' => $allocation->user_id,
                'amount' => $amount,
                'source' => $source,
                'total_income' => $allocation->total_income_generated
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to record asset income", [
                'allocation_id' => $allocation->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get comprehensive income report for a user
     */
    public function getUserIncomeReport(User $user): array
    {
        $allocations = PhysicalRewardAllocation::with('physicalReward')
            ->where('user_id', $user->id)
            ->whereIn('status', ['delivered', 'ownership_transferred'])
            ->whereHas('physicalReward', function ($query) {
                $query->where('income_generating', true);
            })
            ->get();

        $totalIncome = $allocations->sum('total_income_generated');
        $monthlyAverage = $allocations->avg('monthly_income_average');
        $activeAssets = $allocations->count();

        // Calculate performance metrics
        $performanceMetrics = $this->calculatePerformanceMetrics($allocations);

        return [
            'summary' => [
                'total_income_generated' => $totalIncome,
                'monthly_average' => $monthlyAverage,
                'active_assets' => $activeAssets,
                'estimated_annual_income' => $monthlyAverage * 12,
                'roi_percentage' => $this->calculateROI($allocations)
            ],
            'performance_metrics' => $performanceMetrics,
            'asset_breakdown' => $this->getAssetBreakdown($allocations),
            'monthly_trends' => $this->getMonthlyTrends($user),
            'projections' => $this->calculateIncomeProjections($allocations)
        ];
    }

    /**
     * Get asset performance analytics
     */
    public function getAssetPerformanceAnalytics(PhysicalRewardAllocation $allocation): array
    {
        $reward = $allocation->physicalReward;
        $monthsSinceDelivery = $allocation->delivered_at 
            ? $allocation->delivered_at->diffInMonths(now()) + 1 
            : 0;

        $expectedIncome = $reward->estimated_monthly_income * $monthsSinceDelivery;
        $actualIncome = $allocation->total_income_generated;
        $performanceRatio = $expectedIncome > 0 ? ($actualIncome / $expectedIncome) * 100 : 0;

        return [
            'asset_info' => [
                'name' => $reward->name,
                'category' => $reward->category,
                'estimated_value' => $reward->estimated_value,
                'estimated_monthly_income' => $reward->estimated_monthly_income
            ],
            'performance' => [
                'months_active' => $monthsSinceDelivery,
                'expected_income' => $expectedIncome,
                'actual_income' => $actualIncome,
                'performance_ratio' => $performanceRatio,
                'monthly_average' => $allocation->monthly_income_average,
                'status' => $this->getPerformanceStatus($performanceRatio)
            ],
            'maintenance' => [
                'compliant' => $allocation->maintenance_compliant,
                'months_completed' => $allocation->maintenance_months_completed,
                'required_months' => $reward->maintenance_period_months,
                'last_check' => $allocation->last_maintenance_check,
                'ownership_eligible' => $allocation->isEligibleForOwnershipTransfer()
            ],
            'income_history' => $this->getIncomeHistory($allocation),
            'projections' => $this->getAssetProjections($allocation)
        ];
    }

    /**
     * Process asset appreciation updates
     */
    public function processAssetAppreciationUpdates(): array
    {
        $results = [
            'processed' => 0,
            'updated' => 0,
            'errors' => 0
        ];

        $allocations = PhysicalRewardAllocation::with('physicalReward')
            ->whereIn('status', ['delivered', 'ownership_transferred'])
            ->whereHas('physicalReward', function ($query) {
                $query->where('category', 'property')
                      ->orWhere('category', 'vehicle');
            })
            ->get();

        foreach ($allocations as $allocation) {
            $results['processed']++;
            
            try {
                $this->updateAssetAppreciation($allocation);
                $results['updated']++;
            } catch (\Exception $e) {
                $results['errors']++;
                Log::error("Failed to update asset appreciation", [
                    'allocation_id' => $allocation->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Get income optimization recommendations
     */
    public function getIncomeOptimizationRecommendations(User $user): array
    {
        $allocations = PhysicalRewardAllocation::with('physicalReward')
            ->where('user_id', $user->id)
            ->whereIn('status', ['delivered', 'ownership_transferred'])
            ->get();

        $recommendations = [];

        foreach ($allocations as $allocation) {
            $performance = $this->getAssetPerformanceAnalytics($allocation);
            
            if ($performance['performance']['performance_ratio'] < 80) {
                $recommendations[] = [
                    'type' => 'underperforming_asset',
                    'asset' => $allocation->physicalReward->name,
                    'issue' => 'Asset generating below expected income',
                    'recommendation' => $this->getUnderperformanceRecommendation($allocation),
                    'priority' => 'high'
                ];
            }

            if (!$allocation->maintenance_compliant) {
                $recommendations[] = [
                    'type' => 'maintenance_issue',
                    'asset' => $allocation->physicalReward->name,
                    'issue' => 'Asset maintenance requirements not met',
                    'recommendation' => 'Review performance requirements and take corrective action',
                    'priority' => 'critical'
                ];
            }

            if ($allocation->isEligibleForOwnershipTransfer()) {
                $recommendations[] = [
                    'type' => 'ownership_transfer',
                    'asset' => $allocation->physicalReward->name,
                    'issue' => 'Asset eligible for ownership transfer',
                    'recommendation' => 'Complete ownership transfer process to gain full control',
                    'priority' => 'medium'
                ];
            }
        }

        // Add growth opportunities
        $recommendations = array_merge($recommendations, $this->getGrowthOpportunities($user));

        return $recommendations;
    }

    /**
     * Create detailed income record
     */
    private function createIncomeRecord(
        PhysicalRewardAllocation $allocation,
        float $amount,
        ?string $source,
        Carbon $date
    ): void {
        // This would create a detailed income record in a separate table
        // For now, we'll just log it
        Log::info("Detailed income record", [
            'allocation_id' => $allocation->id,
            'amount' => $amount,
            'source' => $source,
            'date' => $date->toDateString(),
            'category' => $allocation->physicalReward->category
        ]);
    }

    /**
     * Update asset appreciation value
     */
    private function updateAssetAppreciation(PhysicalRewardAllocation $allocation): void
    {
        $reward = $allocation->physicalReward;
        
        // Calculate appreciation based on asset type and market conditions
        $appreciationRate = $this->getAppreciationRate($reward->category);
        $monthsSinceAllocation = $allocation->allocated_at->diffInMonths(now());
        
        if ($monthsSinceAllocation > 0 && $appreciationRate > 0) {
            $currentValue = $reward->estimated_value * (1 + ($appreciationRate * $monthsSinceAllocation / 12));
            
            // Update asset management details with current value
            $assetDetails = $allocation->asset_management_details ?? [];
            $assetDetails['current_estimated_value'] = $currentValue;
            $assetDetails['appreciation_rate'] = $appreciationRate;
            $assetDetails['last_valuation_date'] = now()->toDateString();
            
            $allocation->update(['asset_management_details' => $assetDetails]);
        }
    }

    /**
     * Get appreciation rate for asset category
     */
    private function getAppreciationRate(string $category): float
    {
        return match ($category) {
            'property' => 0.08, // 8% annual appreciation
            'vehicle' => -0.15, // 15% annual depreciation
            'electronics' => -0.20, // 20% annual depreciation
            'business_kit' => -0.10, // 10% annual depreciation
            default => 0.0
        };
    }

    /**
     * Calculate performance metrics
     */
    private function calculatePerformanceMetrics(Collection $allocations): array
    {
        $totalExpectedIncome = 0;
        $totalActualIncome = 0;

        foreach ($allocations as $allocation) {
            $monthsActive = $allocation->delivered_at 
                ? $allocation->delivered_at->diffInMonths(now()) + 1 
                : 0;
            
            $expectedIncome = $allocation->physicalReward->estimated_monthly_income * $monthsActive;
            $totalExpectedIncome += $expectedIncome;
            $totalActualIncome += $allocation->total_income_generated;
        }

        $overallPerformance = $totalExpectedIncome > 0 
            ? ($totalActualIncome / $totalExpectedIncome) * 100 
            : 0;

        return [
            'overall_performance_ratio' => $overallPerformance,
            'total_expected_income' => $totalExpectedIncome,
            'total_actual_income' => $totalActualIncome,
            'performance_status' => $this->getPerformanceStatus($overallPerformance)
        ];
    }

    /**
     * Get asset breakdown by category
     */
    private function getAssetBreakdown(Collection $allocations): array
    {
        return $allocations->groupBy('physicalReward.category')
            ->map(function ($categoryAllocations, $category) {
                return [
                    'category' => $category,
                    'count' => $categoryAllocations->count(),
                    'total_income' => $categoryAllocations->sum('total_income_generated'),
                    'average_monthly' => $categoryAllocations->avg('monthly_income_average'),
                    'assets' => $categoryAllocations->map(function ($allocation) {
                        return [
                            'name' => $allocation->physicalReward->name,
                            'income' => $allocation->total_income_generated,
                            'monthly_average' => $allocation->monthly_income_average,
                            'status' => $allocation->status
                        ];
                    })->toArray()
                ];
            })->values()->toArray();
    }

    /**
     * Get monthly income trends
     */
    private function getMonthlyTrends(User $user): array
    {
        // This would query detailed income records
        // For now, return placeholder data
        return [
            'last_6_months' => [],
            'growth_rate' => 0,
            'trend_direction' => 'stable'
        ];
    }

    /**
     * Calculate income projections
     */
    private function calculateIncomeProjections(Collection $allocations): array
    {
        $currentMonthlyAverage = $allocations->avg('monthly_income_average');
        
        return [
            'next_month' => $currentMonthlyAverage,
            'next_quarter' => $currentMonthlyAverage * 3,
            'next_year' => $currentMonthlyAverage * 12,
            'assumptions' => 'Based on current performance trends'
        ];
    }

    /**
     * Get performance status description
     */
    private function getPerformanceStatus(float $performanceRatio): string
    {
        return match (true) {
            $performanceRatio >= 120 => 'excellent',
            $performanceRatio >= 100 => 'good',
            $performanceRatio >= 80 => 'fair',
            $performanceRatio >= 60 => 'poor',
            default => 'critical'
        };
    }

    /**
     * Calculate ROI percentage
     */
    private function calculateROI(Collection $allocations): float
    {
        $totalInvestment = $allocations->sum('physicalReward.estimated_value');
        $totalIncome = $allocations->sum('total_income_generated');
        
        return $totalInvestment > 0 ? ($totalIncome / $totalInvestment) * 100 : 0;
    }

    /**
     * Get income history for an asset
     */
    private function getIncomeHistory(PhysicalRewardAllocation $allocation): array
    {
        // This would query detailed income records
        // For now, return placeholder data
        return [
            'monthly_records' => [],
            'total_records' => 0
        ];
    }

    /**
     * Get asset projections
     */
    private function getAssetProjections(PhysicalRewardAllocation $allocation): array
    {
        $monthlyAverage = $allocation->monthly_income_average;
        
        return [
            'next_month' => $monthlyAverage,
            'next_quarter' => $monthlyAverage * 3,
            'next_year' => $monthlyAverage * 12
        ];
    }

    /**
     * Get underperformance recommendation
     */
    private function getUnderperformanceRecommendation(PhysicalRewardAllocation $allocation): string
    {
        return match ($allocation->physicalReward->category) {
            'vehicle' => 'Consider increasing usage hours or exploring additional income streams like delivery services',
            'property' => 'Review rental rates and occupancy levels, consider property improvements',
            'business_kit' => 'Expand service offerings or increase marketing efforts',
            default => 'Review asset utilization and explore optimization opportunities'
        };
    }

    /**
     * Get growth opportunities
     */
    private function getGrowthOpportunities(User $user): array
    {
        $opportunities = [];
        
        // Check if user is eligible for higher tier rewards
        $currentTier = $user->membershipTier;
        if ($currentTier) {
            $teamVolume = $user->getCurrentTeamVolume();
            if ($teamVolume && $teamVolume->team_volume > $currentTier->minimum_investment * 2) {
                $opportunities[] = [
                    'type' => 'tier_upgrade',
                    'asset' => 'Membership Tier',
                    'issue' => 'Eligible for higher tier with better rewards',
                    'recommendation' => 'Consider upgrading to access premium physical rewards',
                    'priority' => 'medium'
                ];
            }
        }

        return $opportunities;
    }
}