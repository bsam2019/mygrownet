<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Models\PositionKpi;
use App\Models\EmployeeKpiTracking;
use App\Models\PositionResponsibility;
use App\Models\HiringRoadmap;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrganizationalStructureController extends Controller
{
    /**
     * Display organizational chart
     */
    public function index()
    {
        $positions = PositionModel::with([
            'department',
            'reportsTo',
            'employees' => function($query) {
                $query->where('employment_status', 'active');
            }
        ])
        ->orderBy('organizational_level')
        ->orderBy('level')
        ->get();

        // Build hierarchical structure
        $orgChart = $this->buildOrgChart($positions);

        return Inertia::render('Admin/Organization/Index', [
            'orgChart' => $orgChart,
            'stats' => $this->getOrganizationStats(),
        ]);
    }

    /**
     * Display KPI management page
     */
    public function kpis()
    {
        $kpis = PositionKpi::with('position.department')
            ->active()
            ->get()
            ->groupBy('position.department.name');

        return Inertia::render('Admin/Organization/KPIs', [
            'kpis' => $kpis,
            'positions' => PositionModel::active()->with('department')->get(),
        ]);
    }

    /**
     * Store new KPI
     */
    public function storeKpi(Request $request)
    {
        $validated = $request->validate([
            'position_id' => 'required|exists:positions,id',
            'kpi_name' => 'required|string|max:255',
            'kpi_description' => 'nullable|string',
            'target_value' => 'nullable|numeric',
            'measurement_unit' => 'nullable|string|max:50',
            'measurement_frequency' => 'required|in:daily,weekly,monthly,quarterly,annual',
        ]);

        PositionKpi::create($validated);

        return back()->with('success', 'KPI created successfully');
    }

    /**
     * Update KPI
     */
    public function updateKpi(Request $request, PositionKpi $kpi)
    {
        $validated = $request->validate([
            'kpi_name' => 'required|string|max:255',
            'kpi_description' => 'nullable|string',
            'target_value' => 'nullable|numeric',
            'measurement_unit' => 'nullable|string|max:50',
            'measurement_frequency' => 'required|in:daily,weekly,monthly,quarterly,annual',
            'is_active' => 'boolean',
        ]);

        $kpi->update($validated);

        return back()->with('success', 'KPI updated successfully');
    }

    /**
     * Display employee KPI tracking
     */
    public function employeeKpis(EmployeeModel $employee)
    {
        $employee->load(['position.kpis', 'department']);

        $tracking = EmployeeKpiTracking::where('employee_id', $employee->id)
            ->with('positionKpi')
            ->orderBy('period_start', 'desc')
            ->get();

        return Inertia::render('Admin/Organization/EmployeeKPIs', [
            'employee' => $employee,
            'tracking' => $tracking,
            'availableKpis' => $employee->position->kpis,
        ]);
    }

    /**
     * Record employee KPI
     */
    public function recordEmployeeKpi(Request $request, EmployeeModel $employee)
    {
        $validated = $request->validate([
            'position_kpi_id' => 'required|exists:position_kpis,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'actual_value' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        $kpi = PositionKpi::findOrFail($validated['position_kpi_id']);

        $tracking = EmployeeKpiTracking::create([
            'employee_id' => $employee->id,
            'position_kpi_id' => $validated['position_kpi_id'],
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'actual_value' => $validated['actual_value'],
            'target_value' => $kpi->target_value,
            'notes' => $validated['notes'] ?? null,
            'recorded_by' => auth()->id(),
        ]);

        $tracking->calculateAchievement();
        $tracking->save();

        return back()->with('success', 'KPI recorded successfully');
    }

    /**
     * Display hiring roadmap
     */
    public function hiringRoadmap()
    {
        $roadmap = HiringRoadmap::with('position.department')
            ->orderBy('phase')
            ->orderBy('priority')
            ->orderBy('target_hire_date')
            ->get()
            ->groupBy('phase');

        return Inertia::render('Admin/Organization/HiringRoadmap', [
            'roadmap' => $roadmap,
            'positions' => PositionModel::active()->with('department')->get(),
            'stats' => $this->getHiringStats(),
        ]);
    }

    /**
     * Store hiring roadmap entry
     */
    public function storeHiringRoadmap(Request $request)
    {
        $validated = $request->validate([
            'position_id' => 'required|exists:positions,id',
            'phase' => 'required|in:phase_1,phase_2,phase_3',
            'target_hire_date' => 'nullable|date',
            'priority' => 'required|in:critical,high,medium,low',
            'headcount' => 'required|integer|min:1',
            'budget_allocated' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        HiringRoadmap::create($validated);

        return back()->with('success', 'Hiring roadmap entry created successfully');
    }

    /**
     * Update hiring roadmap entry
     */
    public function updateHiringRoadmap(Request $request, HiringRoadmap $roadmap)
    {
        $validated = $request->validate([
            'target_hire_date' => 'nullable|date',
            'priority' => 'required|in:critical,high,medium,low',
            'headcount' => 'required|integer|min:1',
            'status' => 'required|in:planned,in_progress,hired,cancelled',
            'budget_allocated' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $roadmap->update($validated);

        return back()->with('success', 'Hiring roadmap updated successfully');
    }

    /**
     * Display position details with responsibilities
     */
    public function positionDetails(PositionModel $position)
    {
        $position->load([
            'department',
            'reportsTo',
            'directReports',
            'employees',
            'kpis',
            'responsibilities' => function($query) {
                $query->active()->ordered();
            },
            'hiringRoadmap'
        ]);

        return Inertia::render('Admin/Organization/PositionDetails', [
            'position' => $position,
        ]);
    }

    /**
     * Store position responsibility
     */
    public function storeResponsibility(Request $request, PositionModel $position)
    {
        $validated = $request->validate([
            'responsibility_title' => 'required|string|max:255',
            'responsibility_description' => 'nullable|string',
            'priority' => 'required|in:critical,high,medium,low',
            'category' => 'required|in:strategic,operational,administrative,technical',
            'display_order' => 'nullable|integer',
        ]);

        $validated['position_id'] = $position->id;
        
        if (!isset($validated['display_order'])) {
            $maxOrder = PositionResponsibility::where('position_id', $position->id)->max('display_order');
            $validated['display_order'] = ($maxOrder ?? 0) + 1;
        }

        PositionResponsibility::create($validated);

        return back()->with('success', 'Responsibility added successfully');
    }

    /**
     * Update position responsibility
     */
    public function updateResponsibility(Request $request, PositionResponsibility $responsibility)
    {
        $validated = $request->validate([
            'responsibility_title' => 'required|string|max:255',
            'responsibility_description' => 'nullable|string',
            'priority' => 'required|in:critical,high,medium,low',
            'category' => 'required|in:strategic,operational,administrative,technical',
            'display_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $responsibility->update($validated);

        return back()->with('success', 'Responsibility updated successfully');
    }

    /**
     * Build hierarchical org chart
     */
    private function buildOrgChart($positions)
    {
        $positionMap = [];
        $rootPositions = [];

        // First pass: create map
        foreach ($positions as $position) {
            $positionMap[$position->id] = [
                'id' => $position->id,
                'title' => $position->title,
                'department' => $position->department->name,
                'organizational_level' => $position->organizational_level,
                'level' => $position->level,
                'reports_to_position_id' => $position->reports_to_position_id,
                'employees' => $position->employees->map(fn($e) => [
                    'id' => $e->id,
                    'name' => $e->full_name,
                    'email' => $e->email,
                ]),
                'children' => [],
            ];
        }

        // Second pass: build hierarchy
        foreach ($positionMap as $id => $position) {
            if ($position['reports_to_position_id']) {
                if (isset($positionMap[$position['reports_to_position_id']])) {
                    $positionMap[$position['reports_to_position_id']]['children'][] = &$positionMap[$id];
                }
            } else {
                $rootPositions[] = &$positionMap[$id];
            }
        }

        return $rootPositions;
    }

    /**
     * Get organization statistics
     */
    private function getOrganizationStats()
    {
        return [
            'total_positions' => PositionModel::active()->count(),
            'filled_positions' => PositionModel::active()->has('employees')->count(),
            'vacant_positions' => PositionModel::active()->doesntHave('employees')->count(),
            'total_employees' => EmployeeModel::where('employment_status', 'active')->count(),
            'c_level_count' => PositionModel::cLevel()->has('employees')->count(),
            'total_departments' => DepartmentModel::active()->count(),
        ];
    }

    /**
     * Get hiring statistics
     */
    private function getHiringStats()
    {
        return [
            'phase_1_planned' => HiringRoadmap::byPhase('phase_1')->planned()->sum('headcount'),
            'phase_1_in_progress' => HiringRoadmap::byPhase('phase_1')->inProgress()->sum('headcount'),
            'phase_1_hired' => HiringRoadmap::byPhase('phase_1')->hired()->sum('headcount'),
            'phase_2_planned' => HiringRoadmap::byPhase('phase_2')->planned()->sum('headcount'),
            'phase_3_planned' => HiringRoadmap::byPhase('phase_3')->planned()->sum('headcount'),
            'overdue_hires' => HiringRoadmap::overdue()->count(),
            'total_budget' => HiringRoadmap::whereIn('status', ['planned', 'in_progress'])->sum('budget_allocated'),
        ];
    }
}
