<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Services;

use App\Infrastructure\Persistence\Eloquent\GrowBizEmployeeModel;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Service to determine user's role and access level in GrowBiz
 */
class GrowBizAccessService
{
    public const ROLE_OWNER = 'owner';
    public const ROLE_SUPERVISOR = 'supervisor';
    public const ROLE_EMPLOYEE = 'employee';
    public const ROLE_NONE = 'none';

    /**
     * Get the user's GrowBiz context (role, employee record, accessible employee IDs)
     */
    public function getUserContext(User $user): array
    {
        $cacheKey = "growbiz_context_{$user->id}";
        
        return Cache::remember($cacheKey, 300, function () use ($user) {
            // Check if user is a business owner (has employees under them)
            $isOwner = GrowBizEmployeeModel::where('manager_id', $user->id)->exists();
            
            // Check if user is an employee (linked via user_id)
            $employeeRecord = GrowBizEmployeeModel::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
            
            if ($isOwner) {
                return $this->buildOwnerContext($user);
            }
            
            if ($employeeRecord) {
                return $this->buildEmployeeContext($user, $employeeRecord);
            }
            
            return [
                'role' => self::ROLE_NONE,
                'employee' => null,
                'managerId' => null,
                'accessibleEmployeeIds' => [],
                'canManageEmployees' => false,
                'canCreateTasks' => false,
                'canViewAllTasks' => false,
            ];
        });
    }

    /**
     * Build context for business owner
     */
    private function buildOwnerContext(User $user): array
    {
        $employeeIds = GrowBizEmployeeModel::where('manager_id', $user->id)
            ->pluck('id')
            ->toArray();
        
        return [
            'role' => self::ROLE_OWNER,
            'employee' => null,
            'managerId' => $user->id,
            'accessibleEmployeeIds' => $employeeIds,
            'canManageEmployees' => true,
            'canCreateTasks' => true,
            'canViewAllTasks' => true,
        ];
    }

    /**
     * Build context for employee (regular or supervisor)
     */
    private function buildEmployeeContext(User $user, GrowBizEmployeeModel $employee): array
    {
        $isSupervisor = $employee->hasSupervisorRole();
        $subordinateIds = $isSupervisor ? $employee->getAllSubordinateIds() : [];
        
        // Employee can access their own record + subordinates
        $accessibleIds = array_merge([$employee->id], $subordinateIds);
        
        return [
            'role' => $isSupervisor ? self::ROLE_SUPERVISOR : self::ROLE_EMPLOYEE,
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'position' => $employee->position,
                'department' => $employee->department,
            ],
            'managerId' => $employee->manager_id,
            'accessibleEmployeeIds' => $accessibleIds,
            'canManageEmployees' => $isSupervisor,
            'canCreateTasks' => $isSupervisor,
            'canViewAllTasks' => false,
        ];
    }

    /**
     * Clear cached context for a user
     */
    public function clearUserContext(int $userId): void
    {
        Cache::forget("growbiz_context_{$userId}");
    }

    /**
     * Check if user can access a specific employee
     */
    public function canAccessEmployee(User $user, int $employeeId): bool
    {
        $context = $this->getUserContext($user);
        
        if ($context['role'] === self::ROLE_OWNER) {
            // Owner can access all their employees
            return GrowBizEmployeeModel::where('id', $employeeId)
                ->where('manager_id', $user->id)
                ->exists();
        }
        
        return in_array($employeeId, $context['accessibleEmployeeIds']);
    }

    /**
     * Check if user can access a specific task
     */
    public function canAccessTask(User $user, int $taskId): bool
    {
        $context = $this->getUserContext($user);
        
        if ($context['role'] === self::ROLE_OWNER) {
            return true; // Owner can access all tasks
        }
        
        if ($context['role'] === self::ROLE_NONE) {
            return false;
        }
        
        // Check if task is assigned to user or their subordinates
        $assignedEmployeeIds = \App\Infrastructure\Persistence\Eloquent\GrowBizTaskAssignmentModel::where('task_id', $taskId)
            ->pluck('employee_id')
            ->toArray();
        
        return !empty(array_intersect($assignedEmployeeIds, $context['accessibleEmployeeIds']));
    }
}
