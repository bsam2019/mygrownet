# GrowStream — Monetization Model: Viewer-Facing Subscriptions + View Allowance

**Addendum to the PRODUCT_STRATEGIC_PLAN.md §14 Monetization Model.**
**Version:** 1.0 · **Date:** August 2026
**Status:** Implemented (config, enforcement, admin editing)

---

## 1. Purpose

This document replaces the creator-facing tier model that was mistakenly surfaced
on the GrowStream plans page. The platform does **not** charge creators to publish.
Creators upload for free; revenue comes from **viewer** subscriptions. It also
consolidates the "Metered Subscription + TVOD" addendum, incorporating the critique
that led to the view-allowance model actually shipped.

---

## 2. The Model (as implemented)

| Path | Mechanic | Status |
|---|---|---|
| **Free** | Watch the free catalogue + free episode(s) of series. No premium views. | Implemented |
| **Subscription (Starter / Business)** | Monthly premium-view allowance; playback blocked once exhausted. | Implemented |
| **Top-up minutes bundles** | Purchase extra allowance if the monthly allowance runs out. | Not yet — see §7 |
| **Pay-per-title (TVOD)** | One-time purchase to unlock a single movie, no subscription required. | Not yet — see §7 |

### Current tier configuration (`config/modules/growstream.php`)

| Tier | Price /mo | Price /yr | Premium views / month | Features |
|---|---|---|---|---|
| Free | K0 | K0 | 0 | Free catalogue, creator tools |
| Starter | K129 | K1,238 | 300 | HD streaming, ad-free, multi-device |
| Business | K549 | K5,270 | Unlimited (−1) | 4K, ad-free, offline downloads, multi-device, priority support |

All values are **admin-editable** from the dashboard (see §5) and stored as DB
overrides on top of the config defaults.

---

## 3. Where the original model went wrong

The plans page previously showed **creator** quotas — `Videos: 5`, `Storage Mb: 500`,
`Viewers: 100` — implying creators paid to publish. This contradicted the strategic
plan (§14: creators do not pay to publish; earnings come from a 70% watch-time
revenue share pool). Fixing it meant:

1. Reframing tiers as **viewer** plans (upload is free; the viewer pays to watch).
2. Adding a **consumption cap** so a flat-fee subscription cannot cost more to
   deliver (Cloudflare Stream bills by minutes delivered) than the viewer pays.
   A heavy viewer with an unlimited plan is a guaranteed loss. Metering premium
   views per month bounds that exposure.

---

## 4. Critique of the "Metered Subscription + TVOD" addendum

The addendum's core diagnosis is sound and its phasing is sensible. The critique
below is what shaped the shipped implementation.

### Strengths (kept)

- **Cost ceiling via metering.** Bounding per-subscriber consumption is the correct
  hedge against the flat-fee VOD failure mode (Showmax shutdown, March 2026).
- **TVOD as an additive path, not a replacement.** Rent/buy alongside subscription
  matches Amazon/Apple/Google Play precedent and gives one-off viewers a route.
- **Reuses existing measurement.** The minutes-watched data already collected for
  the creator payout pool serves double duty as the consumption meter.
- **Phased introduction** (backend-only ceiling → visible bundles → TVOD) is the
  right order — you should never commit to visible bundle numbers before real
  usage data exists.

### Points where we diverged (and why)

1. **"Watch-minutes" vs "number of views" as the meter.**
   The addendum proposed a **watch-minutes** allowance. We shipped a **premium-view
   count** (number of premium videos started per calendar month) instead, because:
   - A view is trivially countable from the existing `growstream_video_views` table
     with no new metering infrastructure, no client-side timers, and no ambiguity
     about "what counts as a watched minute" (anti-gaming rules the addendum itself
     flagged as an open question).
   - View-count is the unit viewers already understand ("K129 = 300 premium views").
   - Watch-minutes remains the correct *cost* proxy, and the payout pool already
     computes it; it can be layered on later without changing the viewer contract.
   **Trade-off accepted:** a 5-minute skit and a 2-hour movie both cost one view,
   so the allowance under-charges heavy long-form viewers. Revisit if long-form
   dominates after Phase 1 usage data.

2. **Calendar-month window, not billing-cycle window.**
   The allowance resets on the 1st of the month (not the subscriber's renewal
   date). Simpler to reason about and implement; the addendum's open question about
   carryover/top-ups is deferred until bundles are visible (§7).

3. **Free tier has zero premium views.**
   The addendum implied a small free allowance. We kept free = free catalogue only
   (0 premium views) because the free-first-episode rule already gives a marketing
   hook without opening the paid catalogue. Cheap to relax later via admin.

4. **TVOD deferred, not removed.** The addendum's TVOD is correct but was a phase
   2/3 item; we shipped the base metered subscription first (see §7).

---

## 5. Admin capability: editing tiers from the dashboard

Tiers are now editable by admins without touching code:

- **Where:** Admin → Subscription Management → GrowStream
  (`/admin/module-subscriptions/growstream`).
- **What can be edited per tier:** name, description, monthly/annual price,
  active/default/popular flags, sort order, and — for GrowStream — the
  **Premium Views / Month** allowance (`views_per_month`, `-1` = unlimited).
- **Features** (HD, ad-free, offline, etc.) are toggled via the per-tier
  **Manage Features** editor.
- **Sync from Config:** seeds the DB from `config/modules/growstream.php` defaults
  (safe to re-run; it uses `updateOrCreate`).

### How overrides resolve

`TierConfigurationService` is the single source the plans page **and**
`AccessControlService` (playback gate) read from. It now merges DB overrides:

```
getTiers(module) = config/modules/{module}.php tiers
                   MERGED with module_tiers rows (DB wins, when present)
```

- No DB rows → config defaults are used unchanged.
- DB rows exist → the DB tiers (including `views_per_month`) fully replace the
  config tiers for that module. Admin edits take effect immediately on both the
  public plans page and the playback gate.
- `TierConfigurationService::clearCache($moduleId)` is called after admin writes
  so cached config does not mask the update.

---

## 6. Enforcement (server-side)

The view allowance is enforced **server-side**, not just in the UI:

- `AccessControlService::remainingPremiumViews(User)` → allowance − used this month.
- `AccessControlService::userCanAccess()` (used by the playback API) returns
  `false` once the monthly premium-view allowance is exhausted.
- `WatchService::resolveUserAccess()` and `GrowStreamWebController::canWatchVideo()`
  (the web paywall gate) apply the same check.
- Only **premium** content plays count (`growstream_video_views` joined to
  `growstream_videos` where `access_level != 'free'`). Free content and free
  episodes never consume the allowance.
- Admins (`business` tier) are unlimited by default (`views_per_month = -1`).

Counting basis: number of premium video plays started in the current **calendar
month** (`viewed_at >= startOfMonth`), keyed by `user_id`.

---

## 7. Roadmap (deferred from the addendum)

- **[ ] Phase 2 — visible bundles & top-ups.** Once Phase 1 usage data exists,
  calibrate the allowance numbers (don't guess), show "X views left" in the UI,
  and add a top-up purchase flow (mobile money, same checkout pattern).
- **[ ] Phase 2/3 — TVOD.** Per-title Buy/Rent alongside the subscribe prompt,
  using the existing mobile-money checkout. Decide rent-vs-buy and whether TVOD
  revenue splits directly to the creator (vs the shared watch-time pool).
- **[ ] Consider watch-minutes as a secondary meter** for long-form-heavy
  usage, once the payout pool's minutes data is trusted.
- **[ ] Decision needed:** whether the fair-use ceiling should also apply to the
  free catalogue (free content costs delivery too).

---

## 8. Open Decisions

- Exact allowance calibration (needs real viewing data + Cloudflare Stream cost
  per minute input).
- Rent vs buy vs buy-only for TVOD.
- Top-up semantics: extend current cycle vs rolling balance that carries over.
- TVOD settlement: direct split vs shared pool.

---

## 9. Related

- `PRODUCT_STRATEGIC_PLAN.md` §14 (creator revenue-share pool) — unchanged.
- `CREATOR_CHANNELS_STRATEGIC_PLAN.md` (creator channels / attribution).
- Implementation files:
  - `config/modules/growstream.php` — viewer-facing tier defaults.
  - `app/Domain/GrowStream/Services/AccessControlService.php` — allowance enforcement.
  - `app/Domain/GrowStream/Services/WatchService.php` — playback gate.
  - `app/Domain/Module/Services/TierConfigurationService.php` — DB-override merge.
  - `app/Http/Controllers/Admin/ModuleSubscriptionAdminController.php` — admin CRUD.
  - `resources/js/pages/Admin/ModuleSubscriptions/Show.vue` — admin UI.
  - `resources/js/pages/Payments/ModulePlans.vue` — public plans page.
