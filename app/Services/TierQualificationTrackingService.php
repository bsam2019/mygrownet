<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\TeamVolume;
use App\Models\TierQualification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TierQualificationTrackingService
{
    /**
     * Process monthly tier qualifications for all users
     */
    public function processMonthlyQualifications(?Carbon $month = null): array
    {
        $month = $month ?? now()->startOfMonth();
        $processed = [];
        $failed = [];

        $users = User::with(['membershipTier', 'teamVolumes'])
            ->whereNotNull('current_investment_tier_id')
            ->where('status', 'active')
            ->get();

        foreach ($users as $user) {
            try {
                $result = $this->processUserMonthlyQualification($user, $month);
                $processed[] = [
                    'user_id' => $user->id,
                    'tier' => $result['tier'],
                    'qualifies' => $result['qualifies'],
                    'consecutive_months' => $result['consecutive_months'],
                    'permanent_status' => $result['permanent_status']
                ];
            } catch (\Exception $e) {
                $failed[] = [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ];
                Log::error('Monthly qualification processing failed for user ' . $user->id, [
                    'error' => $e->getMessage(),
                    'month' => $month->toDateString()
                ]);
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
            'month' => $month->toDateString(),
            'total_processed' => count($processed),
            'total_failed' => count($failed)
        ];
    }

    /**
     * Process monthly qualification for a specific user
     */
    public function processUserMonthlyQualification(User $user, Carbon $month): array
    {
        $currentTier = $user->membershipTier;
        if (!$currentTier) {
            throw new \Exception('User has no current tier');
        }

        // Get team volume for the specified month
        $teamVolume = TeamVolume::where('user_id', $user->id)
            ->where('period_start', '<=', $month)
            ->where('period_end', '>=', $month->copy()->endOfMonth())
            ->first();

        $activeReferrals = $teamVolume?->active_referrals_count ?? 0;
        $volume = $teamVolume?->team_volume ?? 0;

        // Create or update tier qualification record
        $qualification = TierQualification::updateOrCreate([
            'user_id' => $user->id,
            'tier_id' => $currentTier->id,
            'qualification_month' => $month
        ], [
            'team_volume' => $volume,
            'active_referrals' => $activeReferrals,
            'required_team_volume' => $currentTier->required_team_volume,
            'required_active_referrals' => $currentTier->required_active_referrals
        ]);

        // Update qualification status
        $qualification->updateQualificationStatus();
        $qualification->updateConsecutiveMonths();

        return [
            'tier' => $currentTier->name,
            'qualifies' => $qualification->qualifies,
            'consecutive_months' => $qualification->consecutive_months,
            'permanent_status' => $qualification->permanent_status,
            'team_volume' => $volume,
            'active_referrals' => $activeReferrals
        ];
    }

    /**
     * Get qualification history for a user
     */
    public function getUserQualificationHistory(User $user, int $months = 12): Collection
    {
        return TierQualification::where('user_id', $user->id)
            ->with('tier')
            ->where('qualification_month', '>=', now()->subMonths($months)->startOfMonth())
            ->orderBy('qualification_month', 'desc')
            ->get();
    }

    /**
     * Get users with permanent tier status
     */
    public function getUsersWithPermanentStatus(?InvestmentTier $tier = null): Collection
    {
        $query = TierQualification::permanentStatus()
            ->with(['user', 'tier']);

        if ($tier) {
            $query->forTier($tier->id);
        }

        return $query->get()->unique('user_id');
    }

    /**
     * Get qualification statistics for a tier
     */
    public function getTierQualificationStats(InvestmentTier $tier, Carbon $month): array
    {
        $qualifications = TierQualification::forTier($tier->id)
            ->forMonth($month)
            ->get();

        $totalUsers = $qualifications->count();
        $qualifiedUsers = $qualifications->where('qualifies', true)->count();
        $permanentStatusUsers = $qualifications->where('permanent_status', true)->count();

        $avgTeamVolume = $qualifications->avg('team_volume');
        $avgActiveReferrals = $qualifications->avg('active_referrals');

        return [
            'tier_name' => $tier->name,
            'month' => $month->format('Y-m'),
            'total_users' => $totalUsers,
            'qualified_users' => $qualifiedUsers,
            'qualification_rate' => $totalUsers > 0 ? round(($qualifiedUsers / $totalUsers) * 100, 2) : 0,
            'permanent_status_users' => $permanentStatusUsers,
            'average_team_volume' => round($avgTeamVolume, 2),
            'average_active_referrals' => round($avgActiveReferrals, 2),
            'requirements' => [
                'required_team_volume' => $tier->required_team_volume,
                'required_active_referrals' => $tier->required_active_referrals,
                'consecutive_months_required' => $tier->consecutive_months_required
            ]
        ];
    }

    /**
     * Get users at risk of losing tier status
     */
    public function getUsersAtRisk(InvestmentTier $tier, int $gracePeriodMonths = 2): Collection
    {
        $currentMonth = now()->startOfMonth();
        $riskThreshold = $currentMonth->copy()->subMonths($gracePeriodMonths);

        return TierQualification::forTier($tier->id)
            ->where('qualification_month', '>=', $riskThreshold)
            ->where('qualifies', false)
            ->with(['user', 'tier'])
            ->get()
            ->groupBy('user_id')
            ->filter(function ($qualifications) use ($gracePeriodMonths) {
                // Users who haven't qualified for the grace period
                return $qualifications->count() >= $gracePeriodMonths;
            })
            ->map(function ($qualifications) {
                return $qualifications->first()->user;
            });
    }

    /**
     * Get qualification trends for a tier
     */
    public function getQualificationTrends(InvestmentTier $tier, int $months = 6): array
    {
        $trends = [];
        $currentMonth = now()->startOfMonth();

        for ($i = 0; $i < $months; $i++) {
            $month = $currentMonth->copy()->subMonths($i);
            $stats = $this->getTierQualificationStats($tier, $month);
            $trends[] = $stats;
        }

        return array_reverse($trends);
    }

    /**
     * Process tier advancement based on qualifications
     */
    public function processQualificationBasedAdvancements(): array
    {
        $advancements = [];
        $failed = [];

        // Get users ready for advancement based on consecutive qualifications
        $readyUsers = $this->getUsersReadyForAdvancement();

        foreach ($readyUsers as $user) {
            try {
                $currentTier = $user->membershipTier;
                $nextTier = $currentTier->getNextTier();

                if ($nextTier) {
                    $advancementService = new MyGrowNetTierAdvancementService();
                    $result = $advancementService->checkAndProcessUserTierUpgrade($user);

                    if ($result['upgraded']) {
                        $advancements[] = [
                            'user_id' => $user->id,
                            'from_tier' => $result['from_tier'],
                            'to_tier' => $result['to_tier'],
                            'achievement_bonus' => $result['achievement_bonus']
                        ];
                    }
                }
            } catch (\Exception $e) {
                $failed[] = [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'advancements' => $advancements,
            'failed' => $failed,
            'total_advanced' => count($advancements),
            'total_failed' => count($failed)
        ];
    }

    /**
     * Get users ready for tier advancement
     */
    private function getUsersReadyForAdvancement(): Collection
    {
        return User::with(['membershipTier'])
            ->whereNotNull('current_investment_tier_id')
            ->where('status', 'active')
            ->get()
            ->filter(function ($user) {
                $currentTier = $user->membershipTier;
                $nextTier = $currentTier?->getNextTier();

                if (!$nextTier) {
                    return false; // Already at highest tier
                }

                // Check if user has consecutive qualifications for next tier
                $currentMonth = now()->startOfMonth();
                $requiredMonths = $nextTier->consecutive_months_required;

                $qualifications = TierQualification::where('user_id', $user->id)
                    ->where('tier_id', $nextTier->id)
                    ->where('qualification_month', '>=', $currentMonth->copy()->subMonths($requiredMonths))
                    ->where('qualifies', true)
                    ->count();

                return $qualifications >= $requiredMonths;
            });
    }

    /**
     * Get consecutive months at current tier for a user
     */
    public function getConsecutiveMonthsAtTier(User $user, int $tierId): int
    {
        $qualification = TierQualification::where('user_id', $user->id)
            ->where('tier_id', $tierId)
            ->where('qualifies', true)
            ->orderBy('qualification_month', 'desc')
            ->first();

        return $qualification?->consecutive_months ?? 0;
    }

    /**
     * Backfill qualification history for existing users
     */
    public function backfillQualificationHistory(int $months = 12): array
    {
        $processed = [];
        $failed = [];
        $currentMonth = now()->startOfMonth();

        for ($i = 0; $i < $months; $i++) {
            $month = $currentMonth->copy()->subMonths($i);
            
            try {
                $result = $this->processMonthlyQualifications($month);
                $processed[] = [
                    'month' => $month->format('Y-m'),
                    'processed' => $result['total_processed'],
                    'failed' => $result['total_failed']
                ];
            } catch (\Exception $e) {
                $failed[] = [
                    'month' => $month->format('Y-m'),
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
            'total_months_processed' => count($processed),
            'total_months_failed' => count($failed)
        ];
    }
}