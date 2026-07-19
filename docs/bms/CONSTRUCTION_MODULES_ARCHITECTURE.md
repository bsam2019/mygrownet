# Construction & Aluminium Modules Architecture

**Last Updated:** April 24, 2026  
**Purpose:** Define modular structure for construction/aluminium features

---

## 🏗️ Recommended Module Structure

### Core Philosophy
- **Modular Design:** Each module can be enabled/disabled independently
- **Tight Integration:** Modules share data and work together seamlessly
- **Progressive Enhancement:** Start with basic features, add advanced ones later
- **Industry Agnostic Core:** Base features work for all industries, specialized modules add industry-specific functionality

---

## 📦 Module Breakdown

### **Module 1: Material Planning** ✅ (Already Implemented)
**Module ID:** `material-planning`  
**Status:** Backend 100%, Frontend 60%  
**Auto-enabled for:** Aluminium, Construction

**Features:**
- Material library & categories
- Job material planning
- Purchase orders
- Cost tracking & variance analysis
- Price history
- Material templates

**Why Separate:** 
- Can be used by other industries (manufacturing, retail)
- Self-contained functionality
- Already implemented as standalone module

---

### **Module 2: Project Management** 🆕 (NEW)
**Module ID:** `project-management`  
**Status:** Not implemented  
**Auto-enabled for:** Construction, Engineering, Consulting

**Features:**
- Multi-job projects
- Project timelines & Gantt charts
- Milestone tracking
- Site/location management
- Project budgets
- Progress tracking
- Document management (drawings, plans)
- Site diary/daily reports

**Why Separate:**
- Useful beyond construction (consulting, IT projects, events)
- Can work without other construction modules
- Large feature set deserves own module

**Integration Points:**
- Links to Jobs (one project = many jobs)
- Links to Material Planning (project-level material tracking)
- Links to Subcontractors (project assignments)
- Links to Equipment (project allocation)

**Database Tables:**
```
cms_projects
cms_project_milestones
cms_project_documents
cms_project_sites
cms_site_diary_entries
```

---

### **Module 3: Subcontractor Management** 🆕 (NEW)
**Module ID:** `subcontractor-management`  
**Status:** Not implemented  
**Auto-enabled for:** Construction

**Features:**
- Subcontractor database
- Work assignments
- Payment tracking
- Performance ratings
- Insurance & certification tracking
- Subcontractor invoices
- Compliance documents

**Why Separate:**
- Specific to construction/contracting
- Can be disabled for aluminium fabrication shops that don't use subcontractors
- Focused functionality

**Integration Points:**
- Links to Projects (subcontractor assignments)
- Links to Jobs (work allocation)
- Links to Payments (subcontractor payments)
- Links to Expenses (subcontractor costs)

**Database Tables:**
```
cms_subcontractors
cms_subcontractor_assignments
cms_subcontractor_payments
cms_subcontractor_ratings
cms_subcontractor_documents
```

---

### **Module 4: Equipment & Tools** 🆕 (NEW)
**Module ID:** `equipment-management`  
**Status:** Not implemented  
**Auto-enabled for:** Construction, Manufacturing

**Features:**
- Equipment inventory
- Maintenance schedules
- Usage tracking
- Rental management
- Depreciation tracking
- Equipment allocation to jobs/projects
- Service history

**Why Separate:**
- Useful for multiple industries (construction, manufacturing, logistics)
- Can be disabled for service-based businesses
- Self-contained asset management

**Integration Points:**
- Links to Jobs (equipment allocation)
- Links to Projects (equipment usage)
- Links to Expenses (maintenance costs)
- Links to Inventory (consumable parts)

**Database Tables:**
```
cms_equipment
cms_equipment_maintenance
cms_equipment_usage
cms_equipment_rentals
cms_equipment_allocations
```

---

### **Module 5: Labour & Crew Management** 🆕 (NEW)
**Module ID:** `labour-management`  
**Status:** Not implemented  
**Auto-enabled for:** Construction, Manufacturing

**Features:**
- Crew composition
- Daily timesheets
- Labour cost per job
- Productivity tracking
- Skill matrix
- Crew scheduling
- Labour cost analysis

**Why Separate:**
- Extends existing HR/Employee module
- Specific to field work/manual labour
- Can be disabled for office-based businesses

**Integration Points:**
- Links to Employees (crew members)
- Links to Jobs (labour allocation)
- Links to Projects (crew assignments)
- Links to Payroll (labour costs)
- Links to Attendance (timesheet integration)

**Database Tables:**
```
cms_crews
cms_crew_members
cms_labour_timesheets
cms_labour_allocations
cms_skill_matrix
```

---

### **Module 6: Bill of Quantities (BOQ)** 🆕 (NEW)
**Module ID:** `boq-management`  
**Status:** Not implemented  
**Auto-enabled for:** Construction

**Features:**
- BOQ templates
- Item-by-item breakdown
- Quantity surveying
- Rate analysis
- BOQ comparison (tender vs actual)
- BOQ import/export
- Variation tracking

**Why Separate:**
- Highly specialized for construction
- Complex functionality
- Not needed by aluminium fabrication
- Can be optional even for construction

**Integration Points:**
- Links to Quotations (BOQ-based quotes)
- Links to Projects (project BOQ)
- Links to Material Planning (BOQ items → materials)
- Links to Invoices (BOQ-based billing)

**Database Tables:**
```
cms_boq_templates
cms_boq_items
cms_boq_categories
cms_boq_rates
cms_boq_variations
```

---

### **Module 7: Progress Billing & Retention** 🆕 (NEW)
**Module ID:** `progress-billing`  
**Status:** Not implemented  
**Auto-enabled for:** Construction

**Features:**
- Progress certificates
- Retention money tracking
- Stage-based billing
- Variation orders
- Payment applications
- Final account
- Retention release

**Why Separate:**
- Specific to construction payment terms
- Extends existing invoicing
- Complex financial logic
- Not needed by most industries

**Integration Points:**
- Links to Projects (project billing)
- Links to Invoices (progress invoices)
- Links to BOQ (BOQ-based billing)
- Links to Payments (retention tracking)

**Database Tables:**
```
cms_progress_certificates
cms_retention_tracking
cms_payment_applications
cms_billing_stages
cms_variation_orders
```

---

## 🔗 Integration Architecture

### Data Flow Example: Construction Project

```
1. PROJECT CREATED
   ↓
2. BOQ PREPARED (Module 6)
   ↓
3. MATERIALS PLANNED (Module 1)
   ↓
4. SUBCONTRACTORS ASSIGNED (Module 3)
   ↓
5. EQUIPMENT ALLOCATED (Module 4)
   ↓
6. LABOUR/CREW ASSIGNED (Module 5)
   ↓
7. WORK PROGRESSES
   ↓
8. PROGRESS BILLING (Module 7)
   ↓
9. PROJECT COMPLETED
```

### Shared Data Models

**Core Entities (Used by All Modules):**
- Companies
- Customers
- Jobs
- Invoices
- Payments
- Expenses

**Module-Specific Entities:**
- Projects (Module 2)
- Materials (Module 1)
- Subcontractors (Module 3)
- Equipment (Module 4)
- Crews (Module 5)
- BOQ (Module 6)
- Progress Certificates (Module 7)

---

## 🎛️ Module Dependencies

### Independent Modules (No Dependencies)
- Material Planning ✅
- Equipment Management
- Subcontractor Management

### Dependent Modules (Require Other Modules)
- **Labour Management** → Requires HR/Employee module
- **Progress Billing** → Requires Project Management
- **BOQ Management** → Works best with Project Management

### Optional Integrations
- Material Planning ↔ BOQ Management
- Project Management ↔ All modules
- Labour Management ↔ Attendance/Payroll

---

## 📋 Module Enablement Strategy

### Aluminium Fabrication Industry
**Auto-enabled:**
- ✅ Material Planning
- ✅ Measurements
- ✅ Pricing Rules

**Optional:**
- Equipment Management (if they have machinery)
- Labour Management (if they track crew productivity)

**Not Needed:**
- Subcontractor Management
- BOQ Management
- Progress Billing

### Construction Industry
**Auto-enabled:**
- ✅ Material Planning
- ✅ Project Management
- ✅ Subcontractor Management
- ✅ BOQ Management
- ✅ Progress Billing

**Optional:**
- Equipment Management
- Labour Management

---

## 🎯 Implementation Priority

### Phase 1: Foundation (Week 1-2)
1. **Complete Material Planning** (40% remaining)
2. **Project Management** (core features)
   - Projects CRUD
   - Project-Job linking
   - Basic timeline
   - Site management

### Phase 2: Resources (Week 3-4)
3. **Subcontractor Management**
   - Subcontractor CRUD
   - Assignment to projects/jobs
   - Payment tracking
4. **Labour Management**
   - Crew creation
   - Timesheet entry
   - Labour cost tracking

### Phase 3: Advanced (Week 5-6)
5. **Equipment Management**
   - Equipment inventory
   - Allocation tracking
   - Maintenance schedules
6. **BOQ Management** (basic)
   - BOQ templates
   - Item management
   - Rate analysis

### Phase 4: Financial (Week 7-8)
7. **Progress Billing**
   - Progress certificates
   - Retention tracking
   - Stage billing

---

## 💾 Database Schema Strategy

### Shared Tables (Core CMS)
```
cms_companies
cms_customers
cms_jobs
cms_quotations
cms_invoices
cms_payments
cms_expenses
cms_employees
```

### Module-Specific Tables (Prefixed)
```
# Material Planning
cms_materials
cms_material_categories
cms_job_material_plans
cms_material_purchase_orders

# Project Management
cms_projects
cms_project_milestones
cms_project_sites
cms_site_diary_entries

# Subcontractor Management
cms_subcontractors
cms_subcontractor_assignments

# Equipment Management
cms_equipment
cms_equipment_allocations

# Labour Management
cms_crews
cms_labour_timesheets

# BOQ Management
cms_boq_templates
cms_boq_items

# Progress Billing
cms_progress_certificates
cms_retention_tracking
```

---

## 🔐 Permissions Strategy

### Module-Level Permissions
```php
'material-planning.view'
'material-planning.manage'
'project-management.view'
'project-management.manage'
'subcontractor-management.view'
'subcontractor-management.manage'
// etc.
```

### Feature-Level Permissions
```php
'materials.create'
'materials.edit'
'materials.delete'
'purchase-orders.approve'
'projects.create'
'projects.close'
'subcontractors.rate'
// etc.
```

---

## 📱 UI/UX Considerations

### Navigation Structure
```
CMS Dashboard
├── Jobs
├── Projects (Module 2) 🆕
│   ├── All Projects
│   ├── Project Timeline
│   ├── Site Diary
│   └── Documents
├── Materials (Module 1) ✅
│   ├── Material Library
│   ├── Purchase Orders
│   └── Categories
├── Subcontractors (Module 3) 🆕
│   ├── All Subcontractors
│   ├── Assignments
│   └── Payments
├── Equipment (Module 4) 🆕
│   ├── Equipment List
│   ├── Maintenance
│   └── Allocations
├── Labour (Module 5) 🆕
│   ├── Crews
│   ├── Timesheets
│   └── Productivity
├── BOQ (Module 6) 🆕
│   ├── Templates
│   ├── Active BOQs
│   └── Variations
└── Billing (Module 7) 🆕
    ├── Progress Certificates
    ├── Retention
    └── Payment Applications
```

### Module Settings Page
```
Settings → Modules
├── ✅ Material Planning [Toggle]
├── ⬜ Project Management [Toggle]
├── ⬜ Subcontractor Management [Toggle]
├── ⬜ Equipment Management [Toggle]
├── ⬜ Labour Management [Toggle]
├── ⬜ BOQ Management [Toggle]
└── ⬜ Progress Billing [Toggle]
```

---

## ✅ Recommendation

**Answer to Your Question:**

**NO** - These should NOT be one module together. Here's why:

### Separate Modules (Recommended) ✅
**Pros:**
- ✅ Flexibility - Enable only what you need
- ✅ Maintainability - Easier to update individual modules
- ✅ Scalability - Add new modules without affecting existing ones
- ✅ Industry Agnostic - Equipment module useful for manufacturing too
- ✅ Pricing - Can charge per module
- ✅ Performance - Load only enabled modules

**Cons:**
- More complex architecture
- Need good integration layer

### One Monolithic Module ❌
**Pros:**
- Simpler architecture
- Everything in one place

**Cons:**
- ❌ All-or-nothing - Can't disable unused features
- ❌ Bloated - Aluminium shops don't need BOQ
- ❌ Hard to maintain - Changes affect everything
- ❌ Poor performance - Loading unused features
- ❌ Inflexible - Can't adapt to different industries

---

## 🎯 Final Structure

```
Construction & Aluminium Support = 7 Modules

1. Material Planning ✅ (Universal)
2. Project Management 🆕 (Construction focus)
3. Subcontractor Management 🆕 (Construction only)
4. Equipment Management 🆕 (Universal)
5. Labour Management 🆕 (Universal)
6. BOQ Management 🆕 (Construction only)
7. Progress Billing 🆕 (Construction only)
```

**Integration:** All modules integrate through shared core entities (Jobs, Projects, Customers, etc.)

**Enablement:** Auto-enable relevant modules based on industry type, allow manual toggle in settings.

---

**Next Steps:**
1. Implement Project Management module first (foundation for others)
2. Add Subcontractor Management (most requested)
3. Add Labour Management (high ROI)
4. Add Equipment Management
5. Add BOQ Management (construction-specific)
6. Add Progress Billing (construction-specific)

This modular approach gives you maximum flexibility while maintaining tight integration where needed.
