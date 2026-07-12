<?php

namespace App\Domain\PrimeEdge\Events;

use App\Domain\PrimeEdge\ValueObjects\InquiryId;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use DateTimeImmutable;

class InquirySubmitted
{
    public function __construct(
        public readonly InquiryId $inquiryId,
        public readonly ClientId $clientId,
        public readonly string $serviceDescription,
        public readonly DateTimeImmutable $occurredAt
    ) {}
}
