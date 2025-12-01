# Account Type Assignment Flow

**Last Updated:** December 1, 2025
**Status:** Current Implementation

## Overview

This document explains exactly when and how account types are assigned to users in the MyGrowNet system.

---

## Current Implementation

### Stage 1: User Registration (Primary Assignment)

Account type is assigned **during user creation** in the User model's `boot()` method:

```php
// app/Models/User.php - boot() method

static::creating(function ($user) {
    // Set account type based on referrer
    if (!$user->account_type) {
        $user->account_type = $user->referrer_id 
            ? AccountType::MEMBER->value 
            : AccountType::CLIENT->value;
    }
});
```

**Logic:**
- ✅ **Has referrer_id** → Account type = **MEMBER**
- ❌ **No referrer_id** → Account type = **CLIENT**

**When it happens:**
- Right before the user record is saved to database
- Automatically during `User::create()`
- No manual intervention needed

---

## Registration Flow Examples

### Example 1: Member Registration (With Referral Code)

```
User Journey:
1. User clicks referral link: mygrownet.com/register?ref=MGN123ABC
   ↓
2. Registration form opens with referral code pre-filled
   ↓
3. User fills out: Name, Email, Phone, Password
   ↓
4. User submits form
   ↓
5. Backend finds referrer by code
   ↓
6. User::create([
     'name' => 'John Doe',
     'email' => 'john@example.com',
     'referrer_id' => 123,  ← Referrer found
     ...
   ])
   ↓
7. boot() method runs BEFORE save
   ↓
8. Checks: referrer_id exists? YES
   ↓
9. Sets: account_type = 'member'  ← ASSIGNED HERE
   ↓
10. Sets: account_types = ['member']  ← Also set by migration
   ↓
11. User saved to database
   ↓
12. created() hook assigns 'Member' role
   ↓
13. User redirected to MLM dashboard
```

**Result:**
- `account_type` = 'member'
- `account_types` = ['member']
- `role` = 'Member'
- **MLM rules apply** ✅

---

### Example 2: Client Registration (No Referral Code)

```
User Journey:
1. User browses marketplace or clicks "Sign Up"
   ↓
2. Registration form opens (no referral code)
   ↓
3. User fills out: Name, Email, Phone, Password
   ↓
4. User submits form
   ↓
5. User::create([
     'name' => 'Jane Smith',
     'email' => 'jane@example.com',
     'referrer_id' => null,  ← No referrer
     ...
   ])
   ↓
6. boot() method runs BEFORE save
   ↓
7. Checks: referrer_id exists? NO
   ↓
8. Sets: account_type = 'client'  ← ASSIGNED HERE
   ↓
9. Sets: account_types = ['client']  ← Also set by migration
   ↓
10. User saved to database
   ↓
11. created() hook assigns 'Client' role
   ↓
12. User redirected to marketplace/home hub
```

**Result:**
- `account_type` = 'client'
- `account_types` = ['client']
- `role` = 'Client'
- **MLM rules DO NOT apply** ❌

---

## Timeline Diagram

```
Registration Form Submission
         ↓
    User::create() called
         ↓
    boot() method triggered
         ↓
    static::creating() runs
         ↓
    ┌─────────────────────────┐
    │ Check referrer_id       │
    └─────────────────────────┘
         ↓
    ┌─────────────────────────┐
    │ Has referrer_id?        │
    └─────────────────────────┘
         ↓
    YES ↓         ↓ NO
        ↓         ↓
    MEMBER    CLIENT
        ↓         ↓
    account_type = 'member'
    account_types = ['member']
        ↓
    User saved to database
        ↓
    static::created() runs
        ↓
    Assign role based on account_type
        ↓
    User creation complete
```

---

## Code Flow

### 1. Registration Controller

```php
// Typical registration controller
public function register(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string',
        'email' => 'required|email|unique:users',
        'phone' => 'required|string',
        'password' => 'required|min:8',
        'referral_code' => 'nullable|string',
    ]);

    // Find referrer if code provided
    $referrer = null;
    if (!empty($validated['referral_code'])) {
        $referrer = User::where('referral_code', $validated['referral_code'])->first();
    }

    // Create user
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'phone' => $validated['phone'],
        'password' => Hash::make($validated['password']),
        'referrer_id' => $referrer?->id,  // ← This determines account type
    ]);

    // Account type is already set by boot() method
    // No need to manually set it here

    Auth::login($user);

    return redirect()->route('dashboard');
}
```

### 2. User Model Boot Method

```php
// app/Models/User.php

protected static function boot()
{
    parent::boot();

    static::creating(function ($user) {
        // 1. Generate referral code
        if (!$user->referral_code) {
            do {
                $code = 'MGN' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
            } while (User::where('referral_code', $code)->exists());
            
            $user->referral_code = $code;
        }
        
        // 2. Set account type based on referrer
        if (!$user->account_type) {
            $user->account_type = $user->referrer_id 
                ? AccountType::MEMBER->value   // ← Has referrer = MEMBER
                : AccountType::CLIENT->value;  // ← No referrer = CLIENT
        }
    });

    static::created(function ($user) {
        // 3. Assign role based on account type
        if ($user->account_type !== AccountType::MEMBER->value) {
            if (\Spatie\Permission\Models\Role::where('name', 'Client')->exists()) {
                $user->assignRole('Client');
            }
            return;
        }
        
        if (\Spatie\Permission\Models\Role::where('name', 'Member')->exists()) {
            if ($user->roles()->count() === 0) {
                $user->assignRole('Member');
            }
        }
    });
}
```

### 3. Migration (Dual Column Support)

```php
// database/migrations/2025_12_01_103515_add_account_types_json_to_users_table.php

public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        // Add new JSON column for multi-account type support
        $table->json('account_types')->nullable()->after('account_type');
    });

    // Migrate existing account_type to account_types array
    DB::table('users')->whereNotNull('account_type')->update([
        'account_types' => DB::raw("JSON_ARRAY(account_type)")
    ]);

    // Set default for users without account_type
    DB::table('users')->whereNull('account_type')->update([
        'account_type' => 'client',
        'account_types' => DB::raw("JSON_ARRAY('client')")
    ]);
}
```

---

## Account Type Assignment Summary

| Scenario | referrer_id | account_type | account_types | Role | MLM Rules |
|----------|-------------|--------------|---------------|------|-----------|
| **With referral code** | Set | member | ['member'] | Member | ✅ YES |
| **No referral code** | null | client | ['client'] | Client | ❌ NO |
| **Manual creation** | null | client | ['client'] | Client | ❌ NO |
| **Admin creates** | Depends | Depends | Depends | Depends | Depends |

---

## Special Cases

### Case 1: User Upgrades from CLIENT to MEMBER

```php
// User decides to join MLM later
$user = User::find($userId);

// Add MEMBER account type
$user->addAccountType(AccountType::MEMBER);

// Now user has both types
// account_types = ['client', 'member']

// Assign Member role
$user->assignRole('Member');

// Now MLM rules apply
$user->isMLMParticipant(); // true
```

### Case 2: User Becomes INVESTOR

```php
// User invests in Venture Builder project
$user = User::find($userId);

// Add INVESTOR account type
$user->addAccountType(AccountType::INVESTOR);

// Now user has multiple types
// account_types = ['client', 'investor']
// OR
// account_types = ['member', 'investor']

// User can access both portals
```

### Case 3: Admin Creates EMPLOYEE Account

```php
// Admin creates employee account
$employee = User::create([
    'name' => 'Staff Member',
    'email' => 'staff@mygrownet.com',
    'password' => Hash::make('password'),
    'referrer_id' => null,  // No referrer
    'account_type' => AccountType::EMPLOYEE->value,  // ← Manually set
]);

// Result:
// account_type = 'employee'
// account_types = ['employee']
// No MLM participation
```

---

## Key Points

1. **Automatic Assignment** - Account type is set automatically during user creation
2. **Based on Referrer** - Presence of referrer_id determines MEMBER vs CLIENT
3. **Before Save** - Assignment happens in `creating` hook, before database save
4. **Dual Column** - Both `account_type` (string) and `account_types` (JSON) are set
5. **Role Assignment** - Role is assigned after user creation based on account type
6. **Upgradeable** - Users can gain additional account types later

---

## Future Enhancements

### Planned: Explicit Account Type Selection

In future, registration form will allow users to explicitly choose:

```vue
<template>
  <div class="registration">
    <h2>How would you like to join?</h2>
    
    <label>
      <input type="radio" v-model="accountType" value="member" />
      Join as Member (MLM)
    </label>
    
    <label>
      <input type="radio" v-model="accountType" value="client" />
      Use Apps & Shop (No MLM)
    </label>
    
    <label>
      <input type="radio" v-model="accountType" value="business" />
      Business Tools
    </label>
  </div>
</template>
```

This will override the automatic referrer-based logic.

---

## Troubleshooting

### Issue: User has referrer but is CLIENT
**Cause:** Account type was manually set before creation
**Solution:** Check if account_type was explicitly set in User::create()

### Issue: User has no referrer but is MEMBER
**Cause:** Account type was manually set or user was upgraded
**Solution:** This is valid - users can upgrade to MEMBER later

### Issue: account_type and account_types don't match
**Cause:** Migration didn't run or data inconsistency
**Solution:** Run AccountTypeSeeder to sync data

---

## Summary

**When is account type assigned?**
→ During user creation, in the `creating` hook, before database save

**How is it determined?**
→ Based on presence of `referrer_id`:
- Has referrer → MEMBER
- No referrer → CLIENT

**Can it change?**
→ Yes, users can gain additional account types via `addAccountType()`

**Is it automatic?**
→ Yes, completely automatic based on referrer_id

**Can it be overridden?**
→ Yes, by explicitly setting `account_type` before User::create()

---

**Current Status:** Automatic assignment based on referrer_id
**Future Plan:** Add explicit account type selection in registration form
