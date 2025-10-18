<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvestmentTier;
use App\Models\TierSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InvestmentTierController extends Controller
{
    public function index()
    {
        $tiers = InvestmentTier::with('settings')
            ->ordered()
            ->get();

        return Inertia::render('Admin/InvestmentTiers/Index', [
            'tiers' => $tiers
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/InvestmentTiers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:investment_tiers',
            'minimum_investment' => 'required|numeric|min:0',
            'fixed_profit_rate' => 'required|numeric|min:0|max:100',
            'direct_referral_rate' => 'required|numeric|min:0|max:100',
            'level2_referral_rate' => 'nullable|numeric|min:0|max:100',
            'level3_referral_rate' => 'nullable|numeric|min:0|max:100',
            'benefits' => 'required|array',
            'description' => 'required|string',
            'order' => 'required|integer|min:0',
            'settings' => 'required|array',
            'settings.early_withdrawal_penalty' => 'required|numeric|min:0|max:100',
            'settings.partial_withdrawal_limit' => 'required|numeric|min:0|max:100',
            'settings.minimum_lock_in_period' => 'required|integer|min:1',
            'settings.performance_bonus_rate' => 'nullable|numeric|min:0|max:100',
            'settings.requires_kyc' => 'required|boolean',
            'settings.requires_approval' => 'required|boolean',
        ]);


        try {
            DB::beginTransaction();

            $tier = InvestmentTier::create([
                'name' => $validated['name'],
                'minimum_investment' => $validated['minimum_investment'],
                'fixed_profit_rate' => $validated['fixed_profit_rate'],
                'direct_referral_rate' => $validated['direct_referral_rate'],
                'level2_referral_rate' => $validated['level2_referral_rate'],
                'level3_referral_rate' => $validated['level3_referral_rate'],
                'benefits' => $validated['benefits'],
                'description' => $validated['description'],
                'order' => $validated['order'],
                'is_active' => true,
            ]);

            TierSetting::create([
                'investment_tier_id' => $tier->id,
                'early_withdrawal_penalty' => $validated['settings']['early_withdrawal_penalty'],
                'partial_withdrawal_limit' => $validated['settings']['partial_withdrawal_limit'],
                'minimum_lock_in_period' => $validated['settings']['minimum_lock_in_period'],
                'performance_bonus_rate' => $validated['settings']['performance_bonus_rate'],
                'requires_kyc' => $validated['settings']['requires_kyc'],
                'requires_approval' => $validated['settings']['requires_approval'],
            ]);

            DB::commit();

            return redirect()->route('admin.investment-tiers.index')
                ->with('success', 'Investment tier created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the error for debugging
            \Log::error('Investment tier creation failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to create investment tier: ' . $e->getMessage());
        }
    }

    public function edit(InvestmentTier $tier)
    {
        $tier->load('settings');
        return Inertia::render('Admin/InvestmentTiers/Edit', [
            'tier' => $tier
        ]);
    }

    public function update(Request $request, InvestmentTier $tier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:investment_tiers,name,' . $tier->id,
            'minimum_investment' => 'required|numeric|min:0',
            'fixed_profit_rate' => 'required|numeric|min:0|max:100',
            'direct_referral_rate' => 'required|numeric|min:0|max:100',
            'level2_referral_rate' => 'nullable|numeric|min:0|max:100',
            'level3_referral_rate' => 'nullable|numeric|min:0|max:100',
            'benefits' => 'required|array',
            'description' => 'required|string',
            'order' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
            'settings' => 'required|array',
            'settings.early_withdrawal_penalty' => 'required|numeric|min:0|max:100',
            'settings.partial_withdrawal_limit' => 'required|numeric|min:0|max:100',
            'settings.minimum_lock_in_period' => 'required|integer|min:1',
            'settings.performance_bonus_rate' => 'nullable|numeric|min:0|max:100',
            'settings.requires_kyc' => 'required|boolean',
            'settings.requires_approval' => 'required|boolean',
        ]);

        try {
            DB::beginTransaction();

            $tier->update([
                'name' => $validated['name'],
                'minimum_investment' => $validated['minimum_investment'],
                'fixed_profit_rate' => $validated['fixed_profit_rate'],
                'direct_referral_rate' => $validated['direct_referral_rate'],
                'level2_referral_rate' => $validated['level2_referral_rate'],
                'level3_referral_rate' => $validated['level3_referral_rate'],
                'benefits' => $validated['benefits'],
                'description' => $validated['description'],
                'order' => $validated['order'],
                'is_active' => $validated['is_active'],
            ]);

            $tier->settings()->update([
                'early_withdrawal_penalty' => $validated['settings']['early_withdrawal_penalty'],
                'partial_withdrawal_limit' => $validated['settings']['partial_withdrawal_limit'],
                'minimum_lock_in_period' => $validated['settings']['minimum_lock_in_period'],
                'performance_bonus_rate' => $validated['settings']['performance_bonus_rate'],
                'requires_kyc' => $validated['settings']['requires_kyc'],
                'requires_approval' => $validated['settings']['requires_approval'],
            ]);

            DB::commit();

            return redirect()->route('admin.investment-tiers.index')
                ->with('success', 'Investment tier updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update investment tier.');
        }
    }

    public function destroy(InvestmentTier $tier)
    {
        try {
            DB::beginTransaction();

            // Check if tier has any users
            if ($tier->users()->exists()) {
                return back()->with('error', 'Cannot delete tier with active users.');
            }

            $tier->settings()->delete();
            $tier->delete();

            DB::commit();

            return redirect()->route('admin.investment-tiers.index')
                ->with('success', 'Investment tier deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete investment tier.');
        }
    }

    public function toggleStatus(InvestmentTier $tier)
    {
        try {
            $tier->update(['is_active' => !$tier->is_active]);
            return back()->with('success', 'Investment tier status updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update investment tier status.');
        }
    }

    /**
     * Toggle the archive status of an investment tier.
     *
     * @param  \App\Models\InvestmentTier  $tier
     * @return \Illuminate\Http\Response
     */
    public function toggleArchive(InvestmentTier $tier)
    {
        $tier->update([
            'is_archived' => !$tier->is_archived
        ]);
        
        return redirect()->back()->with('success', 'Investment tier archive status updated successfully');
    }
} 