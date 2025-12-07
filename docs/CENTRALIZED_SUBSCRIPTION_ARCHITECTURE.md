# Centralized Subscription Architecture

**Last Updated:** December 4, 2025
**Status:** Implemented

## Overview

This document outlines the architecture for centralizing subscription management across all MyGrowNet apps (GrowFinance, GrowBiz, and future modules).

## Current State Analysis

### What Exists

1. **Centralized Module Domain** (`app/Domain/Module/`)
   - `ModuleSubscription` entity with full lifecycle management
   - `ModuleSubscriptionService` for subscribe, upgrade, cancel, trial
   - `ModuleAccessService` for access level checks
   - `FeatureAccessService` for feature-level permissions
   - Repository interfaces defined

2. **Duplicate App-Specific Services** (REMOVED)
   - ~~`app/Domain/GrowFinance/Services/SubscriptionService.php`~~ - Deleted
   - ~~`app/Domain/GrowBiz/Services/SubscriptionService.php`~~ - Deleted
   - Both had hardcoded tier limits and duplicate logic

### Problems with Current State

1. **Duplication**: Each app defines its own tier limits, pricing, and feature lists
2. **Inconsistency**: Different patterns for checking limits across apps
3. **Maintenance**: Changes require updates in multiple places
4. **No Single Source of Truth**: Tier configurations scattered across services

## Proposed Architecture

### 1. Centralized Tier Configuration

Create a unified configuration system for all module tiers:

```
config/
├── modules/
│   ├── growfinance.php    # GrowFinance tier config
│   ├── growbiz.php        # GrowBiz tier config
│   └── modules.php        # Shared module registry
```

### 2. Shared Subscription Infrastructure

```
app/Domain/Module/
├── Entities/
│   └── ModuleSubscription.php       # ✅ Already exists
├── Services/
│   ├── ModuleSubscriptionService.php # ✅ Already exists
│   ├── ModuleAccessService.php       # ✅ Already exists
│   ├── FeatureAccessService.php      # ✅ Already exists
│   ├── TierConfigurationService.php  # NEW: Load tier configs
│   └── UsageLimitService.php         # NEW: Centralized limit checking
├── ValueObjects/
│   ├── TierLimits.php               # NEW: Immutable tier limits
│   └── UsageMetrics.php             # NEW: Usage tracking
└── Contracts/
    └── ModuleUsageProviderInterface.php  # NEW: Each app implements
```

### 3. App-Specific Usage Providers

Each app implements a usage provider that knows how to count its resources:

```php
// app/Domain/GrowFinance/Services/GrowFinanceUsageProvider.php
class GrowFinanceUsageProvider implements ModuleUsageProviderInterface
{
    public function getModuleId(): string { return 'growfinance'; }
    
    public function getUsageMetrics(User $user): array
    {
        return [
            'transactions_this_month' => $this->countTransactions($user),
            'invoices_this_month' => $this->countInvoices($user),
            'customers' => $this->countCustomers($user),
            'storage_used_mb' => $this->getStorageUsed($user),
        ];
    }
}
```

### 4. Unified Tier Configuration Format

```php
// config/modules/growfinance.php
return [
    'id' => 'growfinance',
    'name' => 'GrowFinance',
    'tiers' => [
        'free' => [
            'name' => 'Free',
            'price_monthly' => 0,
            'price_annual' => 0,
            'limits' => [
                'transactions_per_month' => 100,
                'invoices_per_month' => 10,
                'customers' => 20,
                'vendors' => 20,
                'bank_accounts' => 1,
                'receipt_storage_mb' => 0,
            ],
            'features' => ['dashboard', 'basic_invoicing', 'basic_expenses'],
            'reports' => ['profit-loss', 'cash-flow'],
        ],
        'basic' => [
            'name' => 'Basic',
            'price_monthly' => 79,
            'price_annual' => 758,
            'limits' => [
                'transactions_per_month' => -1,
                'invoices_per_month' => -1,
                'customers' => -1,
                'vendors' => -1,
                'bank_accounts' => 3,
                'receipt_storage_mb' => 100,
            ],
            'features' => ['dashboard', 'invoicing', 'expenses', 'csv_export', 'receipt_upload'],
            'reports' => ['profit-loss', 'cash-flow', 'balance-sheet', 'trial-balance', 'general-ledger'],
        ],
        // ... professional, business tiers
    ],
];
```

## Implementation Plan

### Phase 1: Create Centralized Infrastructure (Week 1)

1. Create tier configuration files for each module
2. Create `TierConfigurationService` to load configs
3. Create `UsageLimitService` for centralized limit checking
4. Create `ModuleUsageProviderInterface` contract

### Phase 2: Implement Usage Providers (Week 1-2)

1. Create `GrowFinanceUsageProvider`
2. Create `GrowBizUsageProvider`
3. Register providers in service container

### Phase 3: Migrate Existing Services (Week 2)

1. Refactor `GrowFinance\SubscriptionService` to use centralized services
2. Refactor `GrowBiz\SubscriptionService` to use centralized services
3. Update controllers to use new services
4. Deprecate old hardcoded tier configurations

### Phase 4: Database & Repository (Week 2-3)

1. Create/verify `module_subscriptions` table migration
2. Implement `EloquentModuleSubscriptionRepository`
3. Implement `EloquentModuleUsageRepository` for tracking

### Phase 5: Testing & Cleanup (Week 3)

1. Write tests for centralized services
2. Remove deprecated code
3. Update documentation

## Key Benefits

1. **Single Source of Truth**: All tier configs in one place
2. **Easy to Add Modules**: Just add a config file and usage provider
3. **Consistent Patterns**: Same limit-checking logic everywhere
4. **Maintainable**: Change pricing/limits in config, not code
5. **Testable**: Mock usage providers for testing

## Database Schema

```sql
-- Module subscriptions (may already exist)
CREATE TABLE module_subscriptions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    module_id VARCHAR(50) NOT NULL,
    subscription_tier VARCHAR(50) NOT NULL DEFAULT 'free',
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    billing_cycle VARCHAR(20) DEFAULT 'monthly',
    amount DECIMAL(10,2) DEFAULT 0,
    started_at TIMESTAMP NOT NULL,
    trial_ends_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    auto_renew BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY unique_user_module (user_id, module_id),
    INDEX idx_status (status),
    INDEX idx_expires (expires_at)
);

-- Module usage tracking (for analytics)
CREATE TABLE module_usage_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    module_id VARCHAR(50) NOT NULL,
    metric_name VARCHAR(100) NOT NULL,
    metric_value INT NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    created_at TIMESTAMP,
    
    INDEX idx_user_module_period (user_id, module_id, period_start)
);
```

## Questions to Resolve

1. **Pricing Strategy**: Should pricing be in config or database for admin flexibility?
2. **Trial Periods**: Unified trial length or per-module?
3. **Grace Periods**: How long after expiration before restricting access?
4. **Proration**: How to handle mid-cycle upgrades/downgrades?
5. **Bundle Discounts**: Support for multi-module bundles?

## Implementation Status

### ✅ Completed

1. **Centralized Configuration Files**
   - `config/modules/growfinance.php` - GrowFinance tier config
   - `config/modules/growbiz.php` - GrowBiz tier config

2. **Core Services**
   - `app/Domain/Module/Services/TierConfigurationService.php` - Load tier configs
   - `app/Domain/Module/Services/UsageLimitService.php` - Centralized limit checking
   - `app/Domain/Module/Contracts/ModuleUsageProviderInterface.php` - Provider contract

3. **Usage Providers**
   - `app/Domain/GrowFinance/Services/GrowFinanceUsageProvider.php`
   - `app/Domain/GrowBiz/Services/GrowBizUsageProvider.php`

4. **Service Provider**
   - `app/Providers/ModuleSubscriptionServiceProvider.php` - Wires everything together

### ✅ Cleanup Completed

1. **Removed Redundant Files**
   - ~~`app/Domain/GrowFinance/Services/SubscriptionService.php`~~ - Deleted
   - ~~`app/Domain/GrowBiz/Services/SubscriptionService.php`~~ - Deleted

### ✅ Service Provider Registration

1. **Registered in config/app.php** - `ModuleSubscriptionServiceProvider` added to providers array

### ✅ Unified SubscriptionService

1. **Created `app/Domain/Module/Services/SubscriptionService.php`**
   - Facade service providing simple interface for subscription checks
   - Used by both GrowFinance and GrowBiz middleware
   - Delegates to UsageLimitService, TierConfigurationService, and ModuleAccessService

### ✅ Updated Middleware

1. **`app/Http/Middleware/CheckGrowFinanceSubscription.php`**
   - Now uses centralized `SubscriptionService`
   - Adds subscription tier and limits to request

2. **`app/Http/Middleware/CheckGrowBizSubscription.php`**
   - Now uses centralized `SubscriptionService`
   - Consistent error responses with GrowFinance

### ✅ Tests Added

1. **`tests/Unit/Domain/Module/TierConfigurationServiceTest.php`**
   - Tests for tier config loading, limits, features, reports, pricing

2. **`tests/Unit/Domain/Module/UsageLimitServiceTest.php`**
   - Tests for increment checks, feature access, storage limits, upgrade suggestions

3. **`tests/Unit/Domain/Module/SubscriptionServiceTest.php`**
   - Tests for unified subscription service facade

4. **`tests/Unit/Middleware/CheckGrowFinanceSubscriptionTest.php`**
   - Tests for GrowFinance middleware behavior

5. **`tests/Unit/Middleware/CheckGrowBizSubscriptionTest.php`**
   - Tests for GrowBiz middleware behavior

### 🔲 Remaining Tasks

1. **Implement Repository** - Create EloquentModuleSubscriptionRepository
2. **Database Migration** - Create module_subscriptions table if not exists
3. **Update Controllers** - Gradually migrate controllers to use UsageLimitService directly for fine-grained checks

## Architecture Summary

```
┌─────────────────────────────────────────────────────────────────┐
│                        Middleware Layer                          │
│  ┌─────────────────────┐    ┌─────────────────────┐            │
│  │ CheckGrowFinance    │    │ CheckGrowBiz        │            │
│  │ Subscription        │    │ Subscription        │            │
│  └──────────┬──────────┘    └──────────┬──────────┘            │
│             │                          │                        │
│             └──────────┬───────────────┘                        │
│                        ▼                                        │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │              SubscriptionService (Facade)                │   │
│  │  - getUserTier()    - hasFeature()    - canIncrement()  │   │
│  │  - getUserLimits()  - canAccessReport()                 │   │
│  └──────────────────────────┬──────────────────────────────┘   │
└─────────────────────────────┼───────────────────────────────────┘
                              │
┌─────────────────────────────┼───────────────────────────────────┐
│                    Domain Services Layer                         │
│                              ▼                                   │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │                  UsageLimitService                       │   │
│  │  - canIncrement()  - hasFeature()  - getUsageSummary()  │   │
│  └──────────┬────────────────────────────────┬─────────────┘   │
│             │                                │                  │
│             ▼                                ▼                  │
│  ┌─────────────────────┐    ┌─────────────────────────────┐   │
│  │ TierConfiguration   │    │ ModuleAccessService         │   │
│  │ Service             │    │ (determines user tier)      │   │
│  └──────────┬──────────┘    └─────────────────────────────┘   │
│             │                                                   │
│             ▼                                                   │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │              config/modules/*.php                        │   │
│  │  - growfinance.php (tiers, limits, features, pricing)   │   │
│  │  - growbiz.php (tiers, limits, features, pricing)       │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                    Usage Providers Layer                         │
│  ┌─────────────────────┐    ┌─────────────────────┐            │
│  │ GrowFinanceUsage    │    │ GrowBizUsage        │            │
│  │ Provider            │    │ Provider            │            │
│  │ - getMetric()       │    │ - getMetric()       │            │
│  │ - getStorageUsed()  │    │ - getStorageUsed()  │            │
│  └─────────────────────┘    └─────────────────────┘            │
└─────────────────────────────────────────────────────────────────┘
```

## Usage Examples

### In Middleware (automatic)
```php
// Middleware automatically adds to request:
$request->get('subscription_tier');  // 'basic'
$request->get('subscription_limits'); // ['accounts' => 10, ...]
```

### In Controllers
```php
// Check if user can create more items
$result = $this->subscriptionService->canIncrement($user, 'accounts', 'growfinance');
if (!$result['allowed']) {
    return back()->with('error', $result['reason']);
}

// Check feature access
if (!$this->subscriptionService->hasFeature($user, 'api_access', 'growfinance')) {
    return response()->json(['error' => 'Upgrade required'], 403);
}

// Get usage summary for dashboard
$summary = $this->subscriptionService->getUsageSummary($user, 'growfinance');
```

### In Vue Components
```vue
<script setup>
// Subscription info passed via Inertia
const props = defineProps({
    subscriptionTier: String,
    subscriptionLimits: Object,
    usageSummary: Object,
});

// Show upgrade prompt when near limit
const showUpgradePrompt = computed(() => {
    return props.usageSummary?.metrics?.accounts?.percentage >= 80;
});
</script>
```

---

## Appendix: Current Tier Comparison

### GrowFinance Tiers
| Tier | Monthly | Transactions | Invoices | Customers | Storage |
|------|---------|--------------|----------|-----------|---------|
| Free | K0 | 100/mo | 10/mo | 20 | 0 MB |
| Basic | K79 | Unlimited | Unlimited | Unlimited | 100 MB |
| Professional | K199 | Unlimited | Unlimited | Unlimited | 1 GB |
| Business | K399 | Unlimited | Unlimited | Unlimited | 5 GB |

### GrowBiz Tiers
| Tier | Monthly | Tasks | Employees | Templates | Storage |
|------|---------|-------|-----------|-----------|---------|
| Free | K0 | 25/mo | 3 | 0 | 0 MB |
| Basic | K79 | Unlimited | 10 | 5 | 100 MB |
| Professional | K199 | Unlimited | 25 | Unlimited | 1 GB |
| Business | K399 | Unlimited | Unlimited | Unlimited | 5 GB |

### BizBoost Tiers (Implemented)
| Tier | Monthly | Posts | AI Credits | Templates | Storage |
|------|---------|-------|------------|-----------|---------|
| Free | K0 | 10/mo | 10/mo | 5 | 0 MB |
| Basic | K79 | 50/mo | 50/mo | 25 | 100 MB |
| Professional | K199 | Unlimited | 200/mo | Unlimited | 1 GB |
| Business | K399 | Unlimited | Unlimited | Unlimited | 5 GB |

**Implementation Status:** ✅ Complete
- Config: `config/modules/bizboost.php`
- Usage Provider: `app/Domain/BizBoost/Services/BizBoostUsageProvider.php`
- Middleware: `app/Http/Middleware/CheckBizBoostSubscription.php`
- Routes: `routes/bizboost.php`

See [BizBoost Developer Handover](./bizboost/BIZBOOST_DEVELOPER_HANDOVER.md) for full configuration.
