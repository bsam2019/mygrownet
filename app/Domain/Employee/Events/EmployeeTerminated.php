<?php

declare(strict_types=1);

namespace App\Domain\Employee\Events;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\Email;
use DateTimeImmutable;

final class EmployeeTerminated
{
    public function __construct(
        public readonly EmployeeId $employeeId,
        public readonly Email $email,
        public readonly string $reason,
        public readonly DateTimeImmutable $terminationDate,
        public readonly DateTimeImmutable $occurredAt = new DateTimeImmutable()
    ) {}
}