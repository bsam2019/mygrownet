<?php

namespace App\Http\Controllers\Identity;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function showNotice(Request $request): View|RedirectResponse
    {
        return $request->user() && $request->user()->hasVerifiedEmail()
            ? redirect()->intended($this->platformWorkspaceUrl())
            : view('identity.verify-email');
    }

    public function verify(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->away(config('platform.identity.login_url'));
        }

        if (!hash_equals((string) $request->route('id'), (string) $user->getKey())) {
            abort(403);
        }

        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($this->platformWorkspaceUrl('?verified=1'));
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended($this->platformWorkspaceUrl('?verified=1'));
    }

    public function resend(Request $request): RedirectResponse
    {
        if ($request->user() && $request->user()->hasVerifiedEmail()) {
            return redirect()->intended($this->platformWorkspaceUrl());
        }

        $request->user()?->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    private function platformWorkspaceUrl(?string $suffix = null): string
    {
        $baseUrl = config('app.url', 'https://mygrownet.com');
        if (str_contains($baseUrl, 'auth.')) {
            $baseUrl = str_replace('auth.', '', $baseUrl);
        }
        $url = rtrim($baseUrl, '/') . '/workspace';
        return $suffix ? $url . $suffix : $url;
    }
}
