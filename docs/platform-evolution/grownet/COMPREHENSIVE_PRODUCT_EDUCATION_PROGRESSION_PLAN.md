# MyGrowNet — GrowNet
## Comprehensive Product, Education, Points, Progression and Rewards Plan

This document consolidates GrowNet's membership packages, education subscriptions, workshops infrastructure, Facilitator Portal, GrowStream Video Streaming, GrowMusic Portal & Artist Ecosystem, 3x7 Matrix System, Growth Loyalty Reward (LGR) Pool, the seven Education Levels, LP/BP points, Predefined Physical Position Rewards, Digital Resource Library & Kit Downloads, Team Management Tools, and Position/Monthly Bonuses into one connected system, while keeping each component's purpose clearly separated and fully building upon existing production architecture.

- **Product:** GrowNet
- **Platform:** MyGrowNet
- **Model:** Membership + structured education + 3x7 matrix + LGR profit pool + workshops + video streaming + music portal & artist hubs + resource downloads + physical position rewards + team management tools + performance rewards
- **Progression:** Seven Professional Education Levels
- **Primary Point Systems:** Life Points (LP) and Bonus Points (BP / MAP)
- **Primary Rewards:** Position Milestone Cash Bonus, Predefined Physical Position Rewards (Smartphones, Motorbikes, Vehicles, Property), and Growth Loyalty Reward (LGR) Pool Distributions

---

## 1. Executive Product Definition

GrowNet is a structured member development and business education platform with a network-based rewards system. A member joins through a membership package, gains access to applicable education, downloadable kit resources, video streaming, music portal tools, and business infrastructure, develops through seven progressive Education Levels, participates in learning and workshops, develops a team through the 3x7 Matrix System, generates qualifying activity, accumulates Life Points (LP), Bonus Points (BP), and Growth Loyalty Reward (LGR) activity credits, and receives rewards, profit shares, and predefined physical position assets according to the applicable rules.

The system distinguishes between what a member purchases, what a member learns, what a member downloads, what a member does, what their team does in the matrix, what points they accumulate, what level they have achieved, and what financial and physical position rewards they qualify for. This separation is reflected in the business model, the database, the interface, and the compensation rules.

---

## 33. Existing 3x7 Matrix Engine Architecture (`matrix_positions`)

GrowNet leverages an existing, production-proven **3x7 Forced Matrix Engine** (`matrix_positions` table):

```
                                  [ MEMBER (Level 0) ]
                                   /       |       \
                            [ Pos 1 ]  [ Pos 2 ]  [ Pos 3 ]      (Level 1: 3 Members)
                            /   |   \  /   |   \  /   |   \
                           [  9 Matrix Positions (Level 2)  ]    (Level 2: 9 Members)
                           /   |   \  /   |   \  /   |   \
                          [  27 Matrix Positions (Level 3)  ]   (Level 3: 27 Members)
                          ... down to Level 7 (2,187 Members)
```

### Matrix Rules & Placement Mechanics:
- **3x7 Downline Matrix**: Every member position supports up to 3 direct front-line positions, extending down 7 levels (3 $\rightarrow$ 9 $\rightarrow$ 27 $\rightarrow$ 81 $\rightarrow$ 243 $\rightarrow$ 729 $\rightarrow$ 2,187 members).
- **Spillover & Placement Engine**: Handled by `MatrixPlacementService.php`. When a member sponsors more than 3 direct referrals, additional referrals automatically spill over into the first open position in their 3x7 matrix tree from top-to-bottom, left-to-right.
- **Level Matrix Commissions**: Matrix positions generate level commissions according to the member's current active level (15% Level 1, 10% Level 2, 8% Level 3, 6% Level 4, 4% Level 5, 3% Level 6, 2% Level 7).

---

## 34. Growth Loyalty Reward (LGR) Pool System (`lgr_settings`)

GrowNet incorporates an existing, fully built **Growth Loyalty Reward (LGR) Pool System** (`lgr_settings`, `lgr_packages`, `loyalty_growth_cycles` tables):

### 1. LGR Cycle & Profit Share Mechanics:
- **90-Day LGR Cycle**: Operating on defined 90-day cycles with automatic cycle renewal (`auto_start_cycles = 1`).
- **60% Profit Pool Allocation**: 60% of platform profits are allocated into the LGR pool for distribution to qualified members.
- **Minimum Qualification**: Members require a minimum of 5 qualifying activities per cycle to participate.

### 2. Activity Point Weighting Schedule:
| Activity Type | LGR Activity Weight | Description |
|---|---|---|
| `starter_kit_purchase` | **10 Points** | Purchasing or upgrading a Starter Kit |
| `venture_investment` | **10 Points** | Participating in Venture Builder project funding |
| `referral` | **8 Points** | Sponsoring a new active member |
| `course_completion` | **7 Points** | Completing an Education Level course |
| `workshop_attendance` | **6 Points** | Verified check-in at a regional or online workshop |
| `product_purchase` | **5 Points** | Purchasing products or learning materials |
| `subscription_renewal` | **5 Points** | Renewing monthly education subscription |
| `community_engagement` | **3 Points** | Participating in community discussions & voting |

- **Premium Tier Multiplier**: Members holding premium starter kits receive a **1.5x multiplier** on LGR pool earnings.

---

## 35. Integration with Existing GrowNet Mobile SPA Dashboard (`GrowNet.vue`)

The architecture integrates directly with the existing **GrowNet Mobile SPA Dashboard** ([`resources/js/Pages/GrowNet/GrowNet.vue`](file:///c:/Apache24/htdocs/mygrownet/resources/js/Pages/GrowNet/GrowNet.vue)):

- **Single SPA Mobile Layout**: Houses Home, Learning, Matrix Tree, Rewards, Starter Kits, and Wallet tabs in a single seamless Vue SPA.
- **Native Widgets**:
  - `Contextual Primary Focus Card`: Renders active level progression, LP/BP points, and next level blockers.
  - `Matrix & Team Level Viewer`: Visualizes downline tree levels (3 $\rightarrow$ 2,187) and downline active subscriptions.
  - `Physical Rewards & LGR Pool Widget`: Renders physical reward allocation status (Smartphones, Motorbikes, Vehicles) and current 90-day LGR cycle pool earnings.
  - `Starter Kit & Entitlement Downloads Banner`: Quick access to downloadable audiobooks, PDFs, and kit materials.

---

## 2. The Core Model — Ten Connected Layers

Membership Packages, the Education Journey, Workshop & Facilitator System, GrowStream & GrowMusic Ecosystems, Resource Library Downloads, 3x7 Matrix Placement, LGR Pool, and Network Activity feed a unified Point System (LP, BP, and LGR Weights), driving Position Progression and Monthly Performance, triggering cash Position Milestone Bonuses, Predefined Physical Position Rewards, and 90-Day LGR Pool Payouts.

```
[ 1. Membership Packages     ] ──┐
[ 2. Education Journey       ] ──┼──> [ 7. Point System (LP, BP, LGR)] ──> [ 8. Position & Monthly Performance ] ──> [ 9. Cash Milestone Bonuses        ]
[ 3. Workshops & Facilitators] ──┤                                                                                   ├──> [ 10. Predefined Physical Rewards  ]
[ 4. GrowStream & GrowMusic  ] ──┤                                                                                   └──> [ 11. 90-Day LGR Pool Payouts    ]
[ 5. 3x7 Matrix System       ] ──┤
[ 6. Digital Resource Library] ──┘
```

---

## 20B. Predefined Physical Position Rewards & Entitlements Catalog

Reconciled directly from production schema (`physical_rewards` & `physical_reward_allocations` tables):

Every member who achieves and sustains a qualifying Education Level is automatically entitled to receive a **Predefined Physical Position Reward asset**:

| Level | Title | Cash Milestone Bonus | Predefined Physical Position Reward | Estimated Value | Specifications & Entitlement Terms |
|---|---|---|---|---|---|
| **L1** | **Associate** | Base Enrolment | **MyGrowNet Starter Kit** | K500 | Branded t-shirt, business cards, training manual, welcome package |
| **L2** | **Professional** | **K500 Cash** | **Branded Electronics Kit** | K1,000 | Power bank, wireless earbuds, professional business planner |
| **L3** | **Senior** | **K1,500 Cash** | **Smartphone or Tablet Package** | K3,000 | Choice of Samsung Galaxy A54, iPhone SE, or iPad (2-yr warranty) |
| **L4** | **Manager** | **K5,000 Cash** | **Motorbike Package OR Office Equipment Kit** | K12,000 | 125cc Honda CB125F motorbike for delivery/income generation OR Laptop, Printer, Desk & Chair |
| **L5** | **Director** | **K15,000 Cash** | **Car Package OR Property Down Payment Assistance** | K35,000 | Toyota Vitz / Honda Fit vehicle with 3-yr insurance OR K25,000 Property Down Payment |
| **L6** | **Executive** | **K50,000 Cash** | **Luxury Car Package** | K75,000 | Toyota Camry / BMW 3 Series executive vehicle with 3-yr service plan |
| **L7** | **Ambassador** | **K150,000 Cash** | **Property Investment Package** | K100,000 – K150,000 | Multi-unit residential or commercial property in Lusaka / Copperbelt |

---

## 3. Membership Package vs. Education Level

This distinction is strictly enforced throughout GrowNet. A Membership Package answers *“what package has this member purchased or subscribed to?”* An Education Level answers *“what stage of development has this member achieved?”* They are related but not the same thing — a Business member does not automatically become a Level 3 Senior Practitioner, and someone who reaches Level 5 Director does not need a higher membership tier merely to hold the Level 5 title.

---

## 31. Platform Subdomain Architecture & Cross-Subdomain Connectivity (GrowNet ↔ GrowStream ↔ GrowMusic)

The platform operates across three specialized, dedicated subdomains backed by a single unified identity and compensation engine:

| Subdomain | Platform Name | Domain URL | Primary Purpose |
|---|---|---|---|
| **Central Domain** | **GrowNet Core & MLM** | `mygrownet.com` | Member SPA Dashboard (`GrowNet.vue`), 3x7 Matrix, LGR Pool, Education Progression, Wallet & Position Qualification |
| **Identity Gateway** | **MyGrow Identity** | `auth.mygrownet.com` | Centralized Single Sign-On (SSO), Authentication, 2FA & Session Validation |
| **Video Streaming** | **GrowStream** | `growstream.mygrownet.com` | Movies, Series, Video Shows, B2B Video Hubs & Content Moderation |
| **Music Streaming** | **GrowMusic** | `growmusic.mygrownet.com` | Audio Tracks, Singles, Music Videos, Artist Fan Clubs & ZAMCO Royalties |

---

## 29. GrowNet Compensation and Progression Rules Specification (Reconciled Codebase Specification)

Reconciled directly from production seeders (`ProfessionalLevelSeeder.php`, `PackageSeeder.php`, `LgrSettingsSeeder.php`, `2025_10_17_100000_create_points_system_tables.php`, `StarterKitContentSeeder.php`, `WorkshopSeeder.php`, `2025_01_15_000005_enhance_physical_rewards_for_mygrownet_performance.php`):

### Master Level Table (Codebase Reconciled)

| Level | Title (`slug`) | Min Active Time | Required LP (Cumulative) | Required Monthly BP (MAP) | Team 3x7 Matrix Size | Cash Milestone Bonus | Predefined Physical Position Reward | Profit Share Multiplier | Level Commission Rate |
|---|---|---|---|---|---|---|---|---|---|
| **L1** | **Associate** (`associate`) | Immediate | 0 LP | 100 BP | 3 Members | Base Enrolment | **Starter Kit** (Merch & Manual) | 1.0x | 15% (Level 1) |
| **L2** | **Professional** (`professional`) | 1 Month Active | 2,500 LP | 200 BP | 9 Members | **K500 Cash** | **Branded Electronics Kit** | 1.2x | 10% (Level 2) |
| **L3** | **Senior** (`senior`) | 3 Months Active | 4,000 LP | 300 BP | 27 Members | **K1,500 Cash** | **Smartphone / Tablet Package** (K3,000) | 1.5x | 8% (Level 3) |
| **L4** | **Manager** (`manager`) | 6 Months Active | 12,500 LP | 400 BP | 81 Members | **K5,000 Cash** | **Motorbike Package OR Office Equipment Kit** (K12,000) | 2.0x | 6% (Level 4) |
| **L5** | **Director** (`director`) | 12 Months Active | 60,000 LP | 500 BP | 243 Members | **K15,000 Cash** | **Car Package OR Property Down Payment** (K35,000) | 2.5x | 4% (Level 5) |
| **L6** | **Executive** (`executive`) | 18 Months Active | 160,000 LP | 600 BP | 729 Members | **K50,000 Cash** | **Luxury Car Package** (K75,000) | 3.0x | 3% (Level 6) |
| **L7** | **Ambassador** (`ambassador`) | 24 Months Active | 350,000 LP | 800 BP | 2,187 Members | **K150,000 Cash** | **Property Investment Package** (K100,000+) | 4.0x MAX | 2% (Level 7) |
