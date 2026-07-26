<?php

namespace App\Domain\Module\Contracts;

use App\Domain\Core\Contracts\ProviderContract;
use App\Models\User;

interface SubscriptionProvider extends ProviderContract
{
    public function getUserTier(User $user, string $moduleId = 'growfinance'): string;

    public function hasFeature(User $user, string $feature, string $moduleId = 'growfinance'): bool;

    public function getUserLimits(User $user, string $moduleId = 'growfinance'): array;

    public function clearCache(User $user, string $moduleId = 'growfinance'): void;
}
