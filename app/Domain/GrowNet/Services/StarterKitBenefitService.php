<?php

namespace App\Domain\GrowNet\Services;

use App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitPurchaseModel;
use App\Infrastructure\Persistence\Eloquent\Benefit;
use Illuminate\Support\Facades\Log;

class StarterKitBenefitService
{
    public function assignBenefitsToKit(StarterKitPurchaseModel $purchase): void
    {
        $benefits = Benefit::active()
            ->starterKitBenefits()
            ->ordered()
            ->get();

        foreach ($benefits as $benefit) {
            $tierAllocations = $benefit->tier_allocations ?? [];

            if (empty($tierAllocations) || in_array($purchase->tier, $tierAllocations)) {
                $purchase->benefits()->attach($benefit->id, [
                    'included' => true,
                    'limit_value' => null,
                ]);
            }
        }

        Log::info('Benefits assigned to starter kit', [
            'purchase_id' => $purchase->id,
            'tier' => $purchase->tier,
            'benefits_count' => $benefits->count(),
        ]);
    }
}
