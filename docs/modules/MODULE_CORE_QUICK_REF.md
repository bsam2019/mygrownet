# MyGrowNet Core Module - Quick Reference

**Last Updated:** December 1, 2025

## What is MyGrowNet Core?

The **existing MLM/community growth dashboard** - now treated as the foundational module.

## Quick Facts

| Aspect | Details |
|--------|---------|
| **Module ID** | `mygrownet-core` |
| **Access** | All members (no subscription required) |
| **Routes** | `/dashboard`, `/mobile-dashboard` |
| **Status** | Production (existing system) |
| **Account Type** | `member` (default) |

## Core vs Other Modules

```
┌─────────────────────────────────────┐
│     MyGrowNet Core (MLM)            │
│  • FREE for all members             │
│  • Referrals, commissions, team     │
│  • 7-level progression              │
│  • Points system (LP/MAP)           │
│  • Profit-sharing                   │
└─────────────────────────────────────┘
              ↓
    Members can ADD modules:
              ↓
┌──────────┐ ┌──────────┐ ┌──────────┐
│   SME    │ │ Investor │ │ Personal │
│  Tools   │ │  Portal  │ │ Finance  │
│ (Paid)   │ │ (Paid)   │ │ (Paid)   │
└──────────┘ └──────────┘ └──────────┘
```

## Home Hub Display

```
Your Dashboard
┌────────────────────────────────────┐
│ 🌱 MyGrowNet Core                  │
│ Community growth & earnings        │
│ [Open Dashboard →]                 │
└────────────────────────────────────┘

Your Apps
┌──────────┐ ┌──────────┐ ┌──────────┐
│ SME      │ │ Investor │ │ Finance  │
│ [Open]   │ │ [Open]   │ │ [Open]   │
└──────────┘ └──────────┘ └──────────┘

Discover More
┌──────────┐ ┌──────────┐ ┌──────────┐
│Inventory │ │  Goals   │ │   CRM    │
│[Subscribe│ │[Subscribe│ │[Subscribe│
└──────────┘ └──────────┘ └──────────┘
```

## Access Control

```php
// Core is always accessible
if ($moduleId === 'mygrownet-core') {
    return true;  // No subscription check
}

// Other modules require subscription
return $this->hasActiveSubscription($user, $moduleId);
```

## Account Types

| Type | Core Access | Additional Access |
|------|-------------|-------------------|
| `member` | ✅ Yes | None (can subscribe) |
| `sme` | ✅ Yes | SME tools |
| `investor` | ✅ Yes | Investor portal |
| `employee` | ✅ Yes | Employee portal |
| `admin` | ✅ Yes | All modules |

**Key Point:** Everyone gets Core. Account type determines ADDITIONAL modules.

## Domain Structure

```
app/Domain/
├── MyGrowNet/          # Core MLM (existing logic)
│   ├── Entities/
│   ├── Services/
│   └── Repositories/
├── SME/                # SME tools (new)
├── Investor/           # Investor portal (new)
└── PersonalFinance/    # Personal finance (new)
```

## Routes

### Core Routes (Existing)
```php
/dashboard              → Desktop dashboard
/mobile-dashboard       → Mobile dashboard
/team                   → Team management
/earnings               → Earnings tracking
/apps/core              → Optional PWA mode
```

### Module Routes (New)
```php
/modules/accounting     → SME Accounting
/modules/investor       → Investor Portal
/modules/finance        → Personal Finance
```

## Config Entry

```php
// config/modules.php
'mygrownet-core' => [
    'id' => 'mygrownet-core',
    'name' => 'MyGrowNet Core',
    'category' => 'core',
    'required_subscription' => [],  // No subscription needed
    'default_access' => true,       // All users have access
    'routes' => [
        'integrated' => '/dashboard',
        'mobile' => '/mobile-dashboard',
    ],
],
```

## User Journey

### New Member
```
Register → Login → /dashboard (Core)
                      ↓
              Use Core features
                      ↓
         Optional: Subscribe to modules
```

### Member with Modules
```
Login → Home Hub
          ↓
    ┌─────┴─────┐
    ↓           ↓
  Core      Subscribed
(Always)     Modules
```

## Key Features (Core)

- ✅ 7-level professional progression
- ✅ 3×3 forced matrix
- ✅ Referral commissions
- ✅ Profit-sharing
- ✅ Points system (LP/MAP)
- ✅ Team network visualization
- ✅ Earnings calculator
- ✅ Withdrawal management
- ✅ Learning resources

## Migration Checklist

- [ ] Add Core to `config/modules.php`
- [ ] Update Home Hub to show Core
- [ ] Update ModuleService for Core access
- [ ] Create `app/Domain/MyGrowNet/` structure
- [ ] Update documentation
- [ ] Test integration
- [ ] Deploy

## Remember

1. **Core = Existing Dashboard** - No changes to functionality
2. **All Members Get Core** - No subscription required
3. **Modules are ADDITIONS** - Not replacements
4. **Same Architecture** - Core follows same patterns as modules
5. **Backward Compatible** - Existing routes still work

## Quick Commands

```bash
# Check Core module config
php artisan tinker
>>> config('modules.mygrownet-core')

# Test Core access
>>> app(ModuleService::class)->hasAccess($user, 'mygrownet-core')

# View Core routes
php artisan route:list --name=dashboard
```

## Documentation

- **Full Details:** [MODULE_CORE_MLM_INTEGRATION.md](MODULE_CORE_MLM_INTEGRATION.md)
- **Architecture:** [MODULE_SYSTEM_ARCHITECTURE.md](MODULE_SYSTEM_ARCHITECTURE.md)
- **Quick Start:** [MODULE_QUICK_START.md](MODULE_QUICK_START.md)

---

**TL;DR:** The existing dashboard is now "MyGrowNet Core" - the free foundational module that all members get. Other modules are paid add-ons.
