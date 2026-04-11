<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     * Using Blade template for reliability (no JS dependency).
     */
    public function create(Request $request)
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

        // If this is an Inertia request, force a full page reload
        if ($request->header('X-Inertia')) {
            return Inertia::location(url()->current());
        }

        // Use unified Blade template - bypass Inertia completely
        return response()->view('auth.unified', [
            'activeTab' => 'login',
            'canResetPassword' => Route::has('password.request'),
            'referralCode' => $request->query('ref'),
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

        // Check if there's an intended URL (e.g., from password reset or deep link)
        $intendedUrl = $request->session()->get('url.intended');
        
        // Validate intended URL - clear it if it's a BizBoost route
        // (BizBoost routes may not exist or user may not have access)
        if ($intendedUrl && str_contains($intendedUrl, '/bizboost')) {
            $request->session()->forget('url.intended');
            $intendedUrl = null;
        }

        // If there's a valid intended URL, use it
        if ($intendedUrl) {
            return redirect()->intended($this->getDefaultRouteForUser($user));
        }

        // Otherwise, determine the appropriate redirect based on user preferences and type
        $defaultRoute = $this->getDefaultRouteForUser($user);

        return redirect($defaultRoute);
    }

    /**
     * Get the default route for a user based on their preferences, account type, and role.
     * 
     * Redirect logic (in priority order):
     * 1. User's custom preference (pwa_default_app setting) - highest priority
     * 2. Admin/Administrator role → Admin panel
     * 3. Active Employee record → Workspace (Employee Portal)
     * 4. GrowNet members (lgr_package_id set) → GrowNet dashboard
     * 5. Non-GrowNet members → HomeHub dashboard (/dashboard)
     */
    private function getDefaultRouteForUser($user): string
    {
        if (!$user) {
            return route('dashboard', absolute: false);
        }

        // Priority 1: Check if user has set a custom preference in settings
        if ($user->pwa_default_app) {
            $appRoutes = [
                'grownet' => route('grownet.dashboard', absolute: false),
                'growbuilder' => route('growbuilder.dashboard', absolute: false),
                'bizboost' => route('bizboost.dashboard', absolute: false),
                'growfinance' => route('growfinance.dashboard', absolute: false),
                'growbiz' => route('growbiz.dashboard', absolute: false),
                'marketplace' => route('marketplace.index', absolute: false),
                'wallet' => route('wallet.index', absolute: false),
                'admin' => route('admin.dashboard', absolute: false),
                'dashboard' => route('dashboard', absolute: false),
            ];

            // If user has a valid custom preference, use it
            if (isset($appRoutes[$user->pwa_default_app])) {
                return $appRoutes[$user->pwa_default_app];
            }
        }

        // Priority 2: Admin users go to admin dashboard (check role)
        if ($user->hasRole('Administrator') || $user->hasRole('admin') || $user->hasRole('superadmin')) {
            return route('admin.dashboard', absolute: false);
        }

        // Priority 3: Check if user has an active employee record → Workspace
        $hasActiveEmployee = \App\Models\Employee::where('user_id', $user->id)
            ->where('employment_status', 'active')
            ->exists();
        
        if ($hasActiveEmployee) {
            return route('employee.portal.dashboard', absolute: false);
        }

        // Priority 4: GrowNet members (users with lgr_package_id) → GrowNet dashboard
        if (!is_null($user->lgr_package_id)) {
            return route('grownet.dashboard', absolute: false);
        }

        // Priority 5: Non-GrowNet members → HomeHub dashboard
        // This includes clients, business accounts, and users without GrowNet membership
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
