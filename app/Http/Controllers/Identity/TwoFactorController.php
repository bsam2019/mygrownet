<?php

namespace App\Http\Controllers\Identity;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function showSetup(Request $request): View|RedirectResponse
    {
        if (!$request->user()) {
            return redirect()->away(config('platform.identity.login_url'));
        }

        return view('identity.2fa-setup');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        if (!$request->user()) {
            return redirect()->away(config('platform.identity.login_url'));
        }

        $confirmed = $request->user()->confirmTwoFactorAuth($request->code);

        if (!$confirmed) {
            return back()->withErrors(['code' => 'Invalid two-factor authentication code.']);
        }

        return redirect()->intended(route('workspace'));
    }

    public function disable(Request $request): RedirectResponse
    {
        if (!$request->user()) {
            return redirect()->away(config('platform.identity.login_url'));
        }

        $request->user()->disableTwoFactorAuth();

        return redirect()->intended(route('workspace'));
    }
}
