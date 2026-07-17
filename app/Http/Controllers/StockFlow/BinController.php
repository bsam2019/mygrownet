<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\DepartmentBinService;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaBinModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaItemModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BinController extends Controller
{
    public function __construct(
        private DepartmentBinService $departmentBinService,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $search = $request->get('search');
        $perPage = $request->get('per_page', 15);

        $query = SaBinModel::with('department')
            ->where('sa_company_id', $companyId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('label', 'like', "%{$search}%");
            });
        }

        $bins = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($model) => $model->toArray());

        $departments = $this->departmentBinService->getDepartments($companyId);

        return Inertia::render('StockFlow/Bins/Index', [
            'bins' => $bins,
            'departments' => $departments,
        ]);
    }

    public function show(Request $request, int $binId)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $bin = SaBinModel::with('department')
            ->where('sa_company_id', $companyId)
            ->findOrFail($binId);
        $items = SaItemModel::where('sa_bin_id', $binId)
            ->where('sa_company_id', $companyId)
            ->orderBy('name')
            ->get()
            ->toArray();

        return Inertia::render('StockFlow/Bins/Show', [
            'bin' => [
                'id' => $bin->id,
                'name' => $bin->name,
                'label' => $bin->label,
                'description' => $bin->description,
                'department_name' => $bin->department?->name ?? 'N/A',
            ],
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        $validated = $request->validate([
            'sa_department_id' => 'required|exists:sa_departments,id',
            'name' => 'required|string|max:255',
            'label' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $this->departmentBinService->createBin($companyId, $validated);

        return redirect()->sfRoute('stockflow.bins.index')->with('success', 'Bin created successfully.');
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

        return redirect()->sfRoute('stockflow.bins.index')->with('success', 'Bin updated successfully.');
    }

    public function destroy(int $binId)
    {
        $this->departmentBinService->deleteBin($binId);

        return redirect()->sfRoute('stockflow.bins.index')->with('success', 'Bin deleted successfully.');
    }
}
