<?php

namespace App\Listeners\CMS;

use App\Events\CMS\InventoryUpdated;
use App\Models\MarketplaceProduct;
use Illuminate\Support\Facades\Log;

class SyncInventoryToGrowMarket
{
    /**
     * Handle the event
     */
    public function handle(InventoryUpdated $event): void
    {
        try {
            // Check if product is synced to GrowMarket
            $product = \App\Infrastructure\Persistence\Eloquent\CMS\ProductModel::find($event->productId);
            
            if (!$product || !($product->sync_to_market ?? false)) {
                return;
            }

            // Update GrowMarket listing using the existing MarketplaceProduct model
            MarketplaceProduct::where('bizboost_product_id', $event->productId)
                ->where('is_bizboost_synced', true)
                ->update([
                    'stock_quantity' => $event->newQuantity,
                    'updated_at' => now(),
                ]);

            Log::info('Inventory synced to GrowMarket', [
                'product_id' => $event->productId,
                'new_quantity' => $event->newQuantity,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to sync inventory to GrowMarket', [
                'product_id' => $event->productId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
