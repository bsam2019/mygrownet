# Inventory & POS Module Architecture

**Last Updated:** December 12, 2025
**Status:** ✅ Implemented

## Overview

The Inventory and POS systems are designed as **standalone modules** that can be:
1. Used independently via `/inventory` and `/pos` routes
2. Integrated into other modules (GrowBiz, BizBoost, E-Commerce)
3. Activated via the module subscription system
4. Share data through `module_context` field

## Current State

### Inventory Module ✅
- **Module ID:** `inventory`
- **Status:** `active`
- **Routes:** `/inventory/*`
- **Tables:** User-scoped (`user_id` foreign key)
  - `inventory_categories`
  - `inventory_items`
  - `stock_movements`
  - `inventory_alerts`

### POS Module ✅
- **Module ID:** `pos`
- **Status:** `active`
- **Routes:** `/pos/*`
- **Tables:** Generic with `module_context` for multi-module support
  - `pos_shifts`
  - `pos_sales`
  - `pos_sale_items`
  - `pos_settings`
  - `pos_quick_products`
  - `module_integrations` (tracks which modules have POS/Inventory enabled)

## Proposed Architecture

### Option A: Unified Inventory + POS Module (Recommended)

Create a single "Inventory & POS" module that includes both features:

```
Module: inventory-pos
├── Inventory Management
│   ├── Categories
│   ├── Products/Items
│   ├── Stock Tracking
│   └── Alerts
└── Point of Sale
    ├── Terminal
    ├── Shifts
    ├── Sales
    └── Reports
```

**Benefits:**
- Single subscription for both features
- Seamless integration (POS uses inventory items)
- Simpler user experience
- Shared settings and configuration

### Option B: Separate Modules with Integration

Keep as two separate modules with optional integration:

```
Module: inventory
├── Categories
├── Products/Items
├── Stock Tracking
└── Alerts

Module: pos
├── Terminal
├── Shifts
├── Sales
├── Reports
└── Integration with Inventory (optional)
```

**Benefits:**
- Users can subscribe to just what they need
- More granular pricing
- Flexibility for different business types

## Database Schema Changes Required

### For Standalone POS Module

Rename tables from `growbiz_pos_*` to generic `pos_*`:

```sql
-- Current (GrowBiz-specific)
growbiz_pos_shifts
growbiz_pos_sales
growbiz_pos_sale_items
growbiz_pos_settings
growbiz_pos_quick_products

-- Proposed (Standalone)
pos_shifts
pos_sales
pos_sale_items
pos_settings
pos_quick_products
```

### Integration Points

Both modules should support integration via:

1. **Module Context** - Track which module initiated the action
   ```php
   $sale->module_context = 'growbiz'; // or 'bizboost', 'ecommerce'
   ```

2. **Polymorphic Relationships** - Link to different parent entities
   ```php
   $sale->saleable_type = 'App\Models\GrowBizBusiness';
   $sale->saleable_id = 123;
   ```

## Module Registration

### POS Module (New)

```php
[
    'id' => 'pos',
    'name' => 'Point of Sale',
    'slug' => 'pos',
    'category' => 'sme',
    'description' => 'Simple POS system for retail sales, shift management, and reporting',
    'icon' => '🛒',
    'color' => '#8B5CF6', // Purple
    'account_types' => json_encode(['business', 'member']),
    'routes' => json_encode([
        'integrated' => '/modules/pos',
        'standalone' => '/apps/pos',
    ]),
    'subscription_tiers' => json_encode([
        'free' => ['name' => 'Free', 'price' => 0, 'sales_per_month' => 100],
        'basic' => ['name' => 'Basic', 'price' => 49, 'sales_per_month' => 1000],
        'pro' => ['name' => 'Pro', 'price' => 99, 'sales_per_month' => 'unlimited'],
    ]),
    'requires_subscription' => false,
    'status' => 'active',
]
```

## Integration with Other Modules

### GrowBiz Integration
- POS accessible from GrowBiz dashboard
- Sales linked to GrowBiz employees
- Reports integrated into GrowBiz analytics

### BizBoost Integration
- POS data feeds into customer insights
- Sales history for marketing campaigns
- Product performance analytics

### E-Commerce Integration
- Unified inventory across online/offline
- Order fulfillment from POS
- Stock sync between channels

## Implementation Plan

### Phase 1: Standalone Modules
1. [ ] Create generic POS tables (rename from growbiz_pos_*)
2. [ ] Register POS as standalone module
3. [ ] Update Inventory module status to 'active'
4. [ ] Create standalone routes and controllers

### Phase 2: Integration Layer
1. [ ] Add module_context to sales/inventory tables
2. [ ] Create integration service for cross-module access
3. [ ] Build shared components for Vue

### Phase 3: Module Activation
1. [ ] Add to module marketplace
2. [ ] Implement subscription checks
3. [ ] Create onboarding flow

## File Structure

```
app/
├── Domain/
│   ├── Inventory/
│   │   ├── Entities/
│   │   ├── Services/
│   │   └── Repositories/
│   └── POS/
│       ├── Entities/
│       ├── Services/
│       └── Repositories/
├── Http/Controllers/
│   ├── Inventory/
│   │   └── InventoryController.php
│   └── POS/
│       └── POSController.php
└── Infrastructure/Persistence/Eloquent/
    ├── InventoryItemModel.php
    ├── POSShiftModel.php
    └── POSSaleModel.php

resources/js/Pages/
├── Inventory/
│   ├── Index.vue
│   ├── Items.vue
│   └── Categories.vue
└── POS/
    ├── Terminal.vue
    ├── Shifts.vue
    └── Sales.vue
```

## Access Control

### Standalone Access
Users with module subscription can access directly:
- `/pos/terminal` - POS Terminal
- `/inventory/items` - Inventory Management

### Integrated Access (via parent module)
Users with GrowBiz/BizBoost can access embedded features:
- `/growbiz/pos` - POS within GrowBiz
- `/bizboost/inventory` - Inventory within BizBoost

### Permission Mapping
```php
// Standalone permissions
'pos.access', 'pos.sales.create', 'pos.shifts.manage'
'inventory.access', 'inventory.items.manage', 'inventory.stock.adjust'

// Inherited from parent module
'growbiz.pos' => inherits 'pos.*'
'growbiz.inventory' => inherits 'inventory.*'
```

## Summary

**Recommendation:** Implement Option A (Unified Module) for simplicity, but design the database schema to support future separation if needed.

**Key Principles:**
1. User-scoped data (all tables have `user_id`)
2. Module-agnostic core logic
3. Integration via services, not direct coupling
4. Subscription-based access control
