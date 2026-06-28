<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuestController extends Controller
{
    public function login()
    {
        return Inertia::render('BizBoost/Auth/Login', [
            'canResetPassword' => true,
            'status' => session('status'),
        ]);
    }

    public function register()
    {
        return Inertia::render('BizBoost/Auth/Register', [
            'status' => session('status'),
        ]);
    }

    public function forgotPassword()
    {
        return Inertia::render('BizBoost/Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        return Inertia::render('BizBoost/Auth/ResetPassword', [
            'token' => $request->route('token'),
            'email' => $request->email,
        ]);
    }

    public function terms()
    {
        return Inertia::render('BizBoost/Terms');
    }

    public function privacy()
    {
        return Inertia::render('BizBoost/Privacy');
    }

    public function about()
    {
        return Inertia::render('BizBoost/About');
    }
}
