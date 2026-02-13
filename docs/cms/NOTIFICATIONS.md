# CMS Notifications System

**Last Updated:** February 10, 2026  
**Status:** Production Ready

## Overview

The CMS includes a real-time notification system that leverages Laravel's built-in notification infrastructure and Reverb for real-time broadcasting. Notifications are sent for key business events to keep users informed about important activities.

## Architecture

### Technology Stack
- **Laravel Notifications**: Built-in notification system with multiple channels
- **Reverb**: Real-time WebSocket broadcasting for instant notifications
- **Database**: Persistent notification storage
- **Email**: Email notifications via configured mail driver
- **Queues**: Asynchronous notification processing

### Notification Channels
Each notification supports three channels:
1. **Database** - Stored in `notifications` table for in-app display
2. **Broadcast** - Real-time push via Reverb WebSocket
3. **Mail** - Email notifications to user's email address

## Implemented Notifications

### 1. Invoice Sent Notification
**Trigger:** When an invoice is marked as "sent"  
**Recipients:** Customer (if linked to user account)  
**File:** `app/Notifications/CMS/InvoiceSentNotification.php`

**Data Included:**
- Invoice number
- Customer name
- Total amount
- Due date
- Link to view invoice

**Email Subject:** "Invoice {invoice_number} Sent"

---

### 2. Payment Received Notification
**Trigger:** When a payment is recorded  
**Recipients:** Customer (if linked to user account)  
**File:** `app/Notifications/CMS/PaymentReceivedNotification.php`

**Data Included:**
- Payment reference number
- Customer name
- Amount paid
- Payment method
- Link to view receipt

**Email Subject:** "Payment Received - {reference_number}"

---

### 3. Expense Approval Required Notification
**Trigger:** When a new expense is submitted  
**Recipients:** All managers and owners in the company  
**File:** `app/Notifications/CMS/ExpenseApprovalRequiredNotification.php`

**Data Included:**
- Expense number
- Description
- Amount
- Category
- Submitted by (user name)
- Link to review expense

**Email Subject:** "Expense Approval Required - {expense_number}"

---

### 4. Low Stock Alert Notification
**Trigger:** When inventory item stock falls to or below minimum stock level  
**Recipients:** All managers and owners in the company  
**File:** `app/Notifications/CMS/LowStockAlertNotification.php`

**Data Included:**
- Item name
- Item code
- Current stock level
- Minimum stock level
- Unit of measurement
- Link to view inventory item

**Email Subject:** "Low Stock Alert - {item_name}"

**Note:** Only one alert is sent per item until stock is replenished above minimum level.

---

### 5. Job Status Changed Notification
**Trigger:** When a job's status changes (pending → in_progress → completed, etc.)  
**Recipients:** Customer (if linked to user account)  
**File:** `app/Notifications/CMS/JobStatusChangedNotification.php`

**Data Included:**
- Job number
- Job type
- Customer name
- Old status
- New status
- Link to view job details

**Email Subject:** "Job Status Updated - {job_number}"

**Note:** No notification is sent for initial job creation (status change from null to pending).

## Integration Points

### Controllers
Notifications are triggered from the following controllers:

1. **InvoiceController** (`app/Http/Controllers/CMS/InvoiceController.php`)
   - `send()` method → InvoiceSentNotification

2. **PaymentController** (`app/Http/Controllers/CMS/PaymentController.php`)
   - `store()` method → PaymentReceivedNotification

3. **ExpenseController** (`app/Http/Controllers/CMS/ExpenseController.php`)
   - `store()` method → ExpenseApprovalRequiredNotification

### Services
Notifications are triggered from the following services:

1. **InventoryService** (`app/Domain/CMS/Core/Services/InventoryService.php`)
   - `createLowStockAlert()` method → LowStockAlertNotification

2. **JobService** (`app/Domain/CMS/Core/Services/JobService.php`)
   - `recordStatusChange()` method → JobStatusChangedNotification

## Notification Recipients

### Customer Notifications
Customers receive notifications if:
- They have a linked user account (`customer.user` relationship exists)
- The customer record has `user_id` set

**Notifications:**
- Invoice Sent
- Payment Received
- Job Status Changed

### Manager/Owner Notifications
Managers and owners receive notifications for:
- Expense approvals
- Low stock alerts

**Recipient Query:**
```php
CmsUserModel::where('company_id', $companyId)
    ->whereHas('roles', function ($q) {
        $q->whereIn('name', ['owner', 'manager']);
    })
    ->with('user')
    ->get();
```

## Real-Time Broadcasting

### Reverb Configuration
Notifications are broadcast in real-time using Laravel Reverb (WebSocket server).

**Channel:** Private user channel  
**Format:** `App.Models.User.{userId}`

### Frontend Integration
The existing notification system in the platform automatically receives and displays CMS notifications:

1. **Bell Icon** - Shows unread notification count
2. **Notification Dropdown** - Lists recent notifications
3. **Real-time Updates** - New notifications appear instantly via Reverb

## Database Storage

### Notifications Table
All notifications are stored in the `notifications` table:

```sql
- id (uuid)
- type (notification class)
- notifiable_type (User model)
- notifiable_id (user ID)
- data (JSON with notification details)
- read_at (timestamp, nullable)
- created_at
- updated_at
```

### Querying Notifications
```php
// Get unread notifications
$user->unreadNotifications;

// Get all notifications
$user->notifications;

// Mark as read
$notification->markAsRead();

// Mark all as read
$user->unreadNotifications->markAsRead();
```

## Email Notifications

### Email Configuration
Emails are sent using the configured mail driver in `config/mail.php`.

**Default:** SMTP (configurable per environment)

### Email Templates
All notification emails use Laravel's MailMessage builder with:
- Professional greeting
- Clear subject line
- Bullet-point information
- Call-to-action button
- Closing message

### Customization
To customize email appearance, publish Laravel's notification views:
```bash
php artisan vendor:publish --tag=laravel-notifications
```

## Queue Processing

### Asynchronous Notifications
All notifications implement `ShouldQueue` interface for asynchronous processing.

**Benefits:**
- Non-blocking user experience
- Better performance
- Automatic retry on failure

### Queue Configuration
Ensure queue worker is running:
```bash
php artisan queue:work
```

For production, use Supervisor or similar process manager.

## Testing Notifications

### Manual Testing

1. **Invoice Sent:**
   - Create an invoice
   - Mark it as "sent"
   - Check customer's notifications

2. **Payment Received:**
   - Record a payment
   - Check customer's notifications

3. **Expense Approval:**
   - Submit a new expense
   - Check manager/owner notifications

4. **Low Stock Alert:**
   - Reduce inventory stock below minimum
   - Check manager/owner notifications

5. **Job Status Changed:**
   - Change a job's status
   - Check customer's notifications

### Notification Testing in Tinker
```php
php artisan tinker

// Get a user
$user = User::find(1);

// Send test notification
$user->notify(new \App\Notifications\CMS\InvoiceSentNotification([
    'id' => 1,
    'invoice_number' => 'INV-2026-00001',
    'customer_name' => 'Test Customer',
    'total_amount' => 1000,
    'due_date' => '2026-03-01',
]));

// Check notifications
$user->notifications;
```

## Troubleshooting

### Notifications Not Appearing

1. **Check Queue Worker:**
   ```bash
   php artisan queue:work
   ```

2. **Check Reverb Server:**
   ```bash
   php artisan reverb:start
   ```

3. **Check Database:**
   ```sql
   SELECT * FROM notifications WHERE notifiable_id = {user_id};
   ```

4. **Check User Relationship:**
   - Ensure customer has `user_id` set
   - Ensure CMS user has `user` relationship

### Emails Not Sending

1. **Check Mail Configuration:**
   ```bash
   php artisan config:clear
   ```

2. **Test Mail Setup:**
   ```bash
   php artisan tinker
   Mail::raw('Test', function($msg) {
       $msg->to('test@example.com')->subject('Test');
   });
   ```

3. **Check Queue Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Real-Time Not Working

1. **Check Reverb Connection:**
   - Open browser console
   - Look for WebSocket connection errors

2. **Check Broadcasting Configuration:**
   ```php
   // config/broadcasting.php
   'default' => env('BROADCAST_DRIVER', 'reverb'),
   ```

3. **Check Frontend Echo Setup:**
   - Ensure Echo is initialized
   - Check private channel authentication

## Future Enhancements

### Potential Additions

1. **SMS Notifications** - Add SMS channel for critical alerts
2. **Slack Integration** - Send notifications to Slack channels
3. **Notification Preferences** - Allow users to customize notification settings
4. **Digest Emails** - Daily/weekly summary emails
5. **Push Notifications** - Browser push notifications for PWA
6. **Notification Templates** - Customizable email templates per company
7. **Notification History** - Archive and search old notifications
8. **Notification Rules** - Custom rules for when to send notifications

### Notification Preferences (Future)
```php
// Example structure
$user->notificationPreferences()->update([
    'email_invoices' => true,
    'email_payments' => true,
    'email_expenses' => false,
    'sms_low_stock' => true,
    'push_job_updates' => true,
]);
```

## Best Practices

1. **Keep Notifications Relevant** - Only send notifications for important events
2. **Provide Context** - Include enough information to be useful
3. **Include Actions** - Always provide a link to view more details
4. **Respect User Preferences** - Allow users to control notification settings
5. **Test Thoroughly** - Ensure notifications work across all channels
6. **Monitor Queue** - Keep queue workers running in production
7. **Handle Failures** - Implement retry logic for failed notifications

## Related Documentation

- [CMS Implementation Progress](./IMPLEMENTATION_PROGRESS.md)
- [CMS Complete Feature Specification](./COMPLETE_FEATURE_SPECIFICATION.md)
- [Laravel Notifications Documentation](https://laravel.com/docs/notifications)
- [Laravel Reverb Documentation](https://laravel.com/docs/reverb)

## Changelog

### February 10, 2026
- Initial implementation of 5 core CMS notifications
- Integration with existing Reverb real-time system
- Database, broadcast, and mail channels configured
- Documentation created
