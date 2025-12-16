<?php

namespace App\Http\Controllers\LifePlus;

use App\Http\Controllers\Controller;
use App\Domain\LifePlus\Services\BudgetService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public function __construct(protected BudgetService $budgetService) {}

    public function index()
    {
        $userId = auth()->id();

        return Inertia::render('LifePlus/Money/Budget', [
            'budgets' => $this->budgetService->getBudgets($userId),
            'currentBudget' => $this->budgetService->getCurrentBudget($userId),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:lifeplus_expense_categories,id',
            'amount' => 'required|numeric|min:1',
            'period' => 'required|in:weekly,monthly',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $budget = $this->budgetService->createBudget(auth()->id(), $validated);

        if ($request->wantsJson()) {
            return response()->json($budget, 201);
        }

        return back()->with('success', 'Budget created');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'period' => 'nullable|in:weekly,monthly',
            'end_date' => 'nullable|date',
        ]);

        $budget = $this->budgetService->updateBudget($id, auth()->id(), $validated);

        if (!$budget) {
            return response()->json(['error' => 'Budget not found'], 404);
        }

        if ($request->wantsJson()) {
            return response()->json($budget);
        }

        return back()->with('success', 'Budget updated');
    }

    public function destroy(int $id)
    {
        $deleted = $this->budgetService->deleteBudget($id, auth()->id());

        if (!$deleted) {
            return response()->json(['error' => 'Budget not found'], 404);
        }

        return back()->with('success', 'Budget deleted');
    }

    // Savings Goals
    public function savingsGoals()
    {
        return Inertia::render('LifePlus/Money/SavingsGoals', [
            'goals' => $this->budgetService->getSavingsGoals(auth()->id()),
        ]);
    }

    public function storeSavingsGoal(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'target_amount' => 'required|numeric|min:1',
            'current_amount' => 'nullable|numeric|min:0',
            'target_date' => 'nullable|date|after:today',
        ]);

        $goal = $this->budgetService->createSavingsGoal(auth()->id(), $validated);

        if ($request->wantsJson()) {
            return response()->json($goal, 201);
        }

        return back()->with('success', 'Savings goal created');
    }

    public function updateSavingsGoal(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:100',
            'target_amount' => 'nullable|numeric|min:1',
            'target_date' => 'nullable|date',
            'status' => 'nullable|in:active,completed,cancelled',
        ]);

        $goal = $this->budgetService->updateSavingsGoal($id, auth()->id(), $validated);

        if (!$goal) {
            return response()->json(['error' => 'Goal not found'], 404);
        }

        if ($request->wantsJson()) {
            return response()->json($goal);
        }

        return back()->with('success', 'Goal updated');
    }

    public function contributeSavings(Request $request, int $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $goal = $this->budgetService->contributeSavings($id, auth()->id(), $validated['amount']);

        if (!$goal) {
            return response()->json(['error' => 'Goal not found or not active'], 404);
        }

        if ($request->wantsJson()) {
            return response()->json($goal);
        }

        return back()->with('success', 'Contribution added');
    }

    public function destroySavingsGoal(int $id)
    {
        $deleted = $this->budgetService->deleteSavingsGoal($id, auth()->id());

        if (!$deleted) {
            return response()->json(['error' => 'Goal not found'], 404);
        }

        return back()->with('success', 'Goal deleted');
    }
}
