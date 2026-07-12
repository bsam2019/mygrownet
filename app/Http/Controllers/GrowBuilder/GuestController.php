<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuestController extends Controller
{
    public function login()
    {
        return Inertia::render('GrowBuilder/Auth/Login', [
            'canResetPassword' => true,
            'status' => session('status'),
        ]);
    }

    public function register()
    {
        return Inertia::render('GrowBuilder/Auth/Register', [
            'status' => session('status'),
        ]);
    }

    public function forgotPassword()
    {
        return Inertia::render('GrowBuilder/Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        return Inertia::render('GrowBuilder/Auth/ResetPassword', [
            'token' => $request->route('token'),
            'email' => $request->email,
        ]);
    }

    public function terms()
    {
        return Inertia::render('GrowBuilder/Terms');
    }

    public function privacy()
    {
        return Inertia::render('GrowBuilder/Privacy');
    }

    public function about()
    {
        return Inertia::render('GrowBuilder/About');
    }
}
