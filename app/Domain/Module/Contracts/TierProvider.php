<?php

namespace App\Domain\Module\Contracts;

use App\Domain\Core\Contracts\ProviderContract;

interface TierProvider extends ProviderContract
{
    public function getTierConfig(string $moduleId, string $tier): ?array;

    public function getLimit(string $moduleId, string $tier, string $limitKey): int;

    public function getTierFeatures(string $moduleId, string $tier): array;
}
