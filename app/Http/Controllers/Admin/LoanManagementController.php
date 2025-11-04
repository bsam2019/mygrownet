<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Domain\Financial\Services\LoanService;
use App\Domain\Financial\ValueObjects\LoanAmount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoanManagementController extends Controller
{
    public function __construct(
        private readonly LoanService $loanService
    ) {}

    /**
     * Display loan management page
     */
    public function index()
    {
        return Inertia::render('Admin/Loans/Index');
    }

    /**
     * Issue a loan to a member
     */
    public function issueLoan(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:1|max:10000',
                'notes' => 'nullable|string|max:500',
            ]);

            \Log::info('Issuing loan', [
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'issued_by' => $request->user()->id
            ]);

            $this->loanService->issueLoan(
                member: $user,
                amount: LoanAmount::fromFloat($validated['amount']),
                issuedBy: $request->user(),
                notes: $validated['notes'] ?? null
            );

            \Log::info('Loan issued successfully', ['user_id' => $user->id]);

            return back()->with('success', "Loan of K{$validated['amount']} issued to {$user->name}. Funds credited to wallet.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Loan validation failed', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Loan issuance failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to issue loan: ' . $e->getMessage());
        }
    }

    /**
     * Get loan summary for a member
     */
    public function getLoanSummary(User $user)
    {
        return response()->json(
            $this->loanService->getLoanSummary($user)
        );
    }

    /**
     * Get all members with outstanding loans
     */
    public function getMembersWithLoans()
    {
        $members = User::where('loan_balance', '>', 0)
            ->with(['loanIssuedBy:id,name'])
            ->select([
                'id',
                'name',
                'email',
                'phone',
                'loan_balance',
                'total_loan_issued',
                'total_loan_repaid',
                'loan_issued_at',
                'loan_issued_by',
                'loan_notes',
            ])
            ->orderBy('loan_issued_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'loan_balance' => $user->loan_balance,
                    'total_issued' => $user->total_loan_issued,
                    'total_repaid' => $user->total_loan_repaid,
                    'repayment_progress' => $this->loanService->getRepaymentProgress($user),
                    'issued_at' => $user->loan_issued_at,
                    'issued_by' => $user->loanIssuedBy?->name,
                    'notes' => $user->loan_notes,
                ];
            });

        return response()->json($members);
    }
}
