<?php

namespace App\Domain\Module\Contracts;

use App\Domain\Core\Contracts\ProviderContract;

interface SubscriptionManagementProvider extends ProviderContract
{
    public function subscribe(int $userId, string $moduleId, string $tier, string $billingCycle = 'monthly', float $amount = 0.0): array;

    public function cancel(int $userId, string $moduleId): void;
}
