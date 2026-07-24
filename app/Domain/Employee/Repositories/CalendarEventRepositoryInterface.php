<?php

declare(strict_types=1);

namespace App\Domain\Employee\Repositories;

use App\Domain\Employee\ValueObjects\EmployeeId;
use Illuminate\Support\Collection;
use DateTimeImmutable;

interface CalendarEventRepositoryInterface
{
    public function findById(int $id): ?object;

    public function findByEmployee(EmployeeId $employeeId, DateTimeImmutable $start, DateTimeImmutable $end): Collection;

    public function findUpcoming(EmployeeId $employeeId, int $limit = 5): Collection;

    public function findToday(EmployeeId $employeeId): Collection;

    public function findThisWeek(EmployeeId $employeeId): Collection;

    public function create(array $data): object;

    public function update(int $eventId, EmployeeId $employeeId, array $data): ?object;

    public function cancel(int $eventId, EmployeeId $employeeId): void;

    public function countToday(EmployeeId $employeeId): int;

    public function countThisWeek(EmployeeId $employeeId): int;
}