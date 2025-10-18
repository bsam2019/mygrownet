<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        return new UserResource(auth()->user()->load('profile'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'required|string|max:20',
            'profile.address' => 'nullable|string|max:255',
            'profile.city' => 'nullable|string|max:100',
            'profile.country' => 'nullable|string|max:100',
            'profile.preferred_payment_method' => 'nullable|string|max:50',
            'profile.bank_name' => 'nullable|string|max:100',
            'profile.bank_account_number' => 'nullable|string|max:50',
            'profile.bank_branch' => 'nullable|string|max:100',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone']
        ]);

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            array_filter($validated['profile'])
        );

        return new UserResource($user->load('profile'));
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return response()->json([
            'message' => 'Password updated successfully'
        ]);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048'
        ]);

        $user = auth()->user();

        if ($user->profile?->avatar) {
            Storage::disk('public')->delete($user->profile->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            ['avatar' => $path]
        );

        return response()->json([
            'message' => 'Avatar updated successfully',
            'avatar_url' => Storage::disk('public')->url($path)
        ]);
    }

    public function notifications()
    {
        return response()->json([
            'unread' => auth()->user()->unreadNotifications,
            'recent' => auth()->user()->notifications()
                ->latest()
                ->take(10)
                ->get()
        ]);
    }

    public function markNotificationsAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read'
        ]);
    }
}
