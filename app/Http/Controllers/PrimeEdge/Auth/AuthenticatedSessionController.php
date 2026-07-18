<?php

namespace App\Http\Controllers\PrimeEdge\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/** @deprecated Use App\Http\Controllers\Platform\UnifiedAuthController instead. Will be removed after Phase 8 validation. */
class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return Inertia::render('PrimeEdge/Auth/Login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('primeedge')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('primeedge.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function destroy(Request $request)
    {
        Auth::guard('primeedge')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('primeedge.public.landing');
    }
}
