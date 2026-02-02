<?php

namespace App\Services;

use App\Models\SocialShare;
use Illuminate\Support\Facades\Log;

class SocialShareTrackingService
{
    public function __construct(
        private LgrActivityTrackingService $lgrTrackingService
    ) {}

    /**
     * Record a social share event
     */
    public function recordShare(
        int $userId,
        string $shareType,
        ?string $platform = null,
        ?string $sharedUrl = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): void {
        try {
            SocialShare::create([
                'user_id' => $userId,
                'share_type' => $shareType,
                'platform' => $platform,
                'shared_url' => $sharedUrl,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'shared_at' => now(),
            ]);

            // Check if user has reached 5 shares today
            $this->checkDailyShareThreshold($userId);

            Log::info('Social share recorded', [
                'user_id' => $userId,
                'share_type' => $shareType,
                'platform' => $platform,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record social share: ' . $e->getMessage(), [
                'user_id' => $userId,
                'share_type' => $shareType,
            ]);
        }
    }

    /**
     * Get user's share count for today
     */
    public function getTodayShareCount(int $userId): int
    {
        return SocialShare::forUser($userId)
            ->today()
            ->count();
    }

    /**
     * Check if user has reached the daily threshold (5 shares)
     * and trigger LGR activity if they have
     */
    private function checkDailyShareThreshold(int $userId): void
    {
        $todayCount = $this->getTodayShareCount($userId);

        // Only trigger LGR activity when user reaches exactly 5 shares
        // (not on every share after 5)
        if ($todayCount === 5) {
            $this->lgrTrackingService->recordSocialSharing($userId, $todayCount);
            
            Log::info('User reached 5 shares threshold - LGR activity recorded', [
                'user_id' => $userId,
                'share_count' => $todayCount,
            ]);
        }
    }

    /**
     * Get user's sharing statistics
     */
    public function getUserStats(int $userId): array
    {
        $todayCount = $this->getTodayShareCount($userId);
        $totalCount = SocialShare::forUser($userId)->count();
        $reachedThreshold = $todayCount >= 5;

        return [
            'today_count' => $todayCount,
            'total_count' => $totalCount,
            'threshold_reached' => $reachedThreshold,
            'remaining_for_bonus' => max(0, 5 - $todayCount),
        ];
    }

    /**
     * Get user's recent shares
     */
    public function getRecentShares(int $userId, int $limit = 10): array
    {
        return SocialShare::forUser($userId)
            ->orderBy('shared_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
