<?php

namespace App\Http\Middleware;

use App\Domain\Employee\Constants\EmployeePermissions;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckEmployeeAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $action = 'view'): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Get the employee being accessed
        $employee = $this->getEmployeeFromRequest($request);
        
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        // Check if user can perform the action on this employee
        if (!$this->canPerformAction($user, $employee, $action)) {
            return response()->json(['message' => 'Forbidden - Insufficient employee access'], 403);
        }

        // Add employee to request for use in controllers
        $request->merge(['accessed_employee' => $employee]);

        return $next($request);
    }

    /**
     * Get employee from request parameters.
     */
    private function getEmployeeFromRequest(Request $request): ?EmployeeModel
    {
        // Try route parameters
        if ($request->route('employee')) {
            $employeeId = $request->route('employee');
            return EmployeeModel::find($employeeId);
        }

        if ($request->route('employeeId')) {
            $employeeId = $request->route('employeeId');
            return EmployeeModel::find($employeeId);
        }

        // Try query parameters
        if ($request->query('employee_id')) {
            $employeeId = $request->query('employee_id');
            return EmployeeModel::find($employeeId);
        }

        // Try request body
        if ($request->input('employee_id')) {
            $employeeId = $request->input('employee_id');
            return EmployeeModel::find($employeeId);
        }

        return null;
    }

    /**
     * Check if user can perform the specified action on the employee.
     */
    private function canPerformAction($user, EmployeeModel $employee, string $action): bool
    {
        switch ($action) {
            case 'view':
                return $this->canView($user, $employee);
            case 'edit':
            case 'update':
                return $this->canEdit($user, $employee);
            case 'delete':
                return $this->canDelete($user, $employee);
            case 'view_performance':
                return $this->canViewPerformance($user, $employee);
            case 'manage_performance':
                return $this->canManagePerformance($user, $employee);
            case 'view_commissions':
                return $this->canViewCommissions($user, $employee);
            case 'manage_commissions':
                return $this->canManageCommissions($user, $employee);
            case 'view_payroll':
                return $this->canViewPayroll($user, $employee);
            case 'view_client_assignments':
                return $this->canViewClientAssignments($user, $employee);
            case 'manage_client_assignments':
                return $this->canManageClientAssignments($user, $employee);
            default:
                return false;
        }
    }

    /**
     * Check if user can view the employee.
     */
    private function canView($user, EmployeeModel $employee): bool
    {
        // Users can view their own record
        if ($user->id === $employee->user_id) {
            return true;
        }

        // Global view permission
        if ($user->hasPermissionTo(EmployeePermissions::VIEW_EMPLOYEES)) {
            return true;
        }

        // Department-level access
        if ($user->hasPermissionTo(EmployeePermissions::VIEW_DEPARTMENT_EMPLOYEES)) {
            return $this->isSameDepartment($user, $employee);
        }

        return false;
    }

    /**
     * Check if user can edit the employee.
     */
    private function canEdit($user, EmployeeModel $employee): bool
    {
        // Users can edit their own basic information
        if ($user->id === $employee->user_id) {
            return true;
        }

        // Global edit permission
        if ($user->hasPermissionTo(EmployeePermissions::EDIT_EMPLOYEES)) {
            return true;
        }

        // Department managers can edit their department employees
        if ($user->hasPermissionTo(EmployeePermissions::MANAGE_DEPARTMENT_EMPLOYEES)) {
            return $this->isSameDepartment($user, $employee) && $this->isDepartmentHead($user);
        }

        return false;
    }

    /**
     * Check if user can delete the employee.
     */
    private function canDelete($user, EmployeeModel $employee): bool
    {
        return $user->hasPermissionTo(EmployeePermissions::DELETE_EMPLOYEES);
    }

    /**
     * Check if user can view employee performance.
     */
    private function canViewPerformance($user, EmployeeModel $employee): bool
    {
        // Users can view their own performance
        if ($user->id === $employee->user_id) {
            return $user->hasPermissionTo(EmployeePermissions::VIEW_PERFORMANCE);
        }

        // Global performance view permission
        if ($user->hasPermissionTo(EmployeePermissions::VIEW_ALL_PERFORMANCE)) {
            return true;
        }

        // Department-level performance access
        if ($user->hasPermissionTo(EmployeePermissions::VIEW_PERFORMANCE)) {
            return $this->isSameDepartment($user, $employee);
        }

        return false;
    }

    /**
     * Check if user can manage employee performance.
     */
    private function canManagePerformance($user, EmployeeModel $employee): bool
    {
        if (!$user->hasPermissionTo(EmployeePermissions::CREATE_PERFORMANCE_REVIEWS)) {
            return false;
        }

        // Global permission
        if ($user->hasPermissionTo(EmployeePermissions::EDIT_EMPLOYEES)) {
            return true;
        }

        // Department heads can manage performance of their department employees
        if ($user->hasPermissionTo(EmployeePermissions::MANAGE_DEPARTMENT_EMPLOYEES)) {
            return $this->isSameDepartment($user, $employee) && $this->isDepartmentHead($user);
        }

        return false;
    }

    /**
     * Check if user can view employee commissions.
     */
    private function canViewCommissions($user, EmployeeModel $employee): bool
    {
        // Users can view their own commissions
        if ($user->id === $employee->user_id) {
            return $user->hasPermissionTo(EmployeePermissions::VIEW_COMMISSIONS);
        }

        // Global commission access
        if ($user->hasPermissionTo(EmployeePermissions::VIEW_COMMISSIONS) && 
            $user->hasPermissionTo(EmployeePermissions::EDIT_EMPLOYEES)) {
            return true;
        }

        // Department-level commission access
        if ($user->hasPermissionTo(EmployeePermissions::VIEW_COMMISSIONS) &&
            $user->hasPermissionTo(EmployeePermissions::VIEW_DEPARTMENT_EMPLOYEES)) {
            return $this->isSameDepartment($user, $employee);
        }

        return false;
    }

    /**
     * Check if user can manage employee commissions.
     */
    private function canManageCommissions($user, EmployeeModel $employee): bool
    {
        return $user->hasPermissionTo(EmployeePermissions::CREATE_COMMISSIONS) ||
               $user->hasPermissionTo(EmployeePermissions::EDIT_COMMISSIONS) ||
               $user->hasPermissionTo(EmployeePermissions::MARK_COMMISSIONS_PAID);
    }

    /**
     * Check if user can view employee payroll.
     */
    private function canViewPayroll($user, EmployeeModel $employee): bool
    {
        // Users can view their own payroll
        if ($user->id === $employee->user_id) {
            return $user->hasPermissionTo(EmployeePermissions::VIEW_PAYROLL);
        }

        // Global payroll access
        if ($user->hasPermissionTo(EmployeePermissions::VIEW_ALL_PAYROLL)) {
            return true;
        }

        // Department-level payroll access
        if ($user->hasPermissionTo(EmployeePermissions::VIEW_PAYROLL)) {
            return $this->isSameDepartment($user, $employee);
        }

        return false;
    }

    /**
     * Check if user can view client assignments.
     */
    private function canViewClientAssignments($user, EmployeeModel $employee): bool
    {
        // Field agents can view their own assignments
        if ($user->id === $employee->user_id) {
            return $user->hasPermissionTo(EmployeePermissions::VIEW_CLIENT_ASSIGNMENTS);
        }

        // Managers can view assignments
        return $user->hasPermissionTo(EmployeePermissions::MANAGE_CLIENT_ASSIGNMENTS);
    }

    /**
     * Check if user can manage client assignments.
     */
    private function canManageClientAssignments($user, EmployeeModel $employee): bool
    {
        return $user->hasPermissionTo(EmployeePermissions::MANAGE_CLIENT_ASSIGNMENTS);
    }

    /**
     * Check if user and employee are in the same department.
     */
    private function isSameDepartment($user, EmployeeModel $employee): bool
    {
        $userEmployee = EmployeeModel::where('user_id', $user->id)->first();
        
        return $userEmployee && $userEmployee->department_id === $employee->department_id;
    }

    /**
     * Check if user is a department head.
     */
    private function isDepartmentHead($user): bool
    {
        $userEmployee = EmployeeModel::where('user_id', $user->id)->with('position')->first();
        
        return $userEmployee && 
               $userEmployee->position && 
               $userEmployee->position->is_department_head;
    }
}