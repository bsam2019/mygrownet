<?php

namespace App\Services\Integration;

use App\Infrastructure\Persistence\Eloquent\CMS\ProductModel as CMSProduct;
use App\Models\MarketplaceProduct;
use App\Models\MarketplaceSeller;
use App\Events\CMS\ProductSynced;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GrowMarketIntegrationService
{
    /**
     * Sync CMS product to GrowMarket
     */
    public function syncProductToMarket(int $companyId, int $productId): array
    {
        try {
            DB::beginTransaction();

            // Get CMS product
            $cmsProduct = CMSProduct::where('company_id', $companyId)
                ->where('id', $productId)
                ->first();

            if (!$cmsProduct) {
                return ['success' => false, 'error' => 'Product not found'];
            }

            // Get or create seller for this company
            $seller = $this->getOrCreateSellerForCompany($companyId);

            if (!$seller) {
                return ['success' => false, 'error' => 'Could not create seller account'];
            }

            // Create or update marketplace product
            $marketplaceProduct = MarketplaceProduct::updateOrCreate(
                [
                    'seller_id' => $seller->id,
                    'bizboost_product_id' => $productId,
                ],
                [
                    'is_bizboost_synced' => true,
                    'category_id' => $this->mapCategory($cmsProduct->category?->name),
                    'name' => $cmsProduct->name,
                    'slug' => Str::slug($cmsProduct->name) . '-' . $productId,
                    'description' => $cmsProduct->description ?? 'No description available',
                    'price' => (int)($cmsProduct->selling_price * 100), // Convert to cents
                    'compare_price' => $cmsProduct->cost_price ? (int)($cmsProduct->cost_price * 100) : null,
                    'stock_quantity' => $cmsProduct->stock_quantity,
                    'images' => $cmsProduct->image_url ? [$cmsProduct->image_url] : [],
                    'status' => $cmsProduct->is_active ? 'active' : 'inactive',
                ]
            );

            // Mark CMS product as synced
            $cmsProduct->update(['sync_to_market' => true]);

            // Fire event
            event(new ProductSynced($cmsProduct, 'growmarket'));

            DB::commit();

            return [
                'success' => true,
                'marketplace_product_id' => $marketplaceProduct->id,
                'message' => 'Product synced to GrowMarket successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to sync product to GrowMarket', [
                'company_id' => $companyId,
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Bulk sync all CMS products to GrowMarket
     */
    public function bulkSyncProducts(int $companyId): array
    {
        $products = CMSProduct::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        $results = [
            'total' => $products->count(),
            'synced' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($products as $product) {
            $result = $this->syncProductToMarket($companyId, $product->id);
            
            if ($result['success']) {
                $results['synced']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'error' => $result['error'],
                ];
            }
        }

        return $results;
    }

    /**
     * Unsync product from GrowMarket
     */
    public function unsyncProduct(int $companyId, int $productId): array
    {
        try {
            // Find marketplace product
            $seller = $this->getSellerForCompany($companyId);
            
            if (!$seller) {
                return ['success' => false, 'error' => 'Seller not found'];
            }

            $marketplaceProduct = MarketplaceProduct::where('seller_id', $seller->id)
                ->where('bizboost_product_id', $productId)
                ->first();

            if ($marketplaceProduct) {
                $marketplaceProduct->update(['status' => 'inactive']);
            }

            // Update CMS product
            $cmsProduct = CMSProduct::where('company_id', $companyId)
                ->where('id', $productId)
                ->first();

            if ($cmsProduct) {
                $cmsProduct->update(['sync_to_market' => false]);
            }

            return [
                'success' => true,
                'message' => 'Product unsynced from GrowMarket',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to unsync product from GrowMarket', [
                'company_id' => $companyId,
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get or create seller for company
     */
    private function getOrCreateSellerForCompany(int $companyId): ?MarketplaceSeller
    {
        // Get company
        $company = \App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel::find($companyId);
        
        if (!$company) {
            return null;
        }

        // Get user from company
        $cmsUser = \App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel::where('company_id', $companyId)
            ->where('role', 'owner')
            ->first();

        if (!$cmsUser) {
            return null;
        }

        // Get or create seller
        $seller = MarketplaceSeller::firstOrCreate(
            [
                'user_id' => $cmsUser->user_id,
                'bizboost_business_id' => $companyId,
            ],
            [
                'is_bizboost_synced' => true,
                'business_name' => $company->name,
                'business_type' => 'retail',
                'province' => $company->province ?? 'Lusaka',
                'district' => $company->district ?? 'Lusaka',
                'phone' => $company->phone,
                'email' => $company->email,
                'description' => $company->description ?? 'Business powered by CMS',
                'trust_level' => 'verified',
                'kyc_status' => 'approved',
                'is_active' => true,
            ]
        );

        return $seller;
    }

    /**
     * Get seller for company
     */
    private function getSellerForCompany(int $companyId): ?MarketplaceSeller
    {
        return MarketplaceSeller::where('bizboost_business_id', $companyId)
            ->where('is_bizboost_synced', true)
            ->first();
    }

    /**
     * Map CMS category to marketplace category
     */
    private function mapCategory(?string $categoryName): ?int
    {
        if (!$categoryName) {
            return null;
        }

        $category = \App\Models\MarketplaceCategory::where('name', $categoryName)->first();
        
        if (!$category) {
            // Try to find similar category
            $category = \App\Models\MarketplaceCategory::where('name', 'like', "%{$categoryName}%")->first();
        }

        return $category?->id;
    }

    /**
     * Get sync status for company
     */
    public function getSyncStatus(int $companyId): array
    {
        $totalProducts = CMSProduct::where('company_id', $companyId)->count();
        $syncedProducts = CMSProduct::where('company_id', $companyId)
            ->where('sync_to_market', true)
            ->count();

        $seller = $this->getSellerForCompany($companyId);
        $marketplaceProducts = $seller ? $seller->products()->count() : 0;

        return [
            'total_cms_products' => $totalProducts,
            'synced_products' => $syncedProducts,
            'marketplace_products' => $marketplaceProducts,
            'has_seller_account' => $seller !== null,
            'seller_id' => $seller?->id,
        ];
    }
}
