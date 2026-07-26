<?php

namespace App\Infrastructure\Contracts\Module;

use App\Domain\Module\Contracts\TierProvider;
use App\Domain\Module\Services\TierConfigurationService;

class TierProviderImpl implements TierProvider
{
    public function __construct(
        private readonly TierConfigurationService $tierConfig
    ) {}

    public function capability(): string
    {
        return 'module.tier';
    }

    public function getTierConfig(string $moduleId, string $tier): ?array
    {
        return $this->tierConfig->getTierConfig($moduleId, $tier);
    }

    public function getLimit(string $moduleId, string $tier, string $limitKey): int
    {
        return $this->tierConfig->getLimit($moduleId, $tier, $limitKey);
    }

    public function getTierFeatures(string $moduleId, string $tier): array
    {
        return $this->tierConfig->getTierFeatures($moduleId, $tier);
    }
}
