<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\CompanyUser;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\CompanyUserRepositoryInterface;
use App\Domain\StockFlow\Repositories\CompanyRoleRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\CompanyUserId;
use App\Domain\StockFlow\ValueObjects\CompanyRoleId;
use Illuminate\Support\Facades\Hash;

class CompanyUserService
{
    public function __construct(
        private CompanyUserRepositoryInterface $userRepository,
        private CompanyRoleRepositoryInterface $roleRepository,
    ) {}

    /**
     * Invite a user to join the company
     */
    public function inviteUser(
        int $companyId,
        int $invitedUserId,
        ?int $roleId = null,
    ): CompanyUser {
        $companyIdVo = CompanyId::fromInt($companyId);

        // Check if already a member
        $existing = $this->userRepository->findByCompanyIdAndUserId($companyIdVo, $invitedUserId);
        if ($existing) {
            if ($existing->isRemoved()) {
                // Re-invite removed user
                $existing->activate();
                if ($roleId) {
                    $existing->assignRole(CompanyRoleId::fromInt($roleId));
                }
                return $this->userRepository->save($existing);
            }
            throw new OperationFailedException('invite user', 'User is already a member of this company');
        }

        // Validate role belongs to company
        if ($roleId) {
            $role = $this->roleRepository->findById(CompanyRoleId::fromInt($roleId));
            if (!$role || $role->getCompanyId()->toInt() !== $companyId) {
                throw new OperationFailedException('invite user', 'Invalid role for this company');
            }
        }

        $companyUser = CompanyUser::invite(
            companyId: $companyIdVo,
            userId: $invitedUserId,
            roleId: $roleId ? CompanyRoleId::fromInt($roleId) : null,
        );

        return $this->userRepository->save($companyUser);
    }

    /**
     * Accept invitation (user accepts their own invitation)
     */
    public function acceptInvitation(int $companyId, int $userId): CompanyUser
    {
        $companyIdVo = CompanyId::fromInt($companyId);
        $companyUser = $this->userRepository->findByCompanyIdAndUserId($companyIdVo, $userId);

        if (!$companyUser) {
            throw new OperationFailedException('accept invitation', 'No pending invitation found');
        }

        if (!$companyUser->isPending()) {
            throw new OperationFailedException('accept invitation', 'Invitation already processed');
        }

        $companyUser->accept();
        return $this->userRepository->save($companyUser);
    }

    /**
     * Get all employees for a company
     */
    public function getEmployees(int $companyId, ?string $status = null): array
    {
        return array_map(
            fn($u) => $u->toArray(),
            $this->userRepository->findByCompanyId(CompanyId::fromInt($companyId), $status)
        );
    }

    /**
     * Get active employees for a company
     */
    public function getActiveEmployees(int $companyId): array
    {
        return $this->getEmployees($companyId, 'active');
    }

    /**
     * Get pending invitations
     */
    public function getPendingInvitations(int $companyId): array
    {
        return array_map(
            fn($u) => $u->toArray(),
            $this->userRepository->findPendingInvitations(CompanyId::fromInt($companyId))
        );
    }

    /**
     * Get specific employee
     */
    public function getEmployee(int $companyId, int $employeeUserId): ?array
    {
        $companyUser = $this->userRepository->findByCompanyIdAndUserId(
            CompanyId::fromInt($companyId),
            $employeeUserId
        );
        return $companyUser ? $companyUser->toArray() : null;
    }

    /**
     * Update employee role
     */
    public function updateEmployeeRole(int $companyId, int $employeeUserId, ?int $roleId): CompanyUser
    {
        $companyIdVo = CompanyId::fromInt($companyId);
        $companyUser = $this->userRepository->findByCompanyIdAndUserId($companyIdVo, $employeeUserId);

        if (!$companyUser) {
            throw new OperationFailedException('update role', 'Employee not found in this company');
        }

        if ($companyUser->isRemoved()) {
            throw new OperationFailedException('update role', 'Cannot update role for removed employee');
        }

        // Validate role belongs to company
        if ($roleId) {
            $role = $this->roleRepository->findById(CompanyRoleId::fromInt($roleId));
            if (!$role || $role->getCompanyId()->toInt() !== $companyId) {
                throw new OperationFailedException('update role', 'Invalid role for this company');
            }
        }

        $companyUser->assignRole($roleId ? CompanyRoleId::fromInt($roleId) : null);
        return $this->userRepository->save($companyUser);
    }

    /**
     * Suspend an employee
     */
    public function suspendEmployee(int $companyId, int $employeeUserId): CompanyUser
    {
        $companyIdVo = CompanyId::fromInt($companyId);
        $companyUser = $this->userRepository->findByCompanyIdAndUserId($companyIdVo, $employeeUserId);

        if (!$companyUser) {
            throw new OperationFailedException('suspend employee', 'Employee not found');
        }

        $companyUser->suspend();
        return $this->userRepository->save($companyUser);
    }

    /**
     * Reactivate a suspended employee
     */
    public function reactivateEmployee(int $companyId, int $employeeUserId): CompanyUser
    {
        $companyIdVo = CompanyId::fromInt($companyId);
        $companyUser = $this->userRepository->findByCompanyIdAndUserId($companyIdVo, $employeeUserId);

        if (!$companyUser) {
            throw new OperationFailedException('reactivate employee', 'Employee not found');
        }

        $companyUser->activate();
        return $this->userRepository->save($companyUser);
    }

    /**
     * Remove an employee from the company
     */
    public function removeEmployee(int $companyId, int $employeeUserId, ?string $reason = null): CompanyUser
    {
        $companyIdVo = CompanyId::fromInt($companyId);
        $companyUser = $this->userRepository->findByCompanyIdAndUserId($companyIdVo, $employeeUserId);

        if (!$companyUser) {
            throw new OperationFailedException('remove employee', 'Employee not found');
        }

        $companyUser->remove($reason);
        return $this->userRepository->save($companyUser);
    }

    /**
     * Check if user has a specific permission for a company
     */
    public function userHasPermission(int $companyId, int $userId, string $permission): bool
    {
        $companyUser = $this->userRepository->findByCompanyIdAndUserId(
            CompanyId::fromInt($companyId),
            $userId
        );

        if (!$companyUser || !$companyUser->isActive()) {
            return false;
        }

        if (!$companyUser->getRoleId()) {
            return false;
        }

        $role = $this->roleRepository->findById($companyUser->getRoleId());
        if (!$role) {
            return false;
        }

        // Check wildcard permission
        if ($role->hasPermission('*')) {
            return true;
        }

        return $role->hasPermission($permission);
    }

    /**
     * Get user's role for a company
     */
    public function getUserRole(int $companyId, int $userId): ?array
    {
        $companyUser = $this->userRepository->findByCompanyIdAndUserId(
            CompanyId::fromInt($companyId),
            $userId
        );

        if (!$companyUser || !$companyUser->getRoleId()) {
            return null;
        }

        $role = $this->roleRepository->findById($companyUser->getRoleId());
        return $role ? $role->toArray() : null;
    }
}