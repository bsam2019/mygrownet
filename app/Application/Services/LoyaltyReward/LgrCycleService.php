<?php

namespace App\Application\Services\LoyaltyReward;

use App\Infrastructure\Persistence\Eloquent\LoyaltyReward\LgrCycleModel;
use App\Infrastructure\Persistence\Eloquent\LoyaltyReward\LgrActivityModel;
use App\Domain\LoyaltyReward\Entities\LgrCycle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LgrCycleService
{
    public function __construct(
        private LgrQualificationService $qualificationService
    ) {}

    public function getActiveCycle(int $userId): ?LgrCycle
    {
        $model = LgrCycleModel::where('user_id', $userId)
            ->where('status', 'active')
            ->first();

        return $model ? LgrCycle::fromArray($model->toArray()) : null;
    }

    public function startCycle(int $userId): LgrCycle
    {
        // Check if user is qualified
        if (!$this->qualificationService->isUserQualified($userId)) {
            throw new \Exception('User does not meet LGR qualification requirements');
        }

        // Check if user already has an active cycle
        if ($this->getActiveCycle($userId)) {
            throw new \Exception('User already has an active LGR cycle');
        }

        $cycle = LgrCycle::create($userId, Carbon::today());
        
        $model = LgrCycleModel::create($cycle->toArray());
        
        return LgrCycle::fromArray($model->toArray());
    }

    public function recordActivity(
        int $userId,
        string $activityType,
        string $description,
        array $metadata = []
    ): ?float {
        $cycle = $this->getActiveCycle($userId);
        
        if (!$cycle) {
            return null;
        }

        if (!$cycle->canEarnToday(Carbon::today())) {
            return null;
        }

        // Check if activity already recorded today
        $existingActivity = LgrActivityModel::where('user_id', $userId)
            ->where('activity_date', Carbon::today())
            ->where('activity_type', $activityType)
            ->first();

        if ($existingActivity) {
            return null; // Already recorded
        }

        $lgcAmount = $cycle->getDailyRate();

        // Record activity
        LgrActivityModel::create([
            'user_id' => $userId,
            'lgr_cycle_id' => $cycle->getId(),
            'activity_date' => Carbon::today(),
            'activity_type' => $activityType,
            'activity_description' => $description,
            'activity_metadata' => $metadata,
            'lgc_earned' => $lgcAmount,
            'verified' => true,
        ]);

        // Update cycle
        $cycle->recordActivity($lgcAmount);
        $this->saveCycle($cycle);

        // Credit LGC to user wallet
        $this->creditLgcToWallet($userId, $lgcAmount);

        return $lgcAmount;
    }

    public function hasActivityToday(int $userId): bool
    {
        return LgrActivityModel::where('user_id', $userId)
            ->where('activity_date', Carbon::today())
            ->exists();
    }

    public function getCycleStats(int $userId): array
    {
        $cycle = $this->getActiveCycle($userId);
        
        if (!$cycle) {
            return [
                'has_active_cycle' => false,
                'is_qualified' => $this->qualificationService->isUserQualified($userId),
            ];
        }

        return [
            'has_active_cycle' => true,
            'cycle' => $cycle->toArray(),
            'has_activity_today' => $this->hasActivityToday($userId),
            'recent_activities' => $this->getRecentActivities($userId, 7),
        ];
    }

    public function getRecentActivities(int $userId, int $days = 7): array
    {
        return LgrActivityModel::where('user_id', $userId)
            ->where('activity_date', '>=', Carbon::today()->subDays($days))
            ->orderBy('activity_date', 'desc')
            ->get()
            ->map(fn($activity) => [
                'date' => $activity->activity_date->format('Y-m-d'),
                'type' => $activity->activity_type,
                'description' => $activity->activity_description,
                'lgc_earned' => $activity->lgc_earned,
            ])
            ->toArray();
    }

    public function completeCycle(int $cycleId): void
    {
        $model = LgrCycleModel::findOrFail($cycleId);
        $model->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function checkAndCompleteExpiredCycles(): int
    {
        $expiredCycles = LgrCycleModel::where('status', 'active')
            ->where('end_date', '<', Carbon::today())
            ->get();

        foreach ($expiredCycles as $cycle) {
            $this->completeCycle($cycle->id);
        }

        return $expiredCycles->count();
    }

    private function saveCycle(LgrCycle $cycle): void
    {
        LgrCycleModel::where('id', $cycle->getId())
            ->update([
                'active_days' => $cycle->getActiveDays(),
                'total_earned_lgc' => $cycle->getTotalEarnedLgc(),
            ]);
    }

    private function creditLgcToWallet(int $userId, float $amount): void
    {
        DB::table('users')
            ->where('id', $userId)
            ->increment('loyalty_points', $amount);
    }
}
