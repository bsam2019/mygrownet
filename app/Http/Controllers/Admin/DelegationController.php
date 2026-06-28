<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Employee\Constants\DelegatedPermissions;
use App\Domain\Employee\Services\DelegationService;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDelegation;
use App\Models\EmployeeDelegationLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DelegationController extends Controller
{
    public function __construct(
        protected DelegationService $delegationService
    ) {}

    /**
     * Display delegation management dashboard
     */
    public function index(Request $request)
    {
        $employees = Employee::with(['user', 'department', 'position'])
            ->where('employment_status', 'active')
            ->withCount(['delegations' => fn($q) => $q->active()])
            ->orderBy('first_name')
            ->paginate(20);

        $stats = [
            'total_employees' => Employee::where('employment_status', 'active')->count(),
            'employees_with_delegations' => EmployeeDelegation::active()->distinct('employee_id')->count('employee_id'),
            'total_active_delegations' => EmployeeDelegation::active()->count(),
            'pending_approvals' => \App\Models\DelegationApprovalRequest::pending()->count(),
        ];

        return Inertia::render('Admin/Delegations/Index', [
            'employees' => $employees,
            'stats' => $stats,
            'availablePermissions' => $this->delegationService->getAvailablePermissions(),
            'recommendedSets' => $this->delegationService->getRecommendedSets(),
        ]);
    }

    /**
     * Show delegation details for a specific employee
     */
    public function show(Employee $employee)
    {
        $employee->load(['user', 'department', 'position', 'manager']);
        
        $delegations = $this->delegationService->getEmployeeDelegationsGrouped($employee);
        
        $recentLogs = EmployeeDelegationLog::where('employee_id', $employee->id)
            ->with('performer')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return Inertia::render('Admin/Delegations/Show', [
            'employee' => $employee,
            'delegations' => $delegations,
            'recentLogs' => $recentLogs,
            'availablePermissions' => $this->delegationService->getAvailablePermissions(),
            'recommendedSets' => $this->delegationService->getRecommendedSets(),
        ]);
    }

    /**
     * Grant permissions to an employee
     */
    public function grant(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string',
            'requires_approval' => 'boolean',
            'approval_manager_id' => 'nullable|exists:employees,id',
            'expires_at' => 'nullable|date|after:now',
            'notes' => 'nullable|string|max:500',
        ]);

        $approvalManager = null;
        if ($validated['approval_manager_id'] ?? null) {
            $approvalManager = Employee::find($validated['approval_manager_id']);
        }

        $expiresAt = $validated['expires_at'] ?? null;
        if ($expiresAt) {
            $expiresAt = new \DateTime($expiresAt);
        }

        foreach ($validated['permissions'] as $permissionKey) {
            $this->delegationService->grantPermission(
                $employee,
                $permissionKey,
                $request->user(),
                $validated['requires_approval'] ?? false,
                $approvalManager,
                $expiresAt,
                $validated['notes'] ?? null
            );
        }

        return back()->with('success', 'Permissions granted successfully');
    }

    /**
     * Grant a preset permission set
     */
    public function grantPreset(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'preset' => 'required|string',
            'notes' => 'nullable|string|max:500',
        ]);

        $presets = DelegatedPermissions::getRecommendedSets();
        
        if (!isset($presets[$validated['preset']])) {
            return back()->with('error', 'Invalid preset selected');
        }

        $permissions = $presets[$validated['preset']]['permissions'];

        $this->delegationService->grantPermissionSet(
            $employee,
            $permissions,
            $request->user(),
            $validated['notes'] ?? "Granted preset: {$validated['preset']}"
        );

        return back()->with('success', "'{$validated['preset']}' permissions granted successfully");
    }

    /**
     * Revoke a permission from an employee
     */
    public function revoke(Request $request, Employee $employee, string $permissionKey)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $this->delegationService->revokePermission(
            $employee,
            $permissionKey,
            $request->user(),
            $validated['reason'] ?? null
        );

        return back()->with('success', 'Permission revoked successfully');
    }

    /**
     * Revoke all permissions from an employee
     */
    public function revokeAll(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $delegations = EmployeeDelegation::where('employee_id', $employee->id)
            ->active()
            ->get();

        foreach ($delegations as $delegation) {
            $this->delegationService->revokePermission(
                $employee,
                $delegation->permission_key,
                $request->user(),
                $validated['reason'] ?? null
            );
        }

        return back()->with('success', 'All permissions revoked successfully');
    }

    /**
     * View audit logs
     */
    public function logs(Request $request)
    {
        $query = EmployeeDelegationLog::with(['employee', 'performer'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('permission')) {
            $query->where('permission_key', $request->permission);
        }

        $logs = $query->paginate(50);

        return Inertia::render('Admin/Delegations/Logs', [
            'logs' => $logs,
            'filters' => $request->only(['employee_id', 'action', 'permission']),
        ]);
    }

    /**
     * View pending approval requests
     */
    public function approvals(Request $request)
    {
        $query = \App\Models\DelegationApprovalRequest::with(['employee', 'delegation', 'reviewer'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->pending();
        }

        $requests = $query->paginate(20);

        return Inertia::render('Admin/Delegations/Approvals', [
            'requests' => $requests,
            'filters' => $request->only(['status']),
        ]);
    }

    /**
     * Approve a pending request
     */
    public function approve(Request $request, \App\Models\DelegationApprovalRequest $approvalRequest)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $this->delegationService->approveRequest(
            $approvalRequest,
            $request->user(),
            $validated['notes'] ?? null
        );

        return back()->with('success', 'Request approved successfully');
    }

    /**
     * Reject a pending request
     */
    public function reject(Request $request, \App\Models\DelegationApprovalRequest $approvalRequest)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->delegationService->rejectRequest(
            $approvalRequest,
            $request->user(),
            $validated['reason']
        );

        return back()->with('success', 'Request rejected');
    }

    /**
     * Export delegation logs as CSV
     */
    public function exportLogs(Request $request)
    {
        $query = EmployeeDelegationLog::with(['employee', 'performer'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->limit(5000)->get();

        $filename = 'delegation_logs_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ['Date', 'Employee', 'Permission', 'Action', 'Performed By', 'IP Address', 'Details']);
            
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->employee?->full_name ?? 'N/A',
                    $log->permission_key,
                    $log->action,
                    $log->performer?->name ?? 'N/A',
                    $log->ip_address,
                    json_encode($log->metadata),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Activity report dashboard
     */
    public function activityReport(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            default => now()->startOfMonth()
        };

        // Action breakdown
        $actionStats = EmployeeDelegationLog::where('created_at', '>=', $startDate)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();

        // Most active employees
        $topEmployees = EmployeeDelegationLog::where('created_at', '>=', $startDate)
            ->where('action', 'used')
            ->selectRaw('employee_id, COUNT(*) as usage_count')
            ->groupBy('employee_id')
            ->orderByDesc('usage_count')
            ->limit(10)
            ->with('employee:id,first_name,last_name')
            ->get();

        // Most used permissions
        $topPermissions = EmployeeDelegationLog::where('created_at', '>=', $startDate)
            ->where('action', 'used')
            ->selectRaw('permission_key, COUNT(*) as usage_count')
            ->groupBy('permission_key')
            ->orderByDesc('usage_count')
            ->limit(10)
            ->get();

        // Daily activity trend
        $dailyTrend = [];
        $days = $period === 'week' ? 7 : ($period === 'month' ? 30 : 90);
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dailyTrend[] = [
                'date' => $date->format('M d'),
                'count' => EmployeeDelegationLog::whereDate('created_at', $date)->count(),
            ];
        }

        return Inertia::render('Admin/Delegations/ActivityReport', [
            'actionStats' => $actionStats,
            'topEmployees' => $topEmployees,
            'topPermissions' => $topPermissions,
            'dailyTrend' => $dailyTrend,
            'period' => $period,
        ]);
    }
}
