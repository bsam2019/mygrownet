<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Events;

interface DomainEvent
{
    public function occurredAt(): \DateTimeImmutable;
    public function getCompanyId(): int;
}
