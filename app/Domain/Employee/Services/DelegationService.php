<?php

namespace App\Domain\Employee\Services;

use App\Domain\Employee\Constants\DelegatedPermissions;
use App\Domain\Employee\Repositories\DelegationRepositoryInterface;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Models\DelegationApprovalRequest;
use App\Models\User;
use App\Notifications\DelegationApprovalNeededNotification;
use App\Notifications\DelegationGrantedNotification;
use App\Notifications\DelegationRevokedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DelegationService
{
    public const ACTION_GRANTED = 'granted';
    public const ACTION_REVOKED = 'revoked';
    public const ACTION_USED = 'used';
    public const ACTION_APPROVAL_REQUESTED = 'approval_requested';
    public const ACTION_APPROVED = 'approved';
    public const ACTION_REJECTED = 'rejected';
    public const ACTION_EXPIRED = 'expired';

    public function __construct(
        private DelegationRepositoryInterface $delegationRepo,
        private EmployeeRepositoryInterface $employeeRepo
    ) {}

    public function grantPermission(
        object $employee,
        string $permissionKey,
        User $delegator,
        bool $requiresApproval = false,
        ?object $approvalManager = null,
        ?\DateTimeInterface $expiresAt = null,
        ?string $notes = null
    ): object {
        return DB::transaction(function () use ($employee, $permissionKey, $delegator, $requiresApproval, $approvalManager, $expiresAt, $notes) {
            $delegation = $this->delegationRepo->updateOrCreate(
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
                self::ACTION_GRANTED,
                $delegator,
                ['requires_approval' => $requiresApproval, 'expires_at' => $expiresAt?->format('Y-m-d H:i:s')]
            );

            return $delegation;
        });
    }

    public function revokePermission(
        object $employee,
        string $permissionKey,
        User $revoker,
        ?string $reason = null
    ): bool {
        return DB::transaction(function () use ($employee, $permissionKey, $revoker, $reason) {
            $delegation = $this->delegationRepo->findActiveByEmployeeAndKey($employee->id, $permissionKey);

            if (!$delegation) {
                return false;
            }

            $this->delegationRepo->updateOrCreate(
                ['id' => $delegation->id],
                ['is_active' => false]
            );

            $this->logAction(
                $delegation,
                $employee,
                $permissionKey,
                self::ACTION_REVOKED,
                $revoker,
                ['reason' => $reason]
            );

            $permissionMeta = $this->getPermissionMetadata($permissionKey);
            $permissionName = $permissionMeta['name'] ?? $permissionKey;

            if ($employee->user) {
                $employee->user->notify(new DelegationRevokedNotification([$permissionName], $revoker->name, $reason));
            }

            return true;
        });
    }

    public function grantPermissionSet(
        object $employee,
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

        if ($employee->user && count($permissionNames) > 0) {
            $employee->user->notify(new DelegationGrantedNotification($permissionNames, $delegator->name));
        }

        return $delegations;
    }

    public function hasPermission(object $employee, string $permissionKey): bool
    {
        return $this->delegationRepo->hasActivePermission($employee->id, $permissionKey);
    }

    public function getDelegation(object $employee, string $permissionKey): ?object
    {
        return $this->delegationRepo->findActiveByEmployeeAndKey($employee->id, $permissionKey);
    }

    public function getEmployeeDelegations(object $employee): Collection
    {
        return $this->delegationRepo->findActiveByEmployee($employee->id);
    }

    public function getEmployeeDelegationsGrouped(object $employee): array
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

    public function logUsage(
        object $employee,
        string $permissionKey,
        User $user,
        array $metadata = []
    ): void {
        $delegation = $this->delegationRepo->findActiveByEmployeeAndKey($employee->id, $permissionKey);

        $this->logAction(
            $delegation,
            $employee,
            $permissionKey,
            self::ACTION_USED,
            $user,
            $metadata
        );
    }

    public function requestApproval(
        object $delegation,
        string $actionType,
        string $resourceType,
        int $resourceId,
        array $actionData
    ): DelegationApprovalRequest {
        $request = $this->delegationRepo->createApprovalRequest([
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
            self::ACTION_APPROVAL_REQUESTED,
            $delegation->employee->user,
            ['request_id' => $request->id, 'action_type' => $actionType]
        );

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

    public function approveRequest(
        DelegationApprovalRequest $request,
        User $reviewer,
        ?string $notes = null
    ): DelegationApprovalRequest {
        $this->delegationRepo->updateApprovalRequest($request->id, [
            'status' => DelegationApprovalRequest::STATUS_APPROVED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        $request->refresh();

        $this->logAction(
            $request->delegation,
            $request->employee,
            $request->delegation->permission_key,
            self::ACTION_APPROVED,
            $reviewer,
            ['request_id' => $request->id]
        );

        return $request;
    }

    public function rejectRequest(
        DelegationApprovalRequest $request,
        User $reviewer,
        ?string $reason = null
    ): DelegationApprovalRequest {
        $this->delegationRepo->updateApprovalRequest($request->id, [
            'status' => DelegationApprovalRequest::STATUS_REJECTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $reason,
        ]);

        $request->refresh();

        $this->logAction(
            $request->delegation,
            $request->employee,
            $request->delegation->permission_key,
            self::ACTION_REJECTED,
            $reviewer,
            ['request_id' => $request->id, 'reason' => $reason]
        );

        return $request;
    }

    public function getPendingApprovalsForManager(object $manager): Collection
    {
        return $this->delegationRepo->findPendingApprovalsForManager($manager->id);
    }

    public function getAvailablePermissions(): array
    {
        return DelegatedPermissions::getPermissionsByCategory();
    }

    public function getRecommendedSets(): array
    {
        return DelegatedPermissions::getRecommendedSets();
    }

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

    public function expireOverdueDelegations(): int
    {
        $expiredDelegations = $this->delegationRepo->findExpiredDelegations();

        $count = 0;
        foreach ($expiredDelegations as $delegation) {
            $this->delegationRepo->updateOrCreate(
                ['id' => $delegation->id],
                ['is_active' => false]
            );

            $this->logAction(
                $delegation,
                $delegation->employee,
                $delegation->permission_key,
                self::ACTION_EXPIRED,
                User::first(),
                ['expired_at' => $delegation->expires_at->toDateTimeString()]
            );

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

    protected function logAction(
        ?object $delegation,
        object $employee,
        string $permissionKey,
        string $action,
        User $performer,
        array $metadata = []
    ): object {
        return $this->delegationRepo->createDelegationLog([
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