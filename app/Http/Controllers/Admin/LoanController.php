<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PlatformLoanService;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoanController extends Controller
{
    public function __construct(
        private PlatformLoanService $loanService
    ) {}

    /**
     * Display loans list
     */
    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
            'risk_category' => $request->query('risk_category'),
        ];
        
        try {
            $query = $this->loanService->getLoansQuery();
            
            // Apply filters
            if ($filters['search']) {
                $query->where(function ($q) use ($filters) {
                    $q->where('loan_number', 'like', '%' . $filters['search'] . '%')
                      ->orWhereHas('user', function ($q) use ($filters) {
                          $q->where('name', 'like', '%' . $filters['search'] . '%')
                            ->orWhere('email', 'like', '%' . $filters['search'] . '%');
                      });
                });
            }
            
            if ($filters['status']) {
                $query->where('status', $filters['status']);
            }
            
            if ($filters['risk_category']) {
                $query->where('risk_category', $filters['risk_category']);
            }
            
            $loans = $query->with('user:id,name,email')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
            
            // Get summary statistics
            $summary = [
                'total_loans' => $this->loanService->getTotalLoansCount(),
                'active_loans' => $this->loanService->getActiveLoansCount(),
                'total_outstanding' => $this->loanService->getTotalOutstanding(),
                'overdue_loans' => $this->loanService->getOverdueLoansCount(),
                'defaulted_loans' => $this->loanService->getDefaultedLoansCount(),
            ];
            
        } catch (\Exception $e) {
            // If MyGrowNet Platform company doesn't exist, show empty state
            $loans = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $summary = [
                'total_loans' => 0,
                'active_loans' => 0,
                'total_outstanding' => 0,
                'overdue_loans' => 0,
                'defaulted_loans' => 0,
            ];
            
            // Flash error message
            session()->flash('error', 'Platform company not found. Please run: php artisan db:seed --class=MyGrowNetPlatformCompanySeeder');
        }
        
        return Inertia::render('Admin/Loans/Index', [
            'loans' => $loans,
            'summary' => $summary,
            'filters' => $filters,
        ]);
    }

    /**
     * Show create loan form
     */
    public function create(Request $request): Response
    {
        try {
            // Verify platform company exists
            $this->loanService->getTotalLoansCount();
            
            // Get active members
            $users = User::where('account_type', 'member')
                ->where('subscription_status', 'active')
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get();
            
            // Get preselected user ID from query parameter
            $preselectedUserId = $request->query('user_id');
            
            return Inertia::render('Admin/Loans/Create', [
                'users' => $users,
                'preselectedUserId' => $preselectedUserId ? (int)$preselectedUserId : null,
            ]);
            
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.platform-loans.index')
                ->with('error', 'Platform company not found. Please run: php artisan db:seed --class=MyGrowNetPlatformCompanySeeder');
        }
    }

    /**
     * Store new loan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'principal_amount' => 'required|numeric|min:100|max:100000',
            'interest_rate' => 'required|numeric|min:0|max:50',
            'term_months' => 'required|integer|min:1|max:60',
            'loan_type' => 'required|in:member_loan,business_loan,emergency_loan',
            'purpose' => 'nullable|string|max:500',
        ]);
        
        try {
            $user = User::findOrFail($validated['user_id']);
            
            $loan = $this->loanService->disburseLoan(
                user: $user,
                principalAmount: $validated['principal_amount'],
                interestRate: $validated['interest_rate'],
                termMonths: $validated['term_months'],
                loanType: $validated['loan_type'],
                purpose: $validated['purpose'] ?? null,
                approvedBy: auth()->id()
            );
            
            return redirect()
                ->route('admin.platform-loans.show', $loan->id)
                ->with('success', 'Loan created and disbursed successfully!');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to disburse loan: ' . $e->getMessage());
        }
    }

    /**
     * Show loan details
     */
    public function show(int $id): Response
    {
        $loan = $this->loanService->getLoanById($id);
        
        if (!$loan) {
            abort(404, 'Loan not found');
        }
        
        return Inertia::render('Admin/Loans/Show', [
            'loan' => [
                'id' => $loan->id,
                'loan_number' => $loan->loan_number,
                'loan_type' => $loan->loan_type,
                'principal_amount' => $loan->principal_amount,
                'interest_rate' => $loan->interest_rate,
                'total_amount' => $loan->total_amount,
                'amount_paid' => $loan->amount_paid,
                'principal_paid' => $loan->principal_paid,
                'interest_paid' => $loan->interest_paid,
                'outstanding_balance' => $loan->outstanding_balance,
                'term_months' => $loan->term_months,
                'monthly_payment' => $loan->monthly_payment,
                'disbursement_date' => $loan->disbursement_date->format('Y-m-d'),
                'due_date' => $loan->due_date?->format('Y-m-d'),
                'next_payment_date' => $loan->next_payment_date?->format('Y-m-d'),
                'last_payment_date' => $loan->last_payment_date?->format('Y-m-d'),
                'status' => $loan->status,
                'days_overdue' => $loan->days_overdue,
                'risk_category' => $loan->risk_category,
                'purpose' => $loan->purpose,
                'payment_progress' => $loan->payment_progress,
                'user' => [
                    'id' => $loan->user->id,
                    'name' => $loan->user->name,
                    'email' => $loan->user->email,
                ],
                'repayments' => $loan->repayments->map(fn($r) => [
                    'id' => $r->id,
                    'payment_reference' => $r->payment_reference,
                    'payment_amount' => $r->payment_amount,
                    'principal_portion' => $r->principal_portion,
                    'interest_portion' => $r->interest_portion,
                    'penalty_portion' => $r->penalty_portion,
                    'payment_date' => $r->payment_date->format('Y-m-d'),
                    'payment_method' => $r->payment_method,
                ]),
                'schedules' => $loan->schedules->map(fn($s) => [
                    'id' => $s->id,
                    'installment_number' => $s->installment_number,
                    'due_date' => $s->due_date->format('Y-m-d'),
                    'installment_amount' => $s->installment_amount,
                    'principal_portion' => $s->principal_portion,
                    'interest_portion' => $s->interest_portion,
                    'amount_paid' => $s->amount_paid,
                    'status' => $s->status,
                    'paid_date' => $s->paid_date?->format('Y-m-d'),
                    'is_overdue' => $s->isOverdue(),
                ]),
            ],
        ]);
    }

    /**
     * Show payment form
     */
    public function paymentForm(int $id): Response
    {
        $loan = $this->loanService->getLoanById($id);
        
        if (!$loan) {
            abort(404, 'Loan not found');
        }
        
        return Inertia::render('Admin/Loans/RecordPayment', [
            'loan' => [
                'id' => $loan->id,
                'loan_number' => $loan->loan_number,
                'outstanding_balance' => $loan->outstanding_balance,
                'monthly_payment' => $loan->monthly_payment,
                'next_payment_date' => $loan->next_payment_date?->format('Y-m-d'),
                'user' => [
                    'name' => $loan->user->name,
                    'email' => $loan->user->email,
                ],
            ],
        ]);
    }

    /**
     * Record payment
     */
    public function recordPayment(Request $request, int $id)
    {
        $validated = $request->validate([
            'payment_amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:wallet,bank_transfer,cash,mobile_money',
            'notes' => 'nullable|string|max:500',
        ]);
        
        try {
            $loan = $this->loanService->getLoanById($id);
            
            if (!$loan) {
                return back()->with('error', 'Loan not found');
            }
            
            if ($validated['payment_amount'] > $loan->outstanding_balance) {
                return back()
                    ->withInput()
                    ->with('error', 'Payment amount cannot exceed outstanding balance');
            }
            
            $repayment = $this->loanService->recordRepayment(
                loan: $loan,
                paymentAmount: $validated['payment_amount'],
                paymentMethod: $validated['payment_method'],
                notes: $validated['notes'] ?? null
            );
            
            return redirect()
                ->route('admin.platform-loans.show', $loan->id)
                ->with('success', 'Payment recorded successfully!');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }

    /**
     * Show aging report
     */
    public function agingReport(): Response
    {
        $aging = $this->loanService->getAgingReport();
        
        return Inertia::render('Admin/Loans/AgingReport', [
            'aging' => $aging,
        ]);
    }

    /**
     * Show portfolio summary
     */
    public function portfolio(): Response
    {
        $portfolio = $this->loanService->getPortfolioSummary();
        $balanceSheet = $this->loanService->getBalanceSheetData();
        
        return Inertia::render('Admin/Loans/Portfolio', [
            'portfolio' => $portfolio,
            'balanceSheet' => $balanceSheet,
        ]);
    }
}
