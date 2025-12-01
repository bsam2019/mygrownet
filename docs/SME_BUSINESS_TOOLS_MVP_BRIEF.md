# MyGrowNet SME Business Tools – MVP Development Brief

**Last Updated:** November 30, 2025
**Status:** Development Planning
**Version:** 2.0

---

## Executive Summary

MyGrowNet will offer a suite of lightweight, practical digital tools designed specifically for small and medium-sized enterprises. The tools will help managers organize daily work, improve communication, strengthen accountability, and streamline operations. The aim is to solve real challenges faced by SMEs, using simple and affordable technology that fits local business environments.

### Core Problem

Most SMEs lack affordable systems to manage employees, tasks, and business processes. Many rely on WhatsApp, notebooks, and verbal communication. This leads to confusion, delays, poor record-keeping, and weak performance tracking.

### Proposed Solution

A simple, mobile-friendly set of tools inside the MyGrowNet vendor ecosystem that helps managers run their teams effectively. These tools are not complicated enterprise systems—they are designed for everyday use by small teams.

---

## Key Tools and Features

### 1. Task Management
- Create tasks and assign them to employees
- Set deadlines and priorities
- Track work progress
- Get notifications and reminders

**Outcome:** Smoother coordination and fewer forgotten tasks.

### 2. Employee Management
- Basic employee profiles
- Roles and responsibilities
- Attendance and leave
- Performance history

**Outcome:** Clearer accountability and easier team oversight.

### 3. Internal Communication
- Direct manager-to-employee messages
- Work-related chats separate from WhatsApp
- Announcement board for important updates

**Outcome:** Structured communication that does not get lost.

### 4. Proof of Work
- Upload photos, documents, or notes
- Employees submit work each time they complete a task

**Outcome:** Managers can verify progress without being physically present.

### 5. Reports and Summaries
- Daily or weekly activity summaries
- Task completion statistics
- Employee productivity reports

**Outcome:** Managers can see the health of their operations at a glance.

### 6. Simple Workflow Templates
- Checklists for common tasks
- Standard operating procedures
- Repeatable daily routines

**Outcome:** Consistency and reduced mistakes.

### 7. Order and Customer Tracking (Optional Add-on)
- Record customer orders
- Track fulfillment status
- Manage customer communication

**Outcome:** Easier organization for shops, salons, freelancers, repairers, and service providers.

### 8. Light Business Finance Tools (Future Phase)
- Record sales and expenses
- Quick profit overview
- Cashflow notes

**Outcome:** Basic financial visibility for SMEs with no accountant.

---

## Design Principles

| Principle | Implementation |
|-----------|----------------|
| **Simple** | Easy for anyone to use without training |
| **Mobile-first** | Works well on phones |
| **Low-data** | Minimal loading, offline-friendly sections |
| **Practical** | Solves real daily challenges |
| **Modular** | Businesses can activate only what they need |
| **Affordable** | Accessible to small teams |

---

## Why SMEs Will Use It

1. **Replaces scattered tools** with one organized system
2. **Saves time** and reduces confusion
3. **Helps managers control** their business even when away
4. **Strengthens employee accountability**
5. **Improves customer order handling**
6. **Creates digital records** for growth and audits

---

## 1. User Roles & Permissions

### A. Manager Role
**Capabilities:**
- Create and assign tasks to one or more employees
- Add and manage employees (activate/deactivate)
- Review task progress and status updates
- Communicate inside task threads
- Receive daily/weekly summaries
- View manager dashboard with task overview
- Edit and delete tasks
- Access employee performance data

**Permissions Policy:**
- Can manage all tasks and employees
- Can view all task updates and comments
- Can upload proof of work on behalf of employees (optional)

### B. Employee Role
**Capabilities:**
- View assigned tasks
- Update task status (Pending → In Progress → Done)
- Upload proof of work (images, files, text)
- Comment inside task threads
- View employee dashboard with assigned tasks
- Receive notifications for new tasks and comments

**Permissions Policy:**
- Can only view their assigned tasks
- Can only update their assigned tasks
- Cannot delete tasks
- Cannot assign tasks to others

---

## 2. MVP Features (Priority Order)

### Phase 1: Core Task Management
1. **Task Creation**
   - Title, description, due date, priority (Low/Medium/High)
   - Assign to one or more employees
   - Set task category/type (optional for MVP)

2. **Task List & Filtering**
   - Display all tasks with status filters (Pending / In Progress / Done)
   - Sort by due date, priority, assignee
   - Search by task title or description

3. **Task Editing & Deletion**
   - Managers can edit task details
   - Managers can delete tasks
   - Soft delete to preserve history

### Phase 2: Employee Management
1. **Employee Directory**
   - Add employee: name, phone, role, email
   - Activate/deactivate employees
   - View employee list with status

2. **Employee Task Assignment**
   - Bulk assign tasks to multiple employees
   - View tasks per employee
   - Track employee workload

### Phase 3: Task Progress & Proof of Work
1. **Status Updates**
   - Employees update task status with timestamp
   - Status history visible to manager
   - Automatic notifications on status change

2. **Proof of Work Upload**
   - Upload images (photos of completed work)
   - Upload files (documents, spreadsheets)
   - Add text descriptions
   - File storage with virus scanning (optional for MVP)

### Phase 4: Task Communication
1. **Threaded Comments**
   - Comments displayed like chat inside each task
   - Manager ↔ employee communication
   - Timestamp for each comment
   - Comment history preserved

2. **Notifications**
   - Database notifications for new tasks
   - Notifications for new comments
   - Notifications for status updates
   - In-app notification center

### Phase 5: Dashboards
1. **Manager Dashboard**
   - Total tasks today
   - Pending tasks count
   - Completed tasks count
   - Overdue tasks count
   - Quick add task button
   - Recent activity feed

2. **Employee Dashboard**
   - Assigned tasks list
   - Upcoming deadlines
   - Recent communication
   - Quick status update button

### Phase 6: Automated Summaries
1. **Daily Summary**
   - Number of tasks completed
   - Number of tasks pending
   - Number of overdue tasks
   - Employee performance snapshot

2. **Weekly Summary**
   - Weekly task completion rate
   - Top performing employees
   - Overdue tasks requiring attention
   - Trends and patterns

---

## 3. Technical Architecture

### Backend: Laravel

#### Database Models

```
User (existing, extend with role)
├── Employee
├── Task
├── TaskUpdate
├── TaskComment
└── TaskAttachment
```

#### Models to Create

1. **Employee**
   - user_id (FK)
   - name
   - phone
   - role
   - status (active/inactive)
   - created_at, updated_at

2. **Task**
   - id
   - manager_id (FK to User)
   - title
   - description
   - due_date
   - priority (low/medium/high)
   - status (pending/in_progress/done)
   - created_at, updated_at

3. **TaskAssignment**
   - id
   - task_id (FK)
   - employee_id (FK)
   - assigned_at
   - completed_at (nullable)

4. **TaskUpdate**
   - id
   - task_id (FK)
   - employee_id (FK)
   - status (pending/in_progress/done)
   - comment (nullable)
   - created_at

5. **TaskComment**
   - id
   - task_id (FK)
   - user_id (FK)
   - content
   - created_at, updated_at

6. **TaskAttachment**
   - id
   - task_id (FK)
   - user_id (FK)
   - file_path
   - file_type (image/document/text)
   - created_at

#### API Endpoints

**Task Management:**
- `POST /api/tasks` - Create task
- `GET /api/tasks` - List tasks (with filters)
- `GET /api/tasks/{id}` - Get task details
- `PUT /api/tasks/{id}` - Update task
- `DELETE /api/tasks/{id}` - Delete task

**Task Assignment:**
- `POST /api/tasks/{id}/assign` - Assign to employees
- `DELETE /api/tasks/{id}/assign/{employeeId}` - Remove assignment

**Task Progress:**
- `POST /api/tasks/{id}/status` - Update status
- `GET /api/tasks/{id}/updates` - Get status history

**Comments:**
- `POST /api/tasks/{id}/comments` - Add comment
- `GET /api/tasks/{id}/comments` - Get comments
- `DELETE /api/comments/{id}` - Delete comment

**Attachments:**
- `POST /api/tasks/{id}/attachments` - Upload proof
- `GET /api/tasks/{id}/attachments` - Get attachments
- `DELETE /api/attachments/{id}` - Delete attachment

**Employee Management:**
- `POST /api/employees` - Add employee
- `GET /api/employees` - List employees
- `PUT /api/employees/{id}` - Update employee
- `DELETE /api/employees/{id}` - Deactivate employee

**Dashboards:**
- `GET /api/dashboard/manager` - Manager dashboard data
- `GET /api/dashboard/employee` - Employee dashboard data

**Summaries:**
- `GET /api/summaries/daily` - Daily summary
- `GET /api/summaries/weekly` - Weekly summary

#### Authorization Policies

```php
// TaskPolicy
- create() → Manager only
- update() → Manager only
- delete() → Manager only
- view() → Manager or assigned employee
- viewComments() → Manager or assigned employee

// EmployeePolicy
- create() → Manager only
- update() → Manager only
- delete() → Manager only
- view() → Manager only

// TaskUpdatePolicy
- create() → Assigned employee or manager
- view() → Manager or assigned employee
```

#### Notifications

Use Laravel's database notification system:
- `TaskAssignedNotification` - When task assigned
- `TaskCommentedNotification` - When comment added
- `TaskStatusChangedNotification` - When status updated
- `TaskOverdueNotification` - When task overdue

---

## 4. Frontend: Vue.js Components

### Manager Pages

1. **Dashboard** (`ManagerDashboard.vue`)
   - Task summary cards
   - Recent activity feed
   - Quick add task button
   - Employee performance snapshot

2. **Task List** (`TaskList.vue`)
   - Filterable task table
   - Status badges
   - Assignee info
   - Due date indicators
   - Quick actions (edit, delete, view)

3. **Task Creation Form** (`TaskCreateForm.vue`)
   - Title, description inputs
   - Due date picker
   - Priority selector
   - Employee multi-select
   - Submit button

4. **Task Details** (`TaskDetails.vue`)
   - Task info header
   - Status history timeline
   - Comments section (chat-like)
   - Attachments gallery
   - Edit/delete actions

5. **Employee List** (`EmployeeList.vue`)
   - Employee table
   - Status indicators
   - Add employee button
   - Edit/deactivate actions

6. **Employee Add Form** (`EmployeeForm.vue`)
   - Name, phone, role inputs
   - Submit button

### Employee Pages

1. **Dashboard** (`EmployeeDashboard.vue`)
   - Assigned tasks list
   - Upcoming deadlines
   - Recent comments
   - Quick status update

2. **Task List** (`EmployeeTaskList.vue`)
   - My tasks filtered view
   - Status indicators
   - Due dates
   - Click to view details

3. **Task Details** (`EmployeeTaskDetails.vue`)
   - Task info
   - Comments section
   - Status update button
   - Upload proof modal

4. **Upload Proof Modal** (`UploadProofModal.vue`)
   - File/image upload
   - Text description
   - Submit button

### Shared Components

1. **TaskCard** - Compact task display
2. **CommentThread** - Chat-like comments
3. **StatusBadge** - Visual status indicator
4. **PriorityBadge** - Priority indicator
5. **NotificationCenter** - In-app notifications
6. **ConfirmDialog** - Delete confirmations

---

## 5. Design Guidelines

### Mobile-First Approach
- Responsive layout for phones, tablets, desktops
- Touch-friendly buttons (min 44px)
- Vertical scrolling on mobile
- Collapsible sections for space efficiency

### Visual Design
- Clean, minimal interface
- Use existing VBIF color scheme (blue primary, green success)
- Status colors: Green (Done), Blue (In Progress), Gray (Pending)
- Priority colors: Red (High), Orange (Medium), Gray (Low)

### User Experience
- Minimal clicks to assign tasks (2-3 clicks max)
- Inline editing where possible
- Keyboard shortcuts for power users (optional)
- Clear error messages
- Loading states for async operations

### Accessibility
- WCAG 2.1 AA compliance
- Semantic HTML
- ARIA labels for icons
- Keyboard navigation support
- Color not sole indicator of status

---

## 6. What NOT to Build (Out of Scope)

❌ HR modules (leave, contracts, payroll)
❌ Branch management
❌ Reports export (PDF/Excel)
❌ Advanced analytics charts
❌ Financial tools
❌ Workflow automation
❌ Third-party integrations
❌ Mobile app (web-responsive only)
❌ Offline mode
❌ Advanced permissions (roles beyond Manager/Employee)
❌ Time tracking
❌ Expense management

---

## 7. MVP Success Criteria

✅ **Managers can create and assign tasks** - Task creation form works, assignments persist
✅ **Employees can update task status** - Status dropdown updates correctly
✅ **Proof of work uploads work correctly** - Files upload, store, and display
✅ **Communication stays inside each task** - Comments thread displays properly
✅ **Summaries generate correctly** - Daily/weekly summaries show accurate data
✅ **Permissions enforced** - Employees can't delete tasks or manage others
✅ **Notifications work** - Users receive in-app notifications
✅ **Mobile responsive** - Works on phones and tablets
✅ **Performance acceptable** - Page loads < 2 seconds
✅ **No data loss** - All updates persist correctly

---

## 8. Why This Is Better Than WhatsApp

### 1. **Focused Work Communication**
- Work messages stay inside each task
- Nothing gets buried under unrelated chats
- Clear separation of personal and work

### 2. **Task Tracking & Accountability**
- Formal task assignment with clear ownership
- Status tracking (Pending → In Progress → Done)
- Full audit trail of who did what and when
- Proof of work documentation

### 3. **Accountability & Transparency**
- Each task shows who is responsible
- Timestamp of every update
- Proof of work (photo/file) attached
- Clear evidence of completion

### 4. **Team Clarity**
- Assign tasks to specific employees
- Individual dashboards prevent confusion
- Separate communication per task
- No group chat chaos

### 5. **Business Reporting**
- Daily/weekly summaries for managers
- Visual progress indicators
- Performance metrics per employee
- Easy performance reviews

### 6. **Long-Term Records**
- Permanent digital records
- Easy search and retrieval
- Task history for every employee
- Compliance documentation

### 7. **Work Structure**
- Standardized task templates
- Repeatable routines
- Structured work processes
- Best practices enforcement

### 8. **Scalability**
- Grows with business
- Handles multiple projects
- Supports manager performance
- No degradation with growth

---

## 9. Development Timeline (Estimated)

| Phase | Feature | Duration | Status |
|-------|---------|----------|--------|
| 1 | Core Task Management | 3-4 days | Planning |
| 2 | Employee Management | 2-3 days | Planning |
| 3 | Task Progress & Uploads | 3-4 days | Planning |
| 4 | Task Communication | 2-3 days | Planning |
| 5 | Dashboards | 2-3 days | Planning |
| 6 | Summaries | 2-3 days | Planning |
| Testing & Polish | QA & refinement | 2-3 days | Planning |
| **Total** | **MVP Complete** | **16-23 days** | **Planning** |

---

## 10. Database Schema (Quick Reference)

```sql
-- Employees
CREATE TABLE employees (
  id BIGINT PRIMARY KEY,
  user_id BIGINT UNIQUE,
  name VARCHAR(255),
  phone VARCHAR(20),
  role VARCHAR(100),
  status ENUM('active', 'inactive'),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

-- Tasks
CREATE TABLE tasks (
  id BIGINT PRIMARY KEY,
  manager_id BIGINT,
  title VARCHAR(255),
  description TEXT,
  due_date DATE,
  priority ENUM('low', 'medium', 'high'),
  status ENUM('pending', 'in_progress', 'done'),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

-- Task Assignments
CREATE TABLE task_assignments (
  id BIGINT PRIMARY KEY,
  task_id BIGINT,
  employee_id BIGINT,
  assigned_at TIMESTAMP,
  completed_at TIMESTAMP NULL
);

-- Task Updates
CREATE TABLE task_updates (
  id BIGINT PRIMARY KEY,
  task_id BIGINT,
  employee_id BIGINT,
  status ENUM('pending', 'in_progress', 'done'),
  comment TEXT NULL,
  created_at TIMESTAMP
);

-- Task Comments
CREATE TABLE task_comments (
  id BIGINT PRIMARY KEY,
  task_id BIGINT,
  user_id BIGINT,
  content TEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

-- Task Attachments
CREATE TABLE task_attachments (
  id BIGINT PRIMARY KEY,
  task_id BIGINT,
  user_id BIGINT,
  file_path VARCHAR(255),
  file_type ENUM('image', 'document', 'text'),
  created_at TIMESTAMP
);
```

---

## 11. Next Steps

1. **Review & Approve** - Confirm MVP scope and features
2. **Database Setup** - Create migrations for new models
3. **API Development** - Build endpoints in priority order
4. **Frontend Development** - Create Vue components
5. **Testing** - Unit, integration, and feature tests
6. **Deployment** - Deploy to staging and production

---

## 12. Questions & Clarifications

**Ready to proceed with:**
- [ ] Database schema creation
- [ ] API endpoint implementation
- [ ] Vue component scaffolding
- [ ] Testing strategy
- [ ] Deployment plan

---

## Changelog

### Version 2.0 (November 30, 2025)
- Added polished concept overview
- Expanded key tools and features section
- Added design principles table
- Included "Why SMEs Will Use It" section
- Clarified core problem and proposed solution
- Added future phase features (Order Tracking, Finance Tools)
- Enhanced executive summary

### Version 1.0 (November 30, 2025)
- Initial MVP development brief
- Core task management features
- Employee management system
- Technical architecture defined
- Database schema created

---

**Contact:** Kiro Development Team
