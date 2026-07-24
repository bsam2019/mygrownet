<?php

declare(strict_types=1);

namespace App\Domain\Employee\Repositories;

use Illuminate\Support\Collection;

interface DelegationRepositoryInterface
{
    public function findActiveByEmployeeAndKey(int $employeeId, string $permissionKey): ?object;

    public function findActiveByEmployee(int $employeeId): Collection;

    public function findPendingApprovalsForManager(int $managerId): Collection;

    public function findExpiredDelegations(): Collection;

    public function updateOrCreate(array $attributes, array $values): object;

    public function createDelegationLog(array $data): object;

    public function createApprovalRequest(array $data): object;

    public function findApprovalRequestById(int $id): ?object;

    public function updateApprovalRequest(int $id, array $data): ?object;

    public function hasActivePermission(int $employeeId, string $permissionKey): bool;
}