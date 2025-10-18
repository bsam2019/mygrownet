<?php

namespace App\Services;

use App\Models\User;
use App\Models\ReferralCommission;
use App\Models\TeamVolume;
use App\Models\UserNetwork;
use App\Models\InvestmentTier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MLMAdministrationService
{
    /**
     * Get overview metrics for the dashboard
     */
    public function getOverviewMetrics(string $period = 'month'): array
    {
        $startDate = $this->getPeriodStartDate($period);
        
        return [
            'total_commissions' => $this->getTotalCommissions($startDate),
            'pending_commissions' => $this->getPendingCommissions(),
            'active_members' => $this->getActiveMembers($startDate),
            'network_growth' => $this->getNetworkGrowth($startDate),
            'total_volume' => $this->getTotalVolume($startDate),
            'compliance_score' => $this->getComplianceScore(),
        ];
    }

    /**
     * Get commission statistics
     */
    public function getCommissionStatistics(string $period = 'month'): array
    {
        $startDate = $this->getPeriodStartDate($period);
        
        $stats = ReferralCommission::where('created_at', '>=', $startDate)
            ->selectRaw('
                commission_type,
                level,
                status,
                COUNT(*) as count,
                SUM(amount) as total_amount,
                AVG(amount) as avg_amount
            ')
            ->groupBy(['commission_type', 'level', 'status'])
            ->get();

        return [
            'by_type' => $this->groupStatsByType($stats),
            'by_level' => $this->groupStatsByLevel($stats),
            'by_status' => $this->groupStatsByStatus($stats),
            'trends' => $this->getCommissionTrends($period),
        ];
    }

    /**
     * Get network analytics data
     */
    public function getNetworkAnalytics(): array
    {
        return [
            'depth_distribution' => $this->getNetworkDepthDistribution(),
            'tier_distribution' => $this->getTierDistribution(),
            'top_performers' => $this->getTopPerformers(),
            'growth_metrics' => $this->getGrowthMetrics(),
        ];
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(string $period = 'month'): array
    {
        $startDate = $this->getPeriodStartDate($period);
        
        return [
            'volume_performance' => $this->getVolumePerformance($startDate),
            'tier_advancement' => $this->getTierAdvancementMetrics($startDate),
            'retention_rates' => $this->getRetentionRates($startDate),
            'conversion_rates' => $this->getConversionRates($startDate),
        ];
    }

    /**
     * Get system alerts
     */
    public function getSystemAlerts(): array
    {
        $alerts = [];
        
        // High pending commissions
        $pendingCount = ReferralCommission::where('status', 'pending')->count();
        if ($pendingCount > 100) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'High Pending Commissions',
                'message' => "{$pendingCount} commissions are pending processing",
                'action' => 'Process Commissions',
                'url' => route('admin.mlm.commissions')
            ];
        }
        
        // Compliance issues
        $complianceScore = $this->getComplianceScore();
        if ($complianceScore < 85) {
            $alerts[] = [
                'type' => 'error',
                'title' => 'Compliance Alert',
                'message' => "Compliance score is {$complianceScore}% - below threshold",
                'action' => 'Review Compliance',
                'url' => route('admin.mlm.performance-monitoring')
            ];
        }
        
        // Network anomalies
        $anomalies = $this->detectNetworkAnomalies();
        if (!empty($anomalies)) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Network Anomalies Detected',
                'message' => count($anomalies) . ' potential network issues found',
                'action' => 'Investigate',
                'url' => route('admin.mlm.network-analysis')
            ];
        }
        
        return $alerts;
    }

    /**
     * Get commissions with filters
     */
    public function getCommissionsWithFilters(array $filters): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = ReferralCommission::with(['referrer', 'referee'])
            ->orderBy('created_at', 'desc');
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['type'])) {
            $query->where('commission_type', $filters['type']);
        }
        
        if (!empty($filters['level'])) {
            $query->where('level', $filters['level']);
        }
        
        if (!empty($filters['search'])) {
            $query->whereHas('referrer', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        return $query->paginate(25);
    }

    /**
     * Get network structure for a user
     */
    public function getNetworkStructure(int $userId, int $depth = 5): array
    {
        $user = User::findOrFail($userId);
        
        return [
            'root_user' => $this->formatUserForNetwork($user),
            'network_tree' => $this->buildNetworkTree($user, $depth),
            'statistics' => $this->getNetworkStatistics($user),
        ];
    }

    /**
     * Adjust commission amount
     */
    public function adjustCommission(int $commissionId, float $newAmount, string $reason, int $adminId): array
    {
        $commission = ReferralCommission::findOrFail($commissionId);
        $originalAmount = $commission->amount;
        
        DB::beginTransaction();
        
        try {
            // Update commission
            $commission->update([
                'amount' => $newAmount,
                'adjusted_by' => $adminId,
                'adjustment_reason' => $reason,
                'adjusted_at' => now(),
            ]);
            
            // Log the adjustment
            Log::info('Commission adjusted', [
                'commission_id' => $commissionId,
                'original_amount' => $originalAmount,
                'new_amount' => $newAmount,
                'reason' => $reason,
                'admin_id' => $adminId,
            ]);
            
            // Update user balance if commission was already paid
            if ($commission->status === 'paid') {
                $difference = $newAmount - $originalAmount;
                $commission->referrer->increment('balance', $difference);
            }
            
            DB::commit();
            
            return [
                'commission' => $commission->fresh(),
                'adjustment' => [
                    'original_amount' => $originalAmount,
                    'new_amount' => $newAmount,
                    'difference' => $newAmount - $originalAmount,
                ]
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Process pending commissions in bulk
     */
    public function processPendingCommissions(array $commissionIds, string $action, int $adminId): array
    {
        $commissions = ReferralCommission::whereIn('id', $commissionIds)
            ->where('status', 'pending')
            ->get();
        
        $processed = [];
        $failed = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($commissions as $commission) {
                try {
                    if ($action === 'approve') {
                        $commission->processPayment();
                    } else {
                        $commission->update([
                            'status' => 'rejected',
                            'rejected_by' => $adminId,
                            'rejected_at' => now(),
                        ]);
                    }
                    
                    $processed[] = $commission->id;
                } catch (\Exception $e) {
                    $failed[] = [
                        'commission_id' => $commission->id,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            DB::commit();
            
            Log::info("Bulk commission processing completed", [
                'action' => $action,
                'processed_count' => count($processed),
                'failed_count' => count($failed),
                'admin_id' => $adminId,
            ]);
            
            return [
                'processed' => $processed,
                'failed' => $failed,
                'summary' => [
                    'total' => count($commissionIds),
                    'processed' => count($processed),
                    'failed' => count($failed),
                ]
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Recalculate network structure
     */
    public function recalculateNetworkStructure(): void
    {
        // This would typically be run as a job
        $mlmService = app(MLMCommissionService::class);
        $mlmService->rebuildNetworkPaths();
    }

    /**
     * Get commission details
     */
    public function getCommissionDetails(int $commissionId): array
    {
        $commission = ReferralCommission::with(['referrer', 'referee', 'investment'])
            ->findOrFail($commissionId);
        
        return [
            'commission' => $commission,
            'referrer_stats' => $this->getUserStats($commission->referrer_id),
            'referee_stats' => $this->getUserStats($commission->referred_id),
            'related_commissions' => $this->getRelatedCommissions($commission),
            'audit_trail' => $this->getCommissionAuditTrail($commissionId),
        ];
    }

    /**
     * Export commissions data
     */
    public function exportCommissions(array $filters): StreamedResponse
    {
        $filename = 'mlm_commissions_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        return new StreamedResponse(function () use ($filters) {
            $handle = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($handle, [
                'ID', 'Referrer', 'Referee', 'Level', 'Type', 'Amount', 
                'Status', 'Created At', 'Paid At', 'Team Volume', 'Personal Volume'
            ]);
            
            // Get commissions in chunks
            ReferralCommission::with(['referrer', 'referee'])
                ->when(!empty($filters['status']), fn($q) => $q->where('status', $filters['status']))
                ->when(!empty($filters['type']), fn($q) => $q->where('commission_type', $filters['type']))
                ->when(!empty($filters['level']), fn($q) => $q->where('level', $filters['level']))
                ->when(!empty($filters['date_from']), fn($q) => $q->where('created_at', '>=', $filters['date_from']))
                ->when(!empty($filters['date_to']), fn($q) => $q->where('created_at', '<=', $filters['date_to']))
                ->chunk(1000, function ($commissions) use ($handle) {
                    foreach ($commissions as $commission) {
                        fputcsv($handle, [
                            $commission->id,
                            $commission->referrer->name ?? 'N/A',
                            $commission->referee->name ?? 'N/A',
                            $commission->level,
                            $commission->commission_type,
                            $commission->amount,
                            $commission->status,
                            $commission->created_at->format('Y-m-d H:i:s'),
                            $commission->paid_at?->format('Y-m-d H:i:s') ?? 'N/A',
                            $commission->team_volume,
                            $commission->personal_volume,
                        ]);
                    }
                });
            
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // Helper methods
    
    private function getPeriodStartDate(string $period): Carbon
    {
        return match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };
    }

    private function getTotalCommissions(Carbon $startDate): array
    {
        $current = ReferralCommission::where('created_at', '>=', $startDate)->sum('amount');
        $previous = ReferralCommission::whereBetween('created_at', [
            $startDate->copy()->subPeriod($startDate->diffInDays(now())),
            $startDate
        ])->sum('amount');
        
        return [
            'current' => $current,
            'previous' => $previous,
            'change_percent' => $previous > 0 ? (($current - $previous) / $previous) * 100 : 0,
        ];
    }

    private function getPendingCommissions(): array
    {
        $count = ReferralCommission::where('status', 'pending')->count();
        $amount = ReferralCommission::where('status', 'pending')->sum('amount');
        
        return [
            'count' => $count,
            'amount' => $amount,
        ];
    }

    private function getActiveMembers(Carbon $startDate): array
    {
        $current = User::whereHas('referralCommissions', function ($q) use ($startDate) {
            $q->where('created_at', '>=', $startDate);
        })->count();
        
        return [
            'current' => $current,
            'total' => User::whereNotNull('referrer_id')->count(),
        ];
    }

    private function getNetworkGrowth(Carbon $startDate): array
    {
        $newMembers = User::where('created_at', '>=', $startDate)
            ->whereNotNull('referrer_id')
            ->count();
        
        return [
            'new_members' => $newMembers,
            'growth_rate' => $this->calculateGrowthRate($startDate),
        ];
    }

    private function getTotalVolume(Carbon $startDate): array
    {
        $current = TeamVolume::where('period_start', '>=', $startDate)->sum('team_volume');
        
        return [
            'current' => $current,
            'average_per_member' => $current > 0 ? $current / User::count() : 0,
        ];
    }

    private function getComplianceScore(): float
    {
        // Calculate compliance based on various factors
        $factors = [
            'commission_ratio' => $this->getCommissionToRevenueRatio(),
            'payout_timing' => $this->getPayoutTimingCompliance(),
            'volume_legitimacy' => $this->getVolumeLegitimacyScore(),
        ];
        
        return array_sum($factors) / count($factors);
    }

    private function getCommissionToRevenueRatio(): float
    {
        $totalCommissions = ReferralCommission::where('status', 'paid')->sum('amount');
        $totalRevenue = User::sum('total_invested'); // Approximate revenue
        
        $ratio = $totalRevenue > 0 ? ($totalCommissions / $totalRevenue) * 100 : 0;
        
        // Score based on 25% threshold (requirement 12.7)
        return $ratio <= 25 ? 100 : max(0, 100 - (($ratio - 25) * 4));
    }

    private function getPayoutTimingCompliance(): float
    {
        $commissions = ReferralCommission::where('status', 'paid')
            ->whereNotNull('paid_at')
            ->get();
        
        $compliantCount = $commissions->filter(function ($commission) {
            return $commission->created_at->diffInHours($commission->paid_at) <= 24;
        })->count();
        
        return $commissions->count() > 0 ? ($compliantCount / $commissions->count()) * 100 : 100;
    }

    private function getVolumeLegitimacyScore(): float
    {
        // Check for suspicious volume patterns
        // This is a simplified implementation
        return 95; // Placeholder
    }

    private function formatUserForNetwork(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'tier' => $user->currentTier?->name,
            'total_volume' => $user->current_team_volume ?? 0,
            'referral_count' => $user->directReferrals()->count(),
            'commission_earned' => $user->referralCommissions()->sum('amount'),
        ];
    }

    private function buildNetworkTree(User $user, int $depth, int $currentDepth = 0): array
    {
        if ($currentDepth >= $depth) {
            return [];
        }
        
        $children = $user->directReferrals()->with('currentTier')->get();
        
        return $children->map(function ($child) use ($depth, $currentDepth) {
            return [
                'user' => $this->formatUserForNetwork($child),
                'children' => $this->buildNetworkTree($child, $depth, $currentDepth + 1),
            ];
        })->toArray();
    }

    private function getNetworkStatistics(User $user): array
    {
        return [
            'total_network_size' => $this->getNetworkSize($user),
            'max_depth' => $this->getMaxNetworkDepth($user),
            'total_volume' => $this->getNetworkTotalVolume($user),
            'active_members' => $this->getActiveNetworkMembers($user),
        ];
    }

    private function getNetworkSize(User $user): int
    {
        return User::where('network_path', 'LIKE', $user->network_path . '.%')->count();
    }

    private function getMaxNetworkDepth(User $user): int
    {
        return User::where('network_path', 'LIKE', $user->network_path . '.%')
            ->max('network_level') - $user->network_level;
    }

    private function getNetworkTotalVolume(User $user): float
    {
        $networkUserIds = User::where('network_path', 'LIKE', $user->network_path . '.%')
            ->pluck('id');
        
        return TeamVolume::whereIn('user_id', $networkUserIds)
            ->where('period_start', '>=', now()->startOfMonth())
            ->sum('team_volume');
    }

    private function getActiveNetworkMembers(User $user): int
    {
        $networkUserIds = User::where('network_path', 'LIKE', $user->network_path . '.%')
            ->pluck('id');
        
        return User::whereIn('id', $networkUserIds)
            ->whereHas('subscriptions', function ($q) {
                $q->where('status', 'active');
            })
            ->count();
    }

    private function detectNetworkAnomalies(): array
    {
        $anomalies = [];
        
        // Check for users with unusually high commission rates
        $highEarners = ReferralCommission::selectRaw('referrer_id, SUM(amount) as total')
            ->where('created_at', '>=', now()->startOfMonth())
            ->groupBy('referrer_id')
            ->having('total', '>', 50000) // Threshold for investigation
            ->get();
        
        foreach ($highEarners as $earner) {
            $anomalies[] = [
                'type' => 'high_earnings',
                'user_id' => $earner->referrer_id,
                'amount' => $earner->total,
            ];
        }
        
        return $anomalies;
    }

    private function getUserStats(int $userId): array
    {
        $user = User::find($userId);
        if (!$user) return [];
        
        return [
            'total_commissions' => $user->referralCommissions()->sum('amount'),
            'monthly_commissions' => $user->referralCommissions()
                ->where('created_at', '>=', now()->startOfMonth())
                ->sum('amount'),
            'referral_count' => $user->directReferrals()->count(),
            'network_size' => $this->getNetworkSize($user),
            'current_tier' => $user->currentTier?->name,
        ];
    }

    private function getRelatedCommissions(ReferralCommission $commission): Collection
    {
        return ReferralCommission::where('referred_id', $commission->referred_id)
            ->where('id', '!=', $commission->id)
            ->with(['referrer'])
            ->get();
    }

    private function getCommissionAuditTrail(int $commissionId): array
    {
        // This would typically come from an audit log table
        // For now, return basic information
        return [
            'created_at' => ReferralCommission::find($commissionId)?->created_at,
            'adjustments' => [], // Would come from audit table
            'status_changes' => [], // Would come from audit table
        ];
    }

    private function calculateGrowthRate(Carbon $startDate): float
    {
        $currentPeriodMembers = User::where('created_at', '>=', $startDate)->count();
        $previousPeriodMembers = User::whereBetween('created_at', [
            $startDate->copy()->subDays($startDate->diffInDays(now())),
            $startDate
        ])->count();
        
        return $previousPeriodMembers > 0 
            ? (($currentPeriodMembers - $previousPeriodMembers) / $previousPeriodMembers) * 100 
            : 0;
    }

    // Additional helper methods would be implemented here...
    
    private function groupStatsByType(Collection $stats): array
    {
        return $stats->groupBy('commission_type')->map(function ($group) {
            return [
                'count' => $group->sum('count'),
                'total_amount' => $group->sum('total_amount'),
                'avg_amount' => $group->avg('avg_amount'),
            ];
        })->toArray();
    }

    private function groupStatsByLevel(Collection $stats): array
    {
        return $stats->groupBy('level')->map(function ($group) {
            return [
                'count' => $group->sum('count'),
                'total_amount' => $group->sum('total_amount'),
                'avg_amount' => $group->avg('avg_amount'),
            ];
        })->toArray();
    }

    private function groupStatsByStatus(Collection $stats): array
    {
        return $stats->groupBy('status')->map(function ($group) {
            return [
                'count' => $group->sum('count'),
                'total_amount' => $group->sum('total_amount'),
                'avg_amount' => $group->avg('avg_amount'),
            ];
        })->toArray();
    }

    private function getCommissionTrends(string $period): array
    {
        // Implementation for trend analysis
        return [];
    }

    private function getNetworkDepthDistribution(): array
    {
        return User::selectRaw('network_level, COUNT(*) as count')
            ->whereNotNull('network_level')
            ->groupBy('network_level')
            ->orderBy('network_level')
            ->get()
            ->pluck('count', 'network_level')
            ->toArray();
    }

    private function getTierDistribution(): array
    {
        return User::join('investment_tiers', 'users.current_tier_id', '=', 'investment_tiers.id')
            ->selectRaw('investment_tiers.name, COUNT(*) as count')
            ->groupBy('investment_tiers.name')
            ->get()
            ->pluck('count', 'name')
            ->toArray();
    }

    private function getTopPerformers(): array
    {
        return User::withSum('referralCommissions', 'amount')
            ->orderBy('referral_commissions_sum_amount', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'total_commissions' => $user->referral_commissions_sum_amount ?? 0,
                    'tier' => $user->currentTier?->name,
                ];
            })
            ->toArray();
    }

    private function getGrowthMetrics(): array
    {
        return [
            'monthly_growth' => $this->calculateGrowthRate(now()->startOfMonth()),
            'quarterly_growth' => $this->calculateGrowthRate(now()->startOfQuarter()),
            'yearly_growth' => $this->calculateGrowthRate(now()->startOfYear()),
        ];
    }

    private function getVolumePerformance(Carbon $startDate): array
    {
        return TeamVolume::where('period_start', '>=', $startDate)
            ->selectRaw('
                AVG(team_volume) as avg_volume,
                MAX(team_volume) as max_volume,
                MIN(team_volume) as min_volume,
                SUM(team_volume) as total_volume
            ')
            ->first()
            ->toArray();
    }

    private function getTierAdvancementMetrics(Carbon $startDate): array
    {
        // This would track tier upgrades within the period
        return [
            'total_upgrades' => 0, // Would come from tier upgrade tracking
            'by_tier' => [], // Breakdown by target tier
        ];
    }

    private function getRetentionRates(Carbon $startDate): array
    {
        // Calculate member retention rates
        return [
            'monthly_retention' => 85.5, // Placeholder
            'quarterly_retention' => 78.2, // Placeholder
        ];
    }

    private function getConversionRates(Carbon $startDate): array
    {
        // Calculate conversion from referral to active member
        return [
            'referral_to_member' => 65.3, // Placeholder
            'member_to_active' => 82.1, // Placeholder
        ];
    }

    public function getPendingCommissionsCount(): int
    {
        return ReferralCommission::where('status', 'pending')->count();
    }

    public function getRecentActivity(): array
    {
        return ReferralCommission::with(['referrer', 'referee'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($commission) {
                return [
                    'type' => 'commission_created',
                    'description' => "{$commission->referrer->name} earned K{$commission->amount} Level {$commission->level} commission",
                    'timestamp' => $commission->created_at,
                    'amount' => $commission->amount,
                ];
            })
            ->toArray();
    }

    public function getVolumeAnalytics(string $period): array
    {
        $startDate = $this->getPeriodStartDate($period);
        
        return [
            'total_volume' => TeamVolume::where('period_start', '>=', $startDate)->sum('team_volume'),
            'average_volume' => TeamVolume::where('period_start', '>=', $startDate)->avg('team_volume'),
            'volume_distribution' => $this->getVolumeDistribution($startDate),
            'top_volume_generators' => $this->getTopVolumeGenerators($startDate),
        ];
    }

    private function getVolumeDistribution(Carbon $startDate): array
    {
        return TeamVolume::where('period_start', '>=', $startDate)
            ->selectRaw('
                CASE 
                    WHEN team_volume < 1000 THEN "Under K1,000"
                    WHEN team_volume < 5000 THEN "K1,000 - K5,000"
                    WHEN team_volume < 15000 THEN "K5,000 - K15,000"
                    WHEN team_volume < 50000 THEN "K15,000 - K50,000"
                    ELSE "Over K50,000"
                END as volume_range,
                COUNT(*) as count
            ')
            ->groupBy('volume_range')
            ->get()
            ->pluck('count', 'volume_range')
            ->toArray();
    }

    private function getTopVolumeGenerators(Carbon $startDate): array
    {
        return TeamVolume::with('user')
            ->where('period_start', '>=', $startDate)
            ->orderBy('team_volume', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($volume) {
                return [
                    'user_id' => $volume->user_id,
                    'name' => $volume->user->name,
                    'volume' => $volume->team_volume,
                    'tier' => $volume->user->currentTier?->name,
                ];
            })
            ->toArray();
    }

    public function exportNetworkAnalysis(?int $userId, int $depth): StreamedResponse
    {
        $filename = 'network_analysis_' . ($userId ?? 'all') . '_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        return new StreamedResponse(function () use ($userId, $depth) {
            $handle = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($handle, [
                'User ID', 'Name', 'Email', 'Level', 'Referrer ID', 
                'Direct Referrals', 'Network Size', 'Total Volume', 'Tier'
            ]);
            
            $query = User::with(['currentTier', 'referrer']);
            
            if ($userId) {
                $user = User::find($userId);
                if ($user) {
                    $query->where('network_path', 'LIKE', $user->network_path . '%');
                }
            }
            
            $query->chunk(1000, function ($users) use ($handle) {
                foreach ($users as $user) {
                    fputcsv($handle, [
                        $user->id,
                        $user->name,
                        $user->email,
                        $user->network_level ?? 0,
                        $user->referrer_id,
                        $user->directReferrals()->count(),
                        $this->getNetworkSize($user),
                        $user->current_team_volume ?? 0,
                        $user->currentTier?->name ?? 'None',
                    ]);
                }
            });
            
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function getPerformanceData(string $period): array
    {
        return [
            'volume_performance' => $this->getVolumePerformance($this->getPeriodStartDate($period)),
            'commission_performance' => $this->getCommissionPerformance($period),
            'tier_performance' => $this->getTierPerformance($period),
            'network_performance' => $this->getNetworkPerformance($period),
        ];
    }

    private function getCommissionPerformance(string $period): array
    {
        $startDate = $this->getPeriodStartDate($period);
        
        return [
            'total_paid' => ReferralCommission::where('status', 'paid')
                ->where('paid_at', '>=', $startDate)
                ->sum('amount'),
            'average_commission' => ReferralCommission::where('created_at', '>=', $startDate)
                ->avg('amount'),
            'commission_count' => ReferralCommission::where('created_at', '>=', $startDate)
                ->count(),
        ];
    }

    private function getTierPerformance(string $period): array
    {
        // Track tier advancement performance
        return [
            'upgrades_count' => 0, // Would come from tier upgrade tracking
            'downgrades_count' => 0, // Would come from tier downgrade tracking
            'tier_distribution' => $this->getTierDistribution(),
        ];
    }

    private function getNetworkPerformance(string $period): array
    {
        $startDate = $this->getPeriodStartDate($period);
        
        return [
            'new_members' => User::where('created_at', '>=', $startDate)
                ->whereNotNull('referrer_id')
                ->count(),
            'active_recruiters' => User::whereHas('directReferrals', function ($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })->count(),
            'average_network_depth' => User::whereNotNull('network_level')->avg('network_level'),
        ];
    }
}