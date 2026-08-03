<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin(Request $request): RedirectResponse
    {
        return $this->redirectToIdentity(
            $request,
            config('platform.identity.login_url'),
            'bizboost'
        );
    }

    public function login(Request $request): RedirectResponse
    {
        return $this->showLogin($request);
    }

    public function showRegister(Request $request): RedirectResponse
    {
        return $this->redirectToIdentity(
            $request,
            config('platform.identity.register_url'),
            'bizboost'
        );
    }

    public function register(Request $request): RedirectResponse
    {
        return $this->showRegister($request);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->away(config('platform.identity.login_url'));
    }

    public function forgotPassword(Request $request): RedirectResponse
    {
        return redirect()->away(config('platform.identity.password_reset_url'));
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        return redirect()->away(config('platform.identity.password_reset_url'));
    }

    private function redirectToIdentity(Request $request, string $targetUrl, string $app): RedirectResponse
    {
        $returnUrl = $request->getSchemeAndHttpHost() . '/workspace';
        $expires = time() + config('platform.identity.return_url_ttl', 300);
        $payload = $returnUrl . '|' . $expires;
        $signingKey = config('platform.identity.signing_key') ?? '';
        $signature = hash_hmac('sha256', $payload, $signingKey);

        return redirect()->away($targetUrl
            . '?return_url=' . urlencode($returnUrl)
            . '&expires=' . $expires
            . '&signature=' . $signature
            . '&app=' . $app);
    }
}