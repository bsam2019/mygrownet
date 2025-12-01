# User Types & Access Model

**Last Updated:** December 1, 2025

## Overview

MyGrowNet supports multiple user types with distinct access patterns, billing models, and feature sets. This document defines how each user type is handled in the modular system.

**Important:** This document covers **Account Types** (business classification). For information on **Roles** (access control via Spatie Permission), see `docs/ACCOUNT_TYPES_VS_ROLES.md`.

### Quick Distinction
- **Account Type** = Business relationship (billing, MLM participation)
- **Role** = Access control (permissions, features)
- Both work together but serve different purposes

---

## User Type Definitions

### 1. MEMBER (MLM Participant)
**Account Type:** `member`

**Characteristics:**
- Pays registration fee + monthly/annual subscription
- Subject to full MLM rules: commissions, levels, points, network building
- Access to member dashboard with network visualizer, earnings, team management
- Eligible for profit-sharing from company projects
- Can purchase additional modules (apps) at member pricing

**Access:**
- MLM Dashboard (network, earnings, team)
- Training & Resources
- Marketplace (shop)
- Venture Builder (optional co-investment)
- MyGrow Save Wallet
- Profile & Settings

**Billing:**
- Registration: One-time fee
- Subscription: Monthly/Annual recurring
- Module purchases: Optional add-ons at member rates

**MLM Rules Apply:** ✅ YES
- Tracked for network building
- Earns commissions and bonuses
- Subject to activity points (MAP/LP)
- Qualifies for profit-sharing

---

### 2. CLIENT (App/Shop User)
**Account Type:** `client`

**Characteristics:**
- Purchases specific apps/modules standalone
- **NOT** an MLM participant - no network, no commissions, no levels
- Access only to purchased modules + basic profile
- Can shop in marketplace without MLM membership
- May upgrade to MEMBER status later

**Access:**
- Marketplace (shop) - browse and purchase
- Purchased modules only (e.g., Wedding Planner, Business Plan Generator)
- Venture Builder (if they choose to co-invest)
- MyGrow Save Wallet (for transactions)
- Profile & Settings

**Billing:**
- Per-module subscription OR one-time purchase
- No registration fee
- No recurring MLM subscription
- Pay-as-you-go for apps

**MLM Rules Apply:** ❌ NO
- Not tracked for network building
- No commissions or bonuses
- No activity points
- No profit-sharing eligibility

---

### 3. BUSINESS (SME Owner)
**Account Type:** `business`

**Characteristics:**
- SME business owner using MyGrowNet's business tools
- Access to accounting, task management, staff tools
- May or may not be an MLM member (separate concern)
- Pays for business tools subscription

**Access:**
- Accounting System
- Task Management
- Staff Management
- Marketplace (for business supplies)
- MyGrow Save Wallet
- Profile & Settings

**Billing:**
- Business tools subscription (monthly/annual)
- Tiered pricing based on features/users
- Optional add-on modules

**MLM Rules Apply:** ❌ NO (unless also a MEMBER)
- Business operations separate from MLM
- Can be both BUSINESS and MEMBER (dual account types)

---

### 4. INVESTOR (Venture Builder Participant)
**Account Type:** `investor`

**Characteristics:**
- Co-investor in Venture Builder projects
- Access to investor portal with portfolio, documents, returns
- May or may not be an MLM member (separate concern)
- Legal shareholder in funded projects

**Access:**
- Investor Portal (portfolio, documents, returns, voting)
- Venture Builder (project browsing and investment)
- MyGrow Save Wallet (for investment transactions)
- Profile & Settings

**Billing:**
- No subscription fee
- Invests capital in specific projects
- Receives dividends/returns from investments

**MLM Rules Apply:** ❌ NO (unless also a MEMBER)
- Investment returns separate from MLM commissions
- Can be both INVESTOR and MEMBER (dual account types)

---

### 5. EMPLOYEE (Internal Staff)
**Account Type:** `employee`

**Characteristics:**
- MyGrowNet internal staff
- Access to admin tools, live chat, operations dashboard
- Not an MLM member or client
- Role-based permissions (support, admin, manager, etc.)

**Access:**
- Employee Portal (tasks, performance, schedule)
- Live Chat (customer support)
- Admin Tools (based on role)
- Profile & Settings

**Billing:**
- No billing - internal account
- Paid salary by MyGrowNet

**MLM Rules Apply:** ❌ NO
- Internal operations only
- No network building or commissions

---

## Multi-Account Type Support

### Dual Account Types
Users can have multiple account types simultaneously:

**Example 1: MEMBER + INVESTOR**
- User is an MLM member AND co-invests in Venture Builder
- Sees both MLM dashboard and investor portal
- Earns MLM commissions + investment returns separately

**Example 2: MEMBER + BUSINESS**
- User is an MLM member AND runs an SME
- Sees both MLM dashboard and business tools
- MLM earnings and business operations tracked separately

**Example 3: CLIENT + INVESTOR**
- User purchases apps AND co-invests in projects
- No MLM participation
- Access to purchased modules + investor portal

### Implementation
```php
// User model supports multiple account types
$user->account_types = ['member', 'investor']; // JSON array

// Check access
$user->hasAccountType(AccountType::MEMBER); // true
$user->hasAccountType(AccountType::INVESTOR); // true
$user->hasAccountType(AccountType::CLIENT); // false

// Get all available modules
$user->getAvailableModules(); // Merges modules from all account types
```

---

## Access Control Matrix

| Feature | MEMBER | CLIENT | BUSINESS | INVESTOR | EMPLOYEE |
|---------|--------|--------|----------|----------|----------|
| MLM Dashboard | ✅ | ❌ | ❌ | ❌ | ❌ |
| Network Building | ✅ | ❌ | ❌ | ❌ | ❌ |
| Commissions | ✅ | ❌ | ❌ | ❌ | ❌ |
| Profit Sharing | ✅ | ❌ | ❌ | ❌ | ❌ |
| Training | ✅ | ❌ | ❌ | ❌ | ❌ |
| Marketplace | ✅ | ✅ | ✅ | ❌ | ❌ |
| Purchased Modules | ✅ | ✅ | ❌ | ❌ | ❌ |
| Business Tools | ❌ | ❌ | ✅ | ❌ | ❌ |
| Investor Portal | ❌* | ❌* | ❌* | ✅ | ❌ |
| Venture Builder | ✅ | ✅ | ❌ | ✅ | ❌ |
| Employee Portal | ❌ | ❌ | ❌ | ❌ | ✅ |
| Admin Tools | ❌ | ❌ | ❌ | ❌ | ✅ |
| Live Chat Support | ❌ | ❌ | ❌ | ❌ | ✅ |
| MyGrow Save Wallet | ✅ | ✅ | ✅ | ✅ | ❌ |

*Can access if they also have INVESTOR account type

---

## Billing & Pricing Models

### MEMBER Pricing
- Registration: K150 (one-time)
- Subscription: K50/month or K500/year
- Module purchases: 20% member discount
- Example: Wedding Planner K40/month (vs K50 for clients)

### CLIENT Pricing
- No registration fee
- No recurring subscription
- Per-module pricing: Full price
- Example: Wedding Planner K50/month

### BUSINESS Pricing
- Business Starter: K200/month (1-5 users)
- Business Pro: K500/month (6-20 users)
- Business Enterprise: Custom pricing (20+ users)

### INVESTOR Pricing
- No subscription
- Minimum investment per project: K5,000
- Platform fee: 2% of investment amount (one-time)
- MyGrowNet equity stake: 10-20% per project

### EMPLOYEE Pricing
- No billing - internal accounts

---

## Portal Routing

### Home Hub (Landing Page)
All authenticated users land here first:
- Shows tiles for available modules based on account type
- MEMBER sees: MLM Dashboard, Training, Marketplace, etc.
- CLIENT sees: Purchased modules, Marketplace, Venture Builder
- INVESTOR sees: Investor Portal, Venture Builder
- EMPLOYEE sees: Employee Portal, Live Chat, Admin Tools

### Portal-Specific Routes

**MLM Member Portal:**
- `/mygrownet/dashboard` - Main MLM dashboard
- `/mygrownet/network` - Network visualizer
- `/mygrownet/earnings` - Commissions and bonuses
- `/mygrownet/training` - Learning resources

**Investor Portal:**
- `/investor/dashboard` - Portfolio overview
- `/investor/projects` - Available projects
- `/investor/documents` - Legal documents
- `/investor/returns` - Dividend history

**Employee Portal:**
- `/employee/dashboard` - Tasks and performance
- `/employee/live-chat` - Customer support interface
- `/employee/admin` - Admin tools (role-based)

**Module Routes:**
- `/modules/{module-slug}` - Generic module access
- Example: `/modules/wedding-planner`
- Example: `/modules/business-plan-generator`

---

## Registration & Onboarding

### MEMBER Registration
1. Choose "Become a Member" on landing page
2. Pay registration fee (K150)
3. Select subscription plan (monthly/annual)
4. Provide referral code (optional)
5. Complete profile
6. Access MLM dashboard

### CLIENT Registration
1. Browse marketplace or modules
2. Select module to purchase
3. Create account (free)
4. Pay for module subscription
5. Access purchased module only

### BUSINESS Registration
1. Choose "Business Tools" on landing page
2. Select business plan tier
3. Create account
4. Pay first month subscription
5. Access business tools

### INVESTOR Registration
1. Browse Venture Builder projects
2. Express interest in project
3. Complete KYC verification
4. Sign legal documents
5. Transfer investment funds
6. Access investor portal

### EMPLOYEE Onboarding
1. HR creates employee account
2. Employee receives invitation email
3. Sets password and completes profile
4. Assigned role and permissions
5. Access employee portal

---

## Key Implementation Notes

### 1. Separation of Concerns
- MLM logic only applies to MEMBER account type
- Module access based on purchases, not MLM status
- Investor returns separate from MLM commissions
- Employee operations isolated from customer features

### 2. Database Schema
```sql
-- Users table
users (
  id,
  name,
  email,
  account_types JSON, -- ['member', 'investor']
  ...
)

-- Module subscriptions (for CLIENTS and MEMBERS)
module_subscriptions (
  user_id,
  module_id,
  status,
  expires_at,
  ...
)

-- MLM data (only for MEMBERS)
user_investments (
  user_id,
  tier_id,
  ...
) -- Only exists for MEMBER account type

-- Investor data (only for INVESTORS)
venture_investments (
  user_id,
  project_id,
  amount,
  ...
) -- Only exists for INVESTOR account type
```

### 3. Middleware
```php
// Check account type
Route::middleware(['auth', 'account.type:member'])->group(function () {
    // MLM routes
});

Route::middleware(['auth', 'account.type:investor'])->group(function () {
    // Investor routes
});

Route::middleware(['auth', 'account.type:employee'])->group(function () {
    // Employee routes
});

// Check module access (for CLIENTS and MEMBERS)
Route::middleware(['auth', 'module.access:wedding-planner'])->group(function () {
    // Module routes
});
```

### 4. Upgrade Paths
- CLIENT → MEMBER: Pay registration fee, start subscription
- MEMBER → INVESTOR: Complete KYC, invest in project
- BUSINESS → MEMBER: Add MLM subscription
- Any type can add INVESTOR by investing

---

## Summary

The modular system cleanly separates:
1. **MLM Members** - Full network participation with commissions
2. **App Clients** - Pay-per-module access without MLM
3. **SME Businesses** - Business tools subscription
4. **Investors** - Venture Builder co-investment portal
5. **Employees** - Internal operations and support

Each user type has distinct billing, access, and features. Users can hold multiple account types simultaneously for maximum flexibility.
