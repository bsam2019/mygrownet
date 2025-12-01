# Portal System - Complete Overview

**Last Updated:** December 1, 2025
**Status:** Planning Complete

## Quick Summary

MyGrowNet has **5 user types** with **3 portal patterns**:

### User Types
1. **MEMBER** - MLM participant (✅ MLM rules apply)
2. **CLIENT** - App user (❌ NO MLM)
3. **BUSINESS** - SME owner (❌ NO MLM)
4. **INVESTOR** - Co-investor (❌ NO MLM)
5. **EMPLOYEE** - Internal staff (❌ NO MLM)

### Portal Patterns
1. **Home Hub (Modular)** - For MEMBER, CLIENT, BUSINESS
2. **Standalone Portal** - For INVESTOR, EMPLOYEE
3. **Shared Features** - Marketplace (multiple types)

---

## Critical Distinctions

### Who Participates in MLM?
**ONLY MEMBERS** - Everyone else is exempt.

### Who Uses Home Hub?
**MEMBERS, CLIENTS, BUSINESSES** - Modular app access.

### Who Gets Standalone Portals?
**INVESTORS, EMPLOYEES** - Separate professional interfaces.

---

## Key Documents

### Planning Documents (Read These First)
1. **[docs/USER_TYPES_AND_ACCESS_MODEL.md](docs/modules/USER_TYPES_AND_ACCESS_MODEL.md)** ⭐⭐
   - Complete user type definitions
   - Access control matrix
   - Billing models
   - Registration flows

2. **[docs/PORTAL_ARCHITECTURE_PLAN.md](docs/PORTAL_ARCHITECTURE_PLAN.md)** ⭐
   - Portal integration decisions
   - Navigation structure
   - Multi-account scenarios
   - Implementation recommendations

3. **[docs/PORTAL_ARCHITECTURE_VISUAL.md](docs/PORTAL_ARCHITECTURE_VISUAL.md)** ⭐
   - Visual diagrams
   - Navigation examples
   - Access control flows
   - Implementation checklist

### Quick References
4. **[USER_TYPES_QUICK_SUMMARY.md](USER_TYPES_QUICK_SUMMARY.md)**
   - One-page overview
   - Quick decision tree
   - Portal access table

### Module System Documentation
5. **[docs/modules/](docs/modules/)** - Complete module system docs
   - Module architecture
   - Implementation guides
   - Business strategy

---

## The Big Picture

```
MyGrowNet Platform
│
├─ Home Hub (Modular Portal)
│  ├─ MEMBER: Core (MLM) + Purchased Modules
│  ├─ CLIENT: Purchased Modules Only
│  └─ BUSINESS: Business Tools Suite
│
├─ Investor Portal (Standalone)
│  └─ INVESTOR: Portfolio, Projects, Returns
│
├─ Employee Portal (Standalone)
│  └─ EMPLOYEE: Tasks, Live Chat, Admin Tools
│
└─ Marketplace (Shared Feature)
   └─ MEMBER, CLIENT, BUSINESS: Shopping
```

---

## Decision Matrix

| Question | Answer |
|----------|--------|
| Should Investor Portal be in Home Hub? | ❌ NO - Keep standalone |
| Should Employee Portal be in Home Hub? | ❌ NO - Keep standalone |
| Should Business Tools be in Home Hub? | ✅ YES - Module suite |
| Do CLIENTs participate in MLM? | ❌ NO - App users only |
| Do INVESTORs participate in MLM? | ❌ NO (unless also MEMBER) |
| Can users have multiple account types? | ✅ YES - Tracked separately |

---

## Implementation Status

### ✅ Completed
- AccountType enum with all 5 types
- User model with account_type field
- Module system architecture
- Home Hub implementation
- Core module (MLM Dashboard)
- Business tools (Accounting, Tasks)

### 📋 Planned
- Multi-account type support in User model
- Investor Portal (standalone)
- Employee Portal (standalone)
- Navigation updates for all account types
- Access control middleware
- Complete testing

---

## Next Steps

1. **Review Planning Documents**
   - Read USER_TYPES_AND_ACCESS_MODEL.md
   - Read PORTAL_ARCHITECTURE_PLAN.md
   - Review PORTAL_ARCHITECTURE_VISUAL.md

2. **Implement Multi-Account Support**
   - Update User model
   - Create middleware
   - Add access control checks

3. **Build Investor Portal**
   - Standalone routes
   - Portfolio dashboard
   - Project management
   - Returns tracking

4. **Build Employee Portal**
   - Standalone routes
   - Task management
   - Live chat interface
   - Admin tools

5. **Update Navigation**
   - Show correct links per account type
   - Handle multi-account scenarios
   - Test all combinations

6. **Testing**
   - Verify MLM rules only for MEMBERS
   - Test all portal access patterns
   - Verify multi-account scenarios

---

## Questions?

See the detailed planning documents:
- **User Types:** `docs/modules/USER_TYPES_AND_ACCESS_MODEL.md`
- **Portal Architecture:** `docs/PORTAL_ARCHITECTURE_PLAN.md`
- **Visual Guide:** `docs/PORTAL_ARCHITECTURE_VISUAL.md`
- **Quick Summary:** `USER_TYPES_QUICK_SUMMARY.md`

---

**Remember:** Only MEMBERS participate in MLM. Everyone else is exempt from network building, commissions, and MLM rules.

**Last Updated:** December 1, 2025
**Status:** Ready for Implementation
