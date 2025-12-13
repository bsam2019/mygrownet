# GrowBiz User Flow & Remaining Features Plan

**Last Updated:** December 12, 2025
**Status:** Implementation Planning

---

## Current User Flow Analysis

### Role-Based Access

**Business Owner/Manager:**
- Full access to all features
- Bottom navigation: Home, Tasks, To-Do, Team, More
- Can manage employees, projects, inventory, appointments
- Access to analytics and reports

**Employee:**
- Limited access based on role
- Bottom navigation: Home, Tasks, To-Do, More (no Team tab)
- Can view assigned tasks and update progress
- Personal to-do list for self-management
- Cannot access: Team management, Inventory (unless assigned), Projects (view only)

### Current Feature Integration

| Feature | Owner Access | Employee Access | Integration Points |
|---------|--------------|-----------------|-------------------|
| Dashboard | Full stats | Personal stats | Entry point |
| Tasks | Create/Assign | View/Update assigned | Core workflow |
| To-Do List | Personal | Personal | Productivity |
| Team | Full CRUD | View only | HR management |
| Projects | Full CRUD | View assigned | Task grouping |
| Inventory | Full CRUD | View only | Stock management |
| Appointments | Full CRUD | View assigned | Scheduling |
| Reports | Full access | Limited | Analytics |

---

## Recommended Flow Improvements

### 1. Dashboard Quick Actions
Add quick action cards on dashboard for:
- Create Task (Owner)
- Add Inventory Item (Owner)
- Book Appointment (Owner)
- View My Tasks (Employee)
- Check Schedule (Employee)

### 2. Cross-Feature Navigation
- From Task → Link to Project
- From Appointment → Create Invoice (GrowFinance)
- From Inventory → Low stock → Create Purchase Order
- From Project → View Gantt/Kanban

### 3. Employee-Specific Features
- My Schedule (appointments assigned to them)
- My Projects (projects they're members of)
- Time Tracking (log hours on tasks)

---

## Remaining Features Implementation

### 1. Simple POS (Point of Sale) - Priority: LOW

**Purpose:** Quick sales recording for retail businesses

**Core Features:**
- Product search/selection from inventory
- Quick sale entry
- Multiple payment methods (Cash, Mobile Money, Card)
- Receipt generation (print/share)
- Daily sales summary
- Cash drawer management
- Shift management

**Database Schema:**
```sql
CREATE TABLE growbiz_pos_sales (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    sale_number VARCHAR(20) UNIQUE,
    customer_id BIGINT NULL,
    customer_name VARCHAR(255) NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    discount_amount DECIMAL(12,2) DEFAULT 0,
    tax_amount DECIMAL(12,2) DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('cash', 'mobile_money', 'card', 'credit', 'split'),
    payment_reference VARCHAR(100) NULL,
    amount_paid DECIMAL(12,2) NOT NULL,
    change_given DECIMAL(12,2) DEFAULT 0,
    status ENUM('completed', 'refunded', 'partial_refund'),
    notes TEXT NULL,
    shift_id BIGINT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE growbiz_pos_sale_items (
    id BIGINT PRIMARY KEY,
    sale_id BIGINT NOT NULL,
    inventory_item_id BIGINT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    discount DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP
);

CREATE TABLE growbiz_pos_shifts (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    employee_id BIGINT NULL,
    opening_cash DECIMAL(12,2) NOT NULL,
    closing_cash DECIMAL(12,2) NULL,
    expected_cash DECIMAL(12,2) NULL,
    cash_difference DECIMAL(12,2) NULL,
    total_sales DECIMAL(12,2) DEFAULT 0,
    total_transactions INT DEFAULT 0,
    started_at TIMESTAMP NOT NULL,
    ended_at TIMESTAMP NULL,
    notes TEXT NULL,
    status ENUM('open', 'closed'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Estimated Effort:** 5-6 days

---

### 2. Payroll Module - Priority: LOW

**Purpose:** Employee salary management and payslip generation

**Core Features:**
- Employee salary setup (basic, allowances, deductions)
- Payroll period management (monthly)
- Automatic calculations (NAPSA, PAYE for Zambia)
- Payslip generation (PDF)
- Bulk payment processing
- Bank payment file export
- Payroll reports

**Database Schema:**
```sql
CREATE TABLE growbiz_salary_structures (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    employee_id BIGINT NOT NULL,
    basic_salary DECIMAL(12,2) NOT NULL,
    housing_allowance DECIMAL(12,2) DEFAULT 0,
    transport_allowance DECIMAL(12,2) DEFAULT 0,
    other_allowances JSON NULL,
    effective_from DATE NOT NULL,
    effective_to DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE growbiz_payroll_periods (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    period_name VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    payment_date DATE NULL,
    status ENUM('draft', 'processing', 'approved', 'paid'),
    total_gross DECIMAL(14,2) DEFAULT 0,
    total_deductions DECIMAL(14,2) DEFAULT 0,
    total_net DECIMAL(14,2) DEFAULT 0,
    employee_count INT DEFAULT 0,
    approved_by BIGINT NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE growbiz_payslips (
    id BIGINT PRIMARY KEY,
    payroll_period_id BIGINT NOT NULL,
    employee_id BIGINT NOT NULL,
    basic_salary DECIMAL(12,2) NOT NULL,
    gross_salary DECIMAL(12,2) NOT NULL,
    allowances JSON NULL,
    deductions JSON NULL,
    napsa_employee DECIMAL(12,2) DEFAULT 0,
    napsa_employer DECIMAL(12,2) DEFAULT 0,
    paye DECIMAL(12,2) DEFAULT 0,
    other_deductions DECIMAL(12,2) DEFAULT 0,
    net_salary DECIMAL(12,2) NOT NULL,
    payment_method ENUM('bank', 'mobile_money', 'cash'),
    bank_account VARCHAR(50) NULL,
    payment_reference VARCHAR(100) NULL,
    paid_at TIMESTAMP NULL,
    status ENUM('draft', 'approved', 'paid'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Zambian Tax Calculations:**
- NAPSA: 5% employee, 5% employer (capped at K1,221.80/month)
- PAYE: Progressive tax brackets (0%, 25%, 30%, 37.5%)

**Estimated Effort:** 6-7 days

---

### 3. Offline Mode - Priority: MEDIUM

**Purpose:** Allow app to work without internet connection

**Implementation Strategy:**

1. **Service Worker Setup**
   - Cache static assets (JS, CSS, images)
   - Cache API responses for offline access
   - Background sync for pending operations

2. **IndexedDB for Local Storage**
   - Store tasks, todos, inventory locally
   - Queue operations when offline
   - Sync when connection restored

3. **Offline-First Architecture**
   - Check network status before API calls
   - Fall back to cached data
   - Show offline indicator
   - Queue mutations for later sync

4. **Conflict Resolution**
   - Last-write-wins for simple conflicts
   - Manual resolution for complex conflicts
   - Version tracking for records

**Key Files to Create:**
- `public/sw.js` - Service Worker
- `resources/js/composables/useOffline.ts` - Offline detection
- `resources/js/services/offlineStorage.ts` - IndexedDB wrapper
- `resources/js/services/syncQueue.ts` - Sync queue management

**Estimated Effort:** 4-5 days

---

## Implementation Priority

### Phase 1: User Flow Improvements (1-2 days)
- [ ] Add quick actions to dashboards
- [ ] Improve cross-feature navigation
- [ ] Add employee-specific views for projects/appointments

### Phase 2: Offline Mode (4-5 days)
- [ ] Service Worker setup
- [ ] IndexedDB storage
- [ ] Sync queue implementation
- [ ] Offline indicator UI

### Phase 3: Simple POS (5-6 days)
- [ ] Database migration
- [ ] POS service and controller
- [ ] Sale entry interface
- [ ] Receipt generation
- [ ] Shift management

### Phase 4: Payroll Module (6-7 days)
- [ ] Database migration
- [ ] Salary structure management
- [ ] Payroll processing
- [ ] Payslip generation
- [ ] Tax calculations

---

## Integration Points

### POS → Inventory
- Auto-deduct stock on sale
- Low stock alerts
- Product lookup

### POS → GrowFinance
- Daily sales journal entry
- Revenue tracking
- Cash reconciliation

### Payroll → GrowFinance
- Salary expense entries
- Tax liability tracking
- Bank payment integration

### Offline → All Features
- Cache critical data
- Queue operations
- Sync on reconnect

---

**Document Owner:** Development Team
**Next Review:** December 19, 2025
