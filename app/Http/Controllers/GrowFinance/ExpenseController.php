<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\ReceiptStorageService;
use App\Domain\Module\Services\SubscriptionService;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceAccountModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceExpenseModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceVendorModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private ReceiptStorageService $receiptService
    ) {}

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

        // Get transaction usage for limit banner
        $transactionUsage = $this->subscriptionService->canCreateTransaction($request->user());

        return Inertia::render('GrowFinance/Expenses/Index', [
            'expenses' => $expenses,
            'categories' => $categories,
            'transactionUsage' => $transactionUsage,
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
        // Check subscription limits
        $check = $this->subscriptionService->canCreateTransaction($request->user());
        if (!$check['allowed']) {
            return back()->with('error', 'You\'ve reached your monthly transaction limit. Please upgrade your plan to record more expenses.');
        }

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
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:10240',
        ]);

        $businessId = $request->user()->id;

        // Handle receipt upload if provided
        $receiptData = [];
        if ($request->hasFile('receipt')) {
            $uploadResult = $this->receiptService->upload(
                $request->file('receipt'),
                $request->user(),
                'expense'
            );

            if (!$uploadResult['success']) {
                return back()->with('error', $uploadResult['error']);
            }

            $receiptData = [
                'receipt_path' => $uploadResult['path'],
                'receipt_size' => $uploadResult['size'],
                'receipt_original_name' => $uploadResult['original_name'],
                'receipt_mime_type' => $uploadResult['mime_type'],
            ];
        }

        GrowFinanceExpenseModel::create([
            'business_id' => $businessId,
            ...collect($validated)->except('receipt')->toArray(),
            ...$receiptData,
        ]);

        // Clear usage cache
        $this->subscriptionService->clearUsageCache($request->user());

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

        // Delete receipt file if exists
        if ($expense->receipt_path) {
            $this->receiptService->delete($expense->receipt_path, $request->user());
        }

        $expense->delete();

        return back()->with('success', 'Expense deleted successfully!');
    }

    /**
     * Upload receipt for existing expense
     */
    public function uploadReceipt(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,gif,pdf|max:10240',
        ]);

        $businessId = $request->user()->id;
        $expense = GrowFinanceExpenseModel::forBusiness($businessId)->findOrFail($id);

        // Delete old receipt if exists
        if ($expense->receipt_path) {
            $this->receiptService->delete($expense->receipt_path, $request->user());
        }

        $uploadResult = $this->receiptService->upload(
            $request->file('receipt'),
            $request->user(),
            'expense'
        );

        if (!$uploadResult['success']) {
            return back()->with('error', $uploadResult['error']);
        }

        $expense->update([
            'receipt_path' => $uploadResult['path'],
            'receipt_size' => $uploadResult['size'],
            'receipt_original_name' => $uploadResult['original_name'],
            'receipt_mime_type' => $uploadResult['mime_type'],
        ]);

        return back()->with('success', 'Receipt uploaded successfully!');
    }

    /**
     * View/download receipt
     */
    public function viewReceipt(Request $request, int $id)
    {
        $businessId = $request->user()->id;
        $expense = GrowFinanceExpenseModel::forBusiness($businessId)->findOrFail($id);

        if (!$expense->receipt_path) {
            abort(404, 'No receipt attached to this expense.');
        }

        $contents = $this->receiptService->getContents($expense->receipt_path, $request->user());

        if (!$contents) {
            abort(404, 'Receipt file not found.');
        }

        $mimeType = $expense->receipt_mime_type ?? 'application/octet-stream';
        $filename = $expense->receipt_original_name ?? 'receipt';

        return response($contents, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Delete receipt from expense
     */
    public function deleteReceipt(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;
        $expense = GrowFinanceExpenseModel::forBusiness($businessId)->findOrFail($id);

        if ($expense->receipt_path) {
            $this->receiptService->delete($expense->receipt_path, $request->user());
            
            $expense->update([
                'receipt_path' => null,
                'receipt_size' => null,
                'receipt_original_name' => null,
                'receipt_mime_type' => null,
            ]);
        }

        return back()->with('success', 'Receipt deleted successfully!');
    }
}
