<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\WorkflowInstance;

interface WorkflowInstanceRepositoryInterface
{
    public function findById(int $id): ?WorkflowInstance;

    public function findByEntity(int $businessId, string $entityType, int $entityId): array;

    public function findPending(int $businessId): array;

    public function findByStatus(int $businessId, string $status): array;

    public function save(WorkflowInstance $instance): WorkflowInstance;

    public function findByApprover(int $businessId, int $userId): array;
}
