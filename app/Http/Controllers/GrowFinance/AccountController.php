<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\ValueObjects\AccountType;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceAccountModel;
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
            ->get()
            ->groupBy('type');

        return Inertia::render('GrowFinance/Accounts/Index', [
            'accounts' => $accounts,
            'accountTypes' => collect(AccountType::cases())->map(fn($type) => [
                'value' => $type->value,
                'label' => $type->label(),
                'color' => $type->color(),
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('GrowFinance/Accounts/Create', [
            'accountTypes' => collect(AccountType::cases())->map(fn($type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,income,expense',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'opening_balance' => 'nullable|numeric',
        ]);

        $businessId = $request->user()->id;

        // Check for duplicate code
        $exists = GrowFinanceAccountModel::forBusiness($businessId)
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['code' => 'This account code already exists.']);
        }

        GrowFinanceAccountModel::create([
            'business_id' => $businessId,
            'current_balance' => $validated['opening_balance'] ?? 0,
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

        return Inertia::render('GrowFinance/Accounts/Show', [
            'account' => $account,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $businessId = $request->user()->id;

        $account = GrowFinanceAccountModel::forBusiness($businessId)
            ->findOrFail($id);

        return Inertia::render('GrowFinance/Accounts/Edit', [
            'account' => $account,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $businessId = $request->user()->id;

        $account = GrowFinanceAccountModel::forBusiness($businessId)
            ->findOrFail($id);

        if ($account->is_system) {
            return back()->withErrors(['error' => 'System accounts cannot be modified.']);
        }

        $account->update($validated);

        return redirect()->route('growfinance.accounts.index')
            ->with('success', 'Account updated successfully!');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;

        $account = GrowFinanceAccountModel::forBusiness($businessId)
            ->findOrFail($id);

        if ($account->is_system) {
            return back()->withErrors(['error' => 'System accounts cannot be deleted.']);
        }

        if ($account->journalLines()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete account with transactions.']);
        }

        $account->delete();

        return back()->with('success', 'Account deleted successfully!');
    }
}
