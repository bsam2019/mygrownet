<?php

namespace App\Domain\PrimeEdge\Events;

use App\Domain\PrimeEdge\ValueObjects\InvoiceId;
use DateTimeImmutable;

class InvoicePaid
{
    public function __construct(
        public readonly InvoiceId $invoiceId,
        public readonly DateTimeImmutable $occurredAt
    ) {}
}
