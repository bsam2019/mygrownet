<?php

namespace App\Http\Middleware;

use App\Domain\Employee\Constants\EmployeePermissions;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CheckDepartmentAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = null): ResponseAlias
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // If user has global permissions, allow access
        if ($this->hasGlobalAccess($user, $permission)) {
            return $next($request);
        }

        // Check department-specific access
        if ($this->hasDepartmentAccess($user, $request, $permission)) {
            return $next($request);
        }

        return response()->json(['message' => 'Forbidden - Insufficient department access'], 403);
    }

    /**
     * Check if user has global access permissions.
     */
    private function hasGlobalAccess($user, ?string $permission): bool
    {
        // Admin and HR Manager roles typically have global access
        if ($user->hasRole(['Admin', 'HR Manager'])) {
            return true;
        }

        // Check specific global permissions
        $globalPermissions = [
            EmployeePermissions::VIEW_EMPLOYEES,
            EmployeePermissions::EDIT_EMPLOYEES,
            EmployeePermissions::DELETE_EMPLOYEES,
            EmployeePermissions::VIEW_ALL_PERFORMANCE,
            EmployeePermissions::VIEW_ALL_PAYROLL,
        ];

        foreach ($globalPermissions as $globalPermission) {
            if ($user->hasPermissionTo($globalPermission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has department-specific access.
     */
    private function hasDepartmentAccess($user, Request $request, ?string $permission): bool
    {
        $userEmployee = EmployeeModel::where('user_id', $user->id)->first();
        
        if (!$userEmployee) {
            return false;
        }

        // Get department ID from request (could be route parameter, query param, or request body)
        $departmentId = $this->extractDepartmentId($request);
        
        if (!$departmentId) {
            // If no specific department is being accessed, check if user has department-level permissions
            return $this->hasDepartmentLevelPermissions($user, $permission);
        }

        // Check if user belongs to the same department
        if ($userEmployee->department_id !== $departmentId) {
            return false;
        }

        // Check if user has the required department-level permission
        return $this->hasDepartmentLevelPermissions($user, $permission);
    }

    /**
     * Extract department ID from the request.
     */
    private function extractDepartmentId(Request $request): ?int
    {
        // Try route parameters first
        if ($request->route('department')) {
            return (int) $request->route('department');
        }

        if ($request->route('departmentId')) {
            return (int) $request->route('departmentId');
        }

        // Try query parameters
        if ($request->query('department_id')) {
            return (int) $request->query('department_id');
        }

        // Try request body
        if ($request->input('department_id')) {
            return (int) $request->input('department_id');
        }

        // If accessing an employee, get their department
        if ($request->route('employee')) {
            $employeeId = $request->route('employee');
            $employee = EmployeeModel::find($employeeId);
            return $employee ? $employee->department_id : null;
        }

        return null;
    }

    /**
     * Check if user has department-level permissions.
     */
    private function hasDepartmentLevelPermissions($user, ?string $permission): bool
    {
        if (!$permission) {
            // Default department permissions
            return $user->hasPermissionTo(EmployeePermissions::VIEW_DEPARTMENT_EMPLOYEES) ||
                   $user->hasPermissionTo(EmployeePermissions::MANAGE_DEPARTMENT_EMPLOYEES);
        }

        // Map specific permissions to department-level equivalents
        $departmentPermissionMap = [
            'view_employees' => EmployeePermissions::VIEW_DEPARTMENT_EMPLOYEES,
            'manage_employees' => EmployeePermissions::MANAGE_DEPARTMENT_EMPLOYEES,
            'view_performance' => EmployeePermissions::VIEW_PERFORMANCE,
            'manage_performance' => EmployeePermissions::CREATE_PERFORMANCE_REVIEWS,
            'view_commissions' => EmployeePermissions::VIEW_COMMISSIONS,
            'view_payroll' => EmployeePermissions::VIEW_PAYROLL,
        ];

        $departmentPermission = $departmentPermissionMap[$permission] ?? $permission;
        
        return $user->hasPermissionTo($departmentPermission);
    }
}