<?php

namespace App\Domain\PrimeEdge\Events;

use App\Domain\PrimeEdge\ValueObjects\InvoiceId;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\Money;
use DateTimeImmutable;

class InvoiceSent
{
    public function __construct(
        public readonly InvoiceId $invoiceId,
        public readonly ClientId $clientId,
        public readonly Money $amount,
        public readonly string $invoiceNumber,
        public readonly DateTimeImmutable $occurredAt
    ) {}
}
