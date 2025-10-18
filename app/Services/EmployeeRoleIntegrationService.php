<?php

namespace App\Services;

use App\Domain\Employee\Constants\EmployeePermissions;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmployeeRoleIntegrationService
{
    /**
     * Extend existing roles with employee management permissions.
     */
    public function extendExistingRoles(): void
    {
        DB::transaction(function () {
            $this->extendHRManagerRole();
            $this->extendDepartmentHeadRole();
            $this->extendEmployeeRole();
            $this->createFieldAgentRole();
            $this->extendAdminRole();
        });
    }

    /**
     * Extend HR Manager role with full employee management permissions.
     */
    private function extendHRManagerRole(): void
    {
        $hrManager = Role::where('name', 'HR Manager')->first();
        
        if ($hrManager) {
            $permissions = [
                EmployeePermissions::VIEW_EMPLOYEES,
                EmployeePermissions::CREATE_EMPLOYEES,
                EmployeePermissions::EDIT_EMPLOYEES,
                EmployeePermissions::DELETE_EMPLOYEES,
                EmployeePermissions::VIEW_DEPARTMENTS,
                EmployeePermissions::CREATE_DEPARTMENTS,
                EmployeePermissions::EDIT_DEPARTMENTS,
                EmployeePermissions::DELETE_DEPARTMENTS,
                EmployeePermissions::VIEW_POSITIONS,
                EmployeePermissions::CREATE_POSITIONS,
                EmployeePermissions::EDIT_POSITIONS,
                EmployeePermissions::DELETE_POSITIONS,
                EmployeePermissions::VIEW_PERFORMANCE,
                EmployeePermissions::CREATE_PERFORMANCE_REVIEWS,
                EmployeePermissions::EDIT_PERFORMANCE_REVIEWS,
                EmployeePermissions::DELETE_PERFORMANCE_REVIEWS,
                EmployeePermissions::VIEW_ALL_PERFORMANCE,
                EmployeePermissions::VIEW_COMMISSIONS,
                EmployeePermissions::CREATE_COMMISSIONS,
                EmployeePermissions::EDIT_COMMISSIONS,
                EmployeePermissions::DELETE_COMMISSIONS,
                EmployeePermissions::MARK_COMMISSIONS_PAID,
                EmployeePermissions::VIEW_PAYROLL,
                EmployeePermissions::PROCESS_PAYROLL,
                EmployeePermissions::VIEW_ALL_PAYROLL,
                EmployeePermissions::VIEW_CLIENT_ASSIGNMENTS,
                EmployeePermissions::MANAGE_CLIENT_ASSIGNMENTS,
                EmployeePermissions::VIEW_DEPARTMENT_EMPLOYEES,
                EmployeePermissions::MANAGE_DEPARTMENT_EMPLOYEES,
            ];

            $hrManager->givePermissionTo($permissions);
        }
    }

    /**
     * Extend Department Head role with department-specific permissions.
     */
    private function extendDepartmentHeadRole(): void
    {
        $departmentHead = Role::where('name', 'Department Head')->first();
        
        if ($departmentHead) {
            $permissions = [
                EmployeePermissions::VIEW_EMPLOYEES,
                EmployeePermissions::VIEW_DEPARTMENTS,
                EmployeePermissions::VIEW_POSITIONS,
                EmployeePermissions::VIEW_PERFORMANCE,
                EmployeePermissions::CREATE_PERFORMANCE_REVIEWS,
                EmployeePermissions::EDIT_PERFORMANCE_REVIEWS,
                EmployeePermissions::VIEW_COMMISSIONS,
                EmployeePermissions::VIEW_PAYROLL,
                EmployeePermissions::VIEW_DEPARTMENT_EMPLOYEES,
                EmployeePermissions::MANAGE_DEPARTMENT_EMPLOYEES,
            ];

            $departmentHead->givePermissionTo($permissions);
        }
    }

    /**
     * Extend Employee role with basic self-service permissions.
     */
    private function extendEmployeeRole(): void
    {
        $employee = Role::where('name', 'Employee')->first();
        
        if ($employee) {
            $permissions = [
                EmployeePermissions::VIEW_PERFORMANCE,
                EmployeePermissions::VIEW_COMMISSIONS,
                EmployeePermissions::VIEW_PAYROLL,
            ];

            $employee->givePermissionTo($permissions);
        }
    }

    /**
     * Create Field Agent role with client-focused permissions.
     */
    private function createFieldAgentRole(): void
    {
        $fieldAgent = Role::firstOrCreate([
            'name' => 'Field Agent',
            'guard_name' => 'web',
        ], [
            'slug' => 'field-agent',
            'description' => 'Field agents with client portfolio access',
            'is_active' => true,
        ]);

        $permissions = [
            EmployeePermissions::VIEW_PERFORMANCE,
            EmployeePermissions::VIEW_COMMISSIONS,
            EmployeePermissions::VIEW_PAYROLL,
            EmployeePermissions::VIEW_CLIENT_ASSIGNMENTS,
        ];

        $fieldAgent->givePermissionTo($permissions);
    }

    /**
     * Extend Admin role with all employee management permissions.
     */
    private function extendAdminRole(): void
    {
        $admin = Role::where('name', 'Admin')->first();
        
        if ($admin) {
            $admin->givePermissionTo(EmployeePermissions::getAllPermissions());
        }
    }

    /**
     * Assign Field Agent role to users based on criteria.
     */
    public function assignFieldAgentRoles(): void
    {
        // This method can be used to automatically assign Field Agent roles
        // based on specific criteria, such as existing user attributes or
        // employee records with specific positions
        
        $fieldAgentRole = Role::where('name', 'Field Agent')->first();
        
        if (!$fieldAgentRole) {
            return;
        }

        // Example: Assign Field Agent role to users who have employee records
        // with positions that include "Agent" or "Sales" in the title
        $users = User::whereHas('employee.position', function ($query) {
            $query->where('title', 'like', '%Agent%')
                  ->orWhere('title', 'like', '%Sales%')
                  ->orWhere('title', 'like', '%Field%');
        })->get();

        foreach ($users as $user) {
            if (!$user->hasRole('Field Agent')) {
                $user->assignRole($fieldAgentRole);
            }
        }
    }

    /**
     * Migrate existing role permissions to new structure.
     */
    public function migrateExistingPermissions(): void
    {
        // Map old permissions to new employee permissions
        $permissionMappings = [
            'view_users' => [EmployeePermissions::VIEW_EMPLOYEES],
            'create_users' => [EmployeePermissions::CREATE_EMPLOYEES],
            'edit_users' => [EmployeePermissions::EDIT_EMPLOYEES],
            'delete_users' => [EmployeePermissions::DELETE_EMPLOYEES],
            'manage_departments' => [
                EmployeePermissions::VIEW_DEPARTMENTS,
                EmployeePermissions::CREATE_DEPARTMENTS,
                EmployeePermissions::EDIT_DEPARTMENTS,
                EmployeePermissions::DELETE_DEPARTMENTS,
            ],
            'view_reports' => [
                EmployeePermissions::VIEW_PERFORMANCE,
                EmployeePermissions::VIEW_ALL_PERFORMANCE,
                EmployeePermissions::VIEW_PAYROLL,
                EmployeePermissions::VIEW_ALL_PAYROLL,
            ],
        ];

        foreach ($permissionMappings as $oldPermission => $newPermissions) {
            $roles = Role::whereHas('permissions', function ($query) use ($oldPermission) {
                $query->where('name', $oldPermission);
            })->get();

            foreach ($roles as $role) {
                $role->givePermissionTo($newPermissions);
            }
        }
    }

    /**
     * Validate role permission inheritance.
     */
    public function validateRoleHierarchy(): array
    {
        $issues = [];

        // Check that HR Manager has more permissions than Department Head
        $hrManager = Role::where('name', 'HR Manager')->first();
        $departmentHead = Role::where('name', 'Department Head')->first();

        if ($hrManager && $departmentHead) {
            $hrPermissions = $hrManager->permissions->pluck('name')->toArray();
            $deptPermissions = $departmentHead->permissions->pluck('name')->toArray();

            $missingPermissions = array_diff($deptPermissions, $hrPermissions);
            if (!empty($missingPermissions)) {
                $issues[] = "HR Manager is missing permissions that Department Head has: " . implode(', ', $missingPermissions);
            }
        }

        // Check that Department Head has more permissions than Employee
        $employee = Role::where('name', 'Employee')->first();

        if ($departmentHead && $employee) {
            $deptPermissions = $departmentHead->permissions->pluck('name')->toArray();
            $empPermissions = $employee->permissions->pluck('name')->toArray();

            $missingPermissions = array_diff($empPermissions, $deptPermissions);
            if (!empty($missingPermissions)) {
                $issues[] = "Department Head is missing permissions that Employee has: " . implode(', ', $missingPermissions);
            }
        }

        // Check that Admin has all permissions
        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            $adminPermissions = $admin->permissions->pluck('name')->toArray();
            $allEmployeePermissions = EmployeePermissions::getAllPermissions();

            $missingPermissions = array_diff($allEmployeePermissions, $adminPermissions);
            if (!empty($missingPermissions)) {
                $issues[] = "Admin is missing employee permissions: " . implode(', ', $missingPermissions);
            }
        }

        return $issues;
    }

    /**
     * Get role permission summary.
     */
    public function getRolePermissionSummary(): array
    {
        $roles = Role::with('permissions')->get();
        $summary = [];

        foreach ($roles as $role) {
            $employeePermissions = $role->permissions
                ->whereIn('name', EmployeePermissions::getAllPermissions())
                ->pluck('name')
                ->toArray();

            $summary[$role->name] = [
                'total_permissions' => $role->permissions->count(),
                'employee_permissions' => count($employeePermissions),
                'employee_permission_list' => $employeePermissions,
            ];
        }

        return $summary;
    }

    /**
     * Sync user roles based on employee records.
     */
    public function syncUserRolesWithEmployeeData(): void
    {
        // Sync roles based on employee position and department data
        $users = User::with(['employee.position', 'employee.department'])->get();

        foreach ($users as $user) {
            if (!$user->employee) {
                continue;
            }

            $this->assignRoleBasedOnPosition($user);
            $this->assignRoleBasedOnDepartment($user);
        }
    }

    /**
     * Assign role based on employee position.
     */
    private function assignRoleBasedOnPosition(User $user): void
    {
        $position = $user->employee->position;
        
        if (!$position) {
            return;
        }

        // Department heads should have Department Head role
        if ($position->is_department_head && !$user->hasRole('Department Head')) {
            $user->assignRole('Department Head');
        }

        // Field agents based on position title
        $fieldPositions = ['Field Agent', 'Sales Agent', 'Client Manager', 'Account Manager'];
        if (in_array($position->title, $fieldPositions) && !$user->hasRole('Field Agent')) {
            $user->assignRole('Field Agent');
        }

        // HR positions
        $hrPositions = ['HR Manager', 'Human Resources Manager', 'People Manager'];
        if (in_array($position->title, $hrPositions) && !$user->hasRole('HR Manager')) {
            $user->assignRole('HR Manager');
        }
    }

    /**
     * Assign role based on employee department.
     */
    private function assignRoleBasedOnDepartment(User $user): void
    {
        $department = $user->employee->department;
        
        if (!$department) {
            return;
        }

        // HR department employees should have appropriate HR permissions
        if (strtolower($department->name) === 'human resources' || 
            strtolower($department->name) === 'hr') {
            
            if (!$user->hasRole(['HR Manager', 'Department Head'])) {
                // Give them at least employee role with some HR permissions
                if (!$user->hasRole('Employee')) {
                    $user->assignRole('Employee');
                }
                
                // Add specific HR-related permissions
                $user->givePermissionTo([
                    EmployeePermissions::VIEW_EMPLOYEES,
                    EmployeePermissions::VIEW_DEPARTMENTS,
                    EmployeePermissions::VIEW_POSITIONS,
                ]);
            }
        }
    }
}