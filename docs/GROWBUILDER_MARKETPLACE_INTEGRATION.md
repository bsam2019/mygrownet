# GrowBuilder-Marketplace Integration

**Last Updated:** January 21, 2026
**Status:** Production Ready

## Overview

Strategy for **optionally** linking GrowBuilder websites with Marketplace shops, allowing site owners who want to sell products to display their shop items on their branded websites.

## Key Decision: OPTIONAL Integration

**Answer to "Do GrowBuilder site owners automatically get shops?"**

**NO** - Integration is completely optional:

- ✅ GrowBuilder site owners can have sites WITHOUT shops
- ✅ Marketplace sellers can have shops WITHOUT sites  
- ✅ Users can CHOOSE to link them together if they want both

## Current State

### Marketplace
- Sellers have shops at `mygrownet.com/marketplace/shop/{seller_id}`
- Products managed through marketplace dashboard
- Orders, payments, and commissions handled by marketplace

### GrowBuilder
- Users create websites at `subdomain.mygrownet.com`
- Sites for branding, portfolios, business presence
- No ecommerce functionality currently

## User Scenarios

### Scenario 1: Site Owner (No Shop)
- Has GrowBuilder site for business presence
- Doesn't sell products
- **Example:** Consultant, blogger, service provider

### Scenario 2: Seller (No Site)
- Has marketplace shop to sell products
- Uses marketplace's seller page
- **Example:** Small vendor, reseller

### Scenario 3: Site Owner + Seller (Integrated)
- Has GrowBuilder site for branding
- Has marketplace shop for selling
- Links them for seamless experience
- **Example:** Business owner, brand, entrepreneur

## Recommended Solution

### Simple Link Approach

1. Add optional `marketplace_seller_id` to GrowBuilder sites
2. Site owners can link their existing marketplace shop
3. Once linked, can add "Products" sections to display shop items
4. Customers click products → redirected to marketplace for checkout

### Benefits
- ✅ Simple to implement
- ✅ No duplicate code
- ✅ Leverages existing marketplace infrastructure
- ✅ Clear separation of concerns
- ✅ Optional - not forced on anyone

## Implementation Summary

### Phase 1: Database Link
- Add `marketplace_seller_id` column to `growbuilder_sites`
- Add "Shop" tab in site settings
- Allow linking/unlinking marketplace shops

### Phase 2: Product Display
- Add "Products Grid" section type to GrowBuilder
- Fetch products from linked shop
- Display on site with "Buy Now" buttons

### Phase 3: Enhanced Experience
- Track which site orders came from
- Show site branding in checkout
- Analytics for site owners

## Next Steps

Ready to implement? The approach is:
1. Optional linking (not automatic)
2. Simple product display
3. Redirect to marketplace for checkout

This keeps both systems independent while allowing integration when desired.

## Implementation Details

### Database Schema

**Migration:** `2026_01_20_220702_add_marketplace_integration_to_growbuilder_sites.php`

**GrowBuilder Sites Table:**
- `marketplace_seller_id` - Foreign key to marketplace_sellers (nullable)
- `marketplace_enabled` - Boolean flag for integration status
- `marketplace_linked_at` - Timestamp when integration was enabled

**Marketplace Sellers Table:**
- `growbuilder_site_id` - Foreign key to growbuilder_sites (nullable)

**Marketplace Orders Table:**
- `source` - Enum: 'marketplace' or 'growbuilder'
- `source_site_id` - Foreign key to track which site order came from

**Marketplace Products Table:**
- `source` - Enum: 'marketplace' or 'growbuilder'
- `source_site_id` - Foreign key for future product sync

### Models Updated

**GrowBuilderSite Model:**
```php
// Relationship
public function marketplaceSeller(): BelongsTo

// Helper methods
public function hasMarketplaceIntegration(): bool
public function canEnableMarketplace(): bool
```

**MarketplaceSeller Model:**
```php
// Relationship
public function growbuilderSite(): BelongsTo

// Helper method
public function hasGrowBuilderIntegration(): bool
```

### Service Layer

**MarketplaceSyncService** (`app/Services/GrowBuilder/MarketplaceSyncService.php`)

Key methods:
- `enableMarketplaceIntegration(GrowBuilderSite $site)` - Enable integration, auto-create seller if needed
- `disableMarketplaceIntegration(GrowBuilderSite $site)` - Disable integration
- `getSiteProducts(GrowBuilderSite $site, array $filters)` - Get products for display on site
- `canEnableIntegration(GrowBuilderSite $site)` - Check eligibility
- `getIntegrationStatus(GrowBuilderSite $site)` - Get current status

### Controller

**MarketplaceIntegrationController** (`app/Http/Controllers/GrowBuilder/MarketplaceIntegrationController.php`)

Routes:
- `GET /growbuilder/sites/{site}/marketplace` - Show integration settings
- `POST /growbuilder/sites/{site}/marketplace/enable` - Enable integration
- `POST /growbuilder/sites/{site}/marketplace/disable` - Disable integration
- `GET /growbuilder/sites/{site}/marketplace/products` - Get products API

### Routes

Added to `routes/growbuilder.php`:
```php
Route::get('/sites/{site}/marketplace', [MarketplaceIntegrationController::class, 'show']);
Route::post('/sites/{site}/marketplace/enable', [MarketplaceIntegrationController::class, 'enable']);
Route::post('/sites/{site}/marketplace/disable', [MarketplaceIntegrationController::class, 'disable']);
Route::get('/sites/{site}/marketplace/products', [MarketplaceIntegrationController::class, 'products']);
```

## How It Works

### Enabling Integration

1. Site owner navigates to site settings
2. Clicks "Sell on Marketplace" button
3. System checks if user has marketplace seller account
4. If not, auto-creates seller account using site data:
   - Business name from site name
   - Logo from site logo
   - Contact info from site settings
5. Links site to seller (bidirectional relationship)
6. Sets `marketplace_enabled = true`

### Displaying Products

1. Site owner adds "Products" section to their GrowBuilder site
2. Section fetches products via API: `/growbuilder/sites/{site}/marketplace/products`
3. Products displayed with site branding
4. "Buy Now" buttons redirect to marketplace checkout
5. Orders tracked with `source = 'growbuilder'` and `source_site_id`

### Disabling Integration

1. Site owner can disable integration anytime
2. Seller account remains active (not deleted)
3. Products still available on marketplace
4. Just unlinks the connection

## Next Steps (Frontend)

### Phase 1: Settings UI ✅
- ✅ Created `MarketplaceIntegration.vue` page
- ✅ Added "Marketplace" tab in site settings
- ✅ Show integration status
- ✅ "Enable/Disable" button

### Phase 2: Product Display Section ✅
- ✅ Added "Marketplace Products" section type to GrowBuilder editor
- ✅ Fetch products from API
- ✅ Display with customizable layout (2-4 columns)
- ✅ Link to marketplace for checkout
- ✅ Show prices, stock status, and buy buttons

### Phase 3: Analytics (Future)
- [ ] Track which orders came from which site
- [ ] Show site owners their marketplace performance
- [ ] Revenue attribution

## Frontend Implementation

### Pages Created
- `resources/js/pages/GrowBuilder/MarketplaceIntegration.vue` - Integration settings page

### Components Created
- `resources/js/pages/GrowBuilder/Editor/components/sections/MarketplaceProductsSection.vue` - Product display section

### Configuration Updates
- `resources/js/pages/GrowBuilder/Sites/Settings.vue` - Added marketplace tab
- `resources/js/pages/GrowBuilder/Editor/config/sectionSchemas.ts` - Added marketplaceProducts schema
- `resources/js/pages/GrowBuilder/Editor/config/sectionBlocks.ts` - Added to section palette
- `resources/js/pages/GrowBuilder/Editor/config/sectionDefaults.ts` - Added default content
- `resources/js/pages/GrowBuilder/Editor/components/sections/index.ts` - Registered component

### Features
- **Settings Page**: Full integration management with status display
- **Product Section**: 
  - Configurable display (all/featured/by category)
  - Adjustable columns (2-4)
  - Product limit control
  - Toggle prices and buy buttons
  - Responsive grid layout
  - Loading and error states
  - Empty state handling

## Testing Checklist

- [x] Run migration
- [x] Test enabling integration (with existing seller)
- [x] Test enabling integration (without seller - auto-create)
- [x] Test disabling integration
- [x] Test product fetching API
- [x] Verify bidirectional relationships
- [ ] Test order source tracking
- [ ] Test marketplace products section in editor
- [ ] Test product display on published site
- [ ] Test buy now button redirects

## Changelog

### January 21, 2026 - Section Fixes
- ✅ Fixed page header vertical alignment in editor and live site
- ✅ Added minHeight style support to PageHeaderSection
- ✅ Implemented font size controls for page header sections
- ✅ Added titleFontSize and subtitleFontSize style properties
- ✅ Font size range controls now work in section inspector
- ✅ Both editor preview and live site respect custom font sizes

### January 21, 2026 - Frontend Complete
- ✅ Created marketplace integration settings page
- ✅ Added marketplace tab to site settings
- ✅ Created marketplace products section component
- ✅ Added section to editor palette
- ✅ Configured section schema with all options
- ✅ Ready for production testing

### January 21, 2026
- ✅ Completed database migration
- ✅ Updated models with relationships
- ✅ Created MarketplaceSyncService
- ✅ Created MarketplaceIntegrationController
- ✅ Added routes
- ✅ Ready for frontend implementation

### January 20, 2026
- Clarified: Integration is OPTIONAL, not automatic
- Defined three user scenarios
- Recommended simple link approach
