# Module Freemium Model

**Last Updated:** December 1, 2025  
**Status:** Implementation Guide

---

## Overview

The freemium model allows users to access basic features of a module for free, while premium features require a paid subscription. This encourages user adoption while providing a clear upgrade path.

---

## Freemium Tiers

### Tier Structure

```
┌─────────────────────────────────────────────────────────────┐
│                        FREE TIER                            │
│  - Basic features                                           │
│  - Limited usage (e.g., 5 transactions/month)               │
│  - Ads or branding                                          │
│  - Community support                                        │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                      BASIC TIER (K50/mo)                    │
│  - All free features                                        │
│  - Increased limits (e.g., 50 transactions/month)           │
│  - No ads                                                   │
│  - Email support                                            │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                    PREMIUM TIER (K150/mo)                   │
│  - All basic features                                       │
│  - Unlimited usage                                          │
│  - Advanced features                                        │
│  - Priority support                                         │
│  - Data export                                              │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                   BUSINESS TIER (K500/mo)                   │
│  - All premium features                                     │
│  - Multi-user access                                        │
│  - API access                                               │
│  - Custom branding                                          │
│  - Dedicated support                                        │
└─────────────────────────────────────────────────────────────┘
```

---

## Feature Gating

### How Features Are Gated

1. **Feature Flags** - Each feature has a flag indicating which tier(s) can access it
2. **Usage Limits** - Free tier has usage limits (transactions, storage, etc.)
3. **Time Limits** - Some features available for limited time on free tier
4. **Quality Limits** - Free tier may have reduced quality (e.g., lower resolution exports)

### Feature Configuration Example

```php
'features' => [
    'basic_dashboard' => ['free', 'basic', 'premium', 'business'],
    'transaction_history' => ['free', 'basic', 'premium', 'business'],
    'export_csv' => ['basic', 'premium', 'business'],
    'export_pdf' => ['premium', 'business'],
    'advanced_reports' => ['premium', 'business'],
    'api_access' => ['business'],
    'multi_user' => ['business'],
    'custom_branding' => ['business'],
],
'limits' => [
    'free' => [
        'transactions_per_month' => 5,
        'storage_mb' => 50,
        'team_members' => 1,
    ],
    'basic' => [
        'transactions_per_month' => 50,
        'storage_mb' => 500,
        'team_members' => 1,
    ],
    'premium' => [
        'transactions_per_month' => -1, // unlimited
        'storage_mb' => 5000,
        'team_members' => 1,
    ],
    'business' => [
        'transactions_per_month' => -1,
        'storage_mb' => 50000,
        'team_members' => 10,
    ],
]
```

---

## Implementation Approach

### 1. Update ModuleConfiguration Value Object

Add freemium-specific configuration:

```php
class ModuleConfiguration
{
    // Existing properties...
    
    private readonly bool $hasFreeTier;
    private readonly array $freeTierFeatures;
    private readonly array $freeTierLimits;
    
    public function hasFreeTier(): bool
    {
        return $this->hasFreeTier;
    }
    
    public function getFreeTierFeatures(): array
    {
        return $this->freeTierFeatures;
    }
    
    public function getFreeTierLimits(): array
    {
        return $this->freeTierLimits;
    }
}
```

### 2. Update Module Entity

Add freemium methods:

```php
class Module
{
    public function hasFreeTier(): bool
    {
        return $this->configuration->hasFreeTier();
    }
    
    public function getFreeTierFeatures(): array
    {
        return $this->configuration->getFreeTierFeatures();
    }
    
    public function getFreeTierLimits(): array
    {
        return $this->configuration->getFreeTierLimits();
    }
}
```

### 3. Update ModuleAccessService

Modify access logic to handle freemium:

```php
public function canAccess(User $user, ModuleId $moduleId): bool
{
    $module = $this->moduleRepository->findById($moduleId);
    
    // ... existing checks ...
    
    // If module has free tier, grant basic access
    if ($module->hasFreeTier()) {
        return true;
    }
    
    // If module requires subscription, check for active subscription
    if ($module->requiresSubscription()) {
        return $this->hasActiveSubscription($user->id, $moduleId);
    }
    
    return true;
}

public function getAccessLevel(User $user, ModuleId $moduleId): string
{
    $subscription = $this->subscriptionRepository->findByUserAndModule($user->id, $moduleId);
    
    if ($subscription && $subscription->isActive()) {
        return $subscription->getTier();
    }
    
    $module = $this->moduleRepository->findById($moduleId);
    
    if ($module && $module->hasFreeTier()) {
        return 'free';
    }
    
    return 'none';
}
```

### 4. Create Feature Access Service

New service to check feature-level access:

```php
class FeatureAccessService
{
    public function canAccessFeature(User $user, string $moduleId, string $feature): bool
    {
        $accessLevel = $this->moduleAccessService->getAccessLevel($user, $moduleId);
        $module = $this->moduleRepository->findById($moduleId);
        
        $featureConfig = $module->getConfiguration()->getFeatures();
        $allowedTiers = $featureConfig[$feature] ?? [];
        
        return in_array($accessLevel, $allowedTiers);
    }
    
    public function getRemainingLimit(User $user, string $moduleId, string $limitType): int
    {
        $accessLevel = $this->moduleAccessService->getAccessLevel($user, $moduleId);
        $module = $this->moduleRepository->findById($moduleId);
        
        $limits = $module->getConfiguration()->getLimits()[$accessLevel] ?? [];
        $limit = $limits[$limitType] ?? 0;
        
        if ($limit === -1) {
            return PHP_INT_MAX; // Unlimited
        }
        
        $used = $this->usageRepository->getUsage($user->id, $moduleId, $limitType);
        
        return max(0, $limit - $used);
    }
}
```

---

## Database Changes

### Add Usage Tracking Table

```sql
CREATE TABLE module_usage (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    module_id VARCHAR(50) NOT NULL,
    usage_type VARCHAR(50) NOT NULL,
    count INT DEFAULT 0,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_usage (user_id, module_id, usage_type, period_start),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## UI/UX Considerations

### Free Tier Indicators

1. **Badge** - Show "Free" badge on module tile
2. **Limits Display** - Show remaining usage (e.g., "3/5 transactions used")
3. **Upgrade Prompts** - Show upgrade prompts when approaching limits
4. **Feature Lock Icons** - Show lock icons on premium features

### Upgrade Flow

1. User clicks on locked feature
2. Modal shows feature benefits and pricing
3. User selects tier
4. Payment processing
5. Immediate access to features

---

## Example Module Configuration

### MyGrow Save (Freemium)

```php
[
    'id' => 'mygrow-save',
    'name' => 'MyGrow Save',
    'has_free_tier' => true,
    'subscription_tiers' => [
        'free' => [
            'name' => 'Free',
            'price' => 0,
            'features' => [
                'basic_savings_tracking',
                'manual_transactions',
                'basic_reports',
            ],
            'limits' => [
                'transactions_per_month' => 10,
                'savings_goals' => 2,
            ],
        ],
        'basic' => [
            'name' => 'Basic',
            'price' => 50,
            'currency' => 'ZMW',
            'billing_cycle' => 'monthly',
            'features' => [
                'basic_savings_tracking',
                'manual_transactions',
                'basic_reports',
                'export_csv',
                'email_reminders',
            ],
            'limits' => [
                'transactions_per_month' => 100,
                'savings_goals' => 10,
            ],
        ],
        'premium' => [
            'name' => 'Premium',
            'price' => 150,
            'currency' => 'ZMW',
            'billing_cycle' => 'monthly',
            'features' => [
                'all_basic_features',
                'advanced_reports',
                'export_pdf',
                'auto_categorization',
                'budget_tracking',
                'priority_support',
            ],
            'limits' => [
                'transactions_per_month' => -1, // unlimited
                'savings_goals' => -1,
            ],
        ],
    ],
]
```

---

## Implementation Steps

### Phase 1: Core Changes
1. ✅ Update ModuleConfiguration value object
2. ✅ Update Module entity
3. ✅ Update ModuleAccessService
4. ✅ Create FeatureAccessService

### Phase 2: Database
1. Create module_usage migration
2. Create ModuleUsage model
3. Create UsageRepository

### Phase 3: Application Layer
1. Create CheckFeatureAccessUseCase
2. Create TrackUsageUseCase
3. Create GetUsageSummaryUseCase

### Phase 4: Presentation Layer
1. Update ModuleTile to show free tier badge
2. Create UsageLimitIndicator component
3. Create FeatureGate component
4. Update SubscriptionModal for freemium

### Phase 5: Testing
1. Test free tier access
2. Test usage limits
3. Test feature gating
4. Test upgrade flow

---

## API Endpoints

### Check Feature Access

```
GET /api/modules/{moduleId}/features/{feature}/access
Response: { "has_access": true, "tier_required": "basic" }
```

### Get Usage Summary

```
GET /api/modules/{moduleId}/usage
Response: {
    "transactions": { "used": 3, "limit": 10, "remaining": 7 },
    "savings_goals": { "used": 1, "limit": 2, "remaining": 1 }
}
```

### Track Usage

```
POST /api/modules/{moduleId}/usage
Body: { "type": "transaction", "count": 1 }
Response: { "success": true, "remaining": 6 }
```

---

## Benefits

### For Users
- Try before you buy
- No commitment required
- Clear upgrade path
- Value demonstration

### For Business
- Lower barrier to entry
- Higher conversion rates
- Usage data for optimization
- Upsell opportunities

---

## Next Steps

1. Implement core changes to domain layer
2. Create database migration for usage tracking
3. Update UI components
4. Test freemium flow
5. Monitor conversion metrics

---

**Status:** Ready for Implementation 🚀

