# ADR-007: Event-Driven Cross-Module Communication

**Context:** Modules need to react to changes in other modules (e.g., when an organization is created, StockFlow and CMS need to create corresponding company records). Direct module-to-module queries violate the dependency rule.

**Decision:** Use Laravel events + listeners for cross-module communication. Events are defined in the emitting module or Platform Core. Listeners live in the target module, not in Core.

**Consequences:**
- No module queries another module's tables directly
- Events can be promoted to a queue or message bus later without changing contracts
- Current events: OrganizationCreated, OrganizationArchived, MemberAdded, ApplicationSubscribed
- Each listener is self-contained and independently testable
- Event contracts are documented in the Platform Core, not duplicated per module
