<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\ExpenseService;
use App\Infrastructure\Persistence\Eloquent\CMS\ExpenseModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ExpenseCategoryModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel;
use App\Notifications\CMS\ExpenseApprovalRequiredNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseService $expenseService
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $query = ExpenseModel::with(['category', 'job', 'recordedBy.user'])
            ->where('company_id', $companyId);

        // Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('expense_number', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $expenses = $query->orderBy('expense_date', 'desc')
            ->paginate(20)
            ->withQueryString();

        $categories = ExpenseCategoryModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        $summary = [
            'total_expenses' => ExpenseModel::where('company_id', $companyId)
                ->where('approval_status', 'approved')
                ->sum('amount'),
            'pending_approval' => ExpenseModel::where('company_id', $companyId)
                ->where('approval_status', 'pending')
                ->count(),
            'this_month' => ExpenseModel::where('company_id', $companyId)
                ->where('approval_status', 'approved')
                ->whereMonth('expense_date', now()->month)
                ->sum('amount'),
        ];

        return Inertia::render('CMS/Expenses/Index', [
            'expenses' => $expenses,
            'categories' => $categories,
            'summary' => $summary,
            'filters' => $request->only(['category_id', 'approval_status', 'search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:cms_expense_categories,id',
            'job_id' => 'nullable|exists:cms_jobs,id',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer,mtn_momo,airtel_money,company_card',
            'receipt_number' => 'nullable|string|max:100',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $userId = $request->user()->cmsUser->id;

        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            $receiptData = $this->expenseService->uploadReceipt(
                $request->file('receipt'),
                $companyId
            );
            $validated['receipt_path'] = $receiptData['path'];
        }

        $expense = $this->expenseService->createExpense($validated, $companyId, $userId);

        // Notify managers/admins about expense approval
        $managers = CmsUserModel::where('company_id', $companyId)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['owner', 'manager']);
            })
            ->with('user')
            ->get();

        foreach ($managers as $manager) {
            if ($manager->user) {
                $manager->user->notify(new ExpenseApprovalRequiredNotification([
                    'id' => $expense->id,
                    'expense_number' => $expense->expense_number,
                    'description' => $expense->description,
                    'amount' => $expense->amount,
                    'category_name' => $expense->category->name,
                    'submitted_by' => $request->user()->name,
                ]));
            }
        }

        return redirect()->route('cms.expenses.index')
            ->with('success', 'Expense recorded successfully');
    }

    public function approve(Request $request, int $id)
    {
        $userId = $request->user()->cmsUser->id;
        
        $expense = $this->expenseService->approveExpense($id, $userId);

        return back()->with('success', 'Expense approved successfully');
    }

    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $userId = $request->user()->cmsUser->id;
        
        $expense = $this->expenseService->rejectExpense($id, $userId, $validated['reason']);

        return back()->with('success', 'Expense rejected');
    }
}
