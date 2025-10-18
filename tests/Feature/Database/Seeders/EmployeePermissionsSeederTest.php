<?php

namespace Tests\Feature\Database\Seeders;

use App\Domain\Employee\Constants\EmployeePermissions;
use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\EmployeePermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeePermissionsSeederTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_seeder_creates_all_employee_permissions(): void
    {
        $this->seed(EmployeePermissionsSeeder::class);
        
        $allPermissions = EmployeePermissions::getAllPermissions();
        
        foreach ($allPermissions as $permissionName) {
            $this->assertDatabaseHas('permissions', [
                'name' => $permissionName,
                'guard_name' => 'web'
            ]);
        }
    }
    
    public function test_seeder_creates_hr_manager_role_with_full_permissions(): void
    {
        $this->seed(EmployeePermissionsSeeder::class);
        
        $hrManager = Role::findByName('HR Manager', 'web');
        $this->assertNotNull($hrManager);
        
        // HR Manager should have all employee management permissions
        $expectedPermissions = [
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
        
        foreach ($expectedPermissions as $permission) {
            $this->assertTrue($hrManager->hasPermissionTo($permission));
        }
    }
    
    public function test_seeder_creates_department_head_role_with_limited_permissions(): void
    {
        $this->seed(EmployeePermissionsSeeder::class);
        
        $departmentHead = Role::findByName('Department Head', 'web');
        $this->assertNotNull($departmentHead);
        
        // Department Head should have limited permissions
        $expectedPermissions = [
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
        
        foreach ($expectedPermissions as $permission) {
            $this->assertTrue($departmentHead->hasPermissionTo($permission));
        }
        
        // Should NOT have delete permissions
        $this->assertFalse($departmentHead->hasPermissionTo(EmployeePermissions::DELETE_EMPLOYEES));
        $this->assertFalse($departmentHead->hasPermissionTo(EmployeePermissions::DELETE_DEPARTMENTS));
        $this->assertFalse($departmentHead->hasPermissionTo(EmployeePermissions::DELETE_POSITIONS));
    }
    
    public function test_seeder_creates_employee_role_with_basic_permissions(): void
    {
        $this->seed(EmployeePermissionsSeeder::class);
        
        $employee = Role::findByName('Employee', 'web');
        $this->assertNotNull($employee);
        
        // Employee should have basic view permissions
        $expectedPermissions = [
            EmployeePermissions::VIEW_PERFORMANCE,
            EmployeePermissions::VIEW_COMMISSIONS,
            EmployeePermissions::VIEW_PAYROLL,
        ];
        
        foreach ($expectedPermissions as $permission) {
            $this->assertTrue($employee->hasPermissionTo($permission));
        }
        
        // Should NOT have management permissions
        $this->assertFalse($employee->hasPermissionTo(EmployeePermissions::CREATE_EMPLOYEES));
        $this->assertFalse($employee->hasPermissionTo(EmployeePermissions::EDIT_EMPLOYEES));
        $this->assertFalse($employee->hasPermissionTo(EmployeePermissions::DELETE_EMPLOYEES));
    }
    
    public function test_seeder_creates_field_agent_role_with_client_permissions(): void
    {
        $this->seed(EmployeePermissionsSeeder::class);
        
        $fieldAgent = Role::findByName('Field Agent', 'web');
        $this->assertNotNull($fieldAgent);
        
        // Field Agent should have client-focused permissions
        $expectedPermissions = [
            EmployeePermissions::VIEW_PERFORMANCE,
            EmployeePermissions::VIEW_COMMISSIONS,
            EmployeePermissions::VIEW_PAYROLL,
            EmployeePermissions::VIEW_CLIENT_ASSIGNMENTS,
        ];
        
        foreach ($expectedPermissions as $permission) {
            $this->assertTrue($fieldAgent->hasPermissionTo($permission));
        }
        
        // Should NOT have management permissions
        $this->assertFalse($fieldAgent->hasPermissionTo(EmployeePermissions::MANAGE_CLIENT_ASSIGNMENTS));
        $this->assertFalse($fieldAgent->hasPermissionTo(EmployeePermissions::CREATE_EMPLOYEES));
    }
    
    public function test_seeder_creates_admin_role_with_all_permissions(): void
    {
        $this->seed(EmployeePermissionsSeeder::class);
        
        $admin = Role::findByName('Admin', 'web');
        $this->assertNotNull($admin);
        
        // Admin should have all permissions
        $allPermissions = EmployeePermissions::getAllPermissions();
        
        foreach ($allPermissions as $permission) {
            $this->assertTrue($admin->hasPermissionTo($permission));
        }
    }
    
    public function test_seeder_can_be_run_multiple_times_without_errors(): void
    {
        // Run seeder first time
        $this->seed(EmployeePermissionsSeeder::class);
        $initialPermissionCount = Permission::count();
        $initialRoleCount = Role::count();
        
        // Run seeder second time
        $this->seed(EmployeePermissionsSeeder::class);
        $finalPermissionCount = Permission::count();
        $finalRoleCount = Role::count();
        
        // Counts should remain the same (no duplicates)
        $this->assertEquals($initialPermissionCount, $finalPermissionCount);
        $this->assertEquals($initialRoleCount, $finalRoleCount);
    }
    
    public function test_all_permissions_are_created_with_web_guard(): void
    {
        $this->seed(EmployeePermissionsSeeder::class);
        
        $permissions = Permission::all();
        
        foreach ($permissions as $permission) {
            $this->assertEquals('web', $permission->guard_name);
        }
    }
    
    public function test_all_roles_are_created_with_web_guard(): void
    {
        $this->seed(EmployeePermissionsSeeder::class);
        
        $roles = Role::all();
        
        foreach ($roles as $role) {
            $this->assertEquals('web', $role->guard_name);
        }
    }
}