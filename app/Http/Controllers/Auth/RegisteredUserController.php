<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StarterKitService;
use App\Services\DefaultSponsorService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, DefaultSponsorService $defaultSponsorService): RedirectResponse
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
        $referrerId = null;
        if ($request->referral_code) {
            $referrer = User::where('referral_code', $request->referral_code)->first();
            if ($referrer) {
                $referrerId = $referrer->id;
            }
        } else {
            // If no referral code provided, use default sponsor (admin/company)
            $defaultSponsor = $defaultSponsorService->getDefaultSponsor();
            if ($defaultSponsor) {
                $referrerId = $defaultSponsor->id;
            }
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $normalizedPhone,
                'password' => Hash::make($request->password),
                'referrer_id' => $referrerId,
            ]);

            event(new Registered($user));

            // Award points for registration
            $user->awardPointsForActivity('registration', 'New member registration');

            // Award points to referrer if exists
            if ($referrerId) {
                $referrer = User::find($referrerId);
                $referrer->awardPointsForActivity('direct_referral', "Referred new member: {$user->name}");
            }

            // Note: Starter kit processing removed - handled separately when user purchases
            // $starterKitService->processStarterKit($user);

            Auth::login($user);

            return to_route('dashboard');
            
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
