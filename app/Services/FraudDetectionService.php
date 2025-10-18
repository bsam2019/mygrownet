<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\DeviceFingerprint;
use App\Models\SuspiciousActivity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FraudDetectionService
{
    /**
     * Detect duplicate accounts based on IP and device fingerprinting
     */
    public function detectDuplicateAccounts(User $user, array $deviceInfo, array $browserInfo, string $userAgent): void
    {
        $fingerprint = DeviceFingerprint::generateFingerprint($deviceInfo, $browserInfo, $userAgent);
        $ipAddress = request()->ip();

        // Check for existing fingerprints
        $existingFingerprints = DeviceFingerprint::where('fingerprint_hash', $fingerprint)
            ->where('user_id', '!=', $user->id)
            ->with('user')
            ->get();

        if ($existingFingerprints->isNotEmpty()) {
            $this->flagSuspiciousActivity(
                $user,
                SuspiciousActivity::TYPE_DUPLICATE_ACCOUNT,
                SuspiciousActivity::SEVERITY_HIGH,
                [
                    'fingerprint_hash' => $fingerprint,
                    'matching_users' => $existingFingerprints->pluck('user.id')->toArray(),
                    'device_info' => $deviceInfo,
                    'browser_info' => $browserInfo,
                ],
                ['duplicate_device_fingerprint']
            );
        }

        // Check for multiple accounts from same IP within short timeframe
        $recentAccountsFromIP = User::where('created_at', '>=', now()->subDays(7))
            ->whereHas('deviceFingerprints', function ($query) use ($ipAddress) {
                $query->where('ip_address', $ipAddress);
            })
            ->where('id', '!=', $user->id)
            ->count();

        if ($recentAccountsFromIP >= 3) {
            $this->flagSuspiciousActivity(
                $user,
                SuspiciousActivity::TYPE_DUPLICATE_ACCOUNT,
                SuspiciousActivity::SEVERITY_MEDIUM,
                [
                    'ip_address' => $ipAddress,
                    'recent_accounts_count' => $recentAccountsFromIP,
                    'timeframe_days' => 7,
                ],
                ['multiple_accounts_same_ip']
            );
        }

        // Store or update device fingerprint
        $this->storeDeviceFingerprint($user, $fingerprint, $deviceInfo, $browserInfo, $userAgent, $ipAddress);
    }

    /**
     * Detect rapid investment patterns
     */
    public function detectRapidInvestments(User $user, float $amount): void
    {
        // Check for multiple investments within short timeframe
        $recentInvestments = $user->investments()
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        if ($recentInvestments >= 5) {
            $this->flagSuspiciousActivity(
                $user,
                SuspiciousActivity::TYPE_RAPID_INVESTMENTS,
                SuspiciousActivity::SEVERITY_HIGH,
                [
                    'investment_count_24h' => $recentInvestments,
                    'current_amount' => $amount,
                ],
                ['rapid_investment_frequency']
            );
        }

        // Check for unusually large investment amounts
        $averageInvestment = $user->investments()->avg('amount') ?? 0;
        if ($averageInvestment > 0 && $amount > ($averageInvestment * 10)) {
            $this->flagSuspiciousActivity(
                $user,
                SuspiciousActivity::TYPE_RAPID_INVESTMENTS,
                SuspiciousActivity::SEVERITY_MEDIUM,
                [
                    'current_amount' => $amount,
                    'average_amount' => $averageInvestment,
                    'multiplier' => $amount / $averageInvestment,
                ],
                ['unusual_investment_amount']
            );
        }
    }

    /**
     * Detect unusual withdrawal patterns
     */
    public function detectUnusualWithdrawals(User $user, float $amount): void
    {
        // Check for rapid withdrawal attempts
        $recentWithdrawals = $user->withdrawalRequests()
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        if ($recentWithdrawals >= 3) {
            $this->flagSuspiciousActivity(
                $user,
                SuspiciousActivity::TYPE_UNUSUAL_WITHDRAWAL,
                SuspiciousActivity::SEVERITY_HIGH,
                [
                    'withdrawal_count_24h' => $recentWithdrawals,
                    'current_amount' => $amount,
                ],
                ['rapid_withdrawal_attempts']
            );
        }

        // Check for withdrawal of entire balance shortly after investment
        $latestInvestment = $user->investments()->latest()->first();
        if ($latestInvestment && $latestInvestment->created_at->diffInDays(now()) < 7) {
            $totalBalance = $user->total_investment_amount + $user->total_profit_earnings;
            if ($amount >= ($totalBalance * 0.9)) {
                $this->flagSuspiciousActivity(
                    $user,
                    SuspiciousActivity::TYPE_UNUSUAL_WITHDRAWAL,
                    SuspiciousActivity::SEVERITY_CRITICAL,
                    [
                        'withdrawal_amount' => $amount,
                        'total_balance' => $totalBalance,
                        'days_since_investment' => $latestInvestment->created_at->diffInDays(now()),
                    ],
                    ['early_full_withdrawal']
                );
            }
        }
    }

    /**
     * Detect suspicious login patterns
     */
    public function detectSuspiciousLogin(User $user, string $ipAddress, string $userAgent): void
    {
        // Check for logins from multiple countries/regions
        $recentIPs = DeviceFingerprint::where('user_id', $user->id)
            ->where('last_seen_at', '>=', now()->subDays(30))
            ->distinct('ip_address')
            ->pluck('ip_address')
            ->toArray();

        if (count($recentIPs) >= 5) {
            $this->flagSuspiciousActivity(
                $user,
                SuspiciousActivity::TYPE_SUSPICIOUS_LOGIN,
                SuspiciousActivity::SEVERITY_MEDIUM,
                [
                    'unique_ips_30_days' => count($recentIPs),
                    'current_ip' => $ipAddress,
                    'recent_ips' => $recentIPs,
                ],
                ['multiple_ip_addresses']
            );
        }

        // Check for failed login attempts
        if ($user->failed_login_attempts >= 5) {
            $this->flagSuspiciousActivity(
                $user,
                SuspiciousActivity::TYPE_SUSPICIOUS_LOGIN,
                SuspiciousActivity::SEVERITY_HIGH,
                [
                    'failed_attempts' => $user->failed_login_attempts,
                    'last_failed_at' => $user->last_failed_login_at,
                ],
                ['excessive_failed_logins']
            );
        }
    }

    /**
     * Calculate and update user risk score
     */
    public function calculateRiskScore(User $user): float
    {
        $riskScore = 0;

        // Base risk factors
        $suspiciousActivities = SuspiciousActivity::where('user_id', $user->id)
            ->unresolved()
            ->get();

        foreach ($suspiciousActivities as $activity) {
            $riskScore += match ($activity->severity) {
                SuspiciousActivity::SEVERITY_LOW => 5,
                SuspiciousActivity::SEVERITY_MEDIUM => 15,
                SuspiciousActivity::SEVERITY_HIGH => 35,
                SuspiciousActivity::SEVERITY_CRITICAL => 50,
                default => 0,
            };
        }

        // Account age factor (newer accounts are riskier)
        $accountAgeDays = $user->created_at->diffInDays(now());
        if ($accountAgeDays < 7) {
            $riskScore += 20;
        } elseif ($accountAgeDays < 30) {
            $riskScore += 10;
        }

        // ID verification factor
        if (!$user->is_id_verified) {
            $riskScore += 15;
        }

        // Investment pattern factor
        $investmentCount = $user->investments()->count();
        $avgInvestmentAmount = $user->investments()->avg('amount') ?? 0;
        
        if ($investmentCount > 10 && $avgInvestmentAmount > 5000) {
            $riskScore += 10; // High activity might be suspicious
        }

        // Cap at 100
        $riskScore = min($riskScore, 100);

        // Update user risk score
        $user->update([
            'risk_score' => $riskScore,
            'risk_assessed_at' => now(),
        ]);

        return $riskScore;
    }

    /**
     * Automatically block user if risk score is too high
     */
    public function autoBlockIfHighRisk(User $user): bool
    {
        $riskScore = $this->calculateRiskScore($user);

        if ($riskScore >= 80) {
            $this->blockUser($user, 'Automatically blocked due to high risk score: ' . $riskScore);
            return true;
        }

        return false;
    }

    /**
     * Block a user
     */
    public function blockUser(User $user, string $reason, ?int $blockedBy = null): void
    {
        $user->update([
            'is_blocked' => true,
            'block_reason' => $reason,
            'blocked_at' => now(),
            'blocked_by' => $blockedBy,
        ]);

        // Log the blocking action
        AuditLog::logEvent(
            AuditLog::EVENT_USER_BLOCKED,
            $user,
            $blockedBy,
            ['is_blocked' => false],
            ['is_blocked' => true, 'block_reason' => $reason],
            null,
            null,
            ['automated' => is_null($blockedBy)]
        );

        Log::warning('User blocked for security reasons', [
            'user_id' => $user->id,
            'reason' => $reason,
            'blocked_by' => $blockedBy,
        ]);
    }

    /**
     * Flag suspicious activity
     */
    private function flagSuspiciousActivity(
        User $user,
        string $activityType,
        string $severity,
        array $activityData,
        array $detectionRules
    ): void {
        SuspiciousActivity::create([
            'user_id' => $user->id,
            'activity_type' => $activityType,
            'severity' => $severity,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'activity_data' => $activityData,
            'detection_rules' => $detectionRules,
        ]);

        Log::info('Suspicious activity detected', [
            'user_id' => $user->id,
            'activity_type' => $activityType,
            'severity' => $severity,
            'detection_rules' => $detectionRules,
        ]);
    }

    /**
     * Store device fingerprint
     */
    private function storeDeviceFingerprint(
        User $user,
        string $fingerprint,
        array $deviceInfo,
        array $browserInfo,
        string $userAgent,
        string $ipAddress
    ): void {
        $existingFingerprint = DeviceFingerprint::where('user_id', $user->id)
            ->where('fingerprint_hash', $fingerprint)
            ->first();

        if ($existingFingerprint) {
            $existingFingerprint->updateLastSeen();
        } else {
            DeviceFingerprint::create([
                'user_id' => $user->id,
                'fingerprint_hash' => $fingerprint,
                'user_agent' => $userAgent,
                'ip_address' => $ipAddress,
                'device_info' => $deviceInfo,
                'browser_info' => $browserInfo,
                'first_seen_at' => now(),
                'last_seen_at' => now(),
            ]);
        }
    }
}