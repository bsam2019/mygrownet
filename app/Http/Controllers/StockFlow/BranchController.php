<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\BranchService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BranchController extends Controller
{
    public function __construct(private BranchService $branchService) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        return Inertia::render('StockFlow/Branches/Index', [
            'branches' => array_map(fn($b) => $b->toArray(), $this->branchService->getBranches($companyId)),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'is_head_office' => 'boolean',
            'is_active' => 'boolean',
        ]);
        $this->branchService->createBranch($companyId, $validated);
        return redirect()->back()->with('success', 'Branch created.');
    }

    public function update(Request $request, int $id)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'is_head_office' => 'boolean',
            'is_active' => 'boolean',
        ]);
        $this->branchService->updateBranch($id, $companyId, $validated);
        return redirect()->back()->with('success', 'Branch updated.');
    }

    public function destroy(Request $request, int $id)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $this->branchService->deleteBranch($id, $companyId);
        return redirect()->back()->with('success', 'Branch deleted.');
    }
}
