<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Domain\Employee\Repositories\PositionRepositoryInterface;
use App\Domain\Employee\Repositories\DepartmentRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PositionController extends Controller
{
    public function __construct(
        private PositionRepositoryInterface $positionRepo,
        private DepartmentRepositoryInterface $departmentRepo,
    ) {}

    public function index(Request $request): Response|JsonResponse
    {
        $query = $this->positionRepo->query()
            ->with(['department'])
            ->withCount(['employees']);

        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->integer('department_id'));
        }

        $positions = $query->orderBy('title')->paginate(15);

        return Inertia::render('Employee/Positions/Index', [
            'positions' => $positions,
            'departments' => $this->departmentRepo->getAllActive(),
            'filters' => $request->only(['search', 'department_id', 'active_only'])
        ]);
    }

    public function show(PositionModel $position): Response|JsonResponse
    {
        $position->load([
            'department.headEmployee',
            'employees.department'
        ]);

        $data = [
            'position' => $position,
            'statistics' => [
                'total_employees' => $position->employees()->count(),
                'active_employees' => $position->employees()->where('employment_status', 'active')->count(),
                'average_salary' => $position->employees()
                    ->where('employment_status', 'active')
                    ->avg('current_salary'),
                'commission_earnings' => $position->employees()
                    ->where('employment_status', 'active')
                    ->whereHas('commissions', function ($query) {
                        $query->where('status', 'paid')
                              ->whereBetween('payment_date', [now()->startOfMonth(), now()->endOfMonth()]);
                    })
                    ->withSum('commissions', 'commission_amount')
                    ->get()
                    ->sum('commissions_sum_commission_amount')
            ]
        ];

        if (request()->wantsJson()) {
            return response()->json($data);
        }

        return Inertia::render('Employee/Positions/Show', $data);
    }

    public function create()
    {
        return Inertia::render('Employee/Positions/Create', [
            'departments' => $this->departmentRepo->getAllActive(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'required|integer|exists:departments,id',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0',
            'is_commission_eligible' => 'boolean'
        ]);

        $this->positionRepo->save([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'department_id' => $validated['department_id'],
            'min_salary' => $validated['min_salary'] ?? null,
            'max_salary' => $validated['max_salary'] ?? null,
            'is_commission_eligible' => $validated['is_commission_eligible'] ?? false,
            'is_active' => true
        ]);

        return redirect()->route('admin.positions.index')
            ->with('success', 'Position created successfully!');
    }

    public function edit(PositionModel $position)
    {
        $position->load('department');

        return Inertia::render('Employee/Positions/Edit', [
            'position' => $position,
            'departments' => $this->departmentRepo->getAllActive(),
        ]);
    }

    public function update(Request $request, PositionModel $position)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0',
            'commission_eligible' => 'boolean',
            'is_active' => 'boolean'
        ]);

        $position->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'department_id' => $validated['department_id'],
            'min_salary' => $validated['min_salary'] ?? null,
            'max_salary' => $validated['max_salary'] ?? null,
            'is_commission_eligible' => $validated['commission_eligible'] ?? false,
            'is_active' => $validated['is_active'] ?? true
        ]);

        return redirect()->route('admin.positions.index')
            ->with('success', 'Position updated successfully!');
    }

    public function destroy(PositionModel $position): JsonResponse
    {
        if ($position->employees()->exists()) {
            return response()->json([
                'message' => 'Cannot delete position with active employees. Please reassign employees first.'
            ], 422);
        }

        $position->delete();

        return response()->json([
            'message' => 'Position deleted successfully'
        ]);
    }

    public function byDepartment(DepartmentModel $department): JsonResponse
    {
        $positions = $department->positions()
            ->active()
            ->withCount(['employees'])
            ->orderBy('title')
            ->get();

        return response()->json([
            'positions' => $positions,
            'department' => $department->only(['id', 'name'])
        ]);
    }

    public function salaryRanges(): JsonResponse
    {
        $ranges = $this->positionRepo->query()->selectRaw('
                MIN(min_salary) as overall_min,
                MAX(max_salary) as overall_max,
                AVG(min_salary) as avg_min,
                AVG(max_salary) as avg_max
            ')
            ->first();

        $departmentRanges = $this->positionRepo->query()
            ->join('departments', 'positions.department_id', '=', 'departments.id')
            ->selectRaw('
                departments.name as department_name,
                departments.id as department_id,
                MIN(positions.min_salary) as min_salary,
                MAX(positions.max_salary) as max_salary,
                AVG(positions.min_salary) as avg_min_salary,
                AVG(positions.max_salary) as avg_max_salary,
                COUNT(positions.id) as position_count
            ')
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('departments.name')
            ->get();

        return response()->json([
            'overall_ranges' => $ranges,
            'department_ranges' => $departmentRanges
        ]);
    }

    public function commissionEligible(): JsonResponse
    {
        $positions = $this->positionRepo->query()->active()
            ->where(function($q) {
                $q->where('base_commission_rate', '>', 0)
                  ->orWhere('performance_commission_rate', '>', 0);
            })
            ->with(['department'])
            ->withCount(['employees'])
            ->orderBy('title')
            ->get();

        return response()->json([
            'positions' => $positions
        ]);
    }
}
