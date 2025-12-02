<?php

namespace App\Application\Commands;

class CancelSubscriptionCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly string $moduleId,
        public readonly ?string $reason = null,
        public readonly bool $immediate = false
    ) {}
}
