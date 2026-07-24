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

        return view('auth.unified', [
            'activeTab' => 'login',
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

        // Persist session before redirect so auth middleware on next request finds it
        $request->session()->save();

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

        return view('auth.unified', [
            'activeTab' => 'register',
            'referralCode' => $request->query('ref'),
        ]);
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request, DefaultSponsorService $defaultSponsorService): RedirectResponse
    {
        // Determine whether identifier is email or phone
        $isNewFlow = $request->has('identifier');

        if ($isNewFlow) {
            $identifier = $request->identifier;
            $isEmail = $identifier && str_contains($identifier, '@');
            $isPhone = $identifier && !$isEmail;
            $normalizedPhone = $isPhone ? User::normalizePhone($identifier) : null;
        } else {
            // Legacy flow: separate email and phone fields (Blade view)
            $email = $request->email;
            $phone = $request->phone;
            $isEmail = !empty($email);
            $isPhone = !$isEmail && !empty($phone);
            $normalizedPhone = $isPhone ? User::normalizePhone($phone) : null;
            $identifier = $isEmail ? $email : ($isPhone ? $phone : null);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'referral_code' => 'nullable|string|max:20|exists:users,referral_code',
        ];

        $messages = [
            'name.required' => 'Please enter your full name.',
            'password.required' => 'Please enter a password.',
            'password.confirmed' => 'The password confirmation does not match.',
            'referral_code.exists' => 'Invalid referral code. Please check and try again.',
        ];

        if ($isNewFlow) {
            // New single-field flow (LoginModal)
            if ($isEmail) {
                $rules['identifier'] = 'required|string|lowercase|email|max:255|unique:users,email';
                $messages['identifier.unique'] = 'This email is already registered.';
            } elseif ($isPhone) {
                $rules['identifier'] = 'required|string|max:20';
            } else {
                $rules['identifier'] = 'required|string|max:255';
            }
            $messages['identifier.required'] = 'Please enter your email or phone number.';
            $messages['identifier.email'] = 'Please enter a valid email address.';
        } else {
            // Legacy dual-field flow (Blade view)
            $rules['email'] = 'nullable|string|lowercase|email|max:255|unique:users,email';
            $rules['phone'] = 'nullable|string|max:20';
            $messages['email.email'] = 'Please enter a valid email address.';
            $messages['email.unique'] = 'This email is already registered.';
        }

        $request->validate($rules, $messages);

        // Ensure at least one identifier is provided
        if (empty($identifier)) {
            $field = $isNewFlow ? 'identifier' : 'email';
            throw ValidationException::withMessages([
                $field => 'Please provide either an email address or phone number.',
            ]);
        }

        // Check if phone already exists
        if ($isPhone && $normalizedPhone && User::where('phone', $normalizedPhone)->exists()) {
            $field = $isNewFlow ? 'identifier' : 'phone';
            throw ValidationException::withMessages([
                $field => 'This phone number is already registered.',
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
                'email' => $isEmail ? ($isNewFlow ? $identifier : $email) : null,
                'phone' => $isPhone ? $normalizedPhone : null,
                'password' => Hash::make($request->password),
                'referrer_id' => $actualReferrerId,
            ]);

            event(new Registered($user));
            Auth::login($user);
            $request->session()->regenerate();
            $request->session()->save();

            
            return redirect()->route('workspace');

        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                if (str_contains($e->getMessage(), 'users_email_unique')) {
                    throw ValidationException::withMessages([
                        $isNewFlow ? 'identifier' : 'email' => 'This email is already registered.',
                    ]);
                }
                if (str_contains($e->getMessage(), 'users_phone_unique')) {
                    throw ValidationException::withMessages([
                        $isNewFlow ? 'identifier' : 'phone' => 'This phone number is already registered.',
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
        return route('workspace', absolute: false);
    }
}

