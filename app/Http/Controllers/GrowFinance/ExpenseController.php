<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\ValueObjects\AccountType;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceAccountModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceExpenseModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceVendorModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;

        $expenses = GrowFinanceExpenseModel::forBusiness($businessId)
            ->with(['vendor', 'account'])
            ->latest('expense_date')
            ->paginate(20);

        $categories = GrowFinanceExpenseModel::forBusiness($businessId)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        return Inertia::render('GrowFinance/Expenses/Index', [
            'expenses' => $expenses,
            'categories' => $categories,
        ]);
    }

    public function create(Request $request): Response
    {
        $businessId = $request->user()->id;

        $accounts = GrowFinanceAccountModel::forBusiness($businessId)
            ->active()
            ->ofType(AccountType::EXPENSE)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $vendors = GrowFinanceVendorModel::forBusiness($businessId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

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

        return Inertia::render('GrowFinance/Expenses/Create', [
            'accounts' => $accounts,
            'vendors' => $vendors,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:growfinance_accounts,id',
            'vendor_id' => 'nullable|exists:growfinance_vendors,id',
            'expense_date' => 'required|date',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank,mobile_money,credit',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $businessId = $request->user()->id;

        GrowFinanceExpenseModel::create([
            'business_id' => $businessId,
            ...$validated,
        ]);

        return redirect()->route('growfinance.expenses.index')
            ->with('success', 'Expense recorded successfully!');
    }

    public function show(Request $request, int $id): Response
    {
        $businessId = $request->user()->id;

        $expense = GrowFinanceExpenseModel::forBusiness($businessId)
            ->with(['vendor', 'account'])
            ->findOrFail($id);

        return Inertia::render('GrowFinance/Expenses/Show', [
            'expense' => $expense,
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $businessId = $request->user()->id;

        $expense = GrowFinanceExpenseModel::forBusiness($businessId)
            ->findOrFail($id);

        $accounts = GrowFinanceAccountModel::forBusiness($businessId)
            ->active()
            ->ofType(AccountType::EXPENSE)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $vendors = GrowFinanceVendorModel::forBusiness($businessId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

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

        return Inertia::render('GrowFinance/Expenses/Edit', [
            'expense' => $expense,
            'accounts' => $accounts,
            'vendors' => $vendors,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:growfinance_accounts,id',
            'vendor_id' => 'nullable|exists:growfinance_vendors,id',
            'expense_date' => 'required|date',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank,mobile_money,credit',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $businessId = $request->user()->id;

        $expense = GrowFinanceExpenseModel::forBusiness($businessId)
            ->findOrFail($id);

        $expense->update($validated);

        return redirect()->route('growfinance.expenses.index')
            ->with('success', 'Expense updated successfully!');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;

        $expense = GrowFinanceExpenseModel::forBusiness($businessId)
            ->findOrFail($id);

        $expense->delete();

        return back()->with('success', 'Expense deleted successfully!');
    }
}
