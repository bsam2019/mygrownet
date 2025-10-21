<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BonusPointSetting;
use App\Models\BPConversionRate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BonusPointSettingsController extends Controller
{
    public function index()
    {
        $bpSettings = BonusPointSetting::orderBy('name')->get();
        $currentRate = BPConversionRate::where('is_current', true)->first();
        $rateHistory = BPConversionRate::orderBy('effective_from', 'desc')->get();

        return Inertia::render('Admin/Settings/BonusPoints', [
            'bpSettings' => $bpSettings,
            'currentRate' => $currentRate,
            'rateHistory' => $rateHistory,
        ]);
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
}
