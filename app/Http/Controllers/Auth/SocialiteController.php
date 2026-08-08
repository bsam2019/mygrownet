<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DefaultSponsorService;
use App\Domain\GrowNet\Services\LgrActivityTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirectToGoogle(Request $request)
    {
        $domain = $request->getHost();
        $callbackUrl = match ($domain) {
            'auth.mygrownet.com' => route('auth.google.callback', [], true),
            'bizboost.mygrownet.com' => route('bizboost.sub.auth.google.callback', [], true),
            'cms.mygrownet.com' => route('bms.subdomain.auth.google.callback', [], true),
            'growmart.mygrownet.com' => route('growmart.auth.google.callback', [], true),
            'zamstay.mygrownet.com' => route('zamstay.sub.auth.google.callback', [], true),
            default => route('auth.google.callback', [], true),
        };

        session(['socialite_redirect' => $request->input('redirect', url()->previous())]);

        // Preserve the identity gateway's signed return URL so the callback
        // returns the user to their originating app after Google sign-in.
        if (session()->has('identity_return_url')) {
            session(['socialite_identity_return_url' => session('identity_return_url')]);
        }

        // Preserve referral code through the OAuth flow
        if ($request->has('ref')) {
            session(['socialite_referral_code' => $request->input('ref')]);
        }

        return Socialite::driver('google')->redirectUrl($callbackUrl)->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Google authentication failed. Please try again.');
        }

        $socialAccount = \App\Models\SocialAccount::where('provider', 'google')
            ->where('provider_id', $googleUser->getId())
            ->first();

        if ($socialAccount) {
            $user = $socialAccount->user;
        } else {
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                $user->socialAccounts()->create([
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            } else {
                // Find referrer from session (set during redirectToGoogle)
                $referralCode = session()->pull('socialite_referral_code');
                $referrerId = null;

                if ($referralCode) {
                    $referrer = User::where('referral_code', $referralCode)->first();
                    if ($referrer) {
                        // Use the same 3x3 matrix placement logic as regular registration
                        $referrerId = User::findMatrixPlacement($referrer->id);
                    }
                }

                if (!$referrerId) {
                    // Fallback to default sponsor
                    $defaultSponsor = app(DefaultSponsorService::class)->getDefaultSponsor();
                    if ($defaultSponsor) {
                        $referrerId = $defaultSponsor->id;
                    }
                }

                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt(\Str::random(32)),
                    'referrer_id' => $referrerId,
                ]);

                $user->socialAccounts()->create([
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);

                // Record LGR activity for referrer
                if ($referrerId) {
                    try {
                        app(LgrActivityTrackingService::class)->recordReferralRegistration(
                            $referrerId,
                            $user->id,
                            $user->name
                        );
                    } catch (\Exception $e) {
                        // Non-critical failure
                    }
                }
            }
        }

        Auth::login($user);

        $redirect = session()->pull('socialite_redirect', '/dashboard');

        // If this OAuth flow started from the identity gateway with a signed
        // return URL (auth.mygrownet.com/login?return_url=...), honor it.
        if (session()->has('socialite_identity_return_url')) {
            $returnUrl = session()->pull('socialite_identity_return_url');
            session()->forget('identity_return_url');
            if ($returnUrl) {
                return redirect()->away($returnUrl);
            }
        }

        return redirect($redirect);
    }
}
