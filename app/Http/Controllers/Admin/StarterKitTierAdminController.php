<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitTierConfig;
use App\Infrastructure\Persistence\Eloquent\Benefit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StarterKitTierAdminController extends Controller
{
    /**
     * Display list of all tier configurations
     */
    public function index()
    {
        $tiers = StarterKitTierConfig::ordered()
            ->with(['benefits' => function ($query) {
                $query->orderBy('sort_order');
            }])
            ->get()
            ->map(function ($tier) {
                return [
                    'id' => $tier->id,
                    'tier_key' => $tier->tier_key,
                    'tier_name' => $tier->tier_name,
                    'description' => $tier->description,
                    'price' => $tier->price,
                    'storage_gb' => $tier->storage_gb,
                    'earning_potential_percentage' => $tier->earning_potential_percentage,
                    'sort_order' => $tier->sort_order,
                    'is_active' => $tier->is_active,
                    'benefits_count' => $tier->benefits->count(),
                ];
            });

        return Inertia::render('Admin/StarterKitTiers/Index', [
            'tiers' => $tiers,
        ]);
    }

    /**
     * Show edit form for tier
     */
    public function edit(int $id)
    {
        $tier = StarterKitTierConfig::with('benefits')->findOrFail($id);

        // Get all available benefits
        $allBenefits = Benefit::active()
            ->ordered()
            ->get()
            ->groupBy('benefit_type')
            ->map(function ($benefits) {
                return $benefits->map(function ($benefit) {
                    return [
                        'id' => $benefit->id,
                        'name' => $benefit->name,
                        'slug' => $benefit->slug,
                        'category' => $benefit->category,
                        'benefit_type' => $benefit->benefit_type,
                        'description' => $benefit->description,
                        'icon' => $benefit->icon,
                        'unit' => $benefit->unit,
                    ];
                });
            });

        // Get currently included benefits with pivot data
        $includedBenefits = $tier->benefits->mapWithKeys(function ($benefit) {
            return [
                $benefit->id => [
                    'is_included' => $benefit->pivot->is_included,
                    'limit_value' => $benefit->pivot->limit_value,
                ]
            ];
        });

        return Inertia::render('Admin/StarterKitTiers/Edit', [
            'tier' => [
                'id' => $tier->id,
                'tier_key' => $tier->tier_key,
                'tier_name' => $tier->tier_name,
                'description' => $tier->description,
                'price' => $tier->price,
                'storage_gb' => $tier->storage_gb,
                'earning_potential_percentage' => $tier->earning_potential_percentage,
                'sort_order' => $tier->sort_order,
                'is_active' => $tier->is_active,
            ],
            'all_benefits' => $allBenefits,
            'included_benefits' => $includedBenefits,
        ]);
    }

    /**
     * Update tier configuration
     */
    public function update(Request $request, int $id)
    {
        $tier = StarterKitTierConfig::findOrFail($id);

        $validated = $request->validate([
            'tier_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'storage_gb' => 'required|integer|min:0',
            'earning_potential_percentage' => 'required|numeric|min:0|max:100',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $tier->update($validated);

        return redirect()
            ->route('admin.starter-kit-tiers.index')
            ->with('success', 'Tier configuration updated successfully');
    }

    /**
     * Update tier benefits
     */
    public function updateBenefits(Request $request, int $id)
    {
        $tier = StarterKitTierConfig::findOrFail($id);

        $validated = $request->validate([
            'benefits' => 'required|array',
            'benefits.*.benefit_id' => 'required|exists:benefits,id',
            'benefits.*.is_included' => 'required|boolean',
            'benefits.*.limit_value' => 'nullable|integer|min:0',
        ]);

        $benefitsToSync = [];
        foreach ($validated['benefits'] as $benefitData) {
            if ($benefitData['is_included']) {
                $benefitsToSync[$benefitData['benefit_id']] = [
                    'is_included' => true,
                    'limit_value' => $benefitData['limit_value'] ?? null,
                ];
            }
        }

        $tier->benefits()->sync($benefitsToSync);

        return back()->with('success', 'Tier benefits updated successfully');
    }
}
