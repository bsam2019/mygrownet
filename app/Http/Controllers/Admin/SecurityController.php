<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SuspiciousActivity;
use App\Models\User;
use App\Services\AuditService;
use App\Services\FraudDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SecurityController extends Controller
{
    public function __construct(
        private FraudDetectionService $fraudDetectionService,
        private AuditService $auditService
    ) {
        $this->middleware('can:manage-security');
    }

    /**
     * Security dashboard
     */
    public function dashboard()
    {
        $suspiciousActivities = SuspiciousActivity::unresolved()
            ->highPriority()
            ->with('user')
            ->latest()
            ->take(10)
            ->get();

        $auditSummary = $this->auditService->getAuditSummary(
            now()->subDays(30)->toDateString(),
            now()->toDateString()
        );

        $blockedUsers = User::where('is_blocked', true)
            ->count();

        $highRiskUsers = User::where('risk_score', '>=', 70)
            ->where('is_blocked', false)
            ->count();

        return Inertia::render('Admin/Security/Dashboard', [
            'suspiciousActivities' => $suspiciousActivities,
            'auditSummary' => $auditSummary,
            'blockedUsers' => $blockedUsers,
            'highRiskUsers' => $highRiskUsers,
        ]);
    }

    /**
     * List suspicious activities
     */
    public function suspiciousActivities(Request $request)
    {
        $query = SuspiciousActivity::with('user')
            ->latest();

        if ($request->type) {
            $query->ofType($request->type);
        }

        if ($request->severity) {
            $query->where('severity', $request->severity);
        }

        if ($request->resolved !== null) {
            $query->where('is_resolved', $request->boolean('resolved'));
        }

        $activities = $query->paginate(20);

        return Inertia::render('Admin/Security/SuspiciousActivities', [
            'activities' => $activities,
            'filters' => $request->only(['type', 'severity', 'resolved']),
        ]);
    }

    /**
     * Show suspicious activity details
     */
    public function showSuspiciousActivity(SuspiciousActivity $activity)
    {
        $activity->load(['user', 'resolvedBy']);

        return Inertia::render('Admin/Security/SuspiciousActivityDetails', [
            'activity' => $activity,
        ]);
    }

    /**
     * Resolve suspicious activity
     */
    public function resolveSuspiciousActivity(SuspiciousActivity $activity, Request $request)
    {
        $request->validate([
            'action' => 'required|in:blocked,warned,cleared,monitoring',
            'notes' => 'nullable|string|max:1000',
        ]);

        $activity->resolve($request->action, $request->notes, Auth::id());

        // If action is to block, block the user
        if ($request->action === 'blocked' && $activity->user) {
            $this->fraudDetectionService->blockUser(
                $activity->user,
                'Blocked due to suspicious activity: ' . $activity->activity_type,
                Auth::id()
            );
        }

        return back()->with('success', 'Suspicious activity resolved successfully.');
    }

    /**
     * List blocked users
     */
    public function blockedUsers()
    {
        $blockedUsers = User::where('is_blocked', true)
            ->with(['blockedBy'])
            ->latest('blocked_at')
            ->paginate(20);

        return Inertia::render('Admin/Security/BlockedUsers', [
            'users' => $blockedUsers,
        ]);
    }

    /**
     * Block user
     */
    public function blockUser(User $user, Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->fraudDetectionService->blockUser($user, $request->reason, Auth::id());

        return back()->with('success', 'User blocked successfully.');
    }

    /**
     * Unblock user
     */
    public function unblockUser(User $user, Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $user->update([
            'is_blocked' => false,
            'block_reason' => null,
            'blocked_at' => null,
            'blocked_by' => null,
        ]);

        // Log the unblocking action
        AuditLog::logEvent(
            AuditLog::EVENT_USER_UNBLOCKED,
            $user,
            Auth::id(),
            ['is_blocked' => true],
            ['is_blocked' => false],
            null,
            null,
            ['unblock_reason' => $request->reason]
        );

        return back()->with('success', 'User unblocked successfully.');
    }

    /**
     * Audit logs
     */
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with(['user', 'auditable'])
            ->latest();

        if ($request->event_type) {
            $query->ofType($request->event_type);
        }

        if ($request->user_id) {
            $query->forUser($request->user_id);
        }

        if ($request->start_date && $request->end_date) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        if ($request->financial_only) {
            $query->financial();
        }

        $logs = $query->paginate(50);

        return Inertia::render('Admin/Security/AuditLogs', [
            'logs' => $logs,
            'filters' => $request->only(['event_type', 'user_id', 'start_date', 'end_date', 'financial_only']),
        ]);
    }

    /**
     * High risk users
     */
    public function highRiskUsers()
    {
        $highRiskUsers = User::where('risk_score', '>=', 50)
            ->where('is_blocked', false)
            ->with(['suspiciousActivities' => function ($query) {
                $query->unresolved()->latest()->take(3);
            }])
            ->orderBy('risk_score', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Security/HighRiskUsers', [
            'users' => $highRiskUsers,
        ]);
    }

    /**
     * Recalculate risk scores
     */
    public function recalculateRiskScores()
    {
        $users = User::where('is_blocked', false)->get();
        $updated = 0;

        foreach ($users as $user) {
            $this->fraudDetectionService->calculateRiskScore($user);
            $updated++;
        }

        return back()->with('success', "Risk scores recalculated for {$updated} users.");
    }
}