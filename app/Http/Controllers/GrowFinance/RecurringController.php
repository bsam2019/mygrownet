<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\RecurringTransactionService;
use App\Domain\Module\Services\SubscriptionService;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceAccountModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceCustomerModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceRecurringTransactionModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceVendorModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecurringController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private RecurringTransactionService $recurringService
    ) {}

    public function index(Request $request): Response
    {
        // Check if user has recurring feature
        if (!$this->subscriptionService->canPerformAction($request->user(), 'recurring_transactions')) {
            return Inertia::render('GrowFinance/FeatureUpgradeRequired', [
                'feature' => 'Recurring Transactions',
                'requiredTier' => 'professional',
            ]);
        }

        $recurring = $this->recurringService->getForBusiness($request->user());
        $upcoming = $this->recurringService->getUpcoming($request->user(), 14);

        return Inertia::render('GrowFinance/Recurring/Index', [
            'recurring' => $recurring,
            'upcoming' => $upcoming,
        ]);
    }

    public function create(Request $request): Response
    {
        if (!$this->subscriptionService->canPerformAction($request->user(), 'recurring_transactions')) {
            return Inertia::render('GrowFinance/FeatureUpgradeRequired', [
                'feature' => 'Recurring Transactions',
                'requiredTier' => 'professional',
            ]);
        }

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

        $customers = GrowFinanceCustomerModel::forBusiness($businessId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

        $frequencies = [
            ['value' => 'daily', 'label' => 'Daily'],
            ['value' => 'weekly', 'label' => 'Weekly'],
            ['value' => 'biweekly', 'label' => 'Every 2 Weeks'],
            ['value' => 'monthly', 'label' => 'Monthly'],
            ['value' => 'quarterly', 'label' => 'Quarterly'],
            ['value' => 'yearly', 'label' => 'Yearly'],
        ];

        $categories = [
            'Rent',
            'Utilities',
            'Salaries & Wages',
            'Insurance',
            'Subscriptions',
            'Loan Payment',
            'Internet & Phone',
            'Other',
        ];

        return Inertia::render('GrowFinance/Recurring/Create', [
            'accounts' => $accounts,
            'vendors' => $vendors,
            'customers' => $customers,
            'frequencies' => $frequencies,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (!$this->subscriptionService->canPerformAction($request->user(), 'recurring_transactions')) {
            return back()->with('error', 'Recurring transactions require Professional plan or higher.');
        }

        $validated = $request->validate([
            'type' => 'required|in:expense,income',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'frequency' => 'required|in:daily,weekly,biweekly,monthly,quarterly,yearly',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'account_id' => 'nullable|exists:growfinance_accounts,id',
            'vendor_id' => 'nullable|exists:growfinance_vendors,id',
            'customer_id' => 'nullable|exists:growfinance_customers,id',
            'category' => 'nullable|string|max:100',
            'payment_method' => 'nullable|in:cash,bank,mobile_money,credit',
            'max_occurrences' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->recurringService->create($request->user(), $validated);

        return redirect()->route('growfinance.recurring.index')
            ->with('success', 'Recurring transaction created successfully!');
    }

    public function show(Request $request, int $id): Response
    {
        $recurring = GrowFinanceRecurringTransactionModel::forBusiness($request->user()->id)
            ->with(['account', 'vendor', 'customer'])
            ->findOrFail($id);

        return Inertia::render('GrowFinance/Recurring/Show', [
            'recurring' => $recurring,
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $businessId = $request->user()->id;

        $recurring = GrowFinanceRecurringTransactionModel::forBusiness($businessId)
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

        $customers = GrowFinanceCustomerModel::forBusiness($businessId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

        $frequencies = [
            ['value' => 'daily', 'label' => 'Daily'],
            ['value' => 'weekly', 'label' => 'Weekly'],
            ['value' => 'biweekly', 'label' => 'Every 2 Weeks'],
            ['value' => 'monthly', 'label' => 'Monthly'],
            ['value' => 'quarterly', 'label' => 'Quarterly'],
            ['value' => 'yearly', 'label' => 'Yearly'],
        ];

        return Inertia::render('GrowFinance/Recurring/Edit', [
            'recurring' => $recurring,
            'accounts' => $accounts,
            'vendors' => $vendors,
            'customers' => $customers,
            'frequencies' => $frequencies,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'frequency' => 'required|in:daily,weekly,biweekly,monthly,quarterly,yearly',
            'end_date' => 'nullable|date',
            'account_id' => 'nullable|exists:growfinance_accounts,id',
            'vendor_id' => 'nullable|exists:growfinance_vendors,id',
            'customer_id' => 'nullable|exists:growfinance_customers,id',
            'category' => 'nullable|string|max:100',
            'payment_method' => 'nullable|in:cash,bank,mobile_money,credit',
            'max_occurrences' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $recurring = GrowFinanceRecurringTransactionModel::forBusiness($request->user()->id)
            ->findOrFail($id);

        $recurring->update($validated);

        return redirect()->route('growfinance.recurring.index')
            ->with('success', 'Recurring transaction updated successfully!');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $recurring = GrowFinanceRecurringTransactionModel::forBusiness($request->user()->id)
            ->findOrFail($id);

        $recurring->delete();

        return back()->with('success', 'Recurring transaction deleted successfully!');
    }

    public function pause(Request $request, int $id): RedirectResponse
    {
        $recurring = GrowFinanceRecurringTransactionModel::forBusiness($request->user()->id)
            ->findOrFail($id);

        $this->recurringService->pause($recurring);

        return back()->with('success', 'Recurring transaction paused.');
    }

    public function resume(Request $request, int $id): RedirectResponse
    {
        $recurring = GrowFinanceRecurringTransactionModel::forBusiness($request->user()->id)
            ->findOrFail($id);

        $this->recurringService->resume($recurring);

        return back()->with('success', 'Recurring transaction resumed.');
    }

    public function process(Request $request): RedirectResponse
    {
        $result = $this->recurringService->processDueTransactions($request->user());

        if ($result['total_processed'] > 0) {
            return back()->with('success', "Processed {$result['total_processed']} recurring transaction(s).");
        }

        return back()->with('info', 'No recurring transactions due for processing.');
    }
}
