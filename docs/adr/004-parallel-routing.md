# ADR-004: Parallel Routing Migration (RoutingEngine + DetectSubdomain)

**Context:** The existing `DetectSubdomain` middleware uses a hardcoded switch statement that requires editing every time a new subdomain is added. A new `RoutingEngine` middleware was designed to resolve workspaces from `config/platform.php` instead.

**Decision:** Run RoutingEngine in parallel with DetectSubdomain during migration. RoutingEngine logs its decision without acting on it. DetectSubdomain continues as the active routing middleware.

**Consequences:**
- No risk of routing breakage during migration
- Adding a new subdomain = config entry (`config/platform.php`) + DB record — no middleware edit
- Full replacement happens only after RoutingEngine is validated in production
- Diagnostic route `/_platform/workspace` available for verification
