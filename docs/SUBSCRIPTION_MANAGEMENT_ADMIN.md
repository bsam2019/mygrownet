# Subscription Management System - Admin Interface

## Overview

The Subscription Management system provides administrators with comprehensive tools to manage all member subscriptions, monitor revenue, handle renewals, and perform bulk operations.

## Features

### 1. Subscription Dashboard
- **Real-time Statistics**
  - Total subscriptions count
  - Active subscriptions
  - Expired subscriptions
  - Subscriptions expiring this week
  
- **Revenue Analytics**
  - Monthly revenue tracking
  - Growth rate calculation
  - Revenue by professional level
  - 12-month revenue trend

- **Level Distribution**
  - Visual breakdown of members by professional level
  - Percentage distribution
  - Quick insights into member progression

### 2. Subscription Management

#### Individual Actions
- **View Details**: Complete subscription history and member profile
- **Update Status**: Change subscription status (Active/Suspended/Cancelled)
- **Extend Subscription**: Add days to subscription expiry
- **Force Upgrade**: Manually upgrade member's professional level

#### Bulk Operations
- **Bulk Suspend**: Suspend multiple subscriptions at once
- **Bulk Activate**: Reactivate multiple subscriptions
- **Bulk Extend**: Extend multiple subscriptions by specified days
- **Reason Tracking**: All bulk actions require optional reason for audit trail

### 3. Filtering & Search
- **Search**: By name, email, or phone number
- **Filter by Level**: Associate through Ambassador
- **Filter by Status**: Active, Expired, or Suspended
- **Real-time Updates**: Debounced search for performance

### 4. Subscription Detail View

#### Member Information
- Professional level and status
- Life Points (LP) and Monthly Activity Points (BP)
- Wallet balance
- Total referrals and commissions
- Days remaining until expiry

#### History Tracking
- **Subscription History**: All past subscriptions with packages
- **Payment History**: Complete transaction log
- **Commission History**: Recent referral commissions earned

#### Quick Actions
- Send notifications
- View full profile
- Update status
- Extend subscription
- Force level upgrade

### 5. Export Functionality
- Export subscription data with current filters
- CSV format for external analysis
- Includes all relevant member and subscription information

## Routes

### Admin Routes (Prefix: `/admin/subscriptions`)

```php
GET    /admin/subscriptions              // List all subscriptions
GET    /admin/subscriptions/{user}       // View subscription details
POST   /admin/subscriptions/{user}/update-status    // Update status
POST   /admin/subscriptions/{user}/extend           // Extend subscription
POST   /admin/subscriptions/{user}/force-upgrade    // Force level upgrade
POST   /admin/subscriptions/bulk-action             // Bulk operations
GET    /admin/subscriptions/export                  // Export data
```

## Controller Methods

### SubscriptionController

#### `index(Request $request)`
Lists all subscriptions with filtering, search, and pagination.

**Query Parameters:**
- `search`: Search by name, email, or phone
- `level`: Filter by professional level
- `status`: Filter by subscription status (active/expired/suspended)

**Returns:**
- Paginated subscriptions list
- Statistics (total, active, expired, suspended, expiring)
- Revenue data (monthly, growth rate, by level)
- Level distribution

#### `show(User $user)`
Displays detailed subscription information for a specific user.

**Returns:**
- User details with current subscription
- Subscription history
- Payment history
- Recent commissions

#### `updateStatus(Request $request, User $user)`
Updates subscription status for a user.

**Validation:**
- `status`: required|in:active,suspended,cancelled
- `reason`: nullable|string|max:500

**Actions:**
- Updates user subscription_status
- Logs activity with reason and admin ID

#### `extendSubscription(Request $request, User $user)`
Extends subscription expiry date.

**Validation:**
- `days`: required|integer|min:1|max:365
- `reason`: nullable|string|max:500

**Actions:**
- Adds days to subscription_expires_at
- Logs activity with extension details

#### `forceUpgrade(Request $request, User $user)`
Manually upgrades user's professional level.

**Validation:**
- `level`: required|in:Associate,Professional,Senior,Manager,Director,Executive,Ambassador
- `reason`: nullable|string|max:500

**Actions:**
- Updates professional_level
- Logs activity with old and new levels

#### `bulkAction(Request $request)`
Performs bulk operations on multiple users.

**Validation:**
- `action`: required|in:suspend,activate,extend
- `user_ids`: required|array
- `days`: required_if:action,extend|integer|min:1|max:365
- `reason`: nullable|string|max:500

**Actions:**
- Processes action for all selected users
- Returns count of affected users

## Vue Components

### Index.vue
Main subscription management interface.

**Props:**
- `subscriptions`: Paginated user list
- `stats`: Subscription statistics
- `revenueData`: Revenue analytics
- `filters`: Current filter values
- `levels`: Available professional levels

**Features:**
- Statistics cards
- Revenue overview
- Level distribution chart
- Search and filters
- Bulk actions panel
- Subscription table with actions
- Action modals (status, extend, upgrade)

### Show.vue
Detailed subscription view for individual user.

**Props:**
- `user`: User details with subscription
- `subscriptionHistory`: Past subscriptions
- `paymentHistory`: Transaction history

**Features:**
- Current subscription status
- Quick stats sidebar
- Subscription history table
- Payment history table
- Commission history table
- Action modals

## Usage Examples

### Extending Multiple Subscriptions
1. Navigate to Subscription Management
2. Click "Bulk Actions"
3. Select users using checkboxes
4. Choose "Extend" action
5. Enter number of days (e.g., 30)
6. Add optional reason
7. Click "Execute"

### Suspending a Subscription
1. Find user in subscription list
2. Click "Status" button
3. Select "Suspended" from dropdown
4. Enter reason for suspension
5. Click "Update Status"

### Viewing Subscription Details
1. Click "View" next to any subscription
2. Review current status and expiry
3. Check subscription history
4. View payment transactions
5. Perform quick actions as needed

## Activity Logging

All admin actions are logged using Laravel's activity log:
- Action type (status update, extension, upgrade)
- Admin user ID
- Timestamp
- Reason provided
- Old and new values (for upgrades)

## Security & Permissions

- All routes protected by `auth` and `admin` middleware
- Activity logging for audit trail
- Reason field for accountability
- Validation on all inputs

## Integration Points

### Database Tables
- `users`: Subscription status and expiry
- `subscriptions`: Subscription history
- `transactions`: Payment records
- `packages`: Subscription packages
- `activity_log`: Admin action tracking

### Related Systems
- Points System (LP/BP)
- Package Management
- Transaction Processing
- User Management
- Referral System

## Future Enhancements

1. **Automated Notifications**
   - Email reminders for expiring subscriptions
   - SMS alerts for suspended accounts
   - Renewal reminders

2. **Advanced Analytics**
   - Churn rate analysis
   - Lifetime value calculations
   - Retention metrics
   - Cohort analysis

3. **Subscription Automation**
   - Auto-renewal settings
   - Grace period configuration
   - Automatic downgrades

4. **Payment Integration**
   - Direct payment processing
   - Refund management
   - Invoice generation

## Troubleshooting

### Subscription Not Showing
- Check if user has Member role
- Verify subscription_expires_at is set
- Check filters are not too restrictive

### Bulk Action Not Working
- Ensure users are selected
- Verify action is chosen
- Check validation requirements (e.g., days for extend)

### Export Not Generating
- Check file permissions
- Verify export route is accessible
- Ensure data exists for current filters

## Support

For issues or questions:
- Check activity logs for admin actions
- Review user subscription history
- Verify database records
- Contact system administrator

---

**Last Updated**: October 21, 2025
**Version**: 1.0.0
**Status**: Production Ready
