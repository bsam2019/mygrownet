# Organizational Structure Implementation Plan

**Last Updated:** November 5, 2025  
**Status:** Planning & Analysis  
**Priority:** High

---

## Executive Summary

This document outlines how to integrate the **Organizational Structure** document (docs/ORGANIZATIONAL_STRUCTURE.md) with the **existing employee management system** already built in the MyGrowNet platform.

### Key Finding

✅ **Good News:** MyGrowNet already has a comprehensive employee management system with:
- Domain-Driven Design (DDD) architecture
- Departments, Positions, and Employees models
- Employee management controllers and pages
- Performance tracking and commission management
- Hierarchical department structure

### What We Need to Add

The organizational structure document provides a **strategic roadmap** for:
1. **Organizational hierarchy** (CEO → COO → Department Heads)
2. **Role definitions** and KPIs
3. **Hiring roadmap** (Phase 1, 2, 3)
4. **Budget allocation**
5. **Governance framework**

---

## Current System Analysis

### ✅ What Already Exists

#### 1. Database Structure
```
✅ departments table
   - id, name, description
   - head_employee_id (department head)
   - parent_department_id (hierarchical structure)
   - is_active

✅ positions table
   - id, title, description
   - department_id
   - min_salary, max_salary
   - base_commission_rate, performance_commission_rate
   - permissions (JSON)
   - level (hierarchy level)
   - is_active

✅ employees table
   - id, employee_id (unique identifier)
   - user_id (link to users table)
   - first_name, last_name, email, phone
   - department_id, position_id
   - manager_id (reporting structure)
   - employment_status (active, inactive, terminated, suspended)
   - hire_date, termination_date
   - current_salary
   - emergency_contacts, qualifications, notes
```

#### 2. Domain Layer (DDD)
```
✅ app/Domain/Employee/
   - Entities/Employee.php (rich domain model)
   - ValueObjects/ (Email, Phone, Salary, PerformanceMetrics, etc.)
   - Exceptions/
   - Repositories/
```

#### 3. Infrastructure Layer
```
✅ app/Infrastructure/Persistence/Eloquent/
   - EmployeeModel.php
   - DepartmentModel.php
   - PositionModel.php
   - EmployeePerformanceModel.php
   - EmployeeCommissionModel.php
```

#### 4. Controllers & Pages
```
✅ app/Http/Controllers/Employee/
   - EmployeeController.php (CRUD operations)
   - DepartmentController.php

✅ Inertia Pages (assumed):
   - Employee/Index.vue
   - Employee/Create.vue
   - Employee/Show.vue
```

#### 5. Features Already Implemented
- ✅ Employee CRUD operations
- ✅ Department hierarchy (parent-child relationships)
- ✅ Position management with salary ranges
- ✅ Manager-employee reporting structure
- ✅ Performance tracking
- ✅ Commission calculations
- ✅ Employment status management
- ✅ Search and filtering
- ✅ Soft deletes

---

## What Needs to Be Added

### Phase 1: Organizational Hierarchy & Roles (SHORT TERM - 0-6 Months)

#### 1. **C-Level Positions & Organizational Chart**

**Database Changes:**
```sql
-- Add organizational level to positions
ALTER TABLE positions ADD COLUMN organizational_level ENUM(
    'c_level',           -- CEO, COO, CFO, CTO, CGO
    'director',          -- Department Directors
    'manager',           -- Department Managers
    'team_lead',         -- Team Leads
    'individual'         -- Individual Contributors
) DEFAULT 'individual';

-- Add reporting chain tracking
ALTER TABLE positions ADD COLUMN reports_to_position_id BIGINT UNSIGNED NULL;
ALTER TABLE positions ADD FOREIGN KEY (reports_to_position_id) 
    REFERENCES positions(id) ON DELETE SET NULL;
```

**Seed Data Needed:**
```php
// Create organizational positions based on ORGANIZATIONAL_STRUCTURE.md
Positions to create:
1. CEO/Managing Director
2. Chief Operating Officer (COO)
3. Chief Financial Officer (CFO)
4. Chief Technology Officer (CTO)
5. Chief Growth Officer (CGO)
6. Operations Manager
7. Finance & Compliance Lead
8. Technology Lead
9. Growth & Marketing Lead
... (see full list in organizational structure doc)
```

#### 2. **KPI Tracking System**

**New Tables:**
```sql
CREATE TABLE position_kpis (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    position_id BIGINT UNSIGNED NOT NULL,
    kpi_name VARCHAR(255) NOT NULL,
    kpi_description TEXT,
    target_value DECIMAL(10, 2),
    measurement_unit VARCHAR(50),
    measurement_frequency ENUM('daily', 'weekly', 'monthly', 'quarterly', 'annual'),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE CASCADE
);

CREATE TABLE employee_kpi_tracking (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    position_kpi_id BIGINT UNSIGNED NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    actual_value DECIMAL(10, 2),
    target_value DECIMAL(10, 2),
    achievement_percentage DECIMAL(5, 2),
    notes TEXT,
    recorded_by BIGINT UNSIGNED,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (position_kpi_id) REFERENCES position_kpis(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_employee_period (employee_id, period_start, period_end)
);
```

#### 3. **Role Definitions & Responsibilities**

**New Table:**
```sql
CREATE TABLE position_responsibilities (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    position_id BIGINT UNSIGNED NOT NULL,
    responsibility_title VARCHAR(255) NOT NULL,
    responsibility_description TEXT,
    priority ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
    category ENUM('strategic', 'operational', 'administrative', 'technical') DEFAULT 'operational',
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE CASCADE
);
```

#### 4. **Hiring Roadmap Tracker**

**New Table:**
```sql
CREATE TABLE hiring_roadmap (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    position_id BIGINT UNSIGNED NOT NULL,
    phase ENUM('phase_1', 'phase_2', 'phase_3') NOT NULL,
    target_hire_date DATE,
    priority ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
    headcount INT DEFAULT 1,
    status ENUM('planned', 'in_progress', 'hired', 'cancelled') DEFAULT 'planned',
    budget_allocated DECIMAL(10, 2),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE CASCADE
);
```

---

### Phase 2: Enhanced Features (MEDIUM TERM - 6-18 Months)

#### 1. **Department Budget Management**

**New Table:**
```sql
CREATE TABLE department_budgets (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    department_id BIGINT UNSIGNED NOT NULL,
    fiscal_year INT NOT NULL,
    fiscal_quarter INT,
    budget_category ENUM('salaries', 'operations', 'technology', 'marketing', 'other'),
    allocated_amount DECIMAL(12, 2) NOT NULL,
    spent_amount DECIMAL(12, 2) DEFAULT 0,
    remaining_amount DECIMAL(12, 2) GENERATED ALWAYS AS (allocated_amount - spent_amount) STORED,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    INDEX idx_dept_fiscal (department_id, fiscal_year, fiscal_quarter)
);
```

#### 2. **Organizational Chart Visualization**

**Features to Build:**
- Interactive org chart component (Vue.js)
- Drag-and-drop reorganization
- Export to PDF/PNG
- Real-time updates

#### 3. **Performance Review System**

**Enhancement to existing EmployeePerformanceModel:**
- Link to position KPIs
- Automated review scheduling
- 360-degree feedback
- Performance improvement plans

---

### Phase 3: Enterprise Features (LONG TERM - 18+ Months)

#### 1. **Succession Planning**

**New Table:**
```sql
CREATE TABLE succession_plans (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    position_id BIGINT UNSIGNED NOT NULL,
    current_employee_id BIGINT UNSIGNED,
    successor_employee_id BIGINT UNSIGNED,
    readiness_level ENUM('ready_now', 'ready_1_year', 'ready_2_years', 'not_ready'),
    development_plan TEXT,
    last_reviewed_at TIMESTAMP,
    reviewed_by BIGINT UNSIGNED,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE CASCADE,
    FOREIGN KEY (current_employee_id) REFERENCES employees(id) ON DELETE SET NULL,
    FOREIGN KEY (successor_employee_id) REFERENCES employees(id) ON DELETE SET NULL
);
```

#### 2. **Workforce Analytics Dashboard**

**Metrics to Track:**
- Headcount by department/level
- Turnover rate
- Time to hire
- Cost per hire
- Employee satisfaction scores
- Performance distribution
- Salary benchmarking

#### 3. **Governance & Compliance**

**New Tables:**
```sql
CREATE TABLE board_members (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    role ENUM('chairman', 'executive_director', 'independent_director', 'investor_representative'),
    employee_id BIGINT UNSIGNED NULL,
    appointed_date DATE NOT NULL,
    term_end_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    bio TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL
);

CREATE TABLE board_committees (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    type ENUM('audit', 'risk', 'technology', 'compensation', 'governance'),
    description TEXT,
    meeting_frequency VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE committee_members (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    committee_id BIGINT UNSIGNED NOT NULL,
    board_member_id BIGINT UNSIGNED NOT NULL,
    role ENUM('chair', 'member'),
    appointed_date DATE NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (committee_id) REFERENCES board_committees(id) ON DELETE CASCADE,
    FOREIGN KEY (board_member_id) REFERENCES board_members(id) ON DELETE CASCADE
);
```

---

## Implementation Roadmap

### **Week 1-2: Foundation**
- [ ] Create migration for organizational_level in positions table
- [ ] Create migration for position_kpis table
- [ ] Create migration for employee_kpi_tracking table
- [ ] Create migration for position_responsibilities table
- [ ] Seed initial organizational positions (CEO, COO, CFO, CTO, CGO)

### **Week 3-4: KPI System**
- [ ] Build KPI management admin interface
- [ ] Create KPI tracking forms
- [ ] Build KPI dashboard for employees
- [ ] Build KPI reporting for managers

### **Month 2: Organizational Chart**
- [ ] Build interactive org chart component
- [ ] Add org chart to admin dashboard
- [ ] Add position hierarchy visualization
- [ ] Add employee reporting structure view

### **Month 3: Hiring Roadmap**
- [ ] Create hiring_roadmap table
- [ ] Build hiring roadmap admin interface
- [ ] Integrate with recruitment system
- [ ] Add hiring progress tracking

### **Month 4-6: Enhancements**
- [ ] Department budget management
- [ ] Performance review enhancements
- [ ] Workforce analytics dashboard
- [ ] Role-based access control refinements

---

## Integration Points

### 1. **Link to User Management**
```php
// Users table already has roles via Spatie Permission
// Need to add:
- Link employees to organizational positions
- Sync permissions based on position
- Auto-assign roles when employee is created
```

### 2. **Link to Commission System**
```php
// EmployeeCommissionModel already exists
// Need to:
- Link commission calculations to position KPIs
- Track commission against performance targets
- Generate commission reports by department
```

### 3. **Link to Admin Dashboard**
```php
// AdminDashboardController already has employeeManagementData()
// Need to add:
- Organizational chart widget
- Hiring roadmap progress
- Department headcount vs. budget
- Key position vacancies alert
```

### 4. **Link to Reporting**
```php
// Need to create:
- Organizational structure reports
- Headcount reports by department/level
- Salary analysis reports
- KPI achievement reports
```

---

## UI/UX Considerations

### 1. **Admin Navigation**
Add new menu items:
```
Admin Panel
├── Dashboard
├── Users
├── Employees ← Existing
│   ├── All Employees
│   ├── Departments
│   ├── Positions
│   ├── Organizational Chart ← NEW
│   ├── KPI Management ← NEW
│   └── Hiring Roadmap ← NEW
├── ...
```

### 2. **Employee Profile Enhancement**
Add tabs to employee profile:
```
Employee Profile
├── Overview (existing)
├── Performance (existing)
├── KPIs ← NEW
├── Responsibilities ← NEW
├── Direct Reports ← NEW
└── Development Plan ← NEW
```

### 3. **Department Page Enhancement**
```
Department View
├── Overview
├── Team Members
├── Budget ← NEW
├── KPIs ← NEW
├── Org Chart ← NEW
└── Open Positions ← NEW
```

---

## Technical Considerations

### 1. **Performance**
- Use eager loading for organizational hierarchy queries
- Cache org chart data (updates infrequently)
- Index foreign keys and frequently queried columns
- Use database views for complex reporting queries

### 2. **Security**
- Role-based access control for sensitive data (salaries, performance)
- Audit trail for all organizational changes
- Approval workflow for position changes
- Data encryption for sensitive employee information

### 3. **Scalability**
- Design for 100-150+ employees (long-term goal)
- Support multi-level hierarchies (7+ levels)
- Handle complex reporting structures
- Support regional expansion

---

## Next Steps

### Immediate Actions (This Week)
1. **Review and approve this implementation plan**
2. **Prioritize Phase 1 features**
3. **Create database migrations for Phase 1**
4. **Seed initial organizational positions**

### Short Term (Next Month)
1. **Build KPI management system**
2. **Create organizational chart component**
3. **Enhance employee management UI**
4. **Add hiring roadmap tracker**

### Medium Term (3-6 Months)
1. **Department budget management**
2. **Performance review enhancements**
3. **Workforce analytics**
4. **Succession planning**

---

## Questions to Answer

1. **Who will be the first employees to add?**
   - CEO/Founder (you?)
   - Operations Manager (Priority #1 hire)
   - Other key positions?

2. **What KPIs should we track first?**
   - Start with company-level KPIs from organizational structure doc?
   - Department-specific KPIs?
   - Individual performance metrics?

3. **Do you want to implement all phases or start with Phase 1?**
   - Recommendation: Start with Phase 1 (SHORT TERM features)
   - Add Phase 2 and 3 as the organization grows

4. **Should we integrate with existing recruitment system?**
   - I see JobPostingModel and JobApplicationModel exist
   - Link hiring roadmap to job postings?

---

## Conclusion

The existing employee management system provides a **solid foundation**. We need to:

1. ✅ **Leverage existing infrastructure** (departments, positions, employees)
2. ➕ **Add organizational hierarchy** (C-level positions, reporting structure)
3. ➕ **Add KPI tracking** (position KPIs, employee performance)
4. ➕ **Add strategic planning** (hiring roadmap, budget management)
5. ➕ **Add governance** (board, committees, compliance)

**Recommendation:** Start with **Phase 1 (SHORT TERM)** features and build incrementally as the organization grows.

---

**Document Owner:** CTO  
**Next Review:** After Phase 1 implementation  
**Related Documents:**
- docs/ORGANIZATIONAL_STRUCTURE.md
- docs/structure.md (Domain-Driven Design guidelines)
