<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LGR\LgrSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LgrSettingsController extends Controller
{
    public function index(): Response
    {
        $settings = LgrSetting::getAllGrouped();

        // Build tier rates from LgrSetting or use defaults
        $tierNames = ['lite', 'basic', 'growth_plus', 'pro'];
        $tierRates = [];
        foreach ($tierNames as $tier) {
            $tierRates[$tier] = [
                'daily_rate' => (float) LgrSetting::get("tier_{$tier}_daily_rate", $tier === 'lite' ? 12.50 : ($tier === 'basic' ? 25.00 : ($tier === 'growth_plus' ? 37.50 : 62.50))),
                'max_earnings' => (float) LgrSetting::get("tier_{$tier}_max_earnings", $tier === 'lite' ? 150 : ($tier === 'basic' ? 300 : ($tier === 'growth_plus' ? 500 : 1000))),
                'cycle_days' => (int) LgrSetting::get("tier_{$tier}_cycle_days", 90),
            ];
        }

        return Inertia::render('Admin/LGR/Settings', [
            'settings' => $settings,
            'tierRates' => $tierRates,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
            'tierRates' => 'sometimes|array',
        ]);

        try {
            foreach ($validated['settings'] as $setting) {
                LgrSetting::set($setting['key'], $setting['value']);
            }

            // Save tier rates if provided
            if ($request->has('tierRates')) {
                foreach ($request->tierRates as $tier => $rates) {
                    LgrSetting::set("tier_{$tier}_daily_rate", $rates['daily_rate'] ?? 0);
                    LgrSetting::set("tier_{$tier}_max_earnings", $rates['max_earnings'] ?? 0);
                    LgrSetting::set("tier_{$tier}_cycle_days", $rates['cycle_days'] ?? 0);
                }
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
