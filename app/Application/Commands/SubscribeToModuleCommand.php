<?php

namespace App\Application\Commands;

class SubscribeToModuleCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly string $moduleId,
        public readonly string $tier,
        public readonly float $amount,
        public readonly string $currency = 'ZMW',
        public readonly string $billingCycle = 'monthly'
    ) {}
}
