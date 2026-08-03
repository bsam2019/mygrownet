<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Services;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory;
use App\Domain\GrowStream\Repositories\CreatorEarningRepositoryInterface;

/**
 * Computes per-creator earnings from the premium watch-time revenue pool.
 *
 * Business rule (from PRODUCT_STRATEGIC_PLAN.md):
 *   pool = subscription revenue for the period x 70% (creator revenue share)
 *   each creator earns = pool x (creator premium watch seconds / total premium watch seconds)
 */
class RevenuePoolService
{
    public function __construct(
        private CreatorEarningRepositoryInterface $earningRepo,
    ) {}

    /**
     * Calculate earnings for a billing period and persist a per-creator record.
     *
     * @return array<int, array<string, mixed>>
     */
    public function calculateForPeriod(\DateTimeInterface $start, \DateTimeInterface $end, float $subscriptionRevenue): array
    {
        $premiumWatchSecondsByCreator = $this->premiumWatchSecondsByCreator($start, $end);

        $totalPremiumSeconds = (int) array_sum($premiumWatchSecondsByCreator);

        if ($totalPremiumSeconds <= 0) {
            return [];
        }

        $revenueShare = (float) config('growstream.creator.default_revenue_share', 70);
        $pool = round($subscriptionRevenue * ($revenueShare / 100), 2);

        $results = [];

        foreach ($premiumWatchSecondsByCreator as $creatorId => $seconds) {
            $sharePercentage = round(($seconds / $totalPremiumSeconds) * 100, 2);
            $earnedAmount = round($pool * ($seconds / $totalPremiumSeconds), 2);

            $earning = $this->earningRepo->updateOrCreate(
                [
                    'creator_id' => $creatorId,
                    'period_start' => $start,
                    'period_end' => $end,
                ],
                [
                    'premium_watch_seconds' => $seconds,
                    'pool_amount' => $pool,
                    'share_percentage' => $sharePercentage,
                    'earned_amount' => $earnedAmount,
                    'status' => 'pending',
                ]
            );

            $results[] = $earning->toArray();
        }

        return $results;
    }

    /**
     * @return array<int, int> creator_id => total premium watch seconds
     */
    protected function premiumWatchSecondsByCreator(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return WatchHistory::join('growstream_videos as v', 'v.id', '=', 'growstream_watch_history.video_id')
            ->whereIn('v.access_level', ['basic', 'premium', 'institutional'])
            ->where('growstream_watch_history.last_watched_at', '>=', $start)
            ->where('growstream_watch_history.last_watched_at', '<=', $end)
            ->where('v.creator_id', '>', 0)
            ->selectRaw('v.creator_id, SUM(growstream_watch_history.watch_duration) as premium_seconds')
            ->groupBy('v.creator_id')
            ->pluck('premium_seconds', 'creator_id')
            ->map(fn ($seconds) => (int) $seconds)
            ->all();
    }
}
