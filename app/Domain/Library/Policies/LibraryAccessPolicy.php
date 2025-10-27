<?php

namespace App\Domain\Library\Policies;

use App\Domain\Library\ValueObjects\AccessPeriod;

class LibraryAccessPolicy
{
    public function canAccessLibrary(
        bool $hasStarterKit,
        ?AccessPeriod $freeAccessPeriod,
        bool $hasActiveSubscription
    ): bool {
        // Must have purchased starter kit
        if (!$hasStarterKit) {
            return false;
        }

        // Check if within free access period
        if ($freeAccessPeriod && $freeAccessPeriod->isActive()) {
            return true;
        }

        // After free period, requires active subscription
        return $hasActiveSubscription;
    }

    public function getAccessDenialReason(
        bool $hasStarterKit,
        ?AccessPeriod $freeAccessPeriod,
        bool $hasActiveSubscription
    ): string {
        if (!$hasStarterKit) {
            return 'Purchase the Starter Kit to unlock the Resource Library.';
        }

        if ($freeAccessPeriod && $freeAccessPeriod->hasExpired() && !$hasActiveSubscription) {
            return 'Your 30-day free library access has expired. Activate your monthly subscription to continue accessing the library.';
        }

        return 'You do not have access to the library.';
    }
}
