# CMS SMS Notification System

**Last Updated:** February 12, 2026  
**Status:** Production Ready - Disabled by Default

## Overview

Optional SMS notification system for sending invoice notifications, payment confirmations, and reminders to customers. The system is **disabled by default** and requires a subscription to Africa's Talking or Twilio to activate.

## Key Features

- **Disabled by Default**: No SMS subscription required to use the CMS
- **Multiple Providers**: Support for Africa's Talking and Twilio
- **Automatic Fallback**: If SMS is disabled, system continues working normally
- **Cost Tracking**: Track SMS costs and delivery statistics
- **Delivery Logs**: Complete SMS history with status tracking
- **Test Mode**: Test configuration before going live

## Supported Providers

### 1. Africa's Talking (Recommended for Zambia)
- **Website**: https://africastalking.com
- **Coverage**: Zambia, Kenya, Uganda, Tanzania, Rwanda, etc.
- **Pricing**: Pay-as-you-go, ~K0.10 per SMS
- **Setup**: Easy API integration

### 2. Twilio
- **Website**: https://twilio.com
- **Coverage**: Global
- **Pricing**: Pay-as-you-go, varies by country
- **Setup**: Requires phone number purchase

## Implementation

### Backend

#### SmsService (`app/Services/CMS/SmsService.php`)

Main service for sending SMS:

```php
// Check if SMS is enabled
$isEnabled = $smsService->isEnabled($companyId);

// Send SMS (only if enabled)
$result = $smsService->send(
    $companyId,
    '+260XXXXXXXXX',
    'Your invoice #INV-001 is ready',
    'invoice'
);

// Get statistics
$stats = $smsService->getStatistics($companyId);

// Test connection
$result = $smsService->testConnection($companyId, '+260XXXXXXXXX');
```

**Key Methods:**
- `isEnabled()` - Check if SMS is configured and enabled
- `send()` - Send SMS (returns success/error, logs automatically)
- `getStatistics()` - Get SMS usage stats
- `getLogs()` - Get SMS history
- `testConnection()` - Send test SMS

#### Database Schema

**cms_sms_logs table:**
```sql
CREATE TABLE cms_sms_logs (
    id BIGINT PRIMARY KEY,
    company_id BIGINT,
    to VARCHAR(20),
    message TEXT,
    type VARCHAR(50), -- invoice, payment, reminder, job, general
    status ENUM('sent', 'failed', 'pending'),
    message_id VARCHAR(255), -- Provider message ID
    cost DECIMAL(10,4), -- SMS cost
    error TEXT,
    sent_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Company SMS Settings (JSON in cms_companies.sms_settings):**
```json
{
    "enabled": false,
    "provider": "africas_talking",
    "api_key": "your-api-key",
    "username": "sandbox",
    "sender_id": "MyCompany",
    "account_sid": null,
    "auth_token": null,
    "from_number": null
}
```

### Frontend

#### SMS Settings Page (`resources/js/Pages/CMS/Settings/Sms.vue`)

Features:
- Enable/disable SMS
- Provider selection (Africa's Talking or Twilio)
- API credentials configuration
- Test SMS sending
- Usage statistics display
- Link to SMS logs

### Routes

```php
// SMS Settings
GET  /cms/settings/sms              - Show SMS settings page
POST /cms/settings/sms              - Update SMS settings
POST /cms/settings/sms/test-connection - Send test SMS
GET  /cms/settings/sms/logs         - View SMS logs
```

## Usage

### For Companies Without SMS Subscription

**Default Behavior:**
- SMS is disabled by default
- All SMS-related features are hidden
- System works normally without SMS
- No errors or warnings about missing SMS

**To Enable Later:**
1. Subscribe to Africa's Talking or Twilio
2. Go to Settings → SMS
3. Enable SMS and enter API credentials
4. Test connection
5. Save settings

### For Companies With SMS Subscription

#### Africa's Talking Setup

1. **Sign up**: https://africastalking.com
2. **Get credentials**:
   - API Key: From dashboard
   - Username: Your username (or "sandbox" for testing)
   - Sender ID: Optional, max 11 characters

3. **Configure in CMS**:
   ```
   Settings → SMS
   - Enable SMS: ON
   - Provider: Africa's Talking
   - API Key: [your-api-key]
   - Username: [your-username]
   - Sender ID: [optional]
   ```

4. **Test**: Send test SMS to verify configuration

#### Twilio Setup

1. **Sign up**: https://twilio.com
2. **Get credentials**:
   - Account SID: From console
   - Auth Token: From console
   - From Number: Purchase a phone number

3. **Configure in CMS**:
   ```
   Settings → SMS
   - Enable SMS: ON
   - Provider: Twilio
   - Account SID: [your-sid]
   - Auth Token: [your-token]
   - From Number: [your-twilio-number]
   ```

4. **Test**: Send test SMS to verify configuration

### Sending SMS from Code

```php
use App\Services\CMS\SmsService;

// Inject service
public function __construct(private SmsService $smsService) {}

// Send invoice notification
$result = $this->smsService->send(
    $companyId,
    $customer->phone,
    "Invoice #{$invoice->invoice_number} sent. Amount: K{$invoice->total}. View: {$invoiceUrl}",
    'invoice'
);

// Check result
if ($result['success']) {
    // SMS sent successfully
} elseif ($result['skipped'] ?? false) {
    // SMS disabled, continue normally
} else {
    // SMS failed, log error
}
```

### SMS Types

- `invoice` - Invoice sent notifications
- `payment` - Payment received confirmations
- `reminder` - Payment reminders
- `overdue` - Overdue notices
- `job` - Job assignment notifications
- `general` - General notifications

## Cost Management

### Tracking Costs

```php
// Get SMS statistics
$stats = $smsService->getStatistics($companyId, $startDate, $endDate);

// Returns:
[
    'total' => 150,
    'sent' => 145,
    'failed' => 5,
    'success_rate' => 96.67,
    'total_cost' => 15.00
]
```

### Cost Optimization Tips

1. **Use SMS sparingly**: Only for critical notifications
2. **Keep messages short**: Longer messages cost more
3. **Batch notifications**: Send during off-peak hours
4. **Monitor usage**: Check statistics regularly
5. **Set budgets**: Track monthly SMS costs

## Best Practices

### Message Templates

**Invoice Sent:**
```
Invoice #INV-001 sent. Amount: K500. Due: 15 Feb. View: [link]
```

**Payment Received:**
```
Payment received! K500 for Invoice #INV-001. Thank you!
```

**Payment Reminder:**
```
Reminder: Invoice #INV-001 (K500) due tomorrow. Pay: [link]
```

**Overdue Notice:**
```
Invoice #INV-001 (K500) is overdue. Please pay ASAP. Contact: [phone]
```

### Phone Number Format

- **Zambia**: +260XXXXXXXXX (e.g., +260977123456)
- **International**: Include country code
- **Validation**: Validate before sending

### Error Handling

```php
$result = $smsService->send($companyId, $phone, $message, $type);

if (!$result['success']) {
    if ($result['skipped'] ?? false) {
        // SMS disabled, no action needed
        Log::info('SMS skipped (disabled)', ['company_id' => $companyId]);
    } else {
        // SMS failed, log error
        Log::error('SMS failed', [
            'company_id' => $companyId,
            'error' => $result['error'],
        ]);
    }
}
```

## Security

### API Key Storage

- API keys stored encrypted in database
- Never expose in frontend
- Use environment variables for sensitive data

### Rate Limiting

- Implement rate limiting to prevent abuse
- Monitor unusual SMS activity
- Set daily/monthly limits

## Troubleshooting

### SMS Not Sending

1. **Check if enabled**: Settings → SMS → Enabled?
2. **Verify credentials**: Test connection
3. **Check phone format**: Must include country code
4. **Check balance**: Sufficient credits in provider account?
5. **Check logs**: View SMS logs for error details

### Test SMS Not Received

1. **Wait 1-2 minutes**: SMS can be delayed
2. **Check phone number**: Correct format?
3. **Check spam**: SMS might be filtered
4. **Try different number**: Test with another phone
5. **Check provider status**: Provider service operational?

### High Costs

1. **Review usage**: Check SMS logs
2. **Optimize messages**: Keep them short
3. **Reduce frequency**: Only critical notifications
4. **Set limits**: Implement daily caps

## Future Enhancements

- [ ] SMS templates management UI
- [ ] Scheduled SMS sending
- [ ] SMS campaigns
- [ ] Two-way SMS (replies)
- [ ] SMS delivery reports
- [ ] Bulk SMS sending
- [ ] SMS analytics dashboard
- [ ] Cost alerts and budgets

## Changelog

### February 12, 2026
- Initial implementation
- Africa's Talking integration
- Twilio integration
- SMS logging and statistics
- Settings UI
- Test mode
- Complete documentation
