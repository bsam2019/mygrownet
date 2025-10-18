<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Optimized Employee Repository with N+1 Query Prevention
 * 
 * This repository extends the base EloquentEmployeeRepository with optimized
 * query methods that prevent N+1 query problems and improve performance
 * for large datasets and complex reporting operations.
 */
class OptimizedEmployeeRepository extends EloquentEmployeeRepository
{
    /**
     * Find all employees with optimized eager loading to prevent N+1 queries
     */
    public function findAllOptimized(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        // Apply filters (same as parent method)
        $this->applyFilters($query, $filters);

        // Optimized eager loading with specific columns to reduce memory usage
        return $query->with([
            'department:id,name,description,head_employee_id,parent_department_id',
            'position:id,title,department_id,min_salary,max_salary,base_commission_rate,performance_commission_rate',
            'manager:id,employee_id,first_name,last_name,email',
            'user:id,name,email',
            'lastPerformanceReview:id,employee_id,overall_score,rating,period_end'
        ])->get();
    }

    /**
     * Get department hierarchy with employee counts (optimized)
     */
    public function getDepartmentHierarchyOptimized(): Collection
    {
        return DB::table('departments as d')
            ->leftJoin('employees as e', function ($join) {
                $join->on('d.id', '=', 'e.department_id')
                     ->where('e.employment_status', '=', 'active')
                     ->whereNull('e.deleted_at');
            })
            ->leftJoin('employees as head', 'd.head_employee_id', '=', 'head.id')
            ->select([
                'd.id',
                'd.name',
                'd.description',
                'd.parent_department_id',
                'd.head_employee_id',
                DB::raw('CONCAT(head.first_name, " ", head.last_name) as head_name'),
                DB::raw('COUNT(e.id) as employee_count'),
                DB::raw('AVG(e.current_salary) as avg_salary')
            ])
            ->where('d.is_active', true)
            ->whereNull('d.deleted_at')
            ->groupBy([
                'd.id', 'd.name', 'd.description', 'd.parent_department_id', 
                'd.head_employee_id', 'head.first_name', 'head.last_name'
            ])
            ->orderBy('d.name')
            ->get();
    }

    /**
     * Get employee performance analytics with optimized queries
     */
    public function getPerformanceAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        // Single query to get all performance data
        $performanceData = DB::table('employee_performance as ep')
            ->join('employees as e', 'ep.employee_id', '=', 'e.id')
            ->join('departments as d', 'e.department_id', '=', 'd.id')
            ->join('positions as p', 'e.position_id', '=', 'p.id')
            ->select([
                'e.id as employee_id',
                'e.first_name',
                'e.last_name',
                'd.name as department_name',
                'p.title as position_title',
                'ep.overall_score',
                'ep.rating',
                'ep.period_end',
                DB::raw('JSON_EXTRACT(ep.metrics, "$.investments_facilitated_count") as investments_count'),
                DB::raw('JSON_EXTRACT(ep.metrics, "$.commission_generated") as commission_generated')
            ])
            ->whereBetween('ep.period_end', [$startDate, $endDate])
            ->where('ep.status', 'approved')
            ->where('e.employment_status', 'active')
            ->whereNull('e.deleted_at')
            ->orderBy('ep.overall_score', 'desc')
            ->get();

        // Aggregate data for analytics
        $analytics = [
            'total_reviews' => $performanceData->count(),
            'average_score' => $performanceData->avg('overall_score'),
            'top_performers' => $performanceData->take(10),
            'by_department' => $performanceData->groupBy('department_name')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'avg_score' => $group->avg('overall_score'),
                    'total_investments' => $group->sum('investments_count'),
                    'total_commission' => $group->sum('commission_generated')
                ];
            }),
            'by_rating' => $performanceData->groupBy('rating')->map->count(),
            'score_distribution' => [
                'excellent' => $performanceData->where('overall_score', '>=', 90)->count(),
                'good' => $performanceData->whereBetween('overall_score', [80, 89])->count(),
                'satisfactory' => $performanceData->whereBetween('overall_score', [70, 79])->count(),
                'needs_improvement' => $performanceData->where('overall_score', '<', 70)->count()
            ]
        ];

        return $analytics;
    }

    /**
     * Get commission analytics with optimized queries
     */
    public function getCommissionAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        $commissionData = DB::table('employee_commissions as ec')
            ->join('employees as e', 'ec.employee_id', '=', 'e.id')
            ->join('departments as d', 'e.department_id', '=', 'd.id')
            ->join('positions as p', 'e.position_id', '=', 'p.id')
            ->select([
                'e.id as employee_id',
                'e.first_name',
                'e.last_name',
                'd.name as department_name',
                'p.title as position_title',
                'ec.commission_type',
                'ec.amount',
                'ec.earned_date',
                'ec.payment_status'
            ])
            ->whereBetween('ec.earned_date', [$startDate, $endDate])
            ->where('e.employment_status', 'active')
            ->whereNull('e.deleted_at')
            ->get();

        return [
            'total_commissions' => $commissionData->sum('amount'),
            'total_transactions' => $commissionData->count(),
            'average_commission' => $commissionData->avg('amount'),
            'by_type' => $commissionData->groupBy('commission_type')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total_amount' => $group->sum('amount'),
                    'avg_amount' => $group->avg('amount')
                ];
            }),
            'by_department' => $commissionData->groupBy('department_name')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total_amount' => $group->sum('amount'),
                    'avg_amount' => $group->avg('amount')
                ];
            }),
            'by_status' => $commissionData->groupBy('payment_status')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total_amount' => $group->sum('amount')
                ];
            }),
            'top_earners' => $commissionData->groupBy('employee_id')->map(function ($group) {
                $first = $group->first();
                return [
                    'employee_id' => $first->employee_id,
                    'name' => $first->first_name . ' ' . $first->last_name,
                    'department' => $first->department_name,
                    'position' => $first->position_title,
                    'total_commission' => $group->sum('amount'),
                    'transaction_count' => $group->count()
                ];
            })->sortByDesc('total_commission')->take(10)->values()
        ];
    }

    /**
     * Get payroll data with optimized queries
     */
    public function getPayrollData(Carbon $payrollDate): Collection
    {
        return DB::table('employees as e')
            ->join('departments as d', 'e.department_id', '=', 'd.id')
            ->join('positions as p', 'e.position_id', '=', 'p.id')
            ->leftJoin('employee_commissions as ec', function ($join) use ($payrollDate) {
                $join->on('e.id', '=', 'ec.employee_id')
                     ->where('ec.earned_date', '<=', $payrollDate)
                     ->where('ec.payment_status', 'pending');
            })
            ->select([
                'e.id',
                'e.employee_id',
                'e.first_name',
                'e.last_name',
                'e.email',
                'e.current_salary',
                'd.name as department_name',
                'p.title as position_title',
                'p.base_commission_rate',
                'p.performance_commission_rate',
                DB::raw('COALESCE(SUM(ec.amount), 0) as pending_commissions'),
                DB::raw('COUNT(ec.id) as commission_count')
            ])
            ->where('e.employment_status', 'active')
            ->where('e.hire_date', '<=', $payrollDate)
            ->whereNull('e.deleted_at')
            ->groupBy([
                'e.id', 'e.employee_id', 'e.first_name', 'e.last_name', 'e.email',
                'e.current_salary', 'd.name', 'p.title', 'p.base_commission_rate',
                'p.performance_commission_rate'
            ])
            ->orderBy('d.name')
            ->orderBy('e.last_name')
            ->get();
    }

    /**
     * Get organizational chart data with optimized queries
     */
    public function getOrganizationalChart(): array
    {
        // Get all employees with their relationships in a single query
        $employees = DB::table('employees as e')
            ->join('departments as d', 'e.department_id', '=', 'd.id')
            ->join('positions as p', 'e.position_id', '=', 'p.id')
            ->leftJoin('employees as m', 'e.manager_id', '=', 'm.id')
            ->select([
                'e.id',
                'e.employee_id',
                'e.first_name',
                'e.last_name',
                'e.manager_id',
                'd.name as department_name',
                'p.title as position_title',
                'p.level as position_level',
                DB::raw('CONCAT(m.first_name, " ", m.last_name) as manager_name')
            ])
            ->where('e.employment_status', 'active')
            ->whereNull('e.deleted_at')
            ->orderBy('p.level')
            ->orderBy('d.name')
            ->orderBy('e.last_name')
            ->get();

        // Build hierarchical structure
        $employeeMap = $employees->keyBy('id');
        $hierarchy = [];

        foreach ($employees as $employee) {
            if (!$employee->manager_id) {
                // Top-level employee
                $hierarchy[] = $this->buildEmployeeNode($employee, $employeeMap);
            }
        }

        return $hierarchy;
    }

    /**
     * Get employee search results with optimized queries
     */
    public function searchOptimized(string $searchTerm, int $limit = 50): Collection
    {
        return DB::table('employees as e')
            ->join('departments as d', 'e.department_id', '=', 'd.id')
            ->join('positions as p', 'e.position_id', '=', 'p.id')
            ->leftJoin('employees as m', 'e.manager_id', '=', 'm.id')
            ->select([
                'e.id',
                'e.employee_id',
                'e.first_name',
                'e.last_name',
                'e.email',
                'e.phone',
                'e.employment_status',
                'e.hire_date',
                'e.current_salary',
                'd.name as department_name',
                'p.title as position_title',
                DB::raw('CONCAT(m.first_name, " ", m.last_name) as manager_name'),
                DB::raw('MATCH(e.first_name, e.last_name, e.email) AGAINST(? IN BOOLEAN MODE) as relevance')
            ])
            ->whereRaw('MATCH(e.first_name, e.last_name, e.email) AGAINST(? IN BOOLEAN MODE)', [$searchTerm])
            ->orWhere('e.employee_id', 'LIKE', "%{$searchTerm}%")
            ->orWhere('e.phone', 'LIKE', "%{$searchTerm}%")
            ->whereNull('e.deleted_at')
            ->orderByDesc('relevance')
            ->orderBy('e.employment_status')
            ->orderBy('e.last_name')
            ->limit($limit)
            ->get();
    }

    /**
     * Apply filters to query builder
     */
    private function applyFilters($query, array $filters): void
    {
        if (isset($filters['status'])) {
            $query->where('employment_status', $filters['status']);
        }

        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (isset($filters['position_id'])) {
            $query->where('position_id', $filters['position_id']);
        }

        if (isset($filters['manager_id'])) {
            $query->where('manager_id', $filters['manager_id']);
        }

        if (isset($filters['hire_date_from'])) {
            $query->where('hire_date', '>=', $filters['hire_date_from']);
        }

        if (isset($filters['hire_date_to'])) {
            $query->where('hire_date', '<=', $filters['hire_date_to']);
        }

        if (isset($filters['salary_min']) || isset($filters['salary_max'])) {
            $query->withSalaryRange($filters['salary_min'] ?? null, $filters['salary_max'] ?? null);
        }

        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['commission_eligible']) && $filters['commission_eligible']) {
            $query->commissionEligible();
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        if (isset($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        if (isset($filters['offset'])) {
            $query->offset($filters['offset']);
        }
    }

    /**
     * Build employee node for organizational chart
     */
    private function buildEmployeeNode($employee, $employeeMap): array
    {
        $node = [
            'id' => $employee->id,
            'employee_id' => $employee->employee_id,
            'name' => $employee->first_name . ' ' . $employee->last_name,
            'department' => $employee->department_name,
            'position' => $employee->position_title,
            'level' => $employee->position_level,
            'children' => []
        ];

        // Find direct reports
        foreach ($employeeMap as $emp) {
            if ($emp->manager_id == $employee->id) {
                $node['children'][] = $this->buildEmployeeNode($emp, $employeeMap);
            }
        }

        return $node;
    }
}