<?php

namespace App\Http\Middleware;

use App\Services\FraudDetectionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DetectFraudulentActivity
{
    public function __construct(
        private FraudDetectionService $fraudDetectionService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user is blocked
            if ($user->is_blocked) {
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['blocked' => 'Your account has been blocked due to security concerns. Please contact support.']);
            }

            // Collect device information from request headers
            $deviceInfo = [
                'screen' => $request->header('X-Screen-Resolution', ''),
                'timezone' => $request->header('X-Timezone', ''),
                'language' => $request->header('Accept-Language', ''),
                'platform' => $request->header('X-Platform', ''),
            ];

            $browserInfo = [
                'name' => $this->getBrowserName($request->userAgent()),
                'version' => $this->getBrowserVersion($request->userAgent()),
            ];

            // Detect duplicate accounts
            $this->fraudDetectionService->detectDuplicateAccounts(
                $user,
                $deviceInfo,
                $browserInfo,
                $request->userAgent()
            );

            // Detect suspicious login patterns
            $this->fraudDetectionService->detectSuspiciousLogin(
                $user,
                $request->ip(),
                $request->userAgent()
            );

            // Auto-block if high risk
            if ($this->fraudDetectionService->autoBlockIfHighRisk($user)) {
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['blocked' => 'Your account has been temporarily blocked for security review.']);
            }
        }

        return $next($request);
    }

    /**
     * Extract browser name from user agent
     */
    private function getBrowserName(string $userAgent): string
    {
        if (strpos($userAgent, 'Chrome') !== false) {
            return 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            return 'Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            return 'Edge';
        }

        return 'Unknown';
    }

    /**
     * Extract browser version from user agent
     */
    private function getBrowserVersion(string $userAgent): string
    {
        if (preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches)) {
            return $matches[1];
        } elseif (preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches)) {
            return $matches[1];
        } elseif (preg_match('/Version\/([0-9.]+).*Safari/', $userAgent, $matches)) {
            return $matches[1];
        } elseif (preg_match('/Edge\/([0-9.]+)/', $userAgent, $matches)) {
            return $matches[1];
        }

        return 'Unknown';
    }
}