<?php

namespace App\Http\Controllers\Employee;

use App\Domain\Employee\Constants\DelegatedPermissions;
use App\Domain\Employee\Services\DelegationService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel;
use App\Infrastructure\Persistence\Eloquent\Support\TicketCommentModel;
use App\Models\BgfApplication;
use App\Models\DelegationApprovalRequest;
use App\Models\Employee;
use App\Models\EmployeeDelegationLog;
use App\Models\InvestorAccount;
use App\Models\PaymentTransaction;
use App\Models\Receipt;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DelegatedFunctionsController extends Controller
{
    public function __construct(
        protected DelegationService $delegationService
    ) {}

    /**
     * Dashboard for delegated functions - shows overview of all delegated capabilities
     */
    public function dashboard(Request $request)
    {
        $employee = $this->getEmployee($request);
        $delegationsGrouped = $this->delegationService->getEmployeeDelegationsGrouped($employee);
        
        // Format delegations for the Vue component
        $delegations = [];
        foreach ($delegationsGrouped as $categoryName => $categoryData) {
            $categoryDelegations = [];
            foreach ($categoryData['permissions'] as $perm) {
                $categoryDelegations[] = [
                    'permission_key' => $perm['key'],
                    'permission_name' => $perm['name'],
                    'requires_approval' => $perm['requires_approval'],
                    'granted_at' => $perm['delegation']->created_at->diffForHumans(),
                ];
            }
            $delegations[] = [
                'category' => $categoryName,
                'delegations' => $categoryDelegations,
            ];
        }
        
        // Get quick stats for delegated functions
        $stats = [];
        
        // Check what permissions employee has and get relevant stats
        if ($this->delegationService->hasPermission($employee, DelegatedPermissions::VIEW_USERS)) {
            $stats['users'] = User::count();
        }
        
        if ($this->delegationService->hasPermission($employee, DelegatedPermissions::HANDLE_SUPPORT_TICKETS)) {
            $stats['open_tickets'] = SupportTicketModel::whereIn('status', ['open', 'in_progress'])->count();
        }
        
        if ($this->delegationService->hasPermission($employee, DelegatedPermissions::VIEW_RECEIPTS)) {
            $stats['receipts_today'] = Receipt::whereDate('created_at', today())->count();
        }
        
        return Inertia::render('Employee/Portal/Delegated/Dashboard', [
            'employee' => $employee,
            'delegations' => $delegations,
            'stats' => $stats,
        ]);
    }

    /**
     * Delegated Users Management - View and search users
     */
    public function users(Request $request)
    {
        $employee = $this->getEmployee($request);
        
        $query = User::query()
            ->select(['id', 'name', 'email', 'phone', 'created_at', 'email_verified_at'])
            ->with(['subscriptions' => fn($q) => $q->where('status', 'active')->latest()->limit(1)]);
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->status === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        // Log this access
        $this->delegationService->logUsage(
            $employee,
            DelegatedPermissions::VIEW_USERS,
            $request->user(),
            ['action' => 'list_users', 'search' => $request->search]
        );
        
        return Inertia::render('Employee/Portal/Delegated/Users/Index', [
            'employee' => $employee,
            'users' => $users,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * View single user details
     */
    public function userShow(Request $request, User $user)
    {
        $employee = $this->getEmployee($request);
        
        // Load limited relations - no sensitive data
        $user->load([
            'subscriptions' => fn($q) => $q->latest()->limit(5),
        ]);
        
        // Log access
        $this->delegationService->logUsage(
            $employee,
            DelegatedPermissions::VIEW_USERS,
            $request->user(),
            ['action' => 'view_user', 'user_id' => $user->id]
        );
        
        return Inertia::render('Employee/Portal/Delegated/Users/Show', [
            'employee' => $employee,
            'user' => $user->only(['id', 'name', 'email', 'phone', 'created_at', 'email_verified_at']),
            'subscriptions' => $user->subscriptions,
        ]);
    }

    /**
     * Delegated Support Center - Handle member/investor tickets
     */
    public function support(Request $request)
    {
        $employee = $this->getEmployee($request);
        
        // Get all support tickets with user/investor info
        $tickets = SupportTicketModel::with(['user:id,name,email', 'investorAccount:id,name,email'])
            ->select(['id', 'user_id', 'investor_account_id', 'source', 'subject', 'status', 'priority', 'category', 'created_at', 'updated_at'])
            ->orderBy('updated_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($ticket) {
                $data = $ticket->toArray();
                // Add ticket_number as id for display
                $data['ticket_number'] = 'TKT-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT);
                // Normalize user data
                if ($ticket->source === 'investor' && $ticket->investorAccount) {
                    $data['user'] = [
                        'id' => $ticket->investorAccount->id,
                        'name' => $ticket->investorAccount->name,
                        'email' => $ticket->investorAccount->email,
                    ];
                }
                return $data;
            });
        
        // Stats
        $stats = [
            'total_open' => SupportTicketModel::where('status', 'open')->count(),
            'in_progress' => SupportTicketModel::where('status', 'in_progress')->count(),
            'resolved_today' => SupportTicketModel::where('status', 'resolved')
                ->whereDate('resolved_at', today())->count(),
        ];
        
        return Inertia::render('Employee/Portal/Delegated/Support/Index', [
            'employee' => $employee,
            'tickets' => $tickets,
            'stats' => $stats,
        ]);
    }

    /**
     * View single support ticket
     */
    public function supportShow(Request $request, string $source, int $ticketId)
    {
        $employee = $this->getEmployee($request);
        
        $ticket = SupportTicketModel::with(['user:id,name,email', 'investorAccount:id,name,email', 'comments.user:id,name'])
            ->findOrFail($ticketId);
        
        // Format ticket data
        $ticketData = $ticket->toArray();
        $ticketData['ticket_number'] = 'TKT-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT);
        $ticketData['messages'] = $ticket->comments->map(fn($c) => [
            'id' => $c->id,
            'message' => $c->content ?? $c->message,
            'is_staff_reply' => $c->author_type === 'support',
            'created_at' => $c->created_at,
            'user' => $c->user,
        ]);
        
        // Normalize user for investor tickets
        if ($ticket->source === 'investor' && $ticket->investorAccount) {
            $ticketData['user'] = [
                'id' => $ticket->investorAccount->id,
                'name' => $ticket->investorAccount->name,
                'email' => $ticket->investorAccount->email,
            ];
        }
        
        // Log access
        $this->delegationService->logUsage(
            $employee,
            DelegatedPermissions::HANDLE_SUPPORT_TICKETS,
            $request->user(),
            ['action' => 'view_ticket', 'source' => $source, 'ticket_id' => $ticketId]
        );
        
        return Inertia::render('Employee/Portal/Delegated/Support/Show', [
            'employee' => $employee,
            'ticket' => $ticketData,
            'source' => $ticket->source,
        ]);
    }

    /**
     * Reply to support ticket
     */
    public function supportReply(Request $request, string $source, int $ticketId)
    {
        $employee = $this->getEmployee($request);
        
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);
        
        $ticket = SupportTicketModel::findOrFail($ticketId);
        
        // Create comment/reply
        TicketCommentModel::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'content' => $validated['message'],
            'author_type' => 'support',
            'read_by_admin' => true,
            'read_by_user' => false,
        ]);
        
        // Update ticket status
        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }
        
        // Log action
        $this->delegationService->logUsage(
            $employee,
            DelegatedPermissions::RESPOND_SUPPORT_TICKETS,
            $request->user(),
            ['action' => 'reply_ticket', 'source' => $source, 'ticket_id' => $ticketId]
        );
        
        return back()->with('success', 'Reply sent successfully');
    }

    /**
     * Delegated Receipts View
     */
    public function receipts(Request $request)
    {
        $employee = $this->getEmployee($request);
        
        $query = Receipt::with(['user:id,name,email'])
            ->select(['id', 'user_id', 'receipt_number', 'amount', 'type', 'status', 'created_at']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }
        
        $receipts = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        return Inertia::render('Employee/Portal/Delegated/Receipts/Index', [
            'employee' => $employee,
            'receipts' => $receipts,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Delegated Member Analytics
     */
    public function memberAnalytics(Request $request)
    {
        $employee = $this->getEmployee($request);
        
        $stats = [
            'total_members' => User::count(),
            'verified_members' => User::whereNotNull('email_verified_at')->count(),
            'new_this_month' => User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'new_this_week' => User::where('created_at', '>=', now()->startOfWeek())->count(),
        ];
        
        $growthTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $growthTrend[] = [
                'date' => $date->format('M d'),
                'count' => User::whereDate('created_at', $date)->count(),
            ];
        }
        
        return Inertia::render('Employee/Portal/Delegated/Analytics/Members', [
            'employee' => $employee,
            'stats' => $stats,
            'growthTrend' => $growthTrend,
        ]);
    }

    // ============================================
    // PAYMENTS
    // ============================================

    public function payments(Request $request)
    {
        $employee = $this->getEmployee($request);
        
        $query = PaymentTransaction::with(['user:id,name,email'])
            ->select(['id', 'user_id', 'type', 'amount', 'status', 'payment_method', 'reference', 'created_at']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $payments = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        $stats = [
            'pending' => PaymentTransaction::where('status', 'pending')->count(),
            'completed_today' => PaymentTransaction::where('status', 'completed')->whereDate('completed_at', today())->count(),
            'total_today' => PaymentTransaction::whereDate('created_at', today())->sum('amount'),
        ];
        
        return Inertia::render('Employee/Portal/Delegated/Payments/Index', [
            'employee' => $employee,
            'payments' => $payments,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function paymentShow(Request $request, PaymentTransaction $payment)
    {
        $employee = $this->getEmployee($request);
        $payment->load(['user:id,name,email,phone']);
        
        $this->delegationService->logUsage($employee, DelegatedPermissions::VIEW_PAYMENTS, $request->user(), [
            'action' => 'view_payment', 'payment_id' => $payment->id
        ]);
        
        return Inertia::render('Employee/Portal/Delegated/Payments/Show', [
            'employee' => $employee,
            'payment' => $payment,
        ]);
    }

    public function paymentProcess(Request $request, PaymentTransaction $payment)
    {
        $employee = $this->getEmployee($request);
        $delegation = $this->delegationService->getDelegation($employee, DelegatedPermissions::PROCESS_PAYMENTS);
        
        if ($delegation?->requires_approval) {
            return $this->createApprovalRequest($request, $employee, $delegation, 'process_payment', 'payment_transaction', $payment->id, [
                'amount' => $payment->amount,
                'user_name' => $payment->user->name,
            ]);
        }
        
        $validated = $request->validate(['action' => 'required|in:approve,reject', 'notes' => 'nullable|string']);
        
        if ($validated['action'] === 'approve') {
            $payment->update(['status' => 'processing', 'admin_notes' => $validated['notes'] ?? null]);
        } else {
            $payment->update(['status' => 'cancelled', 'failure_reason' => $validated['notes'] ?? 'Rejected by staff']);
        }
        
        $this->delegationService->logUsage($employee, DelegatedPermissions::PROCESS_PAYMENTS, $request->user(), [
            'action' => $validated['action'], 'payment_id' => $payment->id
        ]);
        
        return back()->with('success', 'Payment ' . $validated['action'] . 'd successfully');
    }

    // ============================================
    // WITHDRAWALS
    // ============================================

    public function withdrawals(Request $request)
    {
        $employee = $this->getEmployee($request);
        
        $query = WithdrawalRequest::with(['user:id,name,email'])
            ->select(['id', 'user_id', 'amount', 'net_amount', 'status', 'payment_method', 'created_at', 'requested_at']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $withdrawals = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        $stats = [
            'pending' => WithdrawalRequest::where('status', 'pending')->count(),
            'approved_today' => WithdrawalRequest::where('status', 'approved')->whereDate('approved_at', today())->count(),
            'total_pending_amount' => WithdrawalRequest::where('status', 'pending')->sum('amount'),
        ];
        
        return Inertia::render('Employee/Portal/Delegated/Withdrawals/Index', [
            'employee' => $employee,
            'withdrawals' => $withdrawals,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function withdrawalShow(Request $request, WithdrawalRequest $withdrawal)
    {
        $employee = $this->getEmployee($request);
        $withdrawal->load(['user:id,name,email,phone']);
        
        $this->delegationService->logUsage($employee, DelegatedPermissions::VIEW_WITHDRAWALS, $request->user(), [
            'action' => 'view_withdrawal', 'withdrawal_id' => $withdrawal->id
        ]);
        
        return Inertia::render('Employee/Portal/Delegated/Withdrawals/Show', [
            'employee' => $employee,
            'withdrawal' => $withdrawal,
        ]);
    }

    public function withdrawalProcess(Request $request, WithdrawalRequest $withdrawal)
    {
        $employee = $this->getEmployee($request);
        $delegation = $this->delegationService->getDelegation($employee, DelegatedPermissions::PROCESS_WITHDRAWALS);
        
        if ($delegation?->requires_approval) {
            return $this->createApprovalRequest($request, $employee, $delegation, 'process_withdrawal', 'withdrawal_request', $withdrawal->id, [
                'amount' => $withdrawal->amount,
                'user_name' => $withdrawal->user->name,
            ]);
        }
        
        $validated = $request->validate(['action' => 'required|in:approve,reject', 'notes' => 'nullable|string']);
        
        if ($validated['action'] === 'approve') {
            $withdrawal->update(['status' => 'approved', 'approved_at' => now(), 'admin_notes' => $validated['notes'] ?? null]);
        } else {
            $withdrawal->update(['status' => 'rejected', 'rejection_reason' => $validated['notes'] ?? 'Rejected by staff']);
        }
        
        $this->delegationService->logUsage($employee, DelegatedPermissions::PROCESS_WITHDRAWALS, $request->user(), [
            'action' => $validated['action'], 'withdrawal_id' => $withdrawal->id
        ]);
        
        return back()->with('success', 'Withdrawal ' . $validated['action'] . 'd successfully');
    }

    // ============================================
    // BGF APPLICATIONS
    // ============================================

    public function bgfApplications(Request $request)
    {
        $employee = $this->getEmployee($request);
        
        $query = BgfApplication::with(['user:id,name,email'])
            ->select(['id', 'user_id', 'reference_number', 'business_name', 'amount_requested', 'status', 'score', 'created_at']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $applications = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        $stats = [
            'pending_review' => BgfApplication::where('status', 'submitted')->count(),
            'under_review' => BgfApplication::where('status', 'under_review')->count(),
            'approved_this_month' => BgfApplication::where('status', 'approved')->whereMonth('reviewed_at', now()->month)->count(),
        ];
        
        return Inertia::render('Employee/Portal/Delegated/Bgf/Index', [
            'employee' => $employee,
            'applications' => $applications,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function bgfApplicationShow(Request $request, BgfApplication $application)
    {
        $employee = $this->getEmployee($request);
        $application->load(['user:id,name,email,phone', 'evaluations']);
        
        $this->delegationService->logUsage($employee, DelegatedPermissions::VIEW_BGF_APPLICATIONS, $request->user(), [
            'action' => 'view_bgf_application', 'application_id' => $application->id
        ]);
        
        return Inertia::render('Employee/Portal/Delegated/Bgf/Show', [
            'employee' => $employee,
            'application' => $application,
        ]);
    }

    public function bgfApplicationReview(Request $request, BgfApplication $application)
    {
        $employee = $this->getEmployee($request);
        
        $validated = $request->validate([
            'score' => 'required|integer|min:0|max:100',
            'notes' => 'required|string|max:2000',
            'recommendation' => 'required|in:approve,reject,needs_info',
        ]);
        
        $application->update([
            'status' => 'under_review',
            'score' => $validated['score'],
            'evaluator_notes' => $validated['notes'],
            'evaluated_by' => $request->user()->id,
            'evaluated_at' => now(),
        ]);
        
        $this->delegationService->logUsage($employee, DelegatedPermissions::REVIEW_BGF_APPLICATIONS, $request->user(), [
            'action' => 'review_bgf', 'application_id' => $application->id, 'recommendation' => $validated['recommendation']
        ]);
        
        return back()->with('success', 'Application reviewed successfully');
    }

    // ============================================
    // INVESTOR RELATIONS
    // ============================================

    public function investorMessages(Request $request)
    {
        $employee = $this->getEmployee($request);
        
        $investors = InvestorAccount::select(['id', 'name', 'email', 'investment_amount', 'status'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        
        // Get support tickets from investors
        $tickets = SupportTicketModel::with(['investorAccount:id,name,email'])
            ->where('source', 'investor')
            ->select(['id', 'investor_account_id', 'subject', 'status', 'priority', 'created_at', 'updated_at'])
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();
        
        return Inertia::render('Employee/Portal/Delegated/Investors/Messages', [
            'employee' => $employee,
            'investors' => $investors,
            'tickets' => $tickets,
        ]);
    }

    public function investorDocuments(Request $request)
    {
        $employee = $this->getEmployee($request);
        
        $investors = InvestorAccount::with(['shareCertificates'])
            ->select(['id', 'name', 'email', 'investment_amount', 'equity_percentage', 'status'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        
        return Inertia::render('Employee/Portal/Delegated/Investors/Documents', [
            'employee' => $employee,
            'investors' => $investors,
        ]);
    }

    // ============================================
    // FINANCIAL REPORTS
    // ============================================

    public function financialReports(Request $request)
    {
        $employee = $this->getEmployee($request);
        
        $period = $request->get('period', 'month');
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };
        
        $stats = [
            'total_payments' => PaymentTransaction::where('created_at', '>=', $startDate)->where('status', 'completed')->sum('amount'),
            'total_withdrawals' => WithdrawalRequest::where('created_at', '>=', $startDate)->where('status', 'processed')->sum('amount'),
            'payment_count' => PaymentTransaction::where('created_at', '>=', $startDate)->where('status', 'completed')->count(),
            'withdrawal_count' => WithdrawalRequest::where('created_at', '>=', $startDate)->where('status', 'processed')->count(),
        ];
        
        // Daily breakdown
        $dailyData = [];
        $days = $period === 'week' ? 7 : ($period === 'month' ? 30 : 90);
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dailyData[] = [
                'date' => $date->format('M d'),
                'payments' => PaymentTransaction::whereDate('created_at', $date)->where('status', 'completed')->sum('amount'),
                'withdrawals' => WithdrawalRequest::whereDate('created_at', $date)->where('status', 'processed')->sum('amount'),
            ];
        }
        
        $this->delegationService->logUsage($employee, DelegatedPermissions::VIEW_FINANCIAL_REPORTS, $request->user(), [
            'action' => 'view_financial_reports', 'period' => $period
        ]);
        
        return Inertia::render('Employee/Portal/Delegated/Analytics/Financial', [
            'employee' => $employee,
            'stats' => $stats,
            'dailyData' => $dailyData,
            'period' => $period,
        ]);
    }

    // ============================================
    // APPROVAL QUEUE
    // ============================================

    public function approvalQueue(Request $request)
    {
        $employee = $this->getEmployee($request);
        
        // Get pending approvals for this employee's delegated actions
        $pendingApprovals = DelegationApprovalRequest::with(['employee:id,first_name,last_name', 'reviewer:id,name'])
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Get approvals this employee needs to review (if they're a manager)
        $toReview = [];
        if ($employee->is_manager) {
            $subordinateIds = Employee::where('manager_id', $employee->id)->pluck('id');
            $toReview = DelegationApprovalRequest::with(['employee:id,first_name,last_name'])
                ->whereIn('employee_id', $subordinateIds)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return Inertia::render('Employee/Portal/Delegated/Approvals/Index', [
            'employee' => $employee,
            'pendingApprovals' => $pendingApprovals,
            'toReview' => $toReview,
        ]);
    }

    public function approvalReview(Request $request, DelegationApprovalRequest $approval)
    {
        $employee = $this->getEmployee($request);
        
        // Verify this employee can review this approval
        $requestingEmployee = $approval->employee;
        if ($requestingEmployee->manager_id !== $employee->id) {
            abort(403, 'You are not authorized to review this approval');
        }
        
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $approval->update([
            'status' => $validated['action'] === 'approve' ? 'approved' : 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['notes'],
        ]);
        
        // Log the action
        EmployeeDelegationLog::create([
            'delegation_id' => $approval->delegation_id,
            'employee_id' => $approval->employee_id,
            'permission_key' => $approval->delegation->permission_key ?? 'unknown',
            'action' => $validated['action'] === 'approve' ? 'approved' : 'rejected',
            'performed_by' => $request->user()->id,
            'metadata' => ['approval_id' => $approval->id, 'notes' => $validated['notes']],
            'ip_address' => $request->ip(),
        ]);
        
        // If approved, execute the original action
        if ($validated['action'] === 'approve') {
            $this->executeApprovedAction($approval);
        }
        
        return back()->with('success', 'Approval ' . $validated['action'] . 'd successfully');
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    protected function getEmployee(Request $request): Employee
    {
        $employee = $request->attributes->get('employee');
        
        if (!$employee) {
            $employee = Employee::where('user_id', $request->user()->id)
                ->where('employment_status', 'active')
                ->firstOrFail();
        }
        
        return $employee;
    }

    protected function createApprovalRequest(Request $request, Employee $employee, $delegation, string $actionType, string $resourceType, int $resourceId, array $actionData)
    {
        $approval = DelegationApprovalRequest::create([
            'delegation_id' => $delegation->id,
            'employee_id' => $employee->id,
            'action_type' => $actionType,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'action_data' => array_merge($actionData, $request->only(['action', 'notes'])),
            'status' => 'pending',
        ]);
        
        EmployeeDelegationLog::create([
            'delegation_id' => $delegation->id,
            'employee_id' => $employee->id,
            'permission_key' => $delegation->permission_key,
            'action' => 'approval_requested',
            'performed_by' => $request->user()->id,
            'metadata' => ['approval_id' => $approval->id, 'action_type' => $actionType],
            'ip_address' => $request->ip(),
        ]);
        
        // TODO: Send notification to manager
        
        return back()->with('info', 'Your request has been submitted for manager approval');
    }

    protected function executeApprovedAction(DelegationApprovalRequest $approval): void
    {
        $data = $approval->action_data;
        
        switch ($approval->action_type) {
            case 'process_payment':
                $payment = PaymentTransaction::find($approval->resource_id);
                if ($payment && $data['action'] === 'approve') {
                    $payment->update(['status' => 'processing']);
                }
                break;
                
            case 'process_withdrawal':
                $withdrawal = WithdrawalRequest::find($approval->resource_id);
                if ($withdrawal && $data['action'] === 'approve') {
                    $withdrawal->update(['status' => 'approved', 'approved_at' => now()]);
                }
                break;
        }
    }
}
