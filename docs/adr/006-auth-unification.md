# ADR-006: MyGrow Identity & Centralized Authentication

**Context:** Three separate auth systems exist: `web` guard (users table), `stockflow` guard (sa_users), `primeedge` guard (prime_edge_clients). Each has its own login page, session, and user table. Applications that need auth build their own — duplicating login, registration, password reset, email verification, and 2FA across the platform.

**Decision:** Build **MyGrow Identity** — a shared authentication service hosted at `auth.mygrownet.com` inside Platform Core — that every application delegates to. Applications must never authenticate users themselves.

```
MyGrow Identity (auth.mygrownet.com)
├── Login, Logout, Register
├── Password Reset, Email Verification
├── Two-Factor Authentication
├── Session/Token Validation
└── Application Redirect
```

**Architecture:**

```
                    auth.mygrownet.com
                           │
                    MyGrow Identity
                    (Platform Core)
                           │
          ┌────────────────┼────────────────┐
          │                │                │
   mygrownet.com     stockflow.      growbuilder.
    /workspace       mygrownet.com   mygrownet.com
    /apps
    /marketing
          │                │                │
          └────────────────┼────────────────┘
                     Shared Session
                 (.mygrownet.com cookie)
```

MyGrow Identity is the single authentication authority for the entire platform — not just another application, but the official identity service that every application trusts.

**Architecture split:**

| Concept | Responsibility | Owned By |
|---|---|---|
| Identity | Who the user is | Platform Core Identity |
| Access | What the user can do | Platform Core Access |
| Authentication | Login, register, verify | MyGrow Identity (Phase 8) |

**Authentication vs Authorization:**

| Authentication (MyGrow Identity) | Authorization (Applications) |
|---|---|
| Login | Application Access |
| Logout | Roles |
| Registration | Permissions |
| Password Reset | Organization Membership |
| Email Verification | Feature Access |
| Two-Factor Authentication | Workspace Resolution |
| Session Management | Data-Level Permissions |
| **Belongs to the Platform** | **Belongs to the Application** |

**Rationale:**
- Applications should not own identity — a new app should not need LoginController, RegisterController, ForgotPasswordController, VerifyEmailController. It should integrate with MyGrow Identity and be done.
- Single login surface (`auth.mygrownet.com/login`) means password reset, email verification, and 2FA are implemented once, tested once, audited once.
- MyGrow Identity is a design pattern, not a microservice — it lives inside the monolith and can be extracted later if needed.
- Follows Google's model: `docs.google.com` → `accounts.google.com` → back to `docs.google.com`.
- The login URL is configurable via `config('platform.identity.login_url')`, so the subdomain can change without touching applications.
- Named "MyGrow Identity" to give the component product identity within the platform, distinct from generic "auth" or "gateway" terminology.

**Identity Gateway Principles:**

1. **Only `auth.mygrownet.com`** serves login, registration, password reset, email verification, and two-factor authentication.
2. **Applications never authenticate users directly.** They may only verify that the platform session is valid.
3. **Applications never maintain independent login pages.** If a user needs to authenticate, the application redirects to `auth.mygrownet.com/login` with a signed `return_url`.
4. **Authentication cookies are issued for `.mygrownet.com`** to enable seamless navigation between applications without repeated logins.
5. **Future OAuth2 or OpenID Connect support** will be implemented inside the Identity Gateway without changing application code.
6. **Default destination:** Platform-originated logins (no `return_url`) go to `mygrownet.com/workspace`. Application-originated logins return to the calling application.

**Non-goals:**
- NOT a microservice extraction — MyGrow Identity lives inside the monolith at `auth.mygrownet.com`
- NOT an OAuth implementation — session-based auth is sufficient; OAuth can be added later via Passport
- NOT a rewrite of working auth — existing auth is redirected through MyGrow Identity, not replaced overnight

**Open redirect protection is mandatory:**
- Every `return_url` must be HMAC-signed by the originating application using a shared signing key (`config('platform.identity.signing_key')`)
- The HMAC payload includes an expiry timestamp — links are rejected after `return_url_ttl` (default 300s)
- The Identity Gateway must verify the signature, check expiry, AND enforce an allow-list (`*.mygrownet.com` by default, configurable) before redirecting back
- Rejected signatures, expired links, or hosts not in the allow-list redirect to `mygrownet.com/workspace` instead — never to an external URL
- This is not optional: consolidating all login onto one endpoint makes it a high-value phishing target

**Rate limiting & abuse prevention (required, not optional):**
- `auth.mygrownet.com/login` uses Laravel's `RateLimiter` — per-IP limits (generous) and per-user throttling (aggressive, < 5 attempts/min)
- Account lockout after N consecutive failures with exponential backoff
- Anomaly detection for cross-account brute force attempts

**Kill switch — per-app feature flag:**
- Each application has `config('platform.identity.app_redirect_enabled.{app_slug}')`; defaults to `false`
- Flipped to `true` per-app only after production validation
- Flipping back to `false` instantly restores legacy auth for that app only

**Token-based validation supports custom domains & mobile:**
- `MyGrowIdentity::validateSession(string $token)` is designed from day one for two modes:
  - **Cookie-based** — browser requests on `.mygrownet.com` subdomains (shared session)
  - **Token-based** — custom domains (e.g., `taradasidental.com`), mobile apps, partner integrations use a signed JWT exchanged after authentication at `auth.mygrownet.com`
- For custom domains: authenticate at gateway → receive short-lived signed JWT → validate against `validateSession()` API — no shared cookie needed

**JWT replay protection:** Since the JWT travels as a URL query param, it can leak into access logs and Referer headers. Two mitigation options:
  - **Single-use nonce (jti):** Unique claim per JWT, tracked in Redis (TTL = JWT lifetime). Replayed `jti` rejected.
  - **POST redirect:** Gateway auto-submits a hidden form delivering the JWT in the request body instead of the query string.
  Decide during staging security review — not a blocker.

**Sanctum coexistence:**
- Phase 5's Public API uses Sanctum tokens; Phase 8 uses cookie/session for browsers
- MyGrow Identity mints both: Sanctum tokens for mobile/API, sessions for browser
- `validateSession()` detects the auth mode from the request and dispatches to the correct validator

**StockFlow guard exit criterion:**
- The `stockflow` guard is removed only after **0 logins via `stockflow` guard for 30 consecutive days in production** — a measurable trigger, not an indefinite "until migration completes"

**Legacy table decommissioning requires dedup:**
- Before dropping `sa_users` or `prime_edge_clients`, run `MergeDuplicateUsers`:
  1. Match by email across `users` and old tables
  2. Relink FKs (StockFlow memberships, records) to the platform user
  3. Flag ambiguous cases (different names, different password hashes) for manual review
  4. Log every action for audit

**Local development:**
- Use `*.mygrow.test` via `/etc/hosts` (or Laravel Herd/Valet) with `SESSION_DOMAIN=.mygrow.test`
- Allow-list includes `*.mygrow.test` in non-production environments

**Authentication Principle (Permanent Architectural Rule):**
> Authentication belongs exclusively to the Platform. Applications may authorize authenticated users but must never authenticate users themselves. Any new application added to MyGrowNet must integrate with MyGrow Identity and must not implement its own login, registration, password reset, or session management.

**Implementation decomposed into 6 sub-phases:**

| Sub-phase | Deliverable | Status |
|---|---|---|
| **8a Foundation** | Remove primeedge guard, ResolveSubdomainAuth, update docs | ✅ Completed |
| **8b Gateway** | Contract, routes, Sanctum integration, rate limiting | ⏳ Pending |
| **8c Rollout** | Redirect middleware (HMAC + expiry), per-app kill switches, deprecate old auth pages | ⏳ Pending |
| **8d StockFlow** | Bridge period, exit criterion (0 logins for 30 days), remove stockflow guard | ⏳ Pending |
| **8e Tables** | MergeDuplicateUsers command, drop sa_users and prime_edge_clients | ⏳ Pending |
| **8f Custom Domains** | JWT exchange flow, replay protection | ⏳ Deferred |

**Consequences:**
- Phase 8 is renamed to "MyGrow Identity & Centralized Authentication"
- All application-owned auth pages (/login, /register, /password/reset) will be deprecated and replaced with redirects to `auth.mygrownet.com`
- Separate user tables (sa_users, prime_edge_clients) may remain temporarily but are no longer the authentication source
- The `primeedge` guard has been removed (no users ever existed)
- The `stockflow` guard remains **temporarily** during migration — StockFlow will eventually use `Platform User → StockFlow Membership → StockFlow Permissions`, not a separate auth guard
- Old auth code is never deleted immediately — it is deprecated, replaced with redirects, validated in production, then removed
- `ResolveSubdomainAuth` middleware removed
- `auth.mygrownet.com` is the single entry point for all authentication on the platform
- `return_url` is HMAC-signed with expiry (5 min TTL) and allow-list enforced — open redirect protection is mandatory
- Rate limiting (per-IP, per-user, anomaly detection) on every auth endpoint — required, not optional
- Per-app `app_redirect_enabled` kill switch — defaults false, flipped only after production validation
- Sanctum tokens are minted by MyGrow Identity (single authority), not by individual applications
- `validateSession()` handles both cookie (browser) and token (mobile/API/custom domain) modes from day one
- StockFlow guard removed only after 0 logins via that guard for 30 consecutive days
- Legacy tables dropped only after `MergeDuplicateUsers` deduplication and manual review
