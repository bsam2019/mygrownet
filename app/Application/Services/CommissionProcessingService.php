<?php

namespace App\Application\Services;

use App\Application\UseCases\MLM\ProcessCommissionsUseCase;
use App\Application\UseCases\MLM\ProcessTierAdvancementUseCase;
use App\Models\User;
use App\Models\ReferralCommission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionProcessingService
{
    public function __construct(
        private ProcessCommissionsUseCase $processCommissionsUseCase,
        private ProcessTierAdvancementUseCase $processTierAdvancementUseCase
    ) {}

    /**
     * Process commissions for a package purchase
     */
    public function processPackagePurchaseCommissions(
        int $userId, 
        float $packageAmount, 
        string $packageType = 'membership'
    ): array {
        try {
            $commissions = $this->processCommissionsUseCase->execute(
                $userId, 
                $packageAmount, 
                $packageType
            );

            // Check for tier advancement after processing commissions
            $this->checkTierAdvancementForUpline($userId);

            return [
                'success' => true,
                'commissions_created' => count($commissions),
                'total_commission_amount' => array_sum(array_column($commissions->toArray(), 'amount')),
                'commissions' => $commissions
            ];
        } catch (\Exception $e) {
            Log::error("Commission processing failed", [
                'user_id' => $userId,
                'package_amount' => $packageAmount,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process monthly team volume bonuses
     */
    public function processMonthlyTeamVolumeBonuses(): array
    {
        $results = [];
        
        // Get users with team volume bonuses
        $eligibleUsers = User::whereHas('investmentTier')
            ->where('monthly_team_volume', '>', 0)
            ->get();

        foreach ($eligibleUsers as $user) {
            try {
                $bonus = $this->calculateTeamVolumeBonus($user);
                
                if ($bonus > 0) {
                    $commission = $this->createTeamVolumeBonus($user, $bonus);
                    $results[] = [
                        'user_id' => $user->id,
                        'bonus_amount' => $bonus,
                        'commission_id' => $commission->id
                    ];
                }
            } catch (\Exception $e) {
                Log::error("Team volume bonus processing failed", [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Process pending commission payments
     */
    public function processPendingPayments(): array
    {
        $pendingCommissions = ReferralCommission::where('status', 'pending')
            ->where('earned_at', '<=', now()->subHours(24))
            ->get();

        $results = [
            'processed' => 0,
            'failed' => 0,
            'total_amount' => 0
        ];

        foreach ($pendingCommissions as $commission) {
            try {
                // In a real implementation, this would integrate with payment gateway
                $commission->update([
                    'status' => 'paid',
                    'paid_at' => now()
                ]);

                $results['processed']++;
                $results['total_amount'] += $commission->amount;

                Log::info("Commission payment processed", [
                    'commission_id' => $commission->id,
                    'user_id' => $commission->user_id,
                    'amount' => $commission->amount
                ]);
            } catch (\Exception $e) {
                $results['failed']++;
                Log::error("Commission payment failed", [
                    'commission_id' => $commission->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Calculate team volume bonus for a user
     */
    private function calculateTeamVolumeBonus(User $user): float
    {
        $tier = $user->investmentTier;
        if (!$tier) return 0;

        $bonusRate = $this->getTeamVolumeBonusRate($tier->name);
        $teamVolume = $user->monthly_team_volume ?? 0;

        return $teamVolume * ($bonusRate / 100);
    }

    /**
     * Get team volume bonus rate for tier
     */
    private function getTeamVolumeBonusRate(string $tierName): float
    {
        return match ($tierName) {
            'Silver Member' => 2.0,
            'Gold Member' => 5.0,
            'Diamond Member' => 7.0,
            'Elite Member' => 10.0,
            default => 0.0
        };
    }

    /**
     * Create team volume bonus commission
     */
    private function createTeamVolumeBonus(User $user, float $bonusAmount): ReferralCommission
    {
        return ReferralCommission::create([
            'user_id' => $user->id,
            'referred_user_id' => $user->id,
            'level' => 0,
            'amount' => $bonusAmount,
            'commission_type' => 'TEAM_VOLUME',
            'status' => 'pending',
            'earned_at' => now(),
        ]);
    }

    /**
     * Check tier advancement for upline members
     */
    private function checkTierAdvancementForUpline(int $userId): void
    {
        $user = User::find($userId);
        if (!$user) return;

        // Check advancement for the user and their upline (up to 5 levels)
        $currentUser = $user;
        for ($level = 0; $level < 5; $level++) {
            if ($currentUser) {
                $this->processTierAdvancementUseCase->execute($currentUser->id);
                $currentUser = $currentUser->referrer;
            }
        }
    }

    /**
     * Get commission statistics for a user
     */
    public function getCommissionStats(int $userId): array
    {
        $user = User::findOrFail($userId);
        
        return [
            'total_earned' => $user->referralCommissions()->where('status', 'paid')->sum('amount'),
            'pending_amount' => $user->referralCommissions()->where('status', 'pending')->sum('amount'),
            'this_month' => $user->referralCommissions()
                ->where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->sum('amount'),
            'commission_breakdown' => $user->referralCommissions()
                ->selectRaw('commission_type, SUM(amount) as total')
                ->where('status', 'paid')
                ->groupBy('commission_type')
                ->pluck('total', 'commission_type')
                ->toArray(),
            'level_breakdown' => $user->referralCommissions()
                ->selectRaw('level, SUM(amount) as total')
                ->where('status', 'paid')
                ->groupBy('level')
                ->pluck('total', 'level')
                ->toArray()
        ];
    }
}