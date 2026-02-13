# CMS Email System Testing Guide

**Last Updated:** February 11, 2026  
**Status:** Ready for Testing

---

## Quick Start

### 1. Run Migration

```bash
php artisan migrate
```

This creates:
- Email configuration fields in `cms_companies` table
- `cms_email_logs` table
- `cms_email_templates` table
- `cms_email_unsubscribes` table

---

## Testing Scenarios

### Scenario 1: Platform Email (Default)

**Setup:**
1. Register a new company or use existing
2. No email configuration needed - works immediately
3. Emails sent from: `noreply@mygrownet.com`

**Test Invoice Email:**
1. Go to Invoices → Create Invoice
2. Select a customer with valid email address
3. Fill in invoice details and save
4. Click "Send" button
5. ✅ Email should be sent automatically with PDF attachment
6. Check `cms_email_logs` table for entry

**Test Payment Email:**
1. Go to Payments → Record Payment
2. Select customer and enter payment details
3. Save payment
4. ✅ Email should be sent automatically with receipt PDF
5. Check `cms_email_logs` table for entry

**Test Payment Reminders:**
1. Create an invoice with due date 3 days from now
2. Run command: `php artisan cms:send-payment-reminders`
3. ✅ Should send "upcoming" reminder email
4. Check email logs for reminder entry

**Test Overdue Notices:**
1. Create an invoice with due date 3 days ago
2. Run command: `php artisan cms:send-payment-reminders`
3. ✅ Should send overdue notice email
4. Check email logs for overdue entry

---

### Scenario 2: Custom SMTP (Gmail Example)

**Setup Gmail App Password:**
1. Go to Google Account → Security
2. Enable 2-Step Verification
3. Generate App Password for "Mail"
4. Copy the 16-character password

**Configure in CMS:**
1. Go to Settings → Email Configuration
2. Select "Custom SMTP Server"
3. Fill in:
   - From Email: `your-email@gmail.com`
   - From Name: `Your Company Name`
   - Reply-To: `support@yourcompany.com` (optional)
   - SMTP Host: `smtp.gmail.com`
   - SMTP Port: `587`
   - Encryption: `TLS`
   - Username: `your-email@gmail.com`
   - Password: `[16-char app password]`
4. Click "Test Connection"
5. ✅ Should show "Connection successful"
6. Click "Save Configuration"

**Test Emails:**
1. Send an invoice (same as Scenario 1)
2. Record a payment (same as Scenario 1)
3. ✅ Emails should come from your Gmail address
4. Check recipient inbox for professional branded emails

---

### Scenario 3: Email Statistics

**View Statistics:**
1. Go to Settings → Email Configuration
2. View statistics dashboard:
   - Total Sent
   - Delivered
   - Failed
   - Queued
   - Success Rate

**Check Email Logs:**
1. Go to Settings → Email Logs
2. View complete email history
3. Filter by type (invoice, payment, reminder, overdue)
4. Filter by status (sent, failed, queued)
5. Search by recipient email or subject

**SQL Query:**
```sql
SELECT * FROM cms_email_logs 
WHERE company_id = [your_company_id] 
ORDER BY created_at DESC;
```

---

### Scenario 4: Email Template Customization

**Customize Templates:**
1. Go to Settings → Email Templates
2. Select a template (e.g., Invoice Sent)
3. Edit subject line and HTML body
4. Use available variables ({{company_name}}, {{customer_name}}, etc.)
5. Preview changes
6. Save template
7. Send test invoice to verify customization

**Reset to Default:**
1. Select template
2. Click "Reset to Default"
3. Confirm reset
4. Template reverts to original

---

### Scenario 5: Unsubscribe Management

**Test Unsubscribe:**
1. Open any reminder email
2. Click "Unsubscribe from payment reminders" link
3. ✅ Should show unsubscribe confirmation page
4. Check `cms_email_unsubscribes` table
5. Try sending reminder to that email
6. ✅ Should be blocked and logged

---

### Scenario 6: Scheduled Reminders

**Manual Test:**
```bash
php artisan cms:send-payment-reminders
```

**Check Output:**
- Shows count of reminders sent
- Shows count of overdue notices sent
- Lists each email sent with status

**Automated Schedule:**
- Runs daily at 8:00 AM
- Sends reminders for invoices due in 3 days
- Sends reminders for invoices due today
- Sends overdue notices for invoices 3, 7, 14, 30 days overdue

---

## Testing Checklist

### Basic Functionality
- [ ] Platform email works without configuration
- [ ] Custom SMTP configuration saves correctly
- [ ] SMTP connection test works
- [ ] Invoice emails send with PDF attachment
- [ ] Payment emails send with receipt PDF
- [ ] Email logs are created in database
- [ ] Email statistics display correctly
- [ ] Payment reminders send automatically
- [ ] Overdue notices send automatically
- [ ] Email logs page displays correctly
- [ ] Email template customization works
- [ ] Unsubscribe functionality works

### Email Content
- [ ] Invoice email has correct company name
- [ ] Invoice email has correct customer name
- [ ] Invoice email has correct amounts
- [ ] Invoice PDF is attached
- [ ] Payment email has correct receipt number
- [ ] Payment email has correct payment details
- [ ] Receipt PDF is attached
- [ ] Reminder emails have correct due dates
- [ ] Overdue emails show days overdue
- [ ] Company branding appears in emails
- [ ] Unsubscribe links work in emails

### Error Handling
- [ ] Invalid SMTP credentials show error
- [ ] Missing customer email doesn't crash
- [ ] Failed emails are logged with error message
- [ ] Temp PDF files are cleaned up after sending
- [ ] Unsubscribed emails are not sent
- [ ] Scheduled command handles errors gracefully

### Security
- [ ] SMTP passwords are encrypted in database
- [ ] Unsubscribed emails are not sent
- [ ] Email logs don't expose sensitive data
- [ ] Unsubscribe links are secure
- [ ] Template editing is restricted to company users

### Scheduled Tasks
- [ ] Payment reminders run daily at 8 AM
- [ ] Reminders sent 3 days before due date
- [ ] Reminders sent on due date
- [ ] Overdue notices sent at 3, 7, 14, 30 days
- [ ] Scheduled command logs activity
- [ ] Failed reminders are logged

---

## Common Issues & Solutions

### Issue: Emails not sending

**Check:**
1. Is customer email address valid?
2. Is SMTP configuration correct?
3. Check `cms_email_logs` for error messages
4. Check Laravel logs: `storage/logs/laravel.log`

**Solution:**
- Verify SMTP credentials
- Test connection before saving
- Check firewall/network settings

---

### Issue: Gmail "Less secure app" error

**Solution:**
Use App Password instead of regular password:
1. Enable 2-Step Verification
2. Generate App Password
3. Use 16-character app password in CMS

---

### Issue: PDF not attaching

**Check:**
1. Is temp directory writable? `storage/app/temp`
2. Check file permissions
3. Check Laravel logs for PDF generation errors

**Solution:**
```bash
mkdir -p storage/app/temp
chmod 755 storage/app/temp
```

---

### Issue: Emails going to spam

**Solutions:**
- Use custom SMTP with your domain
- Add SPF record to DNS
- Add DKIM signature
- Use professional email service (not Gmail)

---

## Manual Testing Commands

### Check Email Configuration
```php
php artisan tinker

$company = App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel::find(1);
echo $company->email_provider; // Should be 'platform' or 'custom'
echo $company->smtp_host;
```

### Check Email Logs
```php
php artisan tinker

$logs = App\Infrastructure\Persistence\Eloquent\CMS\EmailLogModel::latest()->take(10)->get();
foreach ($logs as $log) {
    echo "{$log->email_type} to {$log->recipient_email}: {$log->status}\n";
}
```

### Test Email Service
```php
php artisan tinker

$service = app(App\Services\CMS\EmailService::class);
$company = App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel::find(1);

$result = $service->sendEmail(
    company: $company,
    to: 'test@example.com',
    subject: 'Test Email',
    view: 'emails.cms.invoice-sent',
    data: ['invoice' => null, 'company' => $company, 'customer' => null],
    emailType: 'other'
);

echo $result ? 'Success' : 'Failed';
```

---

## Production Checklist

Before deploying to production:

- [ ] Configure platform email service (SendGrid/Mailgun)
- [ ] Set up proper FROM address
- [ ] Configure SPF/DKIM records
- [ ] Test email delivery to multiple providers (Gmail, Outlook, Yahoo)
- [ ] Set up email monitoring/alerts
- [ ] Configure email rate limits
- [ ] Test unsubscribe functionality
- [ ] Review email templates for branding
- [ ] Set up email backup/retry mechanism
- [ ] Test scheduled reminders in production
- [ ] Verify cron job is running (daily at 8 AM)
- [ ] Set up monitoring for failed emails
- [ ] Test email logs page performance
- [ ] Verify template customization permissions

---

## Next Steps

All core features are now complete! Optional future enhancements:

1. **Email Delivery Tracking**: Track opens and clicks (requires webhook integration with email provider)
2. **A/B Testing**: Test different email templates to improve engagement
3. **Email Scheduling**: Allow scheduling emails for specific times
4. **Bulk Email Campaigns**: Send marketing emails to multiple customers
5. **Advanced Analytics**: Detailed email performance metrics and reports

---

## Support

For issues or questions:
- Check Laravel logs: `storage/logs/laravel.log`
- Check email logs table: `cms_email_logs`
- Review documentation: `docs/cms/EMAIL_SYSTEM_DESIGN.md`
