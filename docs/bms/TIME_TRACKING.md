# Time Tracking System

**Last Updated:** February 12, 2026  
**Status:** Production Ready

## Overview

Complete time tracking system for CMS with start/stop timers, manual time entries, billable/non-billable hours, timesheet generation, approval workflows, and payroll integration.

## Features

### Timer Functionality
- Start/stop timer for workers
- One active timer per worker
- Automatic duration calculation
- Real-time timer display

### Manual Time Entry
- Create time entries manually
- Specify start and end times
- Add descriptions and notes
- Set billable status and hourly rate

### Billable Hours
- Mark entries as billable or non-billable
- Automatic amount calculation based on hourly rate
- Override hourly rate per entry
- Track billable vs non-billable hours

### Approval Workflow
- Draft → Submitted → Approved/Rejected flow
- Approval required before payroll inclusion
- Rejection with reason tracking
- Audit trail for all actions

### Timesheets
- Generate timesheets for periods (weekly/biweekly/monthly)
- Automatic calculation of totals
- Submit timesheets for approval
- Track timesheet status

### Payroll Integration
- Approved time entries included in payroll
- Link time entries to payroll runs
- Prevent duplicate payroll inclusion
- Calculate wages from time entries

### Reports
- Time reports by worker, job, date range
- Total hours, billable hours, amounts
- Export capabilities
- Filter and search

## Database Schema

### cms_time_entries
- Timer and manual entry tracking
- Start/stop times, duration
- Billable status and rates
- Approval workflow fields
- Payroll integration

### cms_timesheets
- Period-based grouping
- Total hours calculations
- Approval workflow
- Worker and date range

## API Endpoints

### Time Entries
- `GET /cms/time-tracking` - List entries
- `POST /cms/time-tracking/start-timer` - Start timer
- `POST /cms/time-tracking/{entry}/stop-timer` - Stop timer
- `POST /cms/time-tracking` - Create manual entry
- `PUT /cms/time-tracking/{entry}` - Update entry
- `DELETE /cms/time-tracking/{entry}` - Delete entry
- `POST /cms/time-tracking/{entry}/submit` - Submit for approval
- `POST /cms/time-tracking/{entry}/approve` - Approve entry
- `POST /cms/time-tracking/{entry}/reject` - Reject entry

### Timesheets
- `GET /cms/time-tracking/timesheets` - List timesheets
- `POST /cms/time-tracking/timesheets/generate` - Generate timesheet
- `POST /cms/time-tracking/timesheets/{timesheet}/submit` - Submit
- `POST /cms/time-tracking/timesheets/{timesheet}/approve` - Approve
- `POST /cms/time-tracking/timesheets/{timesheet}/reject` - Reject

### Reports
- `GET /cms/time-tracking/reports` - Time reports

## Implementation Files

### Backend
- `database/migrations/2026_02_12_160000_create_cms_time_tracking_tables.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/TimeEntryModel.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/TimesheetModel.php`
- `app/Domain/CMS/Core/Services/TimeTrackingService.php`
- `app/Http/Controllers/CMS/TimeTrackingController.php`

### Frontend
- `resources/js/Pages/CMS/TimeTracking/Index.vue`

### Routes
- `routes/cms.php` (time-tracking routes)

## Usage

### Starting a Timer
```php
POST /cms/time-tracking/start-timer
{
  "worker_id": 1,
  "job_id": 5,
  "description": "Working on foundation",
  "is_billable": true
}
```

### Stopping a Timer
```php
POST /cms/time-tracking/{entry}/stop-timer
```

### Creating Manual Entry
```php
POST /cms/time-tracking
{
  "worker_id": 1,
  "job_id": 5,
  "start_time": "2026-02-12 08:00:00",
  "end_time": "2026-02-12 17:00:00",
  "description": "Full day work",
  "is_billable": true,
  "hourly_rate": 50.00
}
```

### Generating Timesheet
```php
POST /cms/time-tracking/timesheets/generate
{
  "worker_id": 1,
  "start_date": "2026-02-05",
  "end_date": "2026-02-11",
  "period_type": "weekly"
}
```

## Business Rules

1. **One Active Timer**: Worker can only have one running timer at a time
2. **Draft Editing**: Only draft entries can be edited or deleted
3. **Approval Required**: Entries must be approved before payroll inclusion
4. **Duration Calculation**: Automatic calculation on timer stop or manual entry
5. **Amount Calculation**: Billable entries calculate amount from hourly rate
6. **Timesheet Generation**: Only approved entries included in timesheets
7. **Payroll Integration**: Approved entries can be included in payroll runs

## Security

- Company-scoped queries (all queries filtered by company_id)
- Authorization policies for approve/reject actions
- Audit trail for all time entry actions
- Validation of worker and job associations

## Future Enhancements

- GPS tracking for field workers
- Photo capture for job site verification
- Break time tracking
- Overtime calculation
- Mobile app for timer control
- Real-time timer sync across devices
- Time entry templates
- Bulk approval
- Advanced reporting with charts

## Changelog

### February 12, 2026
- Initial implementation
- Timer functionality
- Manual time entries
- Approval workflow
- Timesheet generation
- Payroll integration
- Basic reporting
