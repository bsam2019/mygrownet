<?php

namespace App\Http\Controllers\Platform;

use App\Domain\Core\Contracts\IdentityProvider;
use App\Domain\Core\Models\UserApplication;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnifiedAuthController extends Controller
{
    public function __construct(
        private IdentityProvider $identityProvider,
    ) {}

    public function showLogin(): Response|RedirectResponse
    {
        if (!config('platform.features.unified_auth', false)) {
            return redirect('/login');
        }

        return Inertia::render('Platform/Auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        if (!config('platform.features.unified_auth', false)) {
            return redirect('/login');
        }

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = $this->identityProvider->authenticate($credentials['email'], $credentials['password']);

        if (!$user) {
            return back()->withErrors(['email' => 'Invalid credentials.']);
        }

        auth('web')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $app = $this->resolvePrimaryApplication($user);
        if ($app) {
            $config = config("platform.applications.{$app}");
            $dashboard = ($config['domain_slug'] ?? $app) . '.dashboard';
            if (app('router')->has($dashboard)) {
                return redirect()->route($dashboard);
            }
        }

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        auth('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function resolvePrimaryApplication(\App\Models\User $user): ?string
    {
        $link = UserApplication::where('user_id', $user->id)
            ->with('application')
            ->first();

        return $link?->application?->slug;
    }
}
