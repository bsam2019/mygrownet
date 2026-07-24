<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Domain\Employee\Repositories\DepartmentRepositoryInterface;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function __construct(
        private DepartmentRepositoryInterface $departmentRepo,
        private EmployeeRepositoryInterface $employeeRepo,
    ) {}

    public function index(Request $request): Response|JsonResponse
    {
        $query = $this->departmentRepo->query()
            ->with(['headEmployee', 'parentDepartment', 'childDepartments'])
            ->withCount(['employees', 'positions']);

        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_department_id', $request->integer('parent_id'));
        }

        if ($request->boolean('hierarchical', false)) {
            $departments = $this->buildHierarchicalStructure($query->get());
        } else {
            $departments = $query->orderBy('name')->paginate(15);
        }

        $allDepartments = $this->departmentRepo->getAllActive();

        if ($request->wantsJson()) {
            return response()->json([
                'departments' => $departments,
                'meta' => [
                    'total_count' => $allDepartments->count(),
                    'active_count' => $allDepartments->count(),
                ]
            ]);
        }

        return Inertia::render('Employee/Departments/Index', [
            'departments' => $departments,
            'filters' => $request->only(['search', 'active_only', 'parent_id', 'hierarchical']),
            'meta' => [
                'total_count' => $allDepartments->count(),
                'active_count' => $allDepartments->count(),
            ]
        ]);
    }

    public function show(DepartmentModel $department): Response|JsonResponse
    {
        $department->load([
            'headEmployee',
            'parentDepartment',
            'childDepartments.headEmployee',
            'employees.position',
            'positions'
        ]);

        $data = [
            'department' => $department,
            'hierarchy' => $this->getDepartmentHierarchy($department),
            'statistics' => [
                'total_employees' => $department->employees()->count(),
                'active_employees' => $department->employees()->where('employment_status', 'active')->count(),
                'total_positions' => $department->positions()->count(),
                'active_positions' => $department->positions()->where('is_active', true)->count(),
                'child_departments' => $department->childDepartments()->count(),
            ]
        ];

        if (request()->wantsJson()) {
            return response()->json($data);
        }

        return Inertia::render('Employee/Departments/Show', $data);
    }

    public function create()
    {
        $activeEmployees = $this->employeeRepo->query()
            ->where('employment_status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        return Inertia::render('Employee/Departments/Create', [
            'parent_departments' => $this->departmentRepo->getAllActive(),
            'employees' => $activeEmployees,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_department_id' => 'nullable|integer|exists:departments,id',
            'head_employee_id' => 'nullable|integer|exists:employees,id'
        ]);

        $this->departmentRepo->save([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'parent_department_id' => $validated['parent_department_id'] ?? null,
            'head_employee_id' => $validated['head_employee_id'] ?? null,
            'is_active' => true
        ]);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully!');
    }

    public function edit(DepartmentModel $department)
    {
        $department->load(['headEmployee', 'parentDepartment']);

        $activeEmployees = $this->employeeRepo->query()
            ->where('employment_status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        return Inertia::render('Employee/Departments/Edit', [
            'department' => $department,
            'parent_departments' => $this->departmentRepo->query()
                ->where('id', '!=', $department->id)
                ->active()
                ->orderBy('name')
                ->get(['id', 'name']),
            'employees' => $activeEmployees,
        ]);
    }

    public function update(Request $request, DepartmentModel $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_department_id' => 'nullable|integer|exists:departments,id',
            'head_employee_id' => 'nullable|integer|exists:employees,id'
        ]);

        $department->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'parent_department_id' => $validated['parent_department_id'] ?? null,
            'head_employee_id' => $validated['head_employee_id'] ?? null
        ]);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully!');
    }

    public function destroy(DepartmentModel $department): JsonResponse
    {
        if ($department->employees()->exists()) {
            return response()->json([
                'message' => 'Cannot delete department with active employees. Please reassign employees first.'
            ], 422);
        }

        if ($department->childDepartments()->exists()) {
            return response()->json([
                'message' => 'Cannot delete department with child departments. Please reassign or delete child departments first.'
            ], 422);
        }

        $department->delete();

        return response()->json([
            'message' => 'Department deleted successfully'
        ]);
    }

    public function hierarchy(): JsonResponse
    {
        $departments = $this->departmentRepo->query()
            ->with(['headEmployee', 'childDepartments'])
            ->withCount(['employees', 'positions'])
            ->active()
            ->get();

        $hierarchy = $this->buildHierarchicalStructure($departments);

        return response()->json([
            'hierarchy' => $hierarchy
        ]);
    }

    public function assignHead(Request $request, DepartmentModel $department): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id'
        ]);

        $employee = $department->employees()->findOrFail($request->employee_id);

        $department->update(['head_employee_id' => $employee->id]);

        return response()->json([
            'message' => 'Department head assigned successfully',
            'department' => $department->load('headEmployee')
        ]);
    }

    public function removeHead(DepartmentModel $department): JsonResponse
    {
        $department->update(['head_employee_id' => null]);

        return response()->json([
            'message' => 'Department head removed successfully',
            'department' => $department->fresh()
        ]);
    }

    private function buildHierarchicalStructure($departments)
    {
        $departmentMap = [];
        $rootDepartments = [];

        foreach ($departments as $department) {
            $departmentMap[$department->id] = $department;
            $department->children = collect();
        }

        foreach ($departments as $department) {
            if ($department->parent_department_id) {
                if (isset($departmentMap[$department->parent_department_id])) {
                    $departmentMap[$department->parent_department_id]->children->push($department);
                }
            } else {
                $rootDepartments[] = $department;
            }
        }

        return $rootDepartments;
    }

    private function getDepartmentHierarchy(DepartmentModel $department): array
    {
        $hierarchy = [];
        $current = $department;

        while ($current) {
            array_unshift($hierarchy, [
                'id' => $current->id,
                'name' => $current->name,
                'level' => count($hierarchy)
            ]);
            $current = $current->parentDepartment;
        }

        return $hierarchy;
    }
}
