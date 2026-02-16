# CMS Payroll & HR Management System - Implementation Documentation

**Last Updated:** February 15, 2026  
**Status:** ✅ Core Features Implemented | 🟡 Enhancements Planned  
**Priority:** 🔴 P1 - Active Development  
**Current Phase:** Production (Core) | Planning (Enhancements)

---

## Document Purpose

This document serves as comprehensive documentation for the CMS HRMS, covering:
1. **Currently Implemented Features** - Production-ready payroll and worker management
2. **Planned Enhancements** - Future HR features for full employee lifecycle management
3. **Implementation Roadmap** - Phased approach for expanding capabilities

---

## Changelog

### February 15, 2026 - Phase 5 Implementation Complete ✅
- ✅ Created 1 migration for performance management tables (8 tables total)
- ✅ Created 8 models: PerformanceCycleModel, PerformanceReviewModel, PerformanceCriteriaModel, PerformanceRatingModel, GoalModel, GoalProgressModel, ImprovementPlanModel, PipMilestoneModel
- ✅ Implemented PerformanceManagementService with review cycles, goal tracking, and PIP management
- ✅ Created PerformanceController with full CRUD operations
- ✅ Created DefaultPerformanceCriteriaSeeder with 8 standard criteria
- ✅ Added performance management routes (cycles, reviews, goals, PIPs)
- ✅ Created Goals.vue page with progress tracking
- ✅ Updated navigation with Performance menu item
- Status: Phase 5 Performance Management - COMPLETE ✅
- Ready for deployment: Run migrations and seeders

### February 15, 2026 - Phase 4 Implementation Complete ✅
- ✅ Created 2 migrations for recruitment and onboarding tables
- ✅ Created 10 models: JobPostingModel, JobApplicationModel, InterviewModel, InterviewEvaluationModel, OfferLetterModel, OnboardingTemplateModel, OnboardingTaskModel, EmployeeOnboardingModel, OnboardingTaskProgressModel
- ✅ Implemented RecruitmentService with job posting, application tracking, interview scheduling
- ✅ Implemented OnboardingService with template management and progress tracking
- ✅ Created RecruitmentController and OnboardingController
- ✅ Created DefaultOnboardingTemplatesSeeder with 10 standard tasks
- ✅ Added recruitment and onboarding routes
- ✅ Created JobPostings.vue, CreateJobPosting.vue, and Templates.vue pages
- ✅ Updated navigation with Recruitment and Onboarding menu items
- Status: Phase 4 Recruitment & Onboarding - COMPLETE ✅
- Ready for deployment: Run migrations and seeders

### February 15, 2026 - Phase 3 Implementation Complete ✅
- ✅ Created 2 migrations for enhanced payroll (allowances, deductions, statutory fields)
- ✅ Created 5 models: AllowanceTypeModel, WorkerAllowanceModel, DeductionTypeModel, WorkerDeductionModel, PayrollItemDetailModel
- ✅ Implemented ZambianTaxCalculator service with PAYE, NAPSA, NHIMA calculations
- ✅ Implemented EnhancedPayrollService for comprehensive payroll processing
- ✅ Created PayrollConfigurationController for managing allowances/deductions
- ✅ Created DefaultAllowanceDeductionTypesSeeder with Zambian defaults
- ✅ Added payroll configuration routes
- ✅ Created AllowanceTypes.vue and DeductionTypes.vue pages
- ✅ Updated navigation with Payroll Configuration menu
- Status: Phase 3 Enhanced Payroll - COMPLETE ✅
- Ready for deployment: Run migrations and seeders

### February 15, 2026 - Phase 2 Deployment Complete ✅
- ✅ All 4 Phase 2 migrations executed successfully
- ✅ Fixed index name length issues and column existence checks
- ✅ Frontend built successfully (3m 33s)
- ✅ All caches cleared
- ✅ Fixed missing Controller imports in all Phase 2 controllers
- Status: Phase 2 Attendance & Time Tracking - DEPLOYED ✅ 🚀
- Production ready and fully operational

### February 14, 2026 - Phase 2 Implementation 100% Complete ✅
- Created 7 Vue pages for Phase 2: Shifts (Index/Create/Edit), Attendance (ClockInOut/Index/Summary), Overtime (Index)
- Created DefaultShiftsSeeder for seeding default shifts (Morning, Evening, Night)
- Updated CMSLayoutNew.vue navigation with Phase 2 menu items (Shifts, Attendance, Overtime)
- All Phase 2 features implemented: shift management, attendance tracking, overtime calculation
- Frontend and backend fully integrated and production-ready

### February 14, 2026 - Phase 2 Backend Implementation Complete
- Created 4 migrations for shifts, attendance, overtime, and worker/company enhancements
- Created 4 models: ShiftModel, WorkerShiftModel, AttendanceRecordModel, OvertimeRecordModel
- Implemented 3 services: ShiftManagementService, AttendanceService, OvertimeService
- Created 3 controllers: ShiftController, AttendanceController, OvertimeController
- Added Phase 2 routes to routes/cms.php
- Status: Phase 2 Backend - 100% complete, ready for frontend

### February 14, 2026 - Phase 1 Implementation 100% Complete ✅
- Created InitializeLeaveBalances Artisan command for bulk leave balance initialization
- Created comprehensive HRMS_TESTING_GUIDE.md with 20+ test scenarios
- Updated HRMS_IMPLEMENTATION_STATUS.md to 100% complete
- All Phase 1 features implemented and production-ready
- Status: Phase 1 Core HR Enhancement - COMPLETE
- Ready for deployment and testing

### February 14, 2026 - Phase 1 Implementation 95% Complete
- Updated Workers/Index.vue with department and job title columns
- Updated Workers/Show.vue with comprehensive HR information display (personal, employment, emergency contact, compensation, tax)
- Updated PayrollController to load department relationships and handle all new worker fields
- Created PublicHolidayModel with helper methods for holiday checking
- Added HR Management navigation section to CMSLayoutNew with Departments and Leave Management menu items
- Status: Phase 1 Core HR Enhancement - 95% complete
- Ready for deployment: Run migrations, seeders, and build frontend assets

### February 14, 2026 - Phase 1 Frontend Pages Complete
- Created 7 Vue pages: Leave Index/Create/Show/Balance, Departments Index/Create/Edit
- Updated Workers/Create.vue with all new HR fields (personal info, emergency contact, employment details, compensation, tax & benefits)
- Updated DepartmentController with proper data passing for create/edit forms
- Enhanced form validation and field mapping in DepartmentController
- Status: Phase 1 Core HR Enhancement - 75% complete
- Next: Update Workers/Show.vue, create PublicHolidayModel, add navigation menu items

### February 14, 2026 - Phase 1 Implementation Started
- Created 3 database migrations (departments, branches, enhanced workers, leave management)
- Created 5 Eloquent models (Branch, Department, LeaveType, LeaveBalance, LeaveRequest)
- Implemented LeaveManagementService with full workflow logic
- Created 2 controllers (LeaveController, DepartmentController)
- Added HRMS routes to routes/cms.php
- Created Leave Index Vue page
- Created 2 seeders (DefaultLeaveTypes, ZambianPublicHolidays)
- Updated WorkerModel with new HRMS fields and relationships
- Status: Phase 1 Core HR Enhancement - 40% complete

### February 14, 2026 - Documentation Update
- Restructured document to clearly separate implemented features from planned enhancements
- Added comprehensive "Current Implementation Status" section documenting all existing features
- Updated executive summary to reflect actual production status
- Clarified that core payroll features are operational
- Maintained all planning sections for future enhancements
- Added implementation summary table showing 7 tables, 2 services, 2 controllers, 7 pages

### February 13, 2026
- Added implementation status summary at end of document
- Revised cost estimates from $80K-$120K to $50K-$75K based on existing foundation
- Updated timeline from 16 weeks to 10 weeks
- Added integration strategy section emphasizing "enhance, don't replace" approach

---

## Executive Summary

The CMS platform currently has a **functional Workers Management and Payroll System** in production. This system handles worker records, attendance tracking, time tracking, commission management, and payroll processing.

**Current Capabilities (✅ Implemented):**
- Worker/Employee records management
- Attendance tracking with clock in/out
- Time tracking and timesheet management
- Commission calculations and tracking
- Payroll run processing and payslip generation

**Planned Enhancements (🔨 In Planning):**
- Enhanced employee profiles with documents
- Leave management system
- Recruitment and onboarding workflows
- Zambian tax compliance (PAYE, NAPSA, NHIMA)
- Performance management and reviews
- Training and development tracking
- Employee self-service portal

**Implementation Strategy:** Enhance existing system incrementally without breaking changes

---

## CURRENT IMPLEMENTATION STATUS

### ✅ IMPLEMENTED FEATURES (Production Ready)

#### 1. Worker/Employee Management
**Status:** ✅ Fully Operational  
**Database:** `cms_workers` table  
**Service:** `PayrollService`  
**Controller:** `PayrollController`  
**Pages:** 
- `resources/js/Pages/CMS/Workers/Index.vue` - Worker listing
- `resources/js/Pages/CMS/Workers/Create.vue` - Add new worker
- `resources/js/Pages/CMS/Workers/Show.vue` - Worker details

**Current Fields:**
- Basic information (name, email, phone, address)
- Employment details (job title, department, hire date)
- Salary information (hourly rate, salary type)
- Status tracking (active/inactive)

**Capabilities:**
- Create, read, update, delete workers
- Track employment status
- Manage salary information
- Link to attendance and payroll

---

#### 2. Attendance Tracking
**Status:** ✅ Fully Operational  
**Database:** `cms_worker_attendance` table  
**Service:** `PayrollService`  
**Controller:** `PayrollController`

**Features:**
- Clock in/out functionality
- Date and time tracking
- Status tracking (present, absent, late, half-day)
- Notes and remarks
- Linked to payroll calculations

**Current Schema:**
```sql
cms_worker_attendance:
- id, company_id, worker_id
- date, clock_in, clock_out
- status (present, absent, late, half_day)
- hours_worked
- notes
- timestamps
```

---

#### 3. Time Tracking System
**Status:** ✅ Fully Operational  
**Database:** `cms_time_entries`, `cms_timesheets` tables  
**Service:** `TimeTrackingService`  
**Controller:** `TimeTrackingController`  
**Pages:** `resources/js/Pages/CMS/TimeTracking/Index.vue`  
**Documentation:** `docs/cms/TIME_TRACKING.md`

**Features:**
- Time entry logging (start/end times)
- Project/task association
- Timesheet generation and approval
- Billable vs non-billable hours
- Overtime tracking
- Weekly/monthly summaries

**Current Schema:**
```sql
cms_time_entries:
- id, company_id, worker_id, job_id
- start_time, end_time, duration
- description, is_billable
- status (draft, submitted, approved, rejected)

cms_timesheets:
- id, company_id, worker_id
- period_start, period_end
- total_hours, billable_hours
- status, submitted_at, approved_at
```

---

#### 4. Commission Management
**Status:** ✅ Fully Operational  
**Database:** `cms_commissions` table  
**Service:** `PayrollService`  
**Controller:** `PayrollController`

**Features:**
- Commission calculation and tracking
- Link to jobs/invoices
- Multiple commission types
- Automatic payroll integration

**Current Schema:**
```sql
cms_commissions:
- id, company_id, worker_id
- job_id, invoice_id
- commission_type, rate, amount
- status (pending, approved, paid)
- paid_at, payroll_run_id
```

---

#### 5. Payroll Processing
**Status:** ✅ Fully Operational  
**Database:** `cms_payroll_runs`, `cms_payroll_items` tables  
**Service:** `PayrollService`  
**Controller:** `PayrollController`  
**Pages:**
- `resources/js/Pages/CMS/Payroll/Index.vue` - Payroll runs list
- `resources/js/Pages/CMS/Payroll/Create.vue` - Create payroll run
- `resources/js/Pages/CMS/Payroll/Show.vue` - Payroll run details  
**Documentation:** `docs/cms/PAYROLL_SYSTEM.md`

**Features:**
- Payroll run creation and processing
- Individual payroll items per worker
- Gross pay calculation
- Basic deductions
- Net pay calculation
- Payslip generation
- Payment status tracking

**Current Schema:**
```sql
cms_payroll_runs:
- id, company_id
- period_start, period_end
- total_gross, total_deductions, total_net
- status (draft, processing, completed, paid)
- processed_at, paid_at

cms_payroll_items:
- id, payroll_run_id, worker_id
- gross_pay, deductions, net_pay
- hours_worked, overtime_hours
- commissions_total
- payment_status, payment_date
```

---

### 📊 IMPLEMENTATION SUMMARY

| Feature | Tables | Services | Controllers | Pages | Status |
|---------|--------|----------|-------------|-------|--------|
| Worker Management | cms_workers | PayrollService | PayrollController | 3 pages | ✅ Complete |
| Attendance Tracking | cms_worker_attendance | PayrollService | PayrollController | - | ✅ Complete |
| Time Tracking | cms_time_entries, cms_timesheets | TimeTrackingService | TimeTrackingController | 1 page | ✅ Complete |
| Commission Management | cms_commissions | PayrollService | PayrollController | - | ✅ Complete |
| Payroll Processing | cms_payroll_runs, cms_payroll_items | PayrollService | PayrollController | 3 pages | ✅ Complete |

**Total Implemented:**
- 7 database tables
- 2 services (PayrollService, TimeTrackingService)
- 2 controllers (PayrollController, TimeTrackingController)
- 7 Vue pages
- 2 documentation files

---

## PLANNED ENHANCEMENTS

The following sections outline planned enhancements to expand the current system into a comprehensive HRMS. These are organized by priority and implementation phase.

---

## ENHANCEMENT ROADMAP

### Module Structure (Planned):
1. **Core HR Enhancement** (Mandatory) - 4 weeks (reduced from 6)
2. **Payroll Enhancement** (High Priority) - 3 weeks (reduced from 4)  
3. **Performance** (Medium Priority) - 2 weeks (reduced from 3)
4. **Advanced HR** (Optional) - 1 week (reduced from 3)

**Total Features:** 20 major modules, 150+ sub-features

---

## ✅ EXISTING IMPLEMENTATION (Production Ready)

The CMS platform already has these HR/Payroll features implemented:

### 1. Worker Records ✅
- Worker registration with auto-generated numbers (WKR-0001)
- Personal information: name, phone, email, ID number
- Worker types: Casual, Contract, Permanent
- Payment methods: Cash, Mobile Money, Bank Transfer
- Status tracking: Active/Inactive
- Rate configuration: hourly_rate, daily_rate, commission_rate
- **Table:** `cms_workers`
- **Model:** `WorkerModel`
- **Service:** `PayrollService::createWorker()`
- **Controller:** `PayrollController::workersIndex/Create/Store/Show()`
- **Pages:** `CMS/Workers/Index.vue`, `Create.vue`, `Show.vue`

### 2. Attendance Tracking ✅
- Work date recording
- Hours worked / days worked tracking
- Automatic earnings calculation
- Job linkage (optional)
- Approval workflow (Pending → Approved → Paid)
- **Table:** `cms_worker_attendance`
- **Model:** `WorkerAttendanceModel`
- **Service:** `PayrollService::recordAttendance()`, `approveAttendance()`
- **Controller:** `PayrollController::attendanceStore/Approve()`

### 3. Time Tracking System ✅
- Start/stop timer functionality
- Manual time entry
- Billable/non-billable hours
- Timesheet generation (weekly/biweekly/monthly)
- Approval workflow (Draft → Submitted → Approved/Rejected)
- Payroll integration
- **Tables:** `cms_time_entries`, `cms_timesheets`
- **Models:** `TimeEntryModel`, `TimesheetModel`
- **Service:** `TimeTrackingService` (complete)
- **Controller:** `TimeTrackingController`
- **Pages:** `CMS/TimeTracking/Index.vue`
- **Documentation:** `docs/cms/TIME_TRACKING.md`

### 4. Commission Management ✅
- Multiple commission types (sales, referral, performance, other)
- Percentage-based calculation
- Job/invoice linkage
- Approval workflow
- **Table:** `cms_commissions`
- **Model:** `CommissionModel`
- **Service:** `PayrollService::calculateCommission()`, `approveCommission()`
- **Controller:** `PayrollController::commissionStore/Approve()`

### 5. Payroll Processing ✅
- Payroll run creation with auto-generated numbers (PAY-2026-001)
- Period types: Weekly, Bi-Weekly, Monthly
- Automatic aggregation of approved attendance and commissions
- Payroll items per worker
- Status workflow: Draft → Approved → Paid
- Basic wage calculation
- **Tables:** `cms_payroll_runs`, `cms_payroll_items`
- **Models:** `PayrollRunModel`, `PayrollItemModel`
- **Service:** `PayrollService::createPayrollRun()`, `approvePayrollRun()`, `markPayrollAsPaid()`
- **Controller:** `PayrollController::payrollIndex/Create/Store/Show/Approve/MarkPaid()`
- **Pages:** `CMS/Payroll/Index.vue`, `Create.vue`, `Show.vue`
- **Documentation:** `docs/cms/PAYROLL_SYSTEM.md`

### Existing Database Tables
- `cms_workers` - Worker registration and details
- `cms_worker_attendance` - Attendance records with earnings
- `cms_commissions` - Commission calculations
- `cms_payroll_runs` - Payroll period management
- `cms_payroll_items` - Individual payment records per worker
- `cms_time_entries` - Time tracking entries
- `cms_timesheets` - Timesheet grouping

### Existing Documentation
- `docs/cms/PAYROLL_SYSTEM.md` - Complete payroll system documentation
- `docs/cms/TIME_TRACKING.md` - Time tracking system documentation

---

## 🔨 WHAT NEEDS TO BE ADDED

The existing "Workers" system provides a solid foundation. We need to enhance it to become a full HRMS by adding:

---

## TIER 1: CORE HR ENHANCEMENT (MANDATORY) - 4 Weeks

**Note:** We already have `cms_workers` table with basic information. We'll enhance it rather than create a new `cms_employees` table to avoid duplication.

### 1. Employee Records Enhancement (Week 1-2)

**Strategy:** Extend existing `cms_workers` table with additional HR fields

#### 1.1 Database Migration - Enhance Workers Table
**New Migration:** `2026_02_14_100000_enhance_cms_workers_for_hrms.php`

```sql
-- Add to existing cms_workers table
ALTER TABLE cms_workers ADD COLUMN (
    -- Enhanced Personal Information
    first_name VARCHAR(100),
    middle_name VARCHAR(100),
    last_name VARCHAR(100),
    nrc_number VARCHAR(50) UNIQUE,
    passport_number VARCHAR(50),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    marital_status ENUM('single', 'married', 'divorced', 'widowed'),
    nationality VARCHAR(100) DEFAULT 'Zambian',
    
    -- Contact Details (already have phone, email)
    personal_email VARCHAR(255),
    emergency_contact_name VARCHAR(255),
    emergency_contact_phone VARCHAR(50),
    emergency_contact_relationship VARCHAR(100),
    residential_address TEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    postal_code VARCHAR(20),
    
    -- Employment Details
    job_title VARCHAR(255),
    department VARCHAR(255),
    reporting_manager_id BIGINT UNSIGNED,
    employment_type ENUM('permanent', 'contract', 'intern', 'casual') DEFAULT 'casual',
    start_date DATE,
    confirmation_date DATE,
    probation_end_date DATE,
    work_location VARCHAR(255),
    branch VARCHAR(255),
    
    -- Contract Details
    contract_start_date DATE,
    contract_end_date DATE,
    notice_period_days INT DEFAULT 30,
    
    -- Salary Information (enhance existing rates)
    basic_salary DECIMAL(15,2) DEFAULT 0,
    currency VARCHAR(10) DEFAULT 'ZMW',
    salary_payment_frequency ENUM('monthly', 'bi-weekly', 'weekly') DEFAULT 'monthly',
    
    -- Bank Details (already have bank_name, bank_account_number)
    bank_branch VARCHAR(255),
    bank_swift_code VARCHAR(50),
    
    -- Tax Information
    tpin_number VARCHAR(50) UNIQUE,
    napsa_number VARCHAR(50),
    nhima_number VARCHAR(50),
    
    -- System Fields
    is_active BOOLEAN DEFAULT TRUE,
    termination_date DATE,
    termination_reason TEXT,
    rehire_eligible BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (reporting_manager_id) REFERENCES cms_workers(id) ON DELETE SET NULL
);

-- Create indexes
CREATE INDEX idx_workers_department ON cms_workers(company_id, department);
CREATE INDEX idx_workers_employment_type ON cms_workers(company_id, employment_type);
CREATE INDEX idx_workers_manager ON cms_workers(reporting_manager_id);
```
    alternative_phone VARCHAR(50),
    residential_address TEXT NOT NULL,
    postal_address TEXT,
    
    -- Next of Kin
    next_of_kin_name VARCHAR(255),

#### 1.2 Document Management (NEW)
**New Table:** `cms_employee_documents`

```sql
CREATE TABLE cms_employee_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    document_type ENUM(
        'contract', 'nrc', 'passport', 'certificate', 'cv', 
        'tax_document', 'policy_acknowledgment', 'warning_letter',
        'performance_review', 'other'
    ) NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    expiry_date DATE,
    uploaded_by BIGINT UNSIGNED,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    
    FOREIGN KEY (worker_id) REFERENCES cms_workers(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    INDEX idx_worker_type (worker_id, document_type)
);
```

**Implementation Files:**
- `database/migrations/2026_02_14_100001_create_cms_employee_documents.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/EmployeeDocumentModel.php`
- Enhance `WorkerModel` with `documents()` relationship
- Enhance `PayrollService` with document management methods
- Update `resources/js/Pages/CMS/Workers/Show.vue` to display documents
- Add document upload modal component

---

### 2. Leave Management (NEW) - Week 2

**Status:** ❌ Not Implemented - Needs to be built

#### 2.1 Leave Types & Policies
```sql
CREATE TABLE cms_employee_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    document_type ENUM(
        'contract', 'nrc', 'passport', 'certificate', 'cv', 
        'tax_document', 'policy_acknowledgment', 'warning_letter',
        'performance_review', 'other'
    ) NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    expiry_date DATE,
    uploaded_by BIGINT UNSIGNED,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE,
    INDEX idx_employee_type (employee_id, document_type)
);
```

**Implementation Files:**
- `database/migrations/YYYY_MM_DD_create_cms_employees_tables.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/EmployeeModel.php`
- `app/Domain/CMS/Core/Services/EmployeeService.php`
- `app/Http/Controllers/CMS/EmployeeController.php`
- `resources/js/Pages/CMS/Employees/Index.vue`
- `resources/js/Pages/CMS/Employees/Create.vue`
- `resources/js/Pages/CMS/Employees/Show.vue`
- `resources/js/Pages/CMS/Employees/Edit.vue`

---

### 2. Recruitment & Onboarding (Week 3)

#### 2.1 Recruitment System
```sql
CREATE TABLE cms_job_postings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    job_title VARCHAR(255) NOT NULL,
    department_id BIGINT UNSIGNED,
    job_description TEXT NOT NULL,
    requirements TEXT,
    salary_range_min DECIMAL(15,2),
    salary_range_max DECIMAL(15,2),
    positions_available INT DEFAULT 1,
    application_deadline DATE,
    status ENUM('draft', 'published', 'closed') DEFAULT 'draft',
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_job_applications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_posting_id BIGINT UNSIGNED NOT NULL,
    applicant_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    cv_path VARCHAR(500) NOT NULL,
    cover_letter TEXT,
    application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('new', 'screening', 'interview', 'offer', 'rejected', 'hired') DEFAULT 'new',
    
    FOREIGN KEY (job_posting_id) REFERENCES cms_job_postings(id) ON DELETE CASCADE,
    INDEX idx_status (status)
);

CREATE TABLE cms_interviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id BIGINT UNSIGNED NOT NULL,
    interview_type ENUM('phone', 'video', 'in_person', 'technical', 'final') NOT NULL,
    scheduled_date DATETIME NOT NULL,
    location VARCHAR(255),
    meeting_link VARCHAR(500),
    interviewer_ids TEXT,
    status ENUM('scheduled', 'completed', 'cancelled', 'rescheduled') DEFAULT 'scheduled',
    
    FOREIGN KEY (application_id) REFERENCES cms_job_applications(id) ON DELETE CASCADE
);

CREATE TABLE cms_interview_evaluations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    interview_id BIGINT UNSIGNED NOT NULL,
    evaluator_id BIGINT UNSIGNED NOT NULL,
    technical_skills_rating INT,
    communication_rating INT,
    cultural_fit_rating INT,
    overall_rating INT,
    comments TEXT,
    recommendation ENUM('strong_yes', 'yes', 'maybe', 'no', 'strong_no'),
    
    FOREIGN KEY (interview_id) REFERENCES cms_interviews(id) ON DELETE CASCADE
);

CREATE TABLE cms_offer_letters (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id BIGINT UNSIGNED NOT NULL,
    job_title VARCHAR(255) NOT NULL,
    salary DECIMAL(15,2) NOT NULL,
    start_date DATE NOT NULL,
    offer_letter_path VARCHAR(500),
    sent_date DATE,
    response_deadline DATE,
    status ENUM('draft', 'sent', 'accepted', 'rejected', 'expired') DEFAULT 'draft',
    
    FOREIGN KEY (application_id) REFERENCES cms_job_applications(id) ON DELETE CASCADE
);
```

#### 2.2 Onboarding System
```sql
CREATE TABLE cms_onboarding_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    template_name VARCHAR(255) NOT NULL,
    department_id BIGINT UNSIGNED,
    is_default BOOLEAN DEFAULT FALSE,
    
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_onboarding_tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_id BIGINT UNSIGNED NOT NULL,
    task_name VARCHAR(255) NOT NULL,
    description TEXT,
    task_category ENUM('documentation', 'system_access', 'training', 'equipment', 'introduction', 'other'),
    assigned_to_role ENUM('hr', 'it', 'manager', 'employee'),
    due_days_after_start INT NOT NULL,
    is_mandatory BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    
    FOREIGN KEY (template_id) REFERENCES cms_onboarding_templates(id) ON DELETE CASCADE
);

CREATE TABLE cms_employee_onboarding (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    template_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    expected_completion_date DATE NOT NULL,
    actual_completion_date DATE,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    completion_percentage DECIMAL(5,2) DEFAULT 0,
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES cms_onboarding_templates(id)
);

CREATE TABLE cms_onboarding_task_progress (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    onboarding_id BIGINT UNSIGNED NOT NULL,
    task_id BIGINT UNSIGNED NOT NULL,
    assigned_to_user_id BIGINT UNSIGNED,
    due_date DATE NOT NULL,
    completed_date DATE,
    is_completed BOOLEAN DEFAULT FALSE,
    notes TEXT,
    
    FOREIGN KEY (onboarding_id) REFERENCES cms_employee_onboarding(id) ON DELETE CASCADE,
    FOREIGN KEY (task_id) REFERENCES cms_onboarding_tasks(id)
);
```

**Implementation Files:**
- `app/Domain/CMS/Core/Services/RecruitmentService.php`
- `app/Domain/CMS/Core/Services/OnboardingService.php` (enhance existing)
- `app/Http/Controllers/CMS/RecruitmentController.php`
- `resources/js/Pages/CMS/Recruitment/JobPostings.vue`
- `resources/js/Pages/CMS/Recruitment/Applications.vue`
- `resources/js/Pages/CMS/Recruitment/Interviews.vue`
- `resources/js/Pages/CMS/Onboarding/Templates.vue`
- `resources/js/Pages/CMS/Onboarding/EmployeeOnboarding.vue`

---


### 3. Attendance & Time Management (Week 4)

#### 3.1 Attendance System
```sql
CREATE TABLE cms_attendance_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    
    -- Clock In/Out
    clock_in_time DATETIME,
    clock_in_location VARCHAR(255),
    clock_in_latitude DECIMAL(10, 8),
    clock_in_longitude DECIMAL(11, 8),
    clock_in_method ENUM('manual', 'biometric', 'gps', 'web') DEFAULT 'web',
    
    clock_out_time DATETIME,
    clock_out_location VARCHAR(255),
    clock_out_latitude DECIMAL(10, 8),
    clock_out_longitude DECIMAL(11, 8),
    clock_out_method ENUM('manual', 'biometric', 'gps', 'web') DEFAULT 'web',
    
    -- Calculated Fields
    total_hours DECIMAL(5,2),
    regular_hours DECIMAL(5,2),
    overtime_hours DECIMAL(5,2),
    late_minutes INT DEFAULT 0,
    early_departure_minutes INT DEFAULT 0,
    
    -- Status
    status ENUM('present', 'absent', 'late', 'half_day', 'on_leave', 'holiday') DEFAULT 'present',
    notes TEXT,
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_date (employee_id, date),
    INDEX idx_date_status (date, status)
);

CREATE TABLE cms_shift_schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    shift_name VARCHAR(100) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    grace_period_minutes INT DEFAULT 15,
    is_active BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_employee_shifts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    shift_id BIGINT UNSIGNED NOT NULL,
    effective_from DATE NOT NULL,
    effective_to DATE,
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE,
    FOREIGN KEY (shift_id) REFERENCES cms_shift_schedules(id)
);

CREATE TABLE cms_public_holidays (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    holiday_name VARCHAR(255) NOT NULL,
    holiday_date DATE NOT NULL,
    is_recurring BOOLEAN DEFAULT FALSE,
    
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    INDEX idx_company_date (company_id, holiday_date)
);
```

**Zambian Public Holidays (Pre-configured):**
- New Year's Day (January 1)
- Youth Day (March 12)
- Good Friday (Variable)
- Easter Monday (Variable)
- Labour Day (May 1)
- Africa Freedom Day (May 25)
- Heroes Day (First Monday of July)
- Unity Day (First Tuesday of July)
- Farmers' Day (First Monday of August)
- Independence Day (October 24)
- Christmas Day (December 25)

**Implementation Files:**
- `app/Domain/CMS/Core/Services/AttendanceService.php`
- `app/Http/Controllers/CMS/AttendanceController.php`
- `resources/js/Pages/CMS/Attendance/Index.vue`
- `resources/js/Pages/CMS/Attendance/ClockInOut.vue`
- `resources/js/Pages/CMS/Attendance/ShiftSchedules.vue`
- `resources/js/composables/useAttendance.ts`

---

### 4. Leave Management (Week 5)

#### 4.1 Leave System
```sql
CREATE TABLE cms_leave_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    leave_name VARCHAR(100) NOT NULL,
    leave_code VARCHAR(20) UNIQUE NOT NULL,
    
    -- Entitlement
    days_per_year DECIMAL(5,2) NOT NULL,
    is_paid BOOLEAN DEFAULT TRUE,
    
    -- Rules
    requires_approval BOOLEAN DEFAULT TRUE,
    can_carry_forward BOOLEAN DEFAULT FALSE,
    max_carry_forward_days DECIMAL(5,2) DEFAULT 0,
    max_consecutive_days INT,
    min_notice_days INT DEFAULT 0,
    
    -- Gender Specific
    applicable_gender ENUM('all', 'male', 'female') DEFAULT 'all',
    
    -- Accrual
    accrual_method ENUM('annual', 'monthly', 'none') DEFAULT 'annual',
    
    is_active BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

-- Zambian Leave Types (Pre-configured)
-- Annual Leave: 24 days per year
-- Sick Leave: As per company policy (typically 12 days)
-- Maternity Leave: 84 days (12 weeks)
-- Paternity Leave: 2 days
-- Compassionate Leave: 3-5 days
-- Study Leave: As per company policy

CREATE TABLE cms_employee_leave_balances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    leave_type_id BIGINT UNSIGNED NOT NULL,
    year INT NOT NULL,
    
    -- Balance Tracking
    opening_balance DECIMAL(5,2) DEFAULT 0,
    accrued_days DECIMAL(5,2) DEFAULT 0,
    used_days DECIMAL(5,2) DEFAULT 0,
    pending_days DECIMAL(5,2) DEFAULT 0,
    carried_forward_days DECIMAL(5,2) DEFAULT 0,
    remaining_days DECIMAL(5,2) DEFAULT 0,
    
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES cms_leave_types(id),
    UNIQUE KEY unique_employee_type_year (employee_id, leave_type_id, year)
);

CREATE TABLE cms_leave_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    leave_type_id BIGINT UNSIGNED NOT NULL,
    
    -- Leave Details
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days DECIMAL(5,2) NOT NULL,
    reason TEXT,
    
    -- Approval Workflow
    status ENUM('draft', 'pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    reviewed_by BIGINT UNSIGNED,
    reviewed_at TIMESTAMP NULL,
    review_notes TEXT,
    
    -- Handover
    handover_to_employee_id BIGINT UNSIGNED,
    handover_notes TEXT,
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES cms_leave_types(id),
    INDEX idx_employee_status (employee_id, status),
    INDEX idx_dates (start_date, end_date)
);

CREATE TABLE cms_leave_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    leave_request_id BIGINT UNSIGNED NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (leave_request_id) REFERENCES cms_leave_requests(id) ON DELETE CASCADE
);
```

**Implementation Files:**
- `app/Domain/CMS/Core/Services/LeaveManagementService.php`
- `app/Http/Controllers/CMS/LeaveController.php`
- `resources/js/Pages/CMS/Leave/Types.vue`
- `resources/js/Pages/CMS/Leave/Requests.vue`
- `resources/js/Pages/CMS/Leave/Calendar.vue`
- `resources/js/Pages/CMS/Leave/Balances.vue`
- `resources/js/components/CMS/LeaveRequestForm.vue`

---

### 5. Organizational Structure (Week 6)

#### 5.1 Departments & Branches
```sql
CREATE TABLE cms_departments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    department_name VARCHAR(255) NOT NULL,
    department_code VARCHAR(50) UNIQUE,
    parent_department_id BIGINT UNSIGNED,
    head_of_department_id BIGINT UNSIGNED,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_department_id) REFERENCES cms_departments(id) ON DELETE SET NULL,
    FOREIGN KEY (head_of_department_id) REFERENCES cms_employees(id) ON DELETE SET NULL
);

CREATE TABLE cms_branches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    branch_name VARCHAR(255) NOT NULL,
    branch_code VARCHAR(50) UNIQUE,
    address TEXT,
    phone VARCHAR(50),
    email VARCHAR(255),
    branch_manager_id BIGINT UNSIGNED,
    is_active BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_manager_id) REFERENCES cms_employees(id) ON DELETE SET NULL
);

-- Update employee employment table
ALTER TABLE cms_employee_employment 
    ADD FOREIGN KEY (department_id) REFERENCES cms_departments(id) ON DELETE SET NULL,
    ADD FOREIGN KEY (branch_id) REFERENCES cms_branches(id) ON DELETE SET NULL;
```

**Implementation Files:**
- `app/Domain/CMS/Core/Services/OrganizationStructureService.php`
- `app/Http/Controllers/CMS/DepartmentController.php`
- `app/Http/Controllers/CMS/BranchController.php`
- `resources/js/Pages/CMS/Organization/Departments.vue`
- `resources/js/Pages/CMS/Organization/Branches.vue`
- `resources/js/Pages/CMS/Organization/OrgChart.vue`
- `resources/js/components/CMS/OrgChartVisualization.vue`

---

## TIER 2: PAYROLL (HIGH PRIORITY) - 4 Weeks

### 6. Enhanced Payroll Management (Week 7-8)

#### 6.1 Salary Structure
```sql
CREATE TABLE cms_salary_structures (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    
    -- Basic Salary
    basic_salary DECIMAL(15,2) NOT NULL,
    currency_code VARCHAR(3) DEFAULT 'ZMW',
    
    -- Payment Details
    payment_frequency ENUM('monthly', 'bi_weekly', 'weekly') DEFAULT 'monthly',
    payment_method ENUM('bank_transfer', 'cash', 'mobile_money') DEFAULT 'bank_transfer',
    bank_name VARCHAR(255),
    account_number VARCHAR(100),
    account_name VARCHAR(255),
    mobile_money_number VARCHAR(50),
    
    -- Effective Dates
    effective_from DATE NOT NULL,
    effective_to DATE,
    
    is_active BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE,
    INDEX idx_employee_active (employee_id, is_active)
);

CREATE TABLE cms_allowance_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    allowance_name VARCHAR(100) NOT NULL,
    allowance_code VARCHAR(50) UNIQUE NOT NULL,
    calculation_type ENUM('fixed', 'percentage_of_basic', 'custom') DEFAULT 'fixed',
    default_amount DECIMAL(15,2),
    is_taxable BOOLEAN DEFAULT TRUE,
    is_pensionable BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

-- Common Zambian Allowances:
-- Housing Allowance, Transport Allowance, Meal Allowance, 
-- Communication Allowance, Medical Allowance

CREATE TABLE cms_employee_allowances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    allowance_type_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    effective_from DATE NOT NULL,
    effective_to DATE,
    is_active BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE,
    FOREIGN KEY (allowance_type_id) REFERENCES cms_allowance_types(id)
);

CREATE TABLE cms_deduction_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    deduction_name VARCHAR(100) NOT NULL,
    deduction_code VARCHAR(50) UNIQUE NOT NULL,
    calculation_type ENUM('fixed', 'percentage_of_gross', 'custom') DEFAULT 'fixed',
    default_amount DECIMAL(15,2),
    is_statutory BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

-- Zambian Statutory Deductions:
-- NAPSA (5% employee + 5% employer)
-- NHIMA (1% employee)
-- PAYE (Progressive tax rates)

CREATE TABLE cms_employee_deductions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    deduction_type_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    effective_from DATE NOT NULL,
    effective_to DATE,
    is_active BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE,
    FOREIGN KEY (deduction_type_id) REFERENCES cms_deduction_types(id)
);
```

#### 6.2 Payroll Processing
```sql
CREATE TABLE cms_payroll_runs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    payroll_period_month INT NOT NULL,
    payroll_period_year INT NOT NULL,
    
    -- Processing
    status ENUM('draft', 'processing', 'review', 'approved', 'paid') DEFAULT 'draft',
    total_employees INT DEFAULT 0,
    total_gross_pay DECIMAL(15,2) DEFAULT 0,
    total_deductions DECIMAL(15,2) DEFAULT 0,
    total_net_pay DECIMAL(15,2) DEFAULT 0,
    
    -- Approval
    processed_by BIGINT UNSIGNED,
    processed_at TIMESTAMP NULL,
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    
    -- Payment
    payment_date DATE,
    bank_file_generated BOOLEAN DEFAULT FALSE,
    bank_file_path VARCHAR(500),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_company_period (company_id, payroll_period_month, payroll_period_year)
);

CREATE TABLE cms_payroll_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payroll_run_id BIGINT UNSIGNED NOT NULL,
    employee_id BIGINT UNSIGNED NOT NULL,
    
    -- Earnings
    basic_salary DECIMAL(15,2) NOT NULL,
    total_allowances DECIMAL(15,2) DEFAULT 0,
    overtime_pay DECIMAL(15,2) DEFAULT 0,
    bonus DECIMAL(15,2) DEFAULT 0,
    commission DECIMAL(15,2) DEFAULT 0,
    gross_pay DECIMAL(15,2) NOT NULL,
    
    -- Deductions
    napsa_employee DECIMAL(15,2) DEFAULT 0,
    napsa_employer DECIMAL(15,2) DEFAULT 0,
    nhima DECIMAL(15,2) DEFAULT 0,
    paye DECIMAL(15,2) DEFAULT 0,
    total_statutory_deductions DECIMAL(15,2) DEFAULT 0,
    total_other_deductions DECIMAL(15,2) DEFAULT 0,
    total_deductions DECIMAL(15,2) DEFAULT 0,
    
    -- Net Pay
    net_pay DECIMAL(15,2) NOT NULL,
    
    -- Days
    working_days INT DEFAULT 0,
    days_worked DECIMAL(5,2) DEFAULT 0,
    days_absent DECIMAL(5,2) DEFAULT 0,
    days_on_leave DECIMAL(5,2) DEFAULT 0,
    
    -- Payment
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    payment_date DATE,
    payment_reference VARCHAR(255),
    
    FOREIGN KEY (payroll_run_id) REFERENCES cms_payroll_runs(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE,
    INDEX idx_payroll_employee (payroll_run_id, employee_id)
);

CREATE TABLE cms_payroll_item_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payroll_item_id BIGINT UNSIGNED NOT NULL,
    item_type ENUM('allowance', 'deduction', 'overtime', 'bonus', 'commission'),
    item_name VARCHAR(255) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    
    FOREIGN KEY (payroll_item_id) REFERENCES cms_payroll_items(id) ON DELETE CASCADE
);
```

**Zambian PAYE Tax Rates (2024):**
```php
// Progressive tax brackets
$taxBrackets = [
    ['min' => 0, 'max' => 4800, 'rate' => 0],           // K0 - K4,800: 0%
    ['min' => 4800, 'max' => 6900, 'rate' => 25],       // K4,801 - K6,900: 25%
    ['min' => 6900, 'max' => 11600, 'rate' => 30],      // K6,901 - K11,600: 30%
    ['min' => 11600, 'max' => PHP_FLOAT_MAX, 'rate' => 37.5], // Above K11,600: 37.5%
];
```

**Implementation Files:**
- `app/Domain/CMS/Core/Services/EnhancedPayrollService.php`
- `app/Domain/CMS/Core/Services/PayeTaxCalculator.php`
- `app/Domain/CMS/Core/Services/NapsaCalculator.php`
- `app/Http/Controllers/CMS/PayrollController.php` (enhance existing)
- `resources/js/Pages/CMS/Payroll/SalaryStructures.vue`
- `resources/js/Pages/CMS/Payroll/Allowances.vue`
- `resources/js/Pages/CMS/Payroll/Deductions.vue`
- `resources/js/Pages/CMS/Payroll/RunPayroll.vue`
- `resources/js/Pages/CMS/Payroll/Payslips.vue`
- `resources/views/pdf/cms/payslip.blade.php`

---


### 7. Expense & Reimbursements (Week 9)

```sql
CREATE TABLE cms_expense_claims (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    claim_number VARCHAR(50) UNIQUE NOT NULL,
    claim_date DATE NOT NULL,
    
    -- Details
    purpose TEXT NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    currency_code VARCHAR(3) DEFAULT 'ZMW',
    
    -- Approval
    status ENUM('draft', 'submitted', 'approved', 'rejected', 'paid') DEFAULT 'draft',
    submitted_at TIMESTAMP NULL,
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    approval_notes TEXT,
    
    -- Payment
    payment_date DATE,
    payment_reference VARCHAR(255),
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE,
    INDEX idx_employee_status (employee_id, status)
);

CREATE TABLE cms_expense_claim_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    expense_claim_id BIGINT UNSIGNED NOT NULL,
    expense_date DATE NOT NULL,
    expense_category VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    receipt_path VARCHAR(500),
    
    FOREIGN KEY (expense_claim_id) REFERENCES cms_expense_claims(id) ON DELETE CASCADE
);
```

**Implementation Files:**
- `app/Domain/CMS/Core/Services/ExpenseClaimService.php`
- `app/Http/Controllers/CMS/ExpenseClaimController.php`
- `resources/js/Pages/CMS/Expenses/Claims.vue`
- `resources/js/Pages/CMS/Expenses/CreateClaim.vue`

---

### 8. Loan Management (Week 10)

```sql
CREATE TABLE cms_employee_loans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    loan_number VARCHAR(50) UNIQUE NOT NULL,
    
    -- Loan Details
    loan_type ENUM('salary_advance', 'emergency', 'education', 'other') NOT NULL,
    principal_amount DECIMAL(15,2) NOT NULL,
    interest_rate DECIMAL(5,2) DEFAULT 0,
    total_amount DECIMAL(15,2) NOT NULL,
    
    -- Repayment
    repayment_period_months INT NOT NULL,
    monthly_deduction DECIMAL(15,2) NOT NULL,
    start_date DATE NOT NULL,
    
    -- Status
    status ENUM('pending', 'approved', 'active', 'completed', 'defaulted') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    
    -- Tracking
    total_paid DECIMAL(15,2) DEFAULT 0,
    balance DECIMAL(15,2) NOT NULL,
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE
);

CREATE TABLE cms_loan_repayments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    loan_id BIGINT UNSIGNED NOT NULL,
    payroll_item_id BIGINT UNSIGNED,
    repayment_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    balance_after DECIMAL(15,2) NOT NULL,
    
    FOREIGN KEY (loan_id) REFERENCES cms_employee_loans(id) ON DELETE CASCADE,
    FOREIGN KEY (payroll_item_id) REFERENCES cms_payroll_items(id) ON DELETE SET NULL
);
```

**Implementation Files:**
- `app/Domain/CMS/Core/Services/LoanManagementService.php`
- `app/Http/Controllers/CMS/LoanController.php`
- `resources/js/Pages/CMS/Loans/Index.vue`
- `resources/js/Pages/CMS/Loans/Create.vue`

---

## TIER 3: PERFORMANCE (MEDIUM PRIORITY) - 3 Weeks

### 9. Performance Management (Week 11-12)

```sql
CREATE TABLE cms_performance_periods (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    period_name VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('planning', 'active', 'review', 'closed') DEFAULT 'planning',
    
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_kpi_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    template_name VARCHAR(255) NOT NULL,
    department_id BIGINT UNSIGNED,
    job_title VARCHAR(255),
    
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_kpis (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_id BIGINT UNSIGNED,
    kpi_name VARCHAR(255) NOT NULL,
    description TEXT,
    measurement_criteria TEXT,
    target_value DECIMAL(10,2),
    weight DECIMAL(5,2) NOT NULL,
    
    FOREIGN KEY (template_id) REFERENCES cms_kpi_templates(id) ON DELETE CASCADE
);

CREATE TABLE cms_employee_kpis (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    period_id BIGINT UNSIGNED NOT NULL,
    kpi_id BIGINT UNSIGNED NOT NULL,
    
    -- Targets
    target_value DECIMAL(10,2) NOT NULL,
    actual_value DECIMAL(10,2),
    achievement_percentage DECIMAL(5,2),
    
    -- Scoring
    weight DECIMAL(5,2) NOT NULL,
    score DECIMAL(5,2),
    
    -- Status
    status ENUM('not_started', 'in_progress', 'achieved', 'not_achieved'),
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE,
    FOREIGN KEY (period_id) REFERENCES cms_performance_periods(id),
    FOREIGN KEY (kpi_id) REFERENCES cms_kpis(id)
);

CREATE TABLE cms_performance_reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    period_id BIGINT UNSIGNED NOT NULL,
    reviewer_id BIGINT UNSIGNED NOT NULL,
    review_type ENUM('self', 'supervisor', 'peer', '360') NOT NULL,
    
    -- Ratings
    overall_rating DECIMAL(3,2),
    kpi_score DECIMAL(5,2),
    competency_score DECIMAL(5,2),
    
    -- Feedback
    strengths TEXT,
    areas_for_improvement TEXT,
    achievements TEXT,
    development_plan TEXT,
    
    -- Status
    status ENUM('draft', 'submitted', 'acknowledged', 'completed') DEFAULT 'draft',
    submitted_at TIMESTAMP NULL,
    acknowledged_at TIMESTAMP NULL,
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE,
    FOREIGN KEY (period_id) REFERENCES cms_performance_periods(id),
    FOREIGN KEY (reviewer_id) REFERENCES cms_employees(id)
);

CREATE TABLE cms_competency_ratings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    review_id BIGINT UNSIGNED NOT NULL,
    competency_name VARCHAR(255) NOT NULL,
    rating DECIMAL(3,2) NOT NULL,
    comments TEXT,
    
    FOREIGN KEY (review_id) REFERENCES cms_performance_reviews(id) ON DELETE CASCADE
);

CREATE TABLE cms_promotion_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    from_job_title VARCHAR(255) NOT NULL,
    to_job_title VARCHAR(255) NOT NULL,
    from_salary DECIMAL(15,2),
    to_salary DECIMAL(15,2),
    promotion_date DATE NOT NULL,
    reason TEXT,
    approved_by BIGINT UNSIGNED,
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE
);
```

**Implementation Files:**
- `app/Domain/CMS/Core/Services/PerformanceManagementService.php`
- `app/Http/Controllers/CMS/PerformanceController.php`
- `resources/js/Pages/CMS/Performance/Periods.vue`
- `resources/js/Pages/CMS/Performance/KPIs.vue`
- `resources/js/Pages/CMS/Performance/Reviews.vue`
- `resources/js/Pages/CMS/Performance/EmployeeReview.vue`

---

### 10. Training & Development (Week 13)

```sql
CREATE TABLE cms_training_programs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    program_name VARCHAR(255) NOT NULL,
    description TEXT,
    training_type ENUM('internal', 'external', 'online', 'workshop', 'seminar'),
    provider VARCHAR(255),
    duration_hours DECIMAL(5,2),
    cost DECIMAL(15,2),
    max_participants INT,
    
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_training_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    program_id BIGINT UNSIGNED NOT NULL,
    session_name VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    location VARCHAR(255),
    trainer_name VARCHAR(255),
    status ENUM('scheduled', 'ongoing', 'completed', 'cancelled') DEFAULT 'scheduled',
    
    FOREIGN KEY (program_id) REFERENCES cms_training_programs(id) ON DELETE CASCADE
);

CREATE TABLE cms_training_attendance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id BIGINT UNSIGNED NOT NULL,
    employee_id BIGINT UNSIGNED NOT NULL,
    attendance_status ENUM('registered', 'attended', 'absent', 'cancelled') DEFAULT 'registered',
    completion_status ENUM('not_started', 'in_progress', 'completed', 'failed'),
    score DECIMAL(5,2),
    certificate_issued BOOLEAN DEFAULT FALSE,
    certificate_path VARCHAR(500),
    feedback TEXT,
    
    FOREIGN KEY (session_id) REFERENCES cms_training_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE
);

CREATE TABLE cms_employee_skills (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    skill_name VARCHAR(255) NOT NULL,
    proficiency_level ENUM('beginner', 'intermediate', 'advanced', 'expert'),
    acquired_date DATE,
    verified_by BIGINT UNSIGNED,
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE
);

CREATE TABLE cms_career_development_plans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    current_position VARCHAR(255) NOT NULL,
    target_position VARCHAR(255) NOT NULL,
    timeline_months INT,
    development_activities TEXT,
    required_skills TEXT,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE
);
```

**Implementation Files:**
- `app/Domain/CMS/Core/Services/TrainingService.php`
- `app/Http/Controllers/CMS/TrainingController.php`
- `resources/js/Pages/CMS/Training/Programs.vue`
- `resources/js/Pages/CMS/Training/Sessions.vue`
- `resources/js/Pages/CMS/Training/Attendance.vue`
- `resources/js/Pages/CMS/Training/Skills.vue`

---

## TIER 4: ADVANCED HR (OPTIONAL) - 3 Weeks

### 11. Disciplinary & Compliance (Week 14)

```sql
CREATE TABLE cms_disciplinary_actions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    incident_date DATE NOT NULL,
    incident_type ENUM('misconduct', 'poor_performance', 'policy_violation', 'attendance', 'other'),
    description TEXT NOT NULL,
    
    -- Action Taken
    action_type ENUM('verbal_warning', 'written_warning', 'final_warning', 'suspension', 'termination'),
    action_date DATE NOT NULL,
    duration_days INT,
    
    -- Process
    issued_by BIGINT UNSIGNED NOT NULL,
    witness_ids TEXT,
    employee_response TEXT,
    
    -- Status
    status ENUM('active', 'resolved', 'appealed', 'overturned'),
    expiry_date DATE,
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE
);

CREATE TABLE cms_incident_reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reported_by BIGINT UNSIGNED NOT NULL,
    incident_date DATETIME NOT NULL,
    incident_type ENUM('accident', 'injury', 'harassment', 'theft', 'damage', 'other'),
    location VARCHAR(255),
    description TEXT NOT NULL,
    witnesses TEXT,
    action_taken TEXT,
    status ENUM('reported', 'investigating', 'resolved', 'closed'),
    
    FOREIGN KEY (reported_by) REFERENCES cms_employees(id)
);

CREATE TABLE cms_employee_exits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    exit_type ENUM('resignation', 'termination', 'retirement', 'contract_end', 'death'),
    notice_date DATE,
    last_working_date DATE NOT NULL,
    reason TEXT,
    
    -- Exit Interview
    exit_interview_date DATE,
    exit_interview_notes TEXT,
    would_rehire BOOLEAN,
    
    -- Clearance
    clearance_completed BOOLEAN DEFAULT FALSE,
    clearance_notes TEXT,
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE
);

CREATE TABLE cms_exit_clearance_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exit_id BIGINT UNSIGNED NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    responsible_department VARCHAR(100),
    is_cleared BOOLEAN DEFAULT FALSE,
    cleared_by BIGINT UNSIGNED,
    cleared_at TIMESTAMP NULL,
    notes TEXT,
    
    FOREIGN KEY (exit_id) REFERENCES cms_employee_exits(id) ON DELETE CASCADE
);
```

**Implementation Files:**
- `app/Domain/CMS/Core/Services/DisciplinaryService.php`
- `app/Http/Controllers/CMS/DisciplinaryController.php`
- `resources/js/Pages/CMS/Disciplinary/Actions.vue`
- `resources/js/Pages/CMS/Disciplinary/Incidents.vue`
- `resources/js/Pages/CMS/Disciplinary/Exits.vue`

---

### 12. Employee Self-Service Portal (Week 15)

```sql
CREATE TABLE cms_employee_portal_access (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED,
    portal_enabled BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES cms_users(id) ON DELETE SET NULL
);

CREATE TABLE cms_employee_document_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    document_type ENUM('employment_letter', 'salary_certificate', 'tax_clearance', 'reference_letter'),
    purpose TEXT,
    status ENUM('pending', 'approved', 'rejected', 'issued') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_by BIGINT UNSIGNED,
    processed_at TIMESTAMP NULL,
    document_path VARCHAR(500),
    
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE
);
```

**Self-Service Features:**
- View payslips
- Apply for leave
- Update personal details
- Download employment letters
- View attendance records
- Submit expense claims
- View performance reviews
- Access training materials
- Update emergency contacts

**Implementation Files:**
- `app/Http/Controllers/CMS/EmployeePortalController.php`
- `resources/js/Pages/CMS/EmployeePortal/Dashboard.vue`
- `resources/js/Pages/CMS/EmployeePortal/Profile.vue`
- `resources/js/Pages/CMS/EmployeePortal/Payslips.vue`
- `resources/js/Pages/CMS/EmployeePortal/LeaveRequests.vue`
- `resources/js/Pages/CMS/EmployeePortal/Documents.vue`

---

### 13. Internal Communication (Week 16)

```sql
CREATE TABLE cms_company_announcements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    announcement_type ENUM('general', 'policy', 'event', 'urgent') DEFAULT 'general',
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    
    -- Targeting
    target_audience ENUM('all', 'department', 'branch', 'custom') DEFAULT 'all',
    department_id BIGINT UNSIGNED,
    branch_id BIGINT UNSIGNED,
    
    -- Publishing
    publish_date DATETIME NOT NULL,
    expiry_date DATETIME,
    is_published BOOLEAN DEFAULT FALSE,
    
    -- Tracking
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_announcement_reads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    announcement_id BIGINT UNSIGNED NOT NULL,
    employee_id BIGINT UNSIGNED NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (announcement_id) REFERENCES cms_company_announcements(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_announcement_employee (announcement_id, employee_id)
);

CREATE TABLE cms_policy_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    policy_name VARCHAR(255) NOT NULL,
    policy_category VARCHAR(100),
    version VARCHAR(20),
    effective_date DATE NOT NULL,
    document_path VARCHAR(500) NOT NULL,
    requires_acknowledgment BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);

CREATE TABLE cms_policy_acknowledgments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    policy_id BIGINT UNSIGNED NOT NULL,
    employee_id BIGINT UNSIGNED NOT NULL,
    acknowledged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    
    FOREIGN KEY (policy_id) REFERENCES cms_policy_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES cms_employees(id) ON DELETE CASCADE
);
```

**Implementation Files:**
- `app/Domain/CMS/Core/Services/InternalCommunicationService.php`
- `app/Http/Controllers/CMS/AnnouncementController.php`
- `resources/js/Pages/CMS/Communication/Announcements.vue`
- `resources/js/Pages/CMS/Communication/Policies.vue`
- `resources/js/components/CMS/AnnouncementBanner.vue`

---


## CROSS-CUTTING FEATURES

### 14. Reporting & Analytics

**HR Reports:**
1. Headcount Reports
   - Total employees by department/branch
   - Employee demographics
   - Turnover analysis
   
2. Attendance Reports
   - Daily/monthly attendance summary
   - Late arrivals report
   - Absenteeism analysis
   - Overtime report

3. Leave Reports
   - Leave balance report
   - Leave utilization analysis
   - Leave trends

4. Payroll Reports
   - Payroll summary
   - Salary distribution
   - Deductions summary
   - Tax reports (PAYE, NAPSA, NHIMA)
   - Bank transfer file

5. Performance Reports
   - Performance ratings distribution
   - KPI achievement report
   - Training completion rates

6. Compliance Reports
   - Contract expiry alerts
   - Probation tracking
   - Document expiry alerts
   - Statutory compliance

**Implementation:**
```sql
CREATE TABLE cms_hr_report_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    report_name VARCHAR(255) NOT NULL,
    report_type VARCHAR(100) NOT NULL,
    parameters TEXT,
    schedule_frequency ENUM('none', 'daily', 'weekly', 'monthly'),
    recipients TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (company_id) REFERENCES cms_companies(id) ON DELETE CASCADE
);
```

**Files:**
- `app/Domain/CMS/Core/Services/HRReportingService.php`
- `app/Http/Controllers/CMS/HRReportController.php`
- `resources/js/Pages/CMS/HR/Reports/Index.vue`
- `resources/js/Pages/CMS/HR/Reports/Headcount.vue`
- `resources/js/Pages/CMS/HR/Reports/Attendance.vue`
- `resources/js/Pages/CMS/HR/Reports/Payroll.vue`

---

### 15. Role & Permission Management

**HR-Specific Roles:**
- **HR Manager**: Full access to all HR modules
- **HR Officer**: Limited access (no salary/payroll access)
- **Payroll Officer**: Payroll and salary access only
- **Department Manager**: View team members, approve leave/attendance
- **Employee**: Self-service portal access only

**Permissions:**
```php
$hrPermissions = [
    // Employee Management
    'employees.view',
    'employees.create',
    'employees.edit',
    'employees.delete',
    'employees.view_salary',
    
    // Recruitment
    'recruitment.manage',
    'recruitment.view_applications',
    'recruitment.schedule_interviews',
    
    // Attendance
    'attendance.view_all',
    'attendance.approve',
    'attendance.edit',
    
    // Leave
    'leave.view_all',
    'leave.approve',
    'leave.manage_types',
    
    // Payroll
    'payroll.view',
    'payroll.process',
    'payroll.approve',
    'payroll.view_reports',
    
    // Performance
    'performance.view_all',
    'performance.conduct_reviews',
    'performance.manage_kpis',
    
    // Reports
    'hr_reports.view',
    'hr_reports.export',
];
```

---

### 16. Integrations

#### 16.1 Biometric Device Integration
```php
// Support for common biometric devices
interface BiometricDeviceInterface {
    public function fetchAttendance(DateTime $from, DateTime $to): array;
    public function syncEmployees(array $employees): bool;
    public function getDeviceStatus(): array;
}

// Supported devices:
// - ZKTeco
// - Suprema
// - Anviz
// - Generic SDK
```

#### 16.2 Bank Integration
```php
// Generate bank transfer files
class BankFileGenerator {
    public function generateZambianBankFile(PayrollRun $payroll): string;
    // Supports: Zanaco, FNB, Stanbic, Standard Chartered formats
}
```

#### 16.3 Email & SMS Notifications
- Leave approval notifications
- Payslip delivery
- Contract expiry alerts
- Birthday wishes
- Work anniversary
- Training reminders

#### 16.4 Accounting System Integration
- Auto-post payroll to general ledger
- Expense claims to accounts payable
- Loan deductions tracking

---

## IMPLEMENTATION ROADMAP

### Phase 1: Core HR (Weeks 1-6)
**Priority:** 🔴 CRITICAL

**Week 1-2: Employee Records**
- Database migrations
- Employee CRUD
- Document management
- Photo upload

**Week 3: Recruitment & Onboarding**
- Job postings
- Application tracking
- Interview scheduling
- Onboarding checklists

**Week 4: Attendance**
- Clock in/out system
- GPS attendance
- Shift management
- Public holidays

**Week 5: Leave Management**
- Leave types setup
- Leave requests
- Approval workflow
- Balance tracking

**Week 6: Organization Structure**
- Departments
- Branches
- Org chart
- Reporting structure

**Deliverables:**
- Functional employee database
- Basic attendance tracking
- Leave management system
- Organizational structure

---

### Phase 2: Payroll (Weeks 7-10)
**Priority:** 🔴 HIGH

**Week 7-8: Salary & Payroll**
- Salary structures
- Allowances & deductions
- PAYE calculator
- NAPSA/NHIMA calculator
- Payroll processing

**Week 9: Expenses**
- Expense claims
- Receipt upload
- Approval workflow
- Reimbursement tracking

**Week 10: Loans**
- Loan management
- Repayment tracking
- Auto-deduction from payroll

**Deliverables:**
- Complete payroll system
- Zambian tax compliance
- Payslip generation
- Bank file generation

---

### Phase 3: Performance (Weeks 11-13)
**Priority:** 🟡 MEDIUM

**Week 11-12: Performance Management**
- KPI templates
- Performance reviews
- 360-degree feedback
- Promotion tracking

**Week 13: Training**
- Training programs
- Session management
- Attendance tracking
- Skills database

**Deliverables:**
- Performance review system
- Training management
- Career development tracking

---

### Phase 4: Advanced HR (Weeks 14-16)
**Priority:** 🟢 OPTIONAL

**Week 14: Disciplinary & Compliance**
- Disciplinary actions
- Incident reports
- Exit management
- Clearance process

**Week 15: Employee Portal**
- Self-service dashboard
- Document requests
- Profile updates
- Leave applications

**Week 16: Communication**
- Announcements
- Policy management
- Notice board

**Deliverables:**
- Complete HRMS
- Employee self-service
- Compliance tracking

---

## TECHNICAL SPECIFICATIONS

### Database Summary
**Total Tables:** 50+ tables
**Estimated Size:** 100MB - 1GB (depending on company size)
**Indexes:** 80+ indexes for performance

### API Endpoints
```
/api/cms/hr/employees
/api/cms/hr/attendance
/api/cms/hr/leave
/api/cms/hr/payroll
/api/cms/hr/performance
/api/cms/hr/training
/api/cms/hr/reports
```

### File Storage
- Employee photos: `storage/cms/employees/photos/`
- Documents: `storage/cms/employees/documents/`
- Payslips: `storage/cms/payroll/payslips/`
- Training certificates: `storage/cms/training/certificates/`

### Performance Considerations
- Index all foreign keys
- Partition large tables (attendance, payroll)
- Cache frequently accessed data
- Queue heavy operations (payroll processing)
- Optimize report queries

---

## TESTING STRATEGY

### Unit Tests
- Payroll calculations
- Tax calculations
- Leave balance calculations
- Attendance calculations

### Integration Tests
- Payroll processing workflow
- Leave approval workflow
- Performance review workflow
- Onboarding workflow

### User Acceptance Testing
- HR Manager scenarios
- Employee self-service scenarios
- Payroll officer scenarios
- Manager approval scenarios

---

## DEPLOYMENT CHECKLIST

### Pre-Deployment
- [ ] Run all migrations
- [ ] Seed Zambian public holidays
- [ ] Seed leave types
- [ ] Seed allowance/deduction types
- [ ] Configure PAYE tax brackets
- [ ] Set up email templates
- [ ] Configure SMS gateway (optional)

### Post-Deployment
- [ ] Import existing employees
- [ ] Set up departments/branches
- [ ] Configure salary structures
- [ ] Set up approval workflows
- [ ] Train HR staff
- [ ] Enable employee portal
- [ ] Test payroll processing

---

## COST ESTIMATES

### Development Costs
- **Core HR (6 weeks):** $30,000 - $45,000
- **Payroll (4 weeks):** $20,000 - $30,000
- **Performance (3 weeks):** $15,000 - $22,500
- **Advanced HR (3 weeks):** $15,000 - $22,500

**Total Development:** $80,000 - $120,000

### Third-Party Services (Annual)
- **Biometric Integration:** $1,000 - $2,000
- **SMS Gateway:** $500 - $1,500
- **Email Service:** $300 - $600

**Total Services:** $1,800 - $4,100/year

---

## SUCCESS METRICS

### Adoption Metrics
- 90% of employees onboarded within 1 month
- 80% self-service portal usage
- 95% payroll accuracy

### Efficiency Metrics
- 50% reduction in HR admin time
- 70% faster leave approval
- 90% reduction in payroll errors

### Compliance Metrics
- 100% statutory compliance
- Zero late payroll runs
- 100% document tracking

---

## MAINTENANCE & SUPPORT

### Regular Updates
- **Monthly:** Tax rate updates
- **Quarterly:** Feature enhancements
- **Annually:** Compliance updates

### Support Levels
- **Critical:** Payroll issues (1-hour response)
- **High:** Leave/attendance issues (4-hour response)
- **Medium:** General queries (24-hour response)

---

## FUTURE ENHANCEMENTS (v2.0)

1. **AI-Powered Features**
   - Predictive attrition analysis
   - Automated resume screening
   - Performance prediction
   - Salary benchmarking

2. **Advanced Analytics**
   - Workforce planning
   - Succession planning
   - Skills gap analysis
   - Diversity metrics

3. **Mobile App**
   - Native iOS/Android apps
   - Offline attendance
   - Push notifications
   - Biometric authentication

4. **Workflow Automation**
   - Custom workflow builder
   - Automated onboarding
   - Smart leave allocation
   - Auto-promotion triggers

---

## CONCLUSION

This comprehensive HRMS implementation will transform the CMS into a complete business management solution. The modular approach allows SMEs to start with Core HR and scale as needed.

**Key Benefits:**
- ✅ Complete employee lifecycle management
- ✅ Zambian compliance (NAPSA, NHIMA, PAYE)
- ✅ Automated payroll processing
- ✅ Employee self-service
- ✅ Performance tracking
- ✅ Comprehensive reporting

**Recommended Approach:**
1. Implement Core HR first (6 weeks)
2. Add Payroll immediately after (4 weeks)
3. Roll out Performance when stable (3 weeks)
4. Add Advanced HR based on demand (3 weeks)

**Total Timeline:** 16 weeks (4 months)  
**Total Investment:** $80,000 - $120,000

---

**Last Updated:** February 13, 2026  
**Document Status:** Complete - Ready for Implementation  
**Priority:** 🔴 P1 - HIGH PRIORITY  
**Next Steps:** Review with stakeholders, allocate resources, begin Phase 1


---

## IMPLEMENTATION STATUS SUMMARY

### ✅ Already Implemented (Production Ready)

| Feature | Status | Tables | Services | Controllers | Pages | Docs |
|---------|--------|--------|----------|-------------|-------|------|
| Worker Records | ✅ Complete | cms_workers | PayrollService | PayrollController | Workers/Index, Create, Show | PAYROLL_SYSTEM.md |
| Attendance Tracking | ✅ Complete | cms_worker_attendance | PayrollService | PayrollController | - | PAYROLL_SYSTEM.md |
| Time Tracking | ✅ Complete | cms_time_entries, cms_timesheets | TimeTrackingService | TimeTrackingController | TimeTracking/Index | TIME_TRACKING.md |
| Commission Management | ✅ Complete | cms_commissions | PayrollService | PayrollController | - | PAYROLL_SYSTEM.md |
| Payroll Processing | ✅ Complete | cms_payroll_runs, cms_payroll_items | PayrollService | PayrollController | Payroll/Index, Create, Show | PAYROLL_SYSTEM.md |

**Total Existing:** 5 major modules, 7 database tables, 2 services, 2 controllers, 6 pages

### 🔨 Needs to be Added

| Feature | Priority | Estimated Time | Dependencies |
|---------|----------|----------------|--------------|
| Enhanced Worker Profile | P1 | 1 week | Extend cms_workers table |
| Document Management | P1 | 3 days | New cms_employee_documents table |
| Leave Management | P1 | 1 week | New tables + service |
| Recruitment System | P2 | 1 week | New tables + pages |
| Onboarding System | P2 | 3 days | New tables + workflow |
| Zambian Payroll Compliance | P1 | 1 week | Enhance PayrollService |
| Performance Management | P2 | 1 week | New tables + service |
| Training & Development | P3 | 3 days | New tables |
| Disciplinary System | P3 | 3 days | New tables |
| Employee Self-Service | P2 | 1 week | New pages + auth |

**Total New:** 10 major modules, ~15 new tables, 5 new services, 5 new controllers, 20+ pages

---

## REVISED IMPLEMENTATION TIMELINE

### Phase 1: Core HR Enhancement (4 weeks)
**Week 1-2: Employee Records Enhancement**
- Extend cms_workers table with additional HR fields
- Add document management system
- Update Worker pages with enhanced fields
- Add organizational structure (departments, branches)

**Week 3: Leave Management**
- Create leave types and policies
- Build leave application workflow
- Add leave balance tracking
- Create leave calendar view

**Week 4: Recruitment & Onboarding**
- Job posting system
- Applicant tracking
- Interview scheduling
- Onboarding checklist

### Phase 2: Payroll Enhancement (3 weeks)
**Week 5-6: Zambian Compliance**
- PAYE tax calculation (Zambian brackets)
- NAPSA deductions (10% employee + employer)
- NHIMA deductions (1% employee + employer)
- Payslip generation with statutory deductions
- Tax reports (PAYE, NAPSA, NHIMA)

**Week 7: Advanced Payroll**
- Allowances management (housing, transport, etc.)
- Loan management and deductions
- Bonus and incentive processing
- Bank transfer file generation

### Phase 3: Performance Management (2 weeks)
**Week 8: KPIs & Reviews**
- KPI setting and tracking
- Performance appraisal forms
- 360-degree reviews
- Performance scoring

**Week 9: Training & Development**
- Training program management
- Attendance tracking
- Certificate management
- Skill tracking

### Phase 4: Advanced HR (1 week)
**Week 10: Self-Service & Compliance**
- Employee self-service portal
- Disciplinary tracking
- Exit management
- Compliance reporting

---

## COST BREAKDOWN (Revised)

### Development Costs
- **Phase 1 (Core HR):** $20,000 (4 weeks × $5,000/week)
- **Phase 2 (Payroll):** $15,000 (3 weeks × $5,000/week)
- **Phase 3 (Performance):** $10,000 (2 weeks × $5,000/week)
- **Phase 4 (Advanced):** $5,000 (1 week × $5,000/week)

**Total Development:** $50,000

### Additional Costs
- Testing & QA: $10,000
- Documentation: $5,000
- Training & Support: $5,000
- Contingency (20%): $10,000

**Total Budget:** $50,000 - $75,000

---

## INTEGRATION STRATEGY

### Approach: Enhance, Don't Replace

1. **Keep Existing Workers System**
   - Rename "Workers" to "Employees" in UI
   - Extend cms_workers table (don't create new cms_employees)
   - Maintain backward compatibility
   - Existing payroll continues to work

2. **Gradual Feature Addition**
   - Add new features incrementally
   - Each feature is optional/configurable
   - SMEs can enable features as needed
   - No breaking changes to existing functionality

3. **Data Migration**
   - No data migration needed (enhancing existing tables)
   - New fields are nullable or have defaults
   - Existing records remain valid

4. **UI Updates**
   - Update Workers pages to show new fields
   - Add new pages for new features (Leave, Recruitment, etc.)
   - Maintain existing workflows
   - Progressive enhancement approach

---

## SUCCESS METRICS

### Phase 1 Success Criteria
- [ ] All existing worker records display correctly with new fields
- [ ] Document upload and management working
- [ ] Leave application workflow functional
- [ ] Recruitment system accepting applications
- [ ] No breaking changes to existing payroll

### Phase 2 Success Criteria
- [ ] PAYE calculation matches Zambian tax tables
- [ ] NAPSA/NHIMA deductions calculated correctly
- [ ] Payslips generated with all statutory deductions
- [ ] Existing payroll runs continue to work
- [ ] Bank transfer files generated correctly

### Phase 3 Success Criteria
- [ ] KPI tracking functional
- [ ] Performance reviews can be conducted
- [ ] Training programs can be managed
- [ ] Reports generated correctly

### Phase 4 Success Criteria
- [ ] Employee self-service portal accessible
- [ ] Disciplinary tracking working
- [ ] Exit management functional
- [ ] All compliance reports available

---

## RISK MITIGATION

### Technical Risks
1. **Risk:** Breaking existing payroll functionality
   - **Mitigation:** Comprehensive testing, feature flags, gradual rollout

2. **Risk:** Data integrity issues when extending tables
   - **Mitigation:** Careful migration scripts, nullable fields, defaults

3. **Risk:** Performance degradation with additional fields
   - **Mitigation:** Proper indexing, query optimization, caching

### Business Risks
1. **Risk:** User confusion with new features
   - **Mitigation:** Clear documentation, training, optional features

2. **Risk:** Zambian compliance errors
   - **Mitigation:** Consult with local accountants, thorough testing

3. **Risk:** Scope creep
   - **Mitigation:** Strict phase boundaries, MVP approach

---

## NEXT STEPS

1. **Immediate (This Week)**
   - Review and approve this implementation plan
   - Create Phase 1 detailed task breakdown
   - Set up development environment
   - Create feature branch: `feature/hrms-enhancement`

2. **Week 1 Start**
   - Create migration to extend cms_workers table
   - Update WorkerModel with new fields
   - Update Worker forms and pages
   - Add document management

3. **Testing Strategy**
   - Unit tests for all new services
   - Integration tests for payroll calculations
   - Feature tests for complete workflows
   - Manual testing with real Zambian scenarios

4. **Documentation Updates**
   - Update PAYROLL_SYSTEM.md with new features
   - Create LEAVE_MANAGEMENT.md
   - Create RECRUITMENT_SYSTEM.md
   - Update this document as implementation progresses

---

## RELATED DOCUMENTATION

- [Payroll System](./PAYROLL_SYSTEM.md) - Existing payroll documentation
- [Time Tracking](./TIME_TRACKING.md) - Time tracking system
- [Missing Features Roadmap](./MISSING_FEATURES_ROADMAP.md) - Overall CMS roadmap
- [Implementation Progress](./IMPLEMENTATION_PROGRESS.md) - CMS implementation status

---

## Changelog

### February 13, 2026
- **Major Update:** Discovered existing Workers/Payroll system
- Revised implementation plan to enhance existing system rather than build from scratch
- Reduced timeline from 16 weeks to 10 weeks
- Reduced budget from $80K-$120K to $50K-$75K
- Added comprehensive status summary of existing vs new features
- Updated strategy to "Enhance, Don't Replace"
- Documented existing tables, services, controllers, and pages
- Created integration strategy for backward compatibility
