<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Services;

use App\Domain\Module\Services\SubscriptionService;
use App\Models\User;

/**
 * Enforces GrowStream content access control based on the user's platform
 * module subscription tier for GrowStream.
 *
 * Tier mapping (from config/modules/growstream.php):
 *   - 'free'      → can watch only free (access_level = free) content
 *   - 'starter'   → paid tier, unlocks basic + premium content
 *   - 'business'  → paid tier, unlocks everything (admins are treated as business)
 */
class AccessControlService
{
    public function __construct(
        private SubscriptionService $subscriptionService,
    ) {}

    public function userCanAccess(?User $user, string $videoAccessLevel): bool
    {
        if ($videoAccessLevel === 'free') {
            return true;
        }

        if ($user === null) {
            return false;
        }

        return $this->hasPaidSubscription($user);
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
}
