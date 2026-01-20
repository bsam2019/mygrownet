<?php

namespace App\Services\GrowBuilder;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Models\MarketplaceSeller;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MarketplaceSyncService
{
    /**
     * Enable marketplace integration for a GrowBuilder site
     */
    public function enableMarketplaceIntegration(GrowBuilderSite $site): array
    {
        return DB::transaction(function () use ($site) {
            $user = $site->user;

            // Check if user already has a marketplace seller account
            $seller = $user->marketplaceSeller;

            if (!$seller) {
                // Auto-create marketplace seller account
                $seller = $this->createSellerFromSite($site, $user);
            }

            // Link the site to the seller
            $site->update([
                'marketplace_seller_id' => $seller->id,
                'marketplace_enabled' => true,
                'marketplace_linked_at' => now(),
            ]);

            // Link the seller to the site (bidirectional)
            $seller->update([
                'growbuilder_site_id' => $site->id,
            ]);

            return [
                'success' => true,
                'seller' => $seller,
                'message' => 'Marketplace integration enabled successfully',
            ];
        });
    }

    /**
     * Disable marketplace integration for a GrowBuilder site
     */
    public function disableMarketplaceIntegration(GrowBuilderSite $site): array
    {
        return DB::transaction(function () use ($site) {
            $seller = $site->marketplaceSeller;

            // Unlink the site from seller
            $site->update([
                'marketplace_enabled' => false,
                'marketplace_linked_at' => null,
            ]);

            // Optionally unlink seller from site (keep seller account active)
            if ($seller) {
                $seller->update([
                    'growbuilder_site_id' => null,
                ]);
            }

            return [
                'success' => true,
                'message' => 'Marketplace integration disabled successfully',
            ];
        });
    }

    /**
     * Create a marketplace seller account from GrowBuilder site data
     */
    private function createSellerFromSite(GrowBuilderSite $site, User $user): MarketplaceSeller
    {
        $contactInfo = $site->contact_info ?? [];

        return MarketplaceSeller::create([
            'user_id' => $user->id,
            'growbuilder_site_id' => $site->id,
            'business_name' => $site->name,
            'business_type' => 'other', // Default, can be updated later
            'phone' => $contactInfo['phone'] ?? $user->phone,
            'email' => $contactInfo['email'] ?? $user->email,
            'description' => $site->description ?? '',
            'logo_path' => $site->logo,
            'trust_level' => 'new',
            'kyc_status' => 'pending',
            'is_active' => true,
        ]);
    }

    /**
     * Get marketplace products for a site
     */
    public function getSiteProducts(GrowBuilderSite $site, array $filters = [])
    {
        if (!$site->hasMarketplaceIntegration()) {
            return collect();
        }

        $query = $site->marketplaceSeller->products()
            ->where('status', 'active');

        // Apply filters
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['featured'])) {
            $query->where('is_featured', true);
        }

        if (isset($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        return $query->with('category')->get();
    }

    /**
     * Check if user can enable marketplace integration
     */
    public function canEnableIntegration(GrowBuilderSite $site): bool
    {
        // Site must be published
        if (!$site->isPublished()) {
            return false;
        }

        // Site must not already have integration enabled
        if ($site->marketplace_enabled) {
            return false;
        }

        return true;
    }

    /**
     * Get integration status for a site
     */
    public function getIntegrationStatus(GrowBuilderSite $site): array
    {
        return [
            'enabled' => $site->marketplace_enabled,
            'has_seller' => $site->marketplace_seller_id !== null,
            'seller' => $site->marketplaceSeller,
            'linked_at' => $site->marketplace_linked_at,
            'can_enable' => $this->canEnableIntegration($site),
        ];
    }
}
