# Default Sponsor System

## Overview

The Default Sponsor System ensures that all new members are placed in the network structure, even if they register without a referral code. This prevents "orphan" registrations and maintains a proper MLM/network hierarchy.

## How It Works

### Registration Flow

**With Referral Code:**
```
User registers → Validates referral code → Places under referrer → Awards points
```

**Without Referral Code:**
```
User registers → Uses default sponsor → Places under admin/company → Awards points
```

### Default Sponsor Selection

The system selects the default sponsor in this order:

1. **Configured Email** - User with email from `DEFAULT_SPONSOR_EMAIL` env variable (default: `admin@mygrownet.com`)
2. **Administrator Role** - First user with "Administrator" role
3. **First User** - Oldest registered user (if `USE_FIRST_USER_AS_DEFAULT=true`)
4. **None** - If `USE_DEFAULT_SPONSOR=false`, users without referral codes are placed as root

## Configuration

### Environment Variables

Add to your `.env` file:

```env
# Enable/disable default sponsor feature
USE_DEFAULT_SPONSOR=true

# Default sponsor email (typically admin/company account)
DEFAULT_SPONSOR_EMAIL=admin@mygrownet.com

# Alternative: Use first registered user as default
USE_FIRST_USER_AS_DEFAULT=false
```

### Config File

Located at `config/referral.php`:

```php
return [
    'use_default_sponsor' => env('USE_DEFAULT_SPONSOR', true),
    'default_sponsor_email' => env('DEFAULT_SPONSOR_EMAIL', 'admin@mygrownet.com'),
    'use_first_user_as_default' => env('USE_FIRST_USER_AS_DEFAULT', false),
    // ... other settings
];
```

## Current Setup

### Default Sponsor

**Email:** admin@mygrownet.com  
**Name:** Admin User  
**Role:** Administrator  
**Created:** During database seeding

### Behavior

When a user registers **without** a referral code:
- ✅ Automatically placed under admin@mygrownet.com
- ✅ Admin receives referral points
- ✅ User is added to admin's network tree
- ✅ Commissions flow to admin for orphan registrations

When a user registers **with** a referral code:
- ✅ Placed under the specified referrer
- ✅ Referrer receives referral points
- ✅ User is added to referrer's network tree
- ✅ Commissions flow to referrer

## Benefits

### For the Platform

1. **Complete Network Structure** - No orphan users
2. **Revenue Tracking** - All registrations tracked under company account
3. **Commission Accuracy** - Proper commission calculations
4. **Network Analytics** - Complete network visibility

### For Members

1. **Always Connected** - Every member is part of the network
2. **Fair Placement** - Consistent placement rules
3. **Spillover Benefits** - Company-sponsored users can spill over to active members
4. **Network Growth** - Company marketing efforts benefit the entire network

## Admin Dashboard

The admin user (default sponsor) can view:

- Total direct referrals (including orphan registrations)
- Complete network tree
- Commission earnings from orphan registrations
- Network statistics and growth

## Matrix Placement

### With Default Sponsor

```
                    Admin (Root)
                   /      |      \
              User1    User2    User3
             (code)  (no code) (code)
```

### Without Default Sponsor (Old Behavior)

```
    User1 (Root)    User2 (Root)    User3 (Root)
     (code)         (no code)        (code)
```

## Service Class

### DefaultSponsorService

Located at `app/Services/DefaultSponsorService.php`

**Methods:**

```php
// Get the default sponsor user
$sponsor = $defaultSponsorService->getDefaultSponsor();

// Check if default sponsor exists
$hasDefault = $defaultSponsorService->hasDefaultSponsor();

// Get default sponsor's referral code
$code = $defaultSponsorService->getDefaultReferralCode();

// Clear cached default sponsor
$defaultSponsorService->clearCache();
```

**Caching:**

The default sponsor is cached for 1 hour to improve performance. Clear cache after:
- Changing default sponsor email
- Updating admin user
- Modifying configuration

```bash
php artisan cache:clear
```

## Testing

### Test Default Sponsor

```bash
# Register without referral code
curl -X POST https://mygrownet.com/register \
  -d "name=Test User" \
  -d "email=test@example.com" \
  -d "password=password" \
  -d "password_confirmation=password"

# Check user's referrer
php artisan tinker
>>> $user = User::where('email', 'test@example.com')->first();
>>> $user->referrer; // Should be admin@mygrownet.com
```

### Test With Referral Code

```bash
# Get admin's referral code
php artisan tinker
>>> $admin = User::where('email', 'admin@mygrownet.com')->first();
>>> $admin->referral_code;

# Register with referral code
curl -X POST https://mygrownet.com/register \
  -d "name=Test User 2" \
  -d "email=test2@example.com" \
  -d "password=password" \
  -d "password_confirmation=password" \
  -d "referral_code=MGN12345"
```

## Troubleshooting

### Issue: Users Not Placed Under Default Sponsor

**Check:**
1. Is `USE_DEFAULT_SPONSOR=true` in `.env`?
2. Does admin@mygrownet.com exist?
3. Clear cache: `php artisan cache:clear`

**Solution:**
```bash
# Verify default sponsor
php artisan tinker
>>> app(App\Services\DefaultSponsorService::class)->getDefaultSponsor();
```

### Issue: Default Sponsor Not Found

**Check:**
1. Run database seeders: `php artisan db:seed --class=UserSeeder`
2. Verify admin user exists: `User::where('email', 'admin@mygrownet.com')->first()`
3. Check config: `config('referral.default_sponsor_email')`

**Solution:**
```bash
# Create admin user manually
php artisan tinker
>>> User::create([
    'name' => 'Admin User',
    'email' => 'admin@mygrownet.com',
    'password' => Hash::make('mygrownet@2025!'),
    'email_verified_at' => now(),
]);
```

### Issue: Wrong User as Default Sponsor

**Solution:**
```bash
# Update .env
DEFAULT_SPONSOR_EMAIL=your-preferred-admin@mygrownet.com

# Clear cache
php artisan cache:clear
php artisan config:clear
```

## Migration Guide

### Existing Users Without Referrer

If you have existing users without a referrer, you can migrate them:

```php
// Run in tinker or create a migration
$defaultSponsor = app(App\Services\DefaultSponsorService::class)->getDefaultSponsor();

User::whereNull('referrer_id')
    ->where('id', '!=', $defaultSponsor->id) // Don't update admin itself
    ->update(['referrer_id' => $defaultSponsor->id]);
```

## Best Practices

1. **Set Up Early** - Configure default sponsor before launching
2. **Use Company Account** - Default sponsor should be a company/admin account
3. **Monitor Orphans** - Track registrations without referral codes
4. **Clear Cache** - After configuration changes
5. **Test Thoroughly** - Test both with and without referral codes

## Security Considerations

1. **Admin Account** - Secure the default sponsor account
2. **Referral Code** - Don't publicly share admin's referral code
3. **Commission Tracking** - Monitor commissions to default sponsor
4. **Access Control** - Restrict who can change default sponsor settings

## Analytics

Track default sponsor performance:

```php
$admin = User::where('email', 'admin@mygrownet.com')->first();

// Direct referrals (including orphans)
$directReferrals = $admin->referrals()->count();

// Orphan registrations (no referral code used)
$orphans = $admin->referrals()
    ->whereDoesntHave('referralUsed')
    ->count();

// Total network size
$networkSize = $admin->getAllDownlineMembers()->count();

// Commission earnings
$commissions = $admin->commissions()->sum('amount');
```

## Summary

✅ **Enabled by default**  
✅ **Uses admin@mygrownet.com as default sponsor**  
✅ **Configurable via environment variables**  
✅ **Cached for performance**  
✅ **Maintains complete network structure**  

All users registering without a referral code are automatically placed under the admin/company account, ensuring a complete and trackable network structure.
