<?php

namespace App\Domain\PrimeEdge\Repositories;

use App\Domain\PrimeEdge\Entities\ComplianceTask;
use App\Domain\PrimeEdge\ValueObjects\ComplianceTaskId;
use App\Domain\PrimeEdge\ValueObjects\ClientId;

interface ComplianceTaskRepositoryInterface
{
    public function save(ComplianceTask $task): void;
    public function findById(ComplianceTaskId $id): ?ComplianceTask;
    public function findByClientId(ClientId $clientId): array;
    public function findPending(): array;
    public function findOverdue(): array;
    public function findUpcoming(int $withinDays): array;
    public function findAll(): array;
    public function nextId(): ComplianceTaskId;
}
