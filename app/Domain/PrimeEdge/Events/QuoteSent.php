<?php

namespace App\Domain\PrimeEdge\Events;

use App\Domain\PrimeEdge\ValueObjects\InquiryId;
use App\Domain\PrimeEdge\ValueObjects\Money;
use DateTimeImmutable;

class QuoteSent
{
    public function __construct(
        public readonly InquiryId $inquiryId,
        public readonly Money $amount,
        public readonly string $description,
        public readonly DateTimeImmutable $occurredAt
    ) {}
}
