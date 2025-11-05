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
        // Get pending loan applications
        $pendingApplications = \DB::table('loan_applications')
            ->join('users', 'loan_applications.user_id', '=', 'users.id')
            ->where('loan_applications.status', 'pending')
            ->select(
                'loan_applications.*',
                'users.name as user_name',
                'users.email as user_email',
                'users.phone as user_phone',
                'users.loan_balance',
                'users.loan_limit'
            )
            ->orderByDesc('loan_applications.created_at')
            ->get();
        
        return Inertia::render('Admin/Loans/Index', [
            'pendingApplications' => $pendingApplications,
        ]);
    }
    
    /**
     * Approve a loan application
     */
    public function approveApplication(Request $request, int $applicationId)
    {
        $application = \DB::table('loan_applications')->find($applicationId);
        
        if (!$application || $application->status !== 'pending') {
            return back()->withErrors(['error' => 'Application not found or already processed']);
        }
        
        $user = User::find($application->user_id);
        
        try {
            \DB::beginTransaction();
            
            // Issue the loan
            $this->loanService->issueLoan(
                member: $user,
                amount: LoanAmount::fromFloat($application->amount),
                issuedBy: $request->user(),
                notes: "Approved from application #{$applicationId}"
            );
            
            // Update application status
            \DB::table('loan_applications')
                ->where('id', $applicationId)
                ->update([
                    'status' => 'approved',
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                    'updated_at' => now(),
                ]);
            
            \DB::commit();
            
            return back()->with('success', "Loan approved and issued to {$user->name}");
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to approve loan application', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Failed to approve application: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Reject a loan application
     */
    public function rejectApplication(Request $request, int $applicationId)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500',
        ]);
        
        $application = \DB::table('loan_applications')->find($applicationId);
        
        if (!$application || $application->status !== 'pending') {
            return back()->withErrors(['error' => 'Application not found or already processed']);
        }
        
        try {
            // Update application status
            \DB::table('loan_applications')
                ->where('id', $applicationId)
                ->update([
                    'status' => 'rejected',
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                    'rejection_reason' => $validated['rejection_reason'],
                    'updated_at' => now(),
                ]);
            
            // Notify user
            $user = User::find($application->user_id);
            $notificationService = app(\App\Application\Notification\UseCases\SendNotificationUseCase::class);
            $notificationService->execute(
                userId: $user->id,
                type: 'loan.application.rejected',
                data: [
                    'title' => 'Loan Application Rejected',
                    'message' => "Your loan application for K{$application->amount} has been rejected. Reason: {$validated['rejection_reason']}",
                    'amount' => 'K' . number_format($application->amount, 2),
                    'reason' => $validated['rejection_reason'],
                    'action_url' => route('mygrownet.loans.index'),
                    'action_text' => 'View Details',
                    'priority' => 'normal'
                ]
            );
            
            return back()->with('success', 'Loan application rejected');
            
        } catch (\Exception $e) {
            \Log::error('Failed to reject loan application', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Failed to reject application: ' . $e->getMessage()]);
        }
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
