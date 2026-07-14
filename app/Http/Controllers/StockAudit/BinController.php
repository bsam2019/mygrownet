<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\DepartmentBinService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BinController extends Controller
{
    public function __construct(
        private DepartmentBinService $departmentBinService,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $bins = $this->departmentBinService->getBins($companyId);

        return Inertia::render('StockAudit/Bins/Index', [
            'bins' => $bins,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        $validated = $request->validate([
            'sa_department_id' => 'required|exists:sa_departments,id',
            'name' => 'required|string|max:255',
            'label' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $this->departmentBinService->createBin($companyId, $validated);

        return redirect()->sfRoute('stock-audit.bins.index');
    }

    public function update(Request $request, int $binId)
    {
        $validated = $request->validate([
            'sa_department_id' => 'required|exists:sa_departments,id',
            'name' => 'required|string|max:255',
            'label' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $this->departmentBinService->updateBin($binId, $validated);

        return redirect()->sfRoute('stock-audit.bins.index');
    }

    public function destroy(int $binId)
    {
        $this->departmentBinService->deleteBin($binId);

        return redirect()->sfRoute('stock-audit.bins.index');
    }
}
