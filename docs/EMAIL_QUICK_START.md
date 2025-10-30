# Email Quick Start - 5 Minutes Setup

## Step 1: Brevo (2 minutes)

1. **Sign up:** https://www.brevo.com
2. **Get SMTP key:** Settings → SMTP & API → Generate SMTP key
3. **Copy:** Login email + SMTP key

## Step 2: Gmail (2 minutes)

1. **Enable 2FA:** https://myaccount.google.com/security
2. **App Password:** https://myaccount.google.com/apppasswords
   - App: Mail
   - Device: Other → "MyGrowNet"
3. **Copy:** Gmail address + 16-char password

## Step 3: Configure (1 minute)

Add to `.env`:

```env
MAIL_MAILER=brevo
MAIL_FROM_ADDRESS=noreply@mygrownet.com
MAIL_FROM_NAME="MyGrowNet"

# Brevo (300/day)
BREVO_USERNAME=your-email@gmail.com
BREVO_PASSWORD=xsmtpsib-xxxxx

# Gmail (500/day)
GMAIL_USERNAME=your-gmail@gmail.com
GMAIL_PASSWORD=xxxx xxxx xxxx xxxx
```

## Step 4: Test

```bash
php artisan tinker
```

```php
Mail::mailer('brevo')->raw('Test', fn($m) => $m->to('you@email.com')->subject('Test'));
```

## Done! 🎉

**Total capacity:** 800 emails/day FREE

**Usage in code:**
```php
$service = app(\App\Services\SmartEmailService::class);
$service->send(new YourMailable(), 'user@email.com');
```

**Check stats:**
```php
$service->getUsageStats();
```

---

**Full guide:** See `docs/EMAIL_SETUP_GUIDE.md`
