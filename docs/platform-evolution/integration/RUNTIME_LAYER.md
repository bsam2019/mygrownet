# Application Runtime Layer

> **Status:** Active  
> **Version:** 1.0  
> **Phase:** 1.3.5 — Platform Core & Runtime Layer  
> **Applies to:** All platform middleware, routing, auth, and caching infrastructure

---

## Architecture Overview

The Runtime Layer sits between Platform Core (domain) and the applications, providing shared infrastructure that all modules depend on.

```
MyGrowNet Platform
     │
     ├── Platform Core            (domain — identity, orgs, integration, settings)
     │
     ├── Runtime Infrastructure   (infrastructure — middleware, routing, auth, cache)
     │    │
     │    ├── Auth Adapters
     │    │    ├── Identity Gateway (auth.mygrownet.com)
     │    │    └── SESSION_DOMAIN (.mygrownet.com)
     │    │
     │    ├── Tenancy Middleware
     │    │    ├── DetectSubdomain
     │    │    ├── ResolveDomainContext
     │    │    ├── ResolvePlatformContext
     │    │    └── SetPlatformContext
     │    │
     │    ├── Routing Gateway
     │    │    ├── Subdomain → Application mapping
     │    │    ├── Custom domain resolution
     │    │    └── URL prefix routing
     │    │
     │    └── Cache Layer
     │         └── CacheKeyHelper (tenant-isolated keys)
     │
     └── Applications             (business domains — StockFlow, BMS, etc.)
```

---

## 1. Auth Adapters

### Identity Gateway (`auth.mygrownet.com`)

The Identity Gateway handles all authentication flows:

- Login / Register
- Password reset
- Email verification
- Two-factor authentication
- Session validation

It serves Blade views (not Inertia) via the `identity.layout` root template. The `DetectSubdomain` middleware marks `auth.mygrownet.com` requests with `$request->attributes->set('identity_gateway', true)` so that `HandleInertiaRequests` skips Inertia processing.

**Route file:** `routes/my-grow-identity.php` (16 routes, no prefix, no subdomain group — served exclusively by `auth.mygrownet.com`)

### SESSION_DOMAIN

Production `.env` setting:

```
SESSION_DOMAIN=.mygrownet.com
```

The leading dot shares the session cookie across all subdomains (bizboost, growmart, stockflow, cms, etc.), allowing cross-subdomain single sign-on.

**Kill switch per app:** `config/platform.php` has `IDENTITY_REDIRECT_{APP}` flags to enable/disable redirecting to the Identity Gateway. When disabled, the app falls back to its own local login.

---

## 2. Tenancy Middleware

### Middleware Stack Order

```
web middleware group
    │
    ├── ... standard Laravel web middleware ...
    │
    ├── DetectSubdomain              ← resolves host → subdomain or custom domain
    ├── ResolveDomainContext         ← looks up domains table → DomainResolution
    ├── SetPlatformContext           ← builds PlatformContext + Inertia shares
    │
    ▼
Route handlers
```

### DetectSubdomain

**File:** `app/Http/Middleware/DetectSubdomain.php`

Resolves the request hostname to determine which application to serve:

| Host Pattern | Branch | Action |
|---|---|---|
| `mygrownet.com` / `www.mygrownet.com` | `main_domain` / `www_domain` | Pass through to main routes |
| `auth.mygrownet.com` | `identity` | Configure URL for Identity Gateway, skip Inertia |
| `geopamu.mygrownet.com` | `geopamu` | Dispatch directly to GeoPamu controller |
| `{company}.stockflow.mygrownet.com` | `stockflow_company` | Look up StockFlow company by subdomain, set `stockflow_company_id` |
| `bizboost.mygrownet.com` | `bizboost` | Configure subdomain URL |
| `zamstay.mygrownet.com` | `zamstay` | Configure subdomain URL |
| `primeedge.mygrownet.com` | `primeedge` | Configure subdomain URL |
| Custom domain | `custom_domain` | Look up `domains` table, render site |

For each subdomain branch, the middleware calls `URL::forceRootUrl()` and `config(['app.url' => ...])` to ensure route generation uses the correct base URL for the subdomain.

### ResolveDomainContext

**File:** `app/Http/Middleware/ResolveDomainContext.php`

Consumes the host resolved by `DetectSubdomain` and looks up the `domains` table to produce a `DomainResolution` value object:

```php
$resolution = $this->domainResolver->resolve($host);
// DomainResolution { type: 'subdomain'|'custom'|'platform', domain: string, organization: ?Organization, shouldAutoLaunch: bool }

$request->attributes->set('domain_resolution', $resolution);
$request->attributes->set('auto_launch', $resolution->shouldAutoLaunch);
```

Then resolves the `WorkspaceContext`:

```php
$context = $this->contextResolver->resolve(
    user: $request->user(),
    domainType: $resolution->type,
    orgHint: $resolution->organization,
    resolution: $resolution,
);
$request->attributes->set('workspace_context', $context);
```

If the domain is not found in the `domains` table (e.g., a direct IP request), it catches `DomainNotFoundException` and sets a fallback context.

### SetPlatformContext

**File:** `app/Http/Middleware/SetPlatformContext.php`

Builds the `PlatformContext` singleton and shares workspace data to Inertia:

1. Retrieves `workspace_context` and `platform_context` from request attributes
2. If the user is authenticated and has a workspace context, shares to Inertia:
   - `workspace.context` — the current workspace context
   - `workspace.platform_context` — the full PlatformContext
   - `workspace.auto_launch` — whether to auto-launch an application
   - `workspace.apps` — available applications (lazy-loaded)
   - `workspace.organizations` — user's organizations (lazy-loaded)

The `platform_context` request attribute is set by `ResolvePlatformContext` middleware (not yet implemented as a standalone middleware — currently the context is built inline within the broader context resolution flow).

---

## 3. Routing Gateway

### Subdomain → Application Mapping

The routing gateway maps hostnames to application controllers:

| Subdomain | Route File | URL Prefix | Name Prefix |
|---|---|---|---|
| `bizboost.mygrownet.com` | `routes/bizboost.php` | `/` | `bizboost.sub.` |
| `growmart.mygrownet.com` | `routes/growmart.php` | `/` | `growmart.sub.` |
| `zamstay.mygrownet.com` | `routes/zamstay.php` | `/` | `zamstay.sub.` |
| `primeedge.mygrownet.com` | `routes/primeedge.php` | `/` | `primeedge.sub.` |
| `{company}.stockflow.mygrownet.com` | `routes/stockflow-subdomain.php` | `/` | `stockflow.sub.` |
| `auth.mygrownet.com` | `routes/my-grow-identity.php` | `/` | — |
| Main domain | `routes/web.php` | `/stock-audit` | `stockflow.*` |
| Main domain | `routes/web.php` | `/primeedge` | `primeedge.*` |

### Custom Domain Resolution

Custom domains (e.g., a client's own domain pointing to their GrowBuilder site) are resolved by looking up the `domains` table:

```php
$domain = Domain::where('domain', $host)->where('is_active', true)->first();
```

The associated application's Blade view is used as the root template.

### StockFlow Dual Routing

StockFlow has two sets of routes:
1. **Main domain prefix:** `routes/stockflow.php` at `/stock-audit` — all CRUD operations
2. **Subdomain routes:** `routes/stockflow-subdomain.php` at `/` for company-specific access

---

## 4. Cache Layer

### CacheKeyHelper

**File:** `app/Domain/Core/Services/CacheKeyHelper.php`

Provides tenant-isolated cache keys to prevent cross-organization cache collisions:

```php
class CacheKeyHelper
{
    public function prefixed(string $key, ?int $organizationId = null): string
    {
        $orgId = $organizationId ?? $this->resolveOrganizationId();

        if ($orgId) {
            return "org:{$orgId}:{$key}";
        }

        return "global:{$key}";
    }

    public function forModule(string $module, string $key, ?int $organizationId = null): string
    {
        $orgPrefix = $organizationId ?? $this->resolveOrganizationId();

        if ($orgPrefix) {
            return "org:{$orgPrefix}:{$module}:{$key}";
        }

        return "global:{$module}:{$key}";
    }
}
```

**Usage:**

```php
// In a service
$helper = app(CacheKeyHelper::class);
$key = $helper->forModule('stockflow', 'items.list', $companyId);

// Results in: "org:7:stockflow:items.list"
// Instead of just: "items.list" (which would collide across tenants)

$items = Cache::remember($key, 3600, fn() => $this->items->all());
```

**Organization ID resolution:** The helper resolves the current organization from `PlatformContextResolver::current()` — so you get automatic tenant isolation without passing the org ID manually in most cases.

---

## StockFlow-Specific Middleware

In addition to the core runtime middleware, StockFlow has its own middleware stack for company-scoped operations:

| Middleware | File | Purpose |
|---|---|---|
| `ResolveStockFlowCompany` | `app/Http/Middleware/ResolveStockFlowCompany.php` | Moves `stockflow_company_id` from request attributes to session |
| `StockFlowCompany` | `app/Http/Middleware/StockFlowCompany.php` | Ensures the stockflow company ID is set in the session |
| `StockFlowPermission` | `app/Http/Middleware/StockFlowPermission.php` | Checks user permissions within a StockFlow company |
| `StockFlowApiAuth` | `app/Http/Middleware/StockFlowApiAuth.php` | Authenticates API requests via `sa_api_keys` table |
| `StockFlowAdminMiddleware` | `app/Http/Middleware/StockFlowAdminMiddleware.php` | Ensures user is a StockFlow admin |

---

## Context Resolution Flow (Full)

```
HTTP Request
    │
    ▼
DetectSubdomain::handle()
    │
    ├── Is auth.mygrownet.com?           → identity_gateway = true, skip Inertia
    ├── Is {company}.stockflow.*?        → stockflow_company_id = X, route to subdomain
    ├── Is bizboost/growmart/zamstay?    → configure subdomain URL
    ├── Is custom domain?                → render GrowBuilder site
    └── Is main domain?                  → pass through
    │
    ▼
ResolveDomainContext::handle()
    │
    ├── domainResolver->resolve($host)   → DomainResolution {type, domain, org, autoLaunch}
    ├── contextResolver->resolve(user,   → WorkspaceContext {type, orgId, appId, locale}
    │       domainType, orgHint)
    └── Attach to request attributes
    │
    ▼
SetPlatformContext::handle()
    │
    ├── Build PlatformContext            → singleton in container
    ├── Share to Inertia                 → workspace.context, platform_context, apps, orgs
    └── Attach platform_context          → request attributes
    │
    ▼
Route Controller
    │
    ├── $request->attributes->get('workspace_context')
    ├── $request->attributes->get('platform_context')
    ├── $request->attributes->get('domain_resolution')
    ├── $request->attributes->get('auto_launch')
    └── $request->attributes->get('stockflow_company_id')  (if StockFlow)
```
