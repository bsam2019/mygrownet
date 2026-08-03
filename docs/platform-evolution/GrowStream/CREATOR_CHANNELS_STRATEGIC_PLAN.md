# GrowStream — Creator Channels & Series

**Version:** 3.0
**Date:** August 2026
**Status:** Strategic spec — reconciled against the GrowStream UI Plan (Phase 1 MVP), Aug 2026
**Supersedes:** v1 (standalone feature draft) and v2 (full Channel → Series → Season → Episode strategy)

v1 was a standalone feature draft. v2 expanded it into a full Channel → Series → Season → Episode strategy. This version scopes that strategy down to what's actually compatible with the committed **Phase 1 UI Plan**, and pushes the rest into a clearly-labeled **Phase 3 candidate list** rather than silently expanding MVP scope.

---

## 1. What's Already There — No New Screens Needed

The UI Plan's existing **Creator Profile** (Section 5) already *is* the Creator Channel concept: followers, catalogue of movies/series, about section, subscribe/follow action. This spec extends it rather than introducing a parallel entity.

The existing **Upload Flow** metadata step (Section 6) already includes a trailer field alongside poster, title, description, genre, language, and visibility. That covers the "free promotional clip drives traffic" mechanic without a new `promo_assets` object model.

The existing **Creator Studio Dashboard** (Section 6) already shows Views, Watch time, Earnings, and a content list with status. This spec adds data underneath it without adding UI to it in Phase 1.

---

## 2. What This Feature Actually Adds in Phase 1

Three things, all additive to what's already scoped — no new top-level screens, no navigation changes:

1. **Free-first-episode paywall logic**, layered onto the existing single-tier subscription model (Section 5, Payment/Subscription) — episode 1 of a series is playable without a subscription; the rest gate behind the existing subscribe flow.
2. **A shareable, attribution-aware Creator Profile URL** (`growstream.co/c/{creator-slug}?src=facebook`) — the profile page itself is unchanged from what's already designed; only the link creators share externally carries a source tag.
3. **Silent attribution tracking** — source, visitor session, and eventual conversion are logged from day one, consistent with Section 12's instrumentation principle, but **not surfaced to creators yet**. This is data collection, not a new UI surface.

Everything else from the earlier v2 draft (Season as a structured entity, rich channel pages with featured banners and badges, creator-facing source/conversion analytics) is moved to Section 5 below as a labeled Phase 3 candidate — on record, not built.

---

## 3. Data Model (Phase 1 — UI-compliant)

Kept intentionally flatter than v2's schema. No `seasons` table exposed anywhere; `season_id` exists as a nullable field for forward compatibility only, never populated or surfaced in Phase 1.

### `productions`
Extends the existing content record (whatever table already backs "movies/series" in the Creator Profile catalogue) rather than replacing it.

| field | type | notes |
|---|---|---|
| id | uuid | |
| creator_id | uuid (fk) | |
| type | enum | movie, series |
| title | string | |
| free_episode_count | int, default 1 | platform default; not creator-configurable in Phase 1 |
| ... | | all existing fields (poster, trailer, genre, language, visibility, moderation status) unchanged |

### `episodes`
Only applies where `type = series`. A flat, ordered list — no season grouping in Phase 1.

| field | type | notes |
|---|---|---|
| id | uuid | |
| production_id | uuid (fk) | |
| season_id | uuid, nullable | **unpopulated in Phase 1** — reserved for Phase 3, not exposed in any UI |
| episode_number | int | ordering within the flat list |
| video_upload_id | uuid (fk) | reuses existing upload pipeline unchanged |
| status | enum | reuses existing: uploading, processing, ready, moderation-pending, moderation-rejected |
| is_free | bool, computed | `episode_number <= production.free_episode_count` |

### `attribution_events`
Tracking only — no creator-facing screen reads from this table in Phase 1.

| field | type | notes |
|---|---|---|
| id | uuid | |
| creator_id | uuid (fk) | |
| source | string, nullable | from `?src=` param |
| visitor_session_id | string | pre-auth |
| converted_user_id | uuid, nullable | set on subscription conversion |
| watch_minutes_attributed | int, default 0 | |
| created_at | timestamp | |

This is deliberately separate from MyGrowNet's existing network-growth referral system — it answers a different question (which social platform converts a creator's audience) and has a different consumer (future Creator Studio analytics, not the referral/rewards mechanic).

---

## 4. Paywall Logic (unchanged in principle from v2)

```
function canWatch(user, episode):
    if episode.is_free:
        return true
    if user.has_active_premium_subscription:   # existing single-tier check
        return true
    return false  # existing subscribe flow, triggered on pressing play
```

This slots directly into the existing subscription-prompt pattern already specified in Section 4 (triggered naturally on pressing play, not as an interstitial) — no new payment UI, no new prompt pattern.

---

## 5. Explicitly Deferred (Phase 3 candidates — added to the existing deferred list, Section 13 of the UI Plan)

These were in the v2 draft and don't belong in Phase 1. Listed here so they're on record, not lost, consistent with how the UI Plan already treats deferred ideas.

| Feature | Why deferred |
|---|---|
| Season as a structured, UI-visible entity | Already explicitly Phase 3 in the UI Plan |
| Rich channel page (verified badge, featured series banner, latest/standalone content sections) | Conflicts with Section 5's "no Behind the Scenes, community posts, or badges yet" for Creator Profile |
| Creator-facing attribution/conversion dashboard (source breakdown, new subscribers by platform) | Conflicts with "no audience segmentation" in Creator Studio Dashboard scope |
| Multiple promo assets per production (trailer + clips + behind-the-scenes as distinct objects) | Existing single trailer field in upload metadata is sufficient for Phase 1 |
| Attribution-link payout/reward mechanics | Data collection starts now; compensation model is a Phase 3 decision once data exists |
| Short-link service (`gs.zm/{code}`) | `?src=` query param on the existing profile URL is sufficient until volume justifies a dedicated link service |

---

## 6. Open Decisions (revised)

- [ ] Should `free_episode_count` be visible to the creator during upload (read-only, platform-set), or entirely invisible until they notice episode 2 is gated?
- [ ] Does episode ordering in the flat list need drag-and-drop reordering in Creator Studio Phase 1, or is upload order sufficient until Season structure arrives in Phase 3?
- [ ] Confirm `attribution_events` logging doesn't need consent/disclosure UI for Phase 1 (likely covered by existing Terms of Service acceptance at signup, per Section 8) — worth a quick legal check given mobile money and user-generated content are already flagged as launch risk areas in that section.
