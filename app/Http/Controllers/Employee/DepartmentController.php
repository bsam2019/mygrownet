<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreDepartmentRequest;
use App\Http\Requests\Employee\UpdateDepartmentRequest;
use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        $query = DepartmentModel::query()
            ->with(['headEmployee', 'parentDepartment', 'childDepartments'])
            ->withCount(['employees', 'positions']);

        // Filter by active status
        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by parent department
        if ($request->filled('parent_id')) {
            $query->where('parent_department_id', $request->integer('parent_id'));
        }

        // Get hierarchical structure if requested
        if ($request->boolean('hierarchical', false)) {
            $departments = $this->buildHierarchicalStructure($query->get());
        } else {
            $departments = $query->orderBy('name')->paginate(15);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'departments' => $departments,
                'meta' => [
                    'total_count' => DepartmentModel::count(),
                    'active_count' => DepartmentModel::active()->count(),
                ]
            ]);
        }

        return Inertia::render('Employee/Departments/Index', [
            'departments' => $departments,
            'filters' => $request->only(['search', 'active_only', 'parent_id', 'hierarchical']),
            'meta' => [
                'total_count' => DepartmentModel::count(),
                'active_count' => DepartmentModel::active()->count(),
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

    /**
     * Show the form for creating a new department
     */
    public function create()
    {
        return Inertia::render('Employee/Departments/Create', [
            'parent_departments' => DepartmentModel::active()->orderBy('name')->get(['id', 'name']),
            'employees' => \App\Infrastructure\Persistence\Eloquent\EmployeeModel::where('employment_status', 'active')->orderBy('first_name')->get(['id', 'first_name', 'last_name'])
        ]);
    }

    /**
     * Store a newly created department
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_department_id' => 'nullable|integer|exists:departments,id',
            'head_employee_id' => 'nullable|integer|exists:employees,id'
        ]);

        $department = DepartmentModel::create([
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
        
        return Inertia::render('Employee/Departments/Edit', [
            'department' => $department,
            'parent_departments' => DepartmentModel::active()
                ->where('id', '!=', $department->id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'employees' => \App\Infrastructure\Persistence\Eloquent\EmployeeModel::where('employment_status', 'active')
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name'])
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
        // Check if department has employees
        if ($department->employees()->exists()) {
            return response()->json([
                'message' => 'Cannot delete department with active employees. Please reassign employees first.'
            ], 422);
        }

        // Check if department has child departments
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
        $departments = DepartmentModel::with(['headEmployee', 'childDepartments'])
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

        // Create a map of departments by ID
        foreach ($departments as $department) {
            $departmentMap[$department->id] = $department;
            $department->children = collect();
        }

        // Build the hierarchy
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

        // Build breadcrumb trail to root
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

    private function isDepartmentDescendant(DepartmentModel $ancestor, DepartmentModel $potential_descendant): bool
    {
        $current = $potential_descendant;
        
        while ($current && $current->parent_department_id) {
            if ($current->parent_department_id === $ancestor->id) {
                return true;
            }
            $current = $current->parentDepartment;
        }
        
        return false;
    }
}