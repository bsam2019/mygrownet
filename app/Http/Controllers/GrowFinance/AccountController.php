<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\ValueObjects\AccountType;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceAccountModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;

        $accounts = GrowFinanceAccountModel::forBusiness($businessId)
            ->orderBy('code')
            ->get();

        $accountTypes = collect(AccountType::cases())->map(fn($type) => [
            'value' => $type->value,
            'label' => $type->label(),
            'color' => $type->color(),
        ]);

        return Inertia::render('GrowFinance/Accounts/Index', [
            'accounts' => $accounts,
            'accountTypes' => $accountTypes,
        ]);
    }

    public function create(): Response
    {
        $businessId = request()->user()->id;

        $parentAccounts = GrowFinanceAccountModel::forBusiness($businessId)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type', 'level']);

        return Inertia::render('GrowFinance/Accounts/Create', [
            'accountTypes' => collect(AccountType::cases())->map(fn($type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ]),
            'parentAccounts' => $parentAccounts,
            'statementCategories' => [
                'cash', 'receivables', 'inventory', 'prepayments',
                'fixed_asset', 'current_liability', 'long_term_liability',
                'payables', 'accruals', 'borrowings', 'tax',
                'equity', 'retained_earnings', 'drawings',
                'operating_revenue', 'other_income',
                'cost_of_sales', 'operating_expense',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,income,expense',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id' => 'nullable|integer|exists:growfinance_accounts,id',
            'statement_category' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'opening_balance' => 'nullable|numeric',
            'is_active' => 'boolean',
        ]);

        $businessId = $request->user()->id;

        $exists = GrowFinanceAccountModel::forBusiness($businessId)
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['code' => 'This account code already exists.']);
        }

        $level = 1;
        $path = $validated['code'];

        if (isset($validated['parent_id']) && $validated['parent_id']) {
            $parent = GrowFinanceAccountModel::forBusiness($businessId)->find($validated['parent_id']);
            if ($parent) {
                $level = $parent->level + 1;
                $path = ($parent->path ?? $parent->code) . '/' . $validated['code'];
            }
        }

        GrowFinanceAccountModel::create([
            'business_id' => $businessId,
            'current_balance' => $validated['opening_balance'] ?? 0,
            'level' => $level,
            'path' => $path,
            ...$validated,
        ]);

        return redirect()->route('growfinance.accounts.index')
            ->with('success', 'Account created successfully!');
    }

    public function show(Request $request, int $id): Response
    {
        $businessId = $request->user()->id;

        $account = GrowFinanceAccountModel::forBusiness($businessId)
            ->with(['journalLines.journalEntry'])
            ->findOrFail($id);

        $recentTransactions = $account->journalLines()
            ->with('journalEntry')
            ->latest('id')
            ->limit(20)
            ->get();

        $children = GrowFinanceAccountModel::forBusiness($businessId)
            ->where('parent_id', $id)
            ->orderBy('code')
            ->get();

        $parent = $account->parent_id
            ? GrowFinanceAccountModel::find($account->parent_id)
            : null;

        return Inertia::render('GrowFinance/Accounts/Show', [
            'account' => $account,
            'recentTransactions' => $recentTransactions,
            'children' => $children,
            'parentAccount' => $parent,
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $businessId = $request->user()->id;

        $account = GrowFinanceAccountModel::forBusiness($businessId)->findOrFail($id);

        $parentAccounts = GrowFinanceAccountModel::forBusiness($businessId)
            ->where('id', '!=', $id)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type', 'level']);

        return Inertia::render('GrowFinance/Accounts/Edit', [
            'account' => $account,
            'parentAccounts' => $parentAccounts,
            'statementCategories' => [
                'cash', 'receivables', 'inventory', 'prepayments',
                'fixed_asset', 'current_liability', 'long_term_liability',
                'payables', 'accruals', 'borrowings', 'tax',
                'equity', 'retained_earnings', 'drawings',
                'operating_revenue', 'other_income',
                'cost_of_sales', 'operating_expense',
            ],
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'normal_balance' => 'nullable|in:debit,credit',
            'parent_id' => 'nullable|integer|exists:growfinance_accounts,id',
            'statement_category' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $businessId = $request->user()->id;

        $account = GrowFinanceAccountModel::forBusiness($businessId)->findOrFail($id);

        if ($account->is_system) {
            return back()->withErrors(['error' => 'System accounts cannot be modified.']);
        }

        $updateData = $validated;

        if ($request->has('parent_id')) {
            $level = 1;
            $path = $account->code;

        if (isset($validated['parent_id']) && $validated['parent_id']) {
                $parent = GrowFinanceAccountModel::forBusiness($businessId)->find($validated['parent_id']);
                if ($parent) {
                    $level = $parent->level + 1;
                    $path = ($parent->path ?? $parent->code) . '/' . $account->code;
                }
            }

            $updateData['level'] = $level;
            $updateData['path'] = $path;
        }

        $account->update($updateData);

        return redirect()->route('growfinance.accounts.index')
            ->with('success', 'Account updated successfully!');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;

        $account = GrowFinanceAccountModel::forBusiness($businessId)->findOrFail($id);

        if ($account->is_system) {
            return back()->withErrors(['error' => 'System accounts cannot be deleted.']);
        }

        $hasChildren = GrowFinanceAccountModel::forBusiness($businessId)
            ->where('parent_id', $id)
            ->exists();

        if ($hasChildren) {
            return back()->withErrors(['error' => 'Cannot delete an account with sub-accounts.']);
        }

        if ($account->journalLines()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete account with transactions.']);
        }

        $account->delete();

        return back()->with('success', 'Account deleted successfully!');
    }
}
