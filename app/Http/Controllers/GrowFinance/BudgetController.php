<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\BudgetService;
use App\Domain\Module\Services\SubscriptionService;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceAccountModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceBudgetModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private BudgetService $budgetService
    ) {}

    public function index(Request $request): Response
    {
        // Check if user has budget feature
        if (!$this->subscriptionService->canPerformAction($request->user(), 'budgets')) {
            return Inertia::render('GrowFinance/FeatureUpgradeRequired', [
                'feature' => 'Budget Tracking',
                'requiredTier' => 'professional',
            ]);
        }

        $budgets = $this->budgetService->getForBusiness($request->user());
        $summary = $this->budgetService->getSummary($request->user());

        return Inertia::render('GrowFinance/Budgets/Index', [
            'budgets' => $budgets,
            'summary' => $summary,
        ]);
    }

    public function create(Request $request): Response
    {
        if (!$this->subscriptionService->canPerformAction($request->user(), 'budgets')) {
            return Inertia::render('GrowFinance/FeatureUpgradeRequired', [
                'feature' => 'Budget Tracking',
                'requiredTier' => 'professional',
            ]);
        }

        $businessId = $request->user()->id;

        $accounts = GrowFinanceAccountModel::forBusiness($businessId)
            ->active()
            ->ofType(AccountType::EXPENSE)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $categories = [
            'Cost of Goods Sold',
            'Salaries & Wages',
            'Rent',
            'Utilities',
            'Transport & Fuel',
            'Office Supplies',
            'Marketing',
            'Bank Charges',
            'Other',
        ];

        $periods = [
            ['value' => 'monthly', 'label' => 'Monthly'],
            ['value' => 'quarterly', 'label' => 'Quarterly'],
            ['value' => 'yearly', 'label' => 'Yearly'],
            ['value' => 'custom', 'label' => 'Custom Period'],
        ];

        return Inertia::render('GrowFinance/Budgets/Create', [
            'accounts' => $accounts,
            'categories' => $categories,
            'periods' => $periods,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (!$this->subscriptionService->canPerformAction($request->user(), 'budgets')) {
            return back()->with('error', 'Budget tracking requires Professional plan or higher.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'account_id' => 'nullable|exists:growfinance_accounts,id',
            'period' => 'required|in:monthly,quarterly,yearly,custom',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'budgeted_amount' => 'required|numeric|min:0.01',
            'alert_threshold' => 'nullable|integer|min:1|max:100',
            'rollover_unused' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->budgetService->create($request->user(), $validated);

        return redirect()->route('growfinance.budgets.index')
            ->with('success', 'Budget created successfully!');
    }

    public function show(Request $request, int $id): Response
    {
        $budget = GrowFinanceBudgetModel::forBusiness($request->user()->id)
            ->with('account')
            ->findOrFail($id);

        return Inertia::render('GrowFinance/Budgets/Show', [
            'budget' => $budget,
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $businessId = $request->user()->id;

        $budget = GrowFinanceBudgetModel::forBusiness($businessId)
            ->findOrFail($id);

        $accounts = GrowFinanceAccountModel::forBusiness($businessId)
            ->active()
            ->ofType(AccountType::EXPENSE)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $categories = [
            'Cost of Goods Sold',
            'Salaries & Wages',
            'Rent',
            'Utilities',
            'Transport & Fuel',
            'Office Supplies',
            'Marketing',
            'Bank Charges',
            'Other',
        ];

        return Inertia::render('GrowFinance/Budgets/Edit', [
            'budget' => $budget,
            'accounts' => $accounts,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'budgeted_amount' => 'required|numeric|min:0.01',
            'alert_threshold' => 'nullable|integer|min:1|max:100',
            'rollover_unused' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $budget = GrowFinanceBudgetModel::forBusiness($request->user()->id)
            ->findOrFail($id);

        $budget->update($validated);

        return redirect()->route('growfinance.budgets.index')
            ->with('success', 'Budget updated successfully!');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $budget = GrowFinanceBudgetModel::forBusiness($request->user()->id)
            ->findOrFail($id);

        $budget->delete();

        return back()->with('success', 'Budget deleted successfully!');
    }

    public function recalculate(Request $request, int $id): RedirectResponse
    {
        $budget = GrowFinanceBudgetModel::forBusiness($request->user()->id)
            ->findOrFail($id);

        $this->budgetService->recalculateSpent($budget);

        return back()->with('success', 'Budget recalculated successfully!');
    }

    public function rollover(Request $request, int $id): RedirectResponse
    {
        $budget = GrowFinanceBudgetModel::forBusiness($request->user()->id)
            ->findOrFail($id);

        $this->budgetService->rolloverBudget($budget);

        return back()->with('success', 'New budget period created!');
    }
}
