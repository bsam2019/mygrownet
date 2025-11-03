<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LgrSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LgrSettingsController extends Controller
{
    public function index(): Response
    {
        $settings = LgrSetting::getAllGrouped();
        
        return Inertia::render('Admin/LGR/Settings', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
        ]);

        try {
            foreach ($validated['settings'] as $setting) {
                LgrSetting::set($setting['key'], $setting['value']);
            }

            LgrSetting::clearCache();

            return redirect()
                ->back()
                ->with('success', 'LGR settings updated successfully');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Failed to update settings: ' . $e->getMessage()]);
        }
    }
}
