<?php

namespace App\Policies;

use App\Domain\Employee\Constants\EmployeePermissions;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any employees.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(EmployeePermissions::VIEW_EMPLOYEES);
    }

    /**
     * Determine whether the user can view the employee.
     */
    public function view(User $user, EmployeeModel $employee): bool
    {
        // Users can always view their own employee record
        if ($user->id === $employee->user_id) {
            return true;
        }

        // Check if user has general view permission
        if ($user->hasPermissionTo(EmployeePermissions::VIEW_EMPLOYEES)) {
            return true;
        }

        // Department heads can view employees in their department
        if ($user->hasPermissionTo(EmployeePermissions::VIEW_DEPARTMENT_EMPLOYEES)) {
            return $this->canAccessDepartmentEmployee($user, $employee);
        }

        return false;
    }

    /**
     * Determine whether the user can create employees.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(EmployeePermissions::CREATE_EMPLOYEES);
    }

    /**
     * Determine whether the user can update the employee.
     */
    public function update(User $user, EmployeeModel $employee): bool
    {
        // Users can update their own basic information (limited fields)
        if ($user->id === $employee->user_id) {
            return true;
        }

        // Check if user has general edit permission
        if ($user->hasPermissionTo(EmployeePermissions::EDIT_EMPLOYEES)) {
            return true;
        }

        // Department heads can edit employees in their department
        if ($user->hasPermissionTo(EmployeePermissions::MANAGE_DEPARTMENT_EMPLOYEES)) {
            return $this->canAccessDepartmentEmployee($user, $employee);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the employee.
     */
    public function delete(User $user, EmployeeModel $employee): bool
    {
        // Only users with explicit delete permission can delete employees
        return $user->hasPermissionTo(EmployeePermissions::DELETE_EMPLOYEES);
    }

    /**
     * Determine whether the user can view employee performance data.
     */
    public function viewPerformance(User $user, EmployeeModel $employee): bool
    {
        // Users can view their own performance
        if ($user->id === $employee->user_id) {
            return $user->hasPermissionTo(EmployeePermissions::VIEW_PERFORMANCE);
        }

        // Check if user has permission to view all performance data
        if ($user->hasPermissionTo(EmployeePermissions::VIEW_ALL_PERFORMANCE)) {
            return true;
        }

        // Department heads can view performance of their department employees
        if ($user->hasPermissionTo(EmployeePermissions::VIEW_PERFORMANCE)) {
            return $this->canAccessDepartmentEmployee($user, $employee);
        }

        return false;
    }

    /**
     * Determine whether the user can create performance reviews.
     */
    public function createPerformanceReview(User $user, EmployeeModel $employee): bool
    {
        if (!$user->hasPermissionTo(EmployeePermissions::CREATE_PERFORMANCE_REVIEWS)) {
            return false;
        }

        // Department heads can create reviews for their department employees
        if ($user->hasPermissionTo(EmployeePermissions::MANAGE_DEPARTMENT_EMPLOYEES)) {
            return $this->canAccessDepartmentEmployee($user, $employee);
        }

        // HR managers can create reviews for any employee
        return $user->hasPermissionTo(EmployeePermissions::EDIT_EMPLOYEES);
    }

    /**
     * Determine whether the user can view employee commissions.
     */
    public function viewCommissions(User $user, EmployeeModel $employee): bool
    {
        // Users can view their own commissions
        if ($user->id === $employee->user_id) {
            return $user->hasPermissionTo(EmployeePermissions::VIEW_COMMISSIONS);
        }

        // Check if user has general commission view permission
        if ($user->hasPermissionTo(EmployeePermissions::VIEW_COMMISSIONS)) {
            // Department heads can view commissions of their department employees
            if ($user->hasPermissionTo(EmployeePermissions::VIEW_DEPARTMENT_EMPLOYEES)) {
                return $this->canAccessDepartmentEmployee($user, $employee);
            }

            // HR managers can view all commissions
            return $user->hasPermissionTo(EmployeePermissions::EDIT_EMPLOYEES);
        }

        return false;
    }

    /**
     * Determine whether the user can manage employee commissions.
     */
    public function manageCommissions(User $user, EmployeeModel $employee): bool
    {
        return $user->hasPermissionTo(EmployeePermissions::CREATE_COMMISSIONS) ||
               $user->hasPermissionTo(EmployeePermissions::EDIT_COMMISSIONS) ||
               $user->hasPermissionTo(EmployeePermissions::MARK_COMMISSIONS_PAID);
    }

    /**
     * Determine whether the user can view employee payroll.
     */
    public function viewPayroll(User $user, EmployeeModel $employee): bool
    {
        // Users can view their own payroll
        if ($user->id === $employee->user_id) {
            return $user->hasPermissionTo(EmployeePermissions::VIEW_PAYROLL);
        }

        // Check if user has permission to view all payroll data
        if ($user->hasPermissionTo(EmployeePermissions::VIEW_ALL_PAYROLL)) {
            return true;
        }

        // Department heads can view payroll of their department employees
        if ($user->hasPermissionTo(EmployeePermissions::VIEW_PAYROLL)) {
            return $this->canAccessDepartmentEmployee($user, $employee);
        }

        return false;
    }

    /**
     * Determine whether the user can process payroll.
     */
    public function processPayroll(User $user): bool
    {
        return $user->hasPermissionTo(EmployeePermissions::PROCESS_PAYROLL);
    }

    /**
     * Determine whether the user can view client assignments.
     */
    public function viewClientAssignments(User $user, EmployeeModel $employee): bool
    {
        // Field agents can view their own client assignments
        if ($user->id === $employee->user_id) {
            return $user->hasPermissionTo(EmployeePermissions::VIEW_CLIENT_ASSIGNMENTS);
        }

        // Managers can view client assignments of their team
        return $user->hasPermissionTo(EmployeePermissions::MANAGE_CLIENT_ASSIGNMENTS);
    }

    /**
     * Determine whether the user can manage client assignments.
     */
    public function manageClientAssignments(User $user): bool
    {
        return $user->hasPermissionTo(EmployeePermissions::MANAGE_CLIENT_ASSIGNMENTS);
    }

    /**
     * Check if the user can access an employee from their department.
     */
    private function canAccessDepartmentEmployee(User $user, EmployeeModel $employee): bool
    {
        // Get the user's employee record to check their department
        $userEmployee = EmployeeModel::where('user_id', $user->id)->first();
        
        if (!$userEmployee) {
            return false;
        }

        // Check if they're in the same department
        if ($userEmployee->department_id === $employee->department_id) {
            return true;
        }

        // Check if the user is a department head of the employee's department
        if ($userEmployee->position && $userEmployee->position->is_department_head) {
            return $userEmployee->department_id === $employee->department_id;
        }

        return false;
    }

    /**
     * Determine if the user can view employees from a specific department.
     */
    public function viewDepartmentEmployees(User $user, int $departmentId): bool
    {
        if ($user->hasPermissionTo(EmployeePermissions::VIEW_EMPLOYEES)) {
            return true;
        }

        if ($user->hasPermissionTo(EmployeePermissions::VIEW_DEPARTMENT_EMPLOYEES)) {
            $userEmployee = EmployeeModel::where('user_id', $user->id)->first();
            return $userEmployee && $userEmployee->department_id === $departmentId;
        }

        return false;
    }

    /**
     * Determine if the user can manage employees from a specific department.
     */
    public function manageDepartmentEmployees(User $user, int $departmentId): bool
    {
        if ($user->hasPermissionTo(EmployeePermissions::EDIT_EMPLOYEES)) {
            return true;
        }

        if ($user->hasPermissionTo(EmployeePermissions::MANAGE_DEPARTMENT_EMPLOYEES)) {
            $userEmployee = EmployeeModel::where('user_id', $user->id)->first();
            return $userEmployee && 
                   $userEmployee->department_id === $departmentId &&
                   $userEmployee->position && 
                   $userEmployee->position->is_department_head;
        }

        return false;
    }
}