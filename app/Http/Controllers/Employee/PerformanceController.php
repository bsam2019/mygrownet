<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StorePerformanceReviewRequest;
use App\Http\Requests\Employee\UpdatePerformanceReviewRequest;
use App\Http\Requests\Employee\StoreGoalRequest;
use App\Domain\Employee\Services\PerformanceTrackingService;
use App\Domain\Employee\Services\PerformanceReviewData;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use DateTimeImmutable;

class PerformanceController extends Controller
{
    /**
     * Display a listing of performance reviews.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'department_id', 'status', 'period']);

        // Load performance reviews from database
        $query = EmployeePerformanceModel::with(['employee.department', 'employee.position']);

        if (!empty($filters['search'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('first_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('last_name', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['department_id'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $performance = $query->orderBy('evaluation_period_end', 'desc')->paginate(15);

        // Get departments for filter dropdown
        $departments = \App\Infrastructure\Persistence\Eloquent\DepartmentModel::active()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Employee/Performance/Index', [
            'performance' => $performance,
            'departments' => $departments,
            'filters' => $filters
        ]);
    }

    /**
     * Show the form for creating a new performance review.
     */
    public function create(Request $request): Response
    {
        $employees = EmployeeModel::select('id', 'first_name', 'last_name', 'department_id', 'position_id')
            ->with(['department', 'position'])
            ->where('employment_status', 'active')
            ->orderBy('first_name')
            ->get()
            ->map(fn($emp) => [
                'id' => $emp->id,
                'name' => $emp->first_name . ' ' . $emp->last_name,
                'department' => $emp->department?->name,
                'position' => $emp->position?->title
            ]);

        $selectedEmployeeId = $request->get('employee_id');

        return Inertia::render('Employee/Performance/Create', [
            'employees' => $employees,
            'selectedEmployeeId' => $selectedEmployeeId
        ]);
    }

    /**
     * Store a newly created performance review.
     */
    public function store(StorePerformanceReviewRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            // Get employee and reviewer entities
            $employee = $this->employeeRepository->findById(EmployeeId::fromString((string) $validated['employee_id']));
            $reviewer = $this->employeeRepository->findById(EmployeeId::fromString((string) $validated['reviewer_id']));

            if (!$employee || !$reviewer) {
                return back()->withErrors(['error' => 'Employee or reviewer not found.']);
            }

            // Create performance review data
            $reviewData = PerformanceReviewData::create(
                $employee,
                new DateTimeImmutable($validated['evaluation_period_start']),
                new DateTimeImmutable($validated['evaluation_period_end']),
                $validated['investments_facilitated'],
                $validated['client_retention_rate'],
                $validated['commission_generated'],
                $validated['new_client_acquisitions'],
                $validated['goal_achievement_rate'],
                $reviewer,
                $validated['review_notes'] ?? null,
                $validated['goals_next_period'] ?? []
            );

            // Create the performance review
            $performance = $this->performanceTrackingService->createPerformanceReview($reviewData);

            // Store in database
            EmployeePerformanceModel::create([
                'employee_id' => $employee->getId()->toInt(),
                'reviewer_id' => $reviewer->getId()->toInt(),
                'evaluation_period' => $validated['evaluation_period_start'] . ' to ' . $validated['evaluation_period_end'],
                'period_start' => $validated['evaluation_period_start'],
                'period_end' => $validated['evaluation_period_end'],
                'metrics' => [
                    'investments_facilitated_count' => $validated['investments_facilitated'],
                    'investments_facilitated_amount' => $validated['investments_facilitated_amount'] ?? 0,
                    'client_retention_rate' => $validated['client_retention_rate'],
                    'commission_generated' => $validated['commission_generated'],
                    'new_client_acquisitions' => $validated['new_client_acquisitions'],
                    'goal_achievement_rate' => $validated['goal_achievement_rate']
                ],
                'overall_score' => $performance->getMetrics()->calculateOverallScore(),
                'rating' => $performance->getPerformanceRating(),
                'strengths' => $performance->calculateTrendAnalysis([])['strengths'] ?? [],
                'areas_for_improvement' => $performance->calculateTrendAnalysis([])['improvement_areas'] ?? [],
                'goals_next_period' => $validated['goals_next_period'] ?? [],
                'reviewer_comments' => $validated['review_notes'],
                'employee_comments' => $validated['employee_comments'] ?? null,
                'status' => 'completed',
                'submitted_at' => now(),
                'approved_at' => now()
            ]);

            return redirect()->route('performance.index')
                ->with('success', 'Performance review created successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create performance review: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified performance review.
     */
    public function show(EmployeePerformanceModel $performance): Response
    {
        $performance->load(['employee', 'reviewer']);

        // Get performance trends for this employee
        $employee = $this->employeeRepository->findById(EmployeeId::fromString((string) $performance->employee_id));
        $trends = [];
        $recommendations = [];

        if ($employee) {
            $trends = $this->performanceTrackingService->calculatePerformanceTrends($employee);
            $recommendations = $this->performanceTrackingService->generatePerformanceRecommendations($employee);
        }

        return Inertia::render('Employee/Performance/Show', [
            'performance' => [
                'id' => $performance->id,
                'employee' => [
                    'id' => $performance->employee->id,
                    'name' => $performance->employee->first_name . ' ' . $performance->employee->last_name,
                    'department' => $performance->employee->department?->name,
                    'position' => $performance->employee->position?->title
                ],
                'reviewer' => [
                    'id' => $performance->reviewer->id,
                    'name' => $performance->reviewer->first_name . ' ' . $performance->reviewer->last_name
                ],
                'evaluation_period' => $performance->evaluation_period,
                'period_start' => $performance->period_start->format('Y-m-d'),
                'period_end' => $performance->period_end->format('Y-m-d'),
                'metrics' => $performance->metrics,
                'overall_score' => $performance->overall_score,
                'rating' => $performance->performance_grade,
                'strengths' => $performance->strengths ?? [],
                'areas_for_improvement' => $performance->areas_for_improvement ?? [],
                'goals_next_period' => $performance->goals_next_period ?? [],
                'reviewer_comments' => $performance->reviewer_comments,
                'employee_comments' => $performance->employee_comments,
                'status' => $performance->status,
                'submitted_at' => $performance->submitted_at?->format('Y-m-d H:i:s'),
                'approved_at' => $performance->approved_at?->format('Y-m-d H:i:s'),
                'created_at' => $performance->created_at->format('Y-m-d H:i:s')
            ],
            'trends' => $trends,
            'recommendations' => $recommendations
        ]);
    }

    /**
     * Show the form for editing the specified performance review.
     */
    public function edit(EmployeePerformanceModel $performance): Response
    {
        $performance->load(['employee', 'reviewer']);

        $employees = EmployeeModel::select('id', 'first_name', 'last_name')
            ->where('employment_status', 'active')
            ->orderBy('first_name')
            ->get()
            ->map(fn($emp) => [
                'id' => $emp->id,
                'name' => $emp->first_name . ' ' . $emp->last_name
            ]);

        return Inertia::render('Employee/Performance/Edit', [
            'performance' => [
                'id' => $performance->id,
                'employee_id' => $performance->employee_id,
                'reviewer_id' => $performance->reviewer_id,
                'evaluation_period_start' => $performance->period_start->format('Y-m-d'),
                'evaluation_period_end' => $performance->period_end->format('Y-m-d'),
                'investments_facilitated' => $performance->metrics['investments_facilitated_count'] ?? 0,
                'investments_facilitated_amount' => $performance->metrics['investments_facilitated_amount'] ?? 0,
                'client_retention_rate' => $performance->metrics['client_retention_rate'] ?? 0,
                'commission_generated' => $performance->metrics['commission_generated'] ?? 0,
                'new_client_acquisitions' => $performance->metrics['new_client_acquisitions'] ?? 0,
                'goal_achievement_rate' => $performance->metrics['goal_achievement_rate'] ?? 0,
                'review_notes' => $performance->reviewer_comments,
                'employee_comments' => $performance->employee_comments,
                'goals_next_period' => $performance->goals_next_period ?? []
            ],
            'employees' => $employees
        ]);
    }

    /**
     * Update the specified performance review.
     */
    public function update(UpdatePerformanceReviewRequest $request, EmployeePerformanceModel $performance): RedirectResponse
    {
        $validated = $request->validated();

        try {
            // Update the performance review
            $performance->update([
                'period_start' => $validated['evaluation_period_start'],
                'period_end' => $validated['evaluation_period_end'],
                'metrics' => [
                    'investments_facilitated_count' => $validated['investments_facilitated'],
                    'investments_facilitated_amount' => $validated['investments_facilitated_amount'] ?? 0,
                    'client_retention_rate' => $validated['client_retention_rate'],
                    'commission_generated' => $validated['commission_generated'],
                    'new_client_acquisitions' => $validated['new_client_acquisitions'],
                    'goal_achievement_rate' => $validated['goal_achievement_rate']
                ],
                'reviewer_comments' => $validated['review_notes'],
                'employee_comments' => $validated['employee_comments'] ?? null,
                'goals_next_period' => $validated['goals_next_period'] ?? []
            ]);

            // Recalculate overall score
            $employee = $this->employeeRepository->findById(EmployeeId::fromString((string) $performance->employee_id));
            if ($employee) {
                $reviewer = $this->employeeRepository->findById(EmployeeId::fromString((string) $performance->reviewer_id));
                if ($reviewer) {
                    $reviewData = PerformanceReviewData::create(
                        $employee,
                        new DateTimeImmutable($validated['evaluation_period_start']),
                        new DateTimeImmutable($validated['evaluation_period_end']),
                        $validated['investments_facilitated'],
                        $validated['client_retention_rate'],
                        $validated['commission_generated'],
                        $validated['new_client_acquisitions'],
                        $validated['goal_achievement_rate'],
                        $reviewer,
                        $validated['review_notes'] ?? null,
                        $validated['goals_next_period'] ?? []
                    );

                    $performanceEntity = $this->performanceTrackingService->createPerformanceReview($reviewData);
                    
                    $performance->update([
                        'overall_score' => $performanceEntity->getMetrics()->calculateOverallScore(),
                        'rating' => $performanceEntity->getPerformanceRating()
                    ]);
                }
            }

            return redirect()->route('performance.show', $performance)
                ->with('success', 'Performance review updated successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update performance review: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified performance review.
     */
    public function destroy(EmployeePerformanceModel $performance): RedirectResponse
    {
        try {
            $performance->delete();

            return redirect()->route('performance.index')
                ->with('success', 'Performance review deleted successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete performance review: ' . $e->getMessage()]);
        }
    }

    /**
     * Display performance analytics and reports.
     */
    public function analytics(Request $request): Response
    {
        // Get department performance averages
        $departmentStats = [];
        $employees = EmployeeModel::with(['department', 'lastPerformanceReview'])->get();
        
        $employeesByDepartment = $employees->groupBy('department.name');
        
        foreach ($employeesByDepartment as $departmentName => $deptEmployees) {
            $performanceData = $deptEmployees->filter(fn($emp) => $emp->lastPerformanceReview)
                ->map(fn($emp) => $emp->lastPerformanceReview);
            
            if ($performanceData->isNotEmpty()) {
                $avgScore = $performanceData->avg('overall_score');
                $departmentStats[] = [
                    'department' => $departmentName ?: 'Unassigned',
                    'employee_count' => $deptEmployees->count(),
                    'reviewed_count' => $performanceData->count(),
                    'average_score' => round($avgScore, 2),
                    'top_performer' => $performanceData->sortByDesc('overall_score')->first()?->employee?->first_name . ' ' . $performanceData->sortByDesc('overall_score')->first()?->employee?->last_name,
                    'needs_attention' => $performanceData->where('overall_score', '<', 6.0)->count()
                ];
            }
        }

        // Get top performers
        $topPerformers = EmployeePerformanceModel::with('employee')
            ->recent(6)
            ->orderByScore()
            ->limit(10)
            ->get()
            ->map(fn($perf) => [
                'employee_name' => $perf->employee->first_name . ' ' . $perf->employee->last_name,
                'department' => $perf->employee->department?->name,
                'score' => $perf->overall_score,
                'rating' => $perf->performance_grade,
                'period' => $perf->evaluation_period
            ]);

        // Get underperformers
        $underperformers = EmployeePerformanceModel::with('employee')
            ->recent(6)
            ->where('overall_score', '<', 6.0)
            ->orderBy('overall_score')
            ->limit(10)
            ->get()
            ->map(fn($perf) => [
                'employee_name' => $perf->employee->first_name . ' ' . $perf->employee->last_name,
                'department' => $perf->employee->department?->name,
                'score' => $perf->overall_score,
                'rating' => $perf->performance_grade,
                'period' => $perf->evaluation_period
            ]);

        // Performance trends over time
        $performanceTrends = EmployeePerformanceModel::selectRaw('
                DATE_FORMAT(period_end, "%Y-%m") as month,
                AVG(overall_score) as avg_score,
                COUNT(*) as review_count
            ')
            ->where('period_end', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return Inertia::render('Employee/Performance/Analytics', [
            'departmentStats' => $departmentStats,
            'topPerformers' => $topPerformers,
            'underperformers' => $underperformers,
            'performanceTrends' => $performanceTrends
        ]);
    }

    /**
     * Set goals for an employee.
     */
    public function setGoals(StoreGoalRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $employee = $this->employeeRepository->findById(EmployeeId::fromString((string) $validated['employee_id']));
            
            if (!$employee) {
                return back()->withErrors(['error' => 'Employee not found.']);
            }

            $this->performanceTrackingService->setPerformanceGoals(
                $employee,
                $validated['goals'],
                new DateTimeImmutable($validated['target_date'])
            );

            return back()->with('success', 'Goals set successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to set goals: ' . $e->getMessage()]);
        }
    }

    /**
     * Track goal progress for an employee.
     */
    public function trackGoals(Request $request, int $employeeId): Response
    {
        $employee = $this->employeeRepository->findById(EmployeeId::fromString((string) $employeeId));
        
        if (!$employee) {
            abort(404, 'Employee not found');
        }

        try {
            $goalUpdates = $request->get('goal_updates', []);
            $progress = $this->performanceTrackingService->trackGoalProgress($employee, $goalUpdates);

            return Inertia::render('Employee/Performance/GoalTracking', [
                'employee' => [
                    'id' => $employee->getId()->toInt(),
                    'name' => $employee->getFullName()
                ],
                'goalProgress' => $progress
            ]);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to track goals: ' . $e->getMessage()]);
        }
    }
}