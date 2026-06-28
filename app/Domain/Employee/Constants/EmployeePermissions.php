<?php

namespace App\Domain\Employee\Constants;

class EmployeePermissions
{
    // Employee Management Permissions
    public const VIEW_EMPLOYEES = 'view_employees';
    public const CREATE_EMPLOYEES = 'create_employees';
    public const EDIT_EMPLOYEES = 'edit_employees';
    public const DELETE_EMPLOYEES = 'delete_employees';
    
    // Department Management Permissions
    public const VIEW_DEPARTMENTS = 'view_departments';
    public const CREATE_DEPARTMENTS = 'create_departments';
    public const EDIT_DEPARTMENTS = 'edit_departments';
    public const DELETE_DEPARTMENTS = 'delete_departments';
    
    // Position Management Permissions
    public const VIEW_POSITIONS = 'view_positions';
    public const CREATE_POSITIONS = 'create_positions';
    public const EDIT_POSITIONS = 'edit_positions';
    public const DELETE_POSITIONS = 'delete_positions';
    
    // Performance Management Permissions
    public const VIEW_PERFORMANCE = 'view_performance';
    public const CREATE_PERFORMANCE_REVIEWS = 'create_performance_reviews';
    public const EDIT_PERFORMANCE_REVIEWS = 'edit_performance_reviews';
    public const DELETE_PERFORMANCE_REVIEWS = 'delete_performance_reviews';
    public const VIEW_ALL_PERFORMANCE = 'view_all_performance';
    
    // Commission Management Permissions
    public const VIEW_COMMISSIONS = 'view_commissions';
    public const CREATE_COMMISSIONS = 'create_commissions';
    public const EDIT_COMMISSIONS = 'edit_commissions';
    public const DELETE_COMMISSIONS = 'delete_commissions';
    public const MARK_COMMISSIONS_PAID = 'mark_commissions_paid';
    
    // Payroll Permissions
    public const VIEW_PAYROLL = 'view_payroll';
    public const PROCESS_PAYROLL = 'process_payroll';
    public const VIEW_ALL_PAYROLL = 'view_all_payroll';
    
    // Client Assignment Permissions (for Field Agents)
    public const VIEW_CLIENT_ASSIGNMENTS = 'view_client_assignments';
    public const MANAGE_CLIENT_ASSIGNMENTS = 'manage_client_assignments';
    
    // Department-specific Permissions
    public const VIEW_DEPARTMENT_EMPLOYEES = 'view_department_employees';
    public const MANAGE_DEPARTMENT_EMPLOYEES = 'manage_department_employees';
    
    /**
     * Get all employee management permissions
     */
    public static function getAllPermissions(): array
    {
        return [
            self::VIEW_EMPLOYEES,
            self::CREATE_EMPLOYEES,
            self::EDIT_EMPLOYEES,
            self::DELETE_EMPLOYEES,
            self::VIEW_DEPARTMENTS,
            self::CREATE_DEPARTMENTS,
            self::EDIT_DEPARTMENTS,
            self::DELETE_DEPARTMENTS,
            self::VIEW_POSITIONS,
            self::CREATE_POSITIONS,
            self::EDIT_POSITIONS,
            self::DELETE_POSITIONS,
            self::VIEW_PERFORMANCE,
            self::CREATE_PERFORMANCE_REVIEWS,
            self::EDIT_PERFORMANCE_REVIEWS,
            self::DELETE_PERFORMANCE_REVIEWS,
            self::VIEW_ALL_PERFORMANCE,
            self::VIEW_COMMISSIONS,
            self::CREATE_COMMISSIONS,
            self::EDIT_COMMISSIONS,
            self::DELETE_COMMISSIONS,
            self::MARK_COMMISSIONS_PAID,
            self::VIEW_PAYROLL,
            self::PROCESS_PAYROLL,
            self::VIEW_ALL_PAYROLL,
            self::VIEW_CLIENT_ASSIGNMENTS,
            self::MANAGE_CLIENT_ASSIGNMENTS,
            self::VIEW_DEPARTMENT_EMPLOYEES,
            self::MANAGE_DEPARTMENT_EMPLOYEES,
        ];
    }
    
    /**
     * Get permissions grouped by category
     */
    public static function getPermissionsByCategory(): array
    {
        return [
            'Employee Management' => [
                self::VIEW_EMPLOYEES,
                self::CREATE_EMPLOYEES,
                self::EDIT_EMPLOYEES,
                self::DELETE_EMPLOYEES,
            ],
            'Department Management' => [
                self::VIEW_DEPARTMENTS,
                self::CREATE_DEPARTMENTS,
                self::EDIT_DEPARTMENTS,
                self::DELETE_DEPARTMENTS,
                self::VIEW_DEPARTMENT_EMPLOYEES,
                self::MANAGE_DEPARTMENT_EMPLOYEES,
            ],
            'Position Management' => [
                self::VIEW_POSITIONS,
                self::CREATE_POSITIONS,
                self::EDIT_POSITIONS,
                self::DELETE_POSITIONS,
            ],
            'Performance Management' => [
                self::VIEW_PERFORMANCE,
                self::CREATE_PERFORMANCE_REVIEWS,
                self::EDIT_PERFORMANCE_REVIEWS,
                self::DELETE_PERFORMANCE_REVIEWS,
                self::VIEW_ALL_PERFORMANCE,
            ],
            'Commission Management' => [
                self::VIEW_COMMISSIONS,
                self::CREATE_COMMISSIONS,
                self::EDIT_COMMISSIONS,
                self::DELETE_COMMISSIONS,
                self::MARK_COMMISSIONS_PAID,
            ],
            'Payroll Management' => [
                self::VIEW_PAYROLL,
                self::PROCESS_PAYROLL,
                self::VIEW_ALL_PAYROLL,
            ],
            'Client Management' => [
                self::VIEW_CLIENT_ASSIGNMENTS,
                self::MANAGE_CLIENT_ASSIGNMENTS,
            ],
        ];
    }
}