# Account Types Implementation Checklist

**Last Updated:** December 1, 2025

Use this checklist to track implementation progress.

---

## Phase 1: Database & User Model

- [ ] **1.1** Create migration for `account_types` JSON column
- [ ] **1.2** Add User model methods:
  - [ ] `getAccountTypesAttribute()`
  - [ ] `setAccountTypesAttribute()`
  - [ ] `hasAccountType()`
  - [ ] `addAccountType()`
  - [ ] `removeAccountType()`
  - [ ] `isMLMParticipant()`
  - [ ] `isEmployee()`
  - [ ] `getAvailableModules()`
  - [ ] `getPrimaryAccountType()`
- [ ] **1.3** Add `account_types` to User model `$fillable`
- [ ] **1.4** Create `AccountTypeSeeder`
- [ ] **1.5** Run migration
- [ ] **1.6** Run seeder to migrate existing users
- [ ] **1.7** Test User model methods

---

## Phase 2: Middleware & Access Control

- [ ] **2.1** Create `CheckAccountType` middleware
- [ ] **2.2** Register middleware in `bootstrap/app.php`
- [ ] **2.3** Update `CheckModuleAccess` middleware
- [ ] **2.4** Test middleware with different account types

---

## Phase 3: Route Protection

- [ ] **3.1** Protect MLM routes (member only)
- [ ] **3.2** Protect Investor routes (investor, member)
- [ ] **3.3** Protect Business routes (business only)
- [ ] **3.4** Protect Employee routes (employee only)
- [ ] **3.5** Update shared routes (marketplace, venture builder)
- [ ] **3.6** Test all route protections

---

## Phase 4: Registration Flows

- [ ] **4.1** Update `RegisterController` with account type logic
- [ ] **4.2** Create account type selection UI
- [ ] **4.3** Add conditional fields for each type
- [ ] **4.4** Test MEMBER registration (with referral)
- [ ] **4.5** Test CLIENT registration (no referral)
- [ ] **4.6** Test BUSINESS registration
- [ ] **4.7** Test INVESTOR registration

---

## Phase 5: Home Hub Integration

- [ ] **5.1** Update `HomeHubController` to use account types
- [ ] **5.2** Update Home Hub Vue component
- [ ] **5.3** Display account type badges
- [ ] **5.4** Filter modules by account types
- [ ] **5.5** Test with single account type
- [ ] **5.6** Test with multiple account types

---

## Phase 6: Portal Routing

- [ ] **6.1** Implement MLM dashboard routing
- [ ] **6.2** Implement Investor portal routing
- [ ] **6.3** Implement Business tools routing
- [ ] **6.4** Implement Employee portal routing
- [ ] **6.5** Implement default routing logic
- [ ] **6.6** Test portal access for each type

---

## Phase 7: Billing Integration

- [ ] **7.1** Implement MEMBER pricing (K150 + K50/month)
- [ ] **7.2** Implement CLIENT pricing (per-module)
- [ ] **7.3** Implement BUSINESS pricing (tiered)
- [ ] **7.4** Implement INVESTOR pricing (no subscription)
- [ ] **7.5** Create account type upgrade flows
- [ ] **7.6** Test billing for each type

---

## Phase 8: Admin Interface

- [ ] **8.1** Create `AccountTypeController`
- [ ] **8.2** Add account type management UI
- [ ] **8.3** Implement add account type
- [ ] **8.4** Implement remove account type
- [ ] **8.5** Implement bulk updates
- [ ] **8.6** Test admin interface

---

## Phase 9: Testing

### Unit Tests
- [ ] Test User model account type methods
- [ ] Test AccountType enum methods
- [ ] Test middleware logic

### Integration Tests
- [ ] Test registration flows
- [ ] Test route protection
- [ ] Test portal access
- [ ] Test multi-account types

### Feature Tests
- [ ] Test MEMBER user journey
- [ ] Test CLIENT user journey
- [ ] Test BUSINESS user journey
- [ ] Test INVESTOR user journey
- [ ] Test EMPLOYEE user journey
- [ ] Test account type upgrades

### Manual Testing
- [ ] Register as each account type
- [ ] Test access to each portal
- [ ] Test module access
- [ ] Test account type upgrades
- [ ] Test admin management

---

## Phase 10: Documentation & Training

- [ ] Update user documentation
- [ ] Create admin training materials
- [ ] Update API documentation
- [ ] Create video tutorials
- [ ] Update help center

---

## Phase 11: Deployment

- [ ] Review all changes
- [ ] Run final tests
- [ ] Backup database
- [ ] Deploy to staging
- [ ] Test on staging
- [ ] Deploy to production
- [ ] Monitor for issues
- [ ] Communicate changes to users

---

## Verification Checklist

After implementation, verify:

- [ ] All 5 account types work correctly
- [ ] Users can have multiple account types
- [ ] MLM rules only apply to MEMBERS
- [ ] Route protection works correctly
- [ ] Registration flows work for each type
- [ ] Home Hub shows correct modules
- [ ] Billing works for each type
- [ ] Admin can manage account types
- [ ] No breaking changes to existing users
- [ ] Documentation is complete

---

## Rollback Plan

If issues occur:

1. **Database:** Keep old `account_type` column until verified
2. **Routes:** Can revert to old middleware
3. **Registration:** Can disable account type selection
4. **Billing:** Can fall back to old pricing

---

## Success Criteria

Implementation is successful when:

- ✅ All 5 account types are functional
- ✅ Users can register as any type
- ✅ Access control works correctly
- ✅ No existing users are broken
- ✅ Admin can manage account types
- ✅ Billing works for each type
- ✅ Documentation is complete
- ✅ Team is trained

---

## Notes

- Keep old `account_type` column during migration for safety
- Test thoroughly with real user scenarios
- Monitor production closely after deployment
- Be ready to rollback if needed

---

**Status:** Ready for implementation
**Estimated Time:** 4-5 weeks
**Priority:** HIGH - Foundation for entire platform
