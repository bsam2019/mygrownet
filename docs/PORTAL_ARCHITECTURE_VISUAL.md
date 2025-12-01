# Portal Architecture - Visual Guide

**Last Updated:** December 1, 2025

## The Big Picture

```
┌─────────────────────────────────────────────────────────────────┐
│                    MyGrowNet Platform                            │
│                                                                  │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │   MEMBER     │  │   CLIENT     │  │  BUSINESS    │         │
│  │  (MLM User)  │  │  (App User)  │  │  (SME Owner) │         │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘         │
│         │                  │                  │                  │
│         ├──────────────────┴──────────────────┤                 │
│         │                                     │                  │
│         ▼                                     ▼                  │
│  ┌─────────────────────────────────────────────────┐           │
│  │            HOME HUB (Module Launcher)            │           │
│  │                                                  │           │
│  │  MEMBER sees:          CLIENT sees:             │           │
│  │  • Core (MLM) - FREE   • Purchased modules only │           │
│  │  • Purchased modules   • [Empty if none]        │           │
│  │                                                  │           │
│  │  BUSINESS sees:                                 │           │
│  │  • Accounting          • Tasks                  │           │
│  │  • Staff Management    • [+ Core if also MEMBER]│           │
│  └─────────────────────────────────────────────────┘           │
│                                                                  │
│  ┌──────────────┐  ┌──────────────┐                            │
│  │   INVESTOR   │  │   EMPLOYEE   │                            │
│  │ (Co-investor)│  │ (Staff Only) │                            │
│  └──────┬───────┘  └──────┬───────┘                            │
│         │                  │                                     │
│         ▼                  ▼                                     │
│  ┌─────────────┐    ┌─────────────┐                            │
│  │  INVESTOR   │    │  EMPLOYEE   │                            │
│  │   PORTAL    │    │   PORTAL    │                            │
│  │ (Standalone)│    │ (Standalone)│                            │
│  └─────────────┘    └─────────────┘                            │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## User Type Comparison

```
┌─────────────┬──────────┬──────────┬──────────┬──────────┬──────────┐
│             │  MEMBER  │  CLIENT  │ BUSINESS │ INVESTOR │ EMPLOYEE │
├─────────────┼──────────┼──────────┼──────────┼──────────┼──────────┤
│ MLM Rules?  │    ✅    │    ❌    │    ❌    │    ❌    │    ❌    │
├─────────────┼──────────┼──────────┼──────────┼──────────┼──────────┤
│ Home Hub?   │    ✅    │    ✅    │    ✅    │    ❌    │    ❌    │
├─────────────┼──────────┼──────────┼──────────┼──────────┼──────────┤
│ Commissions?│    ✅    │    ❌    │    ❌    │    ❌    │    ❌    │
├─────────────┼──────────┼──────────┼──────────┼──────────┼──────────┤
│ Network?    │    ✅    │    ❌    │    ❌    │    ❌    │    ❌    │
├─────────────┼──────────┼──────────┼──────────┼──────────┼──────────┤
│ Shop Access?│    ✅    │    ✅    │    ✅    │    ❌    │    ❌    │
├─────────────┼──────────┼──────────┼──────────┼──────────┼──────────┤
│ Billing     │ K50/mo   │ Per app  │ K200+/mo │ K5000+   │   None   │
└─────────────┴──────────┴──────────┴──────────┴──────────┴──────────┘
```

---

## Portal Integration Patterns

### Pattern 1: Modular (Home Hub Integration)

```
Used for: MEMBER, CLIENT, BUSINESS

┌─────────────────────────────────────┐
│          HOME HUB                   │
│                                     │
│  ┌─────────┐  ┌─────────┐         │
│  │  Core   │  │  App 1  │         │
│  │  (MLM)  │  │         │         │
│  └─────────┘  └─────────┘         │
│                                     │
│  ┌─────────┐  ┌─────────┐         │
│  │  App 2  │  │  App 3  │         │
│  │         │  │         │         │
│  └─────────┘  └─────────┘         │
└─────────────────────────────────────┘

Characteristics:
✅ Shown as tiles in Home Hub
✅ Subscription-based access
✅ Can purchase individually
✅ Integrated experience
```

### Pattern 2: Standalone Portal

```
Used for: INVESTOR, EMPLOYEE

┌─────────────────────────────────────┐
│     INVESTOR/EMPLOYEE PORTAL        │
│                                     │
│  ┌─────────────────────────────┐   │
│  │      Dedicated Dashboard     │   │
│  │                              │   │
│  │  • Portfolio / Tasks         │   │
│  │  • Projects / Performance    │   │
│  │  • Returns / Live Chat       │   │
│  │  • Documents / Admin Tools   │   │
│  └─────────────────────────────┘   │
└─────────────────────────────────────┘

Characteristics:
❌ NOT in Home Hub
❌ NOT subscription-based
✅ Separate navigation
✅ Professional interface
✅ Access-based (investment/employment)
```

---

## Navigation Examples

### MEMBER Navigation

```
┌────────────────────────────────────────────────────────┐
│  [Home Hub] [Marketplace] [Profile]                    │
└────────────────────────────────────────────────────────┘
         │
         ▼
┌────────────────────────────────────────────────────────┐
│                    HOME HUB                             │
│                                                         │
│  ┌──────────────┐  ┌──────────────┐                   │
│  │     Core     │  │ Wedding Plan │                   │
│  │ MLM Dashboard│  │   K40/month  │                   │
│  │    FREE      │  │   ACTIVE     │                   │
│  └──────────────┘  └──────────────┘                   │
└────────────────────────────────────────────────────────┘
```

### CLIENT Navigation

```
┌────────────────────────────────────────────────────────┐
│  [Home Hub] [Marketplace] [Profile]                    │
└────────────────────────────────────────────────────────┘
         │
         ▼
┌────────────────────────────────────────────────────────┐
│                    HOME HUB                             │
│                                                         │
│  ┌──────────────┐  ┌──────────────┐                   │
│  │ Wedding Plan │  │ Business Plan│                   │
│  │   K50/month  │  │   K75/month  │                   │
│  │   ACTIVE     │  │   ACTIVE     │                   │
│  └──────────────┘  └──────────────┘                   │
│                                                         │
│  NO Core module (not MLM member)                       │
└────────────────────────────────────────────────────────┘
```

### MEMBER + INVESTOR Navigation

```
┌────────────────────────────────────────────────────────┐
│  [Home Hub] [Investor Portal] [Marketplace] [Profile]  │
└────────────────────────────────────────────────────────┘
         │              │
         ▼              ▼
┌──────────────┐  ┌──────────────┐
│   HOME HUB   │  │   INVESTOR   │
│              │  │    PORTAL    │
│  • Core      │  │              │
│  • Modules   │  │  • Portfolio │
│              │  │  • Projects  │
│              │  │  • Returns   │
└──────────────┘  └──────────────┘
```

### EMPLOYEE Navigation

```
┌────────────────────────────────────────────────────────┐
│  [Employee Portal] [Admin Tools] [Profile]             │
└────────────────────────────────────────────────────────┘
         │
         ▼
┌────────────────────────────────────────────────────────┐
│               EMPLOYEE PORTAL                           │
│                                                         │
│  • My Tasks                                            │
│  • Performance                                         │
│  • Live Chat (Support)                                 │
│  • Admin Tools (if authorized)                         │
│                                                         │
│  NO Home Hub, NO Marketplace (internal only)           │
└────────────────────────────────────────────────────────┘
```

---

## Multi-Account Type Scenarios

### Scenario: MEMBER + INVESTOR

```
User Profile:
┌─────────────────────────────────┐
│ Name: John Doe                  │
│ Account Types:                  │
│  • MEMBER ✅                    │
│  • INVESTOR ✅                  │
└─────────────────────────────────┘

Navigation:
┌────────────────────────────────────────────────────────┐
│  [Home Hub] [Investor Portal] [Marketplace] [Profile]  │
└────────────────────────────────────────────────────────┘

Access:
✅ MLM Dashboard (Core module)
✅ Purchased modules
✅ Investor Portfolio
✅ Marketplace
✅ MLM commissions + Investment returns (separate)
```

### Scenario: CLIENT + INVESTOR

```
User Profile:
┌─────────────────────────────────┐
│ Name: Jane Smith                │
│ Account Types:                  │
│  • CLIENT ✅                    │
│  • INVESTOR ✅                  │
└─────────────────────────────────┘

Navigation:
┌────────────────────────────────────────────────────────┐
│  [Home Hub] [Investor Portal] [Marketplace] [Profile]  │
└────────────────────────────────────────────────────────┘

Access:
❌ NO MLM Dashboard (not a member)
✅ Purchased modules only
✅ Investor Portfolio
✅ Marketplace
❌ NO commissions, NO network
✅ Investment returns only
```

### Scenario: MEMBER + BUSINESS

```
User Profile:
┌─────────────────────────────────┐
│ Name: Mike Johnson              │
│ Account Types:                  │
│  • MEMBER ✅                    │
│  • BUSINESS ✅                  │
└─────────────────────────────────┘

Navigation:
┌────────────────────────────────────────────────────────┐
│  [Home Hub] [Marketplace] [Profile]                    │
└────────────────────────────────────────────────────────┘

Home Hub Shows:
┌────────────────────────────────────────────────────────┐
│  ┌──────────┐  ┌──────────┐  ┌──────────┐            │
│  │   Core   │  │Accounting│  │  Tasks   │            │
│  │   MLM    │  │  System  │  │  Mgmt    │            │
│  └──────────┘  └──────────┘  └──────────┘            │
└────────────────────────────────────────────────────────┘

Access:
✅ MLM Dashboard + commissions
✅ Business tools
✅ Marketplace
✅ Both systems tracked separately
```

---

## Access Control Flow

```
User Login
    │
    ▼
Check Account Types
    │
    ├─── Has MEMBER? ──────► Show Home Hub with Core
    │                        + MLM features enabled
    │
    ├─── Has CLIENT? ──────► Show Home Hub with
    │                        purchased modules only
    │
    ├─── Has BUSINESS? ────► Show Home Hub with
    │                        business modules
    │
    ├─── Has INVESTOR? ────► Show Investor Portal link
    │                        in navigation
    │
    └─── Has EMPLOYEE? ────► Redirect to Employee Portal
                             (no Home Hub)
```

---

## Key Decision Summary

| Portal | Integration | Reason |
|--------|-------------|--------|
| **MLM Dashboard** | Core Module in Home Hub | Foundation for members, FREE access |
| **Purchased Apps** | Modules in Home Hub | Subscription-based, integrated experience |
| **Business Tools** | Module Suite in Home Hub | Subscription-based, modular approach |
| **Investor Portal** | Standalone (NOT in Home Hub) | Professional interface, investment-based access |
| **Employee Portal** | Standalone (NOT in Home Hub) | Internal operations, separate from customers |
| **Marketplace** | Shared Feature (main nav) | Available to multiple account types |

---

## Implementation Checklist

### Phase 1: Account Type System
- [x] Update AccountType enum with all 5 types
- [ ] Update User model to support multiple account types
- [ ] Create middleware for account type checking
- [ ] Add account type checks to existing features

### Phase 2: Home Hub Integration
- [ ] Update Home Hub to show correct modules per account type
- [ ] Hide Core module for non-MEMBERS
- [ ] Show business modules for BUSINESS users
- [ ] Handle empty state for CLIENTS with no modules

### Phase 3: Investor Portal (Standalone)
- [ ] Create investor portal routes (separate from Home Hub)
- [ ] Build investor dashboard UI
- [ ] Add "Investor Portal" link to navigation (conditional)
- [ ] Implement portfolio, projects, returns pages

### Phase 4: Employee Portal (Standalone)
- [ ] Create employee portal routes (separate from Home Hub)
- [ ] Build employee dashboard UI
- [ ] Implement task management for employees
- [ ] Integrate live chat interface
- [ ] Add role-based admin tools

### Phase 5: Navigation Updates
- [ ] Show correct navigation items per account type
- [ ] Handle multi-account type scenarios
- [ ] Test all navigation combinations

### Phase 6: Access Control
- [ ] Verify MLM rules only apply to MEMBERS
- [ ] Test module access for all account types
- [ ] Verify investor portal access
- [ ] Verify employee portal access
- [ ] Test multi-account type access

---

**Last Updated:** December 1, 2025
**Status:** Planning Complete
