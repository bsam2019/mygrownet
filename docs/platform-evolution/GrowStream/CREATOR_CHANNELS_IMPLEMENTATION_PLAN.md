# GrowStream — Creator Channels & Series Implementation Plan

**Version:** 1.0
**Date:** August 2026
**Based on:** `CREATOR_CHANNELS_STRATEGIC_PLAN.md` v2.0 + current codebase audit
**Status:** Not started
**Companion docs:** `PRODUCT_STRATEGIC_PLAN.md`, `IMPLEMENTATION_PLAN.md`, `UI_PLAN.md`

---

## Read This First

This plan extends the **completed** GrowStream backend (Phases 1–4 in `IMPLEMENTATION_PLAN.md`: Cloudflare Stream provider, tiered creator vetting, self-service upload, moderation queue, watch-time payouts, creator subscriptions, tips, rentals, sponsorship) with the **Creator Channel model** from the v2 strategic spec.

The v2 spec's key structural change: **the Creator Channel is the permanent unit** — series, seasons, movies, specials, music, and promo clips all live inside one channel per Tier-1-verified creator. This plan treats that as the primary data model while reusing the existing upload/processing/payout pipeline unchanged where possible.

**Deliberately reused (no new work):**
- Cloudflare Stream upload pipeline (incl. tus for files >200MB)
- Tier-1 creator vetting (`CreatorProfileService`, `growstream_creator_profiles.status`)
- Video upload/processing/moderation (`growstream_videos.upload_status`, `moderation_status`, `ProcessVideoJob`)
- Watch-time revenue share / payouts (`growstream_creator_earnings`, `growstream_creator_payouts`)
- Subscription/paywall gating (`AccessControlService`, `WatchService`)

---

## Current State Summary (relevant to this plan)

| Area | Existing | Gap for channels |
|---|---|---|
| Creator profiles | `growstream_creator_profiles` (status, tier, channel_name) | No permanent public channel object / slug / follows |
| Series | `growstream_video_series` (title, slug, seasons count, episodes) | Not scoped to a channel; no per-production review status |
| Episodes | `growstream_videos` (creator_id, series_id, season/episode number, upload + moderation status) | No `production_id`/free-flag attribution to a channel |
| Promo | `growstream_videos` with content_type | No first-class trailer/clip objects tied to a production |
| Follows | none | No channel follow table |
| Attribution | MyGrowNet referral identity | No source-attribution (`?src=`) tracking for creator audiences |

---

## Phase CC1 — Data Model & Migrations

New migrations live in `database/migrations/growstream/` (loaded by `GrowStreamServiceProvider`).

| Migration | Purpose |
|---|---|
| `2026_08_04_000001_create_creator_channels_table` | `creator_channels` per spec §4. One per Tier-1 verified creator. `slug` unique, `creator_id` unique FK. |
| `2026_08_04_000002_create_productions_table` | `productions` per spec §4 — generic parent (series/movie/comedy_special/music_release) with `status` review enum + `free_episode_count` default 1. |
| `2026_08_04_000003_create_production_seasons_table` | `production_seasons` (season_number, title) — replaces reliance on `growstream_video_series.total_seasons`. |
| `2026_08_04_000004_add_production_columns_to_growstream_videos_table` | Add `production_id`, `production_season_id`, `episode_number` (nullable), `is_free` (bool) to existing `growstream_videos` — reuses upload pipeline. |
| `2026_08_04_000005_create_promo_assets_table` | `promo_assets` (type trailer/clip/behind_the_scenes, `video_upload_id` FK to `growstream_videos`, `always_free` default true). |
| `2026_08_04_000006_create_channel_follows_table` | `channel_follows` (channel_id, user_id, unique pair). |
| `2026_08_04_000007_create_attribution_links_table` | `attribution_links` per spec §4 (source, short_code, visitor_session_id, converted_user_id, watch_minutes_attributed). |

**Design notes:**
- `productions` uses a single generic table per the spec's resolved decision; movies/specials are `productions` with a single `growstream_videos` row and no season (mirrors the "episodes with season_id = null" approach via `production_season_id = null`).
- `growstream_video_series` is kept for backwards-compat/legacy content but new channel content uses `productions` → `production_seasons` → `growstream_videos`. A `channel_id` FK is added to `growstream_video_series` in the same migration for legacy series tied to a channel.
- All IDs follow the existing codebase convention (`id` bigint + `uuid` where a public slug/identity matters); the spec's uuid suggestion is honored for channel/production `uuid` columns while keeping `id` PKs consistent with every existing GrowStream table.

**Acceptance:** `migrate` applies cleanly on MySQL and SQLite (test DB); FK graph matches spec §4.

---

## Phase CC2 — Domain Layer (entities, value objects, repositories)

Follow the existing DDD layout in `app/Domain/GrowStream/`.

| Component | Notes |
|---|---|
| Entities | `CreatorChannel`, `Production`, `ProductionSeason`, `PromoAsset`, `ChannelFollow`, `AttributionLink` — rich entities mirroring the pattern used by `Video`, `VideoSeries` (private constructor, `create()`/`reconstitute()`, behavior methods, `toArray()`). |
| Value objects | `ChannelSlug`, `ProductionType` (enum), `ProductionStatus` (enum), `PromoAssetType` (enum), `ReleaseCadence` (enum). |
| Repository interfaces (11 new) | `CreatorChannelRepositoryInterface`, `ProductionRepositoryInterface`, `ProductionSeasonRepositoryInterface`, `PromoAssetRepositoryInterface`, `ChannelFollowRepositoryInterface`, `AttributionLinkRepositoryInterface` + queries for review queues and channel home feeds. |
| Eloquent models | `CreatorChannelModel`, `ProductionModel`, `ProductionSeasonModel`, `PromoAssetModel`, `ChannelFollowModel`, `AttributionLinkModel` in `app/Domain/GrowStream/Infrastructure/Persistence/Eloquent/`. |
| Eloquent repo impls | one per interface in `app/Infrastructure/Persistence/Repositories/GrowStream/`. |

**Acceptance:** all interfaces bound in `GrowStreamServiceProvider`; container resolves each.

---

## Phase CC3 — Domain Services

| Service | Responsibility |
|---|---|
| `ChannelService` | `createChannel(creator, data)` (one per verified creator, post-Tier-1), `getBySlug`, `isFollowing`, `follow`/`unfollow`, channel home payload. |
| `ProductionService` | `applyToPublish(production)` → status `pending_review`; `reviewDecision(production, approve/reject/needs-info)` with reason; enforces rights declaration. Separates creator trust from per-production review. |
| `PromoService` | add trailer/clip/behind-the-scenes under a production; `always_free` default true. |
| `AttributionService` | resolve `?src=`/short_code on landing → create `attribution_links` with `visitor_session_id`; bind `converted_user_id` on sign-up/subscription; accumulate `watch_minutes_attributed` from watch events. |
| `ChannelAnalyticsService` | source breakdown, conversions, watch-time per channel (Phase 2 dashboard data). |

Reuse `VideoManagementService` for upload/moderation and `WatchService` for the paywall.

**Acceptance:** unit-testable; no Eloquent imports in services beyond the established data-service boundary.

---

## Phase CC4 — API Endpoints & Controllers

Add a new controller set under the existing `routes/growstream.php` (web, authed) and/or `api.php` (sanctum) per spec §5, with namespaced names (`growstream.channel.*`, `growstream.production.*`, etc.) to satisfy the subdomain route-name guard.

| Route | Controller |
|---|---|
| `POST /creator/channel` | `ChannelController@store` (create after Tier-1) |
| `GET /c/{slug}` + `GET /channel/{slug}` | `ChannelController@show` (public channel home) |
| `POST /channels/{channel}/productions/apply` | `ProductionController@apply` |
| `GET /admin/productions/pending-review` | `ProductionController@pendingReview` (admin) |
| `POST /productions/{production}/review-decision` | `ProductionController@reviewDecision` (admin) |
| `POST /productions/{production}/seasons` | `ProductionSeasonController@store` |
| `POST /seasons/{season}/episodes` | `ProductionEpisodeController@store` (reuses upload/tus flow) |
| `GET /c/{slug}/series/{production_slug}` | `ChannelController@showSeries` (public) |
| `POST /productions/{production}/promo-assets` | `PromoAssetController@store` |
| `POST /channels/{channel}/follow` | `ChannelController@follow` |
| `POST /api/attribution/resolve` | `AttributionController@resolve` |
| `GET /creator/channel/analytics` | `ChannelAnalyticsController@index` |

**Paywall:** extend `AccessControlService::canWatch()` to consult `production.free_episode_count` — `episode.is_free` computed as `episode_number <= free_episode_count` (or promo `always_free`). Enforcement stays server-side at the Cloudflare signed-URL step.

**Acceptance:** all routes register on the growstream subdomain; review queue and channel home render real data.

---

## Phase CC5 — Admin Review Queue (Production-level)

Add `Admin/Productions.vue` (or extend `Admin/Moderation.vue`) — a production-level queue where each entry (series/movie/special/release) carries its episodes' moderation state, with **approve / reject / needs-info** and a rights-declaration record. This is the spec §3 separation made operational: creator already vetted, but every production is reviewed.

Reuse the existing `gs-*` dark theme and `AdminLayout` from the UI work.

**Acceptance:** admin can review productions independently of creator trust level; rejection reason recorded and surfaced to the creator.

---

## Phase CC6 — Creator Channel UI (Creator Studio)

- **Create channel** — post-Tier-1 wizard (display name, slug, tagline, avatar/banner).
- **Channel home** — creator sees their channel as viewers see it, with productions grouped by type.
- **New production flow** — pick type (series/movie/comedy special/music release) → title/synopsis/category/poster → rights declaration → submit for review.
- **Series builder** — seasons + episodes; each episode uses the existing 3-step upload (tus for files) → per-episode moderation.
- **Promo clips** — add trailers/clips under a production.
- **Attribution links** — creator copies their `?src=facebook` / `?src=tiktok` / short link for each social channel (Phase 1: link shown, no payout).

**Acceptance:** a verified creator can stand up a channel, add a series with episodes, attach a trailer, and generate attribution links without touching admin.

---

## Phase CC7 — Viewer Channel Experience (dark theme)

- **Channel page** (`/c/{slug}`) — banner, avatar, verified badge, follows, production grid, episodes.
- **Channel follow** — button + follow count; Phase 2 notification on new episode.
- **Episode paywall** — `episode.is_free` vs. subscription gate, subscribe CTA tagged to channel/production.
- **Promo/trailer playback** — always-free, prominent on the channel page.

**Acceptance:** a guest can browse a channel, watch the free episode + trailer, and hits the subscribe CTA on premium episodes.

---

## Phase CC8 — Attribution Tracking (tracking-only in Phase 1)

- `?src=` / short-code resolved on landing (`AttributionService::resolve`) → `attribution_links` row with `visitor_session_id`.
- On sign-up/subscription, bind `converted_user_id`.
- Watch events accumulate `watch_minutes_attributed`.
- **No payout in Phase 1** — data collected from day one per the spec's "collecting this data late is costlier" rationale.

**Acceptance:** every channel landing records source + session; conversions and watch-minutes accrue; data queryable.

---

## Phase CC9 — Creator Dashboard Analytics (Phase 2)

Channel dashboard showing the spec §7 view:

```
Facebook   1,842 visitors    WhatsApp 623   TikTok 417
New subscribers   384
Watch time    31,420 min
```

Powered by `ChannelAnalyticsService` over `attribution_links` + watch events. Follow/notify-on-new-episode and release-cadence scheduling land here (Phase 2).

**Acceptance:** creator sees per-source visitors, conversions, and watch time.

---

## Prioritized Build Order

```
Sprint 1 (CC1-CC2):  migrations + domain entities/repositories + bindings
Sprint 2 (CC3-CC4):  domain services + API endpoints + controllers
Sprint 3 (CC5):      admin production review queue
Sprint 4 (CC6):      creator channel/series UI in Creator Studio
Sprint 5 (CC7-CC8):  viewer channel experience + attribution tracking
Sprint 6 (CC9):      dashboard analytics (Phase 2)
```

---

## Open Questions (blocked items, from spec §10)

1. **Short-link service** — does `attribution_links` need `gs.zm/{code}` short links, or is `?src=` on the standard channel URL sufficient for Phase 1? (Affects CC8: minimal path is `?src=` only.)
2. **Promo review** — do trailers/clips go through the same content review as full episodes, or a lighter check given they're always-free? (Affects CC5: default = same queue for consistency.)
3. **Movies/specials storage** — confirm `growstream_videos` with `production_season_id = null` (single-asset) is preferred over a separate `standalone_releases` table. (Affects CC1 schema.)

**Default assumptions unless overridden:** `?src=`-only attribution in Phase 1; promo assets share the review queue; single-table episodes approach (no separate releases table).

---

## Success Criteria

- A verified creator can create a channel, publish a series (seasons/episodes), add a trailer, and generate `?src=` attribution links — all self-serve.
- Every production passes its own content/rights review regardless of creator trust level.
- Viewers can browse channels, follow, watch free episodes/trailers, and hit the paywall on premium episodes.
- Attribution data (source, conversion, watch minutes) is collected from day one even though payout is deferred to Phase 3.
- All additions follow the existing DDD + dark-theme UI conventions and the subdomain route-name guard.
