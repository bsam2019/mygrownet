<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitTierConfig;
use Illuminate\Http\JsonResponse;

class PresentationDataController extends Controller
{
    /**
     * Get dynamic data for the GrowNet presentation
     */
    public function index(): JsonResponse
    {
        // Fetch active starter kit tiers
        $tiers = StarterKitTierConfig::active()
            ->ordered()
            ->get()
            ->map(function ($tier) {
                return [
                    'key' => $tier->tier_key,
                    'name' => $tier->tier_name,
                    'price' => (int) $tier->price,
                    'storage_gb' => $tier->storage_gb,
                    'earning_potential' => $tier->earning_potential_percentage,
                    'description' => $tier->description,
                ];
            });

        // Commission rates by level
        $commissionRates = [
            ['level' => 1, 'name' => 'Associate', 'rate' => 15, 'positions' => 3],
            ['level' => 2, 'name' => 'Professional', 'rate' => 10, 'positions' => 9],
            ['level' => 3, 'name' => 'Senior', 'rate' => 8, 'positions' => 27],
            ['level' => 4, 'name' => 'Manager', 'rate' => 6, 'positions' => 81],
            ['level' => 5, 'name' => 'Director', 'rate' => 4, 'positions' => 243],
            ['level' => 6, 'name' => 'Executive', 'rate' => 3, 'positions' => 729],
            ['level' => 7, 'name' => 'Ambassador', 'rate' => 2, 'positions' => 2187],
        ];

        // Performance bonus tiers
        $performanceTiers = [
            ['name' => 'Bronze', 'points' => 500, 'bonus' => 10],
            ['name' => 'Silver', 'points' => 1000, 'bonus' => 15],
            ['name' => 'Gold', 'points' => 2000, 'bonus' => 20],
            ['name' => 'Platinum', 'points' => 3500, 'bonus' => 30],
        ];

        // Matrix system data
        $matrixData = [
            'width' => 3,
            'depth' => 7,
            'total_capacity' => 3279,
        ];

        // Referral bonus
        $referralBonus = [
            'rate' => 15,
            'commission_base' => 50, // 50% of starter kit price
        ];

        return response()->json([
            'tiers' => $tiers,
            'commission_rates' => $commissionRates,
            'performance_tiers' => $performanceTiers,
            'matrix' => $matrixData,
            'referral_bonus' => $referralBonus,
        ]);
    }
}
