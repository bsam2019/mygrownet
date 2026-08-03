# GrowStream — Creator Channels & Series Implementation Plan

**Version:** 2.0
**Date:** August 2026
**Based on:** `CREATOR_CHANNELS_STRATEGIC_PLAN.md` v3.0 (reconciled with UI Plan Phase 1) + current codebase audit
**Status:** Not started
**Companion docs:** `PRODUCT_STRATEGIC_PLAN.md`, `IMPLEMENTATION_PLAN.md`, `UI_PLAN.md`

---

## Read This First

This plan implements **only what the v3 strategic spec scopes to Phase 1** — three additive behaviors layered onto the completed GrowStream backend, with **no new top-level screens, no navigation changes, and no new creator-facing UI**. The v2 ambition (seasons as structured entities, rich channel pages, creator-facing attribution dashboards) is explicitly deferred and listed in §7 so it isn't lost.

**Phase 1 scope (all additive):**
1. **Free-first-episode paywall** — episode 1 of a series plays without a subscription; the rest gate behind the existing subscribe flow.
2. **Shareable, attribution-aware Creator Profile URL** — `growstream.co/c/{creator-slug}?src=facebook`; the profile page itself is unchanged.
3. **Silent attribution tracking** — source/visitor-session/conversion logged from day one; not surfaced to creators yet.

**Reused, not rebuilt:**
- Cloudflare Stream upload pipeline (incl. tus for files >200MB)
- Creator Profile page + follow/subscribe action (already the "channel" per UI Plan §5)
- Upload metadata step (already has the single trailer field)
- Single-tier subscription + paywall (`AccessControlService`, `WatchService`)
- Watch-time revenue share / payouts (`growstream_creator_earnings`, `growstream_creator_payouts`)
- Creator Studio Dashboard (already shows Views/Watch time/Earnings/status — no UI added here)
- Platform notification system (Phase 3 follow/notify, if built)

---

## Current State Summary (relevant to this plan)

| Area | Existing | Phase 1 gap |
|---|---|---|
| Creator Profile (the "channel") | `growstream_creator_profiles` + Creator Profile page + follow/subscribe | A stable public `slug` for shareable URLs; attribution-aware link handling |
| Content (movies/series) | `growstream_video_series` + `growstream_videos` (upload + moderation status, season/episode numbers) | A `free_episode_count` notion + `episode_number <= free` computation |
| Paywall | `AccessControlService::canWatch()` (subscription-based) | Free-first-episode rule layered on top |
| Attribution | MyGrowNet network-growth referral (different question/consumer) | `attribution_events` table + silent tracking |

---

## Phase CC1 — Data Model & Migrations (flat, UI-compliant)

New migrations in `database/migrations/growstream/` (loaded by `GrowStreamServiceProvider`). **SQLite-safe** (test DB runs `migrate:fresh`); follow the existing `id` bigint + `uuid` column convention for FK consistency (deliberate deviation from the spec's "uuid PK" for a mixed codebase).

| Migration | Purpose |
|---|---|
| `2026_08_04_000001_create_productions_table` | `productions` per v3 §3 — extends the content concept (movie/series), `free_episode_count` default 1. Not a replacement for `growstream_video_series`; it's the Phase 1 model for series-with-free-first-episode. |
| `2026_08_04_000002_create_episodes_table` | `episodes` per v3 §3 — flat ordered list, `season_id` nullable **unpopulated** (reserved, never surfaced). `video_upload_id` FK → `growstream_videos`, reuses upload pipeline. |
| `2026_08_04_000003_create_attribution_events_table` | `attribution_events` per v3 §3 — tracking only; `visitor_session_id` pre-auth, `converted_user_id` nullable, `watch_minutes_attributed`. |
| `2026_08_04_000004_add_channel_slug_to_creator_profiles_table` | Add `channel_slug` (unique, nullable) to `growstream_creator_profiles` — the shareable `/c/{slug}` target. |

**Design notes:**
- `is_free` is **computed**, not stored — `episode_number <= production.free_episode_count`.
- `season_id` column exists but is **never populated or queried** in Phase 1; it's a forward-compat stub only.
- No `promo_assets` table — the existing single trailer field in upload metadata covers Phase 1.
- No `channel_follows` table change — the Creator Profile follow/subscribe already exists.

**Acceptance:** `migrate` applies on MySQL + SQLite; schema matches v3 §3; no new tables for seasons/promo/follows.

---

## Phase CC2 — Domain Layer

Follow the existing DDD layout in `app/Domain/GrowStream/`.

| Component | Notes |
|---|---|
| Entities | `Production`, `Episode` — rich entities per the established `Video`/`VideoSeries` pattern (`create()`/`reconstitute()`, behavior methods, `toArray()`). |
| Value objects | `ProductionType` (enum: movie, series). |
| Repository interfaces | `ProductionRepositoryInterface`, `EpisodeRepositoryInterface`. |
| Eloquent models | `ProductionModel`, `EpisodeModel` in `Infrastructure/Persistence/Eloquent/`. |
| Eloquent repo impls | in `app/Infrastructure/Persistence/Repositories/GrowStream/`. |
| `CreatorProfile` | add `channel_slug` fillable/cast; helper `channelUrl($source)` returning `/c/{slug}?src={source}`. |

**Acceptance:** interfaces bound in `GrowStreamServiceProvider`; container resolves each.

---

## Phase CC3 — Domain Services

| Service | Responsibility |
|---|---|
| `ProductionService` | create production (movie/series), attach episodes, `isFreeEpisode(episode)` = `episode_number <= free_episode_count`, moderation passthrough (reuses existing video moderation). |
| `AttributionService` | `resolve(source, visitorSessionId)` → insert `attribution_events`; `recordConversion(visitorSessionId, userId)` on sign-up/subscription; `accumulateWatchMinutes(eventId, minutes)` from watch events. Silent — no read API in Phase 1. |

**Paywall:** extend `AccessControlService::canWatch()` to consult `ProductionService::isFreeEpisode()` before the subscription check (per v3 §4). Enforcement stays server-side at the Cloudflare signed-URL step.

**Acceptance:** unit-testable; free-episode gating and attribution recording work end-to-end without any UI change.

---

## Phase CC4 — API & Routes (minimal)

Add a small, additive route set under `routes/growstream.php` (namespaced `growstream.*` to satisfy the subdomain route-name guard). No new controller surface beyond what's needed.

| Route | Controller | Notes |
|---|---|---|
| `GET /c/{slug}` | `ChannelController@show` | Public channel/profile landing — **renders the existing Creator Profile page**, just via the shareable URL. Honors `?src=` (passes it to attribution). |
| `GET /c/{slug}/series/{production_slug}` | `ChannelController@showSeries` | Public series page (existing detail layout). |
| `POST /creator/productions` | `ProductionController@store` | Create movie/series (authed creator). |
| `POST /creator/productions/{production}/episodes` | `ProductionController@storeEpisode` | Attach an episode (reuses upload/tus flow). |
| `POST /api/attribution/resolve` | `AttributionController@resolve` | Records `?src=` + visitor session on landing. |
| `POST /api/attribution/convert` | `AttributionController@convert` | Binds `converted_user_id` on sign-up/subscription. |

**Paywall integration:** `WatchService`/`AccessControlService` free-episode rule is wired here, no new endpoint.

**Acceptance:** routes register on the growstream subdomain; `/c/{slug}` renders the existing profile; `?src=` is captured silently.

---

## Phase CC5 — Frontend (no new screens)

- **Creator Profile page** — unchanged visually. Add the shareable-link affordance only if it requires no new component (e.g. a copy-link button beside the existing follow/subscribe). Per v3 §2, the page itself is already the channel.
- **Series detail / episode list** — flat episode list; episode 1 shows as free (computed), later episodes show the existing subscribe CTA on play.
- **Attribution** — landing handler attaches `visitor_session_id` (cookie/localStorage) and fires `/api/attribution/resolve` with `?src=`. No UI surface.

**Acceptance:** a guest can open `/c/{slug}?src=facebook`, watch episode 1 free, hit the paywall on episode 2, and attribution is logged — all on the existing dark-theme screens.

---

## Phase CC6 — Attribution Data Collection (tracking-only)

- `?src=` / session resolved on landing → `attribution_events` row (source, `visitor_session_id`).
- On sign-up/subscription, bind `converted_user_id`.
- Watch events accumulate `watch_minutes_attributed`.
- **No payout, no dashboard in Phase 1** — data collected from day one per v3 §2.3.

**Acceptance:** every creator-profile landing records source + session; conversions and watch-minutes accrue; data queryable for a future Phase 3 dashboard.

---

## Phase CC7 — Tests

- `ProductionServiceTest` — free-episode computation, moderation passthrough.
- `AccessControlServiceTest` extension — free-episode rule + subscription fallthrough.
- `AttributionServiceTest` — resolve, convert, watch-minutes accumulation.
- Route/feature tests for `/c/{slug}` + attribution endpoints.
- **Regression:** confirm all 426 existing GrowStream tests still pass; SQLite-safe migrations.

---

## Explicitly Deferred (Phase 3 candidates — on record)

Per v3 §5. These were in the v2 draft; not built in Phase 1. Added to the UI Plan's existing deferred list (Section 13).

| Feature | Why deferred |
|---|---|
| Season as a structured, UI-visible entity | Already Phase 3 in the UI Plan |
| Rich channel page (verified badge, featured banner, sections) | Conflicts with Creator Profile §5 scope |
| Creator-facing attribution/conversion dashboard | Conflicts with "no audience segmentation" in Creator Studio |
| Multiple promo assets (trailer + clips + BTS as objects) | Single trailer field suffices for Phase 1 |
| Attribution payout/reward mechanics | Data collection now; compensation model once data exists |
| Short-link service (`gs.zm/{code}`) | `?src=` on the profile URL is sufficient |

---

## Open Decisions (from v3 §6)

1. **`free_episode_count` visibility** — read-only (platform-set) in upload UI, or invisible until a creator notices episode 2 is gated? (Affects CC5; default: invisible, platform-set.)
2. **Episode ordering** — drag-and-drop reordering in Creator Studio Phase 1, or upload order until Season structure in Phase 3? (Affects CC5; default: upload order.)
3. **Attribution consent** — confirm no disclosure UI needed for Phase 1 (likely covered by ToS at signup); quick legal check given mobile money + UGC are launch-risk areas. (Affects CC6.)

---

## Prioritized Build Order

```
Sprint 1 (CC1-CC2):  migrations + domain entities/repos + bindings
Sprint 2 (CC3-CC4):  services + paywall rule + minimal API/routes
Sprint 3 (CC5-CC6):  frontend (existing screens, attribution wiring)
Sprint 4 (CC7):      tests + regression
```

---

## Success Criteria

- A viewer can open a creator's shareable `/c/{slug}?src=facebook` link, watch episode 1 free, and hit the existing subscribe CTA on episode 2 — **no new screens, no navigation changes**.
- Attribution events (source, session, conversion, watch minutes) are silently collected from day one.
- All additions follow existing DDD + dark-theme conventions, the subdomain route-name guard, and SQLite-safe migrations.
- The v2 deferred features are documented (Phase 3 candidates), not silently dropped.
