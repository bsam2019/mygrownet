# Delegation & Employee Portal Architecture

## Executive Summary

MyGrowNet separates **Platform Governance** from **Operational Task Execution** through two decoupled workspaces:

1. **Superadmin Command Center (`/admin/dashboard`)**: The single platform-wide governance context reserved strictly for Superadmins (platform owners).
2. **Employee Portal (`/employee`)**: The dedicated operational workspace for staff members, where administrative functions delegated by Superadmins are dynamically surfaced and executed.

This architecture enforces strict privilege separation: employees never access the Superadmin Command Center. Instead, delegated functions are scoped, injected, and audited directly within the Employee Portal.

---

## 1. High-Level Workspace Architecture

```
┌───────────────────────────────────────────────────────────────────────────┐
│                    SUPERADMIN COMMAND CENTER                              │
│                        (/admin/dashboard)                                 │
├───────────────────────────────────────────────────────────────────────────┤
│ • Platform-wide metrics, module catalog & provisioning                    │
│ • User & Role Management (Spatie / Platform Roles)                        │
│ • Delegation Management (/admin/delegations)                              │
│ • Multi-step Approval Queue (/admin/delegations/approvals)                 │
└─────────────────────────────────────┬─────────────────────────────────────┘
                                      │
                         Assigns Delegated Permission
                                      │
                                      ▼
┌───────────────────────────────────────────────────────────────────────────┐
│                     EMPLOYEE PORTAL WORKSPACE                             │
│                             (/employee)                                   │
├───────────────────────────────────────────────────────────────────────────┤
│ • HR & Task Management (Timesheets, KPI, Leave, Department)               │
│ • Dynamic Injected Navigation (InjectDelegatedNavigation middleware)       │
│ • Scoped Delegated Functions (/employee/delegated/*)                      │
│ • Automatic Audit Logging (CheckDelegatedPermission middleware)          │
└───────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Core Delegation Data Model

Delegations link an active `Employee` record to specific functional permission keys.

### 2.1 Database Schema (`employee_delegations`)

| Column | Type | Description |
|---|---|---|
| `id` | bigint (PK) | Unique delegation ID |
| `employee_id` | bigint (FK) | References `employees.id` |
| `permission_key` | string | Canonical permission slug (e.g. `delegated.finance.view_receipts`) |
| `requires_approval` | boolean | If `true`, actions require Superadmin review before execution |
| `delegated_by` | bigint (FK) | References `users.id` of the Superadmin who assigned the delegation |
| `granted_at` | timestamp | Timestamp when delegation was assigned |
| `expires_at` | timestamp (nullable) | Optional expiration timestamp |

---

## 3. Delegation Permissions & Scope Registry

Delegated permissions are defined in `App\Domain\Employee\Constants\DelegatedPermissions`:

| Domain | Permission Key | Employee Portal Route | Description |
|---|---|---|---|
| **Support** | `delegated.support.handle_tickets` | `/employee/delegated/support` | View and manage support tickets |
| | `delegated.support.respond_tickets` | `/employee/delegated/support/{source}/{id}/reply` | Reply to customer support tickets |
| **Finance** | `delegated.finance.view_receipts` | `/employee/delegated/receipts` | Inspect platform payment receipts |
| | `delegated.finance.view_payments` | `/employee/delegated/payments` | View incoming payment records |
| | `delegated.finance.process_payments` | `/employee/delegated/payments/{id}/process` | Process or verify payment entries |
| | `delegated.finance.view_withdrawals` | `/employee/delegated/withdrawals` | View withdrawal requests |
| | `delegated.finance.process_withdrawals` | `/employee/delegated/withdrawals/{id}/process` | Approve/reject withdrawal requests |
| **User Mgmt** | `delegated.users.view` | `/employee/delegated/users` | View platform user profiles |
| **BGF** | `delegated.bgf.view_applications` | `/employee/delegated/bgf` | View Business Growth Fund applications |
| | `delegated.bgf.review_applications` | `/employee/delegated/bgf/{id}/review` | Review & evaluate BGF applications |
| **Investors** | `delegated.investors.view_messages` | `/employee/delegated/investors/messages` | View investor communications |
| | `delegated.investors.view_documents` | `/employee/delegated/investors/documents` | Inspect legal & financial investor docs |
| **Analytics** | `delegated.analytics.members` | `/employee/delegated/analytics/members` | Access member growth reports |
| | `delegated.analytics.financial` | `/employee/delegated/analytics/financial` | Access platform financial reports |

---

## 4. Middleware & Security Pipeline

Access to delegated functions inside the Employee Portal is enforced by two dedicated middleware classes registered in `bootstrap/app.php`:

### 4.1 `InjectDelegatedNavigation` (`inject.delegated.nav`)

Applied globally to all `/employee/*` routes:

1. Resolves the authenticated user's active `Employee` record.
2. Fetches all active `employee_delegations` via `DelegationService`.
3. Constructs a structured navigation tree containing **only** the categories and items the employee is explicitly permitted to see.
4. Shares `delegatedNavigation` and `hasDelegatedFunctions` with Inertia views.

```php
// Inside InjectDelegatedNavigation.php
Inertia::share('delegatedNavigation', $navItems);
Inertia::share('hasDelegatedFunctions', count($navItems) > 0);
```

### 4.2 `CheckDelegatedPermission` (`delegated:{permission_key}`)

Guards individual routes under `/employee/delegated/*`:

1. Bypasses check if user has `admin` or `superadmin` role.
2. Verifies the user has an `active` employment status in the `employees` table.
3. Checks `DelegationService::hasPermission($employee, $permission)`.
4. If unauthorized, blocks request with `403 Forbidden` or redirects to `/employee/portal/dashboard` with an error toast.
5. If authorized, records an entry in `delegation_usage_logs` containing `employee_id`, `route`, `method`, `url`, and timestamp.

---

## 5. End-to-End Workflows

### 5.1 Delegation Assignment (Superadmin)
1. Superadmin navigates to `/admin/delegations`.
2. Selects an employee, checks desired permission keys, sets optional expiration dates and approval requirement toggles.
3. Form submission calls `DelegationService::grantDelegation(...)`.

### 5.2 Delegated Execution (Employee)
1. Employee logs into the system and navigates to `/employee`.
2. The sidebar dynamically populates an **"Administrative Functions"** section with granted tabs.
3. Employee clicks a tab (e.g. **Withdrawals** at `/employee/delegated/withdrawals`).
4. `CheckDelegatedPermission` validates the key `delegated.finance.view_withdrawals` and logs usage.
5. If processing an action with `requires_approval = true`, the action is submitted to `approval_queue` for Superadmin confirmation.

---

## 6. Architectural Benefits

- **Zero Superadmin Exposure**: Operational staff never enter the Superadmin panel, protecting system-level settings, roles, and global configuration.
- **Dynamic Context**: Employee sidebar automatically adapts based on real-time permission grants.
- **Granular Auditability**: Every delegated action is traceable back to the specific employee and delegating Superadmin.
- **Approval Safety Net**: High-risk actions (withdrawals, refunds, status overrides) can enforce a two-man rule via the built-in approval queue.
