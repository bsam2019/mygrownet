<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class OtpController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
        $this->middleware('auth');
    }

    public function generate(GenerateOtpRequest $request): JsonResponse
    {
        $user = $request->user();
        $type = $request->input('type', 'email'); // Default to email
        $purpose = $request->input('purpose', 'sensitive_operation');
        $identifier = $request->input('identifier');
        $metadata = $request->input('metadata', []);

        // Add request context to metadata
        $metadata = array_merge($metadata, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'requested_at' => now()->toISOString()
        ]);

        $result = $this->otpService->generateOtp($user, $type, $purpose, $identifier, $metadata);

        if ($result['success']) {
            Log::info('OTP generation requested', [
                'user_id' => $user->id,
                'type' => $type,
                'purpose' => $purpose,
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'message' => $result['message'],
                'expires_at' => $result['expires_at'],
                'identifier' => $result['identifier'],
                'type' => $type,
                'purpose' => $purpose
            ]);
        }

        return response()->json([
            'message' => $result['message'],
            'retry_after' => $result['retry_after'] ?? null
        ], 429);
    }

    public function verify(VerifyOtpRequest $request): JsonResponse
    {
        $user = $request->user();
        $token = $request->input('token');
        $type = $request->input('type', 'email');
        $purpose = $request->input('purpose', 'sensitive_operation');

        $result = $this->otpService->verifyOtp($user, $token, $type, $purpose);

        if ($result['success']) {
            // Store verification in session for middleware
            $otpSessionKey = "otp_verified:{$user->id}:{$purpose}";
            Session::put($otpSessionKey, now());

            Log::info('OTP verification successful', [
                'user_id' => $user->id,
                'type' => $type,
                'purpose' => $purpose,
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'message' => $result['message'],
                'verified' => true,
                'token_id' => $result['token_id']
            ]);
        }

        Log::warning('OTP verification failed', [
            'user_id' => $user->id,
            'type' => $type,
            'purpose' => $purpose,
            'ip_address' => $request->ip(),
            'message' => $result['message']
        ]);

        return response()->json([
            'message' => $result['message'],
            'verified' => false
        ], 400);
    }

    public function resend(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:email,sms',
            'purpose' => 'required|string|max:50'
        ]);

        $user = $request->user();
        $type = $request->input('type');
        $purpose = $request->input('purpose');

        $result = $this->otpService->resendOtp($user, $type, $purpose);

        if ($result['success']) {
            Log::info('OTP resend requested', [
                'user_id' => $user->id,
                'type' => $type,
                'purpose' => $purpose,
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'message' => $result['message'],
                'expires_at' => $result['expires_at'],
                'identifier' => $result['identifier']
            ]);
        }

        return response()->json([
            'message' => $result['message'],
            'retry_after' => $result['retry_after'] ?? null
        ], 429);
    }

    public function status(Request $request): JsonResponse
    {
        $request->validate([
            'purpose' => 'required|string|max:50'
        ]);

        $user = $request->user();
        $purpose = $request->input('purpose');

        // Check if user has valid OTP verification for this purpose
        $otpSessionKey = "otp_verified:{$user->id}:{$purpose}";
        $isVerified = Session::has($otpSessionKey) && Session::get($otpSessionKey) >= now()->subMinutes(5);

        return response()->json([
            'verified' => $isVerified,
            'purpose' => $purpose,
            'expires_at' => $isVerified ? Session::get($otpSessionKey)->addMinutes(5) : null
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $days = $request->input('days', 30);

        $stats = $this->otpService->getOtpStats($user, $days);

        return response()->json([
            'stats' => $stats,
            'period_days' => $days
        ]);
    }
}