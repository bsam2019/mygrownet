<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;

class CommissionPolicy
{
    /**
     * Determine if the user can view commission analytics
     */
    public function viewCommissionAnalytics(User $user): bool
    {
        return $user->hasAnyPermission([
            'viewCommissionAnalytics',
            'manage-employees',
            'admin-access'
        ]);
    }

    /**
     * Determine if the user can calculate commissions
     */
    public function calculate(User $user): bool
    {
        return $user->hasAnyPermission([
            'calculate-commissions',
            'manage-employees',
            'admin-access'
        ]);
    }

    /**
     * Determine if the user can create commission records
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            'create-commissions',
            'manage-employees',
            'admin-access'
        ]);
    }

    /**
     * Determine if the user can approve commissions
     */
    public function approve(User $user): bool
    {
        return $user->hasAnyPermission([
            'approve-commissions',
            'manage-employees',
            'admin-access'
        ]);
    }

    /**
     * Determine if the user can mark commissions as paid
     */
    public function markPaid(User $user): bool
    {
        return $user->hasAnyPermission([
            'mark-commissions-paid',
            'manage-employees',
            'admin-access'
        ]);
    }

    /**
     * Determine if the user can view their own commission dashboard
     */
    public function viewOwn(User $user): bool
    {
        // Check if user has an employee record
        $employee = EmployeeModel::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return false;
        }

        // Check if employee is eligible for commissions
        return $employee->position && $employee->position->commission_eligible;
    }

    /**
     * Determine if the user can view a specific commission record
     */
    public function view(User $user, EmployeeCommissionModel $commission): bool
    {
        // Users can view their own commissions
        $employee = EmployeeModel::where('user_id', $user->id)->first();
        if ($employee && $commission->employee_id === $employee->id) {
            return true;
        }

        // Managers can view commissions for their department
        if ($user->hasPermissionTo('manage-employees')) {
            $userEmployee = EmployeeModel::where('user_id', $user->id)->first();
            if ($userEmployee && $userEmployee->department_id === $commission->employee->department_id) {
                return true;
            }
        }

        // Admins can view all commissions
        return $user->hasPermissionTo('admin-access');
    }

    /**
     * Determine if the user can update a commission record
     */
    public function update(User $user, EmployeeCommissionModel $commission): bool
    {
        // Only managers and admins can update commissions
        return $user->hasAnyPermission([
            'manage-employees',
            'admin-access'
        ]);
    }

    /**
     * Determine if the user can delete a commission record
     */
    public function delete(User $user, EmployeeCommissionModel $commission): bool
    {
        // Only admins can delete commission records
        return $user->hasPermissionTo('admin-access');
    }

    /**
     * Determine if the user can generate commission reports
     */
    public function generateReports(User $user): bool
    {
        return $user->hasAnyPermission([
            'generate-commission-reports',
            'manage-employees',
            'admin-access'
        ]);
    }

    /**
     * Determine if the user can view commission reports for a specific employee
     */
    public function viewEmployeeReports(User $user, EmployeeModel $employee): bool
    {
        // Users can view their own reports
        $userEmployee = EmployeeModel::where('user_id', $user->id)->first();
        if ($userEmployee && $userEmployee->id === $employee->id) {
            return true;
        }

        // Managers can view reports for their department
        if ($user->hasPermissionTo('manage-employees')) {
            if ($userEmployee && $userEmployee->department_id === $employee->department_id) {
                return true;
            }
        }

        // Admins can view all reports
        return $user->hasPermissionTo('admin-access');
    }
}