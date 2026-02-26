<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\IdempotencyService;
use App\Application\Notification\UseCases\SendNotificationUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class LoanApplicationController extends Controller
{
    public function __construct(
        private readonly IdempotencyService $idempotencyService
    ) {}
    
    /**
     * Show loan application page
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        // Get user's loan status
        $hasActiveLoan = $user->loan_balance > 0;
        $availableCredit = $user->loan_limit - $user->loan_balance;
        
        // Get pending applications
        $pendingApplications = DB::table('loan_applications')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();
        
        // Get loan history (approved/rejected)
        $loanHistory = DB::table('loan_applications')
            ->where('user_id', $user->id)
            ->whereIn('status', ['approved', 'rejected'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
        
        // Calculate eligibility
        $isEligible = $this->checkEligibility($user);
        
        return Inertia::render('GrowNet/LoanApplication', [
            'hasActiveLoan' => $hasActiveLoan,
            'loanBalance' => $user->loan_balance,
            'loanLimit' => $user->loan_limit,
            'availableCredit' => $availableCredit,
            'totalIssued' => $user->loan_total_issued,
            'totalRepaid' => $user->loan_total_repaid,
            'pendingApplications' => $pendingApplications,
            'loanHistory' => $loanHistory,
            'eligibility' => $isEligible,
        ]);
    }
    
    /**
     * Submit loan application
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100|max:5000',
            'purpose' => 'required|string|min:20|max:500',
            'repayment_plan' => 'required|in:30_days,60_days,90_days',
        ]);
        
        // Check eligibility
        $eligibility = $this->checkEligibility($user);
        if (!$eligibility['eligible']) {
            return back()->withErrors(['amount' => $eligibility['reason']]);
        }
        
        // Check if amount exceeds available credit
        $availableCredit = $user->loan_limit - $user->loan_balance;
        if ($validated['amount'] > $availableCredit) {
            return back()->withErrors([
                'amount' => "Amount exceeds available credit of K" . number_format($availableCredit, 2)
            ]);
        }
        
        // Check for pending applications
        $hasPending = DB::table('loan_applications')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();
        
        if ($hasPending) {
            return back()->withErrors([
                'amount' => 'You already have a pending loan application. Please wait for review.'
            ]);
        }
        
        // Generate idempotency key
        $timestamp = floor(time() / 300) * 300; // Round to 5 minutes
        $idempotencyKey = $this->idempotencyService->generateKey(
            $user->id,
            'loan_application',
            [
                'amount' => $validated['amount'],
                'timestamp' => $timestamp
            ]
        );
        
        // Check if already submitted
        if ($this->idempotencyService->wasCompleted($idempotencyKey)) {
            return redirect()->route('mygrownet.loans.index')
                ->with('info', 'This loan application was already submitted.');
        }
        
        try {
            $result = $this->idempotencyService->execute(
                $idempotencyKey,
                function () use ($user, $validated) {
                    return $this->createApplication($user, $validated);
                },
                lockDuration: 30,
                keyTtl: 600
            );
            
            return redirect()->route('mygrownet.loans.index')
                ->with('success', $result['message']);
                
        } catch (\Exception $e) {
            Log::error('Loan application failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Application failed: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Create loan application
     */
    private function createApplication(User $user, array $validated): array
    {
        return DB::transaction(function () use ($user, $validated) {
            // Create application
            $applicationId = DB::table('loan_applications')->insertGetId([
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'purpose' => $validated['purpose'],
                'repayment_plan' => $validated['repayment_plan'],
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            Log::info('Loan application created', [
                'application_id' => $applicationId,
                'user_id' => $user->id,
                'amount' => $validated['amount'],
            ]);
            
            // Notify user
            try {
                $notificationService = app(SendNotificationUseCase::class);
                $notificationService->execute(
                    userId: $user->id,
                    type: 'loan.application.submitted',
                    data: [
                        'title' => 'Loan Application Submitted',
                        'message' => "Your loan application for K{$validated['amount']} has been submitted and is under review.",
                        'amount' => 'K' . number_format($validated['amount'], 2),
                        'action_url' => route('mygrownet.loans.index'),
                        'action_text' => 'View Application',
                        'priority' => 'normal'
                    ]
                );
            } catch (\Exception $e) {
                Log::warning('Failed to send notification', ['error' => $e->getMessage()]);
            }
            
            // Notify admins
            $this->notifyAdmins($user, $validated['amount'], $applicationId);
            
            return [
                'success' => true,
                'message' => 'Loan application submitted successfully. You will be notified once reviewed.',
                'application_id' => $applicationId,
            ];
        });
    }
    
    /**
     * Check if user is eligible for a loan
     */
    private function checkEligibility(User $user): array
    {
        // Must have active subscription
        if ($user->status !== 'active') {
            return [
                'eligible' => false,
                'reason' => 'Your account must be active to apply for a loan.',
            ];
        }
        
        // Must have a starter kit (basic or premium)
        if (!$user->has_starter_kit) {
            return [
                'eligible' => false,
                'reason' => 'You must have a starter kit to apply for loans. Please purchase a starter kit first.',
            ];
        }
        
        // Check if has existing loan with poor repayment
        if ($user->loan_balance > 0) {
            $repaymentRate = $user->loan_total_issued > 0 
                ? ($user->loan_total_repaid / $user->loan_total_issued) * 100 
                : 0;
            
            if ($repaymentRate < 50) {
                return [
                    'eligible' => false,
                    'reason' => 'Please repay at least 50% of your current loan before applying for another.',
                ];
            }
        }
        
        // Check available credit
        $availableCredit = $user->loan_limit - $user->loan_balance;
        if ($availableCredit < 100) {
            return [
                'eligible' => false,
                'reason' => 'Insufficient loan limit. Available credit: K' . number_format($availableCredit, 2),
            ];
        }
        
        return [
            'eligible' => true,
            'reason' => null,
            'available_credit' => $availableCredit,
        ];
    }
    
    /**
     * Notify admins of new loan application
     */
    private function notifyAdmins(User $user, float $amount, int $applicationId): void
    {
        try {
            $admins = User::role('admin')->get();
            $notificationService = app(SendNotificationUseCase::class);
            
            foreach ($admins as $admin) {
                $notificationService->execute(
                    userId: $admin->id,
                    type: 'admin.loan.application.new',
                    data: [
                        'title' => 'New Loan Application',
                        'message' => "{$user->name} has applied for a loan of K" . number_format($amount, 2),
                        'amount' => 'K' . number_format($amount, 2),
                        'applicant' => $user->name,
                        'action_url' => route('admin.loans.index'),
                        'action_text' => 'Review Application',
                        'priority' => 'high'
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::warning('Failed to notify admins', ['error' => $e->getMessage()]);
        }
    }
}
