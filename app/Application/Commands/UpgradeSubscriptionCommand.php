<?php

namespace App\Application\Commands;

class UpgradeSubscriptionCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly string $moduleId,
        public readonly string $newTier,
        public readonly float $newAmount,
        public readonly string $currency = 'ZMW'
    ) {}
}
