# Event Replay Runbook

> **Status:** Active  
> **Version:** 1.0  
> **Phase:** 7.3.4 — Reliable Event Delivery  
> **Applies to:** Platform operations team

---

## Prerequisites

Before performing any replay operation, verify:

| Check | Command | Expected |
|---|---|---|
| Outbox table exists | `php artisan tinker --execute="Schema::hasTable('event_outbox')"` | `true` |
| Outbox service registered | Check `CoreServiceProvider` or `tinker` | `app(OutboxService::class)` succeeds |
| Queue worker is running | Check supervisor or `php artisan queue:status` | Running |
| Pending events | `php artisan tinker --execute="app(OutboxService::class)->pendingCount()"` | Any number |
| Event names available | `php artisan tinker --execute="app(EventReplayService::class)->availableEventNames()"` | Array of event names |

---

## Commands Reference

### `platform:process-outbox`

Publishes pending events from the transactional outbox.

```
php artisan platform:process-outbox {--batch=50}
```

| Option | Default | Description |
|---|---|---|
| `--batch` | 50 | Number of pending events to process per run |

**Output:**
```
Processing 120 pending events...
Published: 118, Failed: 2
Some events failed. Check logs for details.
```

### `platform:replay-events`

Replays already-published events from the outbox. Useful for recovery, data correction, or onboarding new subscribers.

```
php artisan platform:replay-events {--event=} {--from=} {--to=}
```

| Option | Description |
|---|---|
| `--event` | Filter by event name (e.g., `stockflow.goods_received`) |
| `--from` | Start date in `Y-m-d` format |
| `--to` | End date in `Y-m-d` format |

The command requires confirmation: `Replay X events? (yes/no) [no]:`

**Output:**
```
Replayed: 45, Failed: 0
```

### `platform:clean-outbox`

Archives published events older than N days.

```
php artisan platform:clean-outbox {--days=7}
```

| Option | Default | Description |
|---|---|---|
| `--days` | 7 | Archive events published more than this many days ago |

---

## Recovery Scenarios

### Scenario 1: Lost Events

**Symptom:** A downstream system is missing events (e.g., GrowFinance didn't receive stock movements from StockFlow for a period).

**Root cause:** Worker crashed, queue backlog, or publishing code failed silently.

**Recovery:**

```
1. Check outbox for failed/pending events:
   php artisan tinker --execute="app(OutboxService::class)->failedCount()"
   php artisan tinker --execute="app(OutboxService::class)->pendingCount()"

2. Process pending events first:
   php artisan platform:process-outbox --batch=100

3. If events are already marked 'published' but were never received:
   → Replay the missing time window:
   php artisan platform:replay-events --event=stockflow.goods_received --from=2026-07-25 --to=2026-07-26

4. Verify downstream system has caught up.
```

**Verification:**
- Check `MetricsService` for published event counts
- Query `event_outbox` for the affected time range
- Check downstream system logs for received events

### Scenario 2: Duplicate Events

**Symptom:** A downstream system processed the same event twice (e.g., stock deducted twice for the same sale).

**Root cause:** Event published without inbox idempotency guard, or replay run without checking inbox.

**Recovery:**

```
1. Identify duplicates in downstream system:
   → Check for duplicate event_id values in inbox table
   → Query: EventInbox::where('event_id', $eventId)->count()

2. If inbox pattern is active, duplicates are automatically discarded:
   php artisan tinker --execute="app(InboxService::class)->alreadyProcessed($eventId)"

3. Manually reverse duplicate effects in downstream system:
   → For inventory: create compensating stock movement
   → For finance: create reversing journal entry

4. Check inbox deduplication is working:
   → Verify event_inbox table has unique constraint on event_id
   → Check migration: 2026_07_26_240015_create_event_inbox_table.php
```

**Verification:**
- `EventInbox::where('event_id', $eventId)->count()` should be exactly 1
- Downstream totals reconcile with source of truth

### Scenario 3: Corrupted Data

**Symptom:** Events were published with incorrect payload data (wrong amounts, wrong IDs) due to a code bug.

**Root cause:** Bug in publishing code that has since been fixed.

**Recovery:**

```
1. Identify affected events by time range and event name:
   php artisan tinker --execute="
       use App\Domain\Core\Models\EventOutbox;
       EventOutbox::where('event_name', 'stockflow.goods_received')
           ->whereBetween('created_at', ['2026-07-25 00:00', '2026-07-26 00:00'])
           ->get()->map(fn($e) => ['id' => $e->id, 'payload' => $e->payload]);
   "

2. Fix the corrupted data in the downstream system directly
   (do not replay corrupted events — fix the data first).

3. If the payload in the outbox is wrong:
   → Option A: Update the outbox record with corrected payload
      (requires manual SQL update — use with extreme caution)
   → Option B: Skip replay of corrupted events, process corrective events instead

4. Replay only the corrected/fixed events:
   php artisan platform:replay-events --event=stockflow.goods_received

5. Verify downstream totals match the correct values.
```

### Scenario 4: Worker is Down

**Symptom:** Outbox is filling up with pending events but nothing is being published.

**Root cause:** Queue worker crashed, supervisor not restarting it, or queue connection misconfigured.

**Recovery:**

```
1. Check queue worker status:
   php artisan queue:status
   ❌ OR check supervisor: supervisorctl status

2. Restart the worker:
   php artisan queue:restart
   ❌ OR supervisorctl restart laravel-worker:*

3. If worker can't start, run outbox processing synchronously:
   php artisan platform:process-outbox --batch=200

4. Verify backlog is clearing:
   php artisan tinker --execute="app(OutboxService::class)->pendingCount()"
```

### Scenario 5: Full Backfill for New Subscriber

**Situation:** A new system integration needs to receive all historical events for a given type.

**Recovery:**

```
1. List available event names:
   php artisan tinker --execute="app(EventReplayService::class)->availableEventNames()"

2. Replay all events of the required type:
   php artisan platform:replay-events --event=stockflow.goods_received

3. For large backfills (thousands of events), run in batches by date:
   php artisan platform:replay-events --event=stockflow.goods_received --from=2026-01-01 --to=2026-03-31
   php artisan platform:replay-events --event=stockflow.goods_received --from=2026-04-01 --to=2026-06-30
   php artisan platform:replay-events --event=stockflow.goods_received --from=2026-07-01 --to=2026-07-26

4. Monitor memory usage and queue depth during backfill.
```

---

## Step-by-Step Recovery Workflow

### Standard Recovery Procedure

```
1. ASSESS
   │
   ├── What events are affected?          → Check metrics dashboard or logs
   ├── What time window?                  → Check alert timestamps
   ├── How many events?                   → Count outbox records
   └── Are events pending or published?   → Check status in event_outbox
   │
   ▼
2. STABILIZE
   │
   ├── Process pending events:
   │     php artisan platform:process-outbox
   │
   ├── If failures: check logs
   │     tail -n 100 storage/logs/laravel.log | grep Outbox
   │
   ├── Fix root cause if identified
   │
   └── Verify pending count is zero
   │
   ▼
3. RECOVER
   │
   ├── Lost events: replay published events for the affected window
   ├── Duplicates: verify inbox deduplication; reverse in downstream if needed
   ├── Corrupted: fix downstream data directly; skip corrupted outbox records
   └── Backfill: replay all events of the required type
   │
   ▼
4. VERIFY
   │
   ├── Downstream system data matches source of truth
   ├── No more pending events:
   │     php artisan platform:process-outbox
   ├── AlertService has no active alerts:
   │     php artisan platform:check-alerts
   └── Event counts are normal:
         php artisan tinker --execute="app(MetricsService::class)->getDashboard()"
```

---

## Rollback Procedure

If an event replay causes downstream issues (e.g., duplicate processing that wasn't caught by inbox):

```
1. IDENTIFY affected downstream records
   └── Query the inbox table to find replayed events:
       EventInbox::where('status', 'processed')
           ->where('updated_at', '>=', $replayStartTime)
           ->get()

2. COMPENSATE for each affected event:
   ├── For inventory events: create reversing stock movement
   ├── For financial events: create reversing journal entry
   ├── For notification events: discard (no compensation needed)
   └── For lifecycle events: update state manually if needed

3. CHECK for side effects in all downstream systems

4. UPDATE the replay strategy:
   ├── Was the inbox check skipped? → Ensure InboxService::processIfNew() is used
   ├── Was the event handler not idempotent? → Fix handler
   └── Was the replay too broad? → Narrower date range or event filter next time
```

---

## Important Notes

- **Replay publishes events again** — downstream listeners WILL fire. Ensure idempotent processing (inbox pattern) is active.
- **`platform:process-outbox` marks events as published** after the first successful dispatch. Running it again won't re-publish already-published events — use `platform:replay-events` for that.
- **Replay is not instant** — for large backfills, the replay runs synchronously in the CLI process. Consider batching or running during low-traffic periods.
- **The outbox is append-only** — records are never modified (except status updates). Historical data is preserved for replay.
- **Archived records are deleted** — once `platform:clean-outbox` removes old records, they cannot be replayed. Adjust the retention window if long-term replay capability is needed.
