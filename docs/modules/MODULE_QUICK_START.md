# Module System Quick Start

**Last Updated:** December 1, 2025

## Overview

MyGrowNet modules work in two modes:
1. **Integrated** - Within the platform (accessed via Home Hub)
2. **Standalone PWA** - Installable apps that work offline

## Adding a New Module (5 Steps)

### 1. Configure Module

```php
// config/modules.php
'personal-finance' => [
    'id' => 'personal-finance',
    'name' => 'Personal Finance Manager',
    'slug' => 'personal-finance',
    'category' => 'personal', // or 'sme'
    
    // ← IMPORTANT: Specify which account types can access
    'account_types' => ['member', 'client'], // Both MEMBER and CLIENT can access
    
    'price' => 50,
    'billing_cycle' => 'monthly',
    'subscription' => ['growth', 'premium'],
    'pwa' => ['enabled' => true],
],
```

**Account Types:**
- `['member']` - Only MEMBER can access
- `['client']` - Only CLIENT can access
- `['member', 'client']` - Both MEMBER and CLIENT can access
- `['business']` - Only BUSINESS can access
- `['investor']` - Only INVESTOR can access
- `['employee']` - Only EMPLOYEE can access

### 2. Create Domain Logic

```php
// app/Domain/PersonalFinance/Services/FinanceService.php
class FinanceService {
    public function calculateBalance(User $user): Money {
        // Business logic here
    }
}
```

### 3. Create Controller

```php
// app/Http/Controllers/PersonalFinance/FinanceController.php
class FinanceController extends Controller {
    public function __construct() {
        // Middleware checks:
        // 1. User is authenticated
        // 2. User has purchased module OR has allowed account type
        $this->middleware(['auth', 'module.access:personal-finance']);
    }
    
    public function index(Request $request) {
        $user = $request->user();
        
        // Optional: Additional account type check
        if (!$user->hasAccountType(AccountType::MEMBER) && 
            !$user->hasAccountType(AccountType::CLIENT)) {
            abort(403, 'This module requires MEMBER or CLIENT account type.');
        }
        
        return Inertia::render('PersonalFinance/Dashboard');
    }
}
```

### 4. Create Vue Page

```vue
<!-- resources/js/pages/PersonalFinance/Dashboard.vue -->
<script setup lang="ts">
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
</script>

<template>
  <ModuleLayout title="Personal Finance">
    <!-- Your module UI here -->
  </ModuleLayout>
</template>
```

### 5. Register Routes

```php
// routes/web.php

// Module routes - checks account type + subscription
Route::middleware(['auth', 'module.access:personal-finance'])->group(function () {
    Route::get('/modules/finance', [FinanceController::class, 'index']);
});

// Alternative: Protect by account type only
Route::middleware(['auth', 'account.type:member,client'])->group(function () {
    Route::get('/modules/finance', [FinanceController::class, 'index']);
});
```

**Middleware Options:**
- `module.access:slug` - Checks account type + subscription
- `account.type:member,client` - Checks account type only

## Module appears automatically in Home Hub!

## Key Files

- `config/modules.php` - Module registry
- `app/Domain/{Module}/` - Business logic
- `app/Http/Controllers/{Module}/` - Controllers
- `resources/js/pages/{Module}/` - Vue pages
- `database/migrations/` - Module tables

## Subscription Tiers

**Personal Apps:**
- Free: 1 app
- Growth (K50/mo): 3-5 apps
- Premium (K100/mo): All apps

**SME Apps:**
- Starter (K200/mo): 2 tools, 5 users
- Professional (K500/mo): 5 tools, 20 users
- Enterprise (K1000/mo): All tools, unlimited users

## Testing

```bash
# Integrated mode
Visit: /modules/finance

# Standalone PWA
Visit: /apps/finance
Click "Install" button

# Check subscription
Visit: /home-hub
```

## Common Patterns

### Offline Storage
```typescript
// Save data offline
await offlineStorage.save(transaction);

// Sync when online
await offlineStorage.sync();
```

### Access Control
```php
// In controller
$this->middleware(['auth', 'module:personal-finance']);

// Check programmatically
if ($moduleService->hasAccess($user, 'personal-finance')) {
    // Allow access
}
```

### PWA Install
```vue
<PWAInstallPrompt :module="module" />
```

## Next Steps

1. Review [MODULE_SYSTEM_ARCHITECTURE.md](MODULE_SYSTEM_ARCHITECTURE.md)
2. Check existing SME Accounting module as example
3. Start with simple module first
4. Test both integrated and standalone modes
5. Add offline support gradually

## Support

- Architecture: `docs/MODULE_SYSTEM_ARCHITECTURE.md`
- Platform: `docs/MYGROWNET_PLATFORM_CONCEPT.md`
- Home Hub: `HOME_HUB_IMPLEMENTATION.md`
