<?php

namespace App\Application\Services;

use App\Application\UseCases\Asset\ProcessAssetMaintenanceUseCase;
use App\Models\PhysicalRewardAllocation;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class AssetMaintenanceService
{
    public function __construct(
        private ProcessAssetMaintenanceUseCase $processAssetMaintenanceUseCase
    ) {}

    /**
     * Monitor all asset maintenance requirements
     */
    public function monitorAssetMaintenance(): array
    {
        $results = $this->processAssetMaintenanceUseCase->execute();
        
        // Send notifications for violations and completions
        $this->sendMaintenanceNotifications($results['details']);
        
        return $results;
    }

    /**
     * Check maintenance for a specific allocation
     */
    public function checkAllocationMaintenance(int $allocationId): array
    {
        return $this->processAssetMaintenanceUseCase->execute($allocationId);
    }

    /**
     * Get maintenance alerts for users who are at risk
     */
    public function getMaintenanceAlerts(): array
    {
        $alerts = [];
        
        // Get allocations that are at risk
        $atRiskAllocations = PhysicalRewardAllocation::with(['user', 'physicalReward'])
            ->whereIn('status', ['pending', 'active'])
            ->get();

        foreach ($atRiskAllocations as $allocation) {
            $alert = $this->assessMaintenanceRisk($allocation);
            if ($alert['risk_level'] !== 'low') {
                $alerts[] = $alert;
            }
        }

        return [
            'total_alerts' => count($alerts),
            'high_risk' => count(array_filter($alerts, fn($a) => $a['risk_level'] === 'high')),
            'medium_risk' => count(array_filter($alerts, fn($a) => $a['risk_level'] === 'medium')),
            'alerts' => $alerts
        ];
    }

    /**
     * Assess maintenance risk for an allocation
     */
    private function assessMaintenanceRisk(PhysicalRewardAllocation $allocation): array
    {
        $user = $allocation->user;
        $asset = $allocation->physicalReward;
        
        // Get maintenance requirements
        $requirements = $this->getAssetMaintenanceRequirements($asset->type);
        
        // Check current status
        $currentReferrals = $user->activeReferrals()->count();
        $currentTeamVolume = $user->monthly_team_volume ?? 0;
        $currentTier = $user->investmentTier;
        
        // Calculate risk factors
        $referralRisk = $currentReferrals < $requirements['min_referrals'];
        $volumeRisk = $currentTeamVolume < $requirements['min_team_volume'];
        $tierRisk = !$currentTier || $currentTier->name !== $requirements['tier'];
        
        // Calculate months until completion
        $monthsElapsed = $allocation->allocated_at->diffInMonths(now());
        $monthsRemaining = max(0, $allocation->maintenance_period_months - $monthsElapsed);
        
        // Determine risk level
        $riskFactors = array_sum([$referralRisk, $volumeRisk, $tierRisk]);
        $riskLevel = match (true) {
            $riskFactors >= 2 => 'high',
            $riskFactors === 1 => 'medium',
            default => 'low'
        };

        return [
            'allocation_id' => $allocation->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'asset_type' => $asset->type,
            'asset_value' => $asset->value,
            'risk_level' => $riskLevel,
            'months_remaining' => $monthsRemaining,
            'risk_factors' => [
                'referral_deficit' => $referralRisk ? $requirements['min_referrals'] - $currentReferrals : 0,
                'volume_deficit' => $volumeRisk ? $requirements['min_team_volume'] - $currentTeamVolume : 0,
                'tier_mismatch' => $tierRisk,
                'current_tier' => $currentTier?->name,
                'required_tier' => $requirements['tier']
            ],
            'current_status' => [
                'referrals' => $currentReferrals,
                'team_volume' => $currentTeamVolume,
                'tier' => $currentTier?->name
            ],
            'requirements' => $requirements
        ];
    }

    /**
     * Get asset maintenance requirements
     */
    private function getAssetMaintenanceRequirements(string $assetType): array
    {
        return match ($assetType) {
            'STARTER_KIT' => [
                'tier' => 'Bronze Member',
                'min_referrals' => 1,
                'min_team_volume' => 0
            ],
            'SMARTPHONE', 'TABLET' => [
                'tier' => 'Silver Member',
                'min_referrals' => 3,
                'min_team_volume' => 15000
            ],
            'MOTORBIKE' => [
                'tier' => 'Gold Member',
                'min_referrals' => 10,
                'min_team_volume' => 50000
            ],
            'CAR' => [
                'tier' => 'Diamond Member',
                'min_referrals' => 25,
                'min_team_volume' => 150000
            ],
            'PROPERTY' => [
                'tier' => 'Elite Member',
                'min_referrals' => 50,
                'min_team_volume' => 500000
            ],
            default => [
                'tier' => 'Bronze Member',
                'min_referrals' => 0,
                'min_team_volume' => 0
            ]
        };
    }

    /**
     * Send maintenance notifications
     */
    private function sendMaintenanceNotifications(array $maintenanceDetails): void
    {
        foreach ($maintenanceDetails as $detail) {
            $user = User::find($detail['user_id']);
            if (!$user) continue;

            switch ($detail['action']) {
                case 'violated':
                    $this->sendViolationNotification($user, $detail);
                    break;
                case 'forfeited':
                    $this->sendForfeitureNotification($user, $detail);
                    break;
                case 'completed':
                    $this->sendCompletionNotification($user, $detail);
                    break;
            }
        }
    }

    /**
     * Send violation notification
     */
    private function sendViolationNotification(User $user, array $detail): void
    {
        // In a real implementation, you would send actual notifications
        Log::info("Maintenance violation notification", [
            'user_id' => $user->id,
            'asset_type' => $detail['asset_type'],
            'message' => $detail['message']
        ]);
        
        // Example: Send email/SMS notification
        // Notification::send($user, new AssetMaintenanceViolationNotification($detail));
    }

    /**
     * Send forfeiture notification
     */
    private function sendForfeitureNotification(User $user, array $detail): void
    {
        Log::warning("Asset forfeiture notification", [
            'user_id' => $user->id,
            'asset_type' => $detail['asset_type'],
            'message' => $detail['message']
        ]);
        
        // Example: Send email/SMS notification
        // Notification::send($user, new AssetForfeitureNotification($detail));
    }

    /**
     * Send completion notification
     */
    private function sendCompletionNotification(User $user, array $detail): void
    {
        Log::info("Asset ownership completion notification", [
            'user_id' => $user->id,
            'asset_type' => $detail['asset_type'],
            'message' => $detail['message']
        ]);
        
        // Example: Send email/SMS notification
        // Notification::send($user, new AssetOwnershipCompletionNotification($detail));
    }

    /**
     * Get maintenance schedule for upcoming checks
     */
    public function getMaintenanceSchedule(int $days = 30): array
    {
        $upcomingChecks = PhysicalRewardAllocation::with(['user', 'physicalReward'])
            ->whereIn('status', ['pending', 'active'])
            ->where(function ($query) use ($days) {
                $query->whereNull('last_maintenance_check')
                    ->orWhere('last_maintenance_check', '<=', now()->subDays($days));
            })
            ->orderBy('allocated_at')
            ->get();

        return $upcomingChecks->map(function ($allocation) {
            $monthsElapsed = $allocation->allocated_at->diffInMonths(now());
            $monthsRemaining = max(0, $allocation->maintenance_period_months - $monthsElapsed);
            
            return [
                'allocation_id' => $allocation->id,
                'user_id' => $allocation->user_id,
                'user_name' => $allocation->user->name,
                'asset_type' => $allocation->physicalReward->type,
                'asset_value' => $allocation->physicalReward->value,
                'allocated_at' => $allocation->allocated_at,
                'last_check' => $allocation->last_maintenance_check,
                'months_elapsed' => $monthsElapsed,
                'months_remaining' => $monthsRemaining,
                'priority' => $monthsRemaining <= 3 ? 'high' : ($monthsRemaining <= 6 ? 'medium' : 'low')
            ];
        })->toArray();
    }

    /**
     * Generate maintenance report
     */
    public function generateMaintenanceReport(): array
    {
        $totalAllocations = PhysicalRewardAllocation::count();
        $activeAllocations = PhysicalRewardAllocation::where('status', 'active')->count();
        $completedAllocations = PhysicalRewardAllocation::where('status', 'completed')->count();
        $forfeitedAllocations = PhysicalRewardAllocation::where('status', 'forfeited')->count();
        
        $maintenanceViolations = PhysicalRewardAllocation::where('maintenance_status', 'violated')
            ->where('last_maintenance_check', '>=', now()->subMonth())
            ->count();

        $alerts = $this->getMaintenanceAlerts();
        
        return [
            'summary' => [
                'total_allocations' => $totalAllocations,
                'active_allocations' => $activeAllocations,
                'completed_allocations' => $completedAllocations,
                'forfeited_allocations' => $forfeitedAllocations,
                'completion_rate' => $totalAllocations > 0 ? ($completedAllocations / $totalAllocations) * 100 : 0,
                'forfeiture_rate' => $totalAllocations > 0 ? ($forfeitedAllocations / $totalAllocations) * 100 : 0,
                'recent_violations' => $maintenanceViolations
            ],
            'alerts' => $alerts,
            'schedule' => $this->getMaintenanceSchedule(),
            'generated_at' => now()
        ];
    }

    /**
     * Process payment plan for asset recovery
     */
    public function createAssetPaymentPlan(int $allocationId, array $paymentTerms): array
    {
        $allocation = PhysicalRewardAllocation::with(['physicalReward', 'user'])
            ->findOrFail($allocationId);

        if ($allocation->status !== 'forfeited') {
            return [
                'success' => false,
                'error' => 'Payment plans are only available for forfeited assets'
            ];
        }

        $assetValue = $allocation->physicalReward->value;
        $paymentAmount = $paymentTerms['monthly_payment'] ?? ($assetValue * 0.1); // Default 10% monthly
        $totalPayments = ceil($assetValue / $paymentAmount);

        $paymentPlan = [
            'allocation_id' => $allocationId,
            'user_id' => $allocation->user_id,
            'asset_value' => $assetValue,
            'monthly_payment' => $paymentAmount,
            'total_payments' => $totalPayments,
            'total_amount' => $paymentAmount * $totalPayments,
            'start_date' => $paymentTerms['start_date'] ?? now()->addMonth(),
            'status' => 'active',
            'created_at' => now()
        ];

        Log::info("Asset payment plan created", $paymentPlan);

        return [
            'success' => true,
            'payment_plan' => $paymentPlan
        ];
    }
}