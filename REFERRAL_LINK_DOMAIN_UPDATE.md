# Referral Link Domain Update

## Issue
The referral links on the "My Team" page are showing "vbif.com" instead of "mygrownet.com" (or your actual domain).

## Root Cause
Referral links are generated dynamically using Laravel's `url()` helper function, which uses the `APP_URL` environment variable from the `.env` file.

## Solution

### Update the .env file

Open your `.env` file in the project root and update the `APP_URL` value:

**Current (incorrect):**
```env
APP_URL=http://vbif.com
```

**Update to:**
```env
APP_URL=http://mygrownet.com
# or for local development:
APP_URL=http://127.0.0.1:8001
# or your actual production domain:
APP_URL=https://yourdomain.com
```

### Clear Configuration Cache

After updating the `.env` file, clear the configuration cache:

```bash
php artisan config:clear
php artisan cache:clear
```

## Where Referral Links Are Generated

The referral links are generated in the following locations:

1. **ReferralController** (`app/Http/Controllers/ReferralController.php`):
   - Line 50: `$referralLink = url('/register?ref=' . $user->referral_code);`
   - Line 134: `$link = url('/register?ref=' . $user->referral_code);`
   - Line 227: `'referral_link' => url('/register?ref=' . $user->referral_code)`

2. **MatrixController** (`app/Http/Controllers/MatrixController.php`):
   - Line 42: `'referralLink' => $user->referral_code ? url('/register?ref=' . $user->referral_code) : url('/register')`
   - Line 55: `'referral_link' => url('/register?ref=' . $code)`
   - Line 62: `'referral_link' => url('/register?ref=' . $user->referral_code)`

3. **MarketingController** (`app/Http/Controllers/Api/MarketingController.php`):
   - Line 66: `'referral_link' => config('app.url') . '/register?ref=' . $user->referral_code`

All these use the `url()` helper or `config('app.url')` which both pull from the `APP_URL` environment variable.

## Message Templates

The message templates in `ReferralService` are already correctly using "MyGrowNet" branding:
- ✅ "Join me on MyGrowNet..."
- ✅ "I've been growing with MyGrowNet..."
- ✅ "...invite you to MyGrowNet..."

## Verification

After updating the `.env` file and clearing cache:

1. Log in to the platform
2. Navigate to "My Team" page
3. Check the referral link - it should now show your correct domain
4. Test copying and sharing the link

## Note

The `url()` helper automatically uses:
- The protocol (http/https) from APP_URL
- The domain from APP_URL
- Appends the path you provide

So `url('/register?ref=ABC123')` becomes:
- `http://yourdomain.com/register?ref=ABC123` (based on your APP_URL)
