<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\DefaultSponsorService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Blade-based authentication controller.
 * 
 * This controller serves pure HTML/CSS auth pages that work without JavaScript.
 * Use these as fallback when Vue/Inertia pages fail to load.
 * 
 * Routes:
 * - GET  /auth/login     -> showLogin()
 * - POST /auth/login     -> login()
 * - GET  /auth/register  -> showRegister()
 * - POST /auth/register  -> register()
 * - GET  /auth/forgot-password -> showForgotPassword()
 */
class BladeAuthController extends Controller
{
    /**
     * Show the Blade login form.
     */
    public function showLogin(Request $request): View
    {
        // Store redirect URL in session if provided
        if ($request->has('redirect')) {
            $redirectUrl = $request->query('redirect');
            if (is_string($redirectUrl) && str_starts_with($redirectUrl, '/')) {
                $request->session()->put('url.intended', url($redirectUrl));
            }
        }

        return view('auth.login', [
            'canResetPassword' => Route::has('password.request'),
        ]);
    }

    /**
     * Handle login request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        // Initialize loan limit for existing users
        $user = $request->user();
        if ($user) {
            $user->initializeLoanLimit();
        }

        // Check for pending GrowBiz invitation
        if ($request->session()->has('pending_invitation_token') || $request->session()->has('pending_invitation_code')) {
            return redirect()->route('growbiz.invitation.pending');
        }

        return redirect()->intended($this->getDefaultRouteForUser($user));
    }

    /**
     * Show the Blade registration form.
     */
    public function showRegister(Request $request): View
    {
        // Store redirect URL in session if provided
        if ($request->has('redirect')) {
            $redirectUrl = $request->query('redirect');
            if (is_string($redirectUrl) && str_starts_with($redirectUrl, '/')) {
                $request->session()->put('url.intended', url($redirectUrl));
            }
        }

        return view('auth.register', [
            'referralCode' => $request->query('ref'),
        ]);
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request, DefaultSponsorService $defaultSponsorService): RedirectResponse
    {
        // Normalize phone number before validation
        $normalizedPhone = $request->phone ? User::normalizePhone($request->phone) : null;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|lowercase|email|max:255|unique:' . User::class,
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'referral_code' => 'nullable|string|max:20|exists:users,referral_code',
        ], [
            'name.required' => 'Please enter your full name.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Please enter a password.',
            'password.confirmed' => 'The password confirmation does not match.',
            'referral_code.exists' => 'Invalid referral code. Please check and try again.',
        ]);

        // Ensure at least one identifier is provided
        if (empty($request->email) && empty($request->phone)) {
            throw ValidationException::withMessages([
                'email' => 'Please provide either an email address or phone number.',
            ]);
        }

        // Check if normalized phone already exists
        if ($normalizedPhone && User::where('phone', $normalizedPhone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => 'This phone number is already registered.',
            ]);
        }

        // Find referrer
        $originalReferrerId = null;
        if ($request->referral_code) {
            $referrer = User::where('referral_code', $request->referral_code)->first();
            if ($referrer) {
                $originalReferrerId = $referrer->id;
            }
        } else {
            $defaultSponsor = $defaultSponsorService->getDefaultSponsor();
            if ($defaultSponsor) {
                $originalReferrerId = $defaultSponsor->id;
            }
        }

        // Find matrix placement
        $actualReferrerId = $originalReferrerId ? User::findMatrixPlacement($originalReferrerId) : null;

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $normalizedPhone,
                'password' => Hash::make($request->password),
                'referrer_id' => $actualReferrerId,
            ]);

            event(new Registered($user));
            Auth::login($user);

            // Check for pending GrowBiz invitation
            if ($request->session()->has('pending_invitation_token') || $request->session()->has('pending_invitation_code')) {
                return to_route('growbiz.invitation.pending');
            }

            return redirect()->intended(route('dashboard', absolute: false));

        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                if (str_contains($e->getMessage(), 'users_email_unique')) {
                    throw ValidationException::withMessages([
                        'email' => 'This email is already registered.',
                    ]);
                }
                if (str_contains($e->getMessage(), 'users_phone_unique')) {
                    throw ValidationException::withMessages([
                        'phone' => 'This phone number is already registered.',
                    ]);
                }
            }
            throw $e;
        }
    }

    /**
     * Show the forgot password form.
     */
    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Get the default route for a user based on their account type.
     */
    private function getDefaultRouteForUser($user): string
    {
        if (!$user) {
            return route('dashboard', absolute: false);
        }

        if ($user->hasRole('Administrator') || $user->hasRole('admin') || $user->hasRole('superadmin')) {
            return route('admin.dashboard', absolute: false);
        }

        $hasActiveEmployee = \App\Models\Employee::where('user_id', $user->id)
            ->where('employment_status', 'active')
            ->exists();

        if ($hasActiveEmployee) {
            return route('employee.portal.dashboard', absolute: false);
        }

        $accountType = $user->getPrimaryAccountType();

        if ($accountType === \App\Enums\AccountType::MEMBER) {
            return route('grownet.dashboard', absolute: false);
        }

        return route('dashboard', absolute: false);
    }
}
