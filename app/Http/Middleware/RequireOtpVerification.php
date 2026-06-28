<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class RequireOtpVerification
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $purpose = 'sensitive_operation'): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Check if OTP verification is required for this operation
        if ($this->requiresOtpVerification($request, $purpose)) {
            // Check if user has a valid OTP verification session
            $otpSessionKey = "otp_verified:{$user->id}:{$purpose}";
            
            if (!Session::has($otpSessionKey) || Session::get($otpSessionKey) < now()->subMinutes(5)) {
                return response()->json([
                    'message' => 'OTP verification required',
                    'requires_otp' => true,
                    'purpose' => $purpose,
                    'user_phone' => $user->phone ? $this->maskPhone($user->phone) : null,
                    'user_email' => $user->email ? $this->maskEmail($user->email) : null
                ], 403);
            }
        }

        return $next($request);
    }

    protected function requiresOtpVerification(Request $request, string $purpose): bool
    {
        // Define which operations require OTP verification
        $sensitiveOperations = [
            'withdrawal',
            'sensitive_operation',
            'tier_upgrade',
            'profile_update'
        ];

        return in_array($purpose, $sensitiveOperations);
    }

    protected function maskPhone(string $phone): string
    {
        if (strlen($phone) < 4) {
            return str_repeat('*', strlen($phone));
        }
        
        return substr($phone, 0, 4) . str_repeat('*', 4) . substr($phone, -4);
    }

    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) === 2) {
            $username = $parts[0];
            $domain = $parts[1];
            $maskedUsername = substr($username, 0, 1) . str_repeat('*', max(0, strlen($username) - 1));
            return $maskedUsername . '@' . $domain;
        }
        
        return $email;
    }
}