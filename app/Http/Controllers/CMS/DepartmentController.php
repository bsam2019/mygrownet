<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\BranchModel;
use App\Infrastructure\Persistence\Eloquent\CMS\DepartmentModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $departments = DepartmentModel::with(['branch', 'manager', 'parentDepartment'])
            ->where('company_id', $companyId)
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->search, fn($q) => $q->where(function($query) use ($request) {
                $query->where('department_name', 'like', "%{$request->search}%")
                      ->orWhere('department_code', 'like', "%{$request->search}%");
            }))
            ->withCount('workers')
            ->latest()
            ->paginate(20);

        $branches = BranchModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'branch_name']);

        return Inertia::render('CMS/Departments/Index', [
            'departments' => $departments,
            'branches' => $branches,
            'filters' => $request->only(['branch_id', 'search']),
        ]);
    }

    public function create(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $branches = BranchModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'branch_name as name']);

        $workers = \App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel::where('company_id', $companyId)
            ->where('employment_status', 'active')
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn($w) => ['id' => $w->id, 'name' => trim($w->first_name . ' ' . $w->last_name)]);

        return Inertia::render('CMS/Departments/Create', [
            'branches' => $branches,
            'workers' => $workers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'code' => 'nullable|string|unique:cms_departments,department_code',
            'branch_id' => 'nullable|exists:cms_branches,id',
            'manager_id' => 'nullable|exists:cms_workers,id',
            'description' => 'nullable|string',
        ]);

        $department = DepartmentModel::create([
            'company_id' => $request->user()->company_id,
            'department_name' => $validated['name'],
            'department_code' => $validated['code'] ?? strtoupper(substr($validated['name'], 0, 3)),
            'branch_id' => $validated['branch_id'] ?? null,
            'manager_id' => $validated['manager_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return redirect()
            ->route('cms.departments.index')
            ->with('success', 'Department created successfully');
    }

    public function edit(DepartmentModel $department)
    {
        $department->load(['branch', 'manager']);
        
        $branches = BranchModel::where('company_id', $department->company_id)
            ->where('is_active', true)
            ->get(['id', 'branch_name as name']);

        $workers = \App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel::where('company_id', $department->company_id)
            ->where('employment_status', 'active')
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn($w) => ['id' => $w->id, 'name' => trim($w->first_name . ' ' . $w->last_name)]);

        return Inertia::render('CMS/Departments/Edit', [
            'department' => [
                'id' => $department->id,
                'name' => $department->department_name,
                'code' => $department->department_code,
                'branch_id' => $department->branch_id,
                'manager_id' => $department->manager_id,
                'description' => $department->description,
            ],
            'branches' => $branches,
            'workers' => $workers,
        ]);
    }

    public function update(Request $request, DepartmentModel $department)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'code' => 'nullable|string|unique:cms_departments,department_code,' . $department->id,
            'branch_id' => 'nullable|exists:cms_branches,id',
            'manager_id' => 'nullable|exists:cms_workers,id',
            'description' => 'nullable|string',
        ]);

        $department->update([
            'department_name' => $validated['name'],
            'department_code' => $validated['code'] ?? $department->department_code,
            'branch_id' => $validated['branch_id'] ?? null,
            'manager_id' => $validated['manager_id'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('cms.departments.index')
            ->with('success', 'Department updated successfully');
    }

    public function destroy(DepartmentModel $department)
    {
        // Check if department has workers
        if ($department->workers()->count() > 0) {
            return back()->with('error', 'Cannot delete department with assigned workers');
        }

        $department->delete();

        return redirect()
            ->route('cms.departments.index')
            ->with('success', 'Department deleted successfully');
    }
}
