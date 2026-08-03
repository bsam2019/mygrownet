<?php

namespace App\Http\Controllers\BMS;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\BMS\RoleModel;
use App\Infrastructure\Persistence\Eloquent\BMS\CmsUserModel;
use App\Models\User;
use App\Domain\BMS\Core\Services\IndustryPresetService;
use App\Services\BMS\SecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function __construct(
        private SecurityService $securityService,
        private IndustryPresetService $presetService
    ) {}

    /**
     * Show the login page
     *
     * Redirects to the MyGrow Identity Gateway (auth.mygrownet.com) so BMS uses
     * the platform-wide centralized login, matching StockFlow/PrimeEdge.
     */
    public function showLogin(Request $request): RedirectResponse
    {
        $returnUrl = $request->getSchemeAndHttpHost() . '/workspace';
        $expires = time() + config('platform.identity.return_url_ttl', 300);
        $payload = $returnUrl . '|' . $expires;
        $signingKey = config('platform.identity.signing_key') ?? '';
        $signature = hash_hmac('sha256', $payload, $signingKey);

        return redirect()->away(config('platform.identity.login_url')
            . '?return_url=' . urlencode($returnUrl)
            . '&expires=' . $expires
            . '&signature=' . $signature
            . '&app=bms');
    }

    /**
     * Handle login request — delegated to the identity gateway.
     */
    public function login(Request $request): RedirectResponse
    {
        return $this->showLogin($request);
    }

    /**
     * Show the registration page with industry presets for rapid company setup.
     */
    public function showRegister()
    {
        $presets = \App\Infrastructure\Persistence\Eloquent\BMS\IndustryPresetModel::active()->ordered()->get([
            'id', 'code', 'name', 'description', 'icon', 'sort_order',
        ]);

        return Inertia::render('BMS/Auth/Register', [
            'presets' => $presets,
        ]);
    }

    /**
     * Handle registration request — creates user + company in one step.
     * Redirects straight to dashboard.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'        => ['required', 'confirmed', Password::defaults()],
            'company_name'    => ['required', 'string', 'max:255'],
            'industry_type'   => ['nullable', 'string', 'max:100'],
            'preset_code'     => ['nullable', 'string', 'exists:cms_industry_presets,code'],
        ]);

        try {
            DB::beginTransaction();

            $hashedPassword = Hash::make($validated['password']);

            $user = User::create([
                'name'                => $validated['name'],
                'email'               => $validated['email'],
                'password'            => $hashedPassword,
                'account_type'        => 'business',
                'password_changed_at' => now(),
            ]);

            $company = CompanyModel::create([
                'name'               => $validated['company_name'],
                'industry_type'      => $validated['industry_type'] ?? null,
                'email'              => $validated['email'],
                'city'               => 'Lusaka',
                'country'            => 'Zambia',
                'status'             => 'active',
                'subscription_type'  => 'complimentary',
                'complimentary_until' => now()->addDays(14),
                'settings' => [
                    'currency'     => 'ZMW',
                    'vat_enabled'  => true,
                    'vat_rate'     => 16,
                    'invoice_due_days' => 30,
                ],
            ]);

            $ownerRole = RoleModel::create([
                'company_id'     => $company->id,
                'name'           => 'Owner',
                'is_system_role' => true,
                'permissions'    => ['*'],
                'approval_authority' => ['limit' => 999999999],
            ]);

            CmsUserModel::create([
                'company_id' => $company->id,
                'user_id'    => $user->id,
                'role_id'    => $ownerRole->id,
                'status'     => 'active',
            ]);

            if (!empty($validated['preset_code'])) {
                $this->presetService->applyPresetToCompany($company->id, $validated['preset_code']);
            }

            $this->setDefaultBizDocsTemplates($company);

            DB::commit();

            Auth::login($user);
            session(['active_cms_company_id' => $company->id]);

            $this->securityService->recordLoginAttempt(
                email: $user->email,
                userId: $user->id,
                successful: true,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent()
            );

            return redirect()->route('bms.dashboard')
                ->with('success', "Welcome to {$company->name}! Your company is ready.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
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

        session()->forget('active_cms_company_id');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('bms.landing');
    }

    /**
     * Show password change page
     */
    public function showPasswordChange()
    {
        return Inertia::render('BMS/Auth/ChangePassword', [
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

        return redirect()->route('bms.dashboard')
            ->with('success', 'Password changed successfully.');
    }

    /**
     * Switch the active company for the current session.
     */
    public function switchCompany(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|integer',
        ]);

        $user = $request->user();

        // Verify the user actually belongs to this company
        $membership = $user->cmsUsers()
            ->where('company_id', $validated['company_id'])
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            return back()->withErrors(['error' => 'You do not have access to that company.']);
        }

        // Store the active company in session
        session(['active_cms_company_id' => $validated['company_id']]);

        // Clear cached user data so the new company loads fresh
        $user->unsetRelation('cmsUsers');

        return redirect()->route('bms.dashboard')
            ->with('success', "Switched to {$membership->company->name}.");
    }
}
