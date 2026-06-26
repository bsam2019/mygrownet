<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirectToGoogle(Request $request)
    {
        $domain = $request->getHost();
        $callbackUrl = match ($domain) {
            'bizboost.mygrownet.com' => route('bizboost.sub.auth.google.callback', [], true),
            'cms.mygrownet.com' => route('cms.subdomain.auth.google.callback', [], true),
            'growmart.mygrownet.com' => route('growmart.auth.google.callback', [], true),
            'zamstay.mygrownet.com' => route('zamstay.sub.auth.google.callback', [], true),
            default => route('auth.google.callback', [], true),
        };

        session(['socialite_redirect' => $request->input('redirect', url()->previous())]);

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
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt(\Str::random(32)),
                ]);

                $user->socialAccounts()->create([
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            }
        }

        Auth::login($user);

        $redirect = session()->pull('socialite_redirect', '/dashboard');

        return redirect($redirect);
    }
}
