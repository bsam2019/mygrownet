<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Application\UseCases\Employee\CreateEmployeeUseCase;
use App\Application\UseCases\Employee\UpdateEmployeeUseCase;
use App\Application\UseCases\Employee\GetEmployeeUseCase;
use App\Application\UseCases\Employee\DeleteEmployeeUseCase;
use App\Application\DTOs\Employee\CreateEmployeeDTO;
use App\Application\DTOs\Employee\UpdateEmployeeDTO;
use App\Domain\Employee\Exceptions\EmployeeNotFoundException;
use App\Domain\Employee\Exceptions\EmployeeAlreadyExistsException;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    public function __construct(
        private CreateEmployeeUseCase $createEmployeeUseCase,
        private UpdateEmployeeUseCase $updateEmployeeUseCase,
        private GetEmployeeUseCase $getEmployeeUseCase,
        private DeleteEmployeeUseCase $deleteEmployeeUseCase
    ) {}

    /**
     * Display a listing of employees
     */
    public function index(Request $request)
    {
        // Filters
        $filters = $request->only([
            'search', 'department', 'position', 'status',
            'hire_date_from', 'hire_date_to', 'sort', 'direction'
        ]);

        // Base query
        $query = EmployeeModel::with(['department:id,name', 'position:id,title']);

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_number', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['department'])) {
            $query->where('department_id', $filters['department']);
        }

        if (!empty($filters['position'])) {
            $query->where('position_id', $filters['position']);
        }

        if (!empty($filters['status'])) {
            $query->where('employment_status', $filters['status']);
        }

        if (!empty($filters['hire_date_from'])) {
            $query->whereDate('hire_date', '>=', $filters['hire_date_from']);
        }

        if (!empty($filters['hire_date_to'])) {
            $query->whereDate('hire_date', '<=', $filters['hire_date_to']);
        }

        // Sorting
        $sort = $filters['sort'] ?? 'hire_date';
        $direction = strtolower($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['hire_date', 'first_name', 'last_name', 'employment_status'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'hire_date';
        }
        $query->orderBy($sort, $direction);

        $employees = $query->paginate(10)->withQueryString();

        return Inertia::render('Employee/Index', [
            'employees' => $employees,
            'departments' => DepartmentModel::active()->orderBy('name')->get(['id', 'name']),
            'positions' => PositionModel::active()->orderBy('title')->get(['id', 'title']),
            'filters' => $filters,
            'stats' => $this->getEmployeeStatistics(),
        ]);
    }

    /**
     * Show the form for creating a new employee
     */
    public function create()
    {
        return Inertia::render('Employee/Create', [
            'departments' => DepartmentModel::active()->orderBy('name')->get(['id', 'name']),
            'positions' => PositionModel::active()->with('department:id,name')->orderBy('title')->get(['id', 'title', 'department_id'])
        ]);
    }

    /**
     * Store a newly created employee
     */
    public function store(Request $request)
    {

        dd("test");
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'department_id' => 'required|integer|exists:departments,id',
            'position_id' => 'required|integer|exists:positions,id',
            'hire_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0'
        ]);

        $employee = EmployeeModel::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'department_id' => $validated['department_id'],
            'position_id' => $validated['position_id'],
            'hire_date' => $validated['hire_date'],
            'current_salary' => $validated['salary'] ?? null,
            'employee_number' => 'EMP' . str_pad(EmployeeModel::count() + 1, 6, '0', STR_PAD_LEFT),
            'employment_status' => 'active'
        ]);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee created successfully!');
    }

    /**
     * Display the specified employee
     */
    public function show(string $id)
    {
        $employee = EmployeeModel::with([
            'department',
            'position',
            'manager',
            'user',
            'directReports',
            'performanceReviews' => function ($q) {
                $q->latest();
            },
        ])->findOrFail($id);

        return Inertia::render('Employee/Show', [
            'employee' => $employee,
            'performanceMetrics' => $this->calculateEmployeePerformanceMetrics($employee),
            'commissionSummary' => $this->getCommissionSummary($employee),
        ]);
    }

    /**
     * Show the form for editing the specified employee
     */
    public function edit(string $id)
    {
        $employee = EmployeeModel::findOrFail($id);
        
        return Inertia::render('Employee/Edit', [
            'employee' => $employee,
            'departments' => DepartmentModel::active()->orderBy('name')->get(['id', 'name']),
            'positions' => PositionModel::active()->orderBy('title')->get(['id', 'title'])
        ]);
    }

    /**
     * Update the specified employee
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $id,
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'current_salary' => 'required|numeric|min:0',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'manager_id' => 'nullable|exists:employees,id',
            'employment_status' => 'required|in:active,inactive,terminated',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $employee = EmployeeModel::findOrFail($id);
            $employee->update($validated);

            DB::commit();

            return redirect()
                ->route('admin.employees.show', ['employee' => $id])
                ->with('success', 'Employee updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Failed to update employee. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified employee (soft delete)
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $employeeModel = EmployeeModel::findOrFail($id);

            // Update employment status to terminated instead of hard delete
            $employeeModel->update([
                'employment_status' => 'terminated',
                'termination_date' => now(),
                'notes' => ($employeeModel->notes ?? '') . "\nTerminated on " . now()->format('Y-m-d H:i:s'),
            ]);

            // Deactivate user account if exists
            if ($employeeModel->user) {
                $employeeModel->user->update([
                    'status' => 'inactive',
                ]);
                
                // Remove all roles and permissions
                $employeeModel->user->roles()->detach();
                $employeeModel->user->permissions()->detach();
            }

            // Soft delete the record
            $employeeModel->delete();

            DB::commit();

            return redirect()
                ->route('admin.employees.index')
                ->with('success', 'Employee terminated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Failed to terminate employee. Please try again.']);
        }
    }

    /**
     * Get employee statistics for dashboard
     */
    private function getEmployeeStatistics(): array
    {
        return [
            'total_employees' => EmployeeModel::count(),
            'active_employees' => EmployeeModel::where('employment_status', 'active')->count(),
            'inactive_employees' => EmployeeModel::where('employment_status', 'inactive')->count(),
            'terminated_employees' => EmployeeModel::where('employment_status', 'terminated')->count(),
            'new_hires_this_month' => EmployeeModel::where('hire_date', '>=', now()->startOfMonth())->count(),
            'departments_count' => DepartmentModel::where('is_active', true)->count(),
            'positions_count' => PositionModel::where('is_active', true)->count(),
            'field_agents_count' => EmployeeModel::whereHas('position', function ($query) {
                $query->where('title', 'like', '%field agent%')
                      ->orWhere('title', 'like', '%agent%');
            })->where('employment_status', 'active')->count(),
        ];
    }

    /**
     * Calculate employee performance metrics
     */
    private function calculateEmployeePerformanceMetrics($employee): array
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        return [
            'current_year_reviews' => $employee->performanceReviews()
                ->whereYear('evaluation_period_start', $currentYear)
                ->count(),
            'average_score' => $employee->performanceReviews()
                ->whereYear('evaluation_period_start', $currentYear)
                ->avg('overall_score') ?? 0,
            'total_commissions_ytd' => $employee->commissions()
                ->whereYear('calculation_date', $currentYear)
                ->where('status', 'paid')
                ->sum('commission_amount'),
            'active_client_assignments' => $employee->clientAssignments()
                ->where('is_active', true)
                ->count(),
            'investments_facilitated_ytd' => $employee->commissions()
                ->whereYear('calculation_date', $currentYear)
                ->where('commission_type', 'investment_facilitation')
                ->count(),
        ];
    }

    /**
     * Get commission summary for employee
     */
    private function getCommissionSummary($employee): array
    {
        $currentYear = now()->year;

        return [
            'total_earned' => $employee->commissions()->where('status', 'paid')->sum('commission_amount'),
            'pending_amount' => $employee->commissions()->where('status', 'pending')->sum('commission_amount'),
            'ytd_earnings' => $employee->commissions()
                ->whereYear('calculation_date', $currentYear)
                ->where('status', 'paid')
                ->sum('commission_amount'),
            'commission_breakdown' => $employee->commissions()
                ->selectRaw('commission_type, SUM(commission_amount) as total, COUNT(*) as count')
                ->where('status', 'paid')
                ->groupBy('commission_type')
                ->get(),
        ];
    }

    /**
     * Convert DepartmentModel to Department entity
     */
    private function convertToDepartmentEntity(DepartmentModel $model): Department
    {
        // This is a simplified conversion - in a full implementation,
        // you'd have proper entity factories or converters
        return Department::create(
            $model->name,
            $model->description ?? '',
            $model->is_active
        );
    }

    /**
     * Convert PositionModel to Position entity
     */
    private function convertToPositionEntity(PositionModel $model): Position
    {
        // This is a simplified conversion - in a full implementation,
        // you'd have proper entity factories or converters
        $department = $this->convertToDepartmentEntity($model->department);
        
        return Position::create(
            $model->title,
            $model->description ?? '',
            $department,
            Salary::fromAmount($model->base_salary_min),
            Salary::fromAmount($model->base_salary_max),
            $model->commission_eligible,
            $model->commission_rate ?? 0.0,
            $model->responsibilities ?? [],
            $model->required_permissions ?? []
        );
    }

    /**
     * Get employee profile data for widget
     */
    public function getProfile(string $id)
    {
        $employeeModel = EmployeeModel::with(['department', 'position'])->findOrFail($id);
        
        // Check if user can access this employee's data
        if (auth()->user()->id !== $employeeModel->user_id && !auth()->user()->can('view', $employeeModel)) {
            abort(403, 'Unauthorized access to employee data');
        }
        
        $profileData = [
            'id' => $employeeModel->id,
            'firstName' => $employeeModel->first_name,
            'lastName' => $employeeModel->last_name,
            'employmentStatus' => $employeeModel->employment_status,
            'department' => $employeeModel->department ? [
                'id' => $employeeModel->department->id,
                'name' => $employeeModel->department->name
            ] : null,
            'position' => $employeeModel->position ? [
                'id' => $employeeModel->position->id,
                'title' => $employeeModel->position->title
            ] : null,
            'hireDate' => $employeeModel->hire_date,
            'yearsOfService' => $employeeModel->hire_date ? now()->diffInYears($employeeModel->hire_date) : 0,
            'performanceScore' => $employeeModel->performance_rating ?? 0
        ];

        // Get recent activities
        $recentActivity = $this->getEmployeeRecentActivities($employeeModel);

        return response()->json([
            'employee' => $profileData,
            'recentActivity' => $recentActivity
        ]);
    }

    /**
     * Get employee recent activities
     */
    private function getEmployeeRecentActivities($employee): array
    {
        $activities = [];

        // Get recent commissions
        $recentCommissions = EmployeeCommissionModel::where('employee_id', $employee->id)
            ->latest()
            ->take(3)
            ->get();

        foreach ($recentCommissions as $commission) {
            $activities[] = [
                'id' => $commission->id,
                'description' => "Commission earned: K" . number_format($commission->commission_amount, 2),
                'date' => $commission->calculation_date->toISOString(),
                'type' => 'commission'
            ];
        }

        // Get recent performance reviews
        $recentReviews = EmployeePerformanceModel::where('employee_id', $employee->id)
            ->latest()
            ->take(2)
            ->get();

        foreach ($recentReviews as $review) {
            $activities[] = [
                'id' => $review->id,
                'description' => "Performance review completed - Score: " . $review->overall_score,
                'date' => $review->created_at->toISOString(),
                'type' => 'performance_review'
            ];
        }

        // Sort by date and return latest 5
        usort($activities, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($activities, 0, 5);
    }
}