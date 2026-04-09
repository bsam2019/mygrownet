<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    /**
     * Show the password reset link request page.
     * Using Blade template for reliability (no JS dependency).
     */
    public function create(Request $request)
    {
        // Use Blade template - bypass Inertia completely
        return response()->view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            // Check if email was sent successfully
            if ($status === Password::RESET_LINK_SENT) {
                return back()->with('status', __('Password reset link has been sent to your email. Please check your inbox and spam folder.'));
            }

            // If user not found, still show success for security
            return back()->with('status', __('If an account exists with this email, a password reset link has been sent. Please check your inbox and spam folder.'));
            
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Password reset email failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Show message with support contact info
            return back()->with('status', __('We\'re experiencing technical difficulties sending the email. Please contact support for assistance.'));
        }
    }
}
