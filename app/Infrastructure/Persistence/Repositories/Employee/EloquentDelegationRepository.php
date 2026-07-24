<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\Employee;

use App\Domain\Employee\Repositories\DelegationRepositoryInterface;
use App\Models\DelegationApprovalRequest;
use App\Models\Employee\EmployeeDelegation;
use App\Models\Employee\EmployeeDelegationLog;
use Illuminate\Support\Collection;

class EloquentDelegationRepository implements DelegationRepositoryInterface
{
    public function findActiveByEmployeeAndKey(int $employeeId, string $permissionKey): ?EmployeeDelegation
    {
        return EmployeeDelegation::where('employee_id', $employeeId)
            ->where('permission_key', $permissionKey)
            ->active()
            ->first();
    }

    public function findActiveByEmployee(int $employeeId): Collection
    {
        return EmployeeDelegation::where('employee_id', $employeeId)
            ->active()
            ->with(['delegator', 'approvalManager'])
            ->get();
    }

    public function findPendingApprovalsForManager(int $managerId): Collection
    {
        return DelegationApprovalRequest::pending()
            ->whereHas('delegation', function ($query) use ($managerId) {
                $query->where('approval_manager_id', $managerId);
            })
            ->with(['employee', 'delegation'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findExpiredDelegations(): Collection
    {
        return EmployeeDelegation::where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->with('employee.user')
            ->get();
    }

    public function updateOrCreate(array $attributes, array $values): EmployeeDelegation
    {
        return EmployeeDelegation::updateOrCreate($attributes, $values);
    }

    public function createDelegationLog(array $data): EmployeeDelegationLog
    {
        return EmployeeDelegationLog::create($data);
    }

    public function createApprovalRequest(array $data): DelegationApprovalRequest
    {
        return DelegationApprovalRequest::create($data);
    }

    public function findApprovalRequestById(int $id): ?DelegationApprovalRequest
    {
        return DelegationApprovalRequest::find($id);
    }

    public function updateApprovalRequest(int $id, array $data): ?DelegationApprovalRequest
    {
        $request = DelegationApprovalRequest::find($id);
        if (!$request) {
            return null;
        }
        $request->update($data);
        return $request;
    }

    public function hasActivePermission(int $employeeId, string $permissionKey): bool
    {
        return EmployeeDelegation::where('employee_id', $employeeId)
            ->where('permission_key', $permissionKey)
            ->active()
            ->exists();
    }
}