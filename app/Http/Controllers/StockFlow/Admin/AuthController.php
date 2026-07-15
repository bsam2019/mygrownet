<?php

namespace App\Http\Controllers\StockFlow\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('StockFlow/Admin/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('stockflow')->attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::guard('stockflow')->user();
            if (!$user->is_stockflow_admin) {
                Auth::guard('stockflow')->logout();
                return back()->withErrors([
                    'email' => 'You do not have admin access.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->intended(route('stockflow.admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('stockflow')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('stockflow.admin.login');
    }
}
