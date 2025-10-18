<?php

namespace Database\Seeders;

use App\Domain\Employee\Constants\EmployeePermissions;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class EmployeePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create all employee management permissions
        $this->createPermissions();
        
        // Create roles and assign permissions
        $this->createRolesAndAssignPermissions();
    }
    
    /**
     * Create all employee management permissions
     */
    private function createPermissions(): void
    {
        $permissionsByCategory = EmployeePermissions::getPermissionsByCategory();
        
        foreach ($permissionsByCategory as $category => $permissions) {
            foreach ($permissions as $permission) {
                Permission::findOrCreate($permission, 'web');
            }
        }
    }
    
    /**
     * Create roles and assign appropriate permissions
     */
    private function createRolesAndAssignPermissions(): void
    {
        // HR Manager - Full access to employee management
        $hrManager = Role::findOrCreate('HR Manager', 'web');
        $hrManager->syncPermissions([
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
        ]);
        
        // Department Head - Limited to department-specific operations
        $departmentHead = Role::findOrCreate('Department Head', 'web');
        $departmentHead->syncPermissions([
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
        ]);
        
        // Employee - Basic view permissions for own data
        $employee = Role::findOrCreate('Employee', 'web');
        $employee->syncPermissions([
            EmployeePermissions::VIEW_PERFORMANCE,
            EmployeePermissions::VIEW_COMMISSIONS,
            EmployeePermissions::VIEW_PAYROLL,
        ]);
        
        // Field Agent - Client-focused permissions
        $fieldAgent = Role::findOrCreate('Field Agent', 'web');
        $fieldAgent->syncPermissions([
            EmployeePermissions::VIEW_PERFORMANCE,
            EmployeePermissions::VIEW_COMMISSIONS,
            EmployeePermissions::VIEW_PAYROLL,
            EmployeePermissions::VIEW_CLIENT_ASSIGNMENTS,
        ]);
        
        // Admin - All permissions (if not already exists)
        $admin = Role::findOrCreate('Admin', 'web');
        $admin->syncPermissions(EmployeePermissions::getAllPermissions());
    }
}