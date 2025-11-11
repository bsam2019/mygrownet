# Announcements System - Quick Guide

## Creating Announcements

### Via Admin Interface (Recommended)

**Access:** `http://localhost:8000/admin/announcements`

**Requirements:** Admin role

**Features:**
- Visual form with all options
- Type selection (Info, Warning, Success, Urgent)
- Target audience dropdown
- Character counter
- Active/inactive toggle
- Urgent flag
- View all announcements
- Activate/deactivate existing announcements
- Delete announcements

Simply fill out the form and click "Create Announcement"!

### Via Tinker (Alternative Method)

```bash
php artisan tinker
```

```php
use App\Infrastructure\Persistence\Eloquent\Announcement\AnnouncementModel;

// Create announcement
AnnouncementModel::create([
    'title' => 'Your Announcement Title',
    'message' => 'Your announcement message here...',
    'type' => 'info', // info, warning, success, urgent
    'target_audience' => 'all', // See targeting options below
    'is_urgent' => false,
    'is_active' => true,
    'created_by' => 1, // Admin user ID
]);
```

## Announcement Types

| Type | Color | Use Case |
|------|-------|----------|
| `info` | Blue | General information, updates, tips |
| `warning` | Amber | Caution, reminders, upcoming changes |
| `success` | Green | Achievements, congratulations, positive news |
| `urgent` | Red | Critical alerts, immediate action required |

## Target Audience Options

### All Users
```php
'target_audience' => 'all'
```

### Starter Kit Owners
```php
'target_audience' => 'starter_kit_owners'
```

### Specific Tier
```php
'target_audience' => 'tier:Associate'
'target_audience' => 'tier:Professional'
'target_audience' => 'tier:Senior'
'target_audience' => 'tier:Manager'
'target_audience' => 'tier:Director'
'target_audience' => 'tier:Executive'
'target_audience' => 'tier:Ambassador'
```

### Multiple Tiers
```php
'target_audience' => 'tier:Manager,Director,Executive,Ambassador'
```

## Examples

### Welcome New Members
```php
AnnouncementModel::create([
    'title' => 'Welcome to MyGrowNet! 🎉',
    'message' => 'Thank you for joining our community. Explore your dashboard to discover earning opportunities!',
    'type' => 'info',
    'target_audience' => 'all',
    'is_urgent' => false,
    'is_active' => true,
    'created_by' => 1,
]);
```

### Urgent System Alert
```php
AnnouncementModel::create([
    'title' => 'System Maintenance Tonight',
    'message' => 'The platform will be unavailable from 2:00 AM to 4:00 AM for scheduled maintenance.',
    'type' => 'urgent',
    'target_audience' => 'all',
    'is_urgent' => true,
    'is_active' => true,
    'created_by' => 1,
]);
```

### Tier-Specific Promotion
```php
AnnouncementModel::create([
    'title' => 'Exclusive Training for Leaders',
    'message' => 'New leadership development program now available. Register in the Learning Center!',
    'type' => 'success',
    'target_audience' => 'tier:Manager,Director,Executive,Ambassador',
    'is_urgent' => false,
    'is_active' => true,
    'created_by' => 1,
]);
```

### Starter Kit Promotion
```php
AnnouncementModel::create([
    'title' => 'Limited Time: Starter Kit Bonus',
    'message' => 'Purchase a Starter Kit this week and receive 50 bonus LGR points!',
    'type' => 'warning',
    'target_audience' => 'all',
    'is_urgent' => false,
    'is_active' => true,
    'created_by' => 1,
]);
```

## Managing Announcements

### Deactivate Announcement
```php
$announcement = AnnouncementModel::find(1);
$announcement->is_active = false;
$announcement->save();
```

### Update Announcement
```php
$announcement = AnnouncementModel::find(1);
$announcement->title = 'Updated Title';
$announcement->message = 'Updated message';
$announcement->save();
```

### Delete Announcement
```php
AnnouncementModel::find(1)->delete();
```

### View All Active Announcements
```php
AnnouncementModel::where('is_active', true)->get();
```

### View Announcements by Type
```php
AnnouncementModel::where('type', 'urgent')->get();
```

## Best Practices

1. **Keep it concise** - Mobile users see limited space
2. **Use emojis sparingly** - 1-2 per announcement maximum
3. **Clear call-to-action** - Tell users what to do next
4. **Target appropriately** - Don't spam all users with tier-specific content
5. **Mark urgent carefully** - Reserve for truly critical alerts
6. **Deactivate old announcements** - Keep the feed fresh
7. **Test before activating** - Preview on mobile dashboard

## Viewing Statistics

### Count Reads for Announcement
```php
use App\Infrastructure\Persistence\Eloquent\Announcement\AnnouncementReadModel;

$reads = AnnouncementReadModel::where('announcement_id', 1)->count();
echo "Announcement read by {$reads} users";
```

### Find Unread Users
```php
$announcement = AnnouncementModel::find(1);
$readUserIds = AnnouncementReadModel::where('announcement_id', 1)
    ->pluck('user_id')
    ->toArray();

$unreadUsers = \App\Models\User::whereNotIn('id', $readUserIds)->count();
echo "{$unreadUsers} users haven't read this announcement";
```

## Future: Admin Interface

An admin interface is planned with:
- Visual announcement builder
- Rich text editor
- Preview before publish
- Analytics dashboard
- Scheduled publishing
- A/B testing

For now, use Tinker or create a simple Artisan command for your team.
