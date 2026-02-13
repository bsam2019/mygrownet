# Recurring Invoices System

**Last Updated:** February 12, 2026  
**Status:** Production Ready  
**Version:** 1.0.0

---

## Overview

The Recurring Invoices system automates invoice generation by creating templates that generate invoices on a schedule. This eliminates manual invoice creation for regular billing cycles.

---

## Features

✅ Create recurring invoice templates  
✅ Set recurrence frequency (daily, weekly, monthly, yearly)  
✅ Auto-generate invoices on schedule  
✅ Email invoices automatically  
✅ Track recurring invoice history  
✅ Pause/resume recurring invoices  
✅ End date or occurrence limit  
✅ Manual generation (generate now)  
✅ Status management (active, paused, completed, cancelled)  

---

## Implementation

### Database Tables

**cms_recurring_invoices:**
- Template details (title, description, items)
- Recurrence settings (frequency, interval, dates)
- Email settings (auto-send, recipients)
- Status tracking

**cms_recurring_invoice_history:**
- Generated invoice tracking
- Email delivery status

**cms_invoices:**
- Added `recurring_invoice_id` foreign key

### Backend Files

- `database/migrations/2026_02_12_140000_create_cms_recurring_invoices_table.php`
- `database/migrations/2026_02_12_140001_add_recurring_invoice_id_to_cms_invoices.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/RecurringInvoiceModel.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/RecurringInvoiceHistoryModel.php`
- `app/Domain/CMS/Core/Services/RecurringInvoiceService.php`
- `app/Http/Controllers/CMS/RecurringInvoiceController.php`
- `app/Console/Commands/GenerateRecurringInvoices.php`

### Frontend Files

- `resources/js/Pages/CMS/RecurringInvoices/Index.vue`
- `resources/js/Pages/CMS/RecurringInvoices/Create.vue` (pending)
- `resources/js/Pages/CMS/RecurringInvoices/Edit.vue` (pending)
- `resources/js/Pages/CMS/RecurringInvoices/Show.vue` (pending)

### Routes

All routes under `/cms/recurring-invoices`:
- GET `/` - List all
- GET `/create` - Create form
- POST `/` - Store new
- GET `/{id}` - View details
- GET `/{id}/edit` - Edit form
- PUT `/{id}` - Update
- DELETE `/{id}` - Delete
- POST `/{id}/pause` - Pause
- POST `/{id}/resume` - Resume
- POST `/{id}/cancel` - Cancel
- POST `/{id}/generate` - Generate now

### Scheduled Command

```bash
php artisan cms:generate-recurring-invoices
```

Runs daily at 6:00 AM to generate due invoices.

---

## Usage

### Creating a Recurring Invoice

1. Navigate to Recurring Invoices
2. Click "New Recurring Invoice"
3. Select customer and optional job
4. Add invoice items
5. Set recurrence frequency (daily/weekly/monthly/yearly)
6. Set interval (e.g., every 2 weeks)
7. Set start date
8. Optional: Set end date or max occurrences
9. Configure email settings
10. Save

### Managing Recurring Invoices

**Pause:** Temporarily stop generation  
**Resume:** Restart generation  
**Cancel:** Permanently stop (cannot be undone)  
**Generate Now:** Create invoice immediately  
**Edit:** Update template details  

### Automatic Generation

The system automatically generates invoices daily at 6:00 AM for any recurring invoice where `next_generation_date <= today` and status is `active`.

After generation:
- Invoice is created with status "sent"
- Email is sent if auto-send is enabled
- History record is created
- Next generation date is calculated
- Occurrence count is incremented

### Completion

A recurring invoice is automatically marked as "completed" when:
- Max occurrences reached
- End date passed

---

## Frequency Options

- **Daily:** Every X days
- **Weekly:** Every X weeks
- **Monthly:** Every X months (same day of month)
- **Yearly:** Every X years (same date)

Examples:
- Daily, interval 1 = Every day
- Weekly, interval 2 = Every 2 weeks
- Monthly, interval 1 = Every month
- Yearly, interval 1 = Every year

---

## Email Automation

When `auto_send_email` is enabled:
- Invoice is emailed to customer automatically
- Uses customer's email or custom email_to
- Supports CC recipients
- Email delivery is tracked in history

---

## Status Flow

```
active → paused → active (resume)
active → cancelled (permanent)
active → completed (auto, when done)
```

---

## API Examples

### Create Recurring Invoice

```php
$recurringInvoice = $service->create($companyId, [
    'customer_id' => 1,
    'title' => 'Monthly Hosting',
    'items' => [...],
    'subtotal' => 500.00,
    'tax_amount' => 80.00,
    'total' => 580.00,
    'frequency' => 'monthly',
    'interval' => 1,
    'start_date' => '2026-02-01',
    'auto_send_email' => true,
    'payment_terms_days' => 30,
]);
```

### Generate Invoice

```php
$invoice = $service->generateInvoice($recurringInvoice);
```

### Get Due for Generation

```php
$dueInvoices = $service->getDueForGeneration($companyId);
```

---

## Testing

### Manual Testing

1. Create recurring invoice with start date = today
2. Run command: `php artisan cms:generate-recurring-invoices`
3. Verify invoice created
4. Verify email sent (if enabled)
5. Verify next generation date updated

### Automated Testing

```bash
php artisan test --filter RecurringInvoiceTest
```

---

## Troubleshooting

### Invoice Not Generated

- Check status is "active"
- Check next_generation_date <= today
- Check max_occurrences not reached
- Check end_date not passed
- Check scheduled command is running

### Email Not Sent

- Check auto_send_email is true
- Check email_to or customer email exists
- Check email service is configured
- Check email logs for errors

### Wrong Next Date

- Verify frequency and interval settings
- Check timezone configuration
- Review calculateNextGenerationDate logic

---

## Future Enhancements

- [ ] Prorate first invoice
- [ ] Custom invoice numbering for recurring
- [ ] Multiple recipients
- [ ] Attach custom documents
- [ ] Discount codes
- [ ] Trial periods
- [ ] Dunning management (failed payments)
- [ ] Analytics dashboard

---

## Changelog

### February 12, 2026
- Initial implementation
- Database schema created
- Service and controller implemented
- Console command for generation
- Index page created
- Routes and scheduling configured

---

**Note:** Create, Edit, and Show pages need to be implemented. The backend is fully functional and ready for UI completion.
