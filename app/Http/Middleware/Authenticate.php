<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        $returnUrl = $request->fullUrl();
        $expires = time() + config('platform.identity.return_url_ttl', 300);
        $payload = $returnUrl . '|' . $expires;
        $signingKey = config('platform.identity.signing_key') ?? '';
        $signature = hash_hmac('sha256', $payload, $signingKey);

        return config('platform.identity.login_url')
            . '?return_url=' . urlencode($returnUrl)
            . '&expires=' . $expires
            . '&signature=' . $signature;
    }
}
