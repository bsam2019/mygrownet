# Account Type Assignment - Summary

**Last Updated:** December 1, 2025
**Status:** Simplified & Production Ready

## Simple Rules

### Public Registration (Automatic)

```
Registration Form:
├─ Name
├─ Email  
├─ Phone
├─ Password
└─ Referral Code (optional)

Assignment Logic:
├─ Has referral code? → MEMBER (MLM access)
└─ No referral code?  → CLIENT (no MLM)
```

**That's it!** No account type selection needed.

---

### Admin-Only Accounts

**INVESTOR and EMPLOYEE accounts can ONLY be created by admins:**

| Account Type | Created By | Purpose |
|--------------|------------|---------|
| INVESTOR | Admin | MyGrowNet shareholders (Venture Builder) |
| EMPLOYEE | Admin | Internal staff |
| BUSINESS | Admin | When user subscribes to business tools |

---

## When Account Type is Assigned

### 1. During Registration (Public)
```php
// User submits registration form
User::create([
    'name' => $data['name'],
    'email' => $data['email'],
    'phone' => $data['phone'],
    'password' => Hash::make($data['password']),
    'referrer_id' => $referrer?->id, // ← KEY FIELD
    // account_types assigned automatically in boot():
    // - If referrer_id exists → ['member']
    // - If no referrer_id → ['client']
]);
```

### 2. In User Model Boot (Automatic)
```php
static::creating(function ($user) {
    if (!$user->account_types) {
        $accountType = $user->referrer_id 
            ? AccountType::MEMBER 
            : AccountType::CLIENT;
        
        $user->account_types = [$accountType->value];
    }
});
```

### 3. By Admin (Manual)
```php
// Admin creates investor
$user = User::create([
    'name' => $data['name'],
    'email' => $data['email'],
    'account_types' => [AccountType::INVESTOR->value], // ← ADMIN SETS
]);

// Admin creates employee
$user = User::create([
    'name' => $data['name'],
    'email' => $data['email'],
    'account_types' => [AccountType::EMPLOYEE->value], // ← ADMIN SETS
]);

// Admin adds business tools
$user->addAccountType(AccountType::BUSINESS);
```

---

## Registration Flow Examples

### Example 1: Member (With Referral)
```
1. User clicks: mygrownet.com/register?ref=MGN123ABC
2. Form pre-fills referral code
3. User fills: Name, Email, Phone, Password
4. User submits
5. System finds referrer → Sets account_types = ['member']
6. User redirected to MLM dashboard
```

### Example 2: Client (No Referral)
```
1. User visits: mygrownet.com/register
2. User fills: Name, Email, Phone, Password
3. User leaves referral code empty
4. User submits
5. No referrer found → Sets account_types = ['client']
6. User redirected to Home Hub/Marketplace
```

### Example 3: Investor (Admin Created)
```
1. Admin logs into admin panel
2. Admin creates investor account
3. Admin sets account_types = ['investor']
4. System sends invitation email
5. Investor sets password and logs in
6. Investor accesses Investor Portal
```

### Example 4: Employee (Admin Created)
```
1. Admin/HR logs into admin panel
2. Admin creates employee account
3. Admin sets account_types = ['employee']
4. System sends invitation email
5. Employee sets password and logs in
6. Employee accesses Employee Portal
```

---

## Account Type Upgrades

### Client → Member (User Initiated)
```
1. CLIENT user clicks "Upgrade to Member"
2. User pays K150 registration + K50/month subscription
3. System adds 'member' to account_types
4. User now has: account_types = ['client', 'member']
5. User gains MLM access
```

### Any → Business (Admin Initiated)
```
1. User subscribes to business tools
2. Admin verifies payment
3. Admin adds 'business' to account_types
4. User gains business tools access
```

### Any → Investor (Admin Initiated)
```
1. User invests in Venture Builder project
2. Admin verifies investment
3. Admin adds 'investor' to account_types
4. User gains Investor Portal access
```

---

## Key Points

1. ✅ **Simple registration** - No account type selection
2. ✅ **Automatic assignment** - Based on referral code
3. ✅ **Admin control** - INVESTOR and EMPLOYEE only by admin
4. ✅ **Multi-account support** - Users can have multiple types
5. ✅ **Secure** - Special accounts require admin authorization

---

## Implementation Status

### ✅ Completed
- [x] AccountType enum with all 5 types
- [x] User model boot method for automatic assignment
- [x] Helper methods (hasAccountType, addAccountType, etc.)
- [x] Documentation complete

### 🚧 Ready to Implement
- [ ] Update registration controller
- [ ] Create admin controllers for INVESTOR/EMPLOYEE
- [ ] Add account type management UI
- [ ] Test all flows

---

## Files to Review

- **Registration Flow:** `docs/account-types/REGISTRATION_FLOW.md`
- **Complete Guide:** `docs/account-types/USER_TYPES_AND_ACCESS_MODEL.md`
- **Implementation:** `docs/account-types/IMPLEMENTATION_GUIDE.md`
- **User Model:** `app/Models/User.php` (boot method)
- **Account Type Enum:** `app/Enums/AccountType.php`

---

**Bottom Line:** Registration is simple. Referral code = MEMBER, no code = CLIENT. INVESTOR and EMPLOYEE are admin-only.
# Account Type Assignment - Quick Summary

**Date:** December 1, 2025

## When is Account Type Assigned?

**Answer:** During user creation, in the User model's `boot()` method, **before** the user is saved to the database.

---

## How It Works (Current Implementation)

### Simple Logic

```php
// In User model boot() method - static::creating()

if (!$user->account_type) {
    $user->account_type = $user->referrer_id 
        ? AccountType::MEMBER->value   // Has referrer = MEMBER
        : AccountType::CLIENT->value;  // No referrer = CLIENT
}
```

### Decision Tree

```
User Registration
    ↓
User::create() called
    ↓
boot() method runs
    ↓
Check: Does user have referrer_id?
    ↓
YES ──→ account_type = 'member' (MLM participant)
    ↓
NO ───→ account_type = 'client' (App/shop user)
    ↓
User saved to database
    ↓
Role assigned based on account_type
```

---

## Examples

### Example 1: Registration WITH Referral Code

```
1. User clicks: mygrownet.com/register?ref=MGN123ABC
2. Form pre-fills referral code
3. User submits form
4. Backend finds referrer (user ID 123)
5. User::create(['referrer_id' => 123, ...])
6. boot() sees referrer_id exists
7. Sets account_type = 'member' ← ASSIGNED HERE
8. User saved with MEMBER account type
9. MLM rules apply ✅
```

### Example 2: Registration WITHOUT Referral Code

```
1. User clicks: "Sign Up" button
2. Form has no referral code
3. User submits form
4. User::create(['referrer_id' => null, ...])
5. boot() sees referrer_id is null
6. Sets account_type = 'client' ← ASSIGNED HERE
7. User saved with CLIENT account type
8. MLM rules DO NOT apply ❌
```

---

## Timeline

```
Form Submission
    ↓ (milliseconds)
User::create() called
    ↓ (microseconds)
boot() method - creating hook
    ↓ (microseconds)
Account type assigned based on referrer_id
    ↓ (microseconds)
User saved to database
    ↓ (milliseconds)
boot() method - created hook
    ↓ (milliseconds)
Role assigned
    ↓
Complete!
```

**Total time:** Less than 1 second

---

## Key Points

1. ✅ **Automatic** - No manual intervention needed
2. ✅ **Before Save** - Happens before database insert
3. ✅ **Based on Referrer** - referrer_id determines type
4. ✅ **Dual Column** - Sets both `account_type` and `account_types`
5. ✅ **Role Assignment** - Role assigned after creation based on type

---

## Account Type Results

| Has Referrer? | account_type | account_types | Role | MLM Rules |
|---------------|--------------|---------------|------|-----------|
| ✅ YES | member | ['member'] | Member | ✅ YES |
| ❌ NO | client | ['client'] | Client | ❌ NO |

---

## Can It Change Later?

**Yes!** Users can gain additional account types:

```php
// User upgrades from CLIENT to MEMBER
$user->addAccountType(AccountType::MEMBER);
// Now: account_types = ['client', 'member']

// User invests in project, becomes INVESTOR
$user->addAccountType(AccountType::INVESTOR);
// Now: account_types = ['client', 'member', 'investor']
```

---

## Summary

**Q: When is account type assigned?**
**A:** During user creation, before database save

**Q: How is it determined?**
**A:** Based on referrer_id (has referrer = MEMBER, no referrer = CLIENT)

**Q: Is it automatic?**
**A:** Yes, completely automatic

**Q: Can it be changed?**
**A:** Yes, additional account types can be added later

**Q: Where does it happen?**
**A:** In User model's `boot()` method, `static::creating()` hook

---

**For complete details:** See `docs/account-types/ACCOUNT_TYPE_ASSIGNMENT_FLOW.md`
