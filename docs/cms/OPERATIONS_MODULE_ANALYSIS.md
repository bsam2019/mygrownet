# CMS Operations Module Analysis

**Document Created:** May 4, 2026  
**Status:** Analysis Complete  
**Purpose:** Compare existing CMS operations implementation with the Operations Management Module specification

---

## Executive Summary

The CMS currently has **partial operations functionality** focused on **fabrication/manufacturing workflows** (Production) and **field installation** (Installation). However, it **lacks the unified task management system** described in the specification.

### Key Findings

| Feature | Specification | Current Implementation | Gap |
|---------|--------------|----------------------|-----|
| **Task Management** | Unified task system for all work | ❌ Not implemented | **MAJOR GAP** |
| **Workflows** | Configurable workflow engine | ❌ Not implemented | **MAJOR GAP** |
| **Production Orders** | Part of task system | ✅ Fully implemented | ✅ Complete |
| **Installation Scheduling** | Part of task system | ✅ Fully implemented | ✅ Complete |
| **Task Logs** | Activity tracking | ⚠️ Partial (production tracking only) | **MODERATE GAP** |
| **Checklists** | Reusable task steps | ⚠️ Partial (installation only) | **MODERATE GAP** |
| **Dashboard** | Manager control dashboard | ❌ Not implemented | **MAJOR GAP** |
| **Resource Allocation** | Phase 2 feature | ❌ Not implemented | Expected |

---

## Current Implementation

### 1. Production Management Module

**Purpose:** Manages fabrication/manufacturing workflows for aluminium and glass companies

**Database Tables:**
- `cms_production_orders` - Production work orders
- `cms_cutting_lists` - Material cutting optimization
- `cms_cutting_list_items` - Individual cut specifications
- `cms_production_tracking` - Stage-by-stage progress tracking
- `cms_waste_tracking` - Material waste recording
- `cms_quality_checkpoints` - Quality control points
- `cms_production_materials_usage` - Material consumption tracking
- `cms_cutting_patterns` - Optimization patterns
- `cms_production_schedule` - Production scheduling
- `cms_workshop_capacity` - Capacity planning

**Features:**
✅ Production order creation and management  
✅ Cutting list generation and optimization  
✅ Stage-based tracking (cutting, assembly, finishing, QC, packaging)  
✅ Waste tracking and disposal management  
✅ Quality checkpoints with pass/fail status  
✅ Material usage vs. planned comparison  
✅ Workshop capacity planning  

**Controller:** `ProductionController.php`  
**Service:** `ProductionService.php`  
**Routes:** `/cms/production/*`

---

### 2. Installation Management Module

**Purpose:** Manages field installation scheduling, site visits, and defect tracking

**Database Tables:**
- `cms_installation_schedules` - Installation appointments
- `cms_installation_team_members` - Team assignments
- `cms_site_visits` - Site visit records
- `cms_installation_photos` - Before/during/after photos
- `cms_installation_checklists` - Checklist templates
- `cms_checklist_items` - Checklist item definitions
- `cms_installation_checklist_responses` - Completed checklists
- `cms_customer_signoffs` - Customer acceptance signatures
- `cms_defects` - Defect/snag list
- `cms_defect_photos` - Defect documentation

**Features:**
✅ Installation scheduling with team assignments  
✅ Site visit tracking with arrival/departure times  
✅ Photo documentation (before/during/after)  
✅ Reusable checklists (pre-installation, installation, post-installation, QC)  
✅ Customer sign-off with digital signatures  
✅ Defect tracking with severity levels  
✅ Resolution workflow for defects  

**Controller:** `InstallationController.php`  
**Service:** `InstallationService.php`  
**Routes:** `/cms/installation/*`

---

## Specification Requirements

### Core Entities (from Specification)

#### 1. Tasks ❌ NOT IMPLEMENTED
**Purpose:** Unified system for all work units

**Required Fields:**
- id, company_id, title, description
- type (task, order, job, project_task)
- status (pending, in_progress, blocked, completed)
- priority (low, medium, high)
- assigned_to, created_by
- due_date, started_at, completed_at
- workflow_stage_id, project_id

**Current Status:** Production orders and installation schedules exist but are **separate systems**, not unified tasks.

---

#### 2. Workflows ❌ NOT IMPLEMENTED
**Purpose:** Define process structures

**Required Tables:**
- `workflows` (id, company_id, name, description)
- `workflow_stages` (id, workflow_id, name, sequence_order, requires_approval)

**Current Status:** Production has hardcoded stages (cutting, assembly, finishing, QC, packaging). Installation has hardcoded statuses. **No configurable workflow engine.**

---

#### 3. Task Logs ⚠️ PARTIAL
**Purpose:** Track all task actions

**Required Fields:**
- id, task_id, user_id, action, note, created_at

**Current Status:**
- Production has `cms_production_tracking` (stage-based tracking)
- Installation has site visit records
- **Missing:** Unified activity log for all actions (started, paused, updated, completed, blocked)

---

#### 4. Projects ✅ EXISTS (Construction Module)
**Purpose:** Group related work

**Current Status:** `cms_projects` table exists for construction companies. Can be used for grouping tasks.

---

#### 5. Checklists ⚠️ PARTIAL
**Purpose:** Reusable task steps

**Current Status:**
- Installation has full checklist system (`cms_installation_checklists`, `cms_checklist_items`, `cms_installation_checklist_responses`)
- **Missing:** Checklists for production, general tasks, and other work types

---

#### 6. Resource Allocation ❌ NOT IMPLEMENTED (Phase 2)
**Purpose:** Track employee, vehicle, equipment allocation

**Current Status:** Not implemented. Expected for Phase 2.

---

#### 7. Issues/Blockers ⚠️ PARTIAL
**Purpose:** Track task blockers

**Current Status:**
- Installation has `cms_defects` table (defect tracking)
- Production tracking has status field but no dedicated blocker system
- **Missing:** Unified issue/blocker system for all task types

---

#### 8. Planning & Decision Making ❌ NOT IMPLEMENTED
**Purpose:** Enable managers to plan workload, balance resources, and make data-driven decisions

**Required Features:**
- **Workload Planning:** View upcoming work, capacity vs. demand
- **Task Scheduling:** Drag-and-drop weekly/monthly scheduling
- **Resource Balancing:** See employee workload distribution
- **Capacity Analysis:** Available hours vs. scheduled hours
- **Bottleneck Identification:** Identify workflow stages causing delays
- **Priority Management:** Bulk reassign priorities based on deadlines
- **What-If Scenarios:** Simulate task reassignments before committing
- **Decision Support:** Recommendations based on workload, skills, availability

**Current Status:**
- Production has `cms_workshop_capacity` (capacity tracking by date)
- Production has `cms_production_schedule` (basic scheduling)
- **Missing:** 
  - Interactive planning interface
  - Workload balancing tools
  - Decision support system
  - What-if scenario modeling
  - Bottleneck analysis dashboard
  - Bulk task reassignment
  - Capacity forecasting

---

## System Flow Comparison

### Specification Flow

```
1. Work Creation → Task created with workflow
2. Task Assignment → Assigned to employee
3. Daily Execution → Employee dashboard shows tasks
4. Workflow Enforcement → Stage-by-stage progression
5. Issue Handling → Mark blocked, create issue
6. Manager Dashboard → Metrics, bottlenecks, workload
7. Planning & Decision Making → Workload balancing, capacity analysis, scheduling
8. Manager Actions → Reassign, reprioritize, schedule, balance workload
9. Integration → HR, CRM, Finance, Inventory
```

### Current Implementation Flow

```
Production Flow:
1. Create production order (from job)
2. Generate cutting list
3. Track by stage (cutting → assembly → finishing → QC → packaging)
4. Record material usage and waste
5. Quality checkpoints
6. Complete order

Installation Flow:
1. Schedule installation (from job)
2. Assign team
3. Record site visits
4. Complete checklists
5. Take photos
6. Customer sign-off
7. Track defects
```

**Gap:** No unified task system, no configurable workflows, no centralized dashboard.

---

## Architecture Comparison

### Specification Architecture

```
┌─────────────────────────────────────────┐
│         Operations Dashboard            │
│  (Manager View - All Tasks, Metrics)    │
└─────────────────────────────────────────┘
                    │
        ┌───────────┴───────────┐
        │                       │
┌───────▼────────┐    ┌────────▼────────┐
│  Task System   │    │  Workflow Engine│
│  (Unified)     │◄───┤  (Configurable) │
└───────┬────────┘    └─────────────────┘
        │
        ├─── Production Tasks
        ├─── Installation Tasks
        ├─── General Tasks
        └─── Project Tasks
```

### Current Implementation Architecture

```
┌──────────────────┐    ┌──────────────────┐
│  Production      │    │  Installation    │
│  Module          │    │  Module          │
│  (Separate)      │    │  (Separate)      │
└──────────────────┘    └──────────────────┘
        │                       │
        │                       │
   Production Orders      Installation Schedules
   Cutting Lists          Site Visits
   Tracking               Checklists
   Waste                  Defects
```

**Gap:** Two separate systems instead of unified task management.

---

## Gap Analysis

### MAJOR GAPS (Must Implement)

1. **Unified Task System** ❌
   - No central `tasks` table
   - Production orders and installation schedules are separate
   - Cannot manage general tasks, project tasks, or other work types

2. **Configurable Workflow Engine** ❌
   - Workflows are hardcoded in each module
   - Cannot define custom workflows per company
   - Cannot add/remove stages dynamically

3. **Manager Control Dashboard** ❌
   - No centralized operations dashboard
   - No real-time metrics (active tasks, overdue, completion rate)
   - No bottleneck identification
   - No workload balancing view

4. **Planning & Decision Making System** ❌
   - No interactive planning interface
   - No workload balancing tools
   - No capacity forecasting
   - No what-if scenario modeling
   - No decision support recommendations
   - No bulk task reassignment capabilities

5. **Unified Task Logs** ❌
   - No single activity log for all task actions
   - Cannot track started, paused, updated, blocked events consistently

### MODERATE GAPS (Should Implement)

6. **Reusable Checklists for All Task Types** ⚠️
   - Checklists only exist for installation
   - Need checklists for production, general tasks, maintenance, etc.

7. **Unified Issue/Blocker System** ⚠️
   - Defects exist for installation
   - No blocker system for production or general tasks

8. **Daily Execution Dashboard (Worker View)** ⚠️
   - No "Today's Tasks" dashboard for employees
   - No quick actions (Start, Complete, Block)

### MINOR GAPS (Nice to Have)

9. **Resource Allocation** (Phase 2 feature - expected gap)
10. **Predictive Workload** (Phase 2 feature - expected gap)
11. **Automation Rules** (Phase 2 feature - expected gap)

---

## Integration Points

### Existing Integrations ✅

| Module | Integration | Status |
|--------|-------------|--------|
| **Jobs** | Production orders link to jobs | ✅ Implemented |
| **Jobs** | Installation schedules link to jobs | ✅ Implemented |
| **Materials** | Production tracks material usage | ✅ Implemented |
| **Inventory** | Material consumption updates stock | ✅ Implemented |
| **Users** | Team assignments, tracking | ✅ Implemented |

### Missing Integrations ❌

| Module | Integration | Gap |
|--------|-------------|-----|
| **CRM** | Leads → Tasks | ❌ Not implemented |
| **Finance** | Completed task → Invoice trigger | ❌ Not implemented |
| **Website** | Form submission → Task | ❌ Not implemented |
| **HR** | Employee availability → Task assignment | ❌ Not implemented |

---

## UI Structure Comparison

### Specification UI

**Worker Interface:**
- Today's Tasks
- Task Details
- Quick Actions (Start, Complete, Block)

**Supervisor Interface:**
- Team Task Board (Kanban)
- Task Assignment Panel

**Manager Interface:**
- Dashboard (metrics + alerts)
- Planning View (weekly/monthly scheduling)
- Workload Balancing (capacity vs. demand)
- Bottleneck Analysis
- Decision Support (recommendations)
- What-If Scenarios

### Current Implementation UI

**Production:**
- Production Orders List
- Order Details (with tracking, materials, waste, QC)
- Cutting Lists
- Waste Tracking

**Installation:**
- Installation Schedules List
- Schedule Details (team, visits, photos, checklists, sign-off)
- Defects List

**Missing:**
- Unified task dashboard
- Kanban board
- Manager control dashboard
- Worker daily task view
- Planning & scheduling interface
- Workload balancing tools
- Decision support system

---

## Planning & Decision Making Requirements

The specification emphasizes that managers need tools to **plan workload** and **make data-driven decisions**. This is a critical component that goes beyond just tracking execution.

### Required Planning Features

#### 1. Workload Planning Dashboard
**Purpose:** Visualize upcoming work and capacity

**Features:**
- **Calendar View:** Weekly/monthly view of all scheduled tasks
- **Capacity Indicators:** Available hours vs. scheduled hours per day/week
- **Overload Warnings:** Highlight days/weeks exceeding capacity
- **Drag-and-Drop Scheduling:** Move tasks between dates/employees
- **Gantt Chart:** Timeline view of task dependencies

**Current Status:** ❌ Not implemented
- Production has basic scheduling table
- No visual planning interface
- No capacity warnings

---

#### 2. Workload Balancing
**Purpose:** Distribute work evenly across team members

**Features:**
- **Employee Workload View:** See each employee's task count and hours
- **Workload Distribution Chart:** Visual comparison of team workload
- **Automatic Balancing Suggestions:** System recommends reassignments
- **Skill-Based Matching:** Consider employee skills when suggesting assignments
- **Availability Integration:** Factor in leave, shifts, other commitments

**Current Status:** ❌ Not implemented
- Can assign tasks to users
- No workload comparison tools
- No balancing recommendations

---

#### 3. Bottleneck Analysis
**Purpose:** Identify workflow stages causing delays

**Features:**
- **Stage Duration Analysis:** Average time spent in each workflow stage
- **Bottleneck Identification:** Highlight stages with longest wait times
- **Trend Analysis:** Track bottlenecks over time
- **Root Cause Indicators:** Show why tasks are stuck (waiting for approval, resource unavailable, etc.)
- **Impact Assessment:** Calculate delay impact on downstream tasks

**Current Status:** ⚠️ Partial
- Production tracking shows stage-by-stage progress
- No automated bottleneck detection
- No trend analysis

---

#### 4. Capacity Forecasting
**Purpose:** Predict future capacity needs

**Features:**
- **Demand Forecasting:** Predict incoming work based on historical data
- **Capacity vs. Demand:** Compare available capacity to forecasted demand
- **Hiring Recommendations:** Suggest when to hire based on capacity gaps
- **Equipment Needs:** Identify equipment shortages
- **Scenario Planning:** Model different capacity scenarios

**Current Status:** ⚠️ Partial
- Workshop capacity tracking exists (available workers, hours)
- No forecasting or predictions
- No scenario modeling

---

#### 5. Decision Support System
**Purpose:** Provide data-driven recommendations

**Features:**
- **Priority Recommendations:** Suggest which tasks to prioritize based on deadlines, dependencies, customer importance
- **Assignment Recommendations:** Suggest best employee for each task based on skills, workload, availability
- **Schedule Optimization:** Recommend optimal task sequencing
- **Risk Alerts:** Flag tasks at risk of missing deadlines
- **Performance Insights:** Show which decisions led to better outcomes

**Current Status:** ❌ Not implemented
- Managers make all decisions manually
- No system recommendations
- No risk alerts

---

#### 6. What-If Scenarios
**Purpose:** Test decisions before committing

**Features:**
- **Scenario Modeling:** Create "what-if" scenarios (e.g., "What if we reassign 5 tasks to John?")
- **Impact Preview:** Show how changes affect deadlines, workload, capacity
- **Comparison View:** Compare multiple scenarios side-by-side
- **Rollback:** Easily undo scenario changes
- **Save Scenarios:** Save scenarios for future reference

**Current Status:** ❌ Not implemented
- All changes are immediate
- No preview or simulation
- No rollback capability

---

#### 7. Bulk Operations
**Purpose:** Make changes to multiple tasks efficiently

**Features:**
- **Bulk Reassignment:** Reassign multiple tasks at once
- **Bulk Priority Change:** Change priority for multiple tasks
- **Bulk Rescheduling:** Move multiple tasks to new dates
- **Bulk Status Update:** Mark multiple tasks as completed/blocked
- **Filters & Selection:** Select tasks by criteria (overdue, assigned to X, priority high, etc.)

**Current Status:** ❌ Not implemented
- All changes are one-by-one
- No bulk operations

---

### Planning UI Components

#### Manager Planning Dashboard (Proposed)

```
┌─────────────────────────────────────────────────────────────┐
│  Operations Planning Dashboard                              │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  📊 Capacity Overview                                        │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  This Week: 85% utilized (340/400 hours)             │  │
│  │  Next Week: 110% overbooked ⚠️ (440/400 hours)       │  │
│  │  [View Details] [Balance Workload]                   │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                              │
│  🚨 Alerts & Recommendations                                 │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  • 8 tasks at risk of missing deadline                │  │
│  │  • Bottleneck detected: Quality Check stage          │  │
│  │  • John is overloaded (60 hours scheduled)           │  │
│  │  • Recommendation: Reassign 3 tasks to Sarah         │  │
│  │  [Take Action]                                        │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                              │
│  📅 Weekly Schedule (Drag to reschedule)                     │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  Mon  Tue  Wed  Thu  Fri  Sat  Sun                   │  │
│  │  ─────────────────────────────────────────────────   │  │
│  │  John   [Task 1] [Task 2]      [Task 3]              │  │
│  │  Sarah  [Task 4] [Task 5] [Task 6]                   │  │
│  │  Mike   [Task 7]           [Task 8] [Task 9]         │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                              │
│  👥 Team Workload Distribution                               │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  John:  ████████████████░░░░ 80% (32h)               │  │
│  │  Sarah: ██████████░░░░░░░░░░ 50% (20h)               │  │
│  │  Mike:  ████████████████████ 100% (40h) ⚠️           │  │
│  │  [Balance Workload]                                   │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                              │
│  🔍 Bottleneck Analysis                                      │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  Workflow Stage        Avg Time    Tasks Stuck       │  │
│  │  ─────────────────────────────────────────────────   │  │
│  │  Cutting               2.5 days    3                 │  │
│  │  Assembly              3.0 days    5                 │  │
│  │  Quality Check ⚠️      5.2 days    12 ← Bottleneck   │  │
│  │  Packaging             1.5 days    2                 │  │
│  │  [View Details]                                       │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                              │
│  🎯 What-If Scenarios                                        │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  Scenario: Hire 1 QC Inspector                       │  │
│  │  Impact: -30% QC bottleneck, +15% capacity           │  │
│  │  [Run Scenario] [Compare Scenarios]                  │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

---

### Database Schema for Planning

**Additional Tables Needed:**

```sql
-- Capacity Planning
cms_capacity_forecasts (
    id, company_id, forecast_date, 
    forecasted_demand_hours, available_capacity_hours,
    capacity_gap, recommendations, created_at
)

-- Decision Support
cms_task_recommendations (
    id, task_id, recommendation_type, 
    recommended_action, confidence_score, 
    reasoning, status, created_at
)

-- Scenarios
cms_planning_scenarios (
    id, company_id, scenario_name, description,
    changes_json, impact_analysis_json,
    created_by, created_at
)

-- Bottleneck Tracking
cms_workflow_bottlenecks (
    id, company_id, workflow_id, stage_id,
    detection_date, avg_duration_days, tasks_affected,
    severity, status, resolved_at
)

-- Workload Snapshots
cms_workload_snapshots (
    id, company_id, snapshot_date,
    user_id, assigned_tasks_count, total_hours,
    utilization_percentage, created_at
)
```

---

## Recommendations

### Phase 1: MVP (Unified Task System)

**Goal:** Create unified task management while preserving existing production and installation functionality

**Implementation Steps:**

1. **Create Core Tables**
   ```sql
   - tasks (unified task table)
   - workflows (workflow definitions)
   - workflow_stages (stage definitions)
   - task_logs (activity tracking)
   - task_checklists (link tasks to checklists)
   - task_issues (blockers/issues)
   ```

2. **Migrate Existing Systems**
   - Keep `cms_production_orders` and `cms_installation_schedules` as-is
   - Add `task_id` foreign key to both tables
   - Create tasks automatically when production orders or installations are created
   - Sync status changes bidirectionally

3. **Build Core UI**
   - Worker Dashboard (Today's Tasks)
   - Task Details Page (unified view)
   - Manager Dashboard (metrics, bottlenecks)
   - Simple Kanban Board
   - **Planning Dashboard (workload, capacity, scheduling)**
   - **Workload Balancing Interface**

4. **Implement Workflow Engine**
   - Seed default workflows (Production, Installation, General Task)
   - Allow companies to customize workflows
   - Enforce stage progression rules

5. **Implement Planning & Decision Support**
   - Capacity forecasting
   - Bottleneck detection
   - Workload balancing recommendations
   - What-if scenario modeling
   - Bulk operations

**Estimated Effort:** 8-10 days

---

### Phase 2: Enhanced Features

**Goal:** Add resource allocation, advanced analytics, automation

**Features:**
- Resource allocation (employees, vehicles, equipment)
- Predictive workload analysis (ML-based forecasting)
- Automation rules (if X then create task)
- Mobile offline mode
- Advanced reporting
- **AI-powered decision recommendations**
- **Automated bottleneck resolution suggestions**

**Estimated Effort:** 10-12 days

---

### Phase 3: Full Integration

**Goal:** Connect operations to all CMS modules

**Integrations:**
- CRM → Tasks (lead conversion)
- Finance → Tasks (invoice triggers)
- Website → Tasks (form submissions)
- HR → Tasks (employee availability)
- Inventory → Tasks (stock triggers)

**Estimated Effort:** 8-10 days

---

## Migration Strategy

### Option A: Gradual Migration (Recommended)

**Approach:** Build unified system alongside existing modules

**Pros:**
- No disruption to current users
- Can test thoroughly before switching
- Fallback option if issues arise

**Cons:**
- Temporary data duplication
- More complex codebase during transition

**Timeline:** 3-4 weeks

---

### Option B: Big Bang Migration

**Approach:** Replace existing systems entirely

**Pros:**
- Clean architecture from day one
- No duplicate code

**Cons:**
- High risk of breaking existing functionality
- Requires extensive testing
- No fallback option

**Timeline:** 2-3 weeks (but higher risk)

---

## Conclusion

The CMS has **strong domain-specific operations modules** (Production and Installation) but **lacks the unified task management system** described in the specification.

**Current State:**
- ✅ Production management (fabrication workflows)
- ✅ Installation management (field operations)
- ❌ Unified task system
- ❌ Configurable workflows
- ❌ Manager control dashboard
- ❌ Planning & decision support system

**Recommended Path Forward:**
1. Implement unified task system (Phase 1 - 8-10 days)
2. Migrate existing modules to use tasks (Phase 1 - included)
3. Build manager dashboard with planning tools (Phase 1 - included)
4. Add resource allocation and automation (Phase 2 - 10-12 days)
5. Full CMS integration (Phase 3 - 8-10 days)

**Total Estimated Effort:** 26-32 days for complete implementation

---

## Appendix: Database Schema Comparison

### Specification Schema

```sql
tasks (id, company_id, title, description, type, status, priority, assigned_to, created_by, due_date, started_at, completed_at, workflow_stage_id, project_id)
workflows (id, company_id, name, description)
workflow_stages (id, workflow_id, name, sequence_order, requires_approval)
task_logs (id, task_id, user_id, action, note, created_at)
projects (id, company_id, name, description, start_date, end_date, status)
checklists (id, company_id, name)
checklist_items (id, checklist_id, label, is_required)
task_checklist (task_id, checklist_item_id, completed)
resource_allocation (id, company_id, resource_type, resource_id, task_id, start_time, end_time)
issues (id, task_id, reported_by, description, status)
```

### Current Implementation Schema

```sql
-- Production Module
cms_production_orders (id, company_id, job_id, order_number, order_date, required_date, status, priority, assigned_to, notes, estimated_hours, actual_hours)
cms_cutting_lists (id, company_id, production_order_id, list_number, generated_date, status, total_length_required, total_length_used, waste_percentage, optimized, notes)
cms_cutting_list_items (id, cutting_list_id, material_id, item_code, description, required_length, quantity, total_length, stock_length, pieces_per_stock, waste_per_stock, sort_order, cut, cut_at, cut_by, notes)
cms_production_tracking (id, production_order_id, user_id, stage, status, started_at, completed_at, hours_spent, quantity_completed, quantity_rejected, notes)
cms_waste_tracking (id, company_id, production_order_id, material_id, waste_date, waste_type, quantity, unit, value, disposal_method, reason, recorded_by)
cms_quality_checkpoints (id, production_order_id, checkpoint_name, stage, status, inspector_id, inspected_at, findings, corrective_action, requires_rework)
cms_production_materials_usage (id, production_order_id, material_id, planned_quantity, actual_quantity, variance, unit, unit_cost, total_cost, notes)
cms_cutting_patterns (id, company_id, material_id, pattern_name, stock_length, cuts, total_used, waste, efficiency_percentage, usage_count, is_template)
cms_production_schedule (id, company_id, production_order_id, scheduled_date, start_time, end_time, assigned_worker_id, workstation, status, notes)
cms_workshop_capacity (id, company_id, date, available_workers, available_hours, scheduled_hours, actual_hours, utilization_percentage, is_working_day, notes)

-- Installation Module
cms_installation_schedules (id, company_id, job_id, project_id, schedule_number, scheduled_date, start_time, end_time, status, team_leader_id, site_address, site_contact_name, site_contact_phone, special_instructions, equipment_required, materials_required, estimated_hours, actual_hours)
cms_installation_team_members (id, installation_schedule_id, user_id, role)
cms_site_visits (id, company_id, installation_schedule_id, job_id, visit_number, visit_date, arrival_time, departure_time, visit_type, status, visited_by, purpose, findings, work_performed, issues_encountered, next_steps)
cms_installation_photos (id, site_visit_id, photo_type, file_path, file_name, mime_type, file_size, caption, sort_order)
cms_installation_checklists (id, company_id, checklist_name, description, checklist_type, is_template, is_active)
cms_checklist_items (id, checklist_id, item_text, description, is_required, sort_order)
cms_installation_checklist_responses (id, site_visit_id, checklist_id, checklist_item_id, status, notes, checked_by, checked_at)
cms_customer_signoffs (id, site_visit_id, customer_name, customer_email, customer_phone, signature_data, signed_at, comments, satisfaction_rating, feedback)
cms_defects (id, company_id, job_id, site_visit_id, defect_number, title, description, severity, status, identified_date, target_resolution_date, actual_resolution_date, identified_by, assigned_to, resolution_notes, resolved_by)
cms_defect_photos (id, defect_id, file_path, file_name, caption)
```

---

**Document End**
