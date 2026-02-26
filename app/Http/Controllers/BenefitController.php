<?php

namespace App\Http\Controllers;

use App\Infrastructure\Persistence\Eloquent\Benefit;
use Illuminate\Http\Request;

class BenefitController extends Controller
{
    public function index()
    {
        $benefits = Benefit::active()
            ->ordered()
            ->get()
            ->groupBy('category');

        return response()->json([
            'benefits' => $benefits,
            'categories' => ['apps', 'cloud', 'learning', 'media', 'resources'],
        ]);
    }

    public function myBenefits(Request $request)
    {
        $user = $request->user();
        
        // Get all active benefits
        $allBenefits = Benefit::active()
            ->ordered()
            ->get();

        // Check if user has starter kit
        $hasStarterKit = $user->has_starter_kit;
        $starterKitPurchase = $user->starterKitPurchases()->latest()->first();
        $userTier = $starterKitPurchase?->tier ?? null;

        $benefitsWithStatus = $allBenefits->map(function ($benefit) use ($hasStarterKit, $starterKitPurchase, $userTier) {
            // Determine status
            if ($benefit->is_coming_soon) {
                $status = 'coming_soon';
            } elseif (!$hasStarterKit) {
                $status = 'locked';
            } else {
                // For starter kit benefits, check if included in user's kit
                if ($benefit->benefit_type === 'starter_kit') {
                    $isIncluded = $starterKitPurchase
                        ? $starterKitPurchase->benefits()
                            ->where('benefit_id', $benefit->id)
                            ->wherePivot('included', true)
                            ->exists()
                        : false;

                    $status = $isIncluded ? 'active' : 'not_active';
                } 
                // For monthly services, check if user has active subscription
                elseif ($benefit->benefit_type === 'monthly_service') {
                    // TODO: Check user's active subscriptions
                    $status = 'available';
                }
                // For physical items, check fulfillment status
                elseif ($benefit->benefit_type === 'physical_item') {
                    $pivot = $starterKitPurchase
                        ? $starterKitPurchase->benefits()
                            ->where('benefit_id', $benefit->id)
                            ->wherePivot('included', true)
                            ->first()?->pivot
                        : null;

                    if ($pivot) {
                        $status = match($pivot->fulfillment_status) {
                            'pending' => 'pending',
                            'issued' => 'issued',
                            'delivered' => 'delivered',
                            default => 'active'
                        };
                    } else {
                        $status = 'not_active';
                    }
                } else {
                    $status = 'not_active';
                }
            }

            // Get tier-specific allocation if applicable
            $allocation = null;
            if ($benefit->tier_allocations && $userTier) {
                $allocation = $benefit->tier_allocations[$userTier] ?? null;
            }

            return [
                'id' => $benefit->id,
                'name' => $benefit->name,
                'slug' => $benefit->slug,
                'category' => $benefit->category,
                'benefit_type' => $benefit->benefit_type,
                'description' => $benefit->description,
                'icon' => $benefit->icon,
                'unit' => $benefit->unit,
                'allocation' => $allocation,
                'is_coming_soon' => $benefit->is_coming_soon,
                'status' => $status,
            ];
        });

        // Group by benefit type for better organization
        $starterKitBenefits = $benefitsWithStatus->where('benefit_type', 'starter_kit')->groupBy('category');
        $monthlyBenefits = $benefitsWithStatus->where('benefit_type', 'monthly_service')->groupBy('category');
        $physicalItems = $benefitsWithStatus->where('benefit_type', 'physical_item')->values();

        return response()->json([
            'starter_kit_benefits' => $starterKitBenefits,
            'monthly_benefits' => $monthlyBenefits,
            'physical_items' => $physicalItems,
            'has_starter_kit' => $hasStarterKit,
            'user_tier' => $userTier,
        ]);
    }
}
