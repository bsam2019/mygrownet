<?php

namespace App\Http\Controllers\GrowStorage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuestController extends Controller
{
    public function login()
    {
        return Inertia::render('GrowStorage/Auth/Login', [
            'canResetPassword' => true,
            'status' => session('status'),
        ]);
    }

    public function register()
    {
        return Inertia::render('GrowStorage/Auth/Register', [
            'status' => session('status'),
        ]);
    }

    public function forgotPassword()
    {
        return Inertia::render('GrowStorage/Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        return Inertia::render('GrowStorage/Auth/ResetPassword', [
            'token' => $request->route('token'),
            'email' => $request->email,
        ]);
    }

    public function terms()
    {
        return Inertia::render('GrowStorage/Terms');
    }

    public function privacy()
    {
        return Inertia::render('GrowStorage/Privacy');
    }

    public function about()
    {
        return Inertia::render('GrowStorage/About');
    }
}
