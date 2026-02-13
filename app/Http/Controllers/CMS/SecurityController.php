<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Services\CMS\SecurityService;
use App\Infrastructure\Persistence\Eloquent\CMS\SuspiciousActivityModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SecurityController extends Controller
{
    public function __construct(
        private SecurityService $securityService
    ) {}

    // Security Settings Page
    public function settings(Request $request)
    {
        $company = $request->user()->company;
        
        $settings = [
            'password_expiry_days' => $company->password_expiry_days ?? 90,
            'max_login_attempts' => $company->max_login_attempts ?? 5,
            'lockout_duration_minutes' => $company->lockout_duration_minutes ?? 30,
            'session_timeout_minutes' => $company->session_timeout_minutes ?? 120,
            'require_2fa' => $company->require_2fa ?? false,
            'password_min_length' => $company->password_min_length ?? 8,
            'enable_security_alerts' => $company->enable_security_alerts ?? true,
        ];

        return Inertia::render('CMS/Security/Settings', [
            'settings' => $settings,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'password_expiry_days' => 'required|integer|min:30|max:365',
            'max_login_attempts' => 'required|integer|min:3|max:10',
            'lockout_duration_minutes' => 'required|integer|min:15|max:120',
            'session_timeout_minutes' => 'required|integer|min:30|max:480',
            'require_2fa' => 'boolean',
            'password_min_length' => 'required|integer|min:8|max:32',
            'enable_security_alerts' => 'boolean',
        ]);

        $request->user()->company->update($validated);

        return redirect()->back()->with('success', 'Security settings updated successfully.');
    }

    // Audit Log Viewer
    public function auditLogs(Request $request)
    {
        $logs = \App\Infrastructure\Persistence\Eloquent\CMS\SecurityAuditLogModel::where('company_id', $request->user()->company_id)
            ->when($request->event_type, fn($q) => $q->where('event_type', $request->event_type))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $users = \App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel::where('company_id', $request->user()->company_id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('CMS/Security/AuditLogs', [
            'logs' => $logs,
            'users' => $users,
            'filters' => $request->only(['event_type', 'user_id']),
        ]);
    }

    // Suspicious Activity Dashboard
    public function suspiciousActivity(Request $request)
    {
        $activities = SuspiciousActivityModel::where('company_id', $request->user()->company_id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->severity, fn($q) => $q->where('severity', $request->severity))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => SuspiciousActivityModel::where('company_id', $request->user()->company_id)->count(),
            'pending' => SuspiciousActivityModel::where('company_id', $request->user()->company_id)->where('status', 'pending')->count(),
            'high_severity' => SuspiciousActivityModel::where('company_id', $request->user()->company_id)->where('severity', 'high')->count(),
        ];

        return Inertia::render('CMS/Security/SuspiciousActivity', [
            'activities' => $activities,
            'stats' => $stats,
            'filters' => $request->only(['status', 'severity']),
        ]);
    }

    public function markActivityReviewed(Request $request, SuspiciousActivityModel $activity)
    {
        $validated = $request->validate([
            'status' => 'required|in:reviewed,false_positive,confirmed',
            'notes' => 'nullable|string',
        ]);

        $activity->update([
            'status' => $validated['status'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Activity marked as reviewed.');
    }

    // 2FA Setup
    public function enable2FA(Request $request)
    {
        $secret = $this->securityService->generate2FASecret();
        $qrCode = $this->securityService->generate2FAQRCode($request->user()->email, $secret);

        return Inertia::render('CMS/Security/Enable2FA', [
            'secret' => $secret,
            'qrCode' => $qrCode,
        ]);
    }

    public function verify2FA(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:6',
            'secret' => 'required|string',
        ]);

        $isValid = $this->securityService->verify2FACode($validated['secret'], $validated['code']);

        if (!$isValid) {
            return redirect()->back()->with('error', 'Invalid verification code.');
        }

        $request->user()->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => $validated['secret'],
        ]);

        return redirect()->route('cms.dashboard')->with('success', '2FA enabled successfully.');
    }

    public function disable2FA(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string',
        ]);

        if (!\Hash::check($validated['password'], $request->user()->password)) {
            return redirect()->back()->with('error', 'Invalid password.');
        }

        $request->user()->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
        ]);

        return redirect()->back()->with('success', '2FA disabled successfully.');
    }
}
