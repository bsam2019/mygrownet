<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\RoleModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel;
use App\Models\User;
use App\Services\CMS\SecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function __construct(
        private SecurityService $securityService
    ) {}

    /**
     * Show the login page
     */
    public function showLogin()
    {
        return Inertia::render('CMS/Auth/Login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['boolean'],
        ]);

        $remember = $credentials['remember'] ?? false;
        $email = $credentials['email'];
        $password = $credentials['password'];

        // Find user by email
        $user = User::where('email', $email)->first();

        // Check if user exists and has CMS access
        if (!$user) {
            $this->securityService->recordLoginAttempt(
                email: $email,
                userId: null,
                successful: false,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
                failureReason: 'user_not_found'
            );

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $cmsUser = CmsUserModel::where('user_id', $user->id)->first();

        if (!$cmsUser) {
            $this->securityService->recordLoginAttempt(
                email: $email,
                userId: $user->id,
                successful: false,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
                failureReason: 'no_cms_access'
            );

            return back()->withErrors([
                'email' => 'You do not have access to the CMS system.',
            ])->onlyInput('email');
        }

        // Check if account is locked
        if ($this->securityService->isAccountLocked($user)) {
            $lockoutMinutes = ceil($user->locked_until->diffInMinutes(now()));
            
            $this->securityService->recordLoginAttempt(
                email: $email,
                userId: $user->id,
                successful: false,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
                failureReason: 'account_locked'
            );

            return back()->withErrors([
                'email' => "Account is locked due to multiple failed login attempts. Please try again in {$lockoutMinutes} minutes.",
            ])->onlyInput('email');
        }

        // Attempt authentication
        if (!Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            // Failed login
            $this->securityService->recordLoginAttempt(
                email: $email,
                userId: $user->id,
                successful: false,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
                failureReason: 'invalid_password'
            );

            $this->securityService->handleFailedLogin($user, $cmsUser->company_id);

            // Check if account was just locked
            $user->refresh();
            if ($this->securityService->isAccountLocked($user)) {
                $settings = $this->securityService->getSecuritySettings($cmsUser->company_id);
                return back()->withErrors([
                    'email' => "Account locked after {$settings['max_login_attempts']} failed attempts. Please try again in {$settings['lockout_duration_minutes']} minutes.",
                ])->onlyInput('email');
            }

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        // Successful login
        $request->session()->regenerate();

        // Record successful login
        $this->securityService->recordLoginAttempt(
            email: $email,
            userId: $user->id,
            successful: true,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $this->securityService->handleSuccessfulLogin($user);

        // Check if password change is required
        if ($user->force_password_change || $this->securityService->isPasswordExpired($user, $cmsUser->company_id)) {
            return redirect()->route('cms.password.change')
                ->with('warning', 'Your password has expired. Please change it to continue.');
        }

        return redirect()->intended(route('cms.dashboard'));
    }

    /**
     * Show the registration page
     */
    public function showRegister()
    {
        return Inertia::render('CMS/Auth/Register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        try {
            DB::beginTransaction();

            // Create a placeholder company first (needed for security settings)
            $company = CompanyModel::create([
                'name' => $validated['name'] . "'s Company", // Temporary name
                'industry' => 'Not Set',
                'owner_user_id' => null, // Will be set after user creation
                'settings' => [
                    'currency' => 'ZMW',
                    'timezone' => 'Africa/Lusaka',
                    'date_format' => 'Y-m-d',
                    'onboarding_completed' => false,
                ],
            ]);

            // Validate password strength
            $passwordValidation = $this->securityService->validatePasswordStrength(
                $validated['password'],
                $company->id
            );

            if (!$passwordValidation['valid']) {
                DB::rollBack();
                return back()->withErrors([
                    'password' => $passwordValidation['errors'],
                ])->withInput();
            }

            // Create main user account
            $hashedPassword = Hash::make($validated['password']);
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $hashedPassword,
                'account_type' => 'cms',
                'password_changed_at' => now(),
            ]);

            // Update company owner
            $company->update(['owner_user_id' => $user->id]);

            // Save password to history
            $this->securityService->savePasswordHistory($user->id, $hashedPassword);

            // Get or create Owner role
            $ownerRole = RoleModel::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'name' => 'Owner',
                ],
                [
                    'permissions' => ['*'], // Full access
                    'description' => 'Company owner with full access',
                ]
            );

            // Create CMS user record
            CmsUserModel::create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'role_id' => $ownerRole->id,
                'is_active' => true,
            ]);

            // Log security event
            $this->securityService->logSecurityEvent(
                userId: $user->id,
                companyId: $company->id,
                eventType: 'account_created',
                ipAddress: $request->ip(),
                description: 'New CMS account registered',
                metadata: ['email' => $user->email],
                severity: 'info'
            );

            DB::commit();

            // Log the user in
            Auth::login($user);

            // Record successful login
            $this->securityService->recordLoginAttempt(
                email: $user->email,
                userId: $user->id,
                successful: true,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent()
            );

            // Redirect to onboarding wizard
            return redirect()->route('cms.onboarding.index');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'Registration failed. Please try again.',
            ])->withInput();
        }
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        if ($user) {
            $cmsUser = CmsUserModel::where('user_id', $user->id)->first();
            
            if ($cmsUser) {
                $this->securityService->logSecurityEvent(
                    userId: $user->id,
                    companyId: $cmsUser->company_id,
                    eventType: 'logout',
                    ipAddress: $request->ip(),
                    description: 'User logged out',
                    severity: 'info'
                );
            }
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('cms.landing');
    }

    /**
     * Show password change page
     */
    public function showPasswordChange()
    {
        return Inertia::render('CMS/Auth/ChangePassword', [
            'forced' => Auth::user()->force_password_change ?? false,
        ]);
    }

    /**
     * Handle password change request
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $cmsUser = CmsUserModel::where('user_id', $user->id)->first();

        if (!$cmsUser) {
            return back()->withErrors(['error' => 'CMS user not found.']);
        }

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Validate new password strength
        $passwordValidation = $this->securityService->validatePasswordStrength(
            $validated['password'],
            $cmsUser->company_id
        );

        if (!$passwordValidation['valid']) {
            return back()->withErrors(['password' => $passwordValidation['errors']]);
        }

        // Check if password was used recently
        if ($this->securityService->isPasswordReused($user->id, $validated['password'], $cmsUser->company_id)) {
            return back()->withErrors([
                'password' => 'Cannot reuse a recent password. Please choose a different password.',
            ]);
        }

        // Update password
        $hashedPassword = Hash::make($validated['password']);
        $user->update([
            'password' => $hashedPassword,
            'password_changed_at' => now(),
            'force_password_change' => false,
        ]);

        // Save to password history
        $this->securityService->savePasswordHistory($user->id, $hashedPassword);

        // Log security event
        $this->securityService->logSecurityEvent(
            userId: $user->id,
            companyId: $cmsUser->company_id,
            eventType: 'password_changed',
            ipAddress: $request->ip(),
            description: 'User changed password',
            severity: 'info'
        );

        return redirect()->route('cms.dashboard')
            ->with('success', 'Password changed successfully.');
    }
}
