# LifePlus Schedule Blocks Implementation

**Last Updated:** January 31, 2026  
**Status:** Development

## Overview

Enhanced LifePlus task manager with time-based daily scheduling. Implements a hybrid approach where users can:
- Manage tasks with due dates (existing functionality)
- Create time-blocked schedule for daily planning (new feature)
- Optionally link schedule blocks to tasks

## Features

### Schedule Blocks
- **Time-based scheduling**: Set specific start and end times for activities
- **Daily planning**: Visual timeline view of your day
- **Categories**: Work, Personal, Health, Learning, Social, Other
- **Color coding**: Each category has a distinct color
- **Duration tracking**: Automatic calculation of time blocks
- **Completion tracking**: Mark blocks as complete
- **Task linking**: Optional connection to existing tasks
- **Recurring blocks**: Daily, weekly, weekdays, weekends patterns

### Use Cases
- **8:00-10:00 AM**: Work on project (linked to task)
- **10:00-12:00 PM**: Prayer/meditation
- **12:00-2:00 PM**: Lunch break
- **2:00-5:00 PM**: Client meetings
- **5:00-6:00 PM**: Exercise
- **6:00-7:00 PM**: Family time
- **7:00-9:00 PM**: Personal learning

## Database Schema

### `lifeplus_schedule_blocks` Table

```sql
- id: bigint (primary key)
- user_id: bigint (foreign key to users)
- task_id: bigint (nullable, foreign key to lifeplus_tasks)
- title: string (required)
- description: text (nullable)
- date: date (required)
- start_time: time (required)
- end_time: time (required)
- color: string (default: #3b82f6)
- category: enum (work, personal, health, learning, social, other)
- is_completed: boolean (default: false)
- completed_at: timestamp (nullable)
- is_recurring: boolean (default: false)
- recurrence_pattern: enum (daily, weekly, weekdays, weekends)
- recurrence_end_date: date (nullable)
- is_synced: boolean (default: true)
- local_id: string (nullable, for offline sync)
- created_at: timestamp
- updated_at: timestamp

Indexes:
- (user_id, date)
- (user_id, date, start_time)
```

## Implementation

### Backend Files

**Migration:**
- `database/migrations/2026_01_31_100000_create_lifeplus_schedule_blocks_table.php`

**Models:**
- `app/Models/LifePlus/ScheduleBlock.php` - Schedule block model
- `app/Models/LifePlus/Task.php` - Updated task model with schedule relationship

**Services:**
- `app/Domain/LifePlus/Services/ScheduleService.php` - Business logic for schedule management

**Controllers:**
- `app/Http/Controllers/LifePlus/ScheduleController.php` - HTTP endpoints

**Routes:**
- `routes/lifeplus.php` - Added schedule routes under `/lifeplus/schedule`

### API Endpoints

**GET `/lifeplus/schedule`** - Get schedule for a specific date
- Query params: `date` (optional, defaults to today)
- Returns: blocks, stats, total minutes

**GET `/lifeplus/schedule/week`** - Get week view
- Query params: `start_date` (optional, defaults to current week)
- Returns: 7 days with blocks for each

**POST `/lifeplus/schedule`** - Create schedule block
- Body: title, description, date, start_time, end_time, category, color, task_id, is_recurring, recurrence_pattern, recurrence_end_date
- Validates: No time overlaps
- Returns: Created block

**PUT `/lifeplus/schedule/{id}`** - Update schedule block
- Body: Any schedule block fields
- Validates: No time overlaps
- Returns: Updated block

**POST `/lifeplus/schedule/{id}/toggle`** - Toggle completion
- Returns: Updated block

**DELETE `/lifeplus/schedule/{id}`** - Delete schedule block
- Returns: Success message

### Frontend (Implemented)

**Pages Created:**
1. `resources/js/pages/LifePlus/Schedule/Index.vue` - Daily schedule view with date navigation
2. `resources/js/pages/LifePlus/Schedule/Week.vue` - Week view (to be implemented)

**Components Created:**
1. `resources/js/components/LifePlus/ScheduleTimeline.vue` - Visual timeline with time blocks (6 AM - 11 PM)
2. `resources/js/components/LifePlus/ScheduleBlockCard.vue` - Individual block display with completion toggle
3. `resources/js/components/LifePlus/CreateScheduleBlockModal.vue` - Form to create/edit blocks with recurring options
4. `resources/js/components/LifePlus/ScheduleStats.vue` - Daily statistics (total blocks, completion %, time scheduled)

**Features Implemented:**
- ✅ Visual timeline with hourly slots
- ✅ Color-coded categories (Work, Personal, Health, Learning, Social, Other)
- ✅ Progress indicators and statistics
- ✅ Quick actions (complete, delete)
- ✅ Recurring block setup (daily, weekly, weekdays, weekends)
- ✅ Date navigation (prev/next day, jump to today)
- ✅ Category selection with emoji icons
- ✅ Time validation (no overlaps)
- ✅ Empty state with call-to-action
- ✅ Added to LifePlus navigation menu

**Features To Be Implemented:**
- ⏳ Drag-and-drop to adjust times
- ⏳ Click empty slots to create blocks
- ⏳ Edit existing blocks
- ⏳ Link to existing tasks
- ⏳ Week view page
- ⏳ Offline sync support
- ⏳ Notifications for upcoming blocks

## Usage Examples

### Create a Work Block
```php
POST /lifeplus/schedule
{
    "title": "Team Meeting",
    "description": "Weekly standup with development team",
    "date": "2026-02-03",
    "start_time": "09:00",
    "end_time": "10:00",
    "category": "work",
    "color": "#3b82f6"
}
```

### Create Recurring Prayer Time
```php
POST /lifeplus/schedule
{
    "title": "Morning Prayer",
    "date": "2026-02-03",
    "start_time": "06:00",
    "end_time": "06:30",
    "category": "personal",
    "is_recurring": true,
    "recurrence_pattern": "daily",
    "recurrence_end_date": "2026-12-31"
}
```

### Link to Existing Task
```php
POST /lifeplus/schedule
{
    "title": "Work on Project X",
    "task_id": 123,
    "date": "2026-02-03",
    "start_time": "14:00",
    "end_time": "17:00",
    "category": "work"
}
```

## Validation Rules

1. **No Overlaps**: Schedule blocks cannot overlap for the same user on the same date
2. **Time Order**: End time must be after start time
3. **Recurring Validation**: If recurring, must have pattern and end date
4. **Task Linking**: Task must exist and belong to user

## Category Colors

- **Work**: #3b82f6 (Blue)
- **Personal**: #8b5cf6 (Purple)
- **Health**: #10b981 (Green)
- **Learning**: #f59e0b (Amber)
- **Social**: #ec4899 (Pink)
- **Other**: #6b7280 (Gray)

## Statistics Tracked

- Total blocks for the day
- Completed blocks
- Total scheduled minutes
- Completed minutes
- Completion percentage

## Next Steps

1. Run migration: `php artisan migrate`
2. Create frontend Vue components
3. Add to LifePlus navigation
4. Implement offline sync support
5. Add notifications for upcoming blocks
6. Mobile-optimized timeline view

## Integration with Tasks

- Tasks remain deadline-focused (due_date)
- Schedule blocks are time-focused (start_time, end_time)
- Users can link schedule blocks to tasks (optional)
- Completing a schedule block doesn't complete the task
- Tasks can have multiple schedule blocks (work sessions)

## Changelog

### January 31, 2026
- Initial implementation
- Created database migration
- Created models (ScheduleBlock, Task)
- Created ScheduleService with full business logic
- Created ScheduleController with REST API
- Added routes to lifeplus.php
- Implemented overlap validation
- Implemented recurring blocks
- Added category-based color coding
- **Frontend implementation completed:**
  - Created Schedule Index page with date navigation
  - Created ScheduleTimeline component with visual timeline
  - Created ScheduleBlockCard component with completion toggle
  - Created CreateScheduleBlockModal with recurring options
  - Created ScheduleStats component for daily statistics
  - Added "Day Plan" to LifePlus navigation menu
  - Ran migration successfully
