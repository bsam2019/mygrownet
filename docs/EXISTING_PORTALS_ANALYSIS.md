# Existing Portals Analysis

**Last Updated:** December 1, 2025
**Status:** Planning

## Overview

This document analyzes how existing and planned portals fit into the modular system architecture, with special focus on the Investor Portal and Employee Portal.

---

## Current Portal Landscape

### 1. MLM Member Portal (EXISTING - Core Module)
**Status:** ✅ Implemented
**Account Type:** MEMBER
**Routes:** `/dashboard`, `/mobile-dashboard`

**What it is:**
- The existing MLM dashboard
- Network visualizer, earnings, team management
- Training and resources

**How it fits in modular system:**
- Becomes the "Core" module
- FREE for all MEMBER account types
- Foundation for additional modules
- Accessed via Home Hub or direct routes

**MLM Rules:** ✅ YES - Full MLM participation

---

### 2. Investor Portal (EXISTING - Separate Portal)
**Status:** ✅ Implemented (Venture Builder)
**Account Type:** INVESTOR (or MEMBER + INVESTOR)
**Routes:** `/investor/*`, `/venture-builder/*`

**What it is:**
- Portfolio dashboard for Venture Builder co-investors
- Project details, documents, returns
- Investment tracking and shareholder rights

**How it fits in modular system:**
- **NOT a purchasable module** - Access granted by investment
- Separate portal with its own navigation
- Can be accessed alongside other portals
- Investor gets access when they invest in a project

**Key Characteristics:**
- Access is **investment-based**, not subscription-based
- Users become investors by co-investing (minimum K5,000)
- Can be INVESTOR only, or MEMBER + INVESTOR, or CLIENT + INVESTOR
- Completely separate from MLM system

**MLM Rules:** ❌ NO (unless user is also MEMBER)

**Integration Approach:**
```
Option 1: Standalone Portal (RECOMMENDED)
- Keep investor portal completely separate
- Not shown in Home Hub module tiles
- Direct navigation: /investor/dashboard
- Separate top-level navigation item when user has INVESTOR account type

Option 2: Special Module
- Create "investor_portal" module
- Only visible to users with INVESTOR account type
- Free access (no subscription)
- Shown in Home Hub if user is investor

RECOMMENDATION: Option 1 - Keep it separate
- Investment portal is fundamentally different from apps
- Investors need dedicated, professional interface
- Mixing with "apps" dilutes the investment experience
```

---

### 3. Employee Portal (PLANNED - Internal Portal)
**Status:** 📋 Planned
**Account Type:** EMPLOYEE
**Routes:** `/employee/*`, `/admin/*`

**What it is:**
- Internal staff dashboard
- Task management, performance tracking
- Live chat interface for customer support
- Admin tools (role-based)

**How it fits in modular system:**
- **NOT a purchasable module** - Internal only
- Separate portal for MyGrowNet staff
- Role-based access (support, admin, manager, etc.)
- Not accessible to customers

**Key Characteristics:**
- Access is **employment-based**, not subscription-based
- Created by HR/admin, not self-registration
- Completely separate from customer-facing features
- No billing - internal accounts

**MLM Rules:** ❌ NO - Internal operations only

**Integration Approach:**
```
Option 1: Standalone Portal (RECOMMENDED)
- Keep employee portal completely separate
- Not shown in Home Hub
- Direct navigation: /employee/dashboard
- Separate admin navigation structure

Option 2: Special Module
- Create "employee_portal" module
- Only visible to EMPLOYEE account type
- Free access (internal)
- Shown in Home Hub for employees

RECOMMENDATION: Option 1 - Keep it separate
- Employee tools are internal operations
- Different UX requirements than customer apps
- Security and access control considerations
- Separate navigation makes role management easier
```

---

### 4. Business Tools Portal (PLANNED - SME Module)
**Status:** 📋 Planned (Accounting system partially implemented)
**Account Type:** BUSINESS
**Routes:** `/business/*`, `/accounting/*`

**What it is:**
- SME business management tools
- Accounting, inventory, CRM, tasks, staff management
- Subscription-based for business owners

**How it fits in modular system:**
- **IS a purchasable module** (or suite of modules)
- Subscription-based (K200-1000/month)
- Can be accessed via Home Hub
- Business owners can also be MEMBERS (separate)

**Key Characteristics:**
- Access is **subscription-based**
- Business owners pay for tools
- Can be BUSINESS only, or MEMBER + BUSINESS
- Separate from MLM system

**MLM Rules:** ❌ NO (unless user is also MEMBER)

**Integration Approach:**
```
Option 1: Module Suite (RECOMMENDED)
- Create multiple business modules:
  - accounting
  - inventory
  - crm
  - tasks
  - staff_management
- Shown in Home Hub for BUSINESS account type
- Subscription unlocks all business modules
- Integrated experience via Home Hub

Option 2: Standalone Portal
- Separate business portal
- Not in Home Hub
- Direct navigation: /business/dashboard

RECOMMENDATION: Option 1 - Module Suite
- Business tools benefit from modular approach
- Can sell individual modules or bundles
- Fits naturally into Home Hub
- Easier to add new business tools
```

---

### 5. Shop/Marketplace (EXISTING - Shared Feature)
**Status:** ✅ Implemented
**Account Type:** MEMBER, CLIENT, BUSINESS
**Routes:** `/marketplace/*`, `/shop/*`

**What it is:**
- E-commerce platform
- Digital products, physical goods
- Open to multiple account types

**How it fits in modular system:**
- **NOT a module** - Shared platform feature
- Accessible to MEMBERS, CLIENTS, and BUSINESSES
- No separate subscription
- Part of platform infrastructure

**MLM Rules:** ❌ NO - Shopping is separate from MLM

**Integration Approach:**
```
Keep as shared platform feature
- Not a module in Home Hub
- Accessible via main navigation
- Available to multiple account types
- No subscription required
```

---

## Portal Architecture Summary

### Standalone Portals (Separate Navigation)
These should remain separate from the modular system:

1. **Investor Portal** - Investment-based access
2. **Employee Portal** - Internal staff only
3. **Marketplace** - Shared platform feature

**Why separate?**
- Different user contexts and needs
- Not subscription-based (except marketplace)
- Require dedicated, professional interfaces
- Security and access control considerations

### Modular Portals (Home Hub Integration)
These fit naturally into the modular system:

1. **Core (MLM Dashboard)** - FREE for MEMBERS
2. **Business Tools Suite** - Subscription for BUSINESS users
3. **Personal Apps** - Individual subscriptions for MEMBERS/CLIENTS
4. **SME Apps** - Individual subscriptions for BUSINESS users

**Why modular?**
- Subscription-based access
- Benefit from unified Home Hub
- Can be purchased individually or as bundles
- Easier to add new modules

---

## User Experience Flows

### MEMBER User Journey
1. Login → Home Hub
2. See Core module (MLM Dashboard) - FREE
3. See purchased modules (if any)
4. Can navigate to Marketplace via main nav
5. If also INVESTOR → See investor link in main nav

### CLIENT User Journey
1. Login → Home Hub
2. See purchased modules only
3. Can navigate to Marketplace via main nav
4. If also INVESTOR → See investor link in main nav

### BUSINESS User Journey
1. Login → Home Hub
2. See business modules (accounting, tasks, etc.)
3. Can navigate to Marketplace via main nav
4. If also MEMBER → See Core module too

### INVESTOR User Journey
1. 