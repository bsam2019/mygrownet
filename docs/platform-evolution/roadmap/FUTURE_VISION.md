# MyGrowNet Platform — Future Vision

> **Status:** Design reference — describes capabilities planned for Stages 3–5  
> **Version:** 1.0  
> **Applies to:** All distributed/future-stage planning  
> **Created:** 2026-07-25 — extracted from `PLATFORM_INTEGRATION_ARCHITECTURE.md` §11 and §§23–27 as part of document split (see AGENTS.md for rationale)

This document captures designs and roadmaps that extend beyond the current modular monolith. It is referenced from the main [`PLATFORM_INTEGRATION_ARCHITECTURE.md`](PLATFORM_INTEGRATION_ARCHITECTURE.md) to keep that document focused on what is true today plus the next immediate stage.

---

## 1. Reliable Event Delivery

Financial systems such as GrowFinance require guaranteed event delivery — a published event must never be lost, even if the publishing process crashes after committing its database transaction. This section describes the reliability patterns that will be introduced as the platform matures.

### 1.1 Transactional Outbox

Instead of publishing an event directly inside a database transaction, the pattern is:

```
Application
       │
       ▼
   1. Begin transaction
        ├── Insert business record (e.g., invoice)
        ├── Insert event record into outbox table
   2. Commit transaction                    ← If crash occurs here, no event is lost
   3. Worker reads outbox table             ← Separate process, survives crashes
        └── Publish event to EventBus
   4. Mark outbox record as published
```

This guarantees that an event is never published before its business record is committed, and never lost after commit.

**Outbox table schema (in each application's database):**

```sql
CREATE TABLE {app}_event_outbox (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_name      VARCHAR(255) NOT NULL,
    event_payload   JSON NOT NULL,
    context         JSON NOT NULL,
    status          ENUM('pending', 'published', 'failed') DEFAULT 'pending',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    published_at    TIMESTAMP NULL,
    attempts        TINYINT UNSIGNED DEFAULT 0
);
```

### 1.2 Inbox Pattern (Idempotent Processing)

The receiving side uses an inbox table to guarantee at-most-once processing:

```
EventBus
   │
   ▼
Listener receives event
   │
   ├── Check inbox: has event_id been processed?  ← Idempotency guard
   │     └── If yes: discard
   │
   └── If no:
         ├── Insert event_id into inbox table
         ├── Process event
         └── Commit
```

### 1.3 Event Replay

Applications expose an endpoint to replay historical events from the outbox:

- Platform Core provides an `EventReplayService` that accepts a date range and event name filter
- Applications can rebuild state by replaying events they missed during downtime
- Used for disaster recovery, data correction, and onboarding new subscribers

### 1.4 When Outbox Is Required

| Context | Required? | Rationale |
|---------|-----------|-----------|
| Financial events (invoice, payment, journal) | **Required** | Lost events cause accounting discrepancies |
| Inventory events (stock adjustment, goods received) | **Required** | Lost events cause inventory drift |
| Notification events | Optional | A lost notification is acceptable |
| Audit events | Required | Audit trail must be complete |
| Platform lifecycle events | Required | Org/application state must be consistent |

---

## 2. Evolution Roadmap

### Stage 3 — Distributed Services (Target — Next 12 months)

```
EventBus can dispatch across process boundaries via event transport layer.
Contracts are resolved through an API Gateway or Service Registry.
HealthService monitors live endpoints.
IntegrationRegistry supports remote providers.
Heavy applications (GrowFinance, StockFlow) can run in separate processes.
```

### Stage 4 — Independent Deployments (Target — Next 18+ months)

```
Any application can be deployed as an independent service.
Communication switches from in-memory to network transport transparently.
Zero domain logic changes in consuming applications (infrastructure configuration
changes such as provider endpoint URLs are expected).
New applications can be added without redeploying the Platform Core.
```

---

## 3. Advanced Platform Services

Only after Phases 1–4 are stable:

- API Gateway for remote contract resolution
- Event transport for cross-process dispatch

---

## 4. Key Architectural Decisions (Extended)

| Decision | Rationale |
|----------|-----------|
| Contracts live in the owning application | Platform Core stays small and does not need to know every business domain |
| PlatformContext uses scalar IDs | Keeps payloads small, serializable, and queue-friendly |
| Lifecycle events in Platform Core | Core is the authority on platform state |
| Domain events in applications | Core should not know business concepts |
| Capability-based lookup (not app-name lookup) | Allows replacing applications without changing consumers |
| Application Runtime Layer | Separates infrastructure concerns from domain logic |

> **Note:** These decisions are also listed in the main architecture document. They are reproduced here for completeness when reviewing the future-stage roadmap.
