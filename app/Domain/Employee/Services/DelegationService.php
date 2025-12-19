<?php

namespace App\Domain\Employee\Services;

use App\Domain\Employee\Constants\DelegatedPermissions;
use App\Models\DelegationApprovalRequest;
use App\Models\Employee;
use App\Models\EmployeeDelegation;
use App\Models\EmployeeDelegationLog;
use App\Models\User;
use App\Notifications\DelegationApprovalNeededNotification;
use App\Notifications\DelegationGrantedNotification;
use App\Notifications\DelegationRevokedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DelegationService
{
    /**
     * Grant a delegated permission to an employee
     */
    public function grantPermission(
        Employee $employee,
        string $permissionKey,
        User $delegator,
        bool $requiresApproval = false,
        ?Employee $approvalManager = null,
        ?\DateTimeInterface $expiresAt = null,
        ?string $notes = null
    ): EmployeeDelegation {
        return DB::transaction(function () use ($employee, $permissionKey, $delegator, $requiresApproval, $approvalManager, $expiresAt, $notes) {
            $delegation = EmployeeDelegation::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'permission_key' => $permissionKey,
                ],
                [
                    'delegated_by' => $delegator->id,
                    'requires_approval' => $requiresApproval,
                    'approval_manager_id' => $approvalManager?->id,
                    'is_active' => true,
                    'expires_at' => $expiresAt,
                    'notes' => $notes,
                ]
            );

            $this->logAction(
                $delegation,
                $employee,
                $permissionKey,
                EmployeeDelegationLog::ACTION_GRANTED,
                $delegator,
                ['requires_approval' => $requiresApproval, 'expires_at' => $expiresAt?->format('Y-m-d H:i:s')]
            );

            return $delegation;
        });
    }

    /**
     * Revoke a delegated permission from an employee
     */
    public function revokePermission(
        Employee $employee,
        string $permissionKey,
        User $revoker,
        ?string $reason = null
    ): bool {
        return DB::transaction(function () use ($employee, $permissionKey, $revoker, $reason) {
            $delegation = EmployeeDelegation::where('employee_id', $employee->id)
                ->where('permission_key', $permissionKey)
                ->first();

            if (!$delegation) {
                return false;
            }

            $delegation->update(['is_active' => false]);

            $this->logAction(
                $delegation,
                $employee,
                $permissionKey,
                EmployeeDelegationLog::ACTION_REVOKED,
                $revoker,
                ['reason' => $reason]
            );

            // Send notification
            $permissionMeta = $this->getPermissionMetadata($permissionKey);
            $permissionName = $permissionMeta['name'] ?? $permissionKey;
            
            if ($employee->user) {
                $employee->user->notify(new DelegationRevokedNotification([$permissionName], $revoker->name, $reason));
            }

            return true;
        });
    }

    /**
     * Grant multiple permissions at once (e.g., from a preset)
     */
    public function grantPermissionSet(
        Employee $employee,
        array $permissionKeys,
        User $delegator,
        ?string $notes = null
    ): Collection {
        $delegations = collect();
        $permissionNames = [];

        foreach ($permissionKeys as $permissionKey) {
            $permissionMeta = $this->getPermissionMetadata($permissionKey);
            $requiresApproval = $permissionMeta['requires_approval'] ?? false;

            $delegation = $this->grantPermission(
                $employee,
                $permissionKey,
                $delegator,
                $requiresApproval,
                null,
                null,
                $notes
            );

            $delegations->push($delegation);
            $permissionNames[] = $permissionMeta['name'] ?? $permissionKey;
        }

        // Send notification for batch grant
        if ($employee->user && count($permissionNames) > 0) {
            $employee->user->notify(new DelegationGrantedNotification($permissionNames, $delegator->name));
        }

        return $delegations;
    }

    /**
     * Check if an employee has a specific delegated permission
     */
    public function hasPermission(Employee $employee, string $permissionKey): bool
    {
        return EmployeeDelegation::where('employee_id', $employee->id)
            ->where('permission_key', $permissionKey)
            ->active()
            ->exists();
    }

    /**
     * Get a specific delegation for an employee
     */
    public function getDelegation(Employee $employee, string $permissionKey): ?EmployeeDelegation
    {
        return EmployeeDelegation::where('employee_id', $employee->id)
            ->where('permission_key', $permissionKey)
            ->active()
            ->first();
    }

    /**
     * Get all active delegations for an employee
     */
    public function getEmployeeDelegations(Employee $employee): Collection
    {
        return EmployeeDelegation::where('employee_id', $employee->id)
            ->active()
            ->with(['delegator', 'approvalManager'])
            ->get();
    }

    /**
     * Get delegations grouped by category for display
     */
    public function getEmployeeDelegationsGrouped(Employee $employee): array
    {
        $delegations = $this->getEmployeeDelegations($employee);
        $categories = DelegatedPermissions::getPermissionsByCategory();
        $grouped = [];

        foreach ($categories as $categoryName => $categoryData) {
            $categoryPermissions = [];
            
            foreach ($categoryData['permissions'] as $permKey => $permData) {
                $delegation = $delegations->firstWhere('permission_key', $permKey);
                
                if ($delegation) {
                    $categoryPermissions[] = [
                        'key' => $permKey,
                        'name' => $permData['name'],
                        'description' => $permData['description'],
                        'delegation' => $delegation,
                        'requires_approval' => $delegation->requires_approval,
                    ];
                }
            }

            if (!empty($categoryPermissions)) {
                $grouped[$categoryName] = [
                    'description' => $categoryData['description'],
                    'risk_level' => $categoryData['risk_level'],
                    'permissions' => $categoryPermissions,
                ];
            }
        }

        return $grouped;
    }

    /**
     * Log usage of a delegated permission
     */
    public function logUsage(
        Employee $employee,
        string $permissionKey,
        User $user,
        array $metadata = []
    ): void {
        $delegation = EmployeeDelegation::where('employee_id', $employee->id)
            ->where('permission_key', $permissionKey)
            ->first();

        $this->logAction(
            $delegation,
            $employee,
            $permissionKey,
            EmployeeDelegationLog::ACTION_USED,
            $user,
            $metadata
        );
    }

    /**
     * Request approval for an action
     */
    public function requestApproval(
        EmployeeDelegation $delegation,
        string $actionType,
        string $resourceType,
        int $resourceId,
        array $actionData
    ): DelegationApprovalRequest {
        $request = DelegationApprovalRequest::create([
            'delegation_id' => $delegation->id,
            'employee_id' => $delegation->employee_id,
            'action_type' => $actionType,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'action_data' => $actionData,
            'status' => DelegationApprovalRequest::STATUS_PENDING,
        ]);

        $this->logAction(
            $delegation,
            $delegation->employee,
            $delegation->permission_key,
            EmployeeDelegationLog::ACTION_APPROVAL_REQUESTED,
            $delegation->employee->user,
            ['request_id' => $request->id, 'action_type' => $actionType]
        );

        // Notify manager
        $manager = $delegation->approvalManager ?? $delegation->employee->manager;
        if ($manager?->user) {
            $actionDescription = str_replace('_', ' ', $actionType);
            $manager->user->notify(new DelegationApprovalNeededNotification(
                $request,
                $delegation->employee->full_name,
                ucwords($actionDescription)
            ));
        }

        return $request;
    }

    /**
     * Approve a pending request
     */
    public function approveRequest(
        DelegationApprovalRequest $request,
        User $reviewer,
        ?string $notes = null
    ): DelegationApprovalRequest {
        $request->update([
            'status' => DelegationApprovalRequest::STATUS_APPROVED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        $this->logAction(
            $request->delegation,
            $request->employee,
            $request->delegation->permission_key,
            EmployeeDelegationLog::ACTION_APPROVED,
            $reviewer,
            ['request_id' => $request->id]
        );

        return $request;
    }

    /**
     * Reject a pending request
     */
    public function rejectRequest(
        DelegationApprovalRequest $request,
        User $reviewer,
        ?string $reason = null
    ): DelegationApprovalRequest {
        $request->update([
            'status' => DelegationApprovalRequest::STATUS_REJECTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $reason,
        ]);

        $this->logAction(
            $request->delegation,
            $request->employee,
            $request->delegation->permission_key,
            EmployeeDelegationLog::ACTION_REJECTED,
            $reviewer,
            ['request_id' => $request->id, 'reason' => $reason]
        );

        return $request;
    }

    /**
     * Get pending approval requests for a manager
     */
    public function getPendingApprovalsForManager(Employee $manager): Collection
    {
        return DelegationApprovalRequest::pending()
            ->whereHas('delegation', function ($query) use ($manager) {
                $query->where('approval_manager_id', $manager->id);
            })
            ->with(['employee', 'delegation'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all available permissions for delegation UI
     */
    public function getAvailablePermissions(): array
    {
        return DelegatedPermissions::getPermissionsByCategory();
    }

    /**
     * Get recommended permission sets
     */
    public function getRecommendedSets(): array
    {
        return DelegatedPermissions::getRecommendedSets();
    }

    /**
     * Get permission metadata
     */
    protected function getPermissionMetadata(string $permissionKey): array
    {
        $categories = DelegatedPermissions::getPermissionsByCategory();
        
        foreach ($categories as $categoryData) {
            if (isset($categoryData['permissions'][$permissionKey])) {
                return $categoryData['permissions'][$permissionKey];
            }
        }

        return [];
    }

    /**
     * Expire delegations that have passed their expiration date
     */
    public function expireOverdueDelegations(): int
    {
        $expiredDelegations = EmployeeDelegation::where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->with('employee.user')
            ->get();

        $count = 0;
        foreach ($expiredDelegations as $delegation) {
            $delegation->update(['is_active' => false]);
            
            $this->logAction(
                $delegation,
                $delegation->employee,
                $delegation->permission_key,
                EmployeeDelegationLog::ACTION_EXPIRED,
                User::first(), // System user
                ['expired_at' => $delegation->expires_at->toDateTimeString()]
            );

            // Notify employee
            $permissionMeta = $this->getPermissionMetadata($delegation->permission_key);
            $permissionName = $permissionMeta['name'] ?? $delegation->permission_key;
            
            if ($delegation->employee->user) {
                $delegation->employee->user->notify(
                    new DelegationRevokedNotification([$permissionName], 'System', 'Permission expired')
                );
            }

            $count++;
        }

        return $count;
    }

    /**
     * Log a delegation action
     */
    protected function logAction(
        ?EmployeeDelegation $delegation,
        Employee $employee,
        string $permissionKey,
        string $action,
        User $performer,
        array $metadata = []
    ): EmployeeDelegationLog {
        return EmployeeDelegationLog::create([
            'delegation_id' => $delegation?->id,
            'employee_id' => $employee->id,
            'permission_key' => $permissionKey,
            'action' => $action,
            'performed_by' => $performer->id,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
        ]);
    }
}
