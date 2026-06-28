<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\LoanAccountingService;
use App\Infrastructure\Persistence\Eloquent\CMS\LoanReceivableModel;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoanController extends Controller
{
    public function __construct(
        private LoanAccountingService $loanService
    ) {}

    /**
     * Display loans list for current company
     */
    public function index(Request $request): Response
    {
        $companyId = session('cms_company_id');
        
        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
            'risk_category' => $request->query('risk_category'),
        ];
        
        $query = LoanReceivableModel::where('company_id', $companyId)
            ->with('user:id,name,email');
        
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
        
        $loans = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get summary statistics
        $summary = [
            'total_loans' => LoanReceivableModel::where('company_id', $companyId)->count(),
            'active_loans' => LoanReceivableModel::where('company_id', $companyId)->where('status', 'active')->count(),
            'total_outstanding' => LoanReceivableModel::where('company_id', $companyId)->where('status', 'active')->sum('outstanding_balance'),
            'overdue_loans' => LoanReceivableModel::where('company_id', $companyId)->where('days_overdue', '>', 0)->count(),
            'defaulted_loans' => LoanReceivableModel::where('company_id', $companyId)->where('status', 'defaulted')->count(),
        ];
        
        return Inertia::render('CMS/Loans/Index', [
            'loans' => $loans,
            'summary' => $summary,
            'filters' => $filters ?? [
                'search' => null,
                'status' => null,
                'risk_category' => null,
            ],
        ]);
    }

    /**
     * Show create loan form
     */
    public function create(): Response
    {
        // Get company users/customers who can receive loans
        $companyId = session('cms_company_id');
        
        // For now, get platform users - in production, this would be company-specific customers
        $users = User::where('account_type', 'member')
            ->where('subscription_status', 'active')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();
        
        return Inertia::render('CMS/Loans/Create', [
            'users' => $users,
        ]);
    }

    /**
     * Store new loan
     */
    public function store(Request $request)
    {
        $companyId = session('cms_company_id');
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'principal_amount' => 'required|numeric|min:100|max:1000000',
            'interest_rate' => 'required|numeric|min:0|max:50',
            'term_months' => 'required|integer|min:1|max:60',
            'loan_type' => 'required|in:member_loan,business_loan,emergency_loan',
            'purpose' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        try {
            $user = User::findOrFail($validated['user_id']);
            
            $loan = $this->loanService->disburseLoan(
                companyId: $companyId,
                user: $user,
                principalAmount: $validated['principal_amount'],
                interestRate: $validated['interest_rate'],
                termMonths: $validated['term_months'],
                loanType: $validated['loan_type'],
                purpose: $validated['purpose'] ?? null,
                notes: $validated['notes'] ?? null,
                approvedBy: auth()->id()
            );
            
            return redirect()
                ->route('cms.loans.show', $loan->id)
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
        $companyId = session('cms_company_id');
        
        $loan = LoanReceivableModel::where('company_id', $companyId)
            ->with(['user', 'repayments', 'schedules'])
            ->findOrFail($id);
        
        return Inertia::render('CMS/Loans/Show', [
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
                'notes' => $loan->notes,
                'user' => [
                    'id' => $loan->user->id,
                    'name' => $loan->user->name,
                    'email' => $loan->user->email,
                ],
                'payments' => $loan->repayments->map(fn($r) => [
                    'id' => $r->id,
                    'payment_reference' => $r->payment_reference,
                    'payment_amount' => $r->payment_amount,
                    'principal_portion' => $r->principal_portion,
                    'interest_portion' => $r->interest_portion,
                    'penalty_portion' => $r->penalty_portion,
                    'payment_date' => $r->payment_date->format('Y-m-d'),
                    'payment_method' => $r->payment_method,
                    'notes' => $r->notes,
                ]),
                'schedule' => $loan->schedules->map(fn($s) => [
                    'id' => $s->id,
                    'installment_number' => $s->installment_number,
                    'due_date' => $s->due_date->format('Y-m-d'),
                    'installment_amount' => $s->installment_amount,
                    'principal_portion' => $s->principal_portion,
                    'interest_portion' => $s->interest_portion,
                    'amount_paid' => $s->amount_paid,
                    'status' => $s->status,
                    'paid_date' => $s->paid_date?->format('Y-m-d'),
                ]),
            ],
        ]);
    }

    /**
     * Show payment form
     */
    public function paymentForm(int $id): Response
    {
        $companyId = session('cms_company_id');
        
        $loan = LoanReceivableModel::where('company_id', $companyId)
            ->with('user')
            ->findOrFail($id);
        
        return Inertia::render('CMS/Loans/RecordPayment', [
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
        $companyId = session('cms_company_id');
        
        $validated = $request->validate([
            'payment_amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:wallet,bank_transfer,cash,mobile_money',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);
        
        try {
            $loan = LoanReceivableModel::where('company_id', $companyId)->findOrFail($id);
            
            if ($validated['payment_amount'] > $loan->outstanding_balance) {
                return back()
                    ->withInput()
                    ->with('error', 'Payment amount cannot exceed outstanding balance');
            }
            
            $repayment = $this->loanService->recordRepayment(
                loan: $loan,
                paymentAmount: $validated['payment_amount'],
                paymentMethod: $validated['payment_method'],
                paymentDate: $validated['payment_date'],
                notes: $validated['notes'] ?? null
            );
            
            return redirect()
                ->route('cms.loans.show', $loan->id)
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
        $companyId = session('cms_company_id');
        
        $aging = $this->loanService->getAgingReport($companyId);
        $loansByCategory = $this->loanService->getLoansByRiskCategory($companyId);
        
        return Inertia::render('CMS/Loans/AgingReport', [
            'aging' => $aging,
            'loans_by_category' => $loansByCategory,
        ]);
    }
}
