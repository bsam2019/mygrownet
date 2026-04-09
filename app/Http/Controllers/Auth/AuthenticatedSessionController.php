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

        // Determine the appropriate redirect based on user type
        $defaultRoute = $this->getDefaultRouteForUser($user);

        // Get intended URL from session
        $intendedUrl = $request->session()->get('url.intended');
        
        // Validate intended URL - clear it if it's a BizBoost route
        // (BizBoost routes may not exist or user may not have access)
        if ($intendedUrl && str_contains($intendedUrl, '/bizboost')) {
            $request->session()->forget('url.intended');
            return redirect($defaultRoute);
        }

        return redirect()->intended($defaultRoute);
    }

    /**
     * Get the default route for a user based on their account type and role.
     * 
     * Redirect logic:
     * - Admin/Administrator role → Admin panel
     * - Active Employee record → Workspace (Employee Portal)
     * - GrowNet members (lgr_package_id set) → GrowNet dashboard
     * - Non-GrowNet members → HomeHub dashboard (/dashboard)
     * 
     * Note: User preferences (pwa_default_app) are handled by PWARedirect middleware
     */
    private function getDefaultRouteForUser($user): string
    {
        if (!$user) {
            return route('dashboard', absolute: false);
        }

        // Admin users go to admin dashboard (check role first)
        if ($user->hasRole('Administrator') || $user->hasRole('admin') || $user->hasRole('superadmin')) {
            return route('admin.dashboard', absolute: false);
        }

        // Check if user has an active employee record → Workspace
        $hasActiveEmployee = \App\Models\Employee::where('user_id', $user->id)
            ->where('employment_status', 'active')
            ->exists();
        
        if ($hasActiveEmployee) {
            return route('employee.portal.dashboard', absolute: false);
        }

        // GrowNet members (users with lgr_package_id) → GrowNet dashboard
        if (!is_null($user->lgr_package_id)) {
            return route('grownet.dashboard', absolute: false);
        }

        // Non-GrowNet members → HomeHub dashboard
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
