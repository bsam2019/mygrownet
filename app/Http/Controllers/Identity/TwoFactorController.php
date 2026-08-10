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

        return redirect()->intended($this->platformWorkspaceUrl());
    }

    public function disable(Request $request): RedirectResponse
    {
        if (!$request->user()) {
            return redirect()->away(config('platform.identity.login_url'));
        }

        $request->user()->disableTwoFactorAuth();

        return redirect()->intended($this->platformWorkspaceUrl());
    }

    private function platformWorkspaceUrl(): string
    {
        $baseUrl = config('app.url', 'https://mygrownet.com');
        if (str_contains($baseUrl, 'auth.')) {
            $baseUrl = str_replace('auth.', '', $baseUrl);
        }
        return rtrim($baseUrl, '/') . '/workspace';
    }
}
