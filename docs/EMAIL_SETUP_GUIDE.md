# Email Setup Guide - Dual Provider (Brevo + Gmail)

**Last Updated:** October 30, 2025  
**Total Free Emails:** 800/day (300 Brevo + 500 Gmail)

---

## Overview

MyGrowNet uses a smart dual-provider email system that automatically switches between Brevo and Gmail to maximize free email sending capacity.

**Strategy:**
1. Use Brevo first (better deliverability) - 300 emails/day
2. Switch to Gmail when Brevo limit reached - 500 emails/day
3. Automatic failover if one provider fails

---

## Step 1: Setup Brevo (Primary Provider)

### 1.1 Create Brevo Account

1. Go to https://www.brevo.com
2. Click "Sign up free"
3. Fill in your details:
   - Email: your-email@gmail.com
   - Company: MyGrowNet
   - Country: Zambia
4. Verify your email

### 1.2 Get SMTP Credentials

1. Login to Brevo dashboard
2. Go to **Settings** (top right) → **SMTP & API**
3. Click on **SMTP** tab
4. You'll see:
   ```
   SMTP Server: smtp-relay.brevo.com
   Port: 587
   Login: your-email@gmail.com
   SMTP Key: [Click "Generate new SMTP key"]
   ```
5. Click **"Generate new SMTP key"**
6. Copy the key (looks like: `xsmtpsib-a1b2c3d4...`)
7. **Save this key** - you can't see it again!

### 1.3 Add Sender Email (Your Domain)

1. In Brevo, go to **Settings** → **Senders & IP**
2. Click **"Add a sender"**
3. Enter:
   - Email: `noreply@mygrownet.com` (or your domain email)
   - Name: `MyGrowNet`
4. Brevo will send a verification email
5. Click the verification link in that email

**Note:** If you don't have `noreply@mygrownet.com` yet, you can:
- Use your personal email for now
- Set up email forwarding later
- Or use Gmail address temporarily

### 1.4 Verify Your Domain (Optional but Recommended)

For better deliverability, verify your domain:

1. In Brevo, go to **Settings** → **Senders & IP** → **Domains**
2. Click **"Add a domain"**
3. Enter: `mygrownet.com`
4. Brevo will give you DNS records to add:

```
Type: TXT
Name: @
Value: [Brevo verification code]

Type: TXT
Name: mail._domainkey
Value: [Brevo DKIM key - long string]

Type: TXT
Name: _dmarc
Value: v=DMARC1; p=none
```

5. Add these to your domain's DNS settings (cPanel, Cloudflare, etc.)
6. Wait 24-48 hours for DNS propagation
7. Click "Verify" in Brevo

---

## Step 2: Setup Gmail (Backup Provider)

### 2.1 Create/Use Gmail Account

Option A: Use existing Gmail  
Option B: Create new Gmail for the app (recommended)
- Email: `mygrownet.notifications@gmail.com`
- Password: [strong password]

### 2.2 Enable 2-Factor Authentication

1. Go to https://myaccount.google.com/security
2. Click **"2-Step Verification"**
3. Follow the setup wizard
4. Verify with your phone

### 2.3 Generate App Password

1. Go to https://myaccount.google.com/apppasswords
2. Select:
   - App: **Mail**
   - Device: **Other (Custom name)**
   - Name: `MyGrowNet Laravel`
3. Click **"Generate"**
4. Copy the 16-character password (looks like: `abcd efgh ijkl mnop`)
5. **Save this password** - you can't see it again!

### 2.4 Add Your Domain Email (Optional)

To send from `noreply@mygrownet.com` via Gmail:

1. In Gmail, click **Settings** (gear icon) → **See all settings**
2. Go to **Accounts and Import** tab
3. Click **"Add another email address"**
4. Enter:
   - Name: `MyGrowNet`
   - Email: `noreply@mygrownet.com`
5. Gmail will send verification email to that address
6. Verify it

---

## Step 3: Configure Laravel

### 3.1 Update .env File

Add these lines to your `.env` file:

```env
# Default mailer (will use smart routing)
MAIL_MAILER=brevo
MAIL_FROM_ADDRESS=noreply@mygrownet.com
MAIL_FROM_NAME="MyGrowNet"

# Brevo Configuration (Primary - 300/day)
BREVO_USERNAME=your-email@gmail.com
BREVO_PASSWORD=your-brevo-smtp-key-here

# Gmail Configuration (Backup - 500/day)
GMAIL_USERNAME=mygrownet.notifications@gmail.com
GMAIL_PASSWORD=your-16-char-app-password-here
```

**Example with real values:**
```env
MAIL_MAILER=brevo
MAIL_FROM_ADDRESS=noreply@mygrownet.com
MAIL_FROM_NAME="MyGrowNet"

BREVO_USERNAME=john@gmail.com
BREVO_PASSWORD=xsmtpsib-a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0

GMAIL_USERNAME=mygrownet.notifications@gmail.com
GMAIL_PASSWORD=abcd efgh ijkl mnop
```

### 3.2 Clear Config Cache

```bash
php artisan config:clear
php artisan cache:clear
```

---

## Step 4: Test Email Setup

### 4.1 Test via Tinker

```bash
php artisan tinker
```

Then run:

```php
// Test Brevo
Mail::mailer('brevo')->raw('Test from Brevo', function($msg) {
    $msg->to('your-email@gmail.com')->subject('Brevo Test');
});

// Test Gmail
Mail::mailer('gmail')->raw('Test from Gmail', function($msg) {
    $msg->to('your-email@gmail.com')->subject('Gmail Test');
});

// Test Smart Service
$service = app(\App\Services\SmartEmailService::class);
$stats = $service->getUsageStats();
print_r($stats);
```

### 4.2 Check Your Inbox

You should receive 2 test emails:
1. From Brevo
2. From Gmail

Both should show as from `noreply@mygrownet.com`

---

## Step 5: Usage in Code

### Basic Usage

```php
use App\Services\SmartEmailService;
use App\Mail\InvestmentConfirmation;

// Automatic provider selection
$emailService = app(SmartEmailService::class);
$emailService->send(
    new InvestmentConfirmation($investment),
    $user->email
);
```

### Force Specific Provider

```php
// Force Brevo for critical emails
$emailService->send(
    new PaymentReceipt($payment),
    $user->email,
    'brevo'  // Force Brevo
);

// Force Gmail
$emailService->send(
    new NewsletterEmail(),
    $user->email,
    'gmail'  // Force Gmail
);
```

### Check Usage Stats

```php
$emailService = app(SmartEmailService::class);
$stats = $emailService->getUsageStats();

/*
Returns:
[
    'brevo' => [
        'used' => 245,
        'limit' => 300,
        'remaining' => 55,
        'percentage' => 81.7
    ],
    'gmail' => [
        'used' => 89,
        'limit' => 500,
        'remaining' => 411,
        'percentage' => 17.8
    ],
    'total' => [
        'used' => 334,
        'limit' => 800,
        'remaining' => 466,
        'percentage' => 41.8
    ]
]
*/
```

---

## Troubleshooting

### Problem: "Authentication failed" for Brevo

**Solution:**
1. Check BREVO_USERNAME is correct (your Brevo login email)
2. Check BREVO_PASSWORD is the SMTP key (not your account password)
3. Generate a new SMTP key if needed
4. Run `php artisan config:clear`

### Problem: "Authentication failed" for Gmail

**Solution:**
1. Ensure 2FA is enabled on Gmail account
2. Use App Password, not regular password
3. Remove spaces from the 16-character password
4. Generate new App Password if needed
5. Run `php artisan config:clear`

### Problem: Emails going to spam

**Solution:**
1. Verify your domain in Brevo
2. Add SPF record: `v=spf1 include:spf.brevo.com ~all`
3. Add DKIM records from Brevo
4. Add DMARC record
5. Send test emails and mark as "Not Spam"

### Problem: "Daily limit reached"

**Solution:**
- You've sent 800 emails today!
- Wait until tomorrow (resets at midnight)
- Or upgrade to paid plan
- Check usage: `$emailService->getUsageStats()`

---

## Monitoring

### View Usage Stats

Create an admin route to monitor email usage:

```php
// routes/admin.php
Route::get('/email-stats', function() {
    $service = app(\App\Services\SmartEmailService::class);
    return response()->json($service->getUsageStats());
});
```

### Reset Counters (Testing Only)

```php
$service = app(\App\Services\SmartEmailService::class);
$service->resetCounters();
```

---

## Upgrade Path

When you exceed 800 emails/day:

### Option 1: Brevo Paid Plans
- **Lite:** $25/month - 20,000 emails
- **Business:** $65/month - 100,000 emails

### Option 2: Add More Gmail Accounts
- 3 Gmail accounts = 1,500 emails/day free
- 5 Gmail accounts = 2,500 emails/day free

### Option 3: Mailgun
- Pay-as-you-go: $0.80 per 1,000 emails
- First 100 emails/day free for 3 months

---

## Security Best Practices

1. ✅ Never commit `.env` file to git
2. ✅ Use App Passwords, not regular passwords
3. ✅ Rotate SMTP keys every 6 months
4. ✅ Monitor for suspicious activity
5. ✅ Use different emails for dev/staging/production
6. ✅ Enable 2FA on all email accounts
7. ✅ Keep Laravel and dependencies updated

---

## Summary

✅ **Brevo Setup:** Primary provider (300/day)  
✅ **Gmail Setup:** Backup provider (500/day)  
✅ **Total Capacity:** 800 emails/day FREE  
✅ **Automatic Switching:** Smart routing based on usage  
✅ **Failover:** If one fails, uses the other  
✅ **Professional:** Emails from your domain  

You're all set! 🚀
