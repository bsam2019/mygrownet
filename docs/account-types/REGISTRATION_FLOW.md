# Account Type Registration Flow

**Last Updated:** December 1, 2025
**Status:** Simplified Implementation

## Overview

MyGrowNet uses a simple, automatic account type assignment during registration. Users don't manually select their account type - it's determined automatically.

---

## Automatic Assignment Rules

### 1. Public Registration (Simple Form)

**Rule:** Account type is determined by presence of referral code

```
Has referral code? 
├─ YES → MEMBER (MLM participant)
└─ NO  → CLIENT (app/shop user)
```

**Registration Form Fields:**
- Name
- Email
- Phone
- Password
- Referral Code (optional)

**That's it!** No account type selection needed.

---

### 2. Admin-Created Accounts

**INVESTOR and EMPLOYEE accounts can ONLY be created by admins:**

- **INVESTOR** - For MyGrowNet shareholders (Venture Builder)
- **EMPLOYEE** - For internal staff

**Process:**
1. Admin logs into admin panel
2. Admin creates user account
3. Admin assigns INVESTOR or EMPLOYEE account type
4. User receives invitation email
5. User sets password and logs in

---

## Registration Flow Examples

### Example 1: Member Registration (With Referral)

```
User Journey:
1. User receives referral link: mygrownet.com/register?ref=MGN123ABC
2. User clicks link
3. Registration form opens with referral code pre-filled
4. User fills: Name, Email, Phone, Password
5. User submits form

Backend Process:
1. System detects referral_id (from code MGN123ABC)
2. System creates user with:
   - referrer_id = [ID of referrer]
   - account_types = ['member']  ← AUTOMATIC
3. System assigns "Member" role
4. User redirected to MLM dashboard

Result: User is now a MEMBER with MLM access
```

### Example 2: Client Registration (No Referral)

```
User Journey:
1. User browses marketplace or modules
2. User clicks "Sign Up" or "Create Account"
3. Registration form opens (no referral code)
4. User fills: Name, Email, Phone, Password
5. User submits form

Backend Process:
1. System detects NO referral_id
2. System creates user with:
   - referrer_id = null
   - account_types = ['client']  ← AUTOMATIC
3. System assigns "Client" role
4. User redirected to Home Hub or Marketplace

Result: User is now a CLIENT (no MLM access)
```

### Example 3: Investor Account (Admin Created)

```
Admin Process:
1. Admin logs into admin panel
2. Admin navigates to "Investors" section
3. Admin clicks "Add Investor"
4. Admin fills:
   - Name
   - Email
   - Phone
   - Investment amount
   - Project details
5. Admin submits form

Backend Process:
1. System creates user with:
   - account_types = ['investor']  ← ADMIN ASSIGNED
   - Temporary password generated
2. System sends invitation email
3. Investor clicks link, sets password
4. Investor logs in to Investor Portal

Result: User is now an INVESTOR with portfolio access
```

### Example 4: Employee Account (Admin Created)

```
Admin/HR Process:
1. Admin logs into admin panel
2. Admin navigates to "Employees" section
3. Admin clicks "Add Employee"
4. Admin fills:
   - Name
   - Email
   - Phone
   - Department
   - Role (Support, Manager, etc.)
5. Admin submits form

Backend Process:
1. System creates user with:
   - account_types = ['employee']  ← ADMIN ASSIGNED
   - Temporary password generated
2. System sends invitation email
3. Employee clicks link, sets password
4. Employee logs in to Employee Portal

Result: User is now an EMPLOYEE with internal access
```

---

## Account Type Upgrades

### Client → Member (User Initiated)

```
User Journey:
1. CLIENT user logs in
2. User sees "Upgrade to Member" option
3. User clicks upgrade
4. User pays registration fee (K150)
5. User selects subscription plan (K50/month)
6. User provides referral code (optional)

Backend Process:
1. Payment processed
2. System adds MEMBER to account_types:
   - account_types = ['client', 'member']
3. System assigns "Member" role
4. User now has access to both CLIENT and MEMBER features

Result: User is now both CLIENT and MEMBER
```

### Client → Business (Admin Initiated)

```
Process:
1. CLIENT user subscribes to business tools
2. Admin verifies subscription payment
3. Admin adds BUSINESS to user's account_types:
   - account_types = ['client', 'business']
4. User gains access to business tools

Result: User is now both CLIENT and BUSINESS
```

### Member → Investor (Admin Initiated)

```
Process:
1. MEMBER user invests in Venture Builder project
2. Admin verifies investment
3. Admin adds INVESTOR to user's account_types:
   - account_types = ['member', 'investor']
4. User gains access to Investor Portal

Result: User is now both MEMBER and INVESTOR
```

---

## Implementation Code

### Registration Controller

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\AccountType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'referral_code' => 'nullable|string|exists:users,referral_code',
        ]);

        // Find referrer if code provided
        $referrer = null;
        if (!empty($validated['referral_code'])) {
            $referrer = User::where('referral_code', $validated['referral_code'])->first();
        }

        // Create user - account type assigned automatically in boot method
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'referrer_id' => $referrer?->id,
            // account_types will be set automatically:
            // - If referrer_id exists → ['member']
            // - If no referrer_id → ['client']
        ]);

        // Log user in
        auth()->login($user);

        // Redirect based on account type
        if ($user->hasAccountType(AccountType::MEMBER)) {
            return redirect()->route('dashboard')
                ->with('success', 'Welcome to MyGrowNet! You are now a member.');
        }

        return redirect()->route('home-hub')
            ->with('success', 'Welcome to MyGrowNet! Explore our marketplace and apps.');
    }
}
```

### Admin - Create Investor

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\AccountType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InvestorController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|unique:users',
            'investment_amount' => 'required|numeric|min:5000',
            'project_id' => 'required|exists:venture_projects,id',
        ]);

        // Generate temporary password
        $tempPassword = Str::random(12);

        // Create investor account
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($tempPassword),
            'account_types' => [AccountType::INVESTOR->value], // ← ADMIN ASSIGNED
        ]);

        // Create investment record
        // ... investment logic here ...

        // Send invitation email with temp password
        // ... email logic here ...

        return back()->with('success', 'Investor account created successfully.');
    }
}
```

### Admin - Create Employee

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\AccountType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|unique:users',
            'department' => 'required|string',
            'role' => 'required|string',
        ]);

        // Generate temporary password
        $tempPassword = Str::random(12);

        // Create employee account
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($tempPassword),
            'account_types' => [AccountType::EMPLOYEE->value], // ← ADMIN ASSIGNED
        ]);

        // Assign role based on department
        $user->assignRole($validated['role']);

        // Send invitation email with temp password
        // ... email logic here ...

        return back()->with('success', 'Employee account created successfully.');
    }
}
```

### Admin - Manage Account Types

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\AccountType;
use Illuminate\Http\Request;

class AccountTypeController extends Controller
{
    /**
     * Add account type to user (admin only)
     */
    public function add(Request $request, User $user)
    {
        $request->validate([
            'account_type' => 'required|in:member,client,business,investor,employee',
        ]);

        $type = AccountType::from($request->account_type);
        
        // Prevent adding INVESTOR or EMPLOYEE without proper authorization
        if (in_array($type, [AccountType::INVESTOR, AccountType::EMPLOYEE])) {
            if (!auth()->user()->hasRole('Super Admin')) {
                abort(403, 'Only Super Admins can assign INVESTOR or EMPLOYEE account types.');
            }
        }

        $user->addAccountType($type);

        return back()->with('success', "Account type {$type->label()} added successfully.");
    }

    /**
     * Remove account type from user (admin only)
     */
    public function remove(Request $request, User $user)
    {
        $request->validate([
            'account_type' => 'required|in:member,client,business,investor,employee',
        ]);

        $type = AccountType::from($request->account_type);
        
        // Prevent removing last account type
        if (count($user->account_types) === 1) {
            return back()->withErrors(['error' => 'Cannot remove the last account type.']);
        }

        $user->removeAccountType($type);

        return back()->with('success', "Account type {$type->label()} removed successfully.");
    }
}
```

---

## Registration Form (Vue)

### Simple Registration Form

```vue
<template>
  <div class="max-w-md mx-auto p-6">
    <h2 class="text-2xl font-bold mb-6">Create Your Account</h2>

    <form @submit.prevent="register">
      <!-- Name -->
      <div class="mb-4">
        <label class="block text-sm font-medium mb-2">Full Name</label>
        <input
          v-model="form.name"
          type="text"
          required
          class="w-full px-4 py-2 border rounded-lg"
        />
      </div>

      <!-- Email -->
      <div class="mb-4">
        <label class="block text-sm font-medium mb-2">Email</label>
        <input
          v-model="form.email"
          type="email"
          required
          class="w-full px-4 py-2 border rounded-lg"
        />
      </div>

      <!-- Phone -->
      <div class="mb-4">
        <label class="block text-sm font-medium mb-2">Phone Number</label>
        <input
          v-model="form.phone"
          type="tel"
          required
          class="w-full px-4 py-2 border rounded-lg"
        />
      </div>

      <!-- Password -->
      <div class="mb-4">
        <label class="block text-sm font-medium mb-2">Password</label>
        <input
          v-model="form.password"
          type="password"
          required
          minlength="8"
          class="w-full px-4 py-2 border rounded-lg"
        />
      </div>

      <!-- Confirm Password -->
      <div class="mb-4">
        <label class="block text-sm font-medium mb-2">Confirm Password</label>
        <input
          v-model="form.password_confirmation"
          type="password"
          required
          class="w-full px-4 py-2 border rounded-lg"
        />
      </div>

      <!-- Referral Code (Optional) -->
      <div class="mb-6">
        <label class="block text-sm font-medium mb-2">
          Referral Code (Optional)
        </label>
        <input
          v-model="form.referral_code"
          type="text"
          class="w-full px-4 py-2 border rounded-lg"
          placeholder="Enter referral code if you have one"
        />
        <p class="text-sm text-gray-500 mt-1">
          Have a referral code? Enter it to join as a member and access MLM features.
        </p>
      </div>

      <!-- Submit -->
      <button
        type="submit"
        class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700"
      >
        Create Account
      </button>
    </form>

    <!-- Info Box -->
    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
      <p class="text-sm text-blue-800">
        <strong>Note:</strong> 
        {{ form.referral_code 
          ? 'You will join as a Member with full MLM access.' 
          : 'You will join as a Client with access to marketplace and apps.' 
        }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  referralCode: String, // Pre-filled from URL
});

const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  referral_code: props.referralCode || '',
});

const register = () => {
  router.post('/register', form);
};
</script>
```

---

## Summary

### Public Registration
- **Simple form** - No account type selection
- **Automatic assignment** - Based on referral code
- **Has referral code** → MEMBER
- **No referral code** → CLIENT

### Admin-Created Accounts
- **INVESTOR** - Created by admin for shareholders
- **EMPLOYEE** - Created by admin for staff
- **BUSINESS** - Added by admin when user subscribes

### Account Type Changes
- **Only admins** can add/remove account types
- **Users can upgrade** from CLIENT to MEMBER (self-service)
- **All other changes** require admin approval

### Key Benefits
- ✅ Simple user experience
- ✅ Automatic MLM assignment
- ✅ Secure investor/employee creation
- ✅ Flexible multi-account support
- ✅ Admin control over special accounts

---

**This is the simplified, production-ready flow.**
