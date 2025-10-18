<?php

namespace App\Jobs;

use App\Domain\Investment\Services\InvestmentTierService;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\TierUpgrade;
use App\Notifications\TierUpgradeNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TierUpgradeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutes
    public $backoff = [30, 60, 120]; // Retry after 30s, 1min, 2min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $jobType,
        public ?int $userId = null,
        public array $additionalData = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(InvestmentTierService $tierService): void
    {
        try {
            Log::info("Starting {$this->jobType} tier upgrade processing", [
                'job_type' => $this->jobType,
                'user_id' => $this->userId,
                'additional_data' => $this->additionalData
            ]);

            $result = match ($this->jobType) {
                'process_single_upgrade' => $this->processSingleTierUpgrade($tierService),
                'batch_process_upgrades' => $this->batchProcessTierUpgrades($tierService),
                'recalculate_benefits' => $this->recalculateTierBenefits($tierService),
                default => throw new Exception("Invalid job type: {$this->jobType}")
            };

            if ($result['success']) {
                $this->sendSuccessNotifications($result);
                Log::info("Successfully completed {$this->jobType} processing", $result);
            } else {
                throw new Exception($result['error'] ?? 'Unknown error occurred during processing');
            }

        } catch (Exception $e) {
            Log::error("Failed to process {$this->jobType} tier upgrades", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->userId
            ]);

            $this->sendFailureNotifications($e);
            throw $e;
        }
    }

    /**
     * Process tier upgrade for a single user
     */
    protected function processSingleTierUpgrade(InvestmentTierService $tierService): array
    {
        if (!$this->userId) {
            throw new Exception('User ID is required for processing single tier upgrade');
        }

        // Load user with minimal data to avoid memory issues
        $user = User::select(['id', 'name', 'email', 'current_investment_tier_id', 'total_investment_amount', 'tier_upgraded_at', 'tier_history'])
            ->with(['currentInvestmentTier:id,name,minimum_investment,fixed_profit_rate'])
            ->find($this->userId);
        
        if (!$user) {
            throw new Exception("User not found: {$this->userId}");
        }

        try {
            DB::beginTransaction();

            $upgradeResult = $this->processUserTierUpgrade($user, $tierService);

            DB::commit();

            return [
                'success' => true,
                'user_id' => $this->userId,
                'upgrade_processed' => $upgradeResult['upgraded'],
                'from_tier' => $upgradeResult['from_tier'],
                'to_tier' => $upgradeResult['to_tier'],
                'total_investment' => $upgradeResult['total_investment'],
                'benefits_recalculated' => $upgradeResult['benefits_recalculated']
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Batch process tier upgrades for multiple users
     */
    protected function batchProcessTierUpgrades(InvestmentTierService $tierService): array
    {
        $batchSize = $this->additionalData['batch_size'] ?? 100;
        $maxAge = $this->additionalData['max_age_days'] ?? 1; // Check users with investments in last day
        
        $cutoffDate = Carbon::now()->subDays($maxAge);
        
        // Get users who might be eligible for tier upgrades with minimal data
        $eligibleUsers = User::select(['id', 'name', 'email', 'current_investment_tier_id', 'total_investment_amount', 'tier_upgraded_at', 'tier_history'])
            ->with(['currentInvestmentTier:id,name,minimum_investment,fixed_profit_rate'])
            ->whereHas('investments', function ($query) use ($cutoffDate) {
                $query->where('created_at', '>=', $cutoffDate);
            })
            ->limit($batchSize)
            ->get();

        if ($eligibleUsers->isEmpty()) {
            return [
                'success' => true,
                'processed_count' => 0,
                'upgraded_count' => 0,
                'message' => 'No users eligible for tier upgrades'
            ];
        }

        try {
            DB::beginTransaction();

            $processedCount = 0;
            $upgradedCount = 0;
            $upgradedUsers = [];

            foreach ($eligibleUsers as $user) {
                $upgradeResult = $this->processUserTierUpgrade($user, $tierService);
                
                $processedCount++;
                
                if ($upgradeResult['upgraded']) {
                    $upgradedCount++;
                    $upgradedUsers[] = [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'from_tier' => $upgradeResult['from_tier'],
                        'to_tier' => $upgradeResult['to_tier'],
                        'total_investment' => $upgradeResult['total_investment']
                    ];
                }
            }

            DB::commit();

            return [
                'success' => true,
                'processed_count' => $processedCount,
                'upgraded_count' => $upgradedCount,
                'upgraded_users' => $upgradedUsers
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Recalculate tier benefits for upgraded users
     */
    protected function recalculateTierBenefits(InvestmentTierService $tierService): array
    {
        $userId = $this->userId;
        $recalculateAll = $this->additionalData['recalculate_all'] ?? false;
        
        if ($recalculateAll) {
            // Recalculate for all users who had tier upgrades in the last 30 days
            $recentUpgrades = TierUpgrade::where('created_at', '>=', Carbon::now()->subDays(30))
                ->with(['user:id,name,email,current_investment_tier_id,total_investment_amount'])
                ->get();
                
            $users = $recentUpgrades->pluck('user')->unique('id');
        } else {
            if (!$userId) {
                throw new Exception('User ID is required for single user benefit recalculation');
            }
            
            $user = User::find($userId);
            if (!$user) {
                throw new Exception("User not found: {$userId}");
            }
            
            $users = collect([$user]);
        }

        try {
            DB::beginTransaction();

            $recalculatedCount = 0;
            $recalculatedUsers = [];

            foreach ($users as $user) {
                $benefitsResult = $this->recalculateUserBenefits($user, $tierService);
                
                if ($benefitsResult['recalculated']) {
                    $recalculatedCount++;
                    $recalculatedUsers[] = [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'tier' => $benefitsResult['tier'],
                        'updated_benefits' => $benefitsResult['benefits']
                    ];
                }
            }

            DB::commit();

            return [
                'success' => true,
                'recalculated_count' => $recalculatedCount,
                'recalculated_users' => $recalculatedUsers
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
/**
     * Process tier upgrade for a specific user
     */
    protected function processUserTierUpgrade(User $user, InvestmentTierService $tierService): array
    {
        $currentTier = $user->currentInvestmentTier;
        $totalInvestment = $user->investments()->sum('amount');
        
        // Check if user is eligible for tier upgrade
        $eligibleTier = $tierService->calculateTierForAmount($totalInvestment);
        
        if (!$currentTier || !$eligibleTier || $eligibleTier->id === $currentTier->id) {
            return [
                'upgraded' => false,
                'from_tier' => $currentTier?->name ?? 'None',
                'to_tier' => $eligibleTier?->name ?? 'None',
                'total_investment' => $totalInvestment,
                'benefits_recalculated' => false
            ];
        }

        // Process the tier upgrade
        $upgradeSuccess = $tierService->upgradeTierIfEligible($user);
        
        if (!$upgradeSuccess) {
            return [
                'upgraded' => false,
                'from_tier' => $currentTier->name,
                'to_tier' => $eligibleTier->name,
                'total_investment' => $totalInvestment,
                'benefits_recalculated' => false
            ];
        }

        // Create tier upgrade history record
        TierUpgrade::create([
            'user_id' => $user->id,
            'from_tier_id' => $currentTier->id,
            'to_tier_id' => $eligibleTier->id,
            'total_investment_amount' => $totalInvestment,
            'upgrade_reason' => 'automatic_investment_threshold',
            'processed_at' => now()
        ]);

        // Update user's tier history
        $tierHistory = $user->tier_history ?? [];
        $tierHistory[] = [
            'from_tier' => $currentTier->name,
            'to_tier' => $eligibleTier->name,
            'upgraded_at' => now()->toISOString(),
            'total_investment' => $totalInvestment,
            'reason' => 'automatic_investment_threshold'
        ];
        
        $user->update([
            'tier_upgraded_at' => now(),
            'tier_history' => $tierHistory
        ]);

        // Recalculate benefits
        $benefitsResult = $this->recalculateUserBenefits($user, $tierService);

        // Record activity
        $user->recordActivity(
            'tier_upgraded',
            "Tier upgraded from {$currentTier->name} to {$eligibleTier->name} (Total Investment: K{$totalInvestment})"
        );

        return [
            'upgraded' => true,
            'from_tier' => $currentTier->name,
            'to_tier' => $eligibleTier->name,
            'total_investment' => $totalInvestment,
            'benefits_recalculated' => $benefitsResult['recalculated']
        ];
    }

    /**
     * Recalculate user benefits after tier upgrade
     */
    protected function recalculateUserBenefits(User $user, InvestmentTierService $tierService): array
    {
        $user->refresh(); // Ensure we have the latest tier information
        $currentTier = $user->currentInvestmentTier;
        
        if (!$currentTier) {
            return [
                'recalculated' => false,
                'tier' => 'None',
                'benefits' => []
            ];
        }

        // Get tier benefits
        $benefits = $tierService->getTierBenefits($currentTier);
        
        // Update user's cached benefit calculations
        $updatedBenefits = [
            'profit_share_rate' => $benefits['profit_share_rate'],
            'direct_referral_rate' => $benefits['direct_referral_rate'],
            'level_2_referral_rate' => $benefits['level_2_referral_rate'] ?? 0,
            'level_3_referral_rate' => $benefits['level_3_referral_rate'] ?? 0,
            'matrix_commission_rate' => $benefits['matrix_commission_rate'] ?? 0,
            'reinvestment_bonus_rate' => $benefits['reinvestment_bonus_rate'] ?? 0,
            'updated_at' => now()->toISOString()
        ];

        // Note: User benefit cache could be implemented in the future
        // For now, benefits are calculated on-demand from the tier relationship

        return [
            'recalculated' => true,
            'tier' => $currentTier->name,
            'benefits' => $updatedBenefits
        ];
    }

    /**
     * Send success notifications
     */
    protected function sendSuccessNotifications(array $result): void
    {
        try {
            match ($this->jobType) {
                'process_single_upgrade' => $this->notifySingleUpgradeSuccess($result),
                'batch_process_upgrades' => $this->notifyBatchUpgradeSuccess($result),
                'recalculate_benefits' => $this->notifyBenefitRecalculationSuccess($result),
            };
        } catch (Exception $e) {
            Log::warning("Failed to send success notifications for {$this->jobType}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify about single tier upgrade success
     */
    protected function notifySingleUpgradeSuccess(array $result): void
    {
        if (!$result['upgrade_processed']) {
            return;
        }

        $user = User::find($result['user_id']);
        if ($user) {
            $user->notify(new TierUpgradeNotification([
                'type' => 'tier_upgraded',
                'from_tier' => $result['from_tier'],
                'to_tier' => $result['to_tier'],
                'total_investment' => $result['total_investment'],
                'benefits_recalculated' => $result['benefits_recalculated']
            ]));
        }
    }

    /**
     * Notify about batch upgrade success
     */
    protected function notifyBatchUpgradeSuccess(array $result): void
    {
        // Notify each upgraded user
        foreach ($result['upgraded_users'] as $upgradedUser) {
            $user = User::find($upgradedUser['user_id']);
            if ($user) {
                $user->notify(new TierUpgradeNotification([
                    'type' => 'tier_upgraded',
                    'from_tier' => $upgradedUser['from_tier'],
                    'to_tier' => $upgradedUser['to_tier'],
                    'total_investment' => $upgradedUser['total_investment'],
                    'benefits_recalculated' => true
                ]));
            }
        }

        // Notify admins about batch completion
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new TierUpgradeNotification([
                'type' => 'batch_upgrade_complete',
                'processed_count' => $result['processed_count'],
                'upgraded_count' => $result['upgraded_count']
            ]));
        }
    }

    /**
     * Notify about benefit recalculation success
     */
    protected function notifyBenefitRecalculationSuccess(array $result): void
    {
        // Notify admins about benefit recalculation completion
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new TierUpgradeNotification([
                'type' => 'benefit_recalculation_complete',
                'recalculated_count' => $result['recalculated_count']
            ]));
        }
    }

    /**
     * Send failure notifications
     */
    protected function sendFailureNotifications(Exception $exception): void
    {
        try {
            $admins = User::role('admin')->get();
            
            foreach ($admins as $admin) {
                $admin->notify(new TierUpgradeNotification([
                    'type' => 'processing_failure',
                    'job_type' => $this->jobType,
                    'error_message' => $exception->getMessage(),
                    'user_id' => $this->userId
                ]));
            }
        } catch (Exception $e) {
            Log::error("Failed to send failure notifications for {$this->jobType}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle job failure
     */
    public function failed(Exception $exception): void
    {
        Log::critical("TierUpgradeJob failed permanently", [
            'job_type' => $this->jobType,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // Send critical failure notification
        try {
            $admins = User::role('admin')->get();
            
            foreach ($admins as $admin) {
                $admin->notify(new TierUpgradeNotification([
                    'type' => 'critical_failure',
                    'job_type' => $this->jobType,
                    'error_message' => $exception->getMessage(),
                    'user_id' => $this->userId,
                    'attempts' => $this->attempts()
                ]));
            }
        } catch (Exception $e) {
            Log::error("Failed to send critical failure notification", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        $tags = ['tier-upgrade', $this->jobType];
        
        if ($this->userId) {
            $tags[] = "user:{$this->userId}";
        }
        
        return $tags;
    }
}