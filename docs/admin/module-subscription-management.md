# Admin Module Subscription Management

**Last Updated:** December 17, 2025
**Status:** Implementation Complete (Pending Migration)

## Overview

Centralized admin interface for managing subscription configurations across all MyGrowNet apps/modules. Allows administrators to configure pricing, tiers, features, discounts, and special offers without code changes.

## Problem Statement

Currently, module subscription configurations are:
- Hardcoded in `config/modules.php`
- Require code deployment to change pricing
- No admin UI for managing tiers/features
- No discount or promotional offer system
- Each app has separate subscription logic

## Solution: Database-Driven Module Subscription Management

### Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    Admin Module Management                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Module Selection                                                │
│  ┌─────────┬─────────────┬─────────┬──────────┬─────────┐      │
│  │ BizBoost│ GrowFinance │ GrowBiz │ LifePlus │ Wedding │      │
│  └─────────┴─────────────┴─────────┴──────────┴─────────┘      │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ Module Settings                                           │   │
│  │ ├─ Status: Active/Inactive/Beta                          │   │
│  │ ├─ Trial Period: X days                                  │   │
│  │ ├─ Grace Period: X days                                  │   │
│  │ └─ Annual Discount: X%                                   │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ Subscription Tiers                                        │   │
│  │ ┌────────┬─────────┬──────────────┬──────────┐           │   │
│  │ │ Free   │ Basic   │ Professional │ Business │           │   │
│  │ │ K0/mo  │ K99/mo  │ K199/mo      │ K399/mo  │           │   │
│  │ │ [Edit] │ [Edit]  │ [Edit]       │ [Edit]   │           │   │
│  │ └────────┴─────────┴──────────────┴──────────┘           │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ Feature Toggles (per Tier)                                │   │
│  │ ┌─────────────────┬──────┬───────┬─────┬──────────┐      │   │
│  │ │ Feature         │ Free │ Basic │ Pro │ Business │      │   │
│  │ ├─────────────────┼──────┼───────┼─────┼──────────┤      │   │
│  │ │ Products        │ 10   │ 100   │ 500 │ ∞        │      │   │
│  │ │ Customers       │ 50   │ 500   │ 2K  │ ∞        │      │   │
│  │ │ AI Content      │ ❌   │ ✅    │ ✅  │ ✅       │      │   │
│  │ │ White Label     │ ❌   │ ❌    │ ❌  │ ✅       │      │   │
│  │ │ API Access      │ ❌   │ ❌    │ ❌  │ ✅       │      │   │
│  │ └─────────────────┴──────┴───────┴─────┴──────────┘      │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ Discounts & Offers                                        │   │
│  │ • Holiday Sale - 30% off annual (Dec 15-31)              │   │
│  │ • New User - 50% off first month                         │   │
│  │ [+ Add Discount]                                          │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

## Current Apps/Modules

| Module ID | App Name | Category | Has Subscription |
|-----------|----------|----------|------------------|
| `bizboost` | BizBoost | SME | ✅ |
| `growfinance` | GrowFinance | Personal/SME | ✅ |
| `growbiz` | GrowBiz | SME | ✅ |
| `lifeplus` | LifePlus | Personal | ✅ |
| `wedding-planner` | Wedding Planner | Personal | ✅ |
| `mygrow-save` | MyGrow Save | Personal | ✅ |
| `sme-accounting` | SME Accounting | SME | ✅ |
| `personal-finance` | Personal Finance | Personal | ✅ |

## Database Schema

### New Tables

#### `module_tiers`
Admin-editable subscription tiers per module.

```sql
CREATE TABLE module_tiers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    module_id VARCHAR(50) NOT NULL,
    tier_key VARCHAR(50) NOT NULL,           -- 'free', 'basic', 'professional', 'business'
    name VARCHAR(100) NOT NULL,              -- Display name
    description TEXT,
    price_monthly DECIMAL(10,2) DEFAULT 0,
    price_annual DECIMAL(10,2) DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'ZMW',
    user_limit INT NULL,                     -- NULL = unlimited
    storage_limit_mb INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_default BOOLEAN DEFAULT FALSE,        -- Default tier for new users
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY (module_id, tier_key),
    FOREIGN KEY (module_id) REFERENCES modules(id)
);
```

#### `module_tier_features`
Feature toggles and limits per tier.

```sql
CREATE TABLE module_tier_features (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    module_tier_id BIGINT NOT NULL,
    feature_key VARCHAR(100) NOT NULL,       -- 'products', 'customers', 'ai_content'
    feature_name VARCHAR(100) NOT NULL,      -- Display name
    feature_type ENUM('boolean', 'limit', 'text') DEFAULT 'boolean',
    value_boolean BOOLEAN DEFAULT FALSE,
    value_limit INT NULL,                    -- NULL = unlimited when type is 'limit'
    value_text VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY (module_tier_id, feature_key),
    FOREIGN KEY (module_tier_id) REFERENCES module_tiers(id) ON DELETE CASCADE
);
```

#### `module_discounts`
Promotional discounts with date ranges and conditions.

```sql
CREATE TABLE module_discounts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    module_id VARCHAR(50) NULL,              -- NULL = applies to all modules
    name VARCHAR(100) NOT NULL,
    description TEXT,
    discount_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
    discount_value DECIMAL(10,2) NOT NULL,
    applies_to ENUM('all_tiers', 'specific_tiers', 'annual_only', 'monthly_only') DEFAULT 'all_tiers',
    tier_keys JSON NULL,                     -- ['basic', 'professional'] if specific_tiers
    code VARCHAR(50) NULL,                   -- Promo code (optional)
    starts_at TIMESTAMP NULL,
    ends_at TIMESTAMP NULL,
    max_uses INT NULL,                       -- NULL = unlimited
    current_uses INT DEFAULT 0,
    min_purchase_amount DECIMAL(10,2) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (module_id) REFERENCES modules(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

#### `module_special_offers`
Time-limited special offers and bundles.

```sql
CREATE TABLE module_special_offers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    offer_type ENUM('bundle', 'upgrade', 'trial_extension', 'bonus_feature') DEFAULT 'bundle',
    module_ids JSON NOT NULL,                -- Modules included in offer
    tier_key VARCHAR(50) NULL,               -- Target tier
    original_price DECIMAL(10,2) NOT NULL,
    offer_price DECIMAL(10,2) NOT NULL,
    savings_display VARCHAR(50),             -- "Save K200" or "50% off"
    billing_cycle ENUM('monthly', 'annual', 'one_time') DEFAULT 'annual',
    bonus_features JSON NULL,                -- Extra features included
    starts_at TIMESTAMP NOT NULL,
    ends_at TIMESTAMP NOT NULL,
    max_purchases INT NULL,
    current_purchases INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

## Admin Features

### 1. Module Selection & Overview
- List all modules with subscription status
- Quick stats: active subscribers, revenue, churn rate
- Module status toggle (Active/Inactive/Beta)

### 2. Tier Management
- Create/edit/delete subscription tiers
- Set pricing (monthly/annual)
- Configure user limits and storage
- Reorder tiers
- Set default tier for new users

### 3. Feature Configuration
- Define features per module
- Toggle features on/off per tier
- Set numeric limits (products, customers, etc.)
- Add custom feature descriptions

### 4. Discount Management
- Create percentage or fixed discounts
- Set date ranges for promotions
- Limit to specific tiers or billing cycles
- Optional promo codes
- Usage tracking and limits

### 5. Special Offers
- Create bundle deals (multiple modules)
- Upgrade promotions
- Trial extensions
- Featured offers on pricing pages

### 6. Analytics Dashboard
- Revenue by module
- Subscriber counts by tier
- Conversion rates (free → paid)
- Churn analysis
- Discount usage stats

## API Endpoints

### Admin Routes

```
GET    /admin/modules                           # List all modules
GET    /admin/modules/{moduleId}                # Module details
PUT    /admin/modules/{moduleId}                # Update module settings

GET    /admin/modules/{moduleId}/tiers          # List tiers
POST   /admin/modules/{moduleId}/tiers          # Create tier
PUT    /admin/modules/{moduleId}/tiers/{id}     # Update tier
DELETE /admin/modules/{moduleId}/tiers/{id}     # Delete tier

GET    /admin/modules/{moduleId}/tiers/{id}/features    # List features
POST   /admin/modules/{moduleId}/tiers/{id}/features    # Add feature
PUT    /admin/modules/{moduleId}/features/{id}          # Update feature
DELETE /admin/modules/{moduleId}/features/{id}          # Delete feature

GET    /admin/discounts                         # List all discounts
POST   /admin/discounts                         # Create discount
PUT    /admin/discounts/{id}                    # Update discount
DELETE /admin/discounts/{id}                    # Delete discount

GET    /admin/special-offers                    # List offers
POST   /admin/special-offers                    # Create offer
PUT    /admin/special-offers/{id}               # Update offer
DELETE /admin/special-offers/{id}               # Delete offer
```

## Service Layer Updates

### TierConfigurationService

Update to read from database with config fallback:

```php
class TierConfigurationService
{
    public function getTierConfig(string $moduleId, string $tierKey): array
    {
        // Try database first
        $dbTier = ModuleTier::where('module_id', $moduleId)
            ->where('tier_key', $tierKey)
            ->where('is_active', true)
            ->first();
            
        if ($dbTier) {
            return $this->formatTierFromDb($dbTier);
        }
        
        // Fallback to config
        return config("modules.{$moduleId}.subscription_tiers.{$tierKey}", []);
    }
    
    public function getAllTiersForDisplay(string $moduleId): array
    {
        // Database tiers take precedence
        $dbTiers = ModuleTier::where('module_id', $moduleId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
            
        if ($dbTiers->isNotEmpty()) {
            return $dbTiers->map(fn($t) => $this->formatTierFromDb($t))->toArray();
        }
        
        // Fallback to config
        return config("modules.{$moduleId}.subscription_tiers", []);
    }
}
```

### DiscountService

New service for applying discounts:

```php
class DiscountService
{
    public function getApplicableDiscounts(
        string $moduleId, 
        string $tierKey, 
        string $billingCycle,
        ?string $promoCode = null
    ): Collection;
    
    public function calculateDiscountedPrice(
        float $originalPrice,
        Collection $discounts
    ): float;
    
    public function validatePromoCode(string $code, string $moduleId): ?ModuleDiscount;
    
    public function recordDiscountUsage(ModuleDiscount $discount, int $userId): void;
}
```

## Vue Components

### Admin Pages

```
resources/js/pages/Admin/Modules/
├── Index.vue              # Module list with stats
├── Show.vue               # Module detail with tabs
├── Tiers/
│   ├── Index.vue          # Tier list for module
│   ├── Edit.vue           # Edit tier modal
│   └── Features.vue       # Feature matrix editor
├── Discounts/
│   ├── Index.vue          # All discounts
│   └── Edit.vue           # Create/edit discount
└── Offers/
    ├── Index.vue          # Special offers list
    └── Edit.vue           # Create/edit offer
```

## Implementation Plan

### Phase 1: Database & Models
1. Create migrations for new tables
2. Create Eloquent models
3. Create seeders to migrate config data to database

### Phase 2: Service Layer
1. Update `TierConfigurationService` for DB support
2. Create `DiscountService`
3. Create `SpecialOfferService`
4. Update existing subscription controllers

### Phase 3: Admin UI
1. Create admin controller
2. Build Vue pages for module management
3. Build tier/feature editor
4. Build discount management UI

### Phase 4: Integration
1. Update checkout flows to apply discounts
2. Add promo code support
3. Display special offers on pricing pages
4. Add analytics tracking

## Migration Strategy

1. **Keep config as fallback** - Existing `config/modules.php` continues to work
2. **Database overrides config** - When DB records exist, they take precedence
3. **Seed from config** - Initial migration seeds DB from existing config
4. **Gradual transition** - Apps continue working during migration

## Security Considerations

- Admin-only access to management UI
- Audit logging for all changes
- Validation on discount amounts (prevent negative prices)
- Rate limiting on promo code validation
- Soft deletes for tiers with active subscribers

## Related Documentation

- [Module System Architecture](../modules/MODULE_SYSTEM_ARCHITECTURE.md)
- [Subscription Checkout](../SUBSCRIPTION_CHECKOUT.md)
- [Centralized Subscription Architecture](../CENTRALIZED_SUBSCRIPTION_ARCHITECTURE.md)

## Files Created

### Migrations
- `database/migrations/2025_12_17_100000_create_module_tiers_table.php`
- `database/migrations/2025_12_17_100001_create_module_tier_features_table.php`
- `database/migrations/2025_12_17_100002_create_module_discounts_table.php`
- `database/migrations/2025_12_17_100003_create_module_special_offers_table.php`

### Models
- `app/Models/ModuleTier.php`
- `app/Models/ModuleTierFeature.php`
- `app/Models/ModuleDiscount.php`
- `app/Models/ModuleSpecialOffer.php`

### Controller
- `app/Http/Controllers/Admin/ModuleSubscriptionAdminController.php`

### Vue Pages
- `resources/js/pages/Admin/ModuleSubscriptions/Index.vue` - Module list with stats
- `resources/js/pages/Admin/ModuleSubscriptions/Show.vue` - Module detail with tier management
- `resources/js/pages/Admin/ModuleSubscriptions/Discounts.vue` - Discount management
- `resources/js/pages/Admin/ModuleSubscriptions/Offers.vue` - Special offers management

### Routes
Added to `routes/admin.php` under `admin.module-subscriptions.*`

## Usage

### Run Migrations
```bash
php artisan migrate
```

### Access Admin Panel
Navigate to `/admin/module-subscriptions` to manage module subscriptions.

### Seed Tiers from Config
Click "Seed from Config" on any module page to populate database from `config/modules.php`.

## Changelog

### December 17, 2025 (Update 3)
- Created `ModuleTierSeeder` to populate tier data for all modules
- Seeder includes tiers and features for:
  - **GrowBiz**: Free, Starter, Professional, Enterprise
  - **BizBoost**: Free, Starter, Professional, Business
  - **GrowFinance**: Free, Basic, Professional, Business
  - **LifePlus**: Free, Premium, Member (MyGrowNet), Elite
- Each tier includes detailed features with proper types (boolean, limit, text)
- Registered seeder in `DatabaseSeeder.php`
- Run with: `php artisan db:seed --class=ModuleTierSeeder`

### December 17, 2025 (Update 2)
- Connected admin subscription management to app subscription pages
- Updated controllers to use `TierConfigurationService`:
  - `GrowBiz/SubscriptionController.php` - Now passes dynamic tiers
  - `BizBoost/SubscriptionController.php` - Now passes dynamic tiers
  - `GrowFinance/SubscriptionController.php` - Now passes dynamic tiers
  - `LifePlus/ProfileController.php` - Now passes dynamic tiers
- Updated Vue subscription pages to use dynamic tier data:
  - `GrowBiz/Settings/Subscription.vue` - Uses tiers prop from backend
  - `BizBoost/Settings/Subscription.vue` - Uses tiers prop from backend
  - `GrowFinance/Settings/Subscription.vue` - Uses tiers prop from backend
- Added billing cycle toggle (monthly/annual) to subscription pages
- Added discount badge display when tiers have active discounts
- Subscription pages now show admin-configured pricing and features

### December 17, 2025 (Initial)
- Initial design document created
- Defined database schema
- Outlined admin features and API endpoints
- Implemented migrations, models, controller, and Vue pages
- Added routes to admin.php
