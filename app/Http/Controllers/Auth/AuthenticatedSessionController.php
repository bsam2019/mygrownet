<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        // Store redirect URL in session if provided via query parameter
        // This allows module landing pages (like BizBoost) to redirect back after login
        if ($request->has('redirect')) {
            $redirectUrl = $request->query('redirect');
            // Only allow internal redirects (starting with /)
            if (is_string($redirectUrl) && str_starts_with($redirectUrl, '/')) {
                $request->session()->put('url.intended', url($redirectUrl));
            }
        }

        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
            'redirect' => $request->query('redirect'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Initialize loan limit for existing users on login
        $user = $request->user();
        if ($user) {
            $user->initializeLoanLimit();
        }

        // Check for pending GrowBiz invitation (token or code)
        if ($request->session()->has('pending_invitation_token') || $request->session()->has('pending_invitation_code')) {
            return redirect()->route('growbiz.invitation.pending');
        }

        // Determine the appropriate redirect based on user type
        $defaultRoute = $this->getDefaultRouteForUser($user);

        return redirect()->intended($defaultRoute);
    }

    /**
     * Get the default route for a user based on their account type and role.
     * 
     * Redirect logic:
     * - Admin/Administrator role → Admin panel
     * - Member account type → GrowNet dashboard
     * - Client/Business account type → HomeHub dashboard
     */
    private function getDefaultRouteForUser($user): string
    {
        // Admin users go to admin dashboard (check role first)
        if ($user && ($user->hasRole('Administrator') || $user->hasRole('admin'))) {
            return route('admin.dashboard', absolute: false);
        }

        // Check account type for routing
        if ($user) {
            $accountType = $user->getPrimaryAccountType();
            
            // Member account type → GrowNet dashboard
            if ($accountType === \App\Enums\AccountType::MEMBER) {
                return route('grownet.dashboard', absolute: false);
            }
            
            // Client and Business account types → HomeHub dashboard
            // (This is the default, but being explicit for clarity)
            if ($accountType === \App\Enums\AccountType::CLIENT || 
                $accountType === \App\Enums\AccountType::BUSINESS) {
                return route('dashboard', absolute: false);
            }
            
            // Investor account type → Investor portal (if exists) or dashboard
            if ($accountType === \App\Enums\AccountType::INVESTOR) {
                return route('dashboard', absolute: false);
            }
            
            // Employee account type → Admin panel (internal staff)
            if ($accountType === \App\Enums\AccountType::EMPLOYEE) {
                return route('admin.dashboard', absolute: false);
            }
        }

        // Default: go to the app launcher (HomeHub/Dashboard)
        return route('dashboard', absolute: false);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
