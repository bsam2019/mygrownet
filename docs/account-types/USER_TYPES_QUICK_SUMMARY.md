# User Types - Quick Summary

**Last Updated:** December 1, 2025

## The 5 User Types

### 1. 👥 MEMBER (MLM Participant)
- **What:** Full MLM member with network building
- **Pays:** K150 registration + K50/month subscription
- **Gets:** MLM dashboard, commissions, profit-sharing, training
- **MLM Rules:** ✅ YES - tracked for network, points, bonuses

### 2. 🛍️ CLIENT (App/Shop User)
- **What:** Purchases specific apps or shops without MLM
- **Pays:** Per-module subscription (e.g., K50/month per app)
- **Gets:** Purchased modules only + marketplace access
- **MLM Rules:** ❌ NO - not tracked, no commissions, no network

### 3. 🏢 BUSINESS (SME Owner)
- **What:** Uses business tools (accounting, tasks, staff)
- **Pays:** K200-1000/month based on tier
- **Gets:** Business tools suite
- **MLM Rules:** ❌ NO (unless also a MEMBER)

### 4. 💼 INVESTOR (Venture Builder)
- **What:** Co-invests in Venture Builder projects
- **Pays:** Minimum K5,000 per project investment
- **Gets:** Investor portal, dividends, shareholder rights
- **MLM Rules:** ❌ NO (unless also a MEMBER)

### 5. 👔 EMPLOYEE (Internal Staff)
- **What:** MyGrowNet staff managing operations
- **Pays:** Nothing - internal account
- **Gets:** Employee portal, live chat, admin tools
- **MLM Rules:** ❌ NO - internal operations only

---

## Key Differences

### Who Participates in MLM?
**ONLY MEMBERS** - Everyone else is exempt from MLM rules.

### Who Can Buy Apps?
**MEMBERS and CLIENTS** - Both can purchase modular apps, but:
- Members get 20% discount
- Clients pay full price

### Who Can Shop?
**MEMBERS, CLIENTS, and BUSINESSES** - Marketplace is open to all.

### Who Can Invest in Ventures?
**MEMBERS, CLIENTS, and INVESTORS** - Anyone can become an investor.

### Who Has Admin Access?
**ONLY EMPLOYEES** - Internal staff with role-based permissions.

---

## Multi-Account Types

Users can have multiple account types:

**Example 1:** MEMBER + INVESTOR
- Earns MLM commissions from network
- Receives investment dividends from Venture Builder
- Both tracked separately

**Example 2:** CLIENT + INVESTOR
- Purchases apps without MLM participation
- Co-invests in projects
- No MLM tracking at all

**Example 3:** MEMBER + BUSINESS
- Participates in MLM network
- Uses business tools for SME
- MLM and business operations separate

---

## Portal Access

| Portal | MEMBER | CLIENT | BUSINESS | INVESTOR | EMPLOYEE |
|--------|--------|--------|----------|----------|----------|
| MLM Dashboard | ✅ | ❌ | ❌ | ❌ | ❌ |
| Marketplace | ✅ | ✅ | ✅ | ❌ | ❌ |
| Investor Portal | ❌* | ❌* | ❌* | ✅ | ❌ |
| Business Tools | ❌* | ❌ | ✅ | ❌ | ❌ |
| Employee Portal | ❌ | ❌ | ❌ | ❌ | ✅ |

*Can access if they also have that account type

---

## Implementation

### Database
```php
// Users can have multiple account types
$user->account_types = ['member', 'investor']; // JSON array
```

### Middleware
```php
// Check account type
Route::middleware(['auth', 'account.type:member'])->group(function () {
    // MLM routes only
});

Route::middleware(['auth', 'account.type:client,member'])->group(function () {
    // Module routes for clients and members
});
```

### Access Control
```php
// Check if user has account type
$user->hasAccountType(AccountType::MEMBER); // true/false

// Check if MLM rules apply
$user->isMLMParticipant(); // true only for MEMBER

// Get available modules
$user->getAvailableModules(); // Merges from all account types
```

---

## Quick Decision Tree

**Is the user building a network and earning commissions?**
- YES → MEMBER
- NO → Continue...

**Is the user just buying apps or shopping?**
- YES → CLIENT
- NO → Continue...

**Is the user running an SME and needs business tools?**
- YES → BUSINESS
- NO → Continue...

**Is the user co-investing in Venture Builder projects?**
- YES → INVESTOR
- NO → Continue...

**Is the user MyGrowNet staff?**
- YES → EMPLOYEE

---

## For Complete Details

See: `docs/USER_TYPES_AND_ACCESS_MODEL.md`

This document covers:
- Detailed characteristics of each type
- Complete access control matrix
- Billing models and pricing
- Portal routing
- Registration flows
- Database schema
- Middleware implementation
- Upgrade paths

---

**Remember:** Only MEMBERS participate in MLM. Everyone else is exempt from network building, commissions, and MLM rules.
