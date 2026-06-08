# Multi-Provider Email Strategy

## Overview

MyGrowNet uses multiple email providers to maximize free tiers and ensure reliable delivery.

## Providers & Allocation

### 1. **Resend** (Primary - Transactional)
- **Free Tier:** 3,000 emails/month
- **Speed:** Instant delivery (seconds)
- **Use For:**
  - ✅ Order confirmations
  - ✅ Payment receipts
  - ✅ Password resets
  - ✅ Account notifications
  - ✅ Transaction alerts
  - ✅ Level advancement notifications

### 2. **Brevo** (Secondary - Marketing)
- **Free Tier:** 300 emails/day (9,000/month)
- **Speed:** 1-5 minutes delivery
- **Use For:**
  - ✅ Newsletters
  - ✅ Marketing campaigns
  - ✅ Promotional emails
  - ✅ Weekly digests
  - ✅ Community announcements

### 3. **Amazon SES** (Backup - Bulk)
- **Free Tier:** 62,000 emails/month (if sent from EC2)
- **Speed:** Variable
- **Use For:**
  - ✅ Bulk reports
  - ✅ Mass notifications
  - ✅ System-wide announcements
  - ✅ Backup when others are exhausted

## Usage Examples

### Method 1: Using EmailService Helper

```php
use App\Services\EmailService;
use App\Mail\WelcomeMail;
use App\Mail\NewsletterMail;

// Transactional email (via Resend - fast)
EmailService::sendTransactional(
    new WelcomeMail($user),
    $user->email
);

// Marketing email (via Brevo)
EmailService::sendMarketing(
    new NewsletterMail($content),
    $user->email
);

// Auto-select provider based on type
EmailService::send(
    new WelcomeMail($user),
    $user->email,
    'transactional' // or 'marketing' or 'bulk'
);
```

### Method 2: Direct Mailer Selection

```php
use Illuminate\Support\Facades\Mail;
use App\Mail\TransactionNotificationMail;

// Send via Resend
Mail::mailer('resend')
    ->to($user->email)
    ->send(new TransactionNotificationMail($transaction));

// Send via Brevo
Mail::mailer('brevo-api')
    ->to($user->email)
    ->send(new NewsletterMail($content));

// Send via default mailer
Mail::to($user->email)
    ->send(new GenericNotificationMail($data));
```

### Method 3: Queue with Specific Mailer

```php
use App\Mail\WelcomeMail;

// Queue email with specific mailer
Mail::mailer('resend')
    ->to($user->email)
    ->queue(new WelcomeMail($user));
```

## Email Type Guidelines

### Transactional (Resend) ⚡
**Characteristics:** Time-sensitive, user-initiated, expected
- Welcome emails
- Password resets
- Order confirmations
- Payment receipts
- Account verifications
- Transaction notifications
- Level advancement alerts

### Marketing (Brevo) 📧
**Characteristics:** Promotional, scheduled, bulk
- Weekly newsletters
- Product announcements
- Special offers
- Event invitations
- Community updates
- Educational content

### Bulk (SES/Default) 📊
**Characteristics:** High volume, less time-sensitive
- Monthly reports
- System maintenance notices
- Bulk announcements
- Data exports
- Analytics summaries

## Monitoring Usage

Track email usage to stay within free tiers:

```php
use App\Services\EmailService;

$stats = EmailService::getUsageStats();

// Returns:
[
    'resend' => [
        'limit' => 3000,
        'used' => 1250,
        'remaining' => 1750,
    ],
    'brevo' => [
        'limit' => 300, // per day
        'used' => 45,
        'remaining' => 255,
    ],
]
```

## Failover Strategy

If a provider reaches its limit:

1. **Resend exhausted** → Switch to Brevo for transactional
2. **Brevo exhausted** → Switch to SES
3. **All exhausted** → Queue emails for next day

## Best Practices

1. **Always use appropriate provider** for email type
2. **Monitor usage** regularly
3. **Queue non-urgent emails** to spread load
4. **Use Resend for time-critical** emails
5. **Batch marketing emails** via Brevo
6. **Test deliverability** of each provider periodically

## Configuration

All providers are configured in `config/mail.php`:

```php
'default' => env('MAIL_MAILER', 'resend'),

'mailers' => [
    'resend' => ['transport' => 'resend'],
    'brevo-api' => ['transport' => 'brevo-api'],
    // ... other mailers
],
```

Environment variables in `.env`:

```env
MAIL_MAILER=resend
RESEND_KEY=re_xxxxx
BREVO_API_KEY=xkeysib-xxxxx
```

## Total Free Capacity

- **Resend:** 3,000/month
- **Brevo:** 9,000/month (300/day)
- **SES:** 62,000/month
- **TOTAL:** ~74,000 emails/month FREE! 🎉
