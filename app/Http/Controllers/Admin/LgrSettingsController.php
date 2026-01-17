<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LgrSetting;
use App\Domain\LoyaltyReward\Entities\LgrCycle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LgrSettingsController extends Controller
{
    public function index(): Response
    {
        $settings = LgrSetting::getAllGrouped();
        
        // Get current tier rates from domain entity (defaults) and database overrides
        $tierRates = $this->getTierRates();
        
        return Inertia::render('Admin/LGR/Settings', [
            'settings' => $settings,
            'tierRates' => $tierRates,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'nullable|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
            'tierRates' => 'nullable|array',
            'tierRates.*.daily_rate' => 'nullable|numeric|min:0',
            'tierRates.*.max_earnings' => 'nullable|numeric|min:0',
            'tierRates.*.cycle_days' => 'nullable|integer|min:1|max:365',
        ]);

        try {
            // Update general settings
            if (!empty($validated['settings'])) {
                foreach ($validated['settings'] as $setting) {
                    LgrSetting::set($setting['key'], $setting['value']);
                }
            }
            
            // Update tier rates
            if (!empty($validated['tierRates'])) {
                foreach ($validated['tierRates'] as $tier => $rates) {
                    // Daily rate
                    if (isset($rates['daily_rate'])) {
                        $this->saveTierSetting($tier, 'daily_rate', $rates['daily_rate'], 'decimal');
                    }
                    
                    // Max earnings
                    if (isset($rates['max_earnings'])) {
                        $this->saveTierSetting($tier, 'max_earnings', $rates['max_earnings'], 'decimal');
                    }
                    
                    // Cycle days
                    if (isset($rates['cycle_days'])) {
                        $this->saveTierSetting($tier, 'cycle_days', $rates['cycle_days'], 'integer');
                    }
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
    
    /**
     * Get tier rates from domain constants with database overrides
     */
    private function getTierRates(): array
    {
        $tierRates = [
            'lite' => [
                'daily_rate' => LgrCycle::TIER_DAILY_RATES['lite'] ?? 12.50,
                'max_earnings' => LgrCycle::TIER_MAX_EARNINGS['lite'] ?? 875.00,
                'cycle_days' => 70,
            ],
            'basic' => [
                'daily_rate' => LgrCycle::TIER_DAILY_RATES['basic'] ?? 25.00,
                'max_earnings' => LgrCycle::TIER_MAX_EARNINGS['basic'] ?? 1750.00,
                'cycle_days' => 70,
            ],
            'growth_plus' => [
                'daily_rate' => LgrCycle::TIER_DAILY_RATES['growth_plus'] ?? 37.50,
                'max_earnings' => LgrCycle::TIER_MAX_EARNINGS['growth_plus'] ?? 2625.00,
                'cycle_days' => 70,
            ],
            'pro' => [
                'daily_rate' => LgrCycle::TIER_DAILY_RATES['pro'] ?? 62.50,
                'max_earnings' => LgrCycle::TIER_MAX_EARNINGS['pro'] ?? 4375.00,
                'cycle_days' => 70,
            ],
        ];
        
        // Check for database overrides
        foreach (['lite', 'basic', 'growth_plus', 'pro'] as $tier) {
            $dailyRate = LgrSetting::get("tier_{$tier}_daily_rate");
            if ($dailyRate !== null) {
                $tierRates[$tier]['daily_rate'] = (float) $dailyRate;
            }
            
            $maxEarnings = LgrSetting::get("tier_{$tier}_max_earnings");
            if ($maxEarnings !== null) {
                $tierRates[$tier]['max_earnings'] = (float) $maxEarnings;
            }
            
            $cycleDays = LgrSetting::get("tier_{$tier}_cycle_days");
            if ($cycleDays !== null) {
                $tierRates[$tier]['cycle_days'] = (int) $cycleDays;
            }
        }
        
        return $tierRates;
    }
    
    /**
     * Save a tier-specific setting
     */
    private function saveTierSetting(string $tier, string $field, $value, string $type): void
    {
        $key = "tier_{$tier}_{$field}";
        $label = ucfirst(str_replace('_', ' ', $tier)) . ' ' . ucfirst(str_replace('_', ' ', $field));
        
        $existing = LgrSetting::where('key', $key)->first();
        
        if ($existing) {
            $existing->update(['value' => (string) $value]);
        } else {
            LgrSetting::create([
                'key' => $key,
                'value' => (string) $value,
                'type' => $type,
                'group' => 'tier_rates',
                'label' => $label,
                'description' => "LGR {$field} for {$tier} tier members",
            ]);
        }
    }
}
