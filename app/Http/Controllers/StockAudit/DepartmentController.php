<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\DepartmentBinService;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaDepartmentModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function __construct(
        private DepartmentBinService $departmentBinService,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $search = $request->get('search');
        $perPage = $request->get('per_page', 15);

        $query = SaDepartmentModel::where('sa_company_id', $companyId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $departments = $query->orderBy('sort_order', 'asc')
            ->paginate($perPage)
            ->through(fn($model) => $model->toArray());

        return Inertia::render('StockAudit/Departments/Index', [
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $department = $this->departmentBinService->createDepartment($companyId, $validated);

        return redirect()->sfRoute('stock-audit.departments.index')->with('success', 'Department created successfully.');
    }

    public function update(Request $request, int $departmentId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $this->departmentBinService->updateDepartment($departmentId, $validated);

        return redirect()->sfRoute('stock-audit.departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(int $departmentId)
    {
        $this->departmentBinService->deleteDepartment($departmentId);

        return redirect()->sfRoute('stock-audit.departments.index')->with('success', 'Department deleted successfully.');
    }
}
