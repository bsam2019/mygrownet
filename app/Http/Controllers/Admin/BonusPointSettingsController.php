<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LifePointSetting;
use App\Models\BonusPointSetting;
use App\Models\BPConversionRate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BonusPointSettingsController extends Controller
{
    public function index()
    {
        $lpSettings = LifePointSetting::orderBy('name')->get();
        $bpSettings = BonusPointSetting::orderBy('name')->get();
        $currentRate = BPConversionRate::where('is_current', true)->first();
        $rateHistory = BPConversionRate::orderBy('effective_from', 'desc')->get();

        // Get level requirements from packages
        $levelRequirements = \App\Models\Package::whereIn('slug', [
            'associate-monthly',
            'professional-monthly',
            'senior-monthly',
            'manager-monthly',
            'director-monthly',
            'executive-monthly',
            'ambassador-monthly'
        ])
        ->orderBy('sort_order')
        ->get()
        ->map(function ($package, $index) {
            return [
                'id' => $package->id,
                'level' => $index + 1,
                'name' => str_replace(' Membership', '', $package->name),
                'subscription_price' => $package->price,
                'lp_requirement' => $package->lp_requirement ?? ($index * 500), // Default progression
            ];
        });

        return Inertia::render('Admin/Settings/BonusPoints', [
            'lpSettings' => $lpSettings,
            'bpSettings' => $bpSettings,
            'currentRate' => $currentRate,
            'rateHistory' => $rateHistory,
            'levelRequirements' => $levelRequirements,
        ]);
    }

    public function updateLPActivity(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:life_point_settings,id',
            'lp_value' => 'required|integer|min:0',
        ]);

        $setting = LifePointSetting::findOrFail($request->id);
        $setting->update(['lp_value' => $request->lp_value]);

        return back()->with('success', 'LP value updated successfully');
    }

    public function toggleLPActivity(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:life_point_settings,id',
            'is_active' => 'required|boolean',
        ]);

        $setting = LifePointSetting::findOrFail($request->id);
        $setting->update(['is_active' => $request->is_active]);

        return back()->with('success', 'LP activity status updated');
    }

    public function updateActivity(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:bonus_point_settings,id',
            'bp_value' => 'required|integer|min:0',
        ]);

        $setting = BonusPointSetting::findOrFail($request->id);
        $setting->update(['bp_value' => $request->bp_value]);

        return back()->with('success', 'BP value updated successfully');
    }

    public function toggleActivity(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:bonus_point_settings,id',
            'is_active' => 'required|boolean',
        ]);

        $setting = BonusPointSetting::findOrFail($request->id);
        $setting->update(['is_active' => $request->is_active]);

        return back()->with('success', 'Activity status updated');
    }

    public function updateRate(Request $request)
    {
        $request->validate([
            'rate' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        BPConversionRate::setNewRate(
            rate: $request->rate,
            notes: $request->notes
        );

        return back()->with('success', 'Conversion rate updated successfully');
    }

    public function updateLevelRequirement(Request $request)
    {
        $request->validate([
            'level' => 'required|integer|min:1|max:7',
            'lp_requirement' => 'required|integer|min:0',
        ]);

        $slugs = [
            1 => 'associate-monthly',
            2 => 'professional-monthly',
            3 => 'senior-monthly',
            4 => 'manager-monthly',
            5 => 'director-monthly',
            6 => 'executive-monthly',
            7 => 'ambassador-monthly',
        ];

        $package = \App\Models\Package::where('slug', $slugs[$request->level])->first();
        
        if ($package) {
            $package->update(['lp_requirement' => $request->lp_requirement]);
        }

        return back()->with('success', 'Level requirement updated successfully');
    }
}
