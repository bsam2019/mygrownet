<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\DepartmentBinService;
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
        $departments = $this->departmentBinService->getDepartments($companyId);

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

        return redirect()->sfRoute('stock-audit.departments.index');
    }

    public function update(Request $request, int $departmentId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $this->departmentBinService->updateDepartment($departmentId, $validated);

        return redirect()->sfRoute('stock-audit.departments.index');
    }

    public function destroy(int $departmentId)
    {
        $this->departmentBinService->deleteDepartment($departmentId);

        return redirect()->sfRoute('stock-audit.departments.index');
    }
}
