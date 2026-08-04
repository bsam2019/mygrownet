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
 *   - 'business'  → paid tier, unlocks everything (admins are treated as business)
 *
 * Viewer monetization model:
 *   Creators upload for free; viewers pay for premium access. Each paid tier
 *   includes a monthly premium-view allowance (views_per_month from the tier
 *   config). Playback of premium content is blocked once the allowance for the
 *   current calendar month is exhausted. Free content never counts against the
 *   allowance.
 */
class AccessControlService
{
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

        $remaining = $this->remainingPremiumViews($user);

        return $remaining === -1 || $remaining > 0;
    }

    public function hasPaidSubscription(User $user): bool
    {
        $tier = $this->currentTier($user);

        return in_array($tier, ['starter', 'business'], true);
    }

    public function currentTier(?User $user): string
    {
        if ($user === null) {
            return 'none';
        }

        return $this->subscriptionService->getUserTier($user, 'growstream');
    }

    /**
     * Monthly premium-view allowance for the user's tier. -1 = unlimited.
     */
    public function premiumViewsAllowance(?User $user): int
    {
        if ($user === null || $this->tierConfig === null) {
            return 0;
        }

        $tier = $this->currentTier($user);
        $limits = $this->tierConfig->getTierLimits('growstream', $tier);

        return (int) ($limits['views_per_month'] ?? 0);
    }

    /**
     * Number of premium views the user has already consumed this calendar month.
     */
    public function premiumViewsUsed(?User $user): int
    {
        if ($user === null || $this->viewRepo === null) {
            return 0;
        }

        return $this->viewRepo->countPremiumViewsByUser($user->id, now()->startOfMonth());
    }

    /**
     * How many premium views remain this month. -1 = unlimited.
     */
    public function remainingPremiumViews(?User $user): int
    {
        $allowance = $this->premiumViewsAllowance($user);

        if ($allowance === -1) {
            return -1;
        }

        return max(0, $allowance - $this->premiumViewsUsed($user));
    }
}
