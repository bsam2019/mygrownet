# ADR-002: Platform Core as Dependency-Free Kernel

**Context:** Multiple modules need shared capabilities (organizations, applications, identity, routing, permissions, settings). If each module implements these independently, we get fragmentation and integration headaches.

**Decision:** Create a Platform Core (`app/Domain/Core/`) that depends on no module. All modules may depend on the Core. Core owns shared schemas (organizations, applications, settings, roles, user_identities, grow_net_users).

**Consequences:**
- Core tables use no module prefix (no `sa_`, no `cms_`, no `bizboost_`)
- Core migrations in `database/migrations/core/` loaded by `CoreServiceProvider`
- Adding a new module requires no Core changes (register in ApplicationRegistry, add config in `config/platform.php`)
- Cross-module queries are forbidden — use events or Core services instead
