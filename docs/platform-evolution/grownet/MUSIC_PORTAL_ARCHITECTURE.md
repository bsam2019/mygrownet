# MyGrowNet — GrowMusic Subdomain Architecture & Platform Integration Specification

This document details the architectural design, standalone subdomain structure, user journeys, data models, audio player UI, and cross-subdomain connectivity for **GrowMusic** (`growmusic.mygrownet.com`) and its seamless integration with **GrowStream** (`growstream.mygrownet.com`) and **GrowNet** (`mygrownet.com`).

---

## 1. Executive Vision & Subdomain Authority

**GrowMusic** (`growmusic.mygrownet.com`) is a dedicated, standalone audio and music streaming platform operating alongside **GrowStream** (`growstream.mygrownet.com`) and **GrowNet** (`mygrownet.com`).

It empowers Zambian musicians, producers, sound engineers, and record labels to upload, monetize, market, and distribute their audio content to fans locally and globally.

---

## 2. Platform Subdomain Ecosystem

```
                                  ┌────────────────────────────────┐
                                  │   auth.mygrownet.com           │
                                  │   (Central Identity Gateway)   │
                                  └───────────────┬────────────────┘
                                                  │ (Cross-Subdomain SSO Cookie: .mygrownet.com)
            ┌─────────────────────────────────────┼─────────────────────────────────────┐
            ▼                                     ▼                                     ▼
┌───────────────────────┐             ┌───────────────────────┐             ┌───────────────────────┐
│  growmusic.mygrownet  │             │ growstream.mygrownet  │             │   mygrownet.com       │
│  (GrowMusic Portal)   │             │ (GrowStream Video)    │             │ (GrowNet MLM & Edu)   │
└───────────┬───────────┘             └───────────┬───────────┘             └───────────┬───────────┘
            │                                     │                                     │
            └─────────────────────────────────────┴─────────────────────────────────────┘
                                                  │
                                                  ▼
                                 ┌─────────────────────────────────┐
                                 │   Unified Database & Event Bus   │
                                 │ (LP/BP Points, Wallet, Quotas)  │
                                 └─────────────────────────────────┘
```

---

## 3. Cross-Subdomain Ecosystem Connectivity (GrowMusic ↔ GrowStream ↔ GrowNet)

### A. Single Sign-On (SSO) & Shared Identity
- **Central Gateway**: All authentication (Login, Register, 2FA, Password Reset) is served exclusively by `auth.mygrownet.com`.
- **Shared Session Cookie**: Set with `SESSION_DOMAIN=.mygrownet.com`. A user who logs into GrowNet or GrowStream is automatically authenticated when visiting `growmusic.mygrownet.com` without re-entering credentials.

### B. Unified Point & Ledger Engine (LP & BP Points)
- Listening to music on `growmusic.mygrownet.com` logs verified streaming events (`music_stream_logs`) which dispatch point events to GrowNet's centralized ledger (`point_transactions`).
- Every 1,000 verified streams awards **+50 Life Points (LP)** for position progression and **+25 Bonus Points (BP)** for monthly performance in the member's GrowNet dashboard.

### C. Global Platform App Switcher
- Every platform header (GrowMusic, GrowStream, GrowNet) incorporates the **Global Platform Flyout Switcher** allowing instant 1-click navigation between:
  - 🎵 **GrowMusic** (`growmusic.mygrownet.com`)
  - 🎬 **GrowStream** (`growstream.mygrownet.com`)
  - 🚀 **GrowNet Workspace & Academy** (`mygrownet.com/workspace`)

### D. Creator Studio & Artist Hub Integration
- Musicians who launch an **Artist Creator Hub** (e.g. `artist.growmusic.app` or `growmusic.mygrownet.com/hub/artist`) have their subscription billing and payouts settled through GrowNet's central financial wallet (`member_payments`).

---

## 4. The GrowMusic Portal Interface & Experience (`growmusic.mygrownet.com`)

### A. Music Catalog & Discovery Navigation
- **Trending Zambian Singles**: Daily & weekly top streaming charts.
- **Genre Hubs**: Zed Kalindula, Afro-Beats, Zambian Gospel, Hip Hop, Dancehall, Amapiano, Traditional & Cultural Sounds.
- **Featured Playlists**: *Lusaka Beats*, *Copperbelt Energy*, *Gospel Inspiration*, *Acoustic Sessions*.
- **New Release Radar**: Direct notifications when subscribed artists release new tracks.

### B. Persistent Audio Player Engine
- **Edge-to-Edge Bottom Player Bar**: Stays active as users navigate across GrowMusic.
- **High-Definition Audio & Low-Bandwidth Mode**: Supports 320kbps HD MP3 audio stream with automatic failover to 96kbps compressed audio for low-data mobile networks.
- **Offline PWA Storage**: Unlocked tracks can be saved to local device cache for offline listening without internet data.
- **Synchronized Lyrics & Credits**: Displays verified song lyrics, producer credits, and songwriter attributions.

---

## 5. Artist Creator Hubs (VIP Fan Clubs & Subscriptions)

Musicians can launch their own white-label **Music Creator Hub** (e.g. `artist.growmusic.app`):

| Fan Tier | Monthly Price | Included Benefits |
|---|---|---|
| **Bronze Fan** | K50 / month | Unlimited ad-free streaming, early song releases (24h before public), exclusive audio commentary |
| **Silver Supporter** | K150 / month | All Bronze benefits + unreleased acoustic stems, high-res download access, VIP fan chat |
| **Gold Backstage VIP** | K500 / month | All Silver benefits + free concert ticket discounts, official artist merch item, monthly 1-on-1 Q&A stream |

---

## 6. Music Business Education Curriculum (GrowNet Music Track)

Musicians progress through seven dedicated Education Levels tailored for the music industry on GrowNet:

- **Level 1 — Associate**: Music Copyright 101, ZAMCO Rights Registration, Metadata & ISRC Codes.
- **Level 2 — Professional**: Digital Distribution Strategy, Audio Mastering Basics, Streaming Algorithmic Placement.
- **Level 3 — Senior**: Music Marketing, Social Media Brand Identity, Independent Release Campaign Execution.
- **Level 4 — Manager**: Live Show Booking, Tour Logistics, Artist Management Contract Structuring.
- **Level 5 — Director**: Music Publishing, Synchronization Licensing (TV/Film/Ads), Catalog Monetization.
- **Level 6 — Executive**: Record Label Operations, A&R Strategy, Regional Tour Production.
- **Level 7 — Ambassador**: Global Music Distribution Networks, Venture Royalty Crowdfunding, Master Catalog Ownership.

---

## 7. Royalty Settlement & Artist Payout Engine

1. **Pay-Per-Stream Royalty Allocation**:
   - Every verified stream (>30 seconds) logs a record in `music_stream_logs`.
   - Streaming royalties are accumulated in real time and disbursed directly to the artist's Mobile Money wallet (MTN / Airtel / Zamtel) or GrowNet Financial Wallet.

2. **Life Points (LP) & Bonus Points (BP) Rewards**:
   - 1,000 Verified Streams = +50 Life Points (LP) and +25 Bonus Points (BP) toward the artist's Education Level progression.

---

## 8. Summary of Subdomain Domain Setup & Route Handling

- **Subdomain Middleware**: Handler in `DetectSubdomain.php` for `growmusic`.
- **Route File**: `routes/growmusic.php` serving all music catalog, audio player, artist hub, and stream log routes at root `/` on `growmusic.mygrownet.com`.
- **Blade Layout & Service Provider**: `GrowMusicServiceProvider.php` registering `database/migrations/growmusic/` and view mappings.
