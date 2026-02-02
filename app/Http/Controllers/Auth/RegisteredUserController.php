<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StarterKitService;
use App\Services\DefaultSponsorService;
use App\Services\LgrActivityTrackingService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     * Using Blade template for reliability (no JS dependency).
     */
    public function create(Request $request)
    {
        // Store redirect URL in session if provided via query parameter
        // This allows module landing pages (like BizBoost) to redirect back after registration
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
            'activeTab' => 'register',
            'referralCode' => $request->query('ref'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(
        Request $request, 
        DefaultSponsorService $defaultSponsorService,
        LgrActivityTrackingService $lgrTrackingService
    ): RedirectResponse
    {
        // Normalize phone number BEFORE validation
        $normalizedPhone = $request->phone ? User::normalizePhone($request->phone) : null;
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|lowercase|email|max:255|unique:'.User::class,
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'referral_code' => 'nullable|string|max:20|exists:users,referral_code',
        ], [
            'name.required' => 'Please enter your full name.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'phone.unique' => 'This phone number is already registered.',
            'password.required' => 'Please enter a password.',
            'password.confirmed' => 'The password confirmation does not match.',
            'referral_code.exists' => 'Invalid referral code. Please check and try again.',
        ]);

        // Ensure at least one identifier (email or phone) is provided
        if (empty($request->email) && empty($request->phone)) {
            throw ValidationException::withMessages([
                'email' => 'Please provide either an email address or phone number.',
                'phone' => 'Please provide either an email address or phone number.',
            ]);
        }

        // Check if normalized phone already exists
        if ($normalizedPhone && User::where('phone', $normalizedPhone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => 'This phone number is already registered.',
            ]);
        }

        // Find referrer if referral code provided
        $originalReferrerId = null;
        if ($request->referral_code) {
            $referrer = User::where('referral_code', $request->referral_code)->first();
            if ($referrer) {
                $originalReferrerId = $referrer->id;
            }
        } else {
            // If no referral code provided, use default sponsor (admin/company)
            $defaultSponsor = $defaultSponsorService->getDefaultSponsor();
            if ($defaultSponsor) {
                $originalReferrerId = $defaultSponsor->id;
            }
        }

        // Find the best placement position in the 3x3 matrix (with spillover)
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

            // Note: Points are ONLY awarded when user purchases starter kit
            // See StarterKitService::awardRegistrationBonus() for correct implementation
            // Registration alone does NOT award points

            // CRITICAL: Record LGR activity for referrer (if exists)
            if ($originalReferrerId) {
                $lgrTrackingService->recordReferralRegistration(
                    $originalReferrerId,
                    $user->id,
                    $user->name
                );
            }

            Auth::login($user);

            // Check for pending GrowBiz invitation (token or code)
            if ($request->session()->has('pending_invitation_token') || $request->session()->has('pending_invitation_code')) {
                return to_route('growbiz.invitation.pending');
            }

            // Use intended redirect if set (e.g., from BizBoost landing page)
            // Otherwise redirect to dashboard
            return redirect()->intended(route('dashboard', absolute: false));
            
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle any database errors gracefully
            if ($e->getCode() === '23000') {
                // Duplicate entry error
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
            
            // Re-throw if it's a different error
            throw $e;
        }
    }
}
