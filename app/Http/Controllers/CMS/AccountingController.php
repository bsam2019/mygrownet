<?php

namespace App\Http\Controllers\CMS;

use App\Domain\CMS\Core\Services\ChartOfAccountsService;
use App\Domain\CMS\Core\ValueObjects\AccountType;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\AccountModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JournalEntryModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountingController extends Controller
{
    public function __construct(
        private ChartOfAccountsService $chartService
    ) {}

    public function index(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $accounts = AccountModel::forCompany($companyId)
            ->orderBy('code')
            ->get()
            ->groupBy('type');

        return Inertia::render('CMS/Accounting/Index', [
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
        return Inertia::render('CMS/Accounting/Create', [
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

        $companyId = $request->user()->cmsUser->company_id;

        $exists = AccountModel::forCompany($companyId)
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['code' => 'This account code already exists.']);
        }

        AccountModel::create([
            'company_id' => $companyId,
            'current_balance' => $validated['opening_balance'] ?? 0,
            ...$validated,
        ]);

        return redirect()->route('cms.accounting.index')
            ->with('success', 'Account created successfully!');
    }

    public function show(Request $request, int $id): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $account = AccountModel::forCompany($companyId)
            ->with(['journalLines.journalEntry'])
            ->findOrFail($id);

        $recentTransactions = $account->journalLines()
            ->with('journalEntry')
            ->latest('id')
            ->limit(50)
            ->get();

        return Inertia::render('CMS/Accounting/Show', [
            'account' => $account,
            'recentTransactions' => $recentTransactions,
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

        $companyId = $request->user()->cmsUser->company_id;

        $account = AccountModel::forCompany($companyId)->findOrFail($id);

        if ($account->is_system) {
            return back()->withErrors(['error' => 'System accounts cannot be modified.']);
        }

        $account->update($validated);

        return redirect()->route('cms.accounting.index')
            ->with('success', 'Account updated successfully!');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $companyId = $request->user()->cmsUser->company_id;

        $account = AccountModel::forCompany($companyId)->findOrFail($id);

        if ($account->is_system) {
            return back()->withErrors(['error' => 'System accounts cannot be deleted.']);
        }

        if ($account->journalLines()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete account with transactions.']);
        }

        $account->delete();

        return back()->with('success', 'Account deleted successfully!');
    }

    public function trialBalance(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $trialBalance = $this->chartService->getTrialBalance($companyId);

        return Inertia::render('CMS/Accounting/TrialBalance', [
            'trialBalance' => $trialBalance,
        ]);
    }

    public function journalEntries(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $entries = JournalEntryModel::forCompany($companyId)
            ->with(['lines.account', 'createdBy'])
            ->latest('entry_date')
            ->paginate(20);

        return Inertia::render('CMS/Accounting/JournalEntries', [
            'entries' => $entries,
        ]);
    }

    public function initializeAccounts(Request $request): RedirectResponse
    {
        $companyId = $request->user()->cmsUser->company_id;

        $this->chartService->initializeChartOfAccounts($companyId);

        return back()->with('success', 'Chart of accounts initialized successfully!');
    }
}
