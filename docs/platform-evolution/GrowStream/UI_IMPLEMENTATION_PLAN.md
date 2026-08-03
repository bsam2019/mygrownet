# GrowStream — UI Implementation Plan (Phase 1 MVP)

**Version:** 1.0
**Date:** August 2026
**Based on:** `UI_PLAN.md` v1.0 (experience/design intent) + current frontend codebase audit
**Companion docs:** `PRODUCT_STRATEGIC_PLAN.md`, `IMPLEMENTATION_PLAN.md` (backend)
**Status:** Not started

---

## Read This First

This document is the **frontend build plan** for GrowStream Phase 1. It tells us *what screens to build, in what order, with what files, and how to verify them*. It does **not** re-derive the experience intent — that lives in `UI_PLAN.md`. It also deliberately **does not build the payments/payout engine** — per the UI_PLAN Scope Note, that is a separate, higher-risk workstream and its UI (checkout, earnings, pending-vs-settled states) must wait on those decisions in UI_PLAN §15.

> ⚠️ **Constraint from AGENTS.md:** never run `npm run build` on the server and never build assets from this task. The implementer builds locally, and the user manually runs the module build. This plan calls out exactly which build target map each task uses.

---

## Current Frontend State (Audit Summary)

What already exists vs. what the UI plan requires.

### Existing viewer pages (all use generic `AppLayout`, light blue/gray — NOT the premium theme)
| Page | File | Notes |
|---|---|---|
| Home | `resources/js/pages/GrowStream/Home.vue` | Blue gradient hero, Featured/Trending/New rows, Continue Watching. Not the dark emerald look, no single-hero focus |
| Browse | `resources/js/pages/GrowStream/Browse.vue` | Category/creator browsing |
| Video detail | `resources/js/pages/GrowStream/VideoDetail.vue` | Player + description + metadata + related |
| Series detail | `resources/js/pages/GrowStream/SeriesDetail.vue` | Series/episode layout |
| My Videos (Library proxy) | `resources/js/pages/GrowStream/MyVideos.vue` | Continue-watching collection |

### Existing components (`resources/js/components/GrowStream/`)
`VideoCard.vue`, `VideoGrid.vue`, `VideoPlayer.vue`, `VideoUploadModal.vue`

### Existing Creator pages (`pages/GrowStream/Creator/`)
`Register.vue`, `PendingApproval.vue`, `Dashboard.vue`, `Videos.vue`, `Upload.vue`, `Analytics.vue`, `Sponsorship.vue`

### Existing Admin pages (`pages/GrowStream/Admin/`)
`Videos.vue`, `Analytics.vue`, `Creators.vue`, `Moderation.vue`, `Sponsorship.vue`

### Existing routes (`routes/growstream.php`)
- Public (web): `home`, `browse`, `video.detail`, `series.detail`
- Authed (web+auth): `subscription`, `my-videos`, creator onboarding, creator videos CRUD, creator analytics, creator sponsorship
- Admin (web+auth+role:admin): videos, analytics, creators (+approve/reject), moderation (+approve/publish/reject), sponsorship (+approve/reject/disburse/complete)
- Namespaces: main domain `growstream.main.*`, subdomain `growstream.*`

### Build wiring
- Vite entry exists: `resources/js/app-growstream.ts` → `bootInertia('GrowStream', pages/GrowStream/**)`
- `vite.config.ts` has a `growstream` module in `ALL_INPUTS` and `knownBuildModules`
- **⚠️ Gap:** there is **no `build:growstream` script** in `package.json` (confirmed missing), so `MODULE=growstream vite build` works via config but has no npm alias.
- **⚠️ Gap:** There is **no `GrowStreamLayout.vue`** — viewer screens use generic `AppLayout`, so the dark emerald/gold visual identity is not realized anywhere.

### Theme gap (biggest single deviation)
Current viewer theme is Tailwind light (`bg-gray-50`, blue/indigo gradient hero), which contradicts UI_PLAN §1 visual identity (deep emerald, gold accent, near-black background, dark-grey cards, 16–20px corners). Every screen that already exists must be re-themed; new screens must be built on the dark theme from the start.

---

## Deliverable Structure

A single `GrowStreamLayout.vue` becomes the foundation for all viewer screens; a `CreatorStudioLayout.vue` (or a mode flag on the same layout) for Creator Mode; admin keeps its pattern but is re-themed. Visual tokens go in a Tailwind theme extension so they are consistent and changeable.

### Design token foundation (UI_PLAN §1, §2)
| Token | Value |
|---|---|
| `--gs-bg` | near-black (`#0a0a0c`-ish) |
| `--gs-card` | dark grey (`#16161a`-ish) |
| `--gs-primary` | deep emerald |
| `--gs-accent` | gold |
| `--gs-radius` | 16–20px |
| border radius | 18px (compromise) |

Define once in `tailwind.config` / a `GrowStream` plugin or CSS vars file, used by all GrowStream components. Do **not** hand-spread hex values across screens.

---

## Implementation Phases

Implementation is ordered so the foundational layout & design system come first, then viewer experience (highest MVP value), then Creator Mode and admin (already exist; mostly re-skin), then trust/legal + instrument.

### Phase U0 — Foundation: Dark Theme + Layout (2–3 days)
**Purpose:** One layout and theme that every other screen builds on. Everything else depends on this.

| Task | Detail | Dependencies |
|---|---|---|
| Add `build:growstream` npm script | `"build:growstream": "NODE_OPTIONS=--max-old-space-size=1024 MODULE=growstream vite build"` (mirror `build:stockflow`) | `package.json` |
| Create `GrowStreamLayout.vue` | Dark near-black bg, header (logo · Home · Discover · Creators · Search · Profile), white/emerald text, gold accent links, rounded cards, mobile bottom nav (5 items: Home · Discover · Search · Library · Profile) | `resources/js/layouts/` |
| Define design tokens | Tailwind theme extension + CSS vars for bg/card/primary/accent/radius | `tailwind.config.js` / global css |
| Component primitives | `GsButton`, `GsCard`, `GsBadge`, `GsRow` (horizontal scroll row) themed, dark | `resources/js/components/GrowStream/primitives/` |
| Re-theme existing 4 viewer components | `VideoCard`/`VideoGrid`/`VideoPlayer`/`VideoUploadModal` → dark emerald/gold | those 4 components |
| Switch Home/video pages to new layout | Move viewer pages off `AppLayout`→`GrowStreamLayout` | all viewer pages |

**Acceptance:** viewer renders dark; tokens are centralized; `build:growstream` script present (but not run here).

---

### Phase 2 — Viewer Experience Rewoirk (Home, Browse, Search, Video, Profile) (3–6 days)
**Goal:** Match UI_PLAN §5 viewer screens on the dark theme, with data-conscious defaults per §2.

| Screen | Work | Files / routes |
|---|---|---|
| **Home** | Replace blue hero with a single featured hero banner (no carousel). Rows: Continue Watching (hidden when empty per §10), Trending, New Releases, "Made in Zambia". Keep data from existing controller | `GrowStream/Home.vue`, + backend prop for hero (featured) & Made-in-Zambia filter |
| **Browse / Discover** | Category + creator browsing, dark cards, poster-first layout | `Browse.vue` |
| **Search** | Type-ahead suggestions across title/creator/genre + trending searches; accessible from Browse & nav | `Search.vue` (new) + endpoint(s) |
| **Video detail** | Dark player page; below: description, creator link, related titles. Data-saver estimated data usage per quality; resume-from-position without re-buffer | `VideoDetail.vue`; `config('growstream.player')` hooks |
| **Creator profile** | Followers, catalogue, about, subscribe/follow | `CreatorProfile.vue` (new); backend endpoint |
| **Library (continue watching)** | Polish existing; hide row when empty | `MyVideos.vue` |
| **Data Saver** | Toggle in Profile/settings; preview autoplay off by default; adaptive quality. UI only — wired to player behaviour | settings in layout/player |

**Acceptance:** viewer journeys match UI_PLAN §5 from browse→play with dark theme; data-efficiency defaults active.

---

### Phase 3 — Creator Mode Rework (on dark theme) (2–3 days)
**Goal:** Creator Studio as a mode of the same product (UI_PLAN §6), re-themed, onboarding & upload simplified.

| Task |
|---|
| Build `CreatorStudioLayout.vue` (or route to studio) that shares the platform header/nav |
| Rework `Creator/Dashboard.vue`: narrow to Views / Watch time / Earnings / content list w/ status (uploading/processing/ready) — no audience segmentation |
| Rework `Creator/Upload.vue` to exactly 3 steps (upload file → metadata → submit for moderation) |
| Re-theme `Videos.vue`, `Analytics.vue`, `Sponsorship.vue`, `Register.vue`, `PendingApproval.vue` |
| First-upload empty state (clear CTA, not zeroed metrics) — §10 |

**Acceptance:** creator flow = register → upload 3 steps. Earnings shown simply, without exposing the pool calc.

---

### Phase 4 — Admin Rework + Content Safety/Legal (1–2 days)
**Goal:** §7 admin + §8 safety screens.

| Task | Detail |
|---|---|
| Re-theme `Admin/*` (moderation central: queue + approve/reject/publish shared with reporting) | `Admin/Moderation.vue` etc. |
| Report action | Reachable from video & creator profile; routes into same moderation queue (backend fields) |
| Content ratings | Rating label (General / Parental Guidance / Mature) shown on poster + detail |
| Legal & account | ToS/Privacy (Profile/ footer), self-service cancel, contact-support dispute path. Subscription status under Profile |

**Acceptance:** admin can manage queue incl. reports; ratings on tiles; legal linked; cancel/dispute reachable.

---

### Phase 5 — Empty States, Error Handling, Notifications, Metrics (2–3 days)
**Purpose:** §10–§12 designed states and instrumentation.

| # | Task | Detail |
|---|---|---|
| Empty/error states | per UI_PLAN table 4: hidden Continue Watching, "nothing found" search, first-upload CTA, playback-failure retry + drop quality, mobile-money payment failure message w/ retry | all screens + player |
| Notifications | New upload from followed creator; renewal/payment confirmation; content approved (creator) — scope to 3 intents | notifications UI + listeners |
| Instrumentation | Report events: browse→play conversion, watch time/session, signup→subscription, renewal, retention (day-7/30). Analytics events fired from player & screen | a small `useGrowStreamMetrics` composable |

**Acceptance:** every empty/fail case has designed state; key §12 events instrumented.

---

### Phase 6 — QA, Responsiveness, Accessibility Check (1–2 days)
**Purpose:** §9 + §14.

- Mobile-first checker: bottom nav, player tap targets, readable font.
- Contrast check near-black + dark-grey + text (WCAG AA-ish).
- Screen-reader labels for nav & playback controls.

**Acceptance:** passes the §9 checklist items; desktop works but mobile-first is primary.

---

## Deferred (explicitly out, match UI_PLAN §13)

- AI recommendations, editorial collections beyond Made in Zambia, regional/language browse → Phase 2 UI
- Premieres, tipping/creator-subscriptions sponsorship UI, badges, AI creator guidance, landing pages, in-player overlays, seasons, Growth Journey → Phase 3 UI
- Livestreams/concerts/merch/fan clubs/multi-country → Phase 4 UI
- Standalone Studio app — only when creator volume justifies
- **Payments/payout UIs that depend on unknown settlement frequency / provider scope / refund interplay / pending-retry** — blocked on UI_PLAN §15 answers; build pending/sc success stale|error hooks per known pending states only

---

## Prioritized Build Order (Sprints)

```
Sprint 1:  Phase 1 foundation (layout, tokens, components) + build:growstream script
Sprint 2:  Phase 2 viewer (Home, Browse, Search, Detail, Creator Profile, Library, Data Saver)
Sprint 3:  Phase 3 Creator Mode rework + upload 3-step
Sprint 4:  Phase 4 admin + safety/legal + ratings
Sprint 5:  Phase 5 empty/error states, notifications, metrics
Sprint 6:  Phase 6 QA + a11y + responsiveness
```

### Risks / open questions
- **Payments/payout coupling:** subscription/payment screens (checkout, earnings balances, payout history) cannot be finalized until UI_PLAN §15 is answered. They will be built to show generic "pending / settled / paid" hooks only.
- **Backend gaps for UI** (needed prop/endpoints): hero featured row, Made in Zambia query, searchable endpoint, creator public profile, report request/endpoints, content rating field, notifications (likely exists via platform), analytics events.
- **Re-theming scope:** all 4 viewer-era pages + components + 13 (creator+admin) pages reworked to dark theme = bulk of effort. Expect near-full rewrites of Home and the player.

## Related docs
- `UI_PLAN.md` — the design/experience this plan executes (visual identity, screens, §15 blockers).
- `IMPLEMENTATION_PLAN.md` — backend (phases 1–4 done). UI consumes backend endpoints built there. Payments row (3.1/3.3) is backend; UI parity pending §15.
- `PRODUCT_STRATEGIC_PLAN.md`