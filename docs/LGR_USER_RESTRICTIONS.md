# LGR User-Specific Restrictions

## Overview

You can now restrict LGR withdrawals for specific users in addition to the global settings. This is useful for:
- Accounts under investigation
- Users with compliance issues
- Special cases requiring custom withdrawal limits
- Testing or demo accounts

## Features

### 1. Custom Withdrawal Percentage
Set a custom percentage for specific users that overrides the global setting.

### 2. Complete Withdrawal Block
Completely block a user from withdrawing/transferring LGR.

### 3. Restriction Reason
Add a note explaining why the restriction was applied (visible to user).

## Database Fields

Three new fields in the `users` table:

| Field | Type | Description |
|-------|------|-------------|
| `lgr_custom_withdrawable_percentage` | decimal(5,2) | Custom percentage for this user (NULL = use global) |
| `lgr_withdrawal_blocked` | boolean | If true, user cannot withdraw LGR at all |
| `lgr_restriction_reason` | text | Explanation shown to user |

## How to Use

### Method 1: Via Database (Tinker)

```bash
php artisan tinker
```

**Set custom percentage (e.g., 20% instead of global 40%):**
```php
$user = User::find(123);
$user->lgr_custom_withdrawable_percentage = 20;
$user->save();
```

**Block withdrawals completely:**
```php
$user = User::find(123);
$user->lgr_withdrawal_blocked = true;
$user->lgr_restriction_reason = 'Account under review for compliance verification';
$user->save();
```

**Remove restrictions:**
```php
$user = User::find(123);
$user->lgr_custom_withdrawable_percentage = null; // Use global setting
$user->lgr_withdrawal_blocked = false;
$user->lgr_restriction_reason = null;
$user->save();
```

### Method 2: Via SQL

**Set custom percentage:**
```sql
UPDATE users 
SET lgr_custom_withdrawable_percentage = 20 
WHERE id = 123;
```

**Block withdrawals:**
```sql
UPDATE users 
SET lgr_withdrawal_blocked = 1,
    lgr_restriction_reason = 'Account under review'
WHERE id = 123;
```

**Remove restrictions:**
```sql
UPDATE users 
SET lgr_custom_withdrawable_percentage = NULL,
    lgr_withdrawal_blocked = 0,
    lgr_restriction_reason = NULL
WHERE id = 123;
```

## User Experience

### When Custom Percentage is Set
- User sees their custom percentage instead of global
- Example: "Up to K200 withdrawable (20% of awarded)" instead of "40% of awarded"
- Transfer button works normally within their limit

### When Withdrawals are Blocked
- Transfer button is disabled and shows "Transfer Disabled"
- Red warning box appears: "LGR Withdrawals Restricted"
- Restriction reason is displayed if provided
- User cannot initiate any LGR transfers

## Examples

### Example 1: Reduce Limit for Suspicious Activity
```php
$user = User::where('email', 'suspicious@example.com')->first();
$user->lgr_custom_withdrawable_percentage = 10; // Only 10% instead of 40%
$user->lgr_restriction_reason = 'Reduced limit due to unusual activity. Contact support.';
$user->save();
```

### Example 2: Temporary Block During Investigation
```php
$user = User::find(456);
$user->lgr_withdrawal_blocked = true;
$user->lgr_restriction_reason = 'Account temporarily restricted pending verification. Please contact support@mygrownet.com';
$user->save();
```

### Example 3: VIP User with Higher Limit
```php
$user = User::where('email', 'vip@example.com')->first();
$user->lgr_custom_withdrawable_percentage = 80; // 80% instead of 40%
$user->save();
```

### Example 4: Bulk Restriction
```php
// Block all users in a specific group
User::whereIn('id', [123, 456, 789])->update([
    'lgr_withdrawal_blocked' => true,
    'lgr_restriction_reason' => 'Temporary restriction during system maintenance'
]);
```

## Priority Order

The system checks restrictions in this order:

1. **Is user blocked?** → If yes, withdrawable = 0
2. **Has custom percentage?** → Use custom percentage
3. **Otherwise** → Use global setting from `lgr_max_cash_conversion`

## Admin Interface (Future Enhancement)

Consider adding to the Admin Users page:
- Column showing LGR restriction status
- Quick action buttons to block/unblock
- Modal to set custom percentage and reason
- Filter to show only restricted users

## Monitoring

To see all restricted users:

```sql
-- Users with custom percentages
SELECT id, name, email, lgr_custom_withdrawable_percentage 
FROM users 
WHERE lgr_custom_withdrawable_percentage IS NOT NULL;

-- Blocked users
SELECT id, name, email, lgr_restriction_reason 
FROM users 
WHERE lgr_withdrawal_blocked = 1;
```

## Best Practices

1. **Always provide a reason** when blocking or restricting
2. **Document restrictions** in your admin notes
3. **Review restrictions regularly** - don't leave users blocked indefinitely
4. **Communicate with users** - let them know why and how to resolve
5. **Use custom percentages** for gradual restrictions before full blocks

## Testing

Test the restrictions:

```php
// Test user
$user = User::find(1);

// Test 1: Custom percentage
$user->lgr_custom_withdrawable_percentage = 25;
$user->save();
// Visit wallet page - should show 25% limit

// Test 2: Block
$user->lgr_withdrawal_blocked = true;
$user->lgr_restriction_reason = 'Test restriction';
$user->save();
// Visit wallet page - should show red warning and disabled button

// Test 3: Try to transfer (should fail)
// Attempt transfer via wallet page - should show error message

// Clean up
$user->lgr_custom_withdrawable_percentage = null;
$user->lgr_withdrawal_blocked = false;
$user->lgr_restriction_reason = null;
$user->save();
```
