<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Initialize loan limit for existing users on login
        $user = $request->user();
        if ($user) {
            $user->initializeLoanLimit();
        }

        // Determine redirect based on subdomain
        $host = $request->getHost();
        $redirectMap = [
            'bizboost.mygrownet.com'     => 'bizboost.sub.dashboard',
            'growmart.mygrownet.com'     => 'growmart.sub.dashboard',
            'zamstay.mygrownet.com'      => 'zamstay.sub.dashboard',
            'bizdocs.mygrownet.com'      => 'bizdocs.sub.dashboard',
            'growbuilder.mygrownet.com'  => 'growbuilder.sub.dashboard',
            'venture.mygrownet.com'      => 'venture.sub.dashboard',
            'grownet.mygrownet.com'      => 'grownet.sub.dashboard',
            'growstorage.mygrownet.com'  => 'growstorage.sub.dashboard',
        ];
        $fallback = $redirectMap[$host] ?? 'workspace';

        // Persist session before redirect so auth middleware on next request finds it
        $request->session()->save();

        return redirect()->intended(route($fallback, absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Determine redirect based on subdomain
        $host = $request->getHost();
        $redirectMap = [
            'bizboost.mygrownet.com'     => 'bizboost.sub.welcome',
            'growmart.mygrownet.com'     => 'growmart.sub.welcome',
            'zamstay.mygrownet.com'      => 'zamstay.sub.welcome',
            'bizdocs.mygrownet.com'      => 'bizdocs.sub.welcome',
            'growbuilder.mygrownet.com'  => 'growbuilder.sub.welcome',
            'venture.mygrownet.com'      => 'venture.sub.welcome',
            'grownet.mygrownet.com'      => 'grownet.sub.welcome',
            'growstorage.mygrownet.com'  => 'growstorage.sub.welcome',
        ];
        $fallback = $redirectMap[$host] ?? '/';

        return redirect($fallback === '/' ? '/' : route($fallback, absolute: false));
    }
}
