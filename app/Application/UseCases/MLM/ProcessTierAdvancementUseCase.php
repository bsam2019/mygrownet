<?php

namespace App\Application\UseCases\MLM;

use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\TierQualification;
use App\Services\MyGrowNetTierAdvancementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessTierAdvancementUseCase
{
    public function __construct(
        private MyGrowNetTierAdvancementService $tierAdvancementService
    ) {}

    public function execute(int $userId): ?array
    {
        return DB::transaction(function () use ($userId) {
            $user = User::findOrFail($userId);
            $currentTier = $user->investmentTier;
            
            if (!$currentTier) {
                Log::warning("User has no current tier", ['user_id' => $userId]);
                return null;
            }

            // Check if user qualifies for tier advancement
            $nextTier = $this->getNextTier($currentTier);
            if (!$nextTier) {
                return null; // Already at highest tier
            }

            $qualificationResult = $this->checkTierQualification($user, $nextTier);
            
            if ($qualificationResult['qualifies']) {
                return $this->processTierUpgrade($user, $nextTier, $qualificationResult);
            }

            return [
                'qualified' => false,
                'current_tier' => $currentTier->name,
                'next_tier' => $nextTier->name,
                'requirements' => $qualificationResult['requirements'],
                'current_status' => $qualificationResult['current_status']
            ];
        });
    }

    private function getNextTier(InvestmentTier $currentTier): ?InvestmentTier
    {
        $tierOrder = [
            'Bronze Member' => 'Silver Member',
            'Silver Member' => 'Gold Member', 
            'Gold Member' => 'Diamond Member',
            'Diamond Member' => 'Elite Member'
        ];

        $nextTierName = $tierOrder[$currentTier->name] ?? null;
        
        return $nextTierName ? InvestmentTier::where('name', $nextTierName)->first() : null;
    }

    private function checkTierQualification(User $user, InvestmentTier $targetTier): array
    {
        $requirements = $this->getTierRequirements($targetTier->name);
        $currentStats = $this->getCurrentUserStats($user);

        $qualifies = $currentStats['active_referrals'] >= $requirements['active_referrals'] &&
                    $currentStats['team_volume'] >= $requirements['team_volume'];

        return [
            'qualifies' => $qualifies,
            'requirements' => $requirements,
            'current_status' => $currentStats
        ];
    }

    private function getTierRequirements(string $tierName): array
    {
        return match ($tierName) {
            'Silver Member' => [
                'active_referrals' => 3,
                'team_volume' => 5000,
                'achievement_bonus' => 500,
                'team_volume_bonus' => 2
            ],
            'Gold Member' => [
                'active_referrals' => 10,
                'team_volume' => 15000,
                'achievement_bonus' => 2000,
                'team_volume_bonus' => 5
            ],
            'Diamond Member' => [
                'active_referrals' => 25,
                'team_volume' => 50000,
                'achievement_bonus' => 5000,
                'team_volume_bonus' => 7
            ],
            'Elite Member' => [
                'active_referrals' => 50,
                'team_volume' => 150000,
                'achievement_bonus' => 10000,
                'team_volume_bonus' => 10
            ],
            default => []
        };
    }

    private function getCurrentUserStats(User $user): array
    {
        return [
            'active_referrals' => $user->activeReferrals()->count(),
            'team_volume' => $user->monthly_team_volume ?? 0,
            'consecutive_months' => $this->getConsecutiveMonthsAtTier($user)
        ];
    }

    private function getConsecutiveMonthsAtTier(User $user): int
    {
        $qualification = TierQualification::where('user_id', $user->id)
            ->where('tier_id', $user->investment_tier_id)
            ->latest()
            ->first();

        return $qualification ? $qualification->consecutive_months : 0;
    }

    private function processTierUpgrade(User $user, InvestmentTier $newTier, array $qualificationData): array
    {
        $oldTier = $user->investmentTier;
        
        // Update user tier
        $user->update([
            'investment_tier_id' => $newTier->id
        ]);

        // Record tier qualification
        TierQualification::create([
            'user_id' => $user->id,
            'tier_id' => $newTier->id,
            'active_referrals' => $qualificationData['current_status']['active_referrals'],
            'team_volume' => $qualificationData['current_status']['team_volume'],
            'consecutive_months' => 1,
            'qualified_at' => now()
        ]);

        // Award achievement bonus
        $this->awardAchievementBonus($user, $newTier);

        Log::info("Tier advancement processed", [
            'user_id' => $user->id,
            'old_tier' => $oldTier->name,
            'new_tier' => $newTier->name,
            'achievement_bonus' => $qualificationData['requirements']['achievement_bonus']
        ]);

        return [
            'qualified' => true,
            'upgraded' => true,
            'old_tier' => $oldTier->name,
            'new_tier' => $newTier->name,
            'achievement_bonus' => $qualificationData['requirements']['achievement_bonus'],
            'new_team_volume_bonus' => $qualificationData['requirements']['team_volume_bonus']
        ];
    }

    private function awardAchievementBonus(User $user, InvestmentTier $tier): void
    {
        $requirements = $this->getTierRequirements($tier->name);
        $bonusAmount = $requirements['achievement_bonus'] ?? 0;

        if ($bonusAmount > 0) {
            // Create achievement bonus commission
            $user->referralCommissions()->create([
                'referred_user_id' => $user->id,
                'level' => 0, // Achievement bonus
                'amount' => $bonusAmount,
                'commission_type' => 'ACHIEVEMENT',
                'status' => 'pending',
                'earned_at' => now(),
            ]);
        }
    }
}