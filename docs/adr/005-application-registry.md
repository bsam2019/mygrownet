# ADR-005: Application Registry (DB Identity + Config Behavior)

**Context:** The platform needs to know what applications exist and how to route to them. Two approaches: store everything in the database (class names, middleware references, etc.) or keep configuration in code.

**Decision:** Use a hybrid approach. The database (`applications` table) identifies what the application is (slug, type, status). The configuration file (`config/platform.php`) determines how it runs (service provider, middleware, Inertia settings, session prefix).

**Consequences:**
- No brittle class name or middleware references in the database
- Adding a new app = DB record + config entry (both required)
- `ApplicationRegistry` caches DB results for 1 hour to reduce queries
- Application metadata (permissions, settings) lives in config, not DB
