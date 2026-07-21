<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectToMyGrowIdentity
{
    public function handle(Request $request, Closure $next, string $app = ''): Response
    {
        if ($app && !config('platform.identity.app_redirect_enabled.' . $app, false)) {
            return $next($request);
        }

        if (Auth::guard('web')->check()) {
            return $next($request);
        }

        $returnUrl = $request->fullUrl();
        $expires = time() + config('platform.identity.return_url_ttl', 300);
        $payload = $returnUrl . '|' . $expires;
        $signingKey = config('platform.identity.signing_key') ?? '';
        $signature = hash_hmac('sha256', $payload, $signingKey);

        $loginUrl = config('platform.identity.login_url');

        $redirectUrl = $loginUrl
            . '?return_url=' . urlencode($returnUrl)
            . '&expires=' . $expires
            . '&signature=' . $signature
            . ($app ? '&app=' . $app : '');

        return redirect()->away($redirectUrl);
    }
}
