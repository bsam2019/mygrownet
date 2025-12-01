<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class ImpersonateController extends Controller
{
    /**
     * Impersonate a user
     */
    public function impersonate(User $user)
    {
        $admin = Auth::user();
        
        // Prevent impersonating another admin
        if ($user->roles()->where('name', 'admin')->exists()) {
            return back()->withErrors(['error' => 'Cannot impersonate another admin.']);
        }

        // Store the original admin ID in session
        Session::put('impersonate_admin_id', $admin->id);
        
        // Log the impersonation for audit trail
        Log::info('Admin impersonation started', [
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'ip' => request()->ip(),
        ]);

        // Login as the user
        Auth::login($user);

        return redirect()->route('home')->with('success', 'You are now viewing as ' . $user->name);
    }

    /**
     * Stop impersonating and return to admin account
     */
    public function leave()
    {
        if (!Session::has('impersonate_admin_id')) {
            return redirect()->route('home');
        }

        $currentUser = Auth::user();
        $adminId = Session::get('impersonate_admin_id');
        $admin = User::findOrFail($adminId);

        // Log the end of impersonation
        Log::info('Admin impersonation ended', [
            'admin_id' => $adminId,
            'admin_name' => $admin->name,
            'user_id' => $currentUser->id,
            'user_name' => $currentUser->name,
            'ip' => request()->ip(),
        ]);

        // Clear the impersonation session
        Session::forget('impersonate_admin_id');

        // Login back as admin
        Auth::login($admin);

        return redirect()->route('admin.users.index')->with('success', 'You are now back to your admin account.');
    }
}
