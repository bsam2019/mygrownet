# GrowStream Points Integration

**Last Updated:** March 11, 2026
**Status:** Production

## Overview

GrowStream is fully integrated with MyGrowNet's centralized points system. MLM members earn both Lifetime Points (LP) and Monthly Activity Points (MAP/BP) for engaging with video content.

## Point Configuration

### Centralized Management

All GrowStream point values are managed through the centralized Bonus Point Settings system, not stored per-video. This ensures:
- Consistent point values across all videos
- Easy bulk updates by admins
- Single source of truth for point configuration
- No data duplication

### Activity Types

GrowStream uses the following activity types in the centralized system:

| Activity Type | Description | Default LP | Default BP |
|--------------|-------------|------------|------------|
| `growstream_video_watch` | Starting to watch a video | 2 | 5 |
| `growstream_video_completion` | Completing a video (90%+) | 5 | 10 |
| `growstream_video_share` | Sharing a video | 3 | 8 |
| `growstream_subscription` | Subscribing to a channel | 10 | 20 |

### Admin Configuration

Admins configure GrowStream points through:
1. Navigate to Admin → Settings → Bonus Points
2. Find GrowStream activities in the list
3. Update LP and BP values as needed
4. Toggle activities on/off

## Implementation

### Database Tables

**Point Settings Tables:**
- `life_point_settings` - LP configuration for activities
- `bonus_point_settings` - BP/MAP configuration for activities

**Point Transaction Table:**
- `point_transactions` - Records all point awards with source tracking

### Point Award Flow

1. User performs GrowStream activity (watch, complete, share)
2. Event is fired (e.g., `VideoWatched`, `VideoCompleted`)
3. `AwardVideoPointsListener` handles the event
4. Listener checks if user is MLM member (`account_type = 'member'`)
5. Listener retrieves point values from centralized settings:
   - `LifePointSetting::getLPValue('growstream_video_watch')`
   - `BonusPointSetting::getBPValue('growstream_video_watch')`
6. `PointService::awardPoints()` creates transaction and updates user points
7. Points are recorded in `point_transactions` with source tracking

### Member-Only Points

Only MLM members (`account_type = 'member'`) earn points from GrowStream:
- Checked via `$user->isMember()` method
- Non-MLM users (clients, business accounts) can watch videos but don't earn points
- Logged for tracking and debugging

### Duplicate Prevention

Points are awarded only once per activity per video:
- Checked via `PointTransaction` records
- Uses `source`, `reference_type`, and `reference_id` for uniqueness
- Prevents gaming the system by re-watching

## Starter Kit Integration

Videos can be added to starter kits with additional LP rewards:
- `is_starter_kit_content` flag marks videos in starter kits
- `starter_kit_points_reward` provides bonus LP (not BP)
- Bonus is LP-focused to encourage level advancement
- Awarded in addition to regular watch/completion points

## Files Modified

### Backend
- `app/Domain/GrowStream/Infrastructure/Listeners/AwardVideoPointsListener.php` - Uses centralized settings
- `app/Domain/GrowStream/Presentation/Http/Controllers/Admin/GrowStreamAdminController.php` - Redirects to centralized settings
- `database/seeders/GrowStreamPointSettingsSeeder.php` - Seeds initial point values

### Models
- `app/Models/LifePointSetting.php` - LP configuration model
- `app/Models/BonusPointSetting.php` - BP configuration model

### Frontend
- `resources/js/Pages/Admin/GrowStream/Dashboard.vue` - Links to centralized settings

## Testing

To test the points integration:

1. **As Admin:**
   ```bash
   # Seed GrowStream point settings
   php artisan db:seed --class=GrowStreamPointSettingsSeeder
   
   # Verify settings in admin panel
   # Navigate to Admin → Settings → Bonus Points
   # Look for "GrowStream:" activities
   ```

2. **As MLM Member:**
   - Watch a video → Should earn LP and BP
   - Complete a video → Should earn LP and BP
   - Share a video → Should earn LP and BP
   - Check point transactions in profile

3. **As Non-MLM User:**
   - Watch videos → No points awarded
   - Check logs for "User is not an MLM member" messages

## Troubleshooting

### Points Not Awarded

1. Check if user is MLM member:
   ```php
   $user->account_type === 'member'
   ```

2. Check if activity is enabled:
   ```php
   LifePointSetting::where('activity_type', 'growstream_video_watch')
       ->where('is_active', true)
       ->exists()
   ```

3. Check for duplicate transactions:
   ```php
   PointTransaction::where('user_id', $userId)
       ->where('source', 'growstream_video_watch')
       ->where('reference_id', $videoId)
       ->exists()
   ```

4. Check logs:
   ```bash
   tail -f storage/logs/laravel.log | grep growstream
   ```

### Incorrect Point Values

1. Verify centralized settings:
   - Admin → Settings → Bonus Points
   - Check LP and BP values for GrowStream activities

2. Clear cache:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

## Changelog

### March 11, 2026
- Migrated from per-video point storage to centralized settings
- Removed `watch_lp`, `completion_lp`, `share_lp` fields from videos table
- Created `GrowStreamPointSettingsSeeder` for initial configuration
- Updated `AwardVideoPointsListener` to use `LifePointSetting` and `BonusPointSetting`
- Updated admin interface to redirect to centralized Bonus Point Settings
- Maintained starter kit bonus points (stored per-video for customization)

### March 11, 2026 (Earlier)
- Initial implementation with per-video point configuration
- Added LP and BP support for all GrowStream activities
- Implemented member-only point awards
- Added duplicate prevention logic
