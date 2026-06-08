# Email Implementation Guide

## Quick Reference: Which Provider to Use?

### ⚡ **RESEND** - "User is waiting for this email"
- Password resets
- Payment confirmations
- Transaction receipts
- Level upgrades
- Critical alerts

### 📧 **BREVO** - "User will read this later"
- Newsletters
- Marketing emails
- Weekly digests
- Feature announcements
- Educational content

### 📊 **SES** - "Bulk/Reports"
- Monthly reports
- System announcements
- Admin notifications
- Bulk communications

---

## Code Examples by Feature

### **1. Authentication (Resend)**

```php
// Password Reset
use App\Services\EmailService;
use App\Mail\PasswordResetMail;

EmailService::sendTransactional(
    new PasswordResetMail($user, $token),
    $user->email
);

// Email Verification
EmailService::sendTransactional(
    new VerifyEmailMail($user, $verificationUrl),
    $user->email
);
```

### **2. Wallet Transactions (Resend)**

```php
// Deposit Confirmation
EmailService::sendTransactional(
    new TransactionNotificationMail(
        title: 'Deposit Successful',
        greeting: "Hello {$user->name}!",
        message: 'Your deposit has been processed successfully.',
        transactionDetails: [
            'Amount' => 'K' . number_format($amount, 2),
            'Date' => now()->format('M d, Y H:i'),
            'Reference' => $transaction->reference,
        ],
        status: 'success'
    ),
    $user->email
);
```

### **3. Level Advancement (Resend)**

```php
// Level Up Notification
use App\Mail\LevelAdvancementMail;

EmailService::sendTransactional(
    new LevelAdvancementMail(
        user: $user,
        newLevel: 'Professional',
        benefits: [
            'Higher commission rates',
            'Access to premium features',
            'Priority support',
        ],
        stats: [
            'Network Size' => $user->network_size,
            'Total Earnings' => 'K' . number_format($user->total_earnings, 2),
        ]
    ),
    $user->email
);
```

### **4. Subscriptions (Resend for payment, Brevo for reminders)**

```php
// Payment Success - Resend
EmailService::sendTransactional(
    new GenericNotificationMail(
        subject: 'Subscription Payment Successful',
        greeting: "Hello {$user->name}!",
        message: 'Your subscription has been renewed successfully.',
        actionText: 'View Subscription',
        actionUrl: route('subscriptions.show')
    ),
    $user->email
);

// Renewal Reminder (3 days before) - Brevo
EmailService::sendMarketing(
    new GenericNotificationMail(
        subject: 'Subscription Renewal Reminder',
        greeting: "Hello {$user->name}!",
        message: 'Your subscription will renew in 3 days.',
        actionText: 'Manage Subscription',
        actionUrl: route('subscriptions.index')
    ),
    $user->email
);
```

### **5. Quick Invoice (Resend)**

```php
// Invoice Generated
EmailService::sendTransactional(
    new GenericNotificationMail(
        subject: 'Invoice Generated',
        greeting: "Hello {$client->name}!",
        message: 'Your invoice has been generated and is ready for download.',
        actionText: 'View Invoice',
        actionUrl: route('quick-invoice.view', $invoice->id)
    ),
    $client->email
);
```

### **6. Newsletters (Brevo)**

```php
// Weekly Newsletter
use App\Mail\NewsletterMail;

foreach ($subscribers as $user) {
    EmailService::sendMarketing(
        new NewsletterMail($content),
        $user->email
    );
}
```

### **7. Monthly Reports (SES)**

```php
// Monthly Financial Report
use App\Mail\MonthlyReportMail;

foreach ($users as $user) {
    EmailService::sendBulk(
        new MonthlyReportMail($user, $reportData),
        $user->email
    );
}
```

---

## Update Existing Code

### **Find and Replace Pattern:**

**OLD (generic):**
```php
Mail::to($user->email)->send(new SomeMail());
```

**NEW (with provider selection):**
```php
// For critical/transactional
EmailService::sendTransactional(new SomeMail(), $user->email);

// For marketing
EmailService::sendMarketing(new SomeMail(), $user->email);

// For bulk
EmailService::sendBulk(new SomeMail(), $user->email);
```

---

## Files to Update

### **Priority 1: Critical Transactional (Switch to Resend)**

1. `app/Http/Controllers/Auth/PasswordResetController.php`
2. `app/Http/Controllers/WalletController.php`
3. `app/Http/Controllers/SubscriptionController.php`
4. `app/Http/Controllers/QuickInvoiceController.php`
5. `app/Services/TierAdvancementService.php`
6. `app/Notifications/*` (review each)

### **Priority 2: Marketing (Switch to Brevo)**

1. `app/Services/EmailCampaignService.php`
2. `app/Http/Controllers/NewsletterController.php`
3. `app/Services/CommunityNotificationService.php`

### **Priority 3: Bulk (Switch to SES)**

1. `app/Console/Commands/SendMonthlyReports.php`
2. `app/Services/ReportingService.php`

---

## Testing Checklist

- [ ] Test password reset email (Resend)
- [ ] Test payment confirmation (Resend)
- [ ] Test level advancement (Resend)
- [ ] Test newsletter (Brevo)
- [ ] Test monthly report (SES)
- [ ] Verify delivery times
- [ ] Check spam folder placement
- [ ] Test branded templates on all providers

---

## Monitoring Setup

Create a simple tracking system:

```php
// In EmailService.php
private static function trackUsage(string $provider): void
{
    DB::table('email_usage')->updateOrInsert(
        [
            'provider' => $provider,
            'date' => now()->toDateString(),
        ],
        [
            'count' => DB::raw('count + 1'),
            'updated_at' => now(),
        ]
    );
}

// Check usage
public static function getUsageToday(string $provider): int
{
    return DB::table('email_usage')
        ->where('provider', $provider)
        ->where('date', now()->toDateString())
        ->value('count') ?? 0;
}
```

---

## Next Steps

1. ✅ Review allocation strategy
2. ⏳ Update critical email sending code
3. ⏳ Deploy to production
4. ⏳ Monitor usage for 1 week
5. ⏳ Adjust allocation if needed
