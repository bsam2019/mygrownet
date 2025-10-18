<?php

namespace App\Services;

use App\Models\User;
use App\Models\OtpToken;
use App\Notifications\OtpNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OtpService
{
    const MAX_ATTEMPTS_PER_HOUR = 5;
    const MAX_VERIFICATION_ATTEMPTS = 3;
    const OTP_EXPIRY_MINUTES = 10;
    const RATE_LIMIT_MINUTES = 60;

    public function generateOtp(
        User $user, 
        string $type, 
        string $purpose, 
        string $identifier = null,
        array $metadata = []
    ): array {
        // Check rate limiting
        if (!$this->checkRateLimit($user, $type, $purpose)) {
            return [
                'success' => false,
                'message' => 'Too many OTP requests. Please try again later.',
                'retry_after' => $this->getRateLimitRetryAfter($user, $type, $purpose)
            ];
        }

        // Invalidate any existing valid OTPs for the same purpose
        $this->invalidateExistingOtps($user, $type, $purpose);

        // Generate 6-digit OTP
        $token = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Determine identifier based on type
        $identifier = $identifier ?? ($type === 'sms' ? $user->phone : $user->email);

        if (!$identifier) {
            return [
                'success' => false,
                'message' => $type === 'sms' ? 'Phone number not found' : 'Email address not found'
            ];
        }

        // Create OTP token
        $otpToken = OtpToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'type' => $type,
            'purpose' => $purpose,
            'identifier' => $identifier,
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata
        ]);

        // Send OTP notification
        try {
            $user->notify(new OtpNotification($otpToken));
            
            // Update rate limiting cache
            $this->updateRateLimit($user, $type, $purpose);

            Log::info('OTP generated successfully', [
                'user_id' => $user->id,
                'type' => $type,
                'purpose' => $purpose,
                'identifier' => $this->maskIdentifier($identifier, $type)
            ]);

            return [
                'success' => true,
                'message' => 'OTP sent successfully',
                'expires_at' => $otpToken->expires_at,
                'identifier' => $this->maskIdentifier($identifier, $type)
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send OTP', [
                'user_id' => $user->id,
                'type' => $type,
                'purpose' => $purpose,
                'error' => $e->getMessage()
            ]);

            // Delete the token if sending failed
            $otpToken->delete();

            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.'
            ];
        }
    }

    public function verifyOtp(
        User $user, 
        string $token, 
        string $type, 
        string $purpose
    ): array {
        $otpToken = OtpToken::forUser($user->id)
            ->forType($type)
            ->forPurpose($purpose)
            ->where('token', $token)
            ->first();

        if (!$otpToken) {
            Log::warning('OTP verification failed - token not found', [
                'user_id' => $user->id,
                'type' => $type,
                'purpose' => $purpose,
                'ip_address' => request()->ip()
            ]);

            return [
                'success' => false,
                'message' => 'Invalid OTP code'
            ];
        }

        // Check if token is still valid
        if (!$otpToken->isValid()) {
            $reason = $otpToken->is_used ? 'already used' : 
                     ($otpToken->isExpired() ? 'expired' : 'too many attempts');

            Log::warning('OTP verification failed - token invalid', [
                'user_id' => $user->id,
                'type' => $type,
                'purpose' => $purpose,
                'reason' => $reason,
                'ip_address' => request()->ip()
            ]);

            return [
                'success' => false,
                'message' => "OTP is {$reason}. Please request a new one."
            ];
        }

        // Increment attempts
        $otpToken->incrementAttempts();

        // Check if this attempt exceeds the limit
        if ($otpToken->attempts > self::MAX_VERIFICATION_ATTEMPTS) {
            Log::warning('OTP verification failed - too many attempts', [
                'user_id' => $user->id,
                'type' => $type,
                'purpose' => $purpose,
                'attempts' => $otpToken->attempts,
                'ip_address' => request()->ip()
            ]);

            return [
                'success' => false,
                'message' => 'Too many verification attempts. Please request a new OTP.'
            ];
        }

        // Mark as used and verified
        $otpToken->markAsUsed();

        Log::info('OTP verified successfully', [
            'user_id' => $user->id,
            'type' => $type,
            'purpose' => $purpose,
            'ip_address' => request()->ip()
        ]);

        return [
            'success' => true,
            'message' => 'OTP verified successfully',
            'token_id' => $otpToken->id
        ];
    }

    public function resendOtp(User $user, string $type, string $purpose): array
    {
        // Check if there's a recent OTP that can be resent
        $recentOtp = OtpToken::forUser($user->id)
            ->forType($type)
            ->forPurpose($purpose)
            ->recent(2) // Within last 2 minutes
            ->first();

        if ($recentOtp) {
            return [
                'success' => false,
                'message' => 'Please wait before requesting another OTP',
                'retry_after' => 120 - now()->diffInSeconds($recentOtp->created_at)
            ];
        }

        return $this->generateOtp($user, $type, $purpose);
    }

    protected function checkRateLimit(User $user, string $type, string $purpose): bool
    {
        $key = $this->getRateLimitKey($user, $type, $purpose);
        $attempts = Cache::get($key, 0);
        
        return $attempts < self::MAX_ATTEMPTS_PER_HOUR;
    }

    protected function updateRateLimit(User $user, string $type, string $purpose): void
    {
        $key = $this->getRateLimitKey($user, $type, $purpose);
        $attempts = Cache::get($key, 0) + 1;
        
        Cache::put($key, $attempts, now()->addMinutes(self::RATE_LIMIT_MINUTES));
    }

    protected function getRateLimitRetryAfter(User $user, string $type, string $purpose): int
    {
        $key = $this->getRateLimitKey($user, $type, $purpose);
        $ttl = Cache::getStore()->getRedis()->ttl($key);
        
        return max(0, $ttl);
    }

    protected function getRateLimitKey(User $user, string $type, string $purpose): string
    {
        return "otp_rate_limit:{$user->id}:{$type}:{$purpose}";
    }

    protected function invalidateExistingOtps(User $user, string $type, string $purpose): void
    {
        OtpToken::forUser($user->id)
            ->forType($type)
            ->forPurpose($purpose)
            ->valid()
            ->update(['is_used' => true]);
    }

    protected function maskIdentifier(string $identifier, string $type): string
    {
        if ($type === 'sms') {
            // Mask phone number: +1234567890 -> +123****7890
            return substr($identifier, 0, 4) . str_repeat('*', 4) . substr($identifier, -4);
        } else {
            // Mask email: user@example.com -> u***@example.com
            $parts = explode('@', $identifier);
            if (count($parts) === 2) {
                $username = $parts[0];
                $domain = $parts[1];
                $maskedUsername = substr($username, 0, 1) . str_repeat('*', max(0, strlen($username) - 1));
                return $maskedUsername . '@' . $domain;
            }
        }
        
        return $identifier;
    }

    public function cleanupExpiredTokens(): int
    {
        $deleted = OtpToken::where('expires_at', '<', now()->subHours(24))->delete();
        
        Log::info('Cleaned up expired OTP tokens', ['deleted_count' => $deleted]);
        
        return $deleted;
    }

    public function getOtpStats(User $user, int $days = 30): array
    {
        $stats = OtpToken::forUser($user->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('
                type,
                purpose,
                COUNT(*) as total_generated,
                SUM(CASE WHEN verified_at IS NOT NULL THEN 1 ELSE 0 END) as verified_count,
                AVG(attempts) as avg_attempts
            ')
            ->groupBy(['type', 'purpose'])
            ->get();

        return $stats->toArray();
    }
}