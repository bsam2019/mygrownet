# ADR-003: Organizations as Canonical Business Entity

**Context:** Multiple modules track business entities: StockFlow has `sa_companies`, CMS has `cms_companies`, BizBoost has `bizboost_businesses`. These represent the same real-world concept (a business/legal entity), but have no shared identity.

**Decision:** Establish `organizations` as the canonical business entity in Platform Core. All existing company tables get a nullable `organization_id` FK. An import command (`platform:import-companies`) creates Organization records from existing data.

**Consequences:**
- Existing company queries continue working unchanged
- New features should reference `organization_id`, not module-specific company IDs
- `organization_id` is nullable (backward-compatible, non-breaking)
- Organization lifecycle events (created, archived) propagate to module-specific tables via event listeners
