<?php

namespace App\Http\Controllers\Identity;

use App\Domain\Core\Contracts\MyGrowIdentity;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        private MyGrowIdentity $identity
    ) {}

    public function showLogin(Request $request): View
    {
        $returnUrl = $request->query('return_url');
        $signature = $request->query('signature');
        $expires = (int) $request->query('expires', 0);

        if ($returnUrl && $signature && $expires) {
            if (!$this->identity->validateReturnUrl($returnUrl, $signature, $expires)) {
                $returnUrl = null;
            }
            session(['identity_return_url' => $returnUrl]);
        }

        return view('identity.login', [
            'returnUrl' => $returnUrl ?? route('workspace'),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $ipKey = 'identity-login-ip:' . $request->ip();
        $ipLimit = config('platform.identity.rate_limiting.per_ip', 20);

        if (RateLimiter::tooManyAttempts($ipKey, $ipLimit)) {
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', [
                    'seconds' => RateLimiter::availableIn($ipKey),
                    'minutes' => ceil(RateLimiter::availableIn($ipKey) / 60),
                ]),
            ]);
        }

        $user = $this->identity->authenticate($credentials['email'], $credentials['password'], $request);

        if (!$user) {
            RateLimiter::hit($ipKey);
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($ipKey);

        $request->session()->regenerate();
        $request->session()->save();

        $returnUrl = session('identity_return_url');

        if ($returnUrl && $this->identity->validateReturnUrl(
            $returnUrl,
            hash_hmac('sha256', $returnUrl . '|' . time(), config('platform.identity.signing_key') ?? ''),
            time() + config('platform.identity.return_url_ttl', 300)
        )) {
            session()->forget('identity_return_url');
            return redirect()->away($returnUrl);
        }

        return redirect()->intended(route('workspace'));
    }
}
