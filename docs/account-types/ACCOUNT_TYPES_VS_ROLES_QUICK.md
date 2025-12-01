# Account Types vs Roles - Quick Reference

**Last Updated:** December 1, 2025

## TL;DR

**They DON'T conflict - they work together!**

- **Account Types** = WHO you are (business identity)
- **Spatie Roles** = WHAT you can do (permissions)

---

## Visual Comparison

```
┌─────────────────────────────────────────────────────────────┐
│                         USER                                 │
│                                                              │
│  ┌──────────────────────┐      ┌──────────────────────┐    │
│  │   ACCOUNT TYPE(S)    │      │   SPATIE ROLE(S)     │    │
│  │   (Business Logic)   │      │   (Permissions)      │    │
│  ├──────────────────────┤      ├──────────────────────┤    │
│  │ • MEMBER             │      │ • User (base)        │    │
│  │ • CLIENT             │      │ • Member             │    │
│  │ • BUSINESS           │      │ • Investor           │    │
│  │ • INVESTOR           │      │ • Business Owner     │    │
│  │ • EMPLOYEE           │      │ • Support Agent      │    │
│  │                      │      │ • Manager            │    │
│  │ (Can have multiple)  │      │ • Admin              │    │
│  │                      │      │ • Super Admin        │    │
│  └──────────────────────┘      └──────────────────────┘    │
│           ↓                              ↓                  │
│    Determines PORTAL              Determines ACTIONS        │
│    Determines FEATURES            Determines PERMISSIONS    │
│    Determines BILLING             Determines AUTHORIZATION  │
└─────────────────────────────────────────────────────────────┘
```

---

## When to Use What

### Use Account Type For:
✅ Portal routing (`/mygrownet/dashboard` vs `/investor/dashboard`)
✅ Feature availability (can access MLM tools?)
✅ Billing logic (subscription model)
✅ Module access (which apps can they use?)

### Use Spatie Roles For:
✅ Action authorization (can edit this user?)
✅ Button visibility (show delete button?)
✅ Permission checks (can approve withdrawals?)
✅ Fine-grained access control

---

## Code Examples

### Portal Access (Account Type)
```php
// Route protection
Route::middleware(['auth', 'account.type:member'])->group(function () {
    Route::get('/mygrownet/dashboard', [DashboardController::class, 'index']);
});

// Check in code
if ($user->hasAccountType(AccountType::MEMBER)) {
    // Show MLM features
}
```

### Action Authorization (Spatie Role)
```php
// Route protection
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});

// Check in code
if ($user->can('manage-users')) {
    // Show edit button
}
```

### Combined Check
```php
// Employee portal admin section
Route::middleware(['auth', 'account.type:employee', 'role:admin'])->group(function () {
    // Only employees with admin role
});
```

---

## Real Examples

### Example 1: MLM Member
```php
Account Type: MEMBER
Roles: User, Member

// Portal access
$user->hasAccountType(AccountType::MEMBER) // true
→ Can access /mygrownet/dashboard

// Permissions
$user->hasRole('Member') // true
$user->can('view-network') // true
$user->can('manage-users') // false
```

### Example 2: Support Agent
```php
Account Type: EMPLOYEE
Roles: User, Support Agent

// Portal access
$user->hasAccountType(AccountType::EMPLOYEE) // true
→ Can access /employee/portal

// Permissions
$user->hasRole('Support Agent') // true
$user->can('respond-to-chat') // true
$user->can('manage-users') // false
```

### Example 3: Admin
```php
Account Type: EMPLOYEE
Roles: User, Support Agent, Manager, Admin

// Portal access
$user->hasAccountType(AccountType::EMPLOYEE) // true
→ Can access /employee/portal

// Permissions
$user->hasRole('Admin') // true
$user->can('manage-users') // true
$user->can('approve-withdrawals') // true
```

### Example 4: Member + Investor
```php
Account Types: MEMBER, INVESTOR
Roles: User, Member, Investor

// Portal access
$user->hasAccountType(AccountType::MEMBER) // true
$user->hasAccountType(AccountType::INVESTOR) // true
→ Can access both /mygrownet/dashboard AND /investor/dashboard

// Permissions
$user->hasRole('Member') // true
$user->hasRole('Investor') // true
$user->can('view-network') // true
$user->can('vote-on-projects') // true
```

---

## The Rule

```
IF checking "Can they ACCESS this portal/feature?"
  → Use Account Type

IF checking "Can they PERFORM this action?"
  → Use Spatie Role/Permission
```

---

## No Conflict!

Account Types and Roles are **complementary**, not conflicting:

- Account Type opens the door to the portal
- Role determines what you can do inside

Think of it like:
- **Account Type** = Your job title (Doctor, Teacher, Engineer)
- **Role** = Your permissions (Can prescribe medicine, Can grade papers, Can approve designs)

---

**For complete details:** See `docs/ACCOUNT_TYPES_VS_ROLES_STRATEGY.md`
