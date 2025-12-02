# GrowBiz - Task & Employee Management Module

**Last Updated:** December 2, 2025
**Status:** Development - Phase 1, 2, 3, 4, 5 & 6 Complete
**Architecture:** Domain-Driven Design (DDD)

## Overview

GrowBiz is a comprehensive task and employee management system for SME business owners on the MyGrowNet platform. This module enables business owners to manage their staff, assign tasks, track progress, and improve operational efficiency.

## Architecture

### Bounded Context: GrowBiz Management

This module operates as a separate bounded context from the core MyGrowNet platform, with its own:
- Domain entities and value objects
- Repository interfaces and implementations
- Domain services
- Application use cases

### Directory Structure

```
app/
├── Domain/
│   └── GrowBiz/
│       ├── Entities/
│       │   ├── Task.php
│       │   └── Employee.php
│       ├── ValueObjects/
│       │   ├── TaskId.php
│       │   ├── EmployeeId.php
│       │   ├── TaskStatus.php
│       │   ├── TaskPriority.php
│       │   └── EmployeeStatus.php
│       ├── Services/
│       │   ├── TaskManagementService.php
│       │   ├── EmployeeManagementService.php
│       │   ├── EmployeeInvitationService.php
│       │   ├── AnalyticsService.php
│       │   ├── NotificationService.php
│       │   ├── SummaryService.php
│       │   └── ExportService.php
│       └── Repositories/
│           ├── TaskRepositoryInterface.php
│           └── EmployeeRepositoryInterface.php
├── Infrastructure/
│   └── Persistence/
│       ├── Eloquent/
│       │   ├── SmeTaskModel.php
│       │   ├── SmeEmployeeModel.php
│       │   ├── SmeTaskAssignmentModel.php
│       │   ├── SmeTaskUpdateModel.php
│       │   ├── SmeTaskCommentModel.php
│       │   ├── SmeTaskAttachmentModel.php
│       │   └── GrowBizEmployeeInvitationModel.php
│       └── Repositories/
│           ├── GrowBizTaskRepository.php
│           └── GrowBizEmployeeRepository.php
├── Http/
│   └── Controllers/
│       └── GrowBiz/
│           ├── DashboardController.php
│           ├── TaskController.php
│           ├── EmployeeController.php
│           ├── InvitationController.php
│           └── ReportsController.php
└── Providers/
    └── GrowBizServiceProvider.php

resources/js/
├── Pages/
│   └── GrowBiz/
│       ├── Dashboard.vue
│       ├── Tasks/
│       │   ├── Index.vue
│       │   ├── Create.vue
│       │   ├── Show.vue
│       │   └── Edit.vue
│       ├── Employees/
│       │   ├── Index.vue
│       │   ├── Create.vue
│       │   ├── Show.vue
│       │   └── Edit.vue
│       ├── Reports/
│       │   ├── Analytics.vue
│       │   ├── Performance.vue
│       │   └── Summaries.vue
│       └── Invitation/
│           ├── Accept.vue
│           ├── EnterCode.vue
│           └── Invalid.vue
└── Components/
    └── GrowBiz/
        ├── StatusBadge.vue
        ├── PriorityBadge.vue
        ├── ProgressBar.vue
        ├── CommentSection.vue
        ├── TimeLogModal.vue
        └── InviteEmployeeModal.vue

routes/
└── growbiz.php
```

---

## Implementation Phases

### Phase 1: Core Task Management ✅ COMPLETE
- [x] Database migrations (7 tables)
- [x] Domain entities (Task, Employee)
- [x] Value objects (TaskId, TaskStatus, TaskPriority, EmployeeId, EmployeeStatus)
- [x] Repository interfaces
- [x] Repository implementations (Eloquent)
- [x] Domain services (TaskManagementService, EmployeeManagementService)
- [x] Eloquent models (6 models)
- [x] Service provider with DI bindings
- [x] API endpoints (controllers, routes)
- [x] Vue components (Dashboard, Task CRUD, Employee CRUD)

### Phase 2: Employee Management ✅ COMPLETE
- [x] Employee CRUD operations
- [x] Employee activation/deactivation
- [x] Employee-task linking
- [x] Employee dashboard

### Phase 3: Progress Tracking ✅ COMPLETE
- [x] Task updates and history (activity feed)
- [x] Progress percentage tracking (0-100% with slider)
- [x] Time tracking (log hours with notes)
- [x] Status transitions with audit trail
- [x] Auto-status updates based on progress
- [x] Time efficiency calculations
- [x] Task notes system

### Phase 4: Communication ✅ COMPLETE
- [x] Task comments (add/delete with real-time updates)
- [x] Notification system (TaskAssigned, TaskComment, TaskStatusChanged, TaskDueReminder)
- [x] NotificationService for centralized notification handling
- [x] CommentSection Vue component
- [x] Activity feed (already implemented in Phase 3)

### Phase 5: Dashboards & Reports ✅ COMPLETE
- [x] AnalyticsService for comprehensive task analytics
- [x] ReportsController for analytics and performance pages
- [x] Analytics page with:
  - Task summary cards (total, completion rate, on-time rate, overdue)
  - Period summaries (this week vs this month)
  - Tasks by status (visual bar chart)
  - Tasks by priority (grid view)
  - Time tracking metrics (estimated vs actual, efficiency)
  - Team workload distribution
  - 14-day productivity trend chart
  - Due date overview
- [x] Performance page with:
  - Period selector (day, week, month, quarter)
  - Period summary card
  - Employee performance list with metrics
  - On-time completion rates
- [x] Navigation links in More menu

### Phase 6: Summaries & Exports ✅ COMPLETE
- [x] Daily summaries with date navigation
- [x] Weekly summaries with breakdown
- [x] SummaryService for generating summaries
- [x] ExportService for CSV exports
- [x] Export tasks to CSV
- [x] Export employees to CSV
- [x] Export weekly summary to CSV
- [x] Export performance report to CSV
- [x] Summaries Vue page with tabs (daily/weekly)
- [x] Export buttons in UI
- [x] Navigation link in More menu

### Phase 7: Employee Invitations ✅ COMPLETE
- [x] Dual invitation system (email link + code)
- [x] EmployeeInvitation entity with token/code generation
- [x] EmployeeInvitationService for invitation management
- [x] Email invitation with unique link
- [x] Code invitation (6-character alphanumeric)
- [x] Invitation acceptance flow (token and code)
- [x] Employee-User account linking
- [x] InviteEmployeeModal Vue component
- [x] Invitation acceptance pages (Accept, EnterCode, Invalid)
- [x] InvitationController for public/auth routes
- [x] EmployeeInvitationNotification for email delivery
- [x] Database migration for invitations table

### Phase 8: Notifications & Messaging ✅ COMPLETE
- [x] Notification bell in header connected to notifications page
- [x] Unread notification count badge
- [x] Notifications page with mark as read functionality
- [x] Team messaging system integrated with platform messaging
- [x] Messages icon in header with unread count
- [x] Messages page with inbox/sent tabs
- [x] Compose message modal
- [x] Conversation view with reply functionality
- [x] Task assignment notifications sent automatically
- [x] Task status change notifications
- [x] Task comment notifications
- [x] NotificationController for GrowBiz-specific notifications
- [x] MessageController using platform messaging use cases
- [x] Middleware updated with notification/message counts

---

## Database Schema

### Tables

#### `growbiz_employees`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key (auto-increment) |
| manager_id | foreignId | Owner (users table) |
| user_id | foreignId | Linked user account (nullable) |
| first_name | string | First name |
| last_name | string | Last name |
| email | string | Email address |
| phone | string | Phone number |
| position | string | Job position |
| department | string | Department |
| status | enum | active, inactive, on_leave, terminated |
| hire_date | date | Hire date |
| hourly_rate | decimal | Hourly rate |
| notes | text | Additional notes |

#### `growbiz_tasks`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key (auto-increment) |
| manager_id | foreignId | Owner (users table) |
| title | string | Task title |
| description | text | Task description |
| priority | enum | low, medium, high, urgent |
| status | enum | pending, in_progress, on_hold, completed, cancelled |
| due_date | date | Due date |
| category | string | Task category |
| progress_percentage | tinyint | Progress 0-100% |
| estimated_hours | decimal | Estimated hours to complete |
| actual_hours | decimal | Actual hours logged |
| started_at | timestamp | When task was started |
| completed_at | timestamp | When task was completed |
| tags | json | Task tags |

#### `growbiz_task_assignments`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| task_id | foreignId | Task reference |
| employee_id | foreignId | Employee reference |
| assigned_at | timestamp | Assignment time |
| completed_at | timestamp | Completion time (nullable) |

#### `growbiz_task_updates`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| task_id | foreignId | Task reference |
| employee_id | foreignId | Employee reference (nullable) |
| user_id | foreignId | User reference (nullable) |
| update_type | enum | status_change, progress_update, time_log, note |
| old_status | string | Previous status (for status_change) |
| new_status | string | New status (for status_change) |
| old_progress | tinyint | Previous progress % (for progress_update) |
| new_progress | tinyint | New progress % (for progress_update) |
| hours_logged | decimal | Hours logged (for time_log) |
| notes | text | Update notes |

#### `growbiz_task_comments`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| task_id | foreignId | Task reference |
| user_id | foreignId | Commenter (users) |
| content | text | Comment content |

#### `growbiz_task_attachments`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| task_id | foreignId | Task reference |
| user_id | foreignId | Uploader (users) |
| file_path | string | Storage path |
| file_name | string | Original filename |
| file_type | enum | image, document, text |
| file_size | integer | Size in bytes |
| description | text | File description |

#### `growbiz_employee_invitations`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| employee_id | foreignId | Employee reference |
| manager_id | foreignId | Manager who sent invite |
| email | string | Email address (for email invites) |
| token | string(64) | Unique token for email links |
| code | string(8) | 6-character invitation code |
| type | enum | email, code |
| status | enum | pending, accepted, expired, revoked |
| expires_at | timestamp | Expiration date |
| accepted_at | timestamp | When accepted (nullable) |
| accepted_by_user_id | foreignId | User who accepted (nullable) |

---

## Routes

All routes are prefixed with `/growbiz` and use the `growbiz.` name prefix.

### Dashboard
| Method | Route | Name | Description |
|--------|-------|------|-------------|
| GET | `/growbiz` | `growbiz.dashboard` | Main dashboard |

### Tasks
| Method | Route | Name | Description |
|--------|-------|------|-------------|
| GET | `/growbiz/tasks` | `growbiz.tasks.index` | List all tasks |
| GET | `/growbiz/tasks/create` | `growbiz.tasks.create` | Create task form |
| POST | `/growbiz/tasks` | `growbiz.tasks.store` | Store new task |
| GET | `/growbiz/tasks/{id}` | `growbiz.tasks.show` | View task details |
| GET | `/growbiz/tasks/{id}/edit` | `growbiz.tasks.edit` | Edit task form |
| PUT | `/growbiz/tasks/{id}` | `growbiz.tasks.update` | Update task |
| DELETE | `/growbiz/tasks/{id}` | `growbiz.tasks.destroy` | Delete task |
| PATCH | `/growbiz/tasks/{id}/status` | `growbiz.tasks.status` | Update status |
| PATCH | `/growbiz/tasks/{id}/progress` | `growbiz.tasks.progress` | Update progress % |
| POST | `/growbiz/tasks/{id}/time` | `growbiz.tasks.time` | Log time worked |
| POST | `/growbiz/tasks/{id}/notes` | `growbiz.tasks.notes.store` | Add note |
| GET | `/growbiz/tasks/{id}/updates` | `growbiz.tasks.updates` | Get activity feed |
| POST | `/growbiz/tasks/{id}/comments` | `growbiz.tasks.comments.store` | Add comment |
| DELETE | `/growbiz/tasks/{id}/comments/{commentId}` | `growbiz.tasks.comments.destroy` | Delete comment |

### Employees
| Method | Route | Name | Description |
|--------|-------|------|-------------|
| GET | `/growbiz/employees` | `growbiz.employees.index` | List employees |
| GET | `/growbiz/employees/create` | `growbiz.employees.create` | Add employee form |
| POST | `/growbiz/employees` | `growbiz.employees.store` | Store new employee |
| GET | `/growbiz/employees/{id}` | `growbiz.employees.show` | View employee |
| GET | `/growbiz/employees/{id}/edit` | `growbiz.employees.edit` | Edit employee form |
| PUT | `/growbiz/employees/{id}` | `growbiz.employees.update` | Update employee |
| DELETE | `/growbiz/employees/{id}` | `growbiz.employees.destroy` | Remove employee |
| PATCH | `/growbiz/employees/{id}/status` | `growbiz.employees.status` | Update status |
| GET | `/growbiz/employees/{id}/tasks` | `growbiz.employees.tasks` | Employee tasks |
| POST | `/growbiz/employees/{id}/invite/email` | `growbiz.employees.invite.email` | Send email invitation |
| POST | `/growbiz/employees/{id}/invite/code` | `growbiz.employees.invite.code` | Generate invitation code |
| GET | `/growbiz/employees/{id}/invitation` | `growbiz.employees.invitation` | Get pending invitation |
| DELETE | `/growbiz/employees/{id}/invitation/{invitationId}` | `growbiz.employees.invitation.revoke` | Revoke invitation |

### Invitations (Public)
| Method | Route | Name | Description |
|--------|-------|------|-------------|
| GET | `/growbiz/invitation/accept/{token}` | `growbiz.invitation.accept` | View invitation (email link) |
| GET | `/growbiz/invitation/code` | `growbiz.invitation.code` | Enter code page |

### Invitations (Authenticated)
| Method | Route | Name | Description |
|--------|-------|------|-------------|
| POST | `/growbiz/invitation/accept/{token}` | `growbiz.invitation.accept.submit` | Accept via token |
| POST | `/growbiz/invitation/code` | `growbiz.invitation.code.submit` | Accept via code |
| GET | `/growbiz/invitation/pending` | `growbiz.invitation.pending` | Handle pending invitation |

### Notifications
| Method | Route | Name | Description |
|--------|-------|------|-------------|
| GET | `/growbiz/notifications` | `growbiz.notifications.index` | List notifications |
| POST | `/growbiz/notifications/{id}/read` | `growbiz.notifications.read` | Mark as read |
| POST | `/growbiz/notifications/mark-all-read` | `growbiz.notifications.mark-all-read` | Mark all as read |
| GET | `/growbiz/notifications/unread-count` | `growbiz.notifications.unread-count` | Get unread count |

### Messages
| Method | Route | Name | Description |
|--------|-------|------|-------------|
| GET | `/growbiz/messages` | `growbiz.messages.index` | List messages |
| GET | `/growbiz/messages/{id}` | `growbiz.messages.show` | View conversation |
| POST | `/growbiz/messages` | `growbiz.messages.store` | Send new message |
| POST | `/growbiz/messages/{id}/reply` | `growbiz.messages.reply` | Reply to message |

### Reports
| Method | Route | Name | Description |
|--------|-------|------|-------------|
| GET | `/growbiz/reports/analytics` | `growbiz.reports.analytics` | Analytics dashboard |
| GET | `/growbiz/reports/performance` | `growbiz.reports.performance` | Performance reports |
| GET | `/growbiz/reports/summaries` | `growbiz.reports.summaries` | Summaries page |
| GET | `/growbiz/reports/daily-summary` | `growbiz.reports.daily-summary` | Daily summary API |
| GET | `/growbiz/reports/weekly-summary` | `growbiz.reports.weekly-summary` | Weekly summary API |
| GET | `/growbiz/reports/export/tasks` | `growbiz.reports.export.tasks` | Export tasks CSV |
| GET | `/growbiz/reports/export/employees` | `growbiz.reports.export.employees` | Export employees CSV |
| GET | `/growbiz/reports/export/weekly-summary` | `growbiz.reports.export.weekly-summary` | Export weekly summary CSV |
| GET | `/growbiz/reports/export/performance` | `growbiz.reports.export.performance` | Export performance CSV |

---

## Value Objects

### TaskPriority
- `low` - Low priority (gray)
- `medium` - Medium priority (blue)
- `high` - High priority (orange)
- `urgent` - Urgent priority (red)

### TaskStatus
- `pending` - Not started
- `in_progress` - Currently being worked on
- `on_hold` - Temporarily paused
- `completed` - Finished
- `cancelled` - Cancelled

### EmployeeStatus
- `active` - Currently employed (green)
- `inactive` - Not currently working (gray)
- `on_leave` - On leave (yellow)
- `terminated` - No longer employed (red)

---

## Usage

### Accessing GrowBiz
Navigate to `/growbiz` to access the GrowBiz dashboard.

### Creating a Task
1. Go to Dashboard or Tasks page
2. Click "New Task" or "Create New Task"
3. Fill in task details (title, description, priority, due date)
4. Optionally assign to employees
5. Click "Create Task"

### Managing Employees
1. Go to Employees page
2. Click "Add Employee"
3. Fill in employee details
4. Click "Add Employee"

### Assigning Tasks
1. Create or edit a task
2. Select employees from the assignment list
3. Save the task

### Inviting Employees to Platform

Business owners can invite employees to create platform accounts and link them to their employee records. Two methods are available:

#### Method 1: Email Invitation
1. Go to Employee details page
2. Click the invite button (user+ icon)
3. Select "Email Invite" tab
4. Enter employee's email address
5. Click "Send Invitation"
6. Employee receives email with unique link
7. Employee clicks link, logs in or registers, and is automatically linked

#### Method 2: Code Invitation
1. Go to Employee details page
2. Click the invite button (user+ icon)
3. Select "Invite Code" tab
4. Click "Generate Code"
5. Share the 6-character code with employee (verbally, SMS, etc.)
6. Employee goes to `/growbiz/invitation/code`
7. Employee enters code after logging in
8. Employee is automatically linked to their record

#### Invitation Features
- **Email invitations** expire after 7 days
- **Code invitations** expire after 30 days
- Only one pending invitation per employee at a time
- Previous invitations are automatically revoked when new one is created
- Linked employees show a green checkmark badge

---

## Changelog

### December 2, 2025 (Phase 8 - Notifications & Messaging)
- Implemented notification system integration:
  - Connected notification bell in header to notifications page
  - Added unread notification count badge with real-time updates
  - Created Notifications/Index.vue page with mark as read functionality
  - Notifications filtered to GrowBiz-specific types
- Implemented team messaging system:
  - Created Messages/Index.vue with inbox/sent tabs
  - Created Messages/Show.vue for conversation view
  - Integrated with platform's existing messaging use cases
  - Added compose message modal with team member selection
  - Messages prefixed with [GrowBiz] for filtering
- Updated GrowBizLayout:
  - Notification bell now links to notifications page
  - Added messages icon with unread count badge
  - Added Notifications and Messages to More menu
- Updated HandleInertiaRequests middleware:
  - Added unreadNotificationCount to shared data
  - Added unreadMessageCount to shared data
  - Added team member ID resolution for message filtering
- Task notifications now sent automatically:
  - TaskAssignedNotification on task creation with assignees
  - TaskStatusChangedNotification on status updates
  - TaskCommentNotification on new comments

### December 2, 2025 (Phase 7 - Employee Invitations)
- Implemented dual invitation system:
  - **Email Invitation**: Send email with unique link (expires in 7 days)
  - **Code Invitation**: Generate 6-character code (expires in 30 days)
- Created EmployeeInvitation entity with token/code value objects
- Created EmployeeInvitationService for invitation management
- Created InvitationController for public and authenticated routes
- Created EmployeeInvitationNotification for email delivery
- Created Vue components:
  - InviteEmployeeModal (email/code tabs)
  - Accept.vue (email link acceptance page)
  - EnterCode.vue (code entry page)
  - Invalid.vue (expired/invalid invitation page)
- Updated Employee Show page with invite button and account status
- Added invitation routes (public and authenticated)
- Database migration for growbiz_employee_invitations table

### December 2, 2025 (Phase 6)
- Implemented SummaryService for daily and weekly summaries
- Implemented ExportService for CSV exports
- Created Summaries Vue page with:
  - Daily summary tab with date navigation
  - Weekly summary tab with breakdown
  - Export buttons for all data types
- Added export endpoints:
  - Tasks export (CSV)
  - Employees export (CSV)
  - Weekly summary export (CSV)
  - Performance report export (CSV)
- Added summary API endpoints for dynamic loading
- Updated GrowBizLayout with Summaries & Export link
- Registered SummaryService and ExportService in GrowBizServiceProvider

### December 2, 2025 (Phase 5)
- Implemented AnalyticsService for comprehensive task analytics
- Created ReportsController with analytics and performance endpoints
- Added Analytics page with:
  - Summary cards (total tasks, completion rate, on-time rate, overdue)
  - Period comparisons (week vs month)
  - Tasks by status visualization
  - Tasks by priority grid
  - Time tracking metrics
  - Team workload distribution
  - 14-day productivity trend chart
  - Due date overview
- Added Performance page with:
  - Period selector (day/week/month/quarter)
  - Employee performance metrics
  - On-time completion tracking
- Updated GrowBizLayout with Analytics and Performance links in More menu
- Registered AnalyticsService in GrowBizServiceProvider

### December 2, 2025 (Phase 4)
- Implemented task comments system
- Added CommentSection Vue component with add/delete functionality
- Created notification classes:
  - TaskAssignedNotification
  - TaskCommentNotification
  - TaskStatusChangedNotification
  - TaskDueReminderNotification
- Created NotificationService for centralized notification handling
- Added comment routes and controller methods
- Updated Task Show page with comments section

### December 2, 2025 (Phase 3)
- Implemented progress tracking system
- Added progress percentage with visual slider (0-100%)
- Added time logging with quick-select buttons
- Added task activity feed with timeline
- Added task notes system
- Auto-status transitions based on progress
- Time efficiency calculations (estimated vs actual)
- New components: ProgressBar, TimeLogModal, TaskActivityFeed
- Updated Task entity with progress tracking methods
- New API endpoints: progress, time, notes, updates

### December 2, 2025 (SPA Enhancements)
- Added SPA enhancements for native app feel
- Global loading bar during navigation
- Toast notification system
- Inertia.js composables for partial reloads and optimistic UI
- See `SPA_ENHANCEMENTS.md` for details

### December 1, 2025
- Renamed module from "SME" to "GrowBiz"
- Updated all routes to use `growbiz` prefix
- Updated all Vue components to use GrowBiz namespace
- Created GrowBizServiceProvider
- Updated documentation

### December 1, 2025 (Initial)
- Phase 1 & 2 complete
- Core task and employee management implemented
- Full CRUD operations for tasks and employees
- Dashboard with statistics
- Filtering and search functionality
