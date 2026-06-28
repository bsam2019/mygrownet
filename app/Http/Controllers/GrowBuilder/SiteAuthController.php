<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Infrastructure\GrowBuilder\Models\SiteUser;
use App\Infrastructure\GrowBuilder\Models\SiteUserSession;
use App\Services\GrowBuilder\SiteRoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SiteAuthController extends Controller
{
    public function __construct(
        protected SiteRoleService $roleService
    ) {}

    /**
     * Show login page
     */
    public function showLogin(string $subdomain)
    {
        $site = $this->getSite($subdomain);

        return Inertia::render('SiteMember/Auth/Login', [
            'site' => $this->getSiteData($site),
            'subdomain' => $subdomain,
        ]);
    }

    /**
     * Handle login
     */
    public function login(Request $request, string $subdomain)
    {
        $site = $this->getSite($subdomain);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = SiteUser::forSite($site->id)
            ->where('email', $request->email)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        if (!$user->isActive()) {
            return back()->withErrors([
                'email' => 'Your account is not active. Please contact the site administrator.',
            ]);
        }

        // Create session
        $session = SiteUserSession::createForUser(
            $user,
            $request->ip(),
            $request->userAgent()
        );

        // Record login
        $user->recordLogin($request->ip());

        // Set cookie and redirect
        $cookie = cookie(
            'site_user_token_' . $site->id,
            $session->token,
            60 * 24 * 7, // 7 days
            '/',
            null,
            false,
            true
        );

        return redirect()
            ->route('site.member.dashboard', ['subdomain' => $subdomain])
            ->withCookie($cookie);
    }

    /**
     * Show registration page
     */
    public function showRegister(string $subdomain)
    {
        $site = $this->getSite($subdomain);

        return Inertia::render('SiteMember/Auth/Register', [
            'site' => $this->getSiteData($site),
            'subdomain' => $subdomain,
        ]);
    }

    /**
     * Handle registration
     */
    public function register(Request $request, string $subdomain)
    {
        $site = $this->getSite($subdomain);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check if email already exists for this site
        if (SiteUser::forSite($site->id)->where('email', $request->email)->exists()) {
            return back()->withErrors([
                'email' => 'This email is already registered.',
            ]);
        }

        // Get default role
        $defaultRole = $this->roleService->getDefaultMemberRole($site);

        // Create user
        $user = SiteUser::create([
            'site_id' => $site->id,
            'site_role_id' => $defaultRole?->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => 'active', // Or 'pending' if email verification required
        ]);

        // Create session
        $session = SiteUserSession::createForUser(
            $user,
            $request->ip(),
            $request->userAgent()
        );

        // Set cookie and redirect
        $cookie = cookie(
            'site_user_token_' . $site->id,
            $session->token,
            60 * 24 * 7,
            '/',
            null,
            false,
            true
        );

        return redirect()
            ->route('site.member.dashboard', ['subdomain' => $subdomain])
            ->withCookie($cookie);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request, string $subdomain)
    {
        $site = $this->getSite($subdomain);
        
        $sessionToken = $request->cookie('site_user_token_' . $site->id);
        
        if ($sessionToken) {
            SiteUserSession::where('token', $sessionToken)->delete();
        }

        $cookie = cookie()->forget('site_user_token_' . $site->id);

        return redirect()
            ->route('site.login', ['subdomain' => $subdomain])
            ->withCookie($cookie);
    }

    /**
     * Show forgot password page
     */
    public function showForgotPassword(string $subdomain)
    {
        $site = $this->getSite($subdomain);

        return Inertia::render('SiteMember/Auth/ForgotPassword', [
            'site' => $this->getSiteData($site),
            'subdomain' => $subdomain,
        ]);
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(Request $request, string $subdomain)
    {
        $site = $this->getSite($subdomain);

        $request->validate([
            'email' => 'required|email',
        ]);

        $user = SiteUser::forSite($site->id)
            ->where('email', $request->email)
            ->first();

        if ($user) {
            // Generate token
            $token = Str::random(64);
            
            \DB::table('site_user_password_resets')->updateOrInsert(
                ['site_id' => $site->id, 'email' => $request->email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );

            // TODO: Send email with reset link
            // Mail::to($user)->send(new SitePasswordResetMail($site, $token));
        }

        return back()->with('status', 'If an account exists with that email, you will receive a password reset link.');
    }

    /**
     * Show reset password page
     */
    public function showResetPassword(string $subdomain, string $token)
    {
        $site = $this->getSite($subdomain);

        return Inertia::render('SiteMember/Auth/ResetPassword', [
            'site' => $this->getSiteData($site),
            'subdomain' => $subdomain,
            'token' => $token,
        ]);
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request, string $subdomain)
    {
        $site = $this->getSite($subdomain);

        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = \DB::table('site_user_password_resets')
            ->where('site_id', $site->id)
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Invalid or expired reset token.']);
        }

        // Check if token is expired (1 hour)
        if (now()->diffInMinutes($record->created_at) > 60) {
            return back()->withErrors(['email' => 'Reset token has expired.']);
        }

        // Update password
        SiteUser::forSite($site->id)
            ->where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        // Delete reset record
        \DB::table('site_user_password_resets')
            ->where('site_id', $site->id)
            ->where('email', $request->email)
            ->delete();

        return redirect()
            ->route('site.login', ['subdomain' => $subdomain])
            ->with('status', 'Password has been reset successfully.');
    }

    /**
     * Get site by subdomain
     */
    protected function getSite(string $subdomain): GrowBuilderSite
    {
        $site = GrowBuilderSite::where('subdomain', $subdomain)->first();

        if (!$site) {
            abort(404, 'Site not found');
        }

        return $site;
    }

    /**
     * Get site data for frontend
     */
    protected function getSiteData(GrowBuilderSite $site): array
    {
        return [
            'id' => $site->id,
            'name' => $site->name,
            'subdomain' => $site->subdomain,
            'logo' => $site->logo,
            'theme' => $site->theme,
        ];
    }
}
