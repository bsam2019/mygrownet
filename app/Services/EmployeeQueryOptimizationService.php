<?php

declare(strict_types=1);

namespace App\Services;

use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EmployeeQueryOptimizationService
{
    private const CACHE_TTL = 300; // 5 minutes

    public function getEmployeesWithOptimizedQueries(array $filters = [], int $perPage = 15): array
    {
        $cacheKey = 'employees_list_' . md5(serialize($filters) . $perPage);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters, $perPage) {
            $query = EmployeeModel::query()
                ->select([
                    'employees.id',
                    'employees.employee_number',
                    'employees.first_name',
                    'employees.last_name',
                    'employees.email',
                    'employees.employment_status',
                    'employees.hire_date',
                    'employees.current_salary',
                    'departments.name as department_name',
                    'positions.title as position_title'
                ])
                ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                ->leftJoin('positions', 'employees.position_id', '=', 'positions.id');

            // Apply filters efficiently
            $this->applyFilters($query, $filters);

            // Add computed fields
            $query->addSelect([
                DB::raw('CONCAT(employees.first_name, " ", employees.last_name) as full_name'),
                DB::raw('TIMESTAMPDIFF(YEAR, employees.hire_date, CURDATE()) as years_of_service')
            ]);

            return $query->paginate($perPage);
        });
    }

    public function getEmployeeStatistics(): array
    {
        return Cache::remember('employee_statistics', self::CACHE_TTL, function () {
            return [
                'total_employees' => EmployeeModel::count(),
                'active_employees' => EmployeeModel::where('employment_status', 'active')->count(),
                'inactive_employees' => EmployeeModel::where('employment_status', 'inactive')->count(),
                'terminated_employees' => EmployeeModel::where('employment_status', 'terminated')->count(),
                'new_hires_this_month' => EmployeeModel::where('hire_date', '>=', now()->startOfMonth())->count(),
                'departments_count' => DB::table('departments')->where('is_active', true)->count(),
                'positions_count' => DB::table('positions')->where('is_active', true)->count(),
                'average_salary' => EmployeeModel::where('employment_status', 'active')->avg('current_salary'),
                'field_agents_count' => EmployeeModel::whereHas('position', function ($query) {
                    $query->where('title', 'like', '%field agent%')
                          ->orWhere('title', 'like', '%agent%');
                })->where('employment_status', 'active')->count(),
            ];
        });
    }

    public function getDepartmentHierarchy(): array
    {
        return Cache::remember('department_hierarchy', self::CACHE_TTL * 2, function () {
            return DB::table('departments')
                ->select([
                    'id',
                    'name',
                    'parent_department_id',
                    'head_employee_id',
                    DB::raw('(SELECT COUNT(*) FROM employees WHERE department_id = departments.id AND employment_status = "active") as employee_count')
                ])
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->toArray();
        });
    }

    public function getPerformanceMetrics(int $employeeId): array
    {
        $cacheKey = "employee_performance_{$employeeId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($employeeId) {
            $currentYear = now()->year;
            
            return [
                'current_year_reviews' => DB::table('employee_performance')
                    ->where('employee_id', $employeeId)
                    ->whereYear('evaluation_period_start', $currentYear)
                    ->count(),
                'average_score' => DB::table('employee_performance')
                    ->where('employee_id', $employeeId)
                    ->whereYear('evaluation_period_start', $currentYear)
                    ->avg('overall_score') ?? 0,
                'total_commissions_ytd' => DB::table('employee_commissions')
                    ->where('employee_id', $employeeId)
                    ->whereYear('calculation_date', $currentYear)
                    ->where('status', 'paid')
                    ->sum('commission_amount'),
                'active_client_assignments' => DB::table('employee_client_assignments')
                    ->where('employee_id', $employeeId)
                    ->where('is_active', true)
                    ->count(),
                'investments_facilitated_ytd' => DB::table('employee_commissions')
                    ->where('employee_id', $employeeId)
                    ->whereYear('calculation_date', $currentYear)
                    ->where('commission_type', 'investment_facilitation')
                    ->count(),
            ];
        });
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('employees.first_name', 'like', "%{$search}%")
                  ->orWhere('employees.last_name', 'like', "%{$search}%")
                  ->orWhere('employees.email', 'like', "%{$search}%")
                  ->orWhere('employees.employee_number', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['department'])) {
            $query->where('employees.department_id', $filters['department']);
        }

        if (!empty($filters['position'])) {
            $query->where('employees.position_id', $filters['position']);
        }

        if (!empty($filters['status'])) {
            $query->where('employees.employment_status', $filters['status']);
        }

        if (!empty($filters['hire_date_from'])) {
            $query->whereDate('employees.hire_date', '>=', $filters['hire_date_from']);
        }

        if (!empty($filters['hire_date_to'])) {
            $query->whereDate('employees.hire_date', '<=', $filters['hire_date_to']);
        }
    }

    public function invalidateCache(int $employeeId = null): void
    {
        Cache::forget('employee_statistics');
        Cache::forget('department_hierarchy');
        
        if ($employeeId) {
            Cache::forget("employee_performance_{$employeeId}");
        }
        
        // Clear paginated results cache
        $pattern = 'employees_list_*';
        $keys = Cache::getRedis()->keys($pattern);
        if (!empty($keys)) {
            Cache::getRedis()->del($keys);
        }
    }
}