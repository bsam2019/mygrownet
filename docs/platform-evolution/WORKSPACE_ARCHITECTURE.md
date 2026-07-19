# Workspace Architecture

## 1. The Problem We Are Solving

MyGrowNet has multiple applications serving different audiences and ownership models:

- **Business (B2B, multi-tenant):** Apps where data is owned by an organization — user accesses through company membership
- **Consumer (B2C, MyGrowNet-operated):** MyGrowNet-owned services — user accesses as customer/member
- **Shared (both audiences):** Apps where ownership depends on what the user creates — user switches between personal/business context

The old main platform landing page (`/workspace`) mixed all of these into one flat view. A user opening MyGrowNet needs to immediately understand:

- **Who am I in this system?** (personal member, business owner, employee, admin?)
- **Which products do I have access to?** (subscriptions, company assignments, platform roles)
- **Which context should I act in?** (operate as myself, or as a specific organization?)

## 2. The Architecture Layers

```
MyGrowNet Platform
  (auth, identity, context, roles)
        |
Workspace Layer
  (context resolution, app launcher)
        |
Application Layer
  (individual product dashboards)
```

### Platform Identity Model

Platform administrators operate above tenants. They are not organization members.

```
platform_roles
- id
- user_id (FK)
- role: super_admin | support | finance | developer
- permissions: JSON
```

This is separate from `organization_members`. A user can be a platform admin, an org owner, both, or neither.

### User Profiles

Separate authentication from identity:

```
users
- id
- email
- password
- status: active | suspended

user_profiles
- user_id (FK)
- first_name
- last_name
- phone
- avatar
- country
- timezone
- language
```

A user account handles login. A profile carries identity data used by all apps (GrowNet member info, business employee profiles, marketplace seller info, ZamStay guest profiles).

## 3. Two Entry Paths

### Path A: Platform-First Entry

```
mygrownet.com
    |
Login
    |
mygrownet.com/workspace
    |
Select context:
  Personal → consumer apps + personal shared apps
  Rockshield Investments → business apps + org shared apps
    |
Workspace shows apps for selected context
    |
Click app → application dashboard
```

### Path B: Application-First Entry

For business/org-required apps (e.g., StockFlow, GrowFinance):

```
stockflow.mygrownet.com  (application domain)
    |
Authentication
    |
Resolve:
  domain type = application
  application = StockFlow
  requires_organization_context = true
  user org = Teradasi Limited
    |
Organization Workspace (StockFlow auto-highlighted)
  [StockFlow] ← highlighted
  [GrowFinance]  [BizDocs]  [BizBoost]
    |
"Continue to StockFlow" or click into another app
```

For consumer apps (e.g., GrowNet, GrowMart):

```
grownet.mygrownet.com  (application domain)
    |
Authentication
    |
Platform Services
  Context Resolver
  Global App Switcher
  Session
    |
Resolve:
  domain type = application
  application = GrowNet
  requires_organization_context = false
  context = Personal
    |
GrowNet dashboard (with global app switcher visible)
```

Consumer apps enter personal application context directly, bypassing the organization workspace. But they do **not** bypass the platform — the context resolver, session, and global app switcher still run on every request.

## 4. Entry Point → Context Resolution → Workspace → Application

```
Entry Point (URL)
    |
    v
Domain Resolution
  - application domain (finance.mygrownet.com)
  - organization domain (teradasi.mygrownet.com)
  - platform domain (mygrownet.com)
    |
    v
Application type?
  requires_org_context = true  → resolve org → Org Workspace
  requires_org_context = false → Personal context → app dashboard
    |
    v
Organization type? → resolve org → Org Workspace
    |
    v
Platform type? → Platform Workspace
```

## 5. Workspace Layer — Two Views, Same System

The Workspace Layer is a single system with two views. They share authentication, app switcher, permissions, and context resolution.

**Internal naming:** Workspace Layer. **User-facing names** may differ — e.g., "MyGrowNet Home" for the Platform Workspace and "Company Workspace" for the Organization Workspace — to avoid confusing ordinary users. The route paths (`/workspace`, `/org/{slug}`) remain unchanged.

### Platform Workspace (`/workspace`)

**Purpose:** "Which area of MyGrowNet do I want to enter?"

Lists all contexts available to the user. The primary entry point for users starting at `mygrownet.com`.

### Organization Workspace (`/org/{slug}`)

**Purpose:** "What can I do inside this organization?"

Shows all apps available to the organization, team members, and settings. The entry point for users arriving via organization subdomains or from the Platform Workspace.

```
MyGrowNet Workspace Layer
        |
+-------+--------+
|                |
Platform         Organization
(/workspace)     (/org/{slug})
```

## 6. Workspace vs Dashboard

| Concept | Dashboard | Workspace |
|---|---|---|
| Purpose | Show status and metrics | Launch the right tool in the right context |
| Content | Deep feature data | App grid + organizations + categories |
| Audience | One-user-fits-all | Context-aware per user |
| Action | Monitor | Navigate |

## 7. Core Principle: Context Determines Everything

```
Context
    ↓
Organizations
    ↓
Applications
    ↓
Application dashboard
```

### Context Model (Implicit — No Table)

For MVP, context is **not a database table**. It's an enum in code: `personal` or `organization`. The `ContextResolver` service determines the current context from the session and domain. Future context types (family, community) can be added later without changing the resolver pattern, but do not build the abstraction before it's needed.

## 8. Organization Membership Model

Before apps can be resolved, the organization membership layer must be clear:

```
users
    |
    |  (via organization_members)
    v
organizations
    |
    |  (via organization_applications)
    v
applications
```

### organizations

```
organizations
- id
- name: string
- slug: string (unique)
- type: company | institution | group | family | community (default: company)
- owner_user_id: FK → users (for billing, subscription management, ownership)
- country: string (default: Zambia)
- currency: string (default: ZMW)
- timezone: string (default: Africa/Lusaka)
- language: string (default: en)
- status: pending | trial | active | suspended | cancelled | archived
- settings: JSON
```

The `owner_user_id` identifies who owns the organization. Organization types allow different usage patterns (a church uses GrowFinance differently than a company). Regional fields are essential for accounting and billing localization.

Ownership changes are tracked:
### Organization Lifecycle

```
pending → trial → active → suspended (grace period) → archived
                         ↓
                     cancelled
```

- **pending:** registration started, not yet active
- **trial:** free trial period
- **active:** fully operational
- **suspended:** payment failure or violation (grace period before archive)
- **cancelled:** voluntarily closed
- **archived:** permanently closed, data retained for legal/audit purposes

### organization_members

```
organization_members
- id
- organization_id (FK → organizations)
- user_id (FK → users)
- role: owner | admin | accountant | manager | employee | viewer
- status: active | invited | suspended
- permissions: JSON (future: relational)
```

### Organization members resolve to apps through:

```
User → member of Organization(s) → each Org has subscribed Applications
    → User role filters which apps they see within each Org
    → Workspace shows only accessible apps
```

**Example — Teradasi Limited:**

| Member | Role | Can See |
|---|---|---|
| Samson | owner | All subscribed apps |
| Jane | accountant | GrowFinance, BizDocs |
| John | inventory manager | StockFlow only |

## 9. Domain Routing

### Domains Table

```
domains
- id
- domain: string (unique)
- type: application | organization | platform
- application_id: FK → applications (nullable)
- organization_id: FK → organizations (nullable)
- is_active: boolean
```

### Domain Types

| Type | Example | Behavior |
|---|---|---|
| `application` | `finance.mygrownet.com` | Resolves app. If `requires_org_context=true`, show Org Workspace. If false, show personal dashboard. |
| `organization` | `teradasi.mygrownet.com` | Resolves org. Shows Organization Workspace. |
| `platform` | `mygrownet.com` | Shows Platform Workspace. |

### Domain Resolution Priority

The `domains` table is the **sole routing authority**. The system never guesses applications or organizations from subdomain patterns. Every domain must be explicitly registered.

```
1. Exact match in domains table (covers custom domains)
2. Registered application domain (e.g., finance.mygrownet.com)
3. Registered organization domain (e.g., teradasi.mygrownet.com)
4. Platform domain (mygrownet.com)
5. 404
```

**Important:** If `finance.mygrownet.com` exists in the domains table, it resolves. If someone types `nonexistent.mygrownet.com`, the system returns 404 — it does not guess the application.

### Subdomain Registry

```
Domain                  Type            Targets
─────────────────────────────────────────────────────
mygrownet.com           platform        Platform Workspace

grownet.mygrownet.com   application     GrowNet (no org required)
growmart.mygrownet.com  application     GrowMart (no org required)
growmarket.mygrownet.com application    GrowMarket (no org required)
zamstay.mygrownet.com   application     ZamStay (no org required)

finance.mygrownet.com   application     GrowFinance (org required)
biz.mygrownet.com       application     GrowBiz (org required)
builder.mygrownet.com   application     GrowBuilder (both)
bms.mygrownet.com       application     BMS (org required)
primeedge.mygrownet.com application     PrimeEdge (org required)

teradasi.mygrownet.com  organization    Teradasi Limited
rockshield.mygrownet.com organization   Rockshield Investments
```

## 10. Workspace Layout — Platform Workspace

### User with organizations — Personal context

```
/workspace
--------------------------------

Welcome, Samson

Working as:
[ Personal ▼ ]

────────────────────────────────────
MyGrowNet Services
  [GrowNet]  [GrowMart]  [GrowMarket]  [ZamStay]

────────────────────────────────────
Shared Tools
  [GrowBuilder]  [GrowBackup]

────────────────────────────────────
Organizations
  Rockshield Investments
  ABC Company
  + Create or Join Organization
--------------------------------
```

### User with organizations — Organization context

```
/workspace
--------------------------------

Welcome, Samson

Working as:
[ Rockshield Investments ▼ ]

────────────────────────────────────
Business Tools
  [GrowFinance]  [BizDocs]  [BizBoost]

────────────────────────────────────
Shared Tools
  [GrowBuilder]  [GrowBackup]

────────────────────────────────────
Organization Workspace →
  Team, billing, settings
--------------------------------
```

### User without organizations

```
/workspace
--------------------------------

Welcome, Samson

Working as:
[ Personal ▼ ]

────────────────────────────────────
MyGrowNet Services
  [GrowNet]  [GrowMart]  [GrowMarket]  [ZamStay]

────────────────────────────────────
Shared Tools
  [GrowBuilder]  [GrowBackup]

────────────────────────────────────
+ Create or Join Organization
--------------------------------
```

## 11. Workspace Layout — Organization Workspace

Accessed via `/org/{slug}` or automatically via organization subdomains.

```
/org/rockshield-investments
--------------------------------

Rockshield Investments
[ Continue to StockFlow → ]    ← intended app auto-highlighted

Apps
  [GrowFinance]  [BizDocs]  [GrowBackup]  [BizBoost]
  [StockFlow] ← highlighted as intended

Team
  Samson Banda — Owner
  Jane Doe — Accountant

Settings
  Subscription | Billing | Members | Security
  Plan: Business Pro (3 of 5 seats used)

────────────────────────────────────
Platform →
  Switch organization or return
--------------------------------
```

## 12. Application Inventory

### Consumer (MyGrowNet-operated, no org required)

| App | Description |
|---|---|
| GrowNet | Membership, rewards, team, earnings |
| GrowMart | Buy products |
| GrowMarket | Marketplace |
| ZamStay | Accommodation booking |

### Business (Organization-owned, org required)

| App | Description |
|---|---|
| GrowFinance | Business accounting and finance |
| GrowBiz | Business management |
| BizDocs | Business document and invoice management |
| BizBoost | Business marketing tools |
| StockFlow | Inventory and stock audit |
| PrimeEdge | Advisory services |

### Shared (available in both contexts)

| App | Description |
|---|---|
| GrowBuilder | Website and digital store builder |
| GrowBackup | Cloud storage and backup |

### Legacy Applications (Migration Period — not part of future architecture)

| App | Replacement | Status |
|---|---|---|
| Quick Invoice | BizDocs | Active maintenance, migration in progress. Hidden from new users, existing users retain access. |

## 13. Application Classification

| App | Category | Ownership | Access Model | Context Support | Requires Org | Lifecycle |
|---|---|---|---|---|---|---|
| GrowFinance | business | Organization | organization_members | organization | true | active |
| BizDocs | business | Organization | organization_members | organization | true | active |
| StockFlow | business | Organization | organization_members | organization | true | active |
| BizBoost | business | Organization | organization_members | organization | true | active |
| PrimeEdge | business | Organization | organization_members | organization | true | active |
| GrowBiz | business | Organization | organization_members | organization | true | active |
| GrowBackup | shared | User/Organization | both | both | depends on plan | active |
| GrowNet | consumer | MyGrowNet | customer | personal | false | active |
| GrowMart | consumer | MyGrowNet | customer | personal | false | active |
| GrowMarket | consumer | MyGrowNet | customer | personal | false | active |
| ZamStay | consumer | MyGrowNet | customer | personal | false | active |
| GrowBuilder | shared | User/Organization | both | both | depends on project | active |
| Quick Invoice | shared | User/Organization | both | both | false | legacy |

## 14. Application Registry

### Data Model

```
App
- name: string
- slug: string                         (e.g., "growfinance")
- icon: string
- category: business | consumer | shared
- access_model: customer | organization_members | both
- context_support: personal | organization | both
- requires_organization_context: boolean
- subscription_required: boolean
- lifecycle: active | legacy | retired
- operational_status: online | maintenance | disabled (runtime availability, independent of lifecycle)
- replacement_app_id: UUID | null
- migration_deadline: date | null
- is_visible: boolean
- is_active: boolean
```

**Note:** `roles_allowed` is intentionally excluded from the App model. Permissions are not application properties — they belong to a separate roles system. The App model describes what the app **is**, not who can use it.

### Feature Flags

Control phased rollouts without deploying code:

```
feature_flags
- id
- name: string (e.g., "ai_invoice_generator")
- application_id (FK, nullable for platform-wide flags)
- enabled: boolean
- rules: JSON (e.g., { "organizations": [15, 20], "users": [3] })
```

Default is disabled. Only explicitly enabled organizations/users see the feature.

### Application Roles (MVP)

Instead of embedding roles in the App model, use a separate table:

```
application_roles
- id
- application_id (FK)
- role_name: string (e.g., "owner", "accountant", "viewer")
- permissions: JSON
```

Example for GrowFinance:

| Role | Permissions |
|---|---|
| owner | `{"invoice.create":true, "invoice.delete":true, "report.view":true, "manage_users":true}` |
| accountant | `{"invoice.create":true, "report.view":true, "manage_users":false}` |
| viewer | `{"report.view":true}` |

This avoids every app building its own permission structure while keeping roles simple for MVP. Migrate to full RBAC later.

Routing is handled by the `domains` table, not the `url` field. This allows multiple domains per app (e.g., `finance.mygrownet.com` and a future custom domain). The `domains` table maps a domain to an application or organization with the target route path.

### Registry Tables

- `applications` — defines each app
- `organization_applications` — maps orgs to subscribed apps with plan details:

  ```
  organization_applications
  - organization_id (FK)
  - application_id (FK)
  - plan_id (nullable — different companies may have different plans)
  - status: active | trial | suspended | cancelled
  - starts_at
  - expires_at
  ```

- `user_application_subscriptions` — maps users to consumer/personal app subscriptions (ownership or payment):

  ```
  user_application_subscriptions
  - user_id (FK)
  - application_id (FK)
  - plan_id (nullable)
  - status: active | trial | cancelled
  - expires_at
  ```

  Used for premium GrowNet membership, GrowBackup personal storage, premium ZamStay services.
- `organization_members` — maps users to orgs (role, status). A user's access to business apps flows through organization membership, not direct subscriptions.
- `domains` — maps domains to applications or organizations, with route path.
- `organization_invitations` — pending invitations for new members

### Organization Invitations

Employees and team members join organizations through invitations. Support both new and existing users:

```
organization_invitations
- id
- organization_id (FK)
- invited_user_id: FK → users (nullable — for existing MyGrowNet users)
- email: string (nullable — for new users without an account)
- role: string
- token: string (unique)
- expires_at: timestamp
- status: pending | accepted | expired | revoked
```

**Logic:**
- Existing user (invited_user_id set) → accepts → instant membership
- New user (email set) → registers → auto-creates user → membership

### Invitation Lifecycle

```
1. Owner creates invitation (email, role, token generated)
2. System sends email with acceptance link
3. Recipient clicks link → login or register
4. On login: system validates token, checks expiry
5. organization_members record created with assigned role
6. Invitation marked as accepted
7. User is redirected to Organization Workspace
```

### Resolution Flow

```
1. Resolve domain → application / organization / platform
2. If application domain:
   a. requires_organization_context = false → Personal context → app dashboard
   b. requires_organization_context = true  → resolve available organizations for user
        → if exactly one match: use that organization
        → if multiple matches: ask user via Org Workspace or context picker
        → if no matches: deny access (403)
3. If organization domain → Org Workspace
4. If platform domain → Platform Workspace
5. In Workspace: fetch apps where context_support matches context
6. Filter by lifecycle (hide legacy from new users)
7. Filter by role/permissions within org
8. Return categorized workspace data
```

### Billing and Subscription Model

The workspace controls app access, but billing is a separate layer. The subscription model:

```
billing_accounts
- id
- owner_type: user | organization
- owner_id: FK
- status: active | past_due | cancelled

subscriptions
- id
- billing_account_id (FK)
- application_id (FK)
- plan_id (nullable)
- status: active | trial | cancelled | expired
- billing_cycle: monthly | annual
- started_at
- expires_at

subscription_items
- id
- subscription_id (FK)
- application_id (FK)
- quantity: integer (e.g., seats)
- unit_price
```

Example: Rockshield has one `billing_account` with subscriptions to GrowFinance (Business Pro, 5 seats) and BizDocs (Starter, 3 seats). This model handles trials, upgrades, renewals, and seat-based billing.

### Application Installation Concept

An organization subscribes to an app, then an **installation** is created:

```
application_installations
- id
- organization_id (FK)
- application_id (FK)
- status: provisioning | active | suspended
- settings: JSON (app-specific configuration)
- created_at
```

This separates subscription (who pays) from installation (who uses). An org could subscribe but not yet have an active installation (during provisioning), or have multiple installations for different environments.

**Application data tables** use `organization_id` to scope all records to the installation's organization.

### Application Launch Contract

When the workspace launches an application, it must pass a standard payload so every app behaves consistently:

```
Launch Payload
{
    "application": "growfinance",
    "context_type": "organization",
    "organization_id": 15,
    "organization_slug": "rockshield-investments",
    "user_id": 3,
    "permissions": ["invoice.create", "report.view"],
    "installation_id": 7,
    "installation_settings": { "currency": "ZMW" }
}
```

The application must return a confirmation:

```
Launch Response
{
    "status": "allowed",
    "redirect": "/finance/dashboard",
    "message": null
}
```

Or on failure:

```
{
    "status": "denied",
    "redirect": "/workspace",
    "message": "No active subscription for GrowFinance"
}
```

This contract prevents every app from inventing its own launch logic and ensures consistent context passing across all applications.

## 15. Context Switcher

### Platform Workspace

```
Working as:
[ Samson Banda ▼ ]

  ○ Personal
  ○ Rockshield Investments  ← active
  ○ ABC Company
```

### Organization Workspace

```
Rockshield Investments

[ GrowFinance ▼ ]

Current:
  StockFlow ← intended app

Switch to:
  GrowFinance
  BizDocs
  BizBoost
  Organization Workspace
  MyGrowNet Platform
```

### Context Resolution Detail

**Personal context:**
1. Default MyGrowNet consumer apps (every authenticated user)
2. User subscriptions / individual purchases
3. Shared apps in personal mode
4. Filter by lifecycle and permissions

**Organization context:**
1. Organization subscriptions (from organization_applications)
2. Shared apps in org mode
3. Filter by lifecycle
4. Filter by user role within org (from organization_members)

### Application-First Resolution (Multi-Org Ambiguity)

When a user arrives at an application domain (e.g., `finance.mygrownet.com`) and belongs to multiple organizations, the resolver must choose:

```
1. URL-specific organization context? (e.g., from a query param or subdomain hint)
2. Last used organization for this application?
3. User's default organization preference?
4. If none: redirect to Organization Workspace and ask user to choose
```

This prevents ambiguity. The system never guesses which organization the user means.

### Session Context Shape

Every request should carry the resolved context:

```json
{
    "context_type": "organization",
    "context_id": 15,
    "organization_id": 15,
    "organization_slug": "rockshield-investments",
    "organization_name": "Rockshield Investments",
    "application_id": null
}
```

When the user is inside an application (e.g., StockFlow):

```json
{
    "context_type": "organization",
    "context_id": 15,
    "organization_id": 15,
    "organization_slug": "rockshield-investments",
    "organization_name": "Rockshield Investments",
    "application_id": 5,
    "application_slug": "stockflow"
}
```

Or for personal context:

```json
{
    "context_type": "personal",
    "context_id": null,
    "organization_id": null,
    "organization_slug": null,
    "organization_name": null,
    "application_id": null
}
```

This enables the platform to know exactly who, where, and what app the user is in — critical for notifications, audit logs, and app switching.

## 16. GrowBuilder: The Shared App Pattern

GrowBuilder appears **once** — under **Shared Tools** in both contexts.

- **Personal context active:** `GrowBuilder → Personal Website`
- **Organization context active:** `GrowBuilder → Company Website (Rockshield Investments)`

Context determines mode. There is no duplicate listing.

## 17. Organizations Section

```
Organizations
  Rockshield Investments — GrowFinance, BizDocs, BizBoost
  ABC Company             — GrowFinance
  + Create or Join Organization
```

Conditional — only shown for users with at least one org membership.

## 18. Global App Switcher

Mandatory in every app layout. Without it, users on organization subdomains feel trapped.

```
[ Rockshield Investments ▾ ]

Current:
  GrowFinance

Switch to:
  StockFlow
  BizDocs
  BizBoost
  Organization Workspace
  MyGrowNet Platform
```

Context must travel with the user via session (`SESSION_DOMAIN=.mygrownet.com`) or query parameter.

## 19. Default Landing Destination

After login, the system determines landing context by precedence:

```
1. User's saved preference (workspace_preferences table)
2. Last used context (session)
3. Single org membership AND user is a business user → that organization
4. No orgs → Personal
5. Employee role → assigned organization
```

**Important:** Do not default to an organization context just because the user belongs to one. MyGrowNet is not purely a business SaaS — a user may own a company but still expect to land in Personal context to access GrowNet, GrowMart, etc. The preference system lets them choose, and last-used context provides continuity.

### Workspace Preferences (future)

```
workspace_preferences
- user_id
- default_context_type: personal | organization
- default_organization_id: FK (nullable)
```

## 20. Tenant Security & Data Isolation

Because business apps are multi-tenant, every organization application request must validate a chain of ownership.

### Security Validation Chain

```
Authenticated User
        |
        v
Organization Membership
  - Is user a member of this organization?
  - Is membership active?
        |
        v
Application Subscription
  - Does the organization have an active subscription to this app?
        |
        v
Role Permission
  - Does user's role grant access to this app within the org?
        |
        v
Application Data Access
  - Is the requested data scoped to this organization?
```

### Required Middleware

```
EnsureOrganizationAccess
  - Validates user → organization_members → organization
  - Returns 403 if not a member

EnsureApplicationAccess
  - Validates organization → organization_applications → application
  - Returns 403 if org has no subscription or user role lacks access

EnsureDataScope
  - Validates all queries include organization_id filter
  - Prevents cross-tenant data leaks
```

### organization_id Scope Requirement

Every business application data model **must** include an `organization_id` foreign key. All queries within business apps must filter by `organization_id`.

**Unacceptable:**
```sql
SELECT * FROM invoices WHERE id = 123
```

**Required:**
```sql
SELECT * FROM invoices WHERE id = 123 AND organization_id = ?
```

This applies to all business apps. **Standardize on `organization_id` as the column name.** StockFlow currently uses `sa_company_id` — this should be aliased or migrated to `organization_id` for consistency across the platform.

### Data Ownership Rules

Who owns the data? The answer depends on the app category:

- **Consumer apps:** User owns data. `user_id` scopes all records.
- **Business apps:** Organization owns data. `organization_id` scopes all records. The user is a custodian through membership.
- **Shared apps:** The resource's `owner_type`/`owner_id` determines ownership.

Example — GrowBuilder:
- Personal website: `owner_type=user, owner_id=15`
- Company website: `owner_type=organization, owner_id=5`

### Organization Switching Security

Every context switch must be validated server-side. Never trust `?organization_id=15` from the client alone.

```
Request to switch to organization
    ↓
Look up organization_members
    WHERE user_id = current_user
    AND organization_id = requested
    AND status = active
    ↓
If found → switch context
If not found → 403
```

### Audit Logging (Phase 1)

For business systems, all data-modifying actions must be logged from the start:

```
activity_logs
- user_id
- organization_id
- application_id
- action: string
- model_type: string
- model_id: integer
- old_values: JSON (nullable)
- new_values: JSON (nullable)
- ip_address
- created_at
```

Examples: "Jane changed invoice INV-1005 status from pending to paid", "John adjusted stock quantity for product XYZ". Required for GrowFinance, StockFlow, and BizDocs.

### Future: Platform-Wide Tenant Middleware

A single `EnsureTenantAccess` middleware can replace the per-app checks once all business apps follow the same `organization_id` pattern.

## 21. What the Workspace Does NOT Do

- Does not show feature-level data
- Does not explain what applications do internally
- Does not mix personal and business data
- Does not hardcode app categories
- Does not assign consumer apps per-user
- Does not drop users directly into organization-required business apps without resolving organization context first
- Does not treat legacy apps as future products

## 22. Global Navigation Model

```
                MyGrowNet Platform
               (auth, session, context)
                      |
            ┌─────────┴─────────┐
            │                   │
    Entry Point A          Entry Point B
  mygrownet.com/workspace  app.mygrownet.com
            │              teradasi.mygrownet.com
            │                   │
            └─────────┬─────────┘
                      |
              Domain Resolution
                      |
      +---------------+---------------+---------------+
      |               |               |               |
  Platform       Application     Application     Organization
  domain         (no org)        (org req)       domain
      |               |               |               |
  Platform       Personal        Organization    Organization
  Workspace      dashboard       Workspace       Workspace
                                  (app hl'ed)
                      |               |
              ┌───────┴───────────────┘
              │
        Shared Apps
        GrowBuilder  GrowBackup

    —— Legacy (not in future model) ——
        Quick Invoice → BizDocs migration
```

## 23. Route Structure

| Route | Purpose |
|---|---|---|
| `/workspace` | Platform Workspace |
| `/org/{slug}` | Organization Workspace |
| `/dashboard` | 301 redirect to `/workspace` |
| `/_platform/workspace` | Diagnostic JSON endpoint |

### Platform Admin Workspace

Not required for initial implementation, but reserve the pattern:

| Route | Purpose |
|---|---|
| `admin.mygrownet.com` | Platform administration — organizations, subscriptions, users, billing, system settings |

The Platform Admin Workspace is distinct from user/organization workspaces. It provides MyGrowNet operations staff with oversight across all tenants. Do not mix platform admin routes into user-facing controllers.

### Application Domain Routing (Important)

Application domains like `finance.mygrownet.com` do **not** redirect directly to the app dashboard. For apps where `requires_organization_context = true`, the flow is:

```
finance.mygrownet.com
        |
        v
Organization Workspace (resolve org context first)
        |
        v
GrowFinance dashboard
```

This ensures the tenant is resolved before the user enters the application. No business app should be accessible without an active organization context.

## 24. Future Considerations

### Application Role Permissions

The current `roles_allowed: string[]` works for MVP. As apps grow, a relational table will be needed:

```
application_role_permissions
- id
- application_id (FK)
- role: string
- permissions: JSON
```

Example: GrowFinance roles: `owner` (full), `accountant` (invoices,reports), `viewer` (read_only).

Do not build this until the current permission model limits development.

### Permissions Model (Future)

Storing permissions as JSON blobs is acceptable for MVP but should become relational as business apps mature:

```
roles
- id
- organization_id (FK, nullable for system roles)
- name

permissions
- id
- role_id (FK)
- permission_key: string (e.g., "invoice.create", "report.view")
```

This enables granular access control for apps like GrowFinance, StockFlow, and BizDocs.

### Notifications Layer

Cross-app notification center in the Workspace Layer: new invoices, rewards, stock alerts across all apps.

### Audit Logging

For business systems, all data-modifying actions should be logged:

```
activity_logs
- user_id
- organization_id
- application_id
- action: string
- metadata: JSON
- ip_address
- created_at
```

Examples: "Samson updated invoice INV-1023", "Jane exported financial report", "John adjusted stock quantity".

Not required for workspace implementation, but essential for GrowFinance, StockFlow, and BizDocs.

### Application Instances (Future)

Enterprise SaaS typically evolves into org → application instance → data:

```
Rockshield Investments
    |
GrowFinance Instance
    |
invoices, customers, reports
```

This enables multiple environments (production, sandbox), data migrations, custom settings, and app-specific configuration per org. Not needed for MVP but the architecture should allow it.

### Organization Ownership History

Ownership transfers are rare but should be tracked. Deferred — not needed before launch:

```
organization_ownership_history
- id
- organization_id
- previous_owner_id
- new_owner_id
- transferred_by
- transferred_at
- reason
```

### Organization Slug History

Organization subdomains depend on slugs (e.g., `rockshield.mygrownet.com`). If a company rebrands, slug changes must preserve access. Future table:

```
organization_slug_history
- organization_id
- old_slug
- new_slug
- redirected_at
```

Not required for initial implementation.

### Pinned Apps / Favorites

Shortcuts for frequently used apps. Future `user_app_pins` table.

## 25. Mobile Responsiveness

Single Inertia page with responsive CSS. The current `GrowNet.vue` must be untangled — it conflates the Workspace with GrowNet's dashboard.

## 26. Identity Segmentation (Future)

| Role | Default Context |
|---|---|
| GrowNet member (no orgs) | Personal |
| Business owner | Organization (or last used) |
| Employee (invited) | Assigned organization |
| Platform Admin | MyGrowNet operations — all organizations, subscriptions, system settings |
| Organization Admin | One organization only — members, billing, app subscriptions within that org |

## 27. User Onboarding Flow

The architecture starts after login. The first-time user experience:

```
1. Register (email, password, name)
2. Choose identity:
   - Personal user → set up profile
   - Business owner → create organization
   - Employee → enter invitation code
3. If business owner:
   - Create organization (name, type, country)
   - Select initial apps (e.g., GrowFinance)
   - Invite team members
4. If personal user:
   - See available consumer apps
   - Subscribe or start using defaults
5. Land in Workspace
```

A new user should never see an empty screen. If they have no organizations and no subscriptions, the workspace should guide them to create an organization or explore available apps.

## 28. Implementation Phases

### Phase 1: Foundation

1. `domains` table migration
2. `requires_organization_context`, `lifecycle`, `is_visible` fields on `applications`
3. `organization_members` table (role, status)
4. Domain resolver middleware
5. Context resolver service
6. Organization membership + permission resolution

### Phase 2: Workspace

7. Platform Workspace controller + Vue page (`/workspace`)
8. Organization Workspace controller + Vue page (`/org/{slug}`)
9. Context switcher component (Personal / Org selector)
10. Global App Switcher component (context-preserving)
11. Intended app auto-highlight on application-first entry

### Phase 3: Migration & Polish

12. Untangle `GrowNet.vue` — separate workspace from GrowNet dashboard
13. Quick Invoice → BizDocs migration assistant (after BizDocs is stable)
14. Notifications layer (future)
15. Pinned Apps / Favorites (future)

## 29. Implementation Checklist

- [x] `/workspace` route created
- [x] `/dashboard` → 301 redirect to `/workspace`
- [x] Route name `dashboard` → `workspace`
- [x] All hardcoded `/dashboard` links updated to `/workspace`
- [ ] Phase 1: Foundation
  - [ ] Build `domains` table migration
  - [ ] Add `requires_organization_context`, `lifecycle`, `is_visible` to applications
  - [ ] Build `organization_members` table (FK → organizations, users, role, status)
  - [ ] Build domain resolver middleware
  - [ ] Build context resolver service
  - [ ] Build org membership + permission resolution
- [ ] Phase 2: Workspace
  - [ ] Build Platform Workspace Controller + Vue page
  - [ ] Build Organization Workspace Controller + Vue page
  - [ ] Implement context switcher (Personal / Org selector)
  - [ ] Implement global App Switcher component (context-preserving)
  - [ ] Implement intended app auto-highlight
  - [ ] Consumer apps default-available (no per-user assignment)
  - [ ] Shared apps appear in both contexts
  - [ ] Legacy apps: visible to existing users, hidden from new
  - [ ] Responsive layout (mobile-first grid)
- [ ] Phase 3: Migration
  - [ ] Untangle `GrowNet.vue` from workspace
  - [ ] Quick Invoice → BizDocs migration (post-BizDocs stability)
  - [ ] Notifications layer (future)
  - [ ] Pinned Apps (future)
- [ ] Update AGENTS.md with workspace conventions
