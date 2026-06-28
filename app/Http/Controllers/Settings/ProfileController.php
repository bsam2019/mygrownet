<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        try {
            \Log::info('ProfileController@edit called', [
                'user' => $request->user()?->id,
                'path' => $request->path(),
                'url' => $request->url()
            ]);
            
            return Inertia::render('settings/Profile', [
                'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
                'status' => $request->session()->get('status'),
            ]);
        } catch (\Exception $e) {
            \Log::error('ProfileController@edit error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Only admins can modify the email field; ignore email changes from non-admins
        if (! $request->user()->hasRole('admin')) {
            unset($data['email']);
        }

        $request->user()->fill($data);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit')->with('success', 'Profile updated successfully!');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Update notification settings (for mobile)
     */
    public function updateNotificationSettings(Request $request)
    {
        $validated = $request->validate([
            'notifications' => 'boolean',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
        ]);

        $user = $request->user();
        
        // Update or create notification preferences
        $user->notificationPreferences()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'push_enabled' => $validated['notifications'] ?? true,
                'email_enabled' => $validated['email_notifications'] ?? true,
                'sms_enabled' => $validated['sms_notifications'] ?? false,
            ]
        );

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Notification settings updated successfully',
            ]);
        }

        return back()->with('success', 'Notification settings updated successfully');
    }

    /**
     * Update dashboard preference (mobile vs desktop)
     */
    public function updateDashboardPreference(Request $request)
    {
        $validated = $request->validate([
            'preference' => 'required|in:auto,mobile,desktop',
        ]);

        $user = $request->user();
        
        // Map 'classic' to 'desktop' for backward compatibility
        $preference = $validated['preference'];
        if ($preference === 'classic') {
            $preference = 'desktop';
        }
        
        $user->update([
            'preferred_dashboard' => $preference,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dashboard preference updated successfully',
            'preference' => $preference,
        ]);
    }
}
