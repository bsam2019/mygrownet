<?php

namespace Tests\Unit\Domain\Employee\Constants;

use App\Domain\Employee\Constants\EmployeePermissions;
use PHPUnit\Framework\TestCase;

class EmployeePermissionsTest extends TestCase
{
    public function test_get_all_permissions_returns_complete_list(): void
    {
        $permissions = EmployeePermissions::getAllPermissions();
        
        $this->assertIsArray($permissions);
        $this->assertNotEmpty($permissions);
        
        // Verify key permissions are included
        $this->assertContains(EmployeePermissions::VIEW_EMPLOYEES, $permissions);
        $this->assertContains(EmployeePermissions::CREATE_EMPLOYEES, $permissions);
        $this->assertContains(EmployeePermissions::EDIT_EMPLOYEES, $permissions);
        $this->assertContains(EmployeePermissions::DELETE_EMPLOYEES, $permissions);
        $this->assertContains(EmployeePermissions::VIEW_DEPARTMENTS, $permissions);
        $this->assertContains(EmployeePermissions::VIEW_POSITIONS, $permissions);
        $this->assertContains(EmployeePermissions::VIEW_PERFORMANCE, $permissions);
        $this->assertContains(EmployeePermissions::VIEW_COMMISSIONS, $permissions);
        $this->assertContains(EmployeePermissions::VIEW_PAYROLL, $permissions);
    }
    
    public function test_get_permissions_by_category_returns_organized_structure(): void
    {
        $categorizedPermissions = EmployeePermissions::getPermissionsByCategory();
        
        $this->assertIsArray($categorizedPermissions);
        $this->assertArrayHasKey('Employee Management', $categorizedPermissions);
        $this->assertArrayHasKey('Department Management', $categorizedPermissions);
        $this->assertArrayHasKey('Position Management', $categorizedPermissions);
        $this->assertArrayHasKey('Performance Management', $categorizedPermissions);
        $this->assertArrayHasKey('Commission Management', $categorizedPermissions);
        $this->assertArrayHasKey('Payroll Management', $categorizedPermissions);
        $this->assertArrayHasKey('Client Management', $categorizedPermissions);
    }
    
    public function test_employee_management_category_contains_expected_permissions(): void
    {
        $categorizedPermissions = EmployeePermissions::getPermissionsByCategory();
        $employeeManagement = $categorizedPermissions['Employee Management'];
        
        $this->assertContains(EmployeePermissions::VIEW_EMPLOYEES, $employeeManagement);
        $this->assertContains(EmployeePermissions::CREATE_EMPLOYEES, $employeeManagement);
        $this->assertContains(EmployeePermissions::EDIT_EMPLOYEES, $employeeManagement);
        $this->assertContains(EmployeePermissions::DELETE_EMPLOYEES, $employeeManagement);
    }
    
    public function test_department_management_category_contains_expected_permissions(): void
    {
        $categorizedPermissions = EmployeePermissions::getPermissionsByCategory();
        $departmentManagement = $categorizedPermissions['Department Management'];
        
        $this->assertContains(EmployeePermissions::VIEW_DEPARTMENTS, $departmentManagement);
        $this->assertContains(EmployeePermissions::CREATE_DEPARTMENTS, $departmentManagement);
        $this->assertContains(EmployeePermissions::EDIT_DEPARTMENTS, $departmentManagement);
        $this->assertContains(EmployeePermissions::DELETE_DEPARTMENTS, $departmentManagement);
        $this->assertContains(EmployeePermissions::VIEW_DEPARTMENT_EMPLOYEES, $departmentManagement);
        $this->assertContains(EmployeePermissions::MANAGE_DEPARTMENT_EMPLOYEES, $departmentManagement);
    }
    
    public function test_performance_management_category_contains_expected_permissions(): void
    {
        $categorizedPermissions = EmployeePermissions::getPermissionsByCategory();
        $performanceManagement = $categorizedPermissions['Performance Management'];
        
        $this->assertContains(EmployeePermissions::VIEW_PERFORMANCE, $performanceManagement);
        $this->assertContains(EmployeePermissions::CREATE_PERFORMANCE_REVIEWS, $performanceManagement);
        $this->assertContains(EmployeePermissions::EDIT_PERFORMANCE_REVIEWS, $performanceManagement);
        $this->assertContains(EmployeePermissions::DELETE_PERFORMANCE_REVIEWS, $performanceManagement);
        $this->assertContains(EmployeePermissions::VIEW_ALL_PERFORMANCE, $performanceManagement);
    }
    
    public function test_commission_management_category_contains_expected_permissions(): void
    {
        $categorizedPermissions = EmployeePermissions::getPermissionsByCategory();
        $commissionManagement = $categorizedPermissions['Commission Management'];
        
        $this->assertContains(EmployeePermissions::VIEW_COMMISSIONS, $commissionManagement);
        $this->assertContains(EmployeePermissions::CREATE_COMMISSIONS, $commissionManagement);
        $this->assertContains(EmployeePermissions::EDIT_COMMISSIONS, $commissionManagement);
        $this->assertContains(EmployeePermissions::DELETE_COMMISSIONS, $commissionManagement);
        $this->assertContains(EmployeePermissions::MARK_COMMISSIONS_PAID, $commissionManagement);
    }
    
    public function test_payroll_management_category_contains_expected_permissions(): void
    {
        $categorizedPermissions = EmployeePermissions::getPermissionsByCategory();
        $payrollManagement = $categorizedPermissions['Payroll Management'];
        
        $this->assertContains(EmployeePermissions::VIEW_PAYROLL, $payrollManagement);
        $this->assertContains(EmployeePermissions::PROCESS_PAYROLL, $payrollManagement);
        $this->assertContains(EmployeePermissions::VIEW_ALL_PAYROLL, $payrollManagement);
    }
    
    public function test_client_management_category_contains_expected_permissions(): void
    {
        $categorizedPermissions = EmployeePermissions::getPermissionsByCategory();
        $clientManagement = $categorizedPermissions['Client Management'];
        
        $this->assertContains(EmployeePermissions::VIEW_CLIENT_ASSIGNMENTS, $clientManagement);
        $this->assertContains(EmployeePermissions::MANAGE_CLIENT_ASSIGNMENTS, $clientManagement);
    }
    
    public function test_all_categorized_permissions_are_included_in_all_permissions(): void
    {
        $allPermissions = EmployeePermissions::getAllPermissions();
        $categorizedPermissions = EmployeePermissions::getPermissionsByCategory();
        
        $flattenedCategorized = [];
        foreach ($categorizedPermissions as $permissions) {
            $flattenedCategorized = array_merge($flattenedCategorized, $permissions);
        }
        
        // All categorized permissions should be in the complete list
        foreach ($flattenedCategorized as $permission) {
            $this->assertContains($permission, $allPermissions);
        }
        
        // All permissions should be categorized
        $this->assertCount(count($allPermissions), $flattenedCategorized);
    }
}