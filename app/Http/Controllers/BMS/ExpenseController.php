<?php

namespace App\Http\Controllers\BMS;

use App\Http\Controllers\Controller;
use App\Domain\BMS\Core\Services\ExpenseService;
use App\Domain\BMS\Repositories\ExpenseRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\ExpenseModel;
use App\Infrastructure\Persistence\Eloquent\BMS\ExpenseCategoryModel;
use App\Infrastructure\Persistence\Eloquent\BMS\JobModel;
use App\Infrastructure\Persistence\Eloquent\BMS\CmsUserModel;
use App\Infrastructure\Persistence\Eloquent\BMS\BranchModel;
use App\Notifications\BMS\ExpenseApprovalRequiredNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseService $expenseService,
        private ExpenseRepositoryInterface $expenseRepo
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $query = ExpenseModel::with(['category', 'job', 'recordedBy.user', 'branch'])
            ->where('company_id', $companyId)
            ->forBranch($request->branch_id);

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

        $summary = $this->expenseRepo->getSummary($companyId);

        $branches = BranchModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'branch_name']);

        return Inertia::render('BMS/Expenses/Index', [
            'expenses' => $expenses,
            'categories' => $categories,
            'summary' => $summary,
            'filters' => $request->only(['category_id', 'approval_status', 'search', 'branch_id']),
            'branches' => $branches,
        ]);
    }

    public function create(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $categories = ExpenseCategoryModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $jobs = JobModel::where('company_id', $companyId)
            ->whereIn('status', ['in_progress', 'pending'])
            ->orderBy('job_number', 'desc')
            ->get(['id', 'job_number', 'description']);

        return Inertia::render('BMS/Expenses/Create', [
            'categories' => $categories,
            'jobs' => $jobs,
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

        if ($request->hasFile('receipt')) {
            $receiptData = $this->expenseService->uploadReceipt($request->file('receipt'), $companyId);
            $validated['receipt_path'] = $receiptData['path'];
        }

        $this->expenseService->createExpense($validated, $companyId, $userId);

        $managers = CmsUserModel::where('company_id', $companyId)
            ->whereHas('role', function ($q) {
                $q->whereIn('name', ['owner', 'manager']);
            })
            ->with('user')
            ->get();

        foreach ($managers as $manager) {
            if ($manager->user) {
                $manager->user->notify(new ExpenseApprovalRequiredNotification([
                    'id' => $validated['id'] ?? null,
                    'description' => $validated['description'],
                    'amount' => $validated['amount'],
                    'submitted_by' => $request->user()->name,
                ]));
            }
        }

        return redirect()->route('bms.expenses.index')
            ->with('success', 'Expense recorded successfully');
    }

    public function approve(Request $request, int $id)
    {
        $userId = $request->user()->cmsUser->id;
        $this->expenseService->approveExpense($id, $userId);
        return back()->with('success', 'Expense approved successfully');
    }

    public function reject(Request $request, int $id)
    {
        $validated = $request->validate(['reason' => 'required|string']);
        $userId = $request->user()->cmsUser->id;
        $this->expenseService->rejectExpense($id, $userId, $validated['reason']);
        return back()->with('success', 'Expense rejected');
    }
}
