# GROWSTREAM — UI Plan

**Phase 1 (MVP) Interface & Experience Design**
*A MyGrowNet Entertainment Platform*

Prepared for internal team review
August 2026 · Version 1.0

## Table of Contents

- [Scope Note — Read This First](#scope-note--read-this-first)
- [1. Design Principles](#1-design-principles)
- [2. Data-Consciousness Defaults](#2-data-consciousness-defaults)
- [3. Navigation](#3-navigation)
- [4. Onboarding & First-Run Experience](#4-onboarding--first-run-experience)
- [5. Phase 1 Screens — Viewer Experience](#5-phase-1-screens--viewer-experience)
- [6. Phase 1 Screens — Creator Mode](#6-phase-1-screens--creator-mode)
- [7. Phase 1 — Administration](#7-phase-1--administration)
- [8. Content Safety, Reporting & Legal Screens](#8-content-safety-reporting--legal-screens)
- [9. Accessibility Considerations](#9-accessibility-considerations)
- [10. Empty States & Error Handling](#10-empty-states--error-handling)
- [11. Notifications](#11-notifications)
- [12. Success Metrics & Instrumentation](#12-success-metrics--instrumentation)
- [13. Explicitly Deferred (Phase 2–4)](#13-explicitly-deferred-phase-24)
- [14. Device & Platform Scope](#14-device--platform-scope)
- [15. Payments & Payout — Open Scoping Questions](#15-payments--payout--open-scoping-questions)
- [16. Related Documents](#16-related-documents)

## Scope Note — Read This First

This document describes the front-end experience of GrowStream's Phase 1 MVP: what viewers, creators, and administrators see and do on screen. It deliberately does not describe the payments and payout engine in technical depth, and that omission is intentional, not an oversight.

Mobile money integration and the watch-time subscription-pool revenue split are core to the MVP hypothesis and cannot be cut — but they are a separate, higher-risk engineering workstream from the UI. Calculating and reconciling a revenue split across many creators based on watch time, unique viewers, and engagement, integrated with mobile money settlement, is non-trivial accounting and payments infrastructure. It is realistically the long pole in the Phase 1 schedule — more complex and more time-consuming than any screen described in this document, including the video player and Creator Studio dashboard.

**Recommendation:** scope, staff, and timeline the payments and payout backend as its own workstream with its own milestones, independent of UI and content-browsing work. Do not let the relative visual simplicity of a subscribe button or an earnings number understate what it takes to make those numbers correct, auditable, and reliably paid out. UI work in this document can proceed in parallel, but Phase 1 as a whole should be scheduled against the payments workstream's timeline, not the UI's.

Everywhere this document shows a payment or earnings screen, it is shown as what the user sees, not as a description of how the underlying calculation or settlement works. See Section 15 for a dedicated breakdown of what the payments workstream needs to resolve before engineering estimates are meaningful.

### Purpose of this document

GrowStream is being designed across three documents that intentionally stay separate: a Strategic Plan (business model, market, monetization), a UX Vision (the five-year experience, including ideas not yet scheduled), and this Product Roadmap / UI Plan (a strict, phase-by-phase implementation plan). Keeping them separate lets ambitious long-term thinking stay on record without it being mistaken for what ships first.

## 1. Design Principles

- **Entertainment first, creator economy second, community and referral mechanics third.** The interface should read as a premium entertainment product, never as a network-marketing app with a video player attached.
- **Local identity, not a Netflix clone.** Visual language (deep emerald and gold, near-black backgrounds, generous whitespace) and content framing ("Made in Zambia," regional trending) should make the platform feel distinctly of its market.
- **Data-consciousness is a first-class design constraint, not an afterthought.** Video is far more bandwidth-intensive than a website; every screen and default behaviour should assume a mobile-data-conscious user unless they indicate otherwise.
- **One product, two modes — not two apps.** Viewers and creators share one application; Creator Studio is an additional mode reachable from the same account, not a separate product, at this stage.
- **Every Phase 1 screen must pass one of three tests:** does it help a viewer find or enjoy content, help a creator upload or understand earnings, or help GrowStream acquire/retain users and prove the core hypothesis? If not, it is out of Phase 1.

**Visual identity**

| Element | Direction |
|---|---|
| Primary colour | Deep emerald (brand) |
| Accent | Gold |
| Background | Near-black |
| Cards | Dark grey |
| Typography | Modern sans-serif |
| Corners | 16–20px rounded |
| Spacing | Generous whitespace |

Table 1 — Visual language intended to feel premium and locally distinctive rather than a dark reskin of an existing platform.

## 2. Data-Consciousness Defaults

Because this is a video product in a market where mobile data is a real cost, the following are Phase 1 requirements, not later polish.

| Behaviour | Default |
|---|---|
| Preview / hover autoplay of trailers | Off on mobile data; may be enabled on Wi-Fi or by explicit user preference |
| Playback quality | Adaptive by default, with a manual override |
| Data Saver mode | Available and easy to find, not buried in settings |
| Estimated data usage | Shown per quality level before playback starts |
| Resume after interruption | Efficient — does not silently re-buffer from zero |

Table 2 — Data-conscious defaults, required for Phase 1 given mobile data costs in the target market.

## 3. Navigation

**Mobile — bottom navigation (5 items)**
Home · Discover · Search · Library · Profile

Everything else — Creator Studio, Rewards, Downloads, Settings — lives under Profile. A viewer who doesn't care about creator tools or referral rewards never has to see them.

**Desktop navigation**
Logo · Home · Discover · Creators · Search · Profile. No more than these top-level items — resist adding categories as content grows; that is what editorial Collections (Phase 2) are for, not new nav items.

## 4. Onboarding & First-Run Experience

The first few minutes of using GrowStream matter disproportionately for the MVP hypothesis — a confusing first run undermines the whole test of whether viewers will pay for content. Keep it short and get to content quickly.

| Step | What happens |
|---|---|
| 1. Landing | A single, confident value statement and a hero title — not a feature tour. |
| 2. Account creation | Phone number or email; minimal fields. Mobile money number can be captured at subscription time, not forced at signup. |
| 3. Browse before paying | Let a new user see the catalogue (posters, titles, previews) before any payment prompt — trust is built by showing real content, not by asking for money first. |
| 4. Subscription prompt | Triggered naturally — e.g. on pressing play on a premium title — rather than as an interstitial before anything is seen. |
| 5. First watch | Get to actual playback in as few taps as possible. |

Table 3 — First-run flow. The goal is to let the content sell the subscription, not a signup wall.

## 5. Phase 1 Screens — Viewer Experience

**Home**
A single-column, personalized feed rather than a static category grid:
- One large hero banner for a single featured title (no rotating carousel — a single, confident hero performs better for focus than a rotating one)
- Continue Watching
- Trending
- New Releases
- A small number of genre/category rows
- "Made in Zambia" collection

No AI-personalized recommendation engine in Phase 1 — rows are ordered by "latest," "popular," and "featured," which is enough to validate the hypothesis without building a recommendation system before there is usage data to train it on.

**Browse / Discover**
Category and creator browsing, with search accessible from the same screen. Editorial collections (Phase 2) live here rather than as separate top-level nav items.

**Search**
Instant search with type-ahead suggestions across titles, genres, and creator names, plus a trending-searches list. No separate "advanced search" in Phase 1.

**Video Player**
Deliberately minimal, per the "stay out of the way" principle:
- Timeline, quality selector, captions, playback speed, fullscreen
- Below the player: description, creator name/link, related titles

No next-episode auto-advance overlays, no in-player creator-support prompts, no "behind the scenes" panel yet — these are Phase 3 additions once there is a base of engaged viewers to justify them.

**Creator Profile**
Followers, their catalogue (movies/series), a short about section, and a subscribe/follow action. No "Behind the Scenes," community posts, or badges yet — those are Phase 3.

**Payment / Subscription (UI only — see Scope Note)**
- A single subscription tier shown clearly with price (e.g. "K50/month")
- Mobile money checkout screen
- Subscription status visible under Profile

No tipping, no multiple tiers, no creator-specific subscriptions in Phase 1 — one platform-wide subscription, one payment method flow.

## 6. Phase 1 Screens — Creator Mode

Reached from Profile, not a separate app. A creator sees the same five-item navigation plus one additional entry: Creator Studio.

**Creator Studio Dashboard**
Kept intentionally narrow for Phase 1:
- Views
- Watch time
- Earnings (shown as a number the creator receives; the underlying subscription-pool calculation is not exposed in detail on this screen — see Scope Note)
- A simple content list (uploaded titles, status: uploading / processing / ready)

No audience segmentation, no AI-driven content suggestions, no sponsorship or promotion tools yet — these are Phase 3.

**Upload Flow**
Three steps, no more:
- Upload file (Cloudflare Stream handles encoding; UI shows Uploading → Processing → Ready)
- Metadata: title, description, genre, language, poster, trailer, visibility
- Submit for moderation

**Earnings / Payout (UI only — see Scope Note)**
Shows the creator their current balance and payout history. This screen is a thin display layer over the payments workstream described in the Scope Note — its simplicity on screen should not be read as simplicity underneath.

## 7. Phase 1 — Administration

Required from day one, not deferred, because of copyright and content-rights exposure:
- Creator approval queue
- Content moderation / approval queue
- Rights verification
- Content removal
- Subscription management (viewing/adjusting a user's subscription status)

## 8. Content Safety, Reporting & Legal Screens

Not covered in earlier discussion of this plan, but necessary for a Phase 1 launch handling payments and user-generated content. These are small screens individually, but they carry real legal and trust weight.

**Content reporting**
A simple "Report" action reachable from any title or creator profile (inappropriate content, copyright concern, misleading information). Reports route into the same moderation queue as creator/content approval (Section 7) rather than a separate system.

**Content ratings**
A basic age/content guidance label (e.g. General, Parental Guidance, Mature) shown on posters and detail pages. Even a simple three-tier system is worth having before launch, given the mix of comedy, drama, and potentially mature content across independent creators.

**Legal and account screens**
- Terms of Service and Privacy Policy, linked from Profile and required at signup
- Subscription cancellation flow — clear, self-service, not hidden behind support contact
- Refund / billing dispute path — even a simple "contact support" screen is needed given mobile money payments are involved

None of this needs to be elaborate for Phase 1, but its absence is a real launch risk given the platform is taking payments and hosting user-generated video from day one.

## 9. Accessibility Considerations

A small, deliberately scoped set for Phase 1 — not a full accessibility audit, but the basics that are far cheaper to build in now than to retrofit later:
- Captions are already planned for the player (Section 5) — ensure they are present for all creator uploads where available, not just platform-produced content
- Sufficient colour contrast between the near-black background, dark-grey cards, and text — worth a deliberate check given the dark theme
- Readable minimum font sizes and tap-target sizes on mobile, especially in the video player controls
- Screen-reader labelling for primary navigation and playback controls

Deeper accessibility work (full screen-reader support across every screen, audio descriptions) is reasonable to defer, but the items above are inexpensive to include from the start.

## 10. Empty States & Error Handling

Every screen that can be empty or fail needs a designed state — otherwise the product looks broken during the exact early period when trust matters most.

| Situation | What the user should see |
|---|---|
| New viewer, nothing watched yet | Continue Watching row is hidden entirely rather than shown empty; Home leads with Trending/New Releases instead |
| Search with no results | A clear "nothing found" state with a suggestion to browse Trending, not a blank screen |
| Creator with no uploads yet | Creator Studio shows a clear first-upload prompt, not an empty dashboard with zeroed metrics |
| Playback failure / poor connection | A plain-language retry message and an offer to drop to a lower quality — not a generic error code |
| Payment failure (mobile money) | A clear, specific reason where possible (e.g. "payment not confirmed") and an easy retry — this is a trust-critical moment, not a place for a generic error |

Table 4 — Designed empty and error states, especially important given the platform is new and payment-related failures directly affect trust.

## 11. Notifications

Kept minimal and useful for Phase 1 — notifications should never feel like marketing spam, especially early on when trust is being established.
- New upload from a followed creator
- Subscription renewal reminder / payment confirmation
- Content approved and live (creator-facing)

No push notifications for Premieres, live events, or promotional campaigns yet — those arrive with the features they support in Phase 3.

## 12. Success Metrics & Instrumentation

The MVP exists to answer one question: will creators upload valuable content, and will viewers pay to watch it? The UI should be instrumented from day one to actually answer that, not just to look complete.

| Question | What to measure |
|---|---|
| Are viewers finding content worth watching? | Browse-to-play conversion rate, average watch time per session, Continue Watching return rate |
| Will viewers pay? | Signup-to-subscription conversion rate, subscription renewal rate, time from signup to first payment |
| Are creators producing content worth paying for? | Upload volume per creator, average watch time per title, repeat-viewer rate per creator |
| Is the platform retaining people? | Weekly/monthly active users, churn rate, day-7 and day-30 retention |

Table 5 — Core metrics the Phase 1 UI should be built to capture. This is analytics instrumentation, not a user-facing feature, but it needs to be planned alongside the screens rather than retrofitted after launch.

## 13. Explicitly Deferred (Phase 2–4)

Kept out of Phase 1 UI deliberately, so scope doesn't quietly expand once early results feel encouraging.

| Feature | Target phase |
|---|---|
| AI-personalized recommendations (viewer) | Phase 2 |
| Editorial collections beyond "Made in Zambia" (Weekend Comedy, Campus Stories, Copperbelt Creators, etc.) | Phase 2 |
| Regional / language browsing (by province, by Bemba, Nyanja, Tonga, etc.) | Phase 2 |
| Creator Quality Score | Phase 3 — needs usage data to be meaningful |
| Premieres (scheduled live events, countdowns, live chat, post-premiere Q&A) | Phase 3 |
| Tipping, creator-set subscriptions, sponsorships | Phase 3 — additive to the watch-time pool, never a replacement for it |
| Creator badges (Verified, Top Creator, Emerging Talent) | Phase 3 |
| AI-driven guidance for creators ("your audience watches longer at 15–25 minutes") | Phase 3 |
| Richer movie landing pages (production notes, creator story, gallery) | Phase 3 |
| In-player next-episode / behind-the-scenes overlays | Phase 3 |
| Seasons / structured series organization | Phase 3 |
| Creator Growth Journey (public progress story) | Phase 3–4 |
| Livestreams, concerts, merchandise, fan clubs, multi-country expansion | Phase 4 |
| Standalone GrowStream Studio app (separated from Creator Mode) | Only once creator volume justifies the split |

Table 6 — The deferred list, kept visible so it is revisited deliberately rather than by accident.

## 14. Device & Platform Scope

Phase 1 should target mobile-first, given the market and the existing GrowBuilder strategy's mobile-first emphasis:
- Mobile web / responsive app as the primary target
- Desktop as a secondary, supported experience — not the primary design target
- Native mobile app and any TV/connected-device app are explicitly out of Phase 1 scope; a well-built responsive mobile experience is the right starting point, consistent with the platform's operating constraint of small-team, one-priority-at-a-time execution

## 15. Payments & Payout — Open Scoping Questions

As flagged in the Scope Note, the payments and payout engine is the likely critical path for Phase 1 and needs its own scoping before engineering estimates are meaningful. The questions below are not answered by this UI plan and should be resolved as their own workstream.

- **Settlement frequency:** how often are creators actually paid out (weekly, monthly), and does the UI need to reflect a pending-vs-settled distinction?
- **Minimum payout threshold:** is there a minimum balance before a payout is issued, and how is that communicated to creators?
- **Dispute and reconciliation process:** what happens when a creator disagrees with their watch-time calculation, and what does that flow look like for the creator and for admin?
- **Mobile money provider scope:** which providers (MTN, Airtel, others) are supported at launch, and does checkout UI need to branch per provider?
- **Refund handling:** how does a viewer-side subscription refund or cancellation interact with money already allocated to the creator pool for that period?
- **Failure and retry handling:** what does the UI show when a mobile money payment is pending confirmation rather than immediately succeeding or failing?

None of these are UI decisions on their own, but each has a direct UI consequence (a pending state, a dispute screen, a provider selector) that cannot be finalized until the underlying payments workstream answers them.

## 16. Related Documents

This plan is deliberately scoped to Phase 1 UI. It is one of three documents intended to describe GrowStream in full:

- **GrowStream Strategic Plan** — business vision, market, positioning, monetization model, expansion strategy
- **GrowStream UX Vision** — the five-year product experience, including Premieres, the creator economy, AI tooling, and community features, regardless of when each is built
- **GrowStream Product Roadmap / UI Plan (this document)** — a strict, phase-by-phase implementation plan

Keeping these separate protects the long-term vision from being lost while keeping this document honest about what is actually being built next.
