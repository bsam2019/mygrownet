# ADR-001: Modular Monolith over Microservices

**Context:** MyGrowNet operates as a single Laravel monolith with 10+ modules (StockFlow, CMS, GrowMart, BizBoost, etc.). As modules grow independently and the team expands, we need an architecture that allows parallel development without coupling modules together.

**Decision:** Keep a modular monolith architecture. Do not extract microservices unless operational data proves a scaling bottleneck.

**Consequences:**
- Modules communicate through shared Platform Core services and events, not direct DB queries
- Each module owns its schema, business rules, services, events, and UI
- Extraction to microservice is possible later (each module is already bounded) but not done preemptively
- Simpler deployment, no network overhead, no distributed transaction complexity
