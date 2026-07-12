<?php

namespace App\Http\Controllers\BizDocs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuestController extends Controller
{
    public function login()
    {
        return Inertia::render('BizDocs/Auth/Login', [
            'canResetPassword' => true,
            'status' => session('status'),
        ]);
    }

    public function register()
    {
        return Inertia::render('BizDocs/Auth/Register', [
            'status' => session('status'),
        ]);
    }

    public function forgotPassword()
    {
        return Inertia::render('BizDocs/Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        return Inertia::render('BizDocs/Auth/ResetPassword', [
            'token' => $request->route('token'),
            'email' => $request->email,
        ]);
    }

    public function terms()
    {
        return Inertia::render('BizDocs/Terms');
    }

    public function privacy()
    {
        return Inertia::render('BizDocs/Privacy');
    }

    public function about()
    {
        return Inertia::render('BizDocs/About');
    }
}
