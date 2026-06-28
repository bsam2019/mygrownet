<?php

namespace App\Http\Controllers\LifePlus;

use App\Http\Controllers\Controller;
use App\Domain\LifePlus\Services\ExpenseService;
use App\Domain\LifePlus\Services\BudgetService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function __construct(
        protected ExpenseService $expenseService,
        protected BudgetService $budgetService
    ) {}

    public function index(Request $request)
    {
        $userId = auth()->id();
        $month = $request->get('month', now()->format('Y-m'));

        return Inertia::render('LifePlus/Money/Overview', [
            'expenses' => $this->expenseService->getExpenses($userId, ['month' => $month]),
            'summary' => $this->expenseService->getMonthSummary($userId, $month),
            'categories' => $this->expenseService->getCategories($userId),
            'currentBudget' => $this->budgetService->getCurrentBudget($userId),
            'month' => $month,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:lifeplus_expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'expense_date' => 'nullable|date',
            'local_id' => 'nullable|string',
        ]);

        $expense = $this->expenseService->createExpense(auth()->id(), $validated);

        if ($request->wantsJson()) {
            return response()->json($expense, 201);
        }

        return back()->with('success', 'Expense recorded');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:lifeplus_expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'expense_date' => 'nullable|date',
        ]);

        $expense = $this->expenseService->updateExpense($id, auth()->id(), $validated);

        if (!$expense) {
            return response()->json(['error' => 'Expense not found'], 404);
        }

        if ($request->wantsJson()) {
            return response()->json($expense);
        }

        return back()->with('success', 'Expense updated');
    }

    public function destroy(int $id)
    {
        $deleted = $this->expenseService->deleteExpense($id, auth()->id());

        if (!$deleted) {
            return response()->json(['error' => 'Expense not found'], 404);
        }

        return back()->with('success', 'Expense deleted');
    }

    public function summary(Request $request)
    {
        $month = $request->get('month');
        return response()->json(
            $this->expenseService->getMonthSummary(auth()->id(), $month)
        );
    }

    public function categories()
    {
        return Inertia::render('LifePlus/Money/Categories', [
            'categories' => $this->expenseService->getCategories(auth()->id()),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
        ]);

        $category = $this->expenseService->createCategory(auth()->id(), $validated);

        if ($request->wantsJson()) {
            return response()->json($category, 201);
        }

        return back()->with('success', 'Category created');
    }

    public function sync(Request $request)
    {
        $validated = $request->validate([
            'expenses' => 'required|array',
            'expenses.*.amount' => 'required|numeric|min:0.01',
            'expenses.*.expense_date' => 'required|date',
            'expenses.*.local_id' => 'required|string',
        ]);

        $synced = $this->expenseService->syncExpenses(auth()->id(), $validated['expenses']);

        return response()->json(['synced' => $synced]);
    }
}
