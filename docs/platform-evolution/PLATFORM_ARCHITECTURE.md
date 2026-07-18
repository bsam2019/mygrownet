# MyGrowNet Architecture Phase 1 Instruction

You are working on the existing MyGrowNet Laravel application.

Before writing any code, inspect and understand the existing system.

Do not rewrite the application.
Do not create duplicate authentication systems.
Do not create duplicate user, role, or permission systems.
Do not create microservices.

The goal is to gradually evolve MyGrowNet into a modular platform architecture while preserving all existing functionality.

## Important Architecture Understanding

MyGrowNet is the main platform.

GrowNet is only one module inside MyGrowNet.

The platform contains multiple products:

- GrowNet
- StockFlow
- GrowFinance
- GrowMart
- ZamStay
- BizDocs
- GrowBuilder
- BizBoost
- BMS

The current `/dashboard` is the MyGrowNet platform home.

It is not the GrowNet MLM dashboard.

---

# Phase 1 Objective

Do not migrate existing modules yet.

Do not rewrite business logic.

The first phase is only to introduce the platform foundation.

The focus is:

- Organizations
- Applications
- Workspaces
- Application registry
- Routing foundation

---

# Existing System Audit First

Before creating anything:

Inspect:

- Existing User model
- Existing authentication flow
- Existing roles and permissions implementation
- Existing middleware
- Existing subdomain routing
- Existing module structures
- Existing migrations

Report:

1. Current authentication approach
2. Current role/permission approach
3. Existing tables that can be reused
4. Potential conflicts
5. Recommended migration approach

After the audit report, wait for approval before generating migrations or modifying existing code.

Do not make database changes until this review is complete and approved.

---

# Platform Core Design

Create a Core domain:

```
app/Domain/Core
```

Suggested structure:

```
app/Domain/Core/

Models/
- Organization
- OrganizationBranch
- Department
- Application

Services/
- OrganizationService
- ApplicationRegistry
- WorkspaceResolver

Middleware/

Exceptions/
```

---

# Database Foundation

Create only missing platform tables.

Do not create duplicate tables if equivalent functionality already exists.

## organizations

Fields:

- id
- uuid
- name
- slug
- type
- status
- owner_id
- settings
- timestamps

## organization_branches

Fields:

- id
- organization_id
- name
- code
- address
- status
- timestamps

## departments

Fields:

- id
- organization_id
- name
- status
- timestamps

## organization_members

Fields:

- id
- organization_id
- user_id
- status
- joined_at

Role reference should not be stored here. Roles are application-specific.

Organization membership only establishes that a user belongs to an organization.

Application roles and permissions determine what the user can do inside each application.

## applications

Fields:

- id
- name
- slug
- type
- url
- status
- settings

## organization_applications

Fields:

- id
- organization_id
- application_id
- status
- subscription_started_at
- subscription_ends_at

Purpose: Tracks which applications an organization has access to.

Example:

```
Taradasi Pharmacy:
StockFlow     = active
GrowFinance   = active
```

## user_applications

Fields:

- id
- user_id
- application_id
- relationship_type
- status

Purpose: Tracks personal application relationships.

Example:

```
John Banda:
GrowNet   = member    (relationship_type)
GrowMart  = customer  (relationship_type)
```

## custom_domains

Purpose: Maps custom domains to organizations or applications.

---

# Roles and Permissions Rule

Do not create:

- roles (already exists)
- permissions (already exists)

Use the existing roles and permissions system.

First inspect the current implementation.

If necessary, extend the existing system to support application-aware roles.

Example:

```
John Banda:

StockFlow:     Manager
GrowFinance:   Accountant
BizDocs:       Editor
GrowNet:       Member
```

Roles and permissions must support different roles in different applications.

---

# Application Registry

Create an Application Registry service.

Applications should be stored in the database.

Adding a new product should require adding a database record, not editing middleware.

Seed:

- StockFlow
- BMS
- GrowMart
- ZamStay
- GrowNet
- BizDocs
- BizBoost
- GrowBuilder
- GrowFinance

---

# Workspace Routing

Replace hardcoded subdomain decisions gradually.

Current:

```
DetectSubdomain middleware
```

Future:

```
Workspace Resolver
```

Resolution:

1. Custom domain

```
inventory.taradasi.com
```

2. Organization workspace

```
taradasi.mygrownet.com
```

3. Application domain

```
stockflow.mygrownet.com
```

Do not remove current routing until the replacement is tested.

---

# Phase 1 Restrictions

Do not:

- Create users (already exists)
- Create credentials (already exists)
- Replace Laravel authentication
- Rewrite GrowNet
- Rewrite StockFlow
- Rewrite GrowFinance
- Replace existing roles and permissions
- Change working business logic

After completing Phase 1, stop and provide a review report before continuing.
