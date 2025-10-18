<?php

namespace App\Application\Services;

use App\Application\UseCases\MLM\ProcessTierAdvancementUseCase;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\TierQualification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TierAdvancementService
{
    public function __construct(
        private ProcessTierAdvancementUseCase $processTierAdvancementUseCase
    ) {}

    /**
     * Process tier advancement for a specific user
     */
    public function processUserTierAdvancement(int $userId): array
    {
        return $this->processTierAdvancementUseCase->execute($userId);
    }

    /**
     * Process tier advancement for all eligible users
     */
    public function processAllEligibleUsers(): array
    {
        $results = [
            'processed' => 0,
            'advanced' => 0,
            'failed' => 0,
            'advancements' => []
        ];

        $eligibleUsers = $this->getEligibleUsers();

        foreach ($eligibleUsers as $user) {
            try {
                $result = $this->processTierAdvancementUseCase->execute($user->id);
                $results['processed']++;

                if ($result && $result['qualified'] && $result['upgraded']) {
                    $results['advanced']++;
                    $results['advancements'][] = [
                        'user_id' => $user->id,
                        'old_tier' => $result['old_tier'],
                        'new_tier' => $result['new_tier'],
                        'achievement_bonus' => $result['achievement_bonus']
                    ];
                }
            } catch (\Exception $e) {
                $results['failed']++;
                Log::error("Tier advancement failed for user", [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Process monthly tier maintenance
     */
    public function processMonthlyTierMaintenance(): array
    {
        $results = [
            'maintained' => 0,
            'downgraded' => 0,
            'consecutive_updated' => 0
        ];

        $qualifications = TierQualification::with(['user', 'tier'])
            ->whereMonth('created_at', now()->subMonth()->month)
            ->get();

        foreach ($qualifications as $qualification) {
            try {
                $user = $qualification->user;
                $currentStats = $this->getCurrentUserStats($user);
                $tierRequirements = $this->getTierRequirements($qualification->tier->name);

                if ($this->meetsTierRequirements($currentStats, $tierRequirements)) {
                    // User maintains tier - increment consecutive months
                    $qualification->increment('consecutive_months');
                    $results['maintained']++;
                    $results['consecutive_updated']++;

                    // Check for permanent tier status (3+ consecutive months)
                    if ($qualification->consecutive_months >= 3) {
                        $this->grantPermanentTierStatus($user, $qualification);
                    }
                } else {
                    // User doesn't meet requirements - consider downgrade
                    $this->processTierDowngrade($user, $qualification);
                    $results['downgraded']++;
                }
            } catch (\Exception $e) {
                Log::error("Tier maintenance failed", [
                    'qualification_id' => $qualification->id,
                    'user_id' => $qualification->user_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Get tier advancement progress for a user
     */
    public function getTierAdvancementProgress(int $userId): array
    {
        $user = User::with('investmentTier')->findOrFail($userId);
        $currentTier = $user->investmentTier;
        
        if (!$currentTier) {
            return ['error' => 'User has no current tier'];
        }

        $nextTier = $this->getNextTier($currentTier);
        if (!$nextTier) {
            return [
                'current_tier' => $currentTier->name,
                'is_highest_tier' => true,
                'message' => 'User is at the highest tier'
            ];
        }

        $currentStats = $this->getCurrentUserStats($user);
        $requirements = $this->getTierRequirements($nextTier->name);

        return [
            'current_tier' => $currentTier->name,
            'next_tier' => $nextTier->name,
            'current_stats' => $currentStats,
            'requirements' => $requirements,
            'progress' => [
                'referrals' => [
                    'current' => $currentStats['active_referrals'],
                    'required' => $requirements['active_referrals'],
                    'percentage' => min(100, ($currentStats['active_referrals'] / $requirements['active_referrals']) * 100)
                ],
                'team_volume' => [
                    'current' => $currentStats['team_volume'],
                    'required' => $requirements['team_volume'],
                    'percentage' => min(100, ($currentStats['team_volume'] / $requirements['team_volume']) * 100)
                ]
            ],
            'qualifies' => $this->meetsTierRequirements($currentStats, $requirements)
        ];
    }

    /**
     * Get users eligible for tier advancement
     */
    private function getEligibleUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereHas('investmentTier')
            ->where('monthly_team_volume', '>', 0)
            ->whereHas('referrals', function ($query) {
                $query->whereHas('investmentTier'); // Has active referrals
            })
            ->get();
    }

    /**
     * Get next tier for advancement
     */
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

    /**
     * Get current user statistics
     */
    private function getCurrentUserStats(User $user): array
    {
        return [
            'active_referrals' => $user->activeReferrals()->count(),
            'team_volume' => $user->monthly_team_volume ?? 0,
            'consecutive_months' => $this->getConsecutiveMonthsAtTier($user)
        ];
    }

    /**
     * Get tier requirements
     */
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
        ];
    }

    /**
     * Check if user meets tier requirements
     */
    private function meetsTierRequirements(array $currentStats, array $requirements): bool
    {
        return $currentStats['active_referrals'] >= $requirements['active_referrals'] &&
               $currentStats['team_volume'] >= $requirements['team_volume'];
    }

    /**
     * Get consecutive months at current tier
     */
    private function getConsecutiveMonthsAtTier(User $user): int
    {
        $qualification = TierQualification::where('user_id', $user->id)
            ->where('tier_id', $user->investment_tier_id)
            ->latest()
            ->first();

        return $qualification ? $qualification->consecutive_months : 0;
    }

    /**
     * Grant permanent tier status
     */
    private function grantPermanentTierStatus(User $user, TierQualification $qualification): void
    {
        $qualification->update(['is_permanent' => true]);
        
        Log::info("Permanent tier status granted", [
            'user_id' => $user->id,
            'tier_id' => $qualification->tier_id,
            'consecutive_months' => $qualification->consecutive_months
        ]);
    }

    /**
     * Process tier downgrade
     */
    private function processTierDowngrade(User $user, TierQualification $qualification): void
    {
        // Only downgrade if not permanent status
        if ($qualification->is_permanent) {
            return;
        }

        $previousTier = $this->getPreviousTier($qualification->tier);
        if ($previousTier) {
            $user->update(['investment_tier_id' => $previousTier->id]);
            
            Log::info("Tier downgrade processed", [
                'user_id' => $user->id,
                'old_tier' => $qualification->tier->name,
                'new_tier' => $previousTier->name
            ]);
        }
    }

    /**
     * Get previous tier for downgrade
     */
    private function getPreviousTier(InvestmentTier $currentTier): ?InvestmentTier
    {
        $tierOrder = [
            'Silver Member' => 'Bronze Member',
            'Gold Member' => 'Silver Member',
            'Diamond Member' => 'Gold Member',
            'Elite Member' => 'Diamond Member'
        ];

        $previousTierName = $tierOrder[$currentTier->name] ?? null;
        
        return $previousTierName ? InvestmentTier::where('name', $previousTierName)->first() : null;
    }
}