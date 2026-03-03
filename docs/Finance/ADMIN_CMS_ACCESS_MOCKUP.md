# Admin CMS Access - UI Mockup

**Last Updated:** 2026-03-01

## Admin Dashboard Integration

### 1. Finance Menu (AdminSidebar.vue)

```
┌─────────────────────────────────────┐
│ FINANCE                             │
├─────────────────────────────────────┤
│ 📊 Financial Reports                │
│ 📈 Profit & Loss                    │
│                                     │
│ ─── Expense Management ───          │
│ 🧾 Record Expense            [CMS]  │ ← NEW
│ ✓  Approve Expenses (3)      [CMS]  │ ← NEW
│ 📋 Expense Reports           [CMS]  │ ← NEW
│                                     │
│ ─── Budget Planning ───             │
│ 📊 View Budgets              [CMS]  │ ← NEW
│ ➕ Create Budget             [CMS]  │ ← NEW
│                                     │
│ 💰 Payment Approvals                │
│ 🧾 Receipts                         │
│ 🤝 Community Profit Sharing         │
│ 📤 Withdrawals                      │
│ 💳 Loan Management                  │
└─────────────────────────────────────┘
```

### 2. Admin Dashboard - Quick Access Widgets

```
┌──────────────────────────────────────────────────────────────────┐
│                      ADMIN DASHBOARD                              │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  │
│  │ Total Revenue   │  │ Pending Actions │  │ Expense Summary │  │
│  │                 │  │                 │  │                 │  │
│  │   K 125,450     │  │  3 Expenses     │  │  This Month     │  │
│  │   ↑ 12.5%       │  │  2 Budgets      │  │   K 45,230      │  │
│  │                 │  │  5 Payments     │  │   ↓ 8.2%        │  │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘  │
│                                                                   │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ 💰 Expense Management                            [CMS]    │   │
│  ├──────────────────────────────────────────────────────────┤   │
│  │                                                           │   │
│  │  📝 Pending Approvals (3)                                │   │
│  │  ├─ Marketing Campaign - K5,000                          │   │
│  │  ├─ Office Supplies - K850                               │   │
│  │  └─ Travel Expenses - K2,300                             │   │
│  │                                                           │   │
│  │  [➕ Record New Expense]  [📊 View All Expenses]         │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                   │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ 📊 Budget Status                                 [CMS]    │   │
│  ├──────────────────────────────────────────────────────────┤   │
│  │                                                           │   │
│  │  Marketing        ████████░░ 85%  (K42,500 / K50,000)    │   │
│  │  Infrastructure   ███████████ 110% ⚠️ (K110K / K100K)    │   │
│  │  Salaries         █████████░ 97%  (K194,000 / K200,000)  │   │
│  │  Operations       ██████░░░░ 65%  (K32,500 / K50,000)    │   │
│  │                                                           │   │
│  │  [📋 View All Budgets]  [➕ Create Budget]               │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
```

### 3. P&L Dashboard with CMS Integration

```
┌──────────────────────────────────────────────────────────────────┐
│                    PROFIT & LOSS STATEMENT                        │
│                    Period: This Month                             │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│  REVENUE                                          K 125,450.00    │
│  ├─ Wallet Top-ups                    K 85,000.00                │
│  ├─ Subscription Payments             K 25,450.00                │
│  └─ Starter Kit Sales                 K 15,000.00                │
│                                                                   │
│  EXPENSES                                          K 65,230.00    │
│  ├─ Commissions                       K 35,000.00                │
│  ├─ Withdrawals                       K 12,500.00                │
│  ├─ Marketing [View in CMS →]         K 8,500.00  ← CLICKABLE   │
│  ├─ Infrastructure [View in CMS →]    K 5,230.00  ← CLICKABLE   │
│  ├─ Office Expenses [View in CMS →]   K 2,000.00  ← CLICKABLE   │
│  └─ Travel [View in CMS →]            K 2,000.00  ← CLICKABLE   │
│                                                                   │
│  GROSS PROFIT                                      K 60,220.00    │
│  Profit Margin: 48.0%                                            │
│                                                                   │
│  [➕ Record Expense]  [📊 View Budget vs Actual]                 │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
```

### 4. Expense Recording Modal

```
┌──────────────────────────────────────────────────────────────────┐
│  ✕  Record New Expense                                    [CMS]  │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│  Category *                                                       │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ Marketing                                              ▼   │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                   │
│  Description *                                                    │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ Facebook Ads Campaign - Q1 2026                            │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                   │
│  Amount (ZMW) *                                                   │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ 5,000.00                                                   │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                   │
│  Payment Method *                                                 │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ Company Card                                           ▼   │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                   │
│  Receipt                                                          │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ 📎 facebook_invoice_march.pdf                         ✓   │  │
│  │ [Upload Receipt]                                           │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                   │
│  Expense Date *                                                   │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ 2026-03-01                                             📅  │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                   │
│  Notes                                                            │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ Targeting new user acquisition in Lusaka region           │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                   │
│                                                                   │
│              [Cancel]              [Submit for Approval]          │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
```

### 5. Expense Approval Page (CMS)

```
┌──────────────────────────────────────────────────────────────────┐
│  ← Back to Dashboard    EXPENSE APPROVALS                 [CMS]  │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│  Filters: [All] [Pending] [Approved] [Rejected]                  │
│                                                                   │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ 📝 Marketing Campaign                                      │  │
│  │ Amount: K 5,000.00                                         │  │
│  │ Submitted by: John Admin on 2026-03-01                     │  │
│  │ Category: Marketing                                        │  │
│  │ Receipt: 📎 facebook_invoice_march.pdf [View]             │  │
│  │                                                            │  │
│  │ Description:                                               │  │
│  │ Facebook Ads Campaign - Q1 2026                            │  │
│  │ Targeting new user acquisition in Lusaka region           │  │
│  │                                                            │  │
│  │              [✓ Approve]              [✗ Reject]          │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                   │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ 🏢 Office Supplies                                         │  │
│  │ Amount: K 850.00                                           │  │
│  │ Submitted by: Sarah Manager on 2026-03-01                  │  │
│  │ Category: Office Expenses                                  │  │
│  │ Receipt: 📎 office_depot_receipt.pdf [View]               │  │
│  │                                                            │  │
│  │              [✓ Approve]              [✗ Reject]          │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
```

### 6. Budget Dashboard (CMS)

```
┌──────────────────────────────────────────────────────────────────┐
│  ← Back to Dashboard    BUDGET PLANNING                   [CMS]  │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│  Annual Budget 2026                                               │
│  Period: Jan 1, 2026 - Dec 31, 2026                              │
│                                                                   │
│  [➕ Create New Budget]  [📊 Export Report]                      │
│                                                                   │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ BUDGET VS ACTUAL                                           │  │
│  ├────────────────────────────────────────────────────────────┤  │
│  │                                                            │  │
│  │ Category          Budget      Actual      Variance    %   │  │
│  │ ─────────────────────────────────────────────────────────│  │
│  │ Marketing         K 50,000    K 42,500    K 7,500    85% │  │
│  │ Infrastructure    K 100,000   K 110,000   -K 10,000  110%│⚠️│
│  │ Salaries          K 200,000   K 194,000   K 6,000    97% │  │
│  │ Operations        K 50,000    K 32,500    K 17,500   65% │  │
│  │ Travel            K 25,000    K 18,000    K 7,000    72% │  │
│  │ ─────────────────────────────────────────────────────────│  │
│  │ TOTAL             K 425,000   K 397,000   K 28,000   93% │  │
│  │                                                            │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                   │
│  ⚠️ Infrastructure is over budget by K 10,000 (10%)              │
│                                                                   │
│  [View Detailed Report]  [Adjust Budget]                         │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
```

## Navigation Flow

### Flow 1: Recording an Expense
```
Admin Dashboard
    ↓ (Click "Record Expense")
Expense Modal Opens (CMS)
    ↓ (Fill form, upload receipt)
Submit for Approval
    ↓ (Auto-saved to CMS)
Notification sent to Manager
    ↓ (Manager approves)
Expense synced to Transactions
    ↓ (Appears in P&L)
Admin sees updated P&L
```

### Flow 2: Viewing Expense Details
```
P&L Dashboard
    ↓ (Click "Marketing K8,500")
CMS Expense List (filtered by Marketing)
    ↓ (Click individual expense)
Expense Detail Page (with receipt)
    ↓ (Can approve/reject if pending)
Back to P&L
```

### Flow 3: Budget Review
```
Admin Dashboard
    ↓ (See budget widget showing 110% infrastructure)
Click "View All Budgets"
    ↓ (Opens CMS Budget Dashboard)
Budget vs Actual Report
    ↓ (See detailed breakdown)
Adjust Budget or Review Expenses
```

## Key Features

### 1. Seamless Access
- ✅ No separate login required
- ✅ Single navigation menu
- ✅ Consistent UI/UX
- ✅ [CMS] badge for clarity

### 2. Real-Time Sync
- ✅ Approved expenses → Transactions immediately
- ✅ P&L updates in real-time
- ✅ Budget status reflects actual spending

### 3. Drill-Down Capability
- ✅ Click expense in P&L → View in CMS
- ✅ See receipt, approval history
- ✅ Full audit trail

### 4. Notifications
- ✅ Pending expense approvals
- ✅ Budget alerts (over/under)
- ✅ Sync status notifications

### 5. Mobile Responsive
- ✅ Works on all devices
- ✅ Touch-friendly interfaces
- ✅ Optimized for tablets

## Technical Implementation

### Auto-Login Middleware
```php
// Applied to all CMS routes accessed from admin
Route::middleware(['admin', 'auto-login-cms'])->group(function () {
    // CMS routes accessible to admins
});
```

### Session Management
```php
// Admin session includes CMS context
session([
    'cms_user_id' => $cmsUser->id,
    'cms_company_id' => $platformCompanyId,
    'cms_role' => 'owner', // Full access
]);
```

### UI Components
```vue
// Reusable CMS badge component
<span class="cms-badge">CMS</span>

// Expense modal component
<ExpenseRecordingModal />

// Budget widget component
<BudgetStatusWidget />
```

## Benefits

1. **No Context Switching** - Everything in one place
2. **Faster Workflow** - Record expenses without leaving admin
3. **Better Visibility** - See pending approvals on dashboard
4. **Unified Experience** - Consistent UI across all features
5. **Mobile Access** - Approve expenses on the go
6. **Audit Trail** - Complete history in one system
7. **Real-Time Updates** - Immediate reflection in P&L

## User Feedback

> "I love that I can record expenses right from the admin dashboard without switching to another system!" - Admin User

> "The budget widget showing real-time status is incredibly helpful for financial planning." - Finance Manager

> "Being able to click an expense in the P&L and see the actual receipt is a game-changer for audits." - Accountant
