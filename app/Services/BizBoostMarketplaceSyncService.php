<?php

namespace App\Services;

use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostProductModel;
use App\Models\MarketplaceSeller;
use App\Models\MarketplaceProduct;
use App\Models\MarketplaceCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BizBoostMarketplaceSyncService
{
    /**
     * Category mapping from BizBoost to Marketplace
     */
    private array $categoryMapping = [
        'Food & Beverage' => 'Food & Drinks',
        'Fashion & Apparel' => 'Fashion',
        'Electronics' => 'Electronics',
        'Health & Beauty' => 'Health & Beauty',
        'Home & Garden' => 'Home & Living',
        'Sports & Fitness' => 'Sports & Outdoors',
        'Books & Media' => 'Books & Media',
        'Toys & Games' => 'Toys & Kids',
        'Automotive' => 'Automotive',
        'Services' => 'Services',
    ];

    /**
     * Create or get marketplace seller from BizBoost business
     */
    public function getOrCreateSeller(BizBoostBusinessModel $business): MarketplaceSeller
    {
        // Check if already synced
        if ($business->marketplace_seller_id) {
            $seller = MarketplaceSeller::find($business->marketplace_seller_id);
            if ($seller) {
                return $seller;
            }
        }

        // Create new marketplace seller
        return $this->createMarketplaceSeller($business);
    }

    /**
     * Create marketplace seller from BizBoost business
     */
    public function createMarketplaceSeller(BizBoostBusinessModel $business): MarketplaceSeller
    {
        Log::info('Creating marketplace seller from BizBoost business', [
            'business_id' => $business->id,
            'business_name' => $business->name,
        ]);

        $seller = MarketplaceSeller::create([
            'user_id' => $business->user_id,
            'bizboost_business_id' => $business->id,
            'is_bizboost_synced' => true,
            'business_name' => $business->name,
            'business_type' => $this->mapBusinessType($business->industry),
            'province' => $business->province ?? 'Lusaka',
            'district' => $business->city ?? 'Lusaka',
            'phone' => $business->phone,
            'email' => $business->email,
            'description' => $business->description,
            'logo_path' => $business->logo_path,
            'trust_level' => 'verified', // BizBoost businesses start as verified
            'kyc_status' => 'approved', // Auto-approve BizBoost businesses
            'is_active' => true,
        ]);

        // Link back to BizBoost
        $business->update([
            'marketplace_seller_id' => $seller->id,
            'marketplace_synced_at' => now(),
        ]);

        Log::info('Marketplace seller created', [
            'seller_id' => $seller->id,
            'business_id' => $business->id,
        ]);

        return $seller;
    }

    /**
     * Sync all products from BizBoost business to Marketplace
     */
    public function syncProducts(BizBoostBusinessModel $business): array
    {
        if (!$business->marketplace_sync_enabled) {
            return [
                'success' => false,
                'message' => 'Marketplace sync is disabled for this business',
                'synced' => 0,
            ];
        }

        $seller = $this->getOrCreateSeller($business);
        $syncedCount = 0;
        $errors = [];

        $products = $business->products()
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->get();

        foreach ($products as $bizboostProduct) {
            try {
                $this->syncProduct($bizboostProduct, $seller);
                $syncedCount++;
            } catch (\Exception $e) {
                Log::error('Failed to sync product', [
                    'product_id' => $bizboostProduct->id,
                    'error' => $e->getMessage(),
                ]);
                $errors[] = [
                    'product_id' => $bizboostProduct->id,
                    'product_name' => $bizboostProduct->name,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $business->update(['marketplace_synced_at' => now()]);

        Log::info('Products synced', [
            'business_id' => $business->id,
            'synced_count' => $syncedCount,
            'total_products' => $products->count(),
            'errors' => count($errors),
        ]);

        return [
            'success' => true,
            'synced' => $syncedCount,
            'total' => $products->count(),
            'errors' => $errors,
        ];
    }

    /**
     * Sync single product
     */
    public function syncProduct(BizBoostProductModel $bizboostProduct, ?MarketplaceSeller $seller = null): MarketplaceProduct
    {
        if (!$seller) {
            $seller = $this->getOrCreateSeller($bizboostProduct->business);
        }

        // Check if already synced
        $marketplaceProduct = MarketplaceProduct::where('bizboost_product_id', $bizboostProduct->id)->first();

        $productData = [
            'seller_id' => $seller->id,
            'bizboost_product_id' => $bizboostProduct->id,
            'is_bizboost_synced' => true,
            'category_id' => $this->getMarketplaceCategory($bizboostProduct),
            'name' => $bizboostProduct->name,
            'slug' => $bizboostProduct->slug ?? Str::slug($bizboostProduct->name),
            'description' => $bizboostProduct->description,
            'price' => $bizboostProduct->price,
            'compare_price' => $bizboostProduct->compare_price,
            'stock_quantity' => $bizboostProduct->stock_quantity,
            'images' => $this->processImages($bizboostProduct->images),
            'status' => 'active', // Auto-approve BizBoost products
            'is_featured' => false,
        ];

        if ($marketplaceProduct) {
            // Update existing
            $marketplaceProduct->update($productData);
            Log::info('Product updated in marketplace', [
                'marketplace_product_id' => $marketplaceProduct->id,
                'bizboost_product_id' => $bizboostProduct->id,
            ]);
        } else {
            // Create new
            $marketplaceProduct = MarketplaceProduct::create($productData);
            Log::info('Product created in marketplace', [
                'marketplace_product_id' => $marketplaceProduct->id,
                'bizboost_product_id' => $bizboostProduct->id,
            ]);
        }

        return $marketplaceProduct;
    }

    /**
     * Handle product deletion
     */
    public function handleProductDeleted(BizBoostProductModel $product): void
    {
        $marketplaceProduct = MarketplaceProduct::where('bizboost_product_id', $product->id)->first();

        if ($marketplaceProduct) {
            $marketplaceProduct->delete();
            Log::info('Product deleted from marketplace', [
                'marketplace_product_id' => $marketplaceProduct->id,
                'bizboost_product_id' => $product->id,
            ]);
        }
    }

    /**
     * Handle product stock update
     */
    public function syncProductStock(BizBoostProductModel $product): void
    {
        $marketplaceProduct = MarketplaceProduct::where('bizboost_product_id', $product->id)->first();

        if ($marketplaceProduct) {
            $marketplaceProduct->update([
                'stock_quantity' => $product->stock_quantity,
            ]);
            Log::info('Product stock synced', [
                'marketplace_product_id' => $marketplaceProduct->id,
                'stock_quantity' => $product->stock_quantity,
            ]);
        }
    }

    /**
     * Disable marketplace sync for business
     */
    public function disableSync(BizBoostBusinessModel $business): void
    {
        $business->update(['marketplace_sync_enabled' => false]);

        // Optionally deactivate products (don't delete)
        if ($business->marketplace_seller_id) {
            MarketplaceProduct::where('seller_id', $business->marketplace_seller_id)
                ->where('is_bizboost_synced', true)
                ->update(['status' => 'inactive']);
        }

        Log::info('Marketplace sync disabled', ['business_id' => $business->id]);
    }

    /**
     * Enable marketplace sync for business
     */
    public function enableSync(BizBoostBusinessModel $business): void
    {
        $business->update(['marketplace_sync_enabled' => true]);

        // Reactivate products
        if ($business->marketplace_seller_id) {
            MarketplaceProduct::where('seller_id', $business->marketplace_seller_id)
                ->where('is_bizboost_synced', true)
                ->update(['status' => 'active']);
        }

        // Sync products
        $this->syncProducts($business);

        Log::info('Marketplace sync enabled', ['business_id' => $business->id]);
    }

    /**
     * Get or create marketplace category
     */
    private function getMarketplaceCategory(BizBoostProductModel $product): ?int
    {
        // Try to get category from product or business industry
        $categoryName = $product->category ?? $product->business->industry ?? null;

        if (!$categoryName) {
            // Default to "Other" category
            $category = MarketplaceCategory::where('name', 'Other')->first();
            return $category?->id;
        }

        // Map BizBoost category to marketplace category
        $mappedName = $this->categoryMapping[$categoryName] ?? $categoryName;

        // Try to find exact match
        $category = MarketplaceCategory::where('name', $mappedName)->first();

        if (!$category) {
            // Try case-insensitive match
            $category = MarketplaceCategory::whereRaw('LOWER(name) = ?', [strtolower($mappedName)])->first();
        }

        if (!$category) {
            // Try to find "Other" category as fallback
            $category = MarketplaceCategory::where('name', 'Other')->first();
            
            // If still no category, create "Other" category
            if (!$category) {
                $category = MarketplaceCategory::create([
                    'name' => 'Other',
                    'slug' => 'other',
                    'description' => 'Other products',
                    'is_active' => true,
                ]);
            }
        }

        return $category?->id;
    }

    /**
     * Map business type
     */
    private function mapBusinessType(string $industry): string
    {
        // Marketplace only has 'individual' and 'registered'
        // BizBoost businesses are typically registered businesses
        return 'registered';
    }

    /**
     * Process images for marketplace
     */
    private function processImages($images): array
    {
        if (empty($images)) {
            return [];
        }

        // Handle Collection
        if ($images instanceof \Illuminate\Support\Collection) {
            $images = $images->toArray();
        }

        // Handle JSON string
        if (is_string($images)) {
            $images = json_decode($images, true) ?? [];
        }

        // Images are already stored, just return them
        return array_values($images);
    }

    /**
     * Check if business should be synced
     */
    public function shouldSync(BizBoostBusinessModel $business): bool
    {
        // Must be active
        if (!$business->is_active) {
            return false;
        }

        // Must have sync enabled
        if (!$business->marketplace_sync_enabled) {
            return false;
        }

        // Must have products
        if ($business->products()->where('is_active', true)->count() === 0) {
            return false;
        }

        return true;
    }

    /**
     * Get sync status for business
     */
    public function getSyncStatus(BizBoostBusinessModel $business): array
    {
        $seller = $business->marketplaceSeller;

        return [
            'is_synced' => (bool) $business->marketplace_seller_id,
            'sync_enabled' => $business->marketplace_sync_enabled,
            'last_synced_at' => $business->marketplace_synced_at?->format('Y-m-d H:i:s'),
            'seller_id' => $seller?->id,
            'marketplace_products_count' => $seller ? MarketplaceProduct::where('seller_id', $seller->id)
                ->where('is_bizboost_synced', true)
                ->count() : 0,
            'bizboost_products_count' => $business->products()->where('is_active', true)->count(),
        ];
    }
}
