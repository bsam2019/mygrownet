<?php

namespace App\Listeners;

use App\Services\PointService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class AwardProductSalePoints implements ShouldQueue
{
    public function __construct(
        protected PointService $pointService
    ) {}

    /**
     * Handle the event when a user makes a product sale
     */
    public function handle($event): void
    {
        try {
            $sale = $event->sale;
            $user = $sale->user;
            $amount = $sale->amount;

            // Award points based on sale amount (10 LP/MAP per K100)
            $pointsPerHundred = 10;
            $lpAmount = (int) floor($amount / 100) * $pointsPerHundred;
            $mapAmount = $lpAmount;

            // Minimum 10 points for any sale
            $lpAmount = max($lpAmount, 10);
            $mapAmount = max($mapAmount, 10);

            $this->pointService->awardPoints(
                user: $user,
                source: 'product_sale',
                lpAmount: $lpAmount,
                mapAmount: $mapAmount,
                description: "Product sale: K{$amount}",
                reference: $sale
            );

            Log::info('Product sale points awarded', [
                'user_id' => $user->id,
                'sale_id' => $sale->id,
                'amount' => $amount,
                'points' => $lpAmount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to award product sale points', [
                'error' => $e->getMessage(),
                'sale_id' => $event->sale->id ?? null,
            ]);
        }
    }
}
