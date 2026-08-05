<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Services;

use App\Domain\GrowStream\Repositories\VideoViewRepositoryInterface;
use App\Domain\Module\Services\SubscriptionService;
use App\Domain\Module\Services\TierConfigurationService;
use App\Models\User;

/**
 * Enforces GrowStream content access control based on the user's platform
 * module subscription tier for GrowStream.
 *
 * Tier mapping (from config/modules/growstream.php):
 *   - 'free'      → can watch only free (access_level = free) content
 *   - 'starter'   → paid tier, unlocks basic + premium content
 *   - 'premium'   → paid tier, higher allowance
 *   - 'business'  → paid tier, unlimited (admins are treated as business)
 *
 * Viewer monetization model:
 *   Creators upload for free; viewers pay for premium access. Each paid tier
 *   includes a monthly watch-minute allowance (watch_minutes_per_month from the
 *   tier config). Playback of premium content is blocked once the allowance for
 *   the current calendar month is exhausted. Free content never counts against
 *   the allowance. Consumption is measured by summing video durations for
 *   premium plays this month (not by counting views).
 */
class AccessControlService
{
    /**
     * How many premium watch-minutes the Unlimited tier must consume in a
     * calendar month before playback quality is throttled (not blocked).
     */
    private const UNLIMITED_THROTTLE_MINUTES = 2000;

    public function __construct(
        private SubscriptionService $subscriptionService,
        private ?VideoViewRepositoryInterface $viewRepo = null,
        private ?TierConfigurationService $tierConfig = null,
    ) {}

    public function userCanAccess(?User $user, string $videoAccessLevel): bool
    {
        if ($videoAccessLevel === 'free') {
            return true;
        }

        if ($user === null) {
            return false;
        }

        if (! $this->hasPaidSubscription($user)) {
            return false;
        }

        return $this->remainingWatchMinutes($user) !== 0;
    }

    public function hasPaidSubscription(User $user): bool
    {
        $tier = $this->currentTier($user);

        return in_array($tier, ['starter', 'premium', 'business'], true);
    }

    public function currentTier(?User $user): string
    {
        if ($user === null) {
            return 'none';
        }

        return $this->subscriptionService->getUserTier($user, 'growstream');
    }

    /**
     * Monthly watch-minute allowance from the user's tier. −1 = unlimited.
     */
    public function watchMinutesAllowance(?User $user): int
    {
        if ($user === null || $this->tierConfig === null) {
            return 0;
        }

        $tier = $this->currentTier($user);
        $limits = $this->tierConfig->getTierLimits('growstream', $tier);

        return (int) ($limits['watch_minutes_per_month'] ?? 0);
    }

    /**
     * Seconds of premium video the user has already consumed this calendar
     * month (sum of video durations for premium ViewVideo rows).
     */
    public function watchSecondsConsumed(?User $user): int
    {
        if ($user === null || $this->viewRepo === null) {
            return 0;
        }

        return $this->viewRepo->sumPremiumWatchSecondsByUser($user->id, now()->startOfMonth());
    }

    /**
     * Watch-minutes remaining this month. −1 = unlimited, 0 = exhausted.
     */
    public function remainingWatchMinutes(?User $user): int
    {
        $allowance = $this->watchMinutesAllowance($user);

        if ($allowance === -1) {
            return -1;
        }

        return max(0, $allowance - (int) floor($this->watchSecondsConsumed($user) / 60));
    }

    /**
     * Is this business-tier user past the fair-use throttle point?
     * They can still watch, but quality should be dropped.
     */
    public function isThrottled(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        $tier = $this->currentTier($user);

        if ($tier !== 'business') {
            return false;
        }

        return (int) floor($this->watchSecondsConsumed($user) / 60) >= self::UNLIMITED_THROTTLE_MINUTES;
    }
}
