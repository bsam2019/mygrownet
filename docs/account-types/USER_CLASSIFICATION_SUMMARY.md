# User Classification - Complete Summary

**Last Updated:** December 1, 2025

## The Two Systems

MyGrowNet uses TWO complementary systems for user classification:

### 1. Account Types (Business Classification)
**What:** Determines business relationship, billing, and MLM participation  
**Where:** `users.account_type` enum field  
**Values:** `member`, `client`, `business`, `investor`, `employee`  
**Purpose:** Business logic, billing, MLM rules

### 2. Roles (Access Control)
**What:** Controls permissions and feature access  
**Where:** Spatie Permission package (`model_has_roles` table)  
**Values:** `superadmin`, `admin`, `employee`, `member`, `Client`, `Business`  
**Purpose:** Access control, UI visibility, route protection

---

## Quick Reference

### Account Types

| Type | Description | MLM? | Billing |
|------|-------------|------|---------|
| **member** | MLM participant | ✅ YES | K150 + K50/month |
| **client** | App/shop user | ❌ NO | Per-module |
| **business** | SME owner | ❌ NO | K200-1000/month |
| **investor** | Venture Builder | ❌ NO | Per-investment |
| **employee** | Internal staff | ❌ NO | None (internal) |

### Roles

| Role | Description | Auto-Assigned To |
|------|-------------|------------------|
| **superadmin** | Super administrator | Manual only |
| **admin** | Platform administrator | Manual only |
| **employee** | Company staff | `employee` account type |
| **member** | Regular platform user | `member` account type |
| **Client** | Non-MLM user | `client` account type |
| **Business** | SME user | `business` account type |

---

## How They Work Together

```
User Registration
       ↓
Account Type Assigned (business classification)
       ↓
Role Auto-Assigned (access control)
       ↓
Permissions Applied
       ↓
User Can Access Features
```

### Example: MLM Member
```
Account Type: member
Role: member
Result: MLM dashboard + commissions + network building
```

### Example: App Client
```
Account Type: client
Role: Client
Result: Marketplace + purchased modules (no MLM)
```

### Example: Admin Member
```
Account Type: member
Roles: member + admin
Result: Everything (MLM + admin panel)
```

---

## When to Use What?

### Use Account Type For:
- ✅ Billing and subscription logic
- ✅ MLM participation checks
- ✅ Dashboard routing
- ✅ Business rules (commissions, points)

### Use Role For:
- ✅ Feature access control
- ✅ UI element visibility
- ✅ Route protection
- ✅ Permission checks

---

## Code Examples

### Check Account Type
```php
// Business logic
if ($user->account_type === 'member') {
    $this->calculateCommissions($user);
}

// MLM participation
if ($user->isMLMParticipant()) {
    // Show network features
}
```

### Check Role
```php
// Access control
if ($user->hasRole('admin')) {
    return view('admin.dashboard');
}

// Permission check
if ($user->can('manage_users')) {
    // Allow user management
}
```

### Middleware
```php
// Account type check
Route::middleware(['auth', 'account.type:member'])->group(function () {
    // MLM routes
});

// Role check
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Admin routes
});

// Permission check
Route::middleware(['auth', 'permission:manage_users'])->group(function () {
    // User management routes
});
```

---

## Complete Documentation

### Detailed Guides
1. **`docs/modules/USER_TYPES_AND_ACCESS_MODEL.md`** - Complete account types guide
2. **`docs/ACCOUNT_TYPES_VS_ROLES.md`** - Account types vs roles comparison
3. **`USER_TYPES_QUICK_SUMMARY.md`** - Quick reference

### Implementation Files
- `app/Enums/AccountType.php` - Account type enum
- `database/seeders/RoleSeeder.php` - Role definitions
- `database/seeders/ClientRoleSeeder.php` - Client/Business roles
- `app/Models/User.php` - User model with auto-role assignment

---

## Key Takeaway

**Account Type** = WHO you are (business relationship)  
**Role** = WHAT you can do (permissions)

Both systems work together to provide flexible, secure access control while maintaining clear business logic separation.
