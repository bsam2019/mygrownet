<?php

namespace App\Services\CMS;

use App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\LoginAttemptModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PasswordHistoryModel;
use App\Infrastructure\Persistence\Eloquent\CMS\SecurityAuditLogModel;
use App\Infrastructure\Persistence\Eloquent\CMS\SuspiciousActivityModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SecurityService
{
    /**
     * Default security settings
     */
    private const DEFAULT_SETTINGS = [
        'password_min_length' => 8,
        'password_require_uppercase' => true,
        'password_require_lowercase' => true,
        'password_require_number' => true,
        'password_require_special' => true,
        'password_expiry_days' => 90,
        'password_history_count' => 5,
        'max_login_attempts' => 5,
        'lockout_duration_minutes' => 30,
        'session_timeout_minutes' => 30,
        'two_factor_required' => false,
    ];

    /**
     * Get security settings for company
     */
    public function getSecuritySettings(int $companyId): array
    {
        $company = CompanyModel::findOrFail($companyId);
        $settings = $company->security_settings ?? [];
        
        return array_merge(self::DEFAULT_SETTINGS, $settings);
    }

    /**
     * Update security settings for company
     */
    public function updateSecuritySettings(int $companyId, array $settings): void
    {
        $company = CompanyModel::findOrFail($companyId);
        $company->security_settings = array_merge(
            $company->security_settings ?? [],
            $settings
        );
        $company->save();
    }

    /**
     * Validate password strength
     */
    public function validatePasswordStrength(string $password, int $companyId): array
    {
        $settings = $this->getSecuritySettings($companyId);
        $errors = [];

        if (strlen($password) < $settings['password_min_length']) {
            $errors[] = "Password must be at least {$settings['password_min_length']} characters long";
        }

        if ($settings['password_require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }

        if ($settings['password_require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }

        if ($settings['password_require_number'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }

        if ($settings['password_require_special'] && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Check if password was used recently
     */
    public function isPasswordReused(int $userId, string $newPassword, int $companyId): bool
    {
        $settings = $this->getSecuritySettings($companyId);
        $historyCount = $settings['password_history_count'];

        $recentPasswords = PasswordHistoryModel::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take($historyCount)
            ->get();

        foreach ($recentPasswords as $history) {
            if (Hash::check($newPassword, $history->password)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Save password to history
     */
    public function savePasswordHistory(int $userId, string $hashedPassword): void
    {
        PasswordHistoryModel::create([
            'user_id' => $userId,
            'password' => $hashedPassword,
            'created_at' => now(),
        ]);
    }

    /**
     * Check if password has expired
     */
    public function isPasswordExpired($user, int $companyId): bool
    {
        $settings = $this->getSecuritySettings($companyId);
        
        if (!$user->password_changed_at) {
            return false; // First time login
        }

        $expiryDate = $user->password_changed_at->addDays($settings['password_expiry_days']);
        return now()->greaterThan($expiryDate);
    }

    /**
     * Record login attempt
     */
    public function recordLoginAttempt(
        string $email,
        ?int $userId,
        bool $successful,
        string $ipAddress,
        ?string $userAgent,
        ?string $failureReason = null
    ): void {
        LoginAttemptModel::create([
            'user_id' => $userId,
            'email' => $email,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'successful' => $successful,
            'failure_reason' => $failureReason,
            'attempted_at' => now(),
        ]);
    }

    /**
     * Check if account is locked
     */
    public function isAccountLocked($user): bool
    {
        if (!$user->locked_until) {
            return false;
        }

        if (now()->greaterThan($user->locked_until)) {
            // Lock expired, reset
            $user->update([
                'locked_until' => null,
                'failed_login_attempts' => 0,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Handle failed login attempt
     */
    public function handleFailedLogin($user, int $companyId): void
    {
        $settings = $this->getSecuritySettings($companyId);
        
        $user->increment('failed_login_attempts');

        if ($user->failed_login_attempts >= $settings['max_login_attempts']) {
            $lockoutMinutes = $settings['lockout_duration_minutes'];
            $user->update([
                'locked_until' => now()->addMinutes($lockoutMinutes),
            ]);

            $this->logSecurityEvent(
                $user->id,
                $companyId,
                'account_locked',
                request()->ip(),
                "Account locked after {$settings['max_login_attempts']} failed login attempts",
                ['lockout_minutes' => $lockoutMinutes],
                'warning'
            );

            $this->detectSuspiciousActivity(
                $user->id,
                $companyId,
                'multiple_failed_logins',
                request()->ip(),
                "Account locked after {$user->failed_login_attempts} failed attempts"
            );
        }
    }

    /**
     * Handle successful login
     */
    public function handleSuccessfulLogin($user): void
    {
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_ip' => request()->ip(),
        ]);

        // Get CMS user for company_id
        $cmsUser = CmsUserModel::where('user_id', $user->id)->first();
        
        if ($cmsUser) {
            $this->logSecurityEvent(
                $user->id,
                $cmsUser->company_id,
                'login_successful',
                request()->ip(),
                'User logged in successfully',
                null,
                'info'
            );
        }
    }

    /**
     * Generate 2FA secret for TOTP
     */
    public function generate2FASecret(): string
    {
        return Str::random(32);
    }

    /**
     * Generate 2FA QR code for authenticator apps
     */
    public function generate2FAQRCode(string $email, string $secret): string
    {
        // Generate TOTP URI
        $issuer = config('app.name', 'CMS');
        $uri = "otpauth://totp/{$issuer}:{$email}?secret={$secret}&issuer={$issuer}";
        
        // Generate QR code as data URL
        // Using a simple QR code library or service
        // For now, return the URI (frontend can generate QR code)
        return $uri;
    }

    /**
     * Generate 2FA code
     */
    public function generate2FACode($user, string $method = 'email'): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $user->update([
            'two_factor_code' => Hash::make($code),
            'two_factor_method' => $method,
            'two_factor_expires_at' => now()->addMinutes(10),
        ]);

        return $code;
    }

    /**
     * Verify 2FA code
     */
    public function verify2FACode($user, string $code): bool
    {
        if (!$user->two_factor_code || !$user->two_factor_expires_at) {
            return false;
        }

        if (now()->greaterThan($user->two_factor_expires_at)) {
            return false;
        }

        if (!Hash::check($code, $user->two_factor_code)) {
            return false;
        }

        // Clear 2FA code after successful verification
        $user->update([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);

        return true;
    }

    /**
     * Send 2FA code
     */
    public function send2FACode($user, string $code): void
    {
        if ($user->two_factor_method === 'email') {
            // Send via email
            Mail::raw(
                "Your verification code is: {$code}\n\nThis code will expire in 10 minutes.",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Your Verification Code');
                }
            );
        }
        // SMS implementation would go here
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(
        ?int $userId,
        int $companyId,
        string $eventType,
        string $ipAddress,
        string $description,
        ?array $metadata = null,
        string $severity = 'info'
    ): void {
        SecurityAuditLogModel::create([
            'user_id' => $userId,
            'company_id' => $companyId,
            'event_type' => $eventType,
            'ip_address' => $ipAddress,
            'user_agent' => request()->userAgent(),
            'description' => $description,
            'metadata' => $metadata,
            'severity' => $severity,
            'created_at' => now(),
        ]);
    }

    /**
     * Detect suspicious activity
     */
    public function detectSuspiciousActivity(
        ?int $userId,
        int $companyId,
        string $activityType,
        string $ipAddress,
        string $description,
        ?array $details = null
    ): void {
        $activity = SuspiciousActivityModel::create([
            'user_id' => $userId,
            'company_id' => $companyId,
            'activity_type' => $activityType,
            'ip_address' => $ipAddress,
            'description' => $description,
            'details' => $details,
            'status' => 'pending',
            'severity' => $this->determineSeverity($activityType),
            'detected_at' => now(),
        ]);

        // Send email alert if enabled
        $company = CompanyModel::find($companyId);
        if ($company && $company->enable_security_alerts) {
            $this->sendSecurityAlert($activity, $company);
        }
    }

    /**
     * Determine severity based on activity type
     */
    private function determineSeverity(string $activityType): string
    {
        $highSeverity = ['multiple_failed_logins', 'account_takeover_attempt', 'unauthorized_access'];
        $mediumSeverity = ['unusual_location', 'unusual_time', 'rapid_requests'];
        
        if (in_array($activityType, $highSeverity)) {
            return 'high';
        } elseif (in_array($activityType, $mediumSeverity)) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Send security alert email
     */
    private function sendSecurityAlert($activity, $company): void
    {
        try {
            // Get admin users
            $adminUsers = \App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel::where('company_id', $company->id)
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'admin');
                })
                ->get();

            $activityData = [
                'activity_type' => $activity->activity_type,
                'description' => $activity->description,
                'ip_address' => $activity->ip_address,
                'detected_at' => $activity->detected_at->format('Y-m-d H:i:s'),
                'user_name' => $activity->user ? $activity->user->name : 'Unknown',
            ];

            foreach ($adminUsers as $admin) {
                if ($admin->user && $admin->user->email) {
                    Mail::to($admin->user->email)
                        ->send(new \App\Mail\CMS\SuspiciousActivityAlert($activityData, $company->name));
                }
            }
        } catch (\Exception $e) {
            // Log error but don't fail the detection
            \Log::error('Failed to send security alert email: ' . $e->getMessage());
        }
    }

    /**
     * Get security audit logs
     */
    public function getAuditLogs(int $companyId, array $filters = [])
    {
        $query = SecurityAuditLogModel::where('company_id', $companyId);

        if (isset($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        if (isset($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->with('user')->latest('created_at')->paginate(50);
    }

    /**
     * Get suspicious activities
     */
    public function getSuspiciousActivities(int $companyId, string $status = 'pending')
    {
        return SuspiciousActivityModel::where('company_id', $companyId)
            ->where('status', $status)
            ->with(['user', 'reviewer'])
            ->latest('detected_at')
            ->paginate(20);
    }
}
