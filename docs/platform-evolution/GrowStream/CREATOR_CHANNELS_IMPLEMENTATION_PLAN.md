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

## Phase CC1 — Data Model & Migrations (Option A: extend existing tables)

**Decision:** Option A — extend the existing tables rather than create parallel `productions`/`episodes` tables. `growstream_video_series` already is the series parent and `growstream_videos` already is the episode list (it has `series_id`, `episode_number`, `season_number`, full upload + moderation status). v3's "extends the existing content record rather than replacing it" is honored literally.

New migrations in `database/migrations/growstream/` (loaded by `GrowStreamServiceProvider`). **SQLite-safe** (test DB runs `migrate:fresh`); follow the existing `id` bigint + `uuid` column convention.

| Migration | Purpose |
|---|---|
| `2026_08_04_000001_add_free_episode_count_to_growstream_video_series_table` | Add `free_episode_count` (int, default 1) to `growstream_video_series` — the per-series free-first-episode setting. Platform-set; not creator-configurable in Phase 1. |
| `2026_08_04_000002_add_channel_slug_to_creator_profiles_table` | Add `channel_slug` (string, unique, nullable) to `growstream_creator_profiles` — the shareable `/c/{slug}` target. |
| `2026_08_04_000003_create_attribution_events_table` | `attribution_events` per v3 §3 — tracking only; `creator_id`, `source`, `visitor_session_id`, `converted_user_id` nullable, `watch_minutes_attributed` default 0. |

**Design notes:**
- `is_free` is **computed**, not stored — `episode_number <= series.free_episode_count`.
- No `productions`, `episodes`, `promo_assets`, `seasons`, or `follows` tables — existing tables cover all of it.
- `growstream_videos.episode_number` already orders episodes within a series (flat list; no season grouping in Phase 1).

**Acceptance:** `migrate` applies on MySQL + SQLite; only 3 additive migrations; no duplicate content tables.

---

## Phase CC2 — Domain Layer

Follow the existing DDD layout in `app/Domain/GrowStream/`. **Option A means no new Production/Episode entities** — the series/episode domain already exists (`VideoSeries`, `Video`). Only the attribution side is genuinely new.

| Component | Notes |
|---|---|
| Entities | `AttributionLink` — rich entity per the established pattern (`create()`/`reconstitute()`, `recordConversion()`, `accumulateWatchMinutes()`, `toArray()`). |
| Value objects | `AttributionSource` (light string VO) — no enum needed for free-text `?src=`. |
| Repository interface | `AttributionLinkRepositoryInterface`. |
| Eloquent model | `AttributionLinkModel` in `Infrastructure/Persistence/Eloquent/`. |
| Eloquent repo impl | in `app/Infrastructure/Persistence/Repositories/GrowStream/`. |
| `VideoSeries` | add `free_episode_count` fillable/cast + `isFreeEpisode(int $episodeNumber)` helper. |
| `CreatorProfile` | add `channel_slug` fillable/cast; helper `channelUrl($source)` returning `/c/{slug}?src={source}`. |

**Acceptance:** interface bound in `GrowStreamServiceProvider`; container resolves each.

---

## Phase CC3 — Domain Services

| Service | Responsibility |
|---|---|
| `AttributionService` | `resolve(creator, source, visitorSessionId)` → insert `attribution_events`; `recordConversion(visitorSessionId, userId)` on sign-up/subscription; `accumulateWatchMinutes(visitorSessionId, minutes)` from watch events. Silent — no read API in Phase 1. |

**Paywall:** extend `AccessControlService::canWatch()` to consult `VideoSeries::isFreeEpisode()` before the subscription check (per v3 §4). Enforcement stays server-side at the Cloudflare signed-URL step.

**Acceptance:** unit-testable; free-episode gating and attribution recording work end-to-end without any UI change.

---

## Phase CC4 — API & Routes (minimal)

Add a small, additive route set under `routes/growstream.php` (namespaced `growstream.*` to satisfy the subdomain route-name guard). No new controller surface beyond what's needed.

| Route | Controller | Notes |
|---|---|---|
| `GET /c/{slug}` | `ChannelController@show` | Public channel/profile landing — **renders the existing Creator Profile page**, just via the shareable URL. Honors `?src=` (passes it to attribution). |
| `POST /api/attribution/resolve` | `AttributionController@resolve` | Records `?src=` + visitor session on landing. |
| `POST /api/attribution/convert` | `AttributionController@convert` | Binds `converted_user_id` on sign-up/subscription. |

**Paywall integration:** `WatchService`/`AccessControlService` free-episode rule is wired here, no new endpoint. No production/episode controllers — the existing series + upload flow already covers content creation.

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

- `AccessControlServiceTest` extension — free-episode rule (`episode_number <= free_episode_count`) + subscription fallthrough.
- `AttributionServiceTest` — resolve, convert, watch-minutes accumulation.
- `VideoSeries` unit tests — `isFreeEpisode()` + `free_episode_count` default 1.
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
