<?php

namespace App\Domain\Storage\Events;

class QuotaExceeded
{
    public function __construct(
        public readonly int $userId,
        public readonly int $attemptedSize,
        public readonly int $quotaLimit,
        public readonly int $currentUsage,
        public readonly \DateTimeImmutable $occurredAt
    ) {}
}
