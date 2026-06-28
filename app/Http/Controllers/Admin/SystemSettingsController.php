<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\SystemSetting;
use DB;

class SystemSettingsController extends Controller
{
    protected $settingsCache = 'system_settings';
    
    protected $validSettings = [
        'lock_in_period',
        'early_withdrawal_penalty',
        'max_partial_withdrawal',
        'direct_referral_rates',
        'level2_referral_rates',
        'level3_referral_rates',
        'quarterly_bonus_pool_percentage',
        'minimum_investment_amount',
    ];

    public function getSettings()
    {
        $settings = Cache::remember($this->settingsCache, 3600, function () {
            return SystemSetting::all()->pluck('value', 'key');
        });

        return response()->json($settings);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'required'
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->settings as $key => $value) {
                if (in_array($key, $this->validSettings)) {
                    SystemSetting::updateOrCreate(
                        ['key' => $key],
                        ['value' => $value]
                    );
                }
            }

            Cache::forget($this->settingsCache);
            DB::commit();

            return response()->json([
                'message' => 'Settings updated successfully',
                'settings' => $this->getSettings()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function resetToDefault()
    {
        try {
            DB::beginTransaction();

            // Default settings based on business rules
            $defaultSettings = [
                'lock_in_period' => 12,
                'early_withdrawal_penalty' => 0.50,
                'max_partial_withdrawal' => 0.50,
                'direct_referral_rates' => json_encode([
                    'Basic' => 0.05,
                    'Starter' => 0.07,
                    'Builder' => 0.10,
                    'Leader' => 0.12,
                    'Elite' => 0.15
                ]),
                'level2_referral_rates' => json_encode([
                    'Starter' => 0.02,
                    'Builder' => 0.03,
                    'Leader' => 0.05,
                    'Elite' => 0.07
                ]),
                'level3_referral_rates' => json_encode([
                    'Builder' => 0.01,
                    'Leader' => 0.02,
                    'Elite' => 0.03
                ]),
                'quarterly_bonus_pool_percentage' => 0.10,
                'minimum_investment_amount' => 500
            ];

            foreach ($defaultSettings as $key => $value) {
                SystemSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }

            Cache::forget($this->settingsCache);
            DB::commit();

            return response()->json([
                'message' => 'Settings reset to default values',
                'settings' => $this->getSettings()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
