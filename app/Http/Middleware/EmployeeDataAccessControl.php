<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use Symfony\Component\HttpFoundation\Response;

class EmployeeDataAccessControl
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get employee ID from route parameter
        $employeeId = $request->route('employee');
        
        if ($employeeId) {
            $employee = EmployeeModel::find($employeeId);
            
            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }

            // Check access permissions
            if (!$this->canAccessEmployee($user, $employee, $request->method())) {
                Log::warning('Unauthorized employee data access attempt', [
                    'user_id' => $user->id,
                    'employee_id' => $employeeId,
                    'method' => $request->method(),
                    'route' => $request->route()->getName(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                
                return response()->json(['error' => 'Access denied'], 403);
            }

            // Log successful access for audit trail
            Log::info('Employee data accessed', [
                'user_id' => $user->id,
                'employee_id' => $employeeId,
                'method' => $request->method(),
                'route' => $request->route()->getName(),
            ]);
        }

        return $next($request);
    }

    private function canAccessEmployee($user, EmployeeModel $employee, string $method): bool
    {
        // System admin has full access
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // HR managers can access all employees
        if ($user->hasRole('hr-manager')) {
            return true;
        }

        // Department heads can access employees in their department
        if ($user->hasRole('department-head')) {
            $userEmployee = EmployeeModel::where('user_id', $user->id)->first();
            if ($userEmployee && $userEmployee->department_id === $employee->department_id) {
                return true;
            }
        }

        // Managers can access their direct reports
        if ($user->hasRole('manager')) {
            $userEmployee = EmployeeModel::where('user_id', $user->id)->first();
            if ($userEmployee && $employee->manager_id === $userEmployee->id) {
                return true;
            }
        }

        // Employees can only access their own data (read-only)
        if ($method === 'GET') {
            $userEmployee = EmployeeModel::where('user_id', $user->id)->first();
            if ($userEmployee && $userEmployee->id === $employee->id) {
                return true;
            }
        }

        // Check specific permissions
        $permissionMap = [
            'GET' => 'view-employees',
            'POST' => 'create-employees',
            'PUT' => 'edit-employees',
            'PATCH' => 'edit-employees',
            'DELETE' => 'delete-employees',
        ];

        $requiredPermission = $permissionMap[$method] ?? 'view-employees';
        
        return $user->can($requiredPermission);
    }
}