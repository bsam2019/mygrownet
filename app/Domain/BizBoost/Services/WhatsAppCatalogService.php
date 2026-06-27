<?php

namespace App\Domain\BizBoost\Services;

use App\Infrastructure\Persistence\Eloquent\BizBoostIntegrationModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostProductModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppCatalogService
{
    private const API_VERSION = 'v25.0';
    private const BASE_URL = 'https://graph.facebook.com';

    public function __construct(
        private BizBoostIntegrationModel $integration
    ) {}

    /**
     * Create a product catalog in the Business Manager.
     */
    public function createCatalog(string $name, string $description = ''): array
    {
        $businessManagerId = $this->getBusinessManagerId();
        $accessToken = $this->integration->access_token;

        $response = Http::withToken($accessToken)
            ->post(self::BASE_URL . '/' . self::API_VERSION . "/{$businessManagerId}/product_catalogs", [
                'name' => $name,
            ]);

        if ($response->failed()) {
            Log::error('WhatsApp catalog creation failed', [
                'response' => $response->body(),
            ]);
            throw new \Exception('Failed to create catalog: ' . $response->body());
        }

        $data = $response->json();
        $catalogId = $data['id'];

        // Store catalog ID
        $this->integration->update([
            'catalog_id' => $catalogId,
            'whatsapp_catalog_settings' => array_merge(
                $this->integration->whatsapp_catalog_settings ?? [],
                ['name' => $name, 'created_at' => now()->toISOString()]
            ),
        ]);

        return $data;
    }

    /**
     * Connect the catalog to the WhatsApp Business Account.
     */
    public function connectCatalogToWaba(): array
    {
        $catalogId = $this->integration->catalog_id;
        $wabaId = $this->integration->provider_user_id;
        $accessToken = $this->integration->access_token;

        if (!$catalogId) {
            throw new \Exception('No catalog ID found. Create a catalog first.');
        }

        $response = Http::withToken($accessToken)
            ->post(self::BASE_URL . '/' . self::API_VERSION . "/{$wabaId}/product_catalogs", [
                'catalog_id' => $catalogId,
            ]);

        if ($response->failed()) {
            Log::error('Failed to connect catalog to WABA', [
                'response' => $response->body(),
            ]);
            throw new \Exception('Failed to connect catalog to WhatsApp: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Disconnect the catalog from the WhatsApp Business Account.
     */
    public function disconnectCatalogFromWaba(): array
    {
        $catalogId = $this->integration->catalog_id;
        $wabaId = $this->integration->provider_user_id;
        $accessToken = $this->integration->access_token;

        if (!$catalogId) {
            throw new \Exception('No catalog ID found.');
        }

        $response = Http::withToken($accessToken)
            ->delete(self::BASE_URL . '/' . self::API_VERSION . "/{$wabaId}/product_catalogs", [
                'catalog_id' => $catalogId,
            ]);

        if ($response->failed()) {
            Log::error('Failed to disconnect catalog from WABA', [
                'response' => $response->body(),
            ]);
            throw new \Exception('Failed to disconnect catalog: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Sync a single product to the WhatsApp catalog.
     */
    public function syncProduct(BizBoostProductModel $product): array
    {
        $catalogId = $this->integration->catalog_id;
        $accessToken = $this->integration->access_token;

        if (!$catalogId) {
            throw new \Exception('No catalog ID found. Set up catalog first.');
        }

        $retailerId = $product->sku ?: 'BBP-' . $product->id;
        $imageUrl = $product->image_url;
        $price = $product->sale_price ?? $product->price;

        $data = [
            'title' => $product->name,
            'description' => strip_tags($product->description ?? ''),
            'price' => number_format((float) $price, 2, '.', ''),
            'currency' => $product->currency ?: 'ZMW',
            'retailer_id' => $retailerId,
            'condition' => 'new',
            'availability' => $product->track_inventory && $product->stock_quantity < 1
                ? 'out of stock'
                : 'in stock',
        ];

        if ($imageUrl) {
            $data['image_url'] = $imageUrl;
        }
        if ($product->categoryModel || $product->category) {
            $data['category'] = $product->categoryModel?->name ?? $product->category;
        }
        // Brand could be stored in product attributes if needed
        if ($product->attributes && isset($product->attributes['brand'])) {
            $data['brand'] = $product->attributes['brand'];
        }

        // Check if product already exists in catalog
        $existing = $this->getCatalogProduct($retailerId);

        if ($existing) {
            // Update
            $response = Http::withToken($accessToken)
                ->post(self::BASE_URL . '/' . self::API_VERSION . "/{$catalogId}:{$retailerId}", $data);
        } else {
            // Create
            $response = Http::withToken($accessToken)
                ->post(self::BASE_URL . '/' . self::API_VERSION . "/{$catalogId}/products", $data);
        }

        if ($response->failed()) {
            Log::error('WhatsApp catalog product sync failed', [
                'product_id' => $product->id,
                'response' => $response->body(),
            ]);
            throw new \Exception('Failed to sync product to catalog: ' . $response->body());
        }

        $result = $response->json();

        // Update catalog settings with last sync time
        $this->integration->update([
            'whatsapp_catalog_settings' => array_merge(
                $this->integration->whatsapp_catalog_settings ?? [],
                ['last_sync_at' => now()->toISOString()]
            ),
        ]);

        return $result;
    }

    /**
     * Remove a product from the WhatsApp catalog.
     */
    public function removeProduct(BizBoostProductModel $product): array
    {
        $catalogId = $this->integration->catalog_id;
        $accessToken = $this->integration->access_token;

        if (!$catalogId) {
            throw new \Exception('No catalog ID found.');
        }

        $retailerId = $product->sku ?: 'BBP-' . $product->id;

        $response = Http::withToken($accessToken)
            ->delete(self::BASE_URL . '/' . self::API_VERSION . "/{$catalogId}:{$retailerId}");

        if ($response->failed()) {
            Log::error('WhatsApp catalog product removal failed', [
                'product_id' => $product->id,
                'response' => $response->body(),
            ]);
            throw new \Exception('Failed to remove product from catalog: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Sync all active products to the WhatsApp catalog.
     */
    public function syncAllProducts(): array
    {
        $business = $this->integration->business;
        $products = $business->products()->where('is_active', true)->get();
        $results = [];

        foreach ($products as $product) {
            try {
                $results[$product->id] = [
                    'success' => true,
                    'result' => $this->syncProduct($product),
                ];
            } catch (\Exception $e) {
                $results[$product->id] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get product from catalog by retailer ID.
     */
    public function getCatalogProduct(string $retailerId): ?array
    {
        $catalogId = $this->integration->catalog_id;
        $accessToken = $this->integration->access_token;

        if (!$catalogId) {
            return null;
        }

        $response = Http::withToken($accessToken)
            ->get(self::BASE_URL . '/' . self::API_VERSION . "/{$catalogId}:{$retailerId}");

        if ($response->failed()) {
            return null;
        }

        return $response->json();
    }

    /**
     * Get the Business Manager ID from the integration.
     * The WABA ID is stored in provider_user_id.
     * The Business Manager ID needs to be derived from the access token.
     */
    private function getBusinessManagerId(): string
    {
        $accessToken = $this->integration->access_token;

        // First try to get from stored meta
        $meta = $this->integration->meta ?? [];

        // Check if we cached it
        $cachedBmId = $meta['business_manager_id'] ?? null;
        if ($cachedBmId) {
            return $cachedBmId;
        }

        // Fetch from API - get the business the user manages
        $response = Http::withToken($accessToken)
            ->get(self::BASE_URL . '/' . self::API_VERSION . '/me/businesses', [
                'fields' => 'id,name,client_whatsapp_business_accounts',
            ]);

        if ($response->failed() || empty($response->json()['data'])) {
            throw new \Exception('No Business Manager found. Ensure your Facebook account has a Business Manager.');
        }

        $businesses = $response->json()['data'];

        // Try to match by WABA ID
        $wabaId = $this->integration->provider_user_id;
        $matched = null;

        foreach ($businesses as $biz) {
            $wabas = $biz['client_whatsapp_business_accounts'] ?? [];
            foreach ($wabas as $waba) {
                if ($waba['id'] === $wabaId) {
                    $matched = $biz;
                    break 2;
                }
            }
        }

        // Fallback to first business
        $matched = $matched ?: $businesses[0];
        $bmId = $matched['id'];

        // Cache it
        $meta['business_manager_id'] = $bmId;
        $this->integration->update(['meta' => $meta]);

        return $bmId;
    }

    /**
     * Get catalog status information.
     */
    public function getCatalogStatus(): array
    {
        $catalogId = $this->integration->catalog_id;

        return [
            'connected' => $catalogId !== null,
            'catalog_id' => $catalogId,
            'settings' => $this->integration->whatsapp_catalog_settings ?? [],
            'waba_connected' => $this->integration->status === 'active',
            'phone_number' => $this->integration->provider_page_name,
        ];
    }

    /**
     * Check if catalog setup is complete and working.
     */
    public function verifyCatalogSetup(): array
    {
        $catalogId = $this->integration->catalog_id;
        $accessToken = $this->integration->access_token;

        if (!$catalogId) {
            return ['valid' => false, 'error' => 'No catalog configured'];
        }

        $response = Http::withToken($accessToken)
            ->get(self::BASE_URL . '/' . self::API_VERSION . "/{$catalogId}", [
                'fields' => 'id,name,product_count',
            ]);

        if ($response->failed()) {
            return ['valid' => false, 'error' => 'Catalog access failed: ' . $response->body()];
        }

        $data = $response->json();

        return [
            'valid' => true,
            'id' => $data['id'],
            'name' => $data['name'],
            'product_count' => $data['product_count'] ?? 0,
        ];
    }

    /**
     * Send a product message via WhatsApp (single product).
     */
    public function sendProductMessage(string $to, BizBoostProductModel $product): array
    {
        $catalogId = $this->integration->catalog_id;
        $phoneNumberId = $this->integration->provider_page_id;
        $accessToken = $this->integration->access_token;

        if (!$catalogId) {
            throw new \Exception('No catalog configured. Set up WhatsApp catalog first.');
        }

        $retailerId = $product->sku ?: 'BBP-' . $product->id;

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'catalog_message',
            'catalog_message' => [
                'catalog_id' => $catalogId,
                'product' => [
                    'retailer_id' => $retailerId,
                ],
            ],
        ];

        $response = Http::withToken($accessToken)
            ->post(
                self::BASE_URL . '/' . self::API_VERSION . "/{$phoneNumberId}/messages",
                $data
            );

        if ($response->failed()) {
            Log::error('WhatsApp product message failed', [
                'response' => $response->body(),
                'to' => $to,
            ]);
            throw new \Exception('Failed to send product message: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Send a catalog message (multi-product) via WhatsApp.
     */
    public function sendCatalogMessage(string $to, array $productIds = []): array
    {
        $catalogId = $this->integration->catalog_id;
        $phoneNumberId = $this->integration->provider_page_id;
        $accessToken = $this->integration->access_token;

        if (!$catalogId) {
            throw new \Exception('No catalog configured.');
        }

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'catalog_message',
            'catalog_message' => [
                'catalog_id' => $catalogId,
            ],
        ];

        if (!empty($productIds)) {
            $data['catalog_message']['items'] = array_map(fn($id) => ['retailer_id' => $id], $productIds);
        }

        $response = Http::withToken($accessToken)
            ->post(
                self::BASE_URL . '/' . self::API_VERSION . "/{$phoneNumberId}/messages",
                $data
            );

        if ($response->failed()) {
            Log::error('WhatsApp catalog message failed', [
                'response' => $response->body(),
                'to' => $to,
            ]);
            throw new \Exception('Failed to send catalog message: ' . $response->body());
        }

        return $response->json();
    }
}
