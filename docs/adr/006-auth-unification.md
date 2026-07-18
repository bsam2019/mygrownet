# ADR-006: Authentication Unification (Identity-Provider Agnostic, Deferred)

**Context:** Three separate auth systems exist: `web` guard (users table), `stockflow` guard (sa_users), `primeedge` guard (prime_edge_clients). Each has its own login controller, session, and user table. Users who exist in multiple systems have no linked identity.

**Decision:** Design an identity abstraction (`IdentityProvider` interface) now, but gate the unified auth behind a `PLATFORM_UNIFIED_AUTH` feature flag (default false). Do not switch default auth until validated.

**Consequences:**
- `IdentityProvider` interface supports swapping between Laravel auth, Passport, Keycloak, Auth0, or custom solutions
- Existing auth continues working unchanged
- Unified login at `/platform/login` resolves user across all systems
- Cross-subdomain session resolution via `ResolveSubdomainAuth` middleware + `SESSION_DOMAIN=.mygrownet.com`
- Standalone auth controllers marked `@deprecated` but not removed
