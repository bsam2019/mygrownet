<?php

namespace App\Http\Controllers\GrowNet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuestController extends Controller
{
    public function login()
    {
        return Inertia::render('GrowNet/Auth/Login', [
            'canResetPassword' => true,
            'status' => session('status'),
        ]);
    }

    public function register()
    {
        return Inertia::render('GrowNet/Auth/Register', [
            'status' => session('status'),
        ]);
    }

    public function forgotPassword()
    {
        return Inertia::render('GrowNet/Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        return Inertia::render('GrowNet/Auth/ResetPassword', [
            'token' => $request->route('token'),
            'email' => $request->email,
        ]);
    }

    public function terms()
    {
        return Inertia::render('GrowNet/Terms');
    }

    public function privacy()
    {
        return Inertia::render('GrowNet/Privacy');
    }

    public function about()
    {
        return Inertia::render('GrowNet/About');
    }
}
