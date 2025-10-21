<?php

namespace App\Domain\Payment\Events;

use DateTimeImmutable;

class PaymentVerified
{
    public function __construct(
        public readonly int $paymentId,
        public readonly int $userId,
        public readonly int $verifiedBy,
        public readonly float $amount,
        public readonly string $paymentType,
        public readonly DateTimeImmutable $occurredAt
    ) {}
}
