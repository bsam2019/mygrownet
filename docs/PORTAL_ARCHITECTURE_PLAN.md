# Portal Architecture Plan

**Last Updated:** December 1, 2025
**Status:** Planning Phase

## Executive Summary

MyGrowNet will have **5 distinct user types** with different portal access patterns. This document defines how each portal integrates with the modular system and clarifies the critical distinction between:
- **MLM Members** - Subject to MLM rules, commissions, network building
- **Non-MLM Users** - App clients, investors, employees, business owners (NO MLM participation)

---

## The 5 User Types & Their Portals

### 1. MEMBER (MLM Participant)
**Portal:** MLM Dashboard (Core Module)
**Access:** FREE with membership subscription (K50/month)
**MLM Rules:** ✅ YES

**What they get:**
- Network visualizer and team management
- Commission tracking and earnings
- Training and resources
- Profit-sharing eligibility
- Can purchase additional modules at member discount

**Portal Integration:**
- Core module in Home Hub
- Direct routes: `/dashboard`, `/mobile-dashboard`
- Foundation for modular system

---

### 2. CLIENT (App/Shop User)
**Portal:** Home Hub (Purchased Modules Only)
**Access:** Per-module subscription (K50-100/month per app)
**MLM Rules:** ❌ NO

**What they get:**
- Access to purchased modules only
- Marketplace shopping
- MyGrow Save Wallet
- NO network building, NO commissions, NO profit-sharing

**Portal Integration:**
- Home Hub shows only purchased modules
- No Core module (not an MLM member)
- Can shop without MLM participation

**Critical:** Clients are NOT MLM members. They:
- Don't build networks
- Don't earn commissions
- Don't qualify for profit-sharing
- Just use apps they purchase

---

### 3. BUSINESS (SME Owner)
**Portal:** Business Tools Suite
**Access:** Subscription (K200-1000/month based on tier)
**MLM Rules:** ❌ NO (unless also MEMBER)

**What they get:**
- Accounting system
- Task management
- Staff management
- Inventory (future)
- CRM (future)

**Portal Integration:**
- Business modules in Home Hub
- Separate from MLM system
- Can be BUSINESS only OR MEMBER + BUSINESS

**Critical:** Business operations are separate from MLM:
- Business subscription ≠ MLM membership
- Can run SME without MLM participation
- If also MEMBER, both portals accessible

---

### 4. INVESTOR (Venture Builder Participant)
**Portal:** Investor Portal (Standalone)
**Access:** Investment-based (minimum K5,000 per project)
**MLM Rules:** ❌ NO (unless also MEMBER)

**What they get:**
- Portfolio dashboard
- Project details and documents
- Dividend tracking
- Shareholder rights
- Voting on project decisions

**Portal Integration:**
```
RECOMMENDATION: Keep Investor Portal SEPARATE from modular system

Why?
1. Investment portal is fundamentally different from "apps"
2. Investors need professional, dedicated interface
3. Access is investment-based, not subscription-based
4. Mixing with apps dilutes investment experience
5. Different security and compliance requirements

Implementation:
- Standalone portal: /investor/dashboard
- Separate top-level navigation item
- NOT shown in Home Hub module tiles
- Direct access when user has INVESTOR account type
```

**Critical:** Investors are NOT automatically MLM members:
- Investment returns ≠ MLM commissions
- Can invest without MLM participation
- If also MEMBER, both portals accessible
- Investment and MLM tracked separately

---

### 5. EMPLOYEE (Internal Staff)
**Portal:** Employee Portal (Standalone)
**Access:** Employment-based (internal accounts)
**MLM Rules:** ❌ NO

**What they get:**
- Task dashboard and performance tracking
- Live chat interface (customer support)
- Admin tools (role-based)
- Operations management

**Portal Integration:**
```
RECOMMENDATION: Keep Employee Portal SEPARATE from modular system

Why?
1. Internal operations, not customer-facing
2. Different UX and security requirements
3. Role-based access control
4. No billing - internal accounts
5. Separate navigation structure

Implementation:
- Standalone portal: /employee/dashboard
- Separate admin navigation
- NOT shown in Home Hub
- Only accessible to EMPLOYEE account type
```

**Critical:** Employees are internal staff:
- No billing or subscriptions
- Created by HR/admin
- Not customers
- Separate from all customer features

---

## Portal Architecture Decision Matrix

| Portal | Integration | Shown in Home Hub? | Subscription? | MLM Rules? |
|--------|-------------|-------------------|---------------|------------|
| **MLM Dashboard** | Core Module | ✅ Yes (MEMBERS) | ✅ Yes (K50/month) | ✅ YES |
| **Purchased Apps** | Modules | ✅ Yes (CLIENTS/MEMBERS) | ✅ Yes (per app) | ❌ NO |
| **Business Tools** | Module Suite | ✅ Yes (BUSINESS) | ✅ Yes (K200-1000/month) | ❌ NO |
| **Investor Portal** | Standalone | ❌ No | ❌ No (investment-based) | ❌ NO |
| **Employee Portal** | Standalone | ❌ No | ❌ No (internal) | ❌ NO |
| **Marketplace** | Shared Feature | ❌ No (main nav) | ❌ No | ❌ NO |

---

## Navigation Structure

### For MEMBER Users
```
Top Navigation:
- Home Hub (shows Core + purchased modules)
- Marketplace
- [Investor Portal] (if also INVESTOR)
- Profile

Home Hub Tiles:
- Core (MLM Dashboard) - FREE
- [Purchased modules] (if any)
```

### For CLIENT Users
```
Top Navigation:
- Home Hub (shows purchased modules only)
- Marketplace
- [Investor Portal] (if also INVESTOR)
- Profile

Home Hub Tiles:
- [Purchased modules only]
- [Empty state if no modules purchased]
```

### For BUSINESS Users
```
Top Navigation:
- Home Hub (shows business modules)
- Marketplace
- [Investor Portal] (if also INVESTOR)
- Profile

Home Hub Tiles:
- Accounting
- Tasks
- Staff Management
- [Other business modules]
- [Core module if also MEMBER]
```

### For INVESTOR Users (not MEMBER)
```
Top Navigation:
- Investor Portal (main dashboard)
- Marketplace
- Profile

No Home Hub access (not using apps)
```

### For EMPLOYEE Users
```
Top Navigation:
- Employee Portal (main dashboard)
- Admin Tools (role-based)
- Profile

No Home Hub, no Marketplace (internal only)
```

---

## Multi-Account Type Scenarios

### Scenario 1: MEMBER + INVESTOR
**User is:** MLM member who also co-invests in projects

**Access:**
- Home Hub with Core module (MLM Dashboard)
- Investor Portal link in top nav
- Both portals accessible
- MLM commissions + investment returns (tracked separately)

**Navigation:**
```
Top Nav: [Home Hub] [Investor Portal] [Marketplace] [Profile]

Home Hub shows:
- Core (MLM Dashboard)
- [Purchased modules]

Investor Portal shows:
- Portfolio
- Projects
- Returns
```

---

### Scenario 2: CLIENT + INVESTOR
**User is:** App user who also co-invests (NO MLM participation)

**Access:**
- Home Hub with purchased modules only
- Investor Portal link in top nav
- NO MLM features at all
- Investment returns only (no commissions)

**Navigation:**
```
Top Nav: [Home Hub] [Investor Portal] [Marketplace] [Profile]

Home Hub shows:
- [Purchased modules only]
- NO Core module (not MLM member)

Investor Portal shows:
- Portfolio
- Projects
- Returns
```

**Critical:** This user is NOT an MLM member:
- No network building
- No commissions
- No profit-sharing
- Just uses apps + invests

---

### Scenario 3: MEMBER + BUSINESS
**User is:** MLM member who also runs an SME

**Access:**
- Home Hub with Core + business modules
- Both MLM and business features
- MLM commissions + business operations (tracked separately)

**Navigation:**
```
Top Nav: [Home Hub] [Marketplace] [Profile]

Home Hub shows:
- Core (MLM Dashboard)
- Accounting
- Tasks
- Staff Management
- [Other business modules]
```

---

### Scenario 4: BUSINESS only
**User is:** SME owner (NO MLM participation)

**Access:**
- Home Hub with business modules only
- NO MLM features
- Business subscription only

**Navigation:**
```
Top Nav: [Home Hub] [Marketplace] [Profile]

Home Hub shows:
- Accounting
- Tasks
- Staff Management
- NO Core module (not MLM member)
```

**Critical:** This user is NOT an MLM member:
- No network building
- No commissions
- No profit-sharing
- Just uses business tools

---

## Implementation Recommendations

### 1. Investor Portal - Keep Standalone ✅

**Rationale:**
- Investment is a serious financial commitment (K5,000+)
- Investors need professional, dedicated interface
- Different from "buying an app"
- Compliance and legal requirements
- Shareholder rights and governance

**Implementation:**
```php
// Separate route group
Route::middleware(['auth', 'account.type:investor,member'])->prefix('investor')->group(function () {
    Route::get('/dashboard', [InvestorController::class, 'dashboard']);
    Route::get('/portfolio', [InvestorController::class, 'portfolio']);
    Route::get('/projects', [InvestorController::class, 'projects']);
    Route::get('/documents', [InvestorController::class, 'documents']);
    Route::get('/returns', [InvestorController::class, 'returns']);
});

// Navigation: Show "Investor Portal" link in top nav if user has INVESTOR account type
```

**NOT in Home Hub:**
- Investor Portal is not a "module"
- Not shown as a tile
- Separate top-level navigation item

---

### 2. Employee Portal - Keep Standalone ✅

**Rationale:**
- Internal operations, not customer-facing
- Different security requirements
- Role-based access control
- No billing or subscriptions
- Separate navigation structure

**Implementation:**
```php
// Separate route group
Route::middleware(['auth', 'account.type:employee'])->prefix('employee')->group(function () {
    Route::get('/dashboard', [EmployeeController::class, 'dashboard']);
    Route::get('/tasks', [EmployeeController::class, 'tasks']);
    Route::get('/live-chat', [LiveChatController::class, 'index']);
    
    // Admin tools (role-based)
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        // Admin routes
    });
});

// Navigation: Separate employee navigation structure
```

**NOT in Home Hub:**
- Employee Portal is not a "module"
- Internal staff interface
- Separate from customer features

---

### 3. Business Tools - Module Suite ✅

**Rationale:**
- Subscription-based like other modules
- Benefits from Home Hub integration
- Can sell individual modules or bundles
- Easier to add new business tools

**Implementation:**
```php
// Business modules in config/modules.php
'accounting' => [
    'name' => 'Accounting System',
    'account_types' => ['business'],
    'subscription_required' => true,
    'price' => 200, // Part of business suite
],
'tasks' => [
    'name' => 'Task Management',
    'account_types' => ['business'],
    'subscription_required' => true,
    'price' => 0, // Included in business suite
],

// Shown in Home Hub for BUSINESS account type
```

**IN Home Hub:**
- Business modules shown as tiles
- Integrated experience
- Part of modular system

---

## Database Schema Implications

### Users Table
```sql
users (
  id,
  name,
  email,
  account_types JSON, -- ['member', 'investor'] for multi-type
  ...
)
```

### Account Type Checks
```php
// Check if user has account type
$user->hasAccountType(AccountType::MEMBER); // true/false

// Check if MLM rules apply
$user->isMLMParticipant(); // true ONLY for MEMBER

// Check if user can access investor portal
$user->hasAccountType(AccountType::INVESTOR); // true/false

// Check if user is internal staff
$user->hasAccountType(AccountType::EMPLOYEE); // true/false
```

### Separate Data Tables

**MLM Data (only for MEMBERS):**
```sql
user_investments (user_id, tier_id, ...) -- Only MEMBERS
commissions (user_id, amount, ...) -- Only MEMBERS
matrix_positions (user_id, position, ...) -- Only MEMBERS
```

**Investment Data (only for INVESTORS):**
```sql
venture_investments (user_id, project_id, amount, ...) -- Only INVESTORS
venture_shareholders (user_id, project_id, shares, ...) -- Only INVESTORS
```

**Business Data (only for BUSINESS):**
```sql
business_profiles (user_id, business_name, ...) -- Only BUSINESS
accounting_entries (business_id, ...) -- Only BUSINESS
employee_tasks (business_id, ...) -- Only BUSINESS
```

---

## Key Takeaways

### 1. Only MEMBERS Participate in MLM
- MEMBER account type = MLM participation
- All other types = NO MLM rules
- Clear separation prevents confusion

### 2. Investor Portal is Standalone
- Not a "module" in Home Hub
- Professional, dedicated interface
- Investment-based access
- Separate from app subscriptions

### 3. Employee Portal is Standalone
- Internal staff only
- Not customer-facing
- Separate navigation
- No billing

### 4. Business Tools are Modular
- Subscription-based
- Shown in Home Hub
- Part of modular system
- Can be bundled or individual

### 5. Multi-Account Types Supported
- Users can have multiple types
- Each type tracked separately
- Clear access control
- No confusion between systems

---

## Next Steps

1. **Update AccountType enum** - Add INVESTOR and EMPLOYEE ✅ (Done)
2. **Update User model** - Support multiple account types
3. **Create middleware** - Check account type access
4. **Implement Investor Portal** - Standalone routes and UI
5. **Implement Employee Portal** - Standalone routes and UI
6. **Update Home Hub** - Show correct modules per account type
7. **Update navigation** - Show correct links per account type
8. **Testing** - Verify access control for all scenarios

---

## Questions & Answers

**Q: Can a CLIENT become a MEMBER later?**
A: Yes, they can upgrade by paying registration fee and starting MLM subscription.

**Q: Can an INVESTOR also be a MEMBER?**
A: Yes, they can have both account types. MLM and investment tracked separately.

**Q: Do BUSINESS users participate in MLM?**
A: No, unless they also have MEMBER account type. Business subscription ≠ MLM membership.

**Q: Can EMPLOYEES also be MEMBERS?**
A: Technically yes, but typically kept separate. Employee account for work, personal account for MLM.

**Q: How do we prevent MLM rules from applying to non-MEMBERS?**
A: Check `$user->isMLMParticipant()` before any MLM logic. Only MEMBER account type returns true.

---

**Last Updated:** December 1, 2025
**Status:** Planning Complete - Ready for Implementation
