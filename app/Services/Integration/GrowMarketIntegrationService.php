<?php

namespace App\Services\Integration;

use App\Domain\BMS\Core\Services\BmsDataService;
use App\Events\BMS\ProductSynced;
use App\Models\Marketplace\MarketplaceProduct;
use App\Models\Marketplace\MarketplaceSeller;
use App\Models\Marketplace\MarketplaceCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GrowMarketIntegrationService
{
    public function __construct(
        private BmsDataService $bmsData,
    ) {}

    public function syncProductToMarket(int $companyId, int $productId): array
    {
        try {
            DB::beginTransaction();

            $cmsProduct = $this->bmsData->getProduct($companyId, $productId);

            if (!$cmsProduct) {
                return ['success' => false, 'error' => 'Product not found'];
            }

            $seller = $this->getOrCreateSellerForCompany($companyId);

            if (!$seller) {
                return ['success' => false, 'error' => 'Could not create seller account'];
            }

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
                    'price' => (int)($cmsProduct->selling_price * 100),
                    'compare_price' => $cmsProduct->cost_price ? (int)($cmsProduct->cost_price * 100) : null,
                    'stock_quantity' => $cmsProduct->stock_quantity,
                    'images' => $cmsProduct->image_url ? [$cmsProduct->image_url] : [],
                    'status' => $cmsProduct->is_active ? 'active' : 'inactive',
                ]
            );

            $cmsProduct->update(['sync_to_market' => true]);

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

    public function bulkSyncProducts(int $companyId): array
    {
        $products = $this->bmsData->getProducts($companyId)->where('is_active', true);

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

    public function unsyncProduct(int $companyId, int $productId): array
    {
        try {
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

            $cmsProduct = $this->bmsData->getProduct($companyId, $productId);

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

    private function getOrCreateSellerForCompany(int $companyId): ?MarketplaceSeller
    {
        $company = $this->bmsData->findCompany($companyId);

        if (!$company) {
            return null;
        }

        $cmsUser = $this->bmsData->findCompanyOwner($companyId);

        if (!$cmsUser) {
            return null;
        }

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

    private function getSellerForCompany(int $companyId): ?MarketplaceSeller
    {
        return MarketplaceSeller::where('bizboost_business_id', $companyId)
            ->where('is_bizboost_synced', true)
            ->first();
    }

    private function mapCategory(?string $categoryName): ?int
    {
        if (!$categoryName) {
            return null;
        }

        $category = MarketplaceCategory::where('name', $categoryName)->first();

        if (!$category) {
            $category = MarketplaceCategory::where('name', 'like', "%{$categoryName}%")->first();
        }

        return $category?->id;
    }

    public function getSyncStatus(int $companyId): array
    {
        $totalProducts = $this->bmsData->getProducts($companyId)->count();
        $syncedProducts = $this->bmsData->getProducts($companyId)
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
