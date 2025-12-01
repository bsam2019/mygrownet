# Account Types Documentation

**Last Updated:** December 1, 2025

## Overview

This folder contains all documentation related to MyGrowNet's account type system, which defines how different user types (MEMBER, CLIENT, BUSINESS, INVESTOR, EMPLOYEE) are handled throughout the platform.

---

## 📚 Documentation Files

### 1. **REGISTRATION_FLOW.md** ⭐⭐
**Simplified registration guide**
- How account types are assigned automatically
- Public registration (referral code logic)
- Admin-created accounts (INVESTOR, EMPLOYEE)
- Account type upgrades
- Code examples

**Read this first** to understand the simple flow.

### 2. **USER_TYPES_AND_ACCESS_MODEL.md** ⭐
**Complete reference guide**
- Detailed characteristics of each account type
- Complete access control matrix
- Billing models and pricing
- Portal routing and registration flows
- Database schema
- Middleware implementation
- Multi-account type support

### 3. **IMPLEMENTATION_GUIDE.md**
**Step-by-step implementation**
- Database migrations
- User model updates
- Middleware creation
- Route protection
- Testing procedures

### 4. **IMPLEMENTATION_CHECKLIST.md**
**Task tracking**
- 11 implementation phases
- Detailed task breakdown
- Verification checklist

### 5. **ACCOUNT_TYPES_VS_ROLES_STRATEGY.md**
**Strategic overview**
- Account types vs roles distinction
- When to use account types vs roles
- Implementation strategy
- Migration plan

### 6. **ACCOUNT_TYPES_VS_ROLES.md**
**Technical comparison**
- Detailed comparison between account types and roles
- Use cases for each
- Implementation patterns

### 7. **ACCOUNT_TYPES_VS_ROLES_QUICK.md**
**Quick reference**
- Fast lookup for account type vs role decisions
- Common patterns
- Quick decision tree

### 8. **USER_CLASSIFICATION_SUMMARY.md**
**User classification guide**
- How users are classified
- Classification criteria
- Examples and edge cases

---

## 🎯 Quick Start

### For Developers
1. Read **REGISTRATION_FLOW.md** (10 min) ⭐ START HERE
2. Read **USER_TYPES_AND_ACCESS_MODEL.md** (15 min)
3. Review **IMPLEMENTATION_GUIDE.md** (20 min)
4. Check **ACCOUNT_TYPES_VS_ROLES_QUICK.md** for quick reference

### For Business Team
1. Read **USER_TYPES_AND_ACCESS_MODEL.md** - Focus on:
   - User Type Definitions section
   - Access Control Matrix
   - Billing & Pricing Models
2. Review **USER_CLASSIFICATION_SUMMARY.md**

### For Project Managers
1. Read **USER_TYPES_AND_ACCESS_MODEL.md** (complete)
2. Read **ACCOUNT_TYPES_VS_ROLES_STRATEGY.md**
3. Use **ACCOUNT_TYPES_VS_ROLES_QUICK.md** for daily reference

---

## 🔑 Key Concepts

### The 5 Account Types

1. **MEMBER** - MLM participant with network building
2. **CLIENT** - App/shop user without MLM participation
3. **BUSINESS** - SME owner using business tools
4. **INVESTOR** - Venture Builder co-investor
5. **EMPLOYEE** - Internal staff with admin access

### Critical Rules

- **Only MEMBERS participate in MLM** - All others are exempt
- **Users can have multiple account types** - e.g., MEMBER + INVESTOR
- **Account types ≠ Roles** - Account types define user category, roles define permissions
- **Billing varies by account type** - Each type has different pricing model

---

## 📋 Implementation Checklist

See **IMPLEMENTATION_GUIDE.md** for complete step-by-step instructions.

### Phase 1: Database & Models ✅
- [x] Add account_type enum to AccountType.php
- [x] Update users table migration
- [x] Add helper methods to User model
- [ ] Create account type seeder

### Phase 2: Middleware & Access Control
- [ ] Create CheckAccountType middleware
- [ ] Update CheckModuleAccess middleware
- [ ] Add account type guards to routes
- [ ] Implement multi-account type support

### Phase 3: Registration & Onboarding
- [ ] Update registration flow for each account type
- [ ] Create account type selection UI
- [ ] Implement account type upgrade paths
- [ ] Add account type verification

### Phase 4: Portal Routing
- [ ] Implement Home Hub with account type filtering
- [ ] Create portal-specific routes
- [ ] Add account type-based navigation
- [ ] Implement portal access control

### Phase 5: Billing Integration
- [ ] Implement per-account-type pricing
- [ ] Create subscription management for each type
- [ ] Add account type-specific payment flows
- [ ] Implement upgrade/downgrade logic

---

## 🔍 Common Questions

### Q: What's the difference between account types and roles?
**A:** Account types define **what kind of user** you are (MEMBER, CLIENT, etc.). Roles define **what you can do** (Admin, Manager, etc.). See **ACCOUNT_TYPES_VS_ROLES.md** for details.

### Q: Can a user have multiple account types?
**A:** Yes! A user can be both a MEMBER and an INVESTOR, for example. See the "Multi-Account Type Support" section in **USER_TYPES_AND_ACCESS_MODEL.md**.

### Q: Do all users participate in MLM?
**A:** No! Only MEMBER account type participates in MLM. All other types (CLIENT, BUSINESS, INVESTOR, EMPLOYEE) are exempt from MLM rules.

### Q: How do I check if a user has a specific account type?
**A:** Use `$user->hasAccountType(AccountType::MEMBER)` or check the implementation section in **USER_TYPES_AND_ACCESS_MODEL.md**.

### Q: What happens when a CLIENT wants to become a MEMBER?
**A:** They pay the registration fee and subscription, then their account type is upgraded. See "Upgrade Paths" in **USER_TYPES_AND_ACCESS_MODEL.md**.

---

## 🛠️ Related Documentation

- **Module System:** `docs/modules/` - How modules integrate with account types
- **Portal System:** `PORTAL_SYSTEM_COMPLETE.md` - Portal architecture
- **Home Hub:** `HOME_HUB_IMPLEMENTATION.md` - Central dashboard for all account types

---

## 📞 Support

### Need Help?

**Technical Questions:**
- See **USER_TYPES_AND_ACCESS_MODEL.md** - Implementation section
- Check **ACCOUNT_TYPES_VS_ROLES_STRATEGY.md** - Technical strategy

**Business Questions:**
- See **USER_TYPES_AND_ACCESS_MODEL.md** - Billing & Pricing section
- Check **USER_CLASSIFICATION_SUMMARY.md** - User classification

**Quick Reference:**
- See **ACCOUNT_TYPES_VS_ROLES_QUICK.md** - Fast lookup

---

## 🚀 Next Steps

### To Implement Account Types:

1. **Review Documentation**
   - Read USER_TYPES_AND_ACCESS_MODEL.md completely
   - Understand the 5 account types
   - Review access control matrix

2. **Update Database**
   - Run migrations for account_type column
   - Seed account types
   - Update existing users

3. **Implement Middleware**
   - Create CheckAccountType middleware
   - Update route guards
   - Test access control

4. **Update Registration**
   - Add account type selection
   - Implement type-specific flows
   - Test all registration paths

5. **Implement Portals**
   - Create Home Hub
   - Build portal-specific routes
   - Add navigation logic

6. **Test Everything**
   - Test each account type
   - Test multi-account types
   - Test upgrade paths
   - Test access control

---

**Remember:** Account types are fundamental to how MyGrowNet operates. Only MEMBERS participate in MLM - everyone else uses the platform differently.
