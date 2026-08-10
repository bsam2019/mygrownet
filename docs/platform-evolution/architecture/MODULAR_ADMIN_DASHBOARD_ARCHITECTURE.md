# Modular Platform Admin & Microservices Extraction Architecture

## 1. Overview & Vision
This document defines the architectural standard for MyGrowNet's **Modular Platform Admin Command Center** and **Domain Admin Isolation Strategy**.

As MyGrowNet scales from a monolithic codebase into a suite of modular bounded contexts (with future microservice/standalone service extraction in mind), administrative governance must be decoupled into two distinct tiers:
1. **Tier 1: Central Platform Admin Command Center (`mygrownet.com/admin`)** — Global governance, user identity, app subscriptions, cross-domain metrics, platform revenue, and system operations.
2. **Tier 2: Domain Application Admin Dashboards (`{app}.mygrownet.com/admin` or `mygrownet.com/{module}/admin`)** — Domain-specific administrative capabilities, catalog management, module metrics, and operations owned exclusively by each domain module.

---

## 2. Multi-App Admin Architecture & Boundaries

```
                             ┌───────────────────────────────────┐
                             │  Central Platform Control Hub     │
                             │  App\Http\Controllers\Admin       │
                             │  https://mygrownet.com/admin      │
                             └─────────────────┬─────────────────┘
                                               │ (API / Contract Calls)
                                 ┌─────────────┴─────────────┐
                                 │ PlatformAdminMetricsService │
                                 └─────────────┬─────────────┘
                                               │
      ┌───────────────────────────┬────────────┴──────────────┬───────────────────────────┐
      │                           │                           │                           │
┌─────▼───────────────────┐ ┌─────▼───────────────────┐ ┌─────▼───────────────────┐ ┌─────▼───────────────────┐
│   App\Domain\BizBoost   │ │  App\Domain\StockFlow   │ │ App\Domain\GrowBuilder  │ │   App\Domain\GrowMusic  │
│   - Admin Controllers   │ │  - Admin Controllers   │ │  - Admin Controllers    │ │   - Admin Controllers   │
│   - Admin Routes        │ │  - Admin Routes        │ │  - Admin Routes         │ │   - Admin Routes        │
│   - Domain Vue Layout   │ │  - Domain Vue Layout   │ │  - Domain Vue Layout    │ │   - Domain Vue Layout   │
└─────────────────────────┘ └─────────────────────────┘ └─────────────────────────┘ └─────────────────────────┘
```

### Responsibility Matrix

| Feature / Responsibility | Tier 1: Platform Admin (`routes/admin.php`) | Tier 2: Domain App Admin (`routes/{module}.php`) |
|---|---|---|
| **User Access & Auth** | Global user status, roles, platform permissions, impersonation | Domain-specific roles & permission checks |
| **Organizations & Apps** | Organization catalog, application installations, app licensing | Organization tenant settings within the app |
| **Financials** | Platform receipts, payouts, withdrawal approvals, global revenue | Module revenue, product/service pricing, module billing |
| **Catalog & Content** | Global feature flags & system settings | Module-specific items (e.g. tracks in GrowMusic, stock in StockFlow, templates in GrowBuilder) |
| **Layout & UI** | `AdminLayout.vue` (Universal Platform Hub) | `{Module}AdminLayout.vue` (Domain-specific layout) |

---

## 3. Domain Admin Extraction Blueprint

To prepare for standalone app extraction:

1. **Domain Controller Location**:
   Domain-specific admin controllers reside inside their respective domain bounded contexts:
   - `App\Domain\GrowMusic\Presentation\Http\Controllers\Admin\`
   - `App\Domain\StockFlow\Http\Controllers\Admin\`
   - `App\Domain\GrowBuilder\Http\Controllers\Admin\`
   - `App\Domain\BizBoost\Http\Controllers\Admin\`
   - `App\Domain\BMS\Http\Controllers\Admin\`

2. **Domain Route Declarations**:
   Domain routes are declared in their respective domain route files (`routes/growmusic.php`, `routes/stockflow-admin.php`, `routes/bizboost.php`, etc.), handling both main domain and subdomain routing transparently.

3. **Metrics Provider Contract (`AdminMetricsContract`)**:
   Instead of running cross-module SQL joins in the platform admin controller, each module implements a metrics provider or REST endpoint (`/api/v1/admin/metrics`). The Central Platform Admin calls this service with isolation.

4. **Cross-Subdomain SSO**:
   Single Sign-On is handled centrally via `auth.mygrownet.com` (MyGrowIdentity), guaranteeing seamless session validation across all subdomains.

---

## 4. Extraction Checklist for App Microservices

When an application (e.g., `StockFlow`, `GrowMusic`, `GrowBuilder`) is extracted into an independent microservice:
- [x] Schema & Migration Isolation (`database/migrations/{module}/` with domain prefixes `sa_`, `growmusic_`, `cms_`)
- [x] Admin Controllers inside `app/Domain/{Module}`
- [x] Dedicated UI Views & Layouts inside `resources/js/Pages/{Module}/Admin/`
- [x] Route group in `routes/{module}.php`
- [x] Auth via MyGrowIdentity (`auth.mygrownet.com`)
- [x] Metrics reported via API contract to Platform Admin
