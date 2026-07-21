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
            ? redirect()->intended(route('workspace'))
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
            return redirect()->intended(route('workspace') . '?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended(route('workspace') . '?verified=1');
    }

    public function resend(Request $request): RedirectResponse
    {
        if ($request->user() && $request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('workspace'));
        }

        $request->user()?->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
