<?php

declare(strict_types=1);

namespace App\Domain\Employee\Repositories;

use App\Domain\Employee\ValueObjects\EmployeeId;
use Illuminate\Support\Collection;

interface SupportTicketRepositoryInterface
{
    public function findById(int $id): ?object;

    public function findByEmployee(EmployeeId $employeeId, array $filters = []): Collection;

    public function findWithComments(int $ticketId, EmployeeId $employeeId): ?object;

    public function create(array $data): object;

    public function addComment(int $ticketId, array $data): object;
}