# CMS Settings & Configuration System

**Last Updated:** February 11, 2026  
**Status:** ✅ Complete (100%) - Production Ready

## Overview

The CMS Settings & Configuration module provides a comprehensive interface for managing company-wide settings including business hours, tax configuration, approval thresholds, invoice settings, notification preferences, payment instructions, branding customization, and SMS gateway configuration. All settings are stored in a JSON field in the company record, allowing flexible configuration without database schema changes.

**New in v1.1:** Payment instructions, branding customization, and SMS gateway configuration added.

## Features

### 1. Business Hours Configuration
Set operating hours for each day of the week with:
- Enable/disable specific days
- Custom open and close times per day
- Visual day-by-day configuration
- Used for business logic (e.g., scheduling, availability checks)

### 2. Tax Settings
Configure tax/VAT settings for invoices:
- Enable/disable tax on invoices
- Set default tax rate (percentage)
- Configure tax number/TPIN
- Customize tax label (VAT, GST, Sales Tax, etc.)
- Choose between tax-inclusive or tax-exclusive pricing

### 3. Approval Thresholds
Set automatic approval limits for:
- **Expenses**: Require approval above certain amount
- **Quotations**: Require approval for high-value quotes
- **Payments**: Require approval for large payments
- Auto-approve items below threshold
- Configurable per transaction type

### 4. Invoice Settings
Configure invoice generation and management:
- Invoice number prefix (e.g., "INV")
- Next invoice number (sequence control)
- Default payment terms (days until due)
- Late fee configuration:
  - Enable/disable late fees
  - Late fee percentage
  - Grace period (days after due date)

### 5. Notification Preferences
Control which events trigger email notifications:
- Invoice notifications (sent/created)
- Payment notifications (received)
- Quotation notifications (sent)
- Low stock alerts
- Expense approval notifications
- Job update notifications

### 6. Payment Instructions ✅ NEW
Configure payment collection methods to display on invoices:
- Bank account details (name, number, branch, SWIFT)
- Mobile money details (provider, number)
- Additional payment instructions (custom text)
- Toggle visibility on invoices

**Important:** MyGrowNet does not process payments. Companies add their own payment details for customers to pay directly.

### 7. Branding Customization ✅ NEW
Customize company visual identity:
- Company logo upload (PNG, JPG, SVG - max 2MB)
- Primary brand color (hex color picker)
- Secondary brand color
- Invoice footer text (custom message)
- Toggle logo display on invoices and receipts

**Logo Storage:** `storage/app/public/cms/logos/`

### 8. SMS Gateway Configuration ✅ NEW
Configure SMS notifications (optional, requires paid subscription):
- Enable/disable SMS notifications
- Provider selection (Africa's Talking or Twilio)
- API credentials (key, secret, sender ID)
- Notification types:
  - Invoice notifications
  - Payment confirmations
  - Payment reminders

**Note:** Can be configured but left disabled until SMS subscription is active.
- Expense approval requests
- Job status updates

## Implementation

### Backend Architecture

#### CompanySettingsService
**Location:** `app/Domain/CMS/Core/Services/CompanySettingsService.php`

**Key Methods:**
```php
// Get all settings with defaults
getSettings(int $companyId): array

// Update specific setting sections
updateBusinessHours(int $companyId, array $businessHours): CompanyModel
updateTaxSettings(int $companyId, array $taxSettings): CompanyModel
updateApprovalThresholds(int $companyId, array $thresholds): CompanyModel
updateInvoiceSettings(int $companyId, array $invoiceSettings): CompanyModel
updateNotificationSettings(int $companyId, array $notificationSettings): CompanyModel

// Utility methods
isBusinessOpen(int $companyId, ?\DateTime $dateTime = null): bool
requiresApproval(int $companyId, string $type, float $amount): bool
resetToDefaults(int $companyId): CompanyModel
```

**Default Settings Structure:**
```php
[
    'business_hours' => [
        'monday' => ['open' => '08:00', 'close' => '17:00', 'enabled' => true],
        // ... other days
    ],
    'tax' => [
        'enabled' => true,
        'default_rate' => 16.0,
        'tax_number' => '',
        'tax_label' => 'VAT',
        'inclusive' => false,
    ],
    'approval_thresholds' => [
        'expense_approval_required' => true,
        'expense_auto_approve_limit' => 500,
        'quotation_approval_required' => false,
        'quotation_auto_approve_limit' => 5000,
        'payment_approval_required' => false,
        'payment_auto_approve_limit' => 10000,
    ],
    'invoice' => [
        'prefix' => 'INV',
        'next_number' => 1,
        'due_days' => 30,
        'late_fee_enabled' => false,
        'late_fee_percentage' => 5,
        'late_fee_days' => 7,
    ],
    'notifications' => [
        'email_invoices' => true,
        'email_payments' => true,
        'email_quotations' => true,
        'email_low_stock' => true,
        'email_expense_approval' => true,
        'email_job_updates' => true,
    ],
]
```

#### SettingsController
**Location:** `app/Http/Controllers/CMS/SettingsController.php`

**Routes:**
- `GET /cms/settings` - Display settings page
- `POST /cms/settings/business-hours` - Update business hours
- `POST /cms/settings/tax` - Update tax settings
- `POST /cms/settings/approval-thresholds` - Update approval thresholds
- `POST /cms/settings/invoice` - Update invoice settings
- `POST /cms/settings/notifications` - Update notification preferences
- `POST /cms/settings/reset-defaults` - Reset all settings to defaults

### Frontend Implementation

#### Settings Page
**Location:** `resources/js/Pages/CMS/Settings/Index.vue`

**Features:**
- Tabbed interface for different setting categories
- Real-time form validation
- Individual save buttons per section
- Reset to defaults functionality
- Professional form styling using FormInput and FormSection components

**Tabs:**
1. Business Hours - Day-by-day time configuration
2. Tax Settings - Tax rate and number configuration
3. Approval Thresholds - Auto-approval limits
4. Invoice Settings - Invoice numbering and late fees
5. Notifications - Email notification preferences

### Navigation Integration

Settings are accessible from the CMS sidebar under the "Settings" section:
- **Company Settings** - Main settings page
- **Industry Presets** - Pre-configured industry templates

## Usage

### Accessing Settings

1. Navigate to CMS → Settings → Company Settings
2. Select the tab for the setting category you want to configure
3. Make your changes
4. Click "Save" button for that section

### Business Hours Example

```typescript
// Set Monday-Friday 8am-5pm, Saturday 9am-1pm, Sunday closed
{
  monday: { open: '08:00', close: '17:00', enabled: true },
  tuesday: { open: '08:00', close: '17:00', enabled: true },
  wednesday: { open: '08:00', close: '17:00', enabled: true },
  thursday: { open: '08:00', close: '17:00', enabled: true },
  friday: { open: '08:00', close: '17:00', enabled: true },
  saturday: { open: '09:00', close: '13:00', enabled: true },
  sunday: { open: '00:00', close: '00:00', enabled: false }
}
```

### Tax Settings Example

```php
// 16% VAT, tax-exclusive pricing
{
  enabled: true,
  default_rate: 16.0,
  tax_number: '1234567890',
  tax_label: 'VAT',
  inclusive: false
}
```

### Approval Thresholds Example

```php
// Auto-approve expenses under K500
{
  expense_approval_required: true,
  expense_auto_approve_limit: 500,
  quotation_approval_required: false,
  quotation_auto_approve_limit: 5000,
  payment_approval_required: false,
  payment_auto_approve_limit: 10000
}
```

## Integration with Other Modules

### Invoice Module
- Uses `invoice.prefix` and `invoice.next_number` for invoice numbering
- Uses `invoice.due_days` for default payment terms
- Uses `tax` settings for tax calculations
- Uses `invoice.late_fee_*` settings for late fee calculations

### Expense Module
- Uses `approval_thresholds.expense_*` to determine if approval is required
- Auto-approves expenses below threshold
- Sends notifications based on `notifications.email_expense_approval`

### Quotation Module
- Uses `approval_thresholds.quotation_*` for approval workflow
- Uses `tax` settings for tax calculations

### Payment Module
- Uses `approval_thresholds.payment_*` for approval workflow
- Sends notifications based on `notifications.email_payments`

### Inventory Module
- Sends low stock alerts based on `notifications.email_low_stock`

### Job Module
- Sends job updates based on `notifications.email_job_updates`
- Can use `business_hours` for scheduling

## Utility Methods

### Check if Business is Open

```php
use App\Domain\CMS\Core\Services\CompanySettingsService;

$settingsService = app(CompanySettingsService::class);

// Check if open now
$isOpen = $settingsService->isBusinessOpen($companyId);

// Check if open at specific time
$dateTime = new \DateTime('2026-02-10 14:30:00');
$isOpen = $settingsService->isBusinessOpen($companyId, $dateTime);
```

### Check if Approval Required

```php
use App\Domain\CMS\Core\Services\CompanySettingsService;

$settingsService = app(CompanySettingsService::class);

// Check if expense requires approval
$requiresApproval = $settingsService->requiresApproval(
    companyId: $companyId,
    type: 'expense',
    amount: 750.00
);

// Returns true if amount > auto_approve_limit and approval is required
```

### Get Specific Setting

```php
use App\Domain\CMS\Core\Services\CompanySettingsService;

$settingsService = app(CompanySettingsService::class);
$settings = $settingsService->getSettings($companyId);

// Access specific settings
$taxRate = $settings['tax']['default_rate'];
$invoicePrefix = $settings['invoice']['prefix'];
$emailInvoices = $settings['notifications']['email_invoices'];
```

## Database Storage

Settings are stored in the `cms_companies` table in the `settings` JSON column:

```sql
SELECT settings FROM cms_companies WHERE id = 1;
```

Returns:
```json
{
  "business_hours": {...},
  "tax": {...},
  "approval_thresholds": {...},
  "invoice": {...},
  "notifications": {...}
}
```

## Default Values

When a company is created or settings are accessed for the first time, default values are automatically applied. This ensures all settings have sensible defaults even if not explicitly configured.

**Default Behavior:**
- Business hours: Monday-Friday 8am-5pm
- Tax: 16% VAT enabled, tax-exclusive
- Approval thresholds: Expenses require approval above K500
- Invoice: 30-day payment terms, no late fees
- Notifications: All email notifications enabled

## Reset to Defaults

Users can reset all settings to defaults using the "Reset to Defaults" button. This action:
1. Confirms with the user (cannot be undone)
2. Replaces all settings with default values
3. Preserves company information (name, address, etc.)
4. Redirects back to settings page with success message

## Security & Permissions

- Only users with `owner` or `manager` roles can access settings
- Settings are company-specific (multi-tenant safe)
- All updates are logged in audit trail
- Settings changes take effect immediately

## Testing

### Manual Testing Checklist

1. **Business Hours:**
   - [ ] Set different hours for each day
   - [ ] Disable specific days
   - [ ] Verify isBusinessOpen() method works correctly

2. **Tax Settings:**
   - [ ] Enable/disable tax
   - [ ] Change tax rate
   - [ ] Verify tax appears on invoices
   - [ ] Test tax-inclusive vs tax-exclusive

3. **Approval Thresholds:**
   - [ ] Set expense approval limit
   - [ ] Create expense below limit (auto-approved)
   - [ ] Create expense above limit (requires approval)
   - [ ] Test quotation and payment thresholds

4. **Invoice Settings:**
   - [ ] Change invoice prefix
   - [ ] Verify next invoice uses new prefix
   - [ ] Change payment terms
   - [ ] Enable late fees
   - [ ] Verify late fee calculation

5. **Notifications:**
   - [ ] Disable specific notifications
   - [ ] Verify emails are not sent for disabled types
   - [ ] Re-enable and verify emails resume

6. **Reset to Defaults:**
   - [ ] Click reset button
   - [ ] Confirm dialog appears
   - [ ] Verify all settings reset to defaults

## Troubleshooting

### Settings Not Saving

**Problem:** Changes don't persist after saving

**Solutions:**
1. Check browser console for JavaScript errors
2. Verify CSRF token is valid
3. Check server logs for validation errors
4. Ensure user has proper permissions

### Settings Not Appearing

**Problem:** Settings page shows empty or default values

**Solutions:**
1. Check if company record exists
2. Verify `settings` column is not null
3. Check if user has access to company
4. Clear browser cache

### Approval Logic Not Working

**Problem:** Items not auto-approving or requiring approval incorrectly

**Solutions:**
1. Verify approval threshold settings are saved
2. Check if approval is enabled for that type
3. Verify amount comparison logic
4. Check audit trail for approval events

## Future Enhancements

### Potential Additions

1. **Email Templates** - Customizable email templates for notifications
2. **Currency Settings** - Multi-currency support
3. **Date/Time Format** - Customizable date and time formats
4. **Language Settings** - Multi-language support
5. **Branding** - Custom colors, fonts, and logos
6. **Payment Gateway Settings** - Configure payment processors
7. **SMS Notifications** - SMS notification preferences
8. **Backup Settings** - Automatic backup configuration
9. **API Settings** - API keys and webhook configuration
10. **Custom Fields** - Define custom fields for entities

## Related Documentation

- [CMS Implementation Progress](./IMPLEMENTATION_PROGRESS.md)
- [CMS Complete Feature Specification](./COMPLETE_FEATURE_SPECIFICATION.md)
- [Industry Presets](./INDUSTRY_PRESETS.md)
- [Notifications System](./NOTIFICATIONS.md)

## Changelog

### February 10, 2026
- Initial implementation of settings system
- Business hours configuration
- Tax settings
- Approval thresholds
- Invoice settings
- Notification preferences
- Reset to defaults functionality
- Complete documentation created
