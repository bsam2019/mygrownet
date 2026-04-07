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
        
        return Inertia::render('settings/PWA', [
            'hasGrowNetPackage' => !is_null($user->lgr_package_id),
        ]);
    }

    /**
     * Update PWA default app preference
     */
    public function update(Request $request)
    {
        $request->validate([
            'pwa_default_app' => 'nullable|string|in:grownet,growbuilder,bizboost,growfinance,growbiz,marketplace,wallet',
        ]);

        $user = auth()->user();
        
        // Don't allow GrowNet users to change (they're auto-redirected)
        if (!is_null($user->lgr_package_id)) {
            return back()->with('error', 'GrowNet members are automatically directed to GrowNet dashboard.');
        }

        $user->pwa_default_app = $request->pwa_default_app;
        $user->save();

        return back()->with('success', 'PWA preference updated successfully!');
    }
}
