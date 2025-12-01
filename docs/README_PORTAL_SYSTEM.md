# Portal System Documentation

**Last Updated:** December 1, 2025

## 📚 Complete Documentation Index

### 🎯 Start Here (5 minutes)
**[PORTAL_SYSTEM_COMPLETE.md](PORTAL_SYSTEM_COMPLETE.md)** ⭐⭐⭐
- Quick overview of entire portal system
- User types and portal patterns
- Decision matrix
- Next steps

### 📖 Core Planning Documents

#### 1. User Types & Access
**[docs/modules/USER_TYPES_AND_ACCESS_MODEL.md](docs/modules/USER_TYPES_AND_ACCESS_MODEL.md)** (15 min)
- Complete definitions of all 5 user types
- Access control matrix
- Billing models for each type
- Registration and onboarding flows

**[USER_TYPES_QUICK_SUMMARY.md](USER_TYPES_QUICK_SUMMARY.md)** (3 min)
- One-page quick reference
- User type comparison table
- Quick decision tree

#### 2. Portal Architecture
**[docs/PORTAL_ARCHITECTURE_PLAN.md](docs/PORTAL_ARCHITECTURE_PLAN.md)** (20 min)
- Portal integration decisions
- Navigation structure for each user type
- Multi-account type scenarios
- Implementation recommendations

**[docs/PORTAL_ARCHITECTURE_VISUAL.md](docs/PORTAL_ARCHITECTURE_VISUAL.md)** (15 min)
- Visual diagrams and flowcharts
- Navigation examples
- Access control flows
- Implementation checklist

#### 3. Module System
**[docs/modules/](docs/modules/)** - Complete module documentation
- Module system architecture
- Implementation guides
- Business strategy
- Quick start guides

---

## 🔑 Key Concepts

### The 5 User Types
1. **MEMBER** - MLM participant (✅ MLM rules)
2. **CLIENT** - App user (❌ NO MLM)
3. **BUSINESS** - SME owner (❌ NO MLM)
4. **INVESTOR** - Co-investor (❌ NO MLM)
5. **EMPLOYEE** - Internal staff (❌ NO MLM)

### The 3 Portal Patterns
1. **Home Hub** - Modular apps (MEMBER, CLIENT, BUSINESS)
2. **Standalone** - Dedicated portals (INVESTOR, EMPLOYEE)
3. **Shared** - Platform features (Marketplace)

---

## ✅ Quick Decisions

| Question | Answer |
|----------|--------|
| Do apps users participate in MLM? | ❌ NO - Only MEMBERS |
| Should Investor Portal be in Home Hub? | ❌ NO - Standalone |
| Should Employee Portal be in Home Hub? | ❌ NO - Standalone |
| Can users have multiple account types? | ✅ YES |

---

## 📋 Implementation Status

- ✅ AccountType enum updated
- ✅ Planning documents complete
- 📋 Multi-account support (next)
- 📋 Investor Portal (planned)
- 📋 Employee Portal (planned)

---

**Start with:** [PORTAL_SYSTEM_COMPLETE.md](PORTAL_SYSTEM_COMPLETE.md)
