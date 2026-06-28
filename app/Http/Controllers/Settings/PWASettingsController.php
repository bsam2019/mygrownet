<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PWASettingsController extends Controller
{
    /**
     * Show PWA settings page
     */
    public function index()
    {
        $user = auth()->user();
        
        // Determine smart default based on user type
        $smartDefault = null;
        if ($user->hasRole(['Administrator', 'admin', 'superadmin'])) {
            $smartDefault = 'admin';
        } elseif (!is_null($user->lgr_package_id)) {
            $smartDefault = 'grownet';
        } else {
            $smartDefault = 'dashboard';
        }
        
        return Inertia::render('settings/PWA', [
            'hasGrowNetPackage' => !is_null($user->lgr_package_id),
            'isAdmin' => $user->hasRole(['Administrator', 'admin', 'superadmin']),
            'smartDefault' => $smartDefault,
        ]);
    }

    /**
     * Update PWA default app preference
     */
    public function update(Request $request)
    {
        $request->validate([
            'pwa_default_app' => 'nullable|string|in:grownet,growbuilder,bizboost,growfinance,growbiz,marketplace,wallet,admin,dashboard',
        ]);

        $user = auth()->user();
        
        $user->pwa_default_app = $request->pwa_default_app;
        $user->save();

        return back()->with('success', 'PWA preference updated successfully!');
    }
}
