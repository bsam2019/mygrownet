# Starter Kit System Documentation

## Overview

The Starter Kit System automatically provides new members with a welcome package upon registration. This system is designed to streamline onboarding and ensure every new member receives their initial benefits, including Life Points (LP) and access to basic platform features.

## Features

### Automatic Assignment
- **Trigger**: Automatically assigned when a new user registers
- **Package**: "Starter Kit - Associate" package
- **Cost**: K150 (Registration fee K50 + First month Basic K100)
- **Duration**: One-time purchase with 1-month Basic membership included

### Initial Benefits
- **Life Points**: 100 LP awarded upon registration
- **Bonus Points**: 0 MAP (Monthly Activity Points start accumulating through activities)
- **Membership**: First month of Basic membership included
- **Resources**: Welcome learning pack, getting started guide, community access

### Package Contents
The Starter Kit includes:
1. One-time registration fee
2. First month Basic membership
3. Welcome learning pack
4. Getting started guide
5. Community access
6. Initial mentorship session
7. Starter resources bundle

## Technical Implementation

### Components

#### 1. StarterKitService
**Location**: `app/Services/StarterKitService.php`

**Methods**:
- `processStarterKit(User $user)`: Processes starter kit for new member
- `hasStarterKit(User $user)`: Checks if user has received starter kit
- `getStarterKitDetails()`: Returns starter kit package details

**Process Flow**:
```php
1. Retrieve starter kit package from database
2. Create subscription record for the user
3. Record transaction in transaction history
4. Fire UserRegistered event for points system
5. Log the operation
```

#### 2. UserRegistered Event
**Location**: `app/Events/UserRegistered.php`

Fired when a new user completes registration. This event triggers the points system to award initial Life Points.

#### 3. AwardRegistrationPoints Listener
**Location**: `app/Listeners/AwardRegistrationPoints.php`

Listens for UserRegistered event and awards:
- **100 LP** (Life Points) - Initial registration bonus
- **0 MAP** (Monthly Activity Points) - Start fresh each month

#### 4. RegisteredUserController
**Location**: `app/Http/Controllers/Auth/RegisteredUserController.php`

Updated to automatically call `StarterKitService::processStarterKit()` after user creation.

### Database Schema

#### Packages Table
```sql
- id: Primary key
- name: Package name
- slug: Unique identifier (starter-kit-associate)
- description: Package description
- price: Package price (150.00)
- billing_cycle: one-time
- duration_months: 1
- features: JSON array of features
- is_active: Boolean
- sort_order: Integer
```

#### Package Subscriptions Table
```sql
- id: Primary key
- user_id: Foreign key to users
- package_id: Foreign key to packages
- amount: Subscription amount
- status: active|expired|cancelled|pending
- start_date: Subscription start
- end_date: Subscription end
- renewal_date: Next renewal date
- auto_renew: Boolean (false for starter kit)
```

#### Transactions Table
Records the starter kit assignment as a transaction:
```sql
- type: 'subscription'
- description: 'Welcome Package - Starter Kit (Associate)'
- amount: 150.00
- status: 'completed'
```

## Admin Interface

### Starter Kit Management Page
**Route**: `/admin/starter-kits`
**Controller**: `App\Http\Controllers\Admin\StarterKitController`
**View**: `resources/js/pages/Admin/StarterKits/Index.vue`

**Features**:
- View starter kit package details
- Monitor assignment statistics
- Track recent assignments
- View assignment rate across all members

**Statistics Displayed**:
1. Total Assigned: Number of starter kits assigned
2. Total Members: Total registered members
3. Assignment Rate: Percentage of members with starter kits

## User Flow

### Registration Process
```
1. User fills registration form
   ↓
2. User account created
   ↓
3. Laravel Registered event fired
   ↓
4. StarterKitService processes starter kit
   ↓
5. Subscription created (status: active)
   ↓
6. Transaction recorded
   ↓
7. UserRegistered event fired
   ↓
8. AwardRegistrationPoints listener awards 100 LP
   ↓
9. User logged in and redirected to dashboard
```

### Member Dashboard
New members will see:
- Welcome message with starter kit details
- Initial 100 LP in their points balance
- Access to Basic membership features
- Getting started guide and resources

## Points System Integration

### Initial Points Award
- **Life Points (LP)**: 100 LP
  - Purpose: Long-term growth tracking
  - Never expires
  - Contributes to professional level advancement
  
- **Monthly Activity Points (MAP)**: 0 MAP
  - Purpose: Monthly earnings calculation
  - Resets on 1st of each month
  - Earned through activities

### Points Tracking
All points are tracked in the `point_transactions` table:
```sql
- user_id: Member receiving points
- source: 'registration'
- lp_amount: 100
- map_amount: 0
- description: "Welcome to MyGrowNet! Initial registration bonus"
```

## Configuration

### Package Seeder
**Location**: `database/seeders/PackageSeeder.php`

The starter kit package is seeded with:
```php
[
    'name' => 'Starter Kit - Associate',
    'slug' => 'starter-kit-associate',
    'price' => 150.00,
    'billing_cycle' => 'one-time',
    'duration_months' => 1,
    'features' => [
        'One-time registration fee',
        'First month Basic membership included',
        'Welcome learning pack',
        'Getting started guide',
        'Community access',
        'Initial mentorship session',
        'Starter resources bundle'
    ],
]
```

### Event Registration
**Location**: `app/Providers/EventServiceProvider.php`

```php
\App\Events\UserRegistered::class => [
    \App\Listeners\AwardRegistrationPoints::class,
],
```

## Testing

### Manual Testing
1. Register a new user account
2. Verify subscription created in `package_subscriptions` table
3. Verify transaction recorded in `transactions` table
4. Verify 100 LP awarded in `point_transactions` table
5. Check admin starter kits page for new assignment

### Test Accounts
After seeding, test with:
- Email: `test@example.com`
- Password: `password`

## Troubleshooting

### Common Issues

#### Starter Kit Not Assigned
**Symptoms**: New user registered but no starter kit subscription
**Solutions**:
1. Check if starter kit package exists: `php artisan db:seed --class=PackageSeeder`
2. Check application logs for errors
3. Verify StarterKitService is being called in RegisteredUserController

#### Points Not Awarded
**Symptoms**: Starter kit assigned but no LP awarded
**Solutions**:
1. Verify UserRegistered event is registered in EventServiceProvider
2. Check queue worker is running: `php artisan queue:work`
3. Check `point_transactions` table for failed transactions

#### Transaction Not Recorded
**Symptoms**: Subscription created but no transaction record
**Solutions**:
1. Check database transaction rollback in logs
2. Verify transactions table exists and is accessible
3. Check user permissions and database constraints

## Future Enhancements

### Planned Features
1. **Customizable Starter Kits**: Allow different starter kits based on referral source
2. **Welcome Email**: Automated email with starter kit details
3. **Onboarding Checklist**: Track completion of initial setup tasks
4. **Referrer Bonus**: Award bonus points to referrer when referee completes starter kit
5. **Analytics Dashboard**: Detailed analytics on starter kit conversion and engagement

### Potential Improvements
- Add starter kit expiration tracking
- Implement starter kit upgrade paths
- Create starter kit completion metrics
- Add A/B testing for different starter kit configurations

## Related Documentation

- [Points System Specification](POINTS_SYSTEM_SPECIFICATION.md)
- [MyGrowNet Platform Concept](MYGROWNET_PLATFORM_CONCEPT.md)
- [Unified Products & Services](UNIFIED_PRODUCTS_SERVICES.md)
- [Level Structure](LEVEL_STRUCTURE.md)

## Support

For issues or questions about the starter kit system:
1. Check application logs: `storage/logs/laravel.log`
2. Review this documentation
3. Contact development team

---

**Last Updated**: October 21, 2025
**Version**: 1.0
**Status**: Production Ready
