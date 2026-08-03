# GrowStream — Creator Channels & Series

**Version:** 2.0 — Core product strategy spec, not a minor upload extension
**Date:** August 2026
**Status:** Strategic spec — supersedes the earlier `growstream-creator-series-spec.md` draft
**Key change:** the **Creator Channel**, not the series, is the permanent unit

Extends the existing GrowStream upload pipeline (Cloudflare Stream, tiered creator vetting, minutes-watched payout model).

---

## 1. Strategic Framing

This isn't "let creators upload series." It's the mechanism that makes GrowStream a **distribution and monetization layer for Zambian creators**, not just a video host.

The loop it enables:

```
Creator produces series → publishes to own Channel → promotes free
clips/trailers on Facebook/TikTok/WhatsApp → viewer clicks link →
Episode 1 free → viewer subscribes → watches premium episodes →
creator earns from watch time → creator sees revenue + audience
source data → produces better content → cycle repeats
```

This is the reason a Zambian creator picks GrowStream over just uploading everything to Facebook or YouTube: GrowStream gives them monetization + audience-source analytics that raw social platforms don't.

---

## 2. Core Structural Change

**Old model:** channel belonged to a series (`growstream.co/c/{series-slug}`)
**New model:** the **Creator Channel is permanent**; series, seasons, movies, specials, and music all live inside it.

```
Creator
  └── Tier 1 Verified (vetted once)
        └── Creator Channel        (permanent, one per creator)
              ├── Series
              │     └── Season
              │           └── Episode
              ├── Movies
              ├── Comedy Specials
              ├── Music Releases
              └── Trailers / Promo Clips
```

URL structure:

```
growstream.co/c/chanda-comedy                          → channel home
growstream.co/c/chanda-comedy/series/life-in-lusaka     → series page
gs.zm/chanda                                            → short attribution link
```

A creator's social audience gets **one permanent GrowStream destination** they can point people to, not a one-off series link that becomes dead weight once the series ends.

---

## 3. Vetting vs. Content Review — Separated

The creator is vetted once (existing Tier 1 flow, unchanged). Individual productions are reviewed separately, every time:

```
Creator → Tier 1 Verified → Creator Channel
                                  │
                          Apply to publish:
                          ├── Series
                          ├── Movie
                          ├── Comedy Special
                          └── Music Release
                                  │
                          (each goes through content/rights review,
                           regardless of creator's trust level)
```

Rationale: a legitimate, trusted creator can still upload content they don't own the rights to. Trust in the person and review of the specific content are different checks and shouldn't be conflated.

---

## 4. Data Model

### `creator_channels`

| field | type | notes |
|---|---|---|
| id | uuid | |
| creator_id | uuid (fk) | one channel per Tier 1 verified creator |
| slug | string, unique | `growstream.co/c/{slug}` |
| display_name | string | e.g. "Chanda Comedy" |
| tagline | string, nullable | |
| avatar_media_id | string | |
| banner_media_id | string, nullable | |
| verified | bool | |
| created_at | timestamp | |

### `productions`

Generic parent for anything a creator publishes under their channel — series, movie, comedy special, music release. Keeps the schema from needing a separate near-identical table per content type.

| field | type | notes |
|---|---|---|
| id | uuid | |
| channel_id | uuid (fk) | |
| type | enum | series, movie, comedy_special, music_release |
| title | string | |
| synopsis | text | |
| category | enum | drama, comedy, telenovela, music, etc. |
| status | enum | pending_review, approved, rejected, paused, completed |
| poster_media_id | string | |
| release_cadence | enum/nullable | weekly, biweekly, irregular — series only |
| free_episode_count | int, default 1 | platform default; admin can override per production |
| created_at / updated_at | timestamp | |

### `seasons`

| field | type | notes |
|---|---|---|
| id | uuid | |
| production_id | uuid (fk) | only applies where `type = series` |
| season_number | int | |
| title | string, nullable | |

### `episodes`

| field | type | notes |
|---|---|---|
| id | uuid | |
| season_id | uuid (fk, nullable) | null for movies/specials (single-asset productions) |
| production_id | uuid (fk) | |
| episode_number | int, nullable | |
| video_upload_id | uuid (fk → existing uploads/videos table) | reuses existing upload pipeline unchanged |
| status | enum | reuses existing: uploaded, processing, pending_review, published, rejected |
| is_free | bool, computed | free if `episode_number <= production.free_episode_count` |
| published_at | timestamp/nullable | |

### `promo_assets`

Trailers and clips as first-class objects, not afterthoughts — these are what creators actually post on social media.

| field | type | notes |
|---|---|---|
| id | uuid | |
| production_id | uuid (fk) | |
| type | enum | trailer, clip, behind_the_scenes |
| video_upload_id | uuid (fk) | |
| always_free | bool, default true | |

### `channel_follows`

| field | type | notes |
|---|---|---|
| id | uuid | |
| channel_id | uuid (fk) | follow the channel, not just one series |
| user_id | uuid (fk) | |
| followed_at | timestamp | |

### `attribution_links` (Creator Attribution Links)

Standalone system, not dependent on MyGrowNet's existing referral identity — built specifically to answer "which social platform is actually converting my audience," which is a distinct question from MyGrowNet's network-growth referral tracking.

| field | type | notes |
|---|---|---|
| id | uuid | |
| channel_id | uuid (fk) | |
| source | string | e.g. facebook, tiktok, whatsapp — from `?src=` param or short-link mapping |
| short_code | string, unique, nullable | for `gs.zm/{code}` style links |
| visitor_session_id | string | pre-auth tracking, resolved to `user_id` on sign-up if conversion happens |
| converted_user_id | uuid, nullable | set on subscription conversion |
| watch_minutes_attributed | int, default 0 | accumulates as the attributed user watches |
| created_at | timestamp | |

---

## 5. API Endpoints

```
# Channel
POST   /api/creators/{creator}/channel                   # create channel (post Tier-1 verification)
GET    /api/channels/{slug}                               # channel home data
GET    /c/{slug}                                          # public channel page (SSR/shareable)

# Productions (series / movie / special / music)
POST   /api/channels/{channel}/productions/apply          # submit for review
GET    /api/productions/pending-review                    # admin review queue
POST   /api/productions/{production}/review-decision       # approve/reject/needs-info

# Seasons & Episodes
POST   /api/productions/{production}/seasons
POST   /api/seasons/{season}/episodes                      # reuses existing upload flow
GET    /c/{slug}/series/{production_slug}                  # public series page

# Promo assets
POST   /api/productions/{production}/promo-assets

# Follows
POST   /api/channels/{channel}/follow

# Attribution
POST   /api/attribution/resolve                            # resolve ?src= or short_code on landing
GET    /api/channels/{channel}/analytics                   # source breakdown, conversions, watch time
```

---

## 6. Paywall Logic (unchanged in principle, now channel-aware)

```
function canWatch(user, episode):
    if episode.is_free:
        return true
    if user.has_active_premium_subscription:
        return true
    return false  # show subscribe CTA, tagged with the episode's production/channel
```

Enforced server-side at the Cloudflare Stream signed-URL step, not client-side only — unchanged from v1.

---

## 7. Creator Dashboard (Analytics)

This is the payoff for creators and the reason Attribution Links matter — it's more valuable than raw view counts because it shows which channel actually converts:

```
Your Growth

Facebook          1,842 visitors
WhatsApp             623 visitors
TikTok               417 visitors

New subscribers        384
Watch time          31,420 min
```

---

## 8. Build Phases (revised)

**Phase 1 — MVP** (expanded from v1 to include the Channel as a first-class object, per the strategic framing above)

*Viewer:* Home, Browse, Search, Watch, Subscription, Continue Watching, Creator Channels
*Creator:* Verification (existing Tier 1, unchanged), Creator Channel, Upload, Series/Season/Episode structure, Trailers/promo clips, basic earnings view
*Admin:* Creator vetting (existing, unchanged), Production review queue, video moderation (existing, unchanged), rights verification

Attribution Links: **tracking only** in Phase 1 — capture source + conversion + watch-minutes data from day one even though nothing is paid out on it yet. Collecting this data late is much more costly than collecting it from the start.

**Phase 2**
- Creator dashboard analytics (source breakdown, conversion, watch time — the view shown above)
- Follow/notify-on-new-episode
- Release cadence scheduling/reminders

**Phase 3**
- Additional creator rewards based on verified attribution-link conversions — introduced only if the economics support it once Phase 1/2 data is in hand
- Series-level discovery/recommendation on the GrowStream home feed

---

## 9. Resolved Decisions (from v1's open list)

- **`free_episode_count`**: platform default of 1, with admin-approved per-production overrides (e.g. 2 free episodes for a series GrowStream wants to push for acquisition). Not a creator-configurable setting — prevents a creator undermining the subscription model by making everything free.
- **Series review vs. Tier 1 vetting**: separate. Creator identity/trust is vetted once; every production (series, movie, special, release) goes through its own content/rights review regardless of creator standing.
- **`channel_follows`**: Phase 1, scoped to the channel (not per-series) — see schema above.
- **Referral/attribution payout mechanics**: deferred to Phase 3 by design, but the underlying tracking (`attribution_links`) ships in Phase 1 so the data exists once the compensation model is decided.

---

## 10. Still Open

- [ ] Does `attribution_links` need its own short-link service (`gs.zm/{code}`), or is a `?src=` query param on the standard channel URL sufficient for Phase 1?
- [ ] Should promo assets (trailers/clips) go through the same content review queue as full episodes, or a lighter-touch check given they're always-free marketing material?
- [ ] Movies/specials use `episodes` with `season_id = null` — confirm this single-table approach is preferable to a separate `standalone_releases` table before building.
