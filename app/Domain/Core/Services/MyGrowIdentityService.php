<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Contracts\MyGrowIdentity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Lockout;

class MyGrowIdentityService implements MyGrowIdentity
{
    public function authenticate(string $email, string $password, Request $request): ?User
    {
        $this->ensureIsNotRateLimited($email, $request);

        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            RateLimiter::hit($this->throttleKey($email, $request));
            return null;
        }

        RateLimiter::clear($this->throttleKey($email, $request));

        if (!Auth::loginUsingId($user->id)) {
            return null;
        }

        $request->session()->regenerate();

        return $user;
    }

    public function validateSession(string $token): ?User
    {
        if (strlen($token) > 100) {
            return Auth::guard('web')->user();
        }

        $user = User::where('email', $token)->first();
        return $user ? Auth::guard('web')->user() : null;
    }

    public function redirectToLogin(string $returnUrl): string
    {
        $expires = time() + config('platform.identity.return_url_ttl', 300);
        $payload = $returnUrl . '|' . $expires;
        $signature = hash_hmac('sha256', $payload, config('platform.identity.signing_key') ?? '');
        $loginUrl = config('platform.identity.login_url');

        return $loginUrl
            . '?return_url=' . urlencode($returnUrl)
            . '&expires=' . $expires
            . '&signature=' . $signature;
    }

    public function getLoginUrl(): string
    {
        return config('platform.identity.login_url');
    }

    public function validateReturnUrl(string $returnUrl, string $signature, int $expires): bool
    {
        if (time() > $expires) {
            return false;
        }

        $payload = $returnUrl . '|' . $expires;
        $expected = hash_hmac('sha256', $payload, config('platform.identity.signing_key') ?? '');

        if (!hash_equals($expected, $signature)) {
            return false;
        }

        $host = parse_url($returnUrl, PHP_URL_HOST);
        $allowed = config('platform.identity.allowed_return_hosts', ['*.mygrownet.com']);

        foreach ($allowed as $pattern) {
            if (fnmatch($pattern, $host)) {
                return true;
            }
        }

        return false;
    }

    public function mintToken(User $user, string $deviceName): string
    {
        if (method_exists($user, 'createToken')) {
            return $user->createToken($deviceName)->plainTextToken;
        }

        throw new \RuntimeException('Sanctum is not installed. Cannot mint API tokens.');
    }

    private function ensureIsNotRateLimited(string $email, Request $request): void
    {
        $maxAttempts = config('platform.identity.rate_limiting.per_user', 5);
        $key = $this->throttleKey($email, $request);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            event(new Lockout($request));

            $seconds = RateLimiter::availableIn($key);

            throw new \Illuminate\Validation\ValidationException(
                validator(request()->all(), []),
                __('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ])
            );
        }
    }

    private function throttleKey(string $email, Request $request): string
    {
        return Str::transliterate(Str::lower($email) . '|' . $request->ip());
    }
}
