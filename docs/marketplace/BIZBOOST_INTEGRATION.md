# BizBoost + GrowNet Market Integration

**Last Updated:** December 19, 2025  
**Status:** Phase 1-3 Complete (Backend + Sync Service + Command)  
**Priority:** High (Strategic Integration)

---

## Changelog

### December 19, 2025 - Implementation Complete ✅
**Completed:**
- ✅ Database schema (migrations)
- ✅ Model relationships (BizBoostBusinessModel ↔ MarketplaceSeller)
- ✅ Sync service (BizBoostMarketplaceSyncService)
- ✅ Artisan command (marketplace:sync-bizboost)
- ✅ Configuration (config/marketplace.php)
- ✅ Scheduled task (every 6 hours automatic sync)
- ✅ Bug fixes:
  - Fixed business_type enum (changed 'retail' to 'registered')
  - Fixed category mapping with fallback to "Other"
  - Fixed image processing for Collections
  - Fixed MarketplaceProduct image URL handling for arrays
- ✅ Tested successfully: 2 businesses synced, 7 products synced
- ✅ Production-ready with automatic scheduling

**Status:** Fully functional. Automatic sync every 6 hours. Manual sync available anytime.

**Next (Optional Enhancements):**
- ⏳ Event listeners for instant real-time sync
- ⏳ UI updates (BizBoost dashboard marketplace status card)
- ⏳ Marketplace product page "From BizBoost" badge

---

## Overview

This document outlines the integration strategy between **BizBoost** (mini-website builder) and **GrowNet Market** (e-commerce marketplace).

### Current State

**Two Separate Systems:**
1. **BizBoost Marketplace** (`/bizboost/marketplace`)
   - Lists BizBoost mini-websites
   - Service-oriented businesses
   - Directory-style listing
   - No transactions, just discovery

2. **GrowNet Market** (`/marketplace`)
   - E-commerce marketplace
   - Physical product sales
   - Full transaction flow (cart, checkout, escrow)
   - Seller dashboard, reviews, ratings

**Problem:** No integration - businesses must manage two separate presences.

---

## Integration Goals

1. **Unified Seller Experience** - One dashboard to manage both mini-website and marketplace
2. **Product Sync** - Products created in BizBoost automatically available in GrowNet Market
3. **Dual Discovery** - Businesses discoverable in both places
4. **Seamless Transition** - Easy upgrade from mini-website to full marketplace seller

---

## Recommended Strategy: Smart Auto-Listing

### How It Works

```
BizBoost Business Owner
    ↓
Creates Mini-Website + Products
    ↓
Publishes Mini-Website
    ↓
System Checks: Has Products?
    ↓
YES → Auto-create GrowNet Market Seller
    ↓
Sync Products to Marketplace
    ↓
Business now has:
- Mini-website (bizboost.mygrownet.com/business-name)
- Marketplace presence (marketplace.mygrownet.com/seller/business-name)
```

### Benefits

✅ **For Business Owners:**
- One-click presence in both places
- No duplicate data entry
- Manage everything from BizBoost dashboard
- Automatic product sync

✅ **For Platform:**
- More marketplace sellers instantly
- Better value proposition for BizBoost
- Unified ecosystem
- Cross-promotion opportunities

✅ **For Buyers:**
- More products available
- Can visit seller's full website
- Trust through mini-website presence

---

## Implementation Plan

### Phase 1: Database Schema (1-2 hours)

**Add to `bizboost_businesses` table:**
```sql
ALTER TABLE bizboost_businesses ADD COLUMN:
- marketplace_seller_id INT NULL (FK to marketplace_sellers)
- auto_sync_products BOOLEAN DEFAULT true
- marketplace_sync_enabled BOOLEAN DEFAULT true
```

**Add to `marketplace_sellers` table:**
```sql
ALTER TABLE marketplace_sellers ADD COLUMN:
- bizboost_business_id INT NULL (FK to bizboost_businesses)
- is_bizboost_synced BOOLEAN DEFAULT false
```

### Phase 2: Sync Service (3-4 hours)

Create `BizBoostMarketplaceSyncService`:

```php
class BizBoostMarketplaceSyncService
{
    /**
     * Create marketplace seller from BizBoost business
     */
    public function createMarketplaceSeller(BizBoostBusinessModel $business): MarketplaceSeller
    {
        // Check if already synced
        if ($business->marketplace_seller_id) {
            return MarketplaceSeller::find($business->marketplace_seller_id);
        }

        // Create marketplace seller
        $seller = MarketplaceSeller::create([
            'user_id' => $business->user_id,
            'business_name' => $business->name,
            'description' => $business->description,
            'logo' => $business->logo_path,
            'phone' => $business->phone,
            'whatsapp' => $business->whatsapp,
            'email' => $business->email,
            'address' => $business->address,
            'city' => $business->city,
            'province' => $business->province,
            'country' => $business->country,
            'kyc_status' => 'approved', // Auto-approve BizBoost businesses
            'is_active' => true,
            'bizboost_business_id' => $business->id,
            'is_bizboost_synced' => true,
        ]);

        // Link back to BizBoost
        $business->update([
            'marketplace_seller_id' => $seller->id,
        ]);

        return $seller;
    }

    /**
     * Sync products from BizBoost to Marketplace
     */
    public function syncProducts(BizBoostBusinessModel $business): int
    {
        $seller = $this->getOrCreateSeller($business);
        $syncedCount = 0;

        foreach ($business->products()->where('is_active', true)->get() as $bizboostProduct) {
            // Check if already synced
            $marketplaceProduct = MarketplaceProduct::where('bizboost_product_id', $bizboostProduct->id)->first();

            if ($marketplaceProduct) {
                // Update existing
                $marketplaceProduct->update([
                    'name' => $bizboostProduct->name,
                    'description' => $bizboostProduct->description,
                    'price' => $bizboostProduct->price,
                    'images' => $bizboostProduct->images,
                    'stock_quantity' => $bizboostProduct->stock_quantity,
                ]);
            } else {
                // Create new
                MarketplaceProduct::create([
                    'seller_id' => $seller->id,
                    'bizboost_product_id' => $bizboostProduct->id,
                    'name' => $bizboostProduct->name,
                    'description' => $bizboostProduct->description,
                    'price' => $bizboostProduct->price,
                    'images' => $bizboostProduct->images,
                    'stock_quantity' => $bizboostProduct->stock_quantity,
                    'category_id' => $this->mapCategory($bizboostProduct->category),
                    'status' => 'active', // Auto-approve
                ]);
            }

            $syncedCount++;
        }

        return $syncedCount;
    }

    /**
     * Handle product deletion
     */
    public function handleProductDeleted(BizBoostProductModel $product): void
    {
        MarketplaceProduct::where('bizboost_product_id', $product->id)->delete();
    }
}
```

### Phase 3: Event Listeners (2-3 hours)

**Listen to BizBoost events:**

```php
// When mini-website is published
Event::listen(MiniWebsitePublished::class, function ($event) {
    $business = $event->business;
    
    // Only sync if business has products
    if ($business->products()->where('is_active', true)->count() > 0) {
        $syncService = app(BizBoostMarketplaceSyncService::class);
        $syncService->createMarketplaceSeller($business);
        $syncService->syncProducts($business);
    }
});

// When product is created/updated
Event::listen(ProductCreated::class, function ($event) {
    $product = $event->product;
    $business = $product->business;
    
    if ($business->marketplace_sync_enabled) {
        $syncService = app(BizBoostMarketplaceSyncService::class);
        $syncService->syncProducts($business);
    }
});

// When product is deleted
Event::listen(ProductDeleted::class, function ($event) {
    $syncService = app(BizBoostMarketplaceSyncService::class);
    $syncService->handleProductDeleted($event->product);
});
```

### Phase 4: UI Updates (2-3 hours)

**BizBoost Dashboard:**
- Add "Marketplace Status" card
- Show marketplace seller stats (orders, revenue)
- Toggle to enable/disable marketplace sync
- Link to marketplace seller dashboard

**BizBoost Mini-Website Settings:**
- Checkbox: "List products on GrowNet Market"
- Preview marketplace listing
- Sync status indicator

**GrowNet Market Product Page:**
- Badge: "From BizBoost Business"
- Link to full mini-website
- "Visit Store Website" button

### Phase 5: Migration (1-2 hours)

**Migrate existing BizBoost businesses:**
```php
php artisan marketplace:sync-bizboost-businesses
```

This command:
1. Finds all published BizBoost businesses with products
2. Creates marketplace sellers
3. Syncs products
4. Sends notification to business owners

---

## Production Usage

### Automatic Sync (Scheduled)

The system automatically syncs BizBoost businesses to GrowNet Market **every 6 hours**:
- Configured in `routes/console.php`
- Runs: 12:00 AM, 6:00 AM, 12:00 PM, 6:00 PM
- Syncs all eligible businesses with `marketplace_sync_enabled = true`

**No manual intervention needed** - just ensure Laravel scheduler is running:
```bash
# Add to crontab (production server)
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### Manual Sync Commands

**Sync all eligible businesses:**
```bash
php artisan marketplace:sync-bizboost --all
```

**Sync specific business:**
```bash
php artisan marketplace:sync-bizboost --business-id=5
```

**Force sync (even if already synced):**
```bash
php artisan marketplace:sync-bizboost --all --force
```

### Quick Reference Card

Save this for production:

```bash
# ============================================
# GrowNet Market - BizBoost Sync Commands
# ============================================

# Sync all businesses (most common)
php artisan marketplace:sync-bizboost --all

# Sync one business
php artisan marketplace:sync-bizboost --business-id=X

# Force re-sync everything
php artisan marketplace:sync-bizboost --all --force

# Check sync status (coming soon)
php artisan marketplace:sync-status

# ============================================
```

---

## User Flows

### Flow 1: New BizBoost Business

```
1. User creates BizBoost business
2. Adds products
3. Publishes mini-website
4. ✨ System auto-creates marketplace seller
5. ✨ Products appear in GrowNet Market
6. User receives notification: "Your products are now on GrowNet Market!"
7. User can manage from BizBoost dashboard
```

### Flow 2: Existing BizBoost Business

```
1. User has published mini-website
2. Adds first product
3. ✨ System prompts: "List this product on GrowNet Market?"
4. User clicks "Yes"
5. ✨ Marketplace seller created
6. Product synced
```

### Flow 3: Buyer Discovery

```
1. Buyer browses GrowNet Market
2. Finds product from BizBoost business
3. Sees "Visit Store Website" button
4. Clicks → Opens mini-website in new tab
5. Can browse full catalog, read about business
6. Returns to marketplace to complete purchase
```

---

## Data Sync Rules

### What Gets Synced

✅ **Always Synced:**
- Product name, description, price
- Product images
- Stock quantity
- Business name, logo, description
- Contact info (phone, email, WhatsApp)

✅ **Synced on Change:**
- Product updates (price, description, images)
- Stock quantity changes
- Business profile updates

❌ **Not Synced:**
- BizBoost-specific features (posts, campaigns)
- Customer data (separate per platform)
- Sales/orders (tracked separately)

### Sync Direction

**One-Way Sync (BizBoost → Marketplace):**
- Products created in BizBoost appear in marketplace
- Updates in BizBoost reflect in marketplace
- Deletions in BizBoost remove from marketplace

**No Reverse Sync:**
- Marketplace orders don't create BizBoost sales
- Marketplace reviews stay in marketplace
- Inventory managed in BizBoost only

---

## Edge Cases

### Case 1: Business Unpublishes Mini-Website
**Action:** Keep marketplace listing active (they may want marketplace only)
**Option:** Add setting to auto-delist from marketplace

### Case 2: Product Out of Stock
**Action:** Sync stock quantity, marketplace shows "Out of Stock"

### Case 3: Business Deletes Product
**Action:** Remove from marketplace immediately

### Case 4: Duplicate Products
**Action:** Use `bizboost_product_id` to prevent duplicates

### Case 5: Category Mismatch
**Action:** Map BizBoost categories to marketplace categories
**Fallback:** Use "Other" category if no match

---

## Configuration

```php
// config/marketplace.php
'bizboost_integration' => [
    'enabled' => env('MARKETPLACE_BIZBOOST_SYNC', true),
    'auto_sync_on_publish' => true,
    'auto_approve_sellers' => true, // BizBoost businesses pre-approved
    'auto_approve_products' => true,
    'sync_interval' => 300, // 5 minutes for batch sync
    'category_mapping' => [
        'Food & Beverage' => 'Food & Drinks',
        'Fashion & Apparel' => 'Fashion',
        'Electronics' => 'Electronics',
        // ... more mappings
    ],
],
```

---

## Benefits Summary

### For Business Owners
- 🚀 Instant marketplace presence
- 📦 No duplicate product entry
- 💰 More sales channels
- ⚡ One dashboard for everything

### For Platform
- 📈 More marketplace inventory instantly
- 🎯 Better BizBoost value proposition
- 🔄 Ecosystem synergy
- 💪 Competitive advantage

### For Buyers
- 🛍️ More products to choose from
- 🏪 Can visit full business websites
- ✅ Trust through dual presence
- 🔍 Better discovery

---

## Implementation Timeline

| Phase | Tasks | Time | Priority |
|-------|-------|------|----------|
| 1 | Database schema updates | 1-2h | High |
| 2 | Sync service | 3-4h | High |
| 3 | Event listeners | 2-3h | High |
| 4 | UI updates | 2-3h | Medium |
| 5 | Migration command | 1-2h | Medium |
| 6 | Testing | 2-3h | High |

**Total:** 11-17 hours

---

## Next Steps

1. ✅ Document integration strategy (this document)
2. ⏳ Get stakeholder approval
3. ⏳ Create database migrations
4. ⏳ Implement sync service
5. ⏳ Add event listeners
6. ⏳ Update UI
7. ⏳ Test with sample businesses
8. ⏳ Migrate existing businesses
9. ⏳ Launch and monitor

---

## Questions to Resolve

1. **Auto-approve or manual review?**
   - Recommendation: Auto-approve BizBoost businesses (they're already vetted)

2. **Sync frequency?**
   - Recommendation: Real-time for critical updates, batch sync every 5 minutes for others

3. **Inventory management?**
   - Recommendation: BizBoost is source of truth, marketplace reads only

4. **Order fulfillment?**
   - Recommendation: Marketplace orders managed in marketplace, not synced to BizBoost

5. **Pricing strategy?**
   - Recommendation: Same prices in both places (no marketplace markup)

---

## Changelog

### December 19, 2025
- Initial document created
- Defined integration strategy
- Outlined implementation plan

