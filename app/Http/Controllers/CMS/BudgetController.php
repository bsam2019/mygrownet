<?php

namespace App\Http\Controllers\CMS;

use App\Domain\CMS\Core\Services\BudgetService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\BudgetModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    public function __construct(
        private BudgetService $budgetService
    ) {}

    public function index(Request $request): Response
    {
        $cmsUser = $request->user()->cmsUser;
        $companyId = $cmsUser->company_id;

        $budgets = BudgetModel::where('company_id', $companyId)
            ->with(['items', 'createdBy'])
            ->orderBy('start_date', 'desc')
            ->paginate(20);

        return Inertia::render('CMS/Budgets/Index', [
            'budgets' => $budgets,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('CMS/Budgets/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'period_type' => 'required|in:monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_budget' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:draft,active,completed,archived',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.category' => 'required|string',
            'items.*.item_type' => 'required|in:revenue,expense',
            'items.*.budgeted_amount' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        $cmsUser = $request->user()->cmsUser;
        
        $budget = $this->budgetService->createBudget(
            $cmsUser->company_id,
            $validated,
            $cmsUser->id
        );

        return redirect()->route('cms.budgets.show', $budget->id)
            ->with('success', 'Budget created successfully.');
    }

    public function show(Request $request, int $id): Response
    {
        $cmsUser = $request->user()->cmsUser;
        
        $budget = BudgetModel::where('company_id', $cmsUser->company_id)
            ->with(['items', 'createdBy'])
            ->findOrFail($id);

        // Get budget vs actual comparison
        $comparison = $this->budgetService->getBudgetVsActual($id);

        return Inertia::render('CMS/Budgets/Show', [
            'budget' => $budget,
            'comparison' => $comparison,
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $cmsUser = $request->user()->cmsUser;
        
        $budget = BudgetModel::where('company_id', $cmsUser->company_id)
            ->with('items')
            ->findOrFail($id);

        return Inertia::render('CMS/Budgets/Edit', [
            'budget' => $budget,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'period_type' => 'required|in:monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_budget' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:draft,active,completed,archived',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.category' => 'required|string',
            'items.*.item_type' => 'required|in:revenue,expense',
            'items.*.budgeted_amount' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        $cmsUser = $request->user()->cmsUser;
        
        $budget = BudgetModel::where('company_id', $cmsUser->company_id)
            ->findOrFail($id);

        $this->budgetService->updateBudget($id, $validated);

        return redirect()->route('cms.budgets.show', $id)
            ->with('success', 'Budget updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $cmsUser = $request->user()->cmsUser;
        
        $budget = BudgetModel::where('company_id', $cmsUser->company_id)
            ->findOrFail($id);

        $this->budgetService->deleteBudget($id);

        return redirect()->route('cms.budgets.index')
            ->with('success', 'Budget deleted successfully.');
    }
}
