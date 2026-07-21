<?php

namespace App\Http\Controllers\StockFlow\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin(Request $request): RedirectResponse
    {
        $returnUrl = $request->fullUrl();
        $expires = time() + config('platform.identity.return_url_ttl', 300);
        $payload = $returnUrl . '|' . $expires;
        $signingKey = config('platform.identity.signing_key') ?? '';
        $signature = hash_hmac('sha256', $payload, $signingKey);

        return redirect()->away(config('platform.identity.login_url')
            . '?return_url=' . urlencode($returnUrl)
            . '&expires=' . $expires
            . '&signature=' . $signature
            . '&app=stockflow');
    }

    public function login(Request $request): RedirectResponse
    {
        return $this->showLogin($request);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->away(config('platform.identity.login_url'));
    }
}
