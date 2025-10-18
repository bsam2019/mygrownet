<?php

namespace App\Http\Middleware;

use App\Services\IdVerificationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireIdVerification
{
    public function __construct(
        private IdVerificationService $idVerificationService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user needs ID verification
            if ($this->idVerificationService->requiresVerification($user)) {
                // Allow access to verification routes
                $allowedRoutes = [
                    'verification.show',
                    'verification.store',
                    'verification.documents',
                    'logout',
                ];

                if (!in_array($request->route()->getName(), $allowedRoutes)) {
                    return redirect()->route('verification.show')
                        ->with('warning', 'Please complete ID verification to continue using your account.');
                }
            }
        }

        return $next($request);
    }
}