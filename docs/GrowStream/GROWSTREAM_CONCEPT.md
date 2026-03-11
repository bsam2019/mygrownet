# GrowStream - Netflix-Style Video Streaming Module

**Last Updated:** March 11, 2026  
**Version:** 2.0 (Major Architecture Update)  
**Status:** Concept & Planning Phase - Production Ready  
**Platform:** MyGrowNet  
**Tech Stack:** Laravel 12 + Vue 3 + Cloudflare Stream  
**Architecture:** Domain-Driven Design (DDD) Modular Structure

---

## Document Overview

This is the complete technical specification for GrowStream, a professional Netflix-style video streaming module for the MyGrowNet platform. This document has been enhanced with critical architectural requirements including:

- **DDD Modular Architecture** - Structured as a standalone domain module
- **Event-Driven Processing** - Asynchronous video workflows
- **Comprehensive Metadata** - Production-ready content structure
- **Series Support** - Built-in episodic content from day one
- **Progress Tracking** - Netflix-style continue watching
- **Analytics Foundation** - Data collection for future recommendations
- **Creator Economy Ready** - Infrastructure for revenue sharing
- **Mobile API Ready** - Designed for future mobile apps
- **Clear MVP Boundaries** - 8-10 week shippable scope

---

## Table of Contents

1. [Module Overview](#1-module-overview)
2. [Objectives](#2-objectives)
3. [Core Features](#3-core-features)
4. [Business Model and Monetization](#4-business-model-and-monetization)
5. [Technical Architecture](#5-technical-architecture)
6. [Database Design](#6-database-design)
7. [Video Provider Abstraction](#7-video-provider-abstraction)
8. [User Roles and Permissions](#8-user-roles-and-permissions)
9. [Access Control and Subscription Logic](#9-access-control-and-subscription-logic)
10. [Admin Panel Requirements](#10-admin-panel-requirements)
11. [Frontend Screens and Pages](#11-frontend-screens-and-pages)
12. [API Endpoints](#12-api-endpoints)
13. [Cloudflare Stream Integration Plan](#13-cloudflare-stream-integration-plan)
14. [Security and Content Protection](#14-security-and-content-protection)
15. [MVP Scope](#15-mvp-scope)
16. [Phase 2 and Future Expansion](#16-phase-2-and-future-expansion)
17. [Implementation Roadmap](#17-implementation-roadmap)
18. [Deliverables](#18-deliverables)

---

## 1. Module Overview

### What is GrowStream?

GrowStream is a professional video streaming module for the MyGrowNet platform that delivers a Netflix-style viewing experience. It provides subscription-based access to educational content, local shows, workshops, training programs, and creator-generated content.

### Business Purpose

GrowStream transforms MyGrowNet from a text-based learning platform into a comprehensive multimedia education and entertainment ecosystem. It:

- **Enhances member value** by providing rich video content beyond static learning materials
- **Creates new revenue streams** through subscription tiers and premium content
- **Enables creator economy** by allowing members to produce and monetize content
- **Increases engagement** through binge-worthy series and continuous learning paths
- **Differentiates MyGrowNet** from competitors with professional streaming capabilities


### Target Users

1. **MyGrowNet Members** - Access educational content as part of their subscription
2. **Learners** - Individuals seeking skill development and training
3. **Content Creators** - Members who produce and share educational content
4. **Institutions** - Organizations purchasing bulk access for employees/students
5. **General Public** - Free tier users accessing promotional content

### Content Types Supported

- **Educational Courses** - Structured learning paths with multiple episodes
- **Workshop Recordings** - Live event recordings and masterclasses
- **Short-Form Content** - Quick tips, tutorials, and micro-learning (5-15 min)
- **Documentary Series** - Multi-episode educational documentaries
- **Local Shows** - Community-produced entertainment and cultural content
- **Training Programs** - Professional development and certification courses
- **Creator Content** - Member-generated educational and entertainment videos
- **Webinar Archives** - Recorded webinars and Q&A sessions

### Revenue Growth Impact

GrowStream directly supports MyGrowNet's revenue model by:

1. **Increasing subscription value** - Members get more for their monthly fee
2. **Creating premium tiers** - Exclusive content drives higher-tier subscriptions
3. **Enabling pay-per-content** - Individual course/series purchases
4. **Supporting creator economy** - Revenue sharing attracts quality content producers
5. **Institutional sales** - Bulk licensing to organizations
6. **Advertising opportunities** - Sponsored content and brand partnerships
7. **Data insights** - Viewing analytics inform product development

---

## 2. Objectives

### User Experience Goals

- **Netflix-quality streaming** - Smooth, adaptive playback across devices and connections
- **Intuitive discovery** - Easy browsing, searching, and content recommendations
- **Seamless continuation** - Pick up where you left off across devices
- **Personalization** - Tailored content suggestions based on viewing history
- **Accessibility** - Subtitles, multiple languages, and inclusive design

### Monetization Goals

- **Sustainable revenue** - Cover Cloudflare Stream costs plus profit margin
- **Multiple revenue streams** - Subscriptions, pay-per-view, institutional licensing
- **Creator incentives** - Revenue sharing that attracts quality content producers
- **Conversion optimization** - Free content that converts to paid subscriptions
- **Lifetime value increase** - Reduce churn through engaging content


### Scalability Goals

- **Modular architecture** - Clean separation allowing independent scaling
- **Provider abstraction** - Easy switching between video providers
- **Performance optimization** - Fast loading, minimal buffering
- **Cost efficiency** - Smart encoding and delivery to minimize bandwidth costs
- **Global reach** - CDN-powered delivery for international audiences

### Creator Economy Goals

- **Low barrier to entry** - Simple upload and publishing workflow
- **Fair compensation** - Transparent revenue sharing model
- **Analytics dashboard** - Creators see their performance metrics
- **Quality incentives** - Reward high-engagement content
- **Community building** - Enable creator-audience interaction

---

## 3. Core Features

### 3.1 Video Library Homepage

**Purpose:** Main landing page showcasing content in an engaging, browsable format

**Components:**
- **Hero Banner** - Featured content with auto-playing trailer
- **Continue Watching** - Resume videos from last position
- **Trending Now** - Most-viewed content in last 7 days
- **New Releases** - Recently published content
- **Category Rows** - Horizontal scrolling rows by genre/topic
- **Personalized Recommendations** - Based on viewing history
- **Quick Search** - Prominent search bar

**Technical Notes:**
- Lazy loading for performance
- Infinite scroll or pagination
- Cached queries for speed
- Real-time view counts

### 3.2 Categories and Genres

**Purpose:** Organize content for easy discovery

**Category Types:**
- **Educational Topics** - Business, Technology, Health, Finance, etc.
- **Content Format** - Courses, Workshops, Documentaries, Shorts
- **Skill Level** - Beginner, Intermediate, Advanced
- **Duration** - Quick Bites (<15min), Standard (15-60min), Deep Dives (>60min)
- **Language** - English, Local languages
- **Creator Type** - MyGrowNet Official, Verified Creators, Community

**Features:**
- Multi-category tagging
- Dynamic category creation
- Category-specific landing pages
- Subcategory support


### 3.3 Featured and Trending Content

**Featured Content:**
- Admin-curated spotlight content
- Promotional banners for new releases
- Seasonal or event-based features
- Partner/sponsor highlights
- Time-limited featured slots

**Trending Algorithm:**
- View count weighted by recency
- Engagement metrics (completion rate, likes)
- Share and save frequency
- Velocity of growth (trending up)
- Manual boost capability for admins

### 3.4 Continue Watching

**Functionality:**
- Automatic progress tracking
- Resume from exact timestamp
- Cross-device synchronization
- Remove from continue watching option
- Episode auto-advance for series

**Technical Implementation:**
- Store watch progress in `watch_history` table
- Update progress every 10 seconds during playback
- Mark as "completed" at 95% watched
- Expire old entries after 30 days

### 3.5 Watch History

**Features:**
- Complete viewing history
- Filter by date range
- Search within history
- Clear history option
- Export history data

**Privacy:**
- User-controlled visibility
- Option to pause history tracking
- Bulk delete capability

### 3.6 My List / Saved Videos

**Purpose:** Personal watchlist for bookmarking content

**Features:**
- One-click add/remove
- Organize into custom playlists (future)
- Share lists with others (future)
- Notifications when saved content updates
- Sort by date added, title, duration

### 3.7 Series and Episodes Support

**Structure:**
- **Series** - Container for related episodes
- **Seasons** - Optional grouping within series
- **Episodes** - Individual videos within series
- **Sequential ordering** - Episode 1, 2, 3, etc.
- **Auto-play next episode** - Configurable countdown

**Metadata:**
- Series description and artwork
- Season summaries
- Episode titles and descriptions
- Release dates
- Total runtime


### 3.8 Creator Channels

**Purpose:** Dedicated spaces for content creators to build their brand

**Channel Features:**
- Creator profile and bio
- Channel banner and avatar
- All videos by creator
- Subscriber count
- Social media links
- Contact/collaboration options

**Creator Tools:**
- Upload dashboard
- Analytics overview
- Revenue reports
- Audience demographics
- Content performance metrics

### 3.9 Search and Filter

**Search Capabilities:**
- Full-text search across titles, descriptions, tags
- Auto-complete suggestions
- Search history
- Trending searches
- Voice search (future)

**Filter Options:**
- Category/genre
- Duration range
- Release date
- Content type (free/premium)
- Language
- Creator
- Skill level
- Sort by: Relevance, Date, Views, Rating

### 3.10 Subscription-Based Access Control

**Access Levels:**
- **Guest** - Preview content only (trailers, first 5 minutes)
- **Free Member** - Limited free content library
- **Basic Subscriber** - Standard content library
- **Premium Subscriber** - All content including exclusives
- **Institutional** - Custom access packages

**Integration:**
- Ties into existing MyGrowNet subscription system
- Seamless upgrade prompts
- Trial period support
- Grace period for expired subscriptions

### 3.11 Free vs Premium Content

**Free Content Strategy:**
- Introductory episodes of series
- Selected workshops and webinars
- Community-contributed content
- Promotional content
- Trailers and previews

**Premium Content:**
- Full series access
- Exclusive workshops
- Advanced training programs
- Early access to new releases
- Ad-free experience
- Downloadable resources


### 3.12 Video Detail Page

**Layout:**
- Large video player or thumbnail
- Play/Resume button
- Title and episode information
- Creator/channel link
- View count and publish date
- Description (expandable)
- Tags and categories
- Add to My List button
- Share button
- Like/Rating (optional)

**Metadata Display:**
- Duration
- Language
- Subtitles available
- Content rating
- Skill level
- Related resources/downloads

**Related Content Section:**
- More from this series
- More from this creator
- Similar content recommendations
- Users also watched

### 3.13 Related Content Suggestions

**Algorithm Factors:**
- Same category/tags
- Same creator
- Similar duration
- Viewing patterns of similar users
- Completion rate correlation
- Manual curation by admins

**Display:**
- Thumbnail grid below video
- Sidebar during playback
- End-of-video recommendations
- Auto-play next suggestion (optional)

### 3.14 Admin Upload and Moderation Tools

**Upload Workflow:**
1. Select video file or provide URL
2. Upload to Cloudflare Stream (or staging)
3. Add metadata (title, description, tags)
4. Set access level (free/premium)
5. Assign categories
6. Upload thumbnail and poster
7. Add subtitles (optional)
8. Set publish date/time
9. Review and publish

**Moderation Features:**
- Content review queue
- Approval workflow
- Flagging system
- Content takedown
- Creator warnings/bans
- Automated content scanning (future)

**Bulk Operations:**
- Batch upload
- Bulk categorization
- Mass publish/unpublish
- Bulk access level changes


### 3.15 Creator Upload Flow (Future Phase)

**Self-Service Upload:**
- Creator dashboard access
- Drag-and-drop upload
- Progress tracking
- Automatic encoding status
- Metadata form
- Preview before publish
- Submit for review

**Creator Requirements:**
- Verified creator status
- Accepted terms and conditions
- Content guidelines acknowledgment
- Tax/payment information on file

**Review Process:**
- Automated checks (duration, format, size)
- Manual review by content team
- Approval/rejection with feedback
- Revision and resubmission

### 3.16 View Analytics

**User Analytics (Personal):**
- Total watch time
- Videos completed
- Favorite categories
- Viewing streaks
- Learning progress

**Creator Analytics:**
- Total views per video
- Watch time and completion rate
- Audience demographics
- Traffic sources
- Revenue earned
- Engagement metrics

**Admin Analytics:**
- Platform-wide metrics
- Content performance
- User engagement trends
- Revenue reports
- Bandwidth usage
- Popular categories
- Peak viewing times
- Churn analysis

### 3.17 Playback Restrictions and Security

**Restrictions:**
- Geographic restrictions (future)
- Device limits per account
- Concurrent stream limits
- Download restrictions
- Screen recording prevention (where possible)
- Watermarking (future)

**Security Measures:**
- Signed playback URLs
- Token-based authentication
- Session validation
- Rate limiting
- DRM support (future)
- Content encryption

### 3.18 Multi-Device Playback Readiness

**Device Support:**
- Desktop browsers (Chrome, Firefox, Safari, Edge)
- Mobile browsers (iOS Safari, Android Chrome)
- Tablet optimization
- Smart TV readiness (future)
- Mobile app readiness (future)

**Responsive Player:**
- Adaptive bitrate streaming
- Auto-quality selection
- Manual quality override
- Bandwidth detection
- Offline mode preparation (future)


### 3.19 Thumbnails, Posters, Trailers, and Metadata Management

**Visual Assets:**
- **Thumbnail** - Small preview image (16:9 ratio, 320x180px min)
- **Poster** - Large hero image (16:9 ratio, 1920x1080px)
- **Banner** - Channel/series banner (21:9 ratio)
- **Trailer** - Short preview video (30-90 seconds)

**Storage Strategy:**
- Store in Wasabi/S3 (not Cloudflare Stream)
- Multiple sizes generated automatically
- WebP format for modern browsers
- JPEG fallback for compatibility
- CDN delivery for fast loading

**Metadata Fields:**
- Title (required)
- Description (required)
- Long description (optional)
- Tags (comma-separated)
- Categories (multi-select)
- Language
- Subtitles available
- Duration (auto-detected)
- Release date
- Content rating
- Skill level
- Related resources (PDFs, links)

**SEO Optimization:**
- Meta titles and descriptions
- Open Graph tags
- Schema.org markup
- Sitemap inclusion
- Canonical URLs

---

## 4. Business Model and Monetization

### 4.1 Revenue Streams

#### Monthly Subscription
- **Basic Plan** (K50/month) - Access to standard content library
- **Premium Plan** (K100/month) - All content + early access + downloads
- **Family Plan** (K150/month) - Up to 5 profiles
- **Annual Plans** - 2 months free (K500/K1000)

#### Premium Content Subscription
- **Course Bundles** - K200-K500 per course series
- **Workshop Access** - K50-K150 per workshop
- **Certification Programs** - K500-K2000 with certificate

#### Pay-Per-Series or Pay-Per-Course
- Individual series purchase (K100-K300)
- Single workshop rental (K30-K80, 48-hour access)
- Lifetime access option

#### Creator Revenue Share
- **70/30 split** - Creators get 70% of revenue from their content
- **Tiered system** - Top performers get 80/20
- **Bonus pool** - Monthly bonus for top 10 creators


#### Sponsored Content
- Brand partnerships for educational content
- Sponsored series or workshops
- Product placement in relevant content
- Pre-roll ads on free content (future)

#### Business or Institutional Content Packages
- **Corporate Training** - Custom pricing for 50+ employees
- **Educational Institutions** - Student access packages
- **NGO/Community Groups** - Discounted bulk access
- **White-label options** - Custom branding (future)

### 4.2 Cost Structure and Profitability

**Cloudflare Stream Costs:**
- Storage: $5/1000 minutes stored
- Delivery: $1/1000 minutes delivered
- Example: 1000 hours content = $300/month storage
- Example: 10,000 views (30min avg) = $50/month delivery

**Break-Even Analysis:**
- Target: 500 paying subscribers at K75 average = K37,500/month
- Cloudflare costs (moderate usage): K1,500/month
- Creator payouts (30%): K11,250/month
- Net revenue: K24,750/month
- Operating margin: 66%

**Profitability Strategies:**
1. **Efficient encoding** - Optimize quality vs. file size
2. **Smart caching** - Reduce redundant delivery
3. **Popular content focus** - Promote high-value content
4. **Tiered access** - Most users on basic plans
5. **Creator incentives** - Reward content that drives subscriptions
6. **Institutional sales** - High-margin bulk deals
7. **Freemium conversion** - Free content that converts to paid

**Scaling Economics:**
- Fixed costs decrease per user as platform grows
- Content library becomes more valuable over time
- Network effects from creator community
- Data-driven content investment decisions

---

## 5. Technical Architecture

### 5.1 Domain-Driven Modular Architecture

**CRITICAL REQUIREMENT:** GrowStream must be implemented as a standalone domain module following MyGrowNet's existing modular architecture pattern (GrowNet, GrowFinance, GrowMarket, etc.).

**Module Isolation Benefits:**
- Clear boundaries and responsibilities
- Independent testing and deployment
- Team scalability
- Maintainability as platform grows
- Reusable across MyGrowNet ecosystem

**Module Structure:**
```
app/Domain/GrowStream/              # Domain module root
├── Application/                    # Application layer
├── Domain/                         # Core business logic
├── Infrastructure/                 # External integrations
├── Presentation/                   # HTTP/API layer
└── GrowStreamServiceProvider.php   # Module service provider
```

### 5.2 Event-Driven Architecture

**CRITICAL REQUIREMENT:** All video processing workflows must be asynchronous and event-driven to prevent blocking the main application.

**Event-Driven Workflows:**
```
Video Upload → VideoUploadedEvent → ProcessVideoJob (Queue)
                                  → GenerateThumbnailsJob (Queue)
                                  → SyncCloudflareMetadataJob (Queue)
                                  → NotifyCreatorJob (Queue)

Video Processing Complete → VideoReadyEvent → UpdateAnalyticsJob
                                            → NotifySubscribersJob
                                            → IndexForSearchJob

Watch Progress → WatchProgressUpdatedEvent → UpdateAnalyticsJob
                                           → UpdateRecommendationsJob
```

**Queue Configuration:**
- Separate queues for different priorities (high, default, low)
- Failed job handling with retry logic
- Job monitoring and alerting
- Horizontal scaling of queue workers

### 5.3 System Overview

```
┌─────────────────────────────────────────────────────────────┐
│                         Frontend (Vue 3)                     │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │  Browse  │  │  Watch   │  │  Search  │  │  Profile │   │
│  │  Pages   │  │  Player  │  │  Filter  │  │  Dashboard│   │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘   │
└─────────────────────────────────────────────────────────────┘
                              │
                              │ API Calls (Axios)
                              ▼
┌─────────────────────────────────────────────────────────────┐
│              GrowStream Domain Module (Laravel)              │
│  ┌──────────────────────────────────────────────────────┐  │
│  │         Presentation Layer (API Controllers)          │  │
│  └──────────────────────────────────────────────────────┘  │
│  ┌──────────────────────────────────────────────────────┐  │
│  │         Application Layer (Use Cases, DTOs)           │  │
│  └──────────────────────────────────────────────────────┘  │
│  ┌──────────────────────────────────────────────────────┐  │
│  │    Domain Layer (Entities, Services, Events)          │  │
│  └──────────────────────────────────────────────────────┘  │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  Infrastructure (Providers, Repositories, Jobs)       │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                              │
                ┌─────────────┼─────────────┬──────────────┐
                ▼             ▼             ▼              ▼
        ┌──────────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐
        │  Cloudflare  │ │  Wasabi  │ │ Database │ │  Queue   │
        │    Stream    │ │  Storage │ │  MySQL   │ │  Redis   │
        │              │ │          │ │          │ │          │
        │ Video Upload │ │ Thumbs   │ │ Metadata │ │ Jobs     │
        │ Encoding     │ │ Posters  │ │ Analytics│ │ Events   │
        │ Delivery     │ │ Subtitles│ │ Users    │ │ Workers  │
        └──────────────┘ └──────────┘ └──────────┘ └──────────┘
```


### 5.4 Content Metadata Design

**CRITICAL REQUIREMENT:** Comprehensive metadata structure from day one to avoid future refactoring.

**Core Metadata Fields:**
```php
// Video Metadata Structure
[
    // Basic Info
    'title' => 'required|max:255',
    'slug' => 'required|unique',
    'description' => 'required|max:1000',
    'long_description' => 'nullable|max:5000',
    
    // Classification
    'content_type' => 'enum:movie,series,episode,lesson,short,workshop',
    'categories' => 'array', // Multiple categories
    'tags' => 'array', // Flexible tagging
    'language' => 'string', // ISO 639-1 code
    'subtitles_available' => 'array', // Available subtitle languages
    
    // Media Assets
    'duration' => 'integer', // seconds
    'poster_image' => 'url',
    'thumbnail_image' => 'url',
    'banner_image' => 'url',
    'trailer_video_id' => 'nullable|exists:videos,id',
    
    // Ratings and Classification
    'content_rating' => 'enum:G,PG,PG-13,R,NR', // Age rating
    'quality_rating' => 'decimal:2,1', // 0.0-5.0 user rating
    'skill_level' => 'enum:beginner,intermediate,advanced,expert',
    
    // Publishing
    'release_date' => 'date',
    'published_at' => 'timestamp',
    'is_published' => 'boolean',
    'is_featured' => 'boolean',
    
    // Series Structure (if applicable)
    'series_id' => 'nullable|exists:video_series,id',
    'season_number' => 'nullable|integer',
    'episode_number' => 'nullable|integer',
    
    // Access Control
    'access_level' => 'enum:free,basic,premium,institutional',
    'is_downloadable' => 'boolean',
    'geographic_restrictions' => 'array',
    
    // SEO
    'meta_title' => 'max:60',
    'meta_description' => 'max:160',
    'keywords' => 'array',
    
    // Analytics Preparation
    'view_count' => 'integer',
    'unique_viewers' => 'integer',
    'total_watch_time' => 'integer', // seconds
    'average_watch_duration' => 'integer',
    'completion_rate' => 'decimal:5,2',
    'like_count' => 'integer',
    'share_count' => 'integer',
]
```

**Series and Episodic Structure:**
```php
// Series Metadata
[
    'title' => 'Introduction to Laravel',
    'total_seasons' => 3,
    'total_episodes' => 24,
    'series_type' => 'enum:course,show,documentary,workshop_series',
    'is_ongoing' => 'boolean',
    'next_episode_date' => 'nullable|date',
]

// Episode Metadata (extends Video)
[
    'series_id' => 'required',
    'season_number' => 'required',
    'episode_number' => 'required',
    'episode_title' => 'optional', // Can differ from main title
    'previous_episode_id' => 'nullable',
    'next_episode_id' => 'nullable',
]
```

### 5.5 Watch Progress Tracking

**CRITICAL REQUIREMENT:** Per-user, per-video progress tracking for "Continue Watching" functionality.

**Progress Tracking Design:**
```php
// watch_history table structure
[
    'user_id' => 'required',
    'video_id' => 'required',
    'current_position' => 'integer', // seconds
    'duration' => 'integer', // total video duration
    'progress_percentage' => 'decimal:5,2',
    'is_completed' => 'boolean',
    'last_watched_at' => 'timestamp',
    'device_type' => 'string',
    'session_id' => 'string',
]
```

**Progress Update Strategy:**
- Update every 10 seconds during playback
- Mark completed at 95% watched
- Sync across devices in real-time
- Expire old progress after 30 days
- Support resume from exact timestamp

**API Endpoints:**
```
POST   /api/v1/watch/progress        # Update progress
GET    /api/v1/continue-watching     # Get in-progress videos
DELETE /api/v1/watch/progress/{id}   # Remove from continue watching
```

### 5.6 Recommendation and Discovery Data Collection

**CRITICAL REQUIREMENT:** Collect comprehensive data from day one to support future recommendation algorithms.

**Data Collection Points:**
```php
// video_views table - Detailed view tracking
[
    'video_id' => 'required',
    'user_id' => 'nullable', // NULL for anonymous
    'watch_duration' => 'integer', // actual seconds watched
    'completion_percentage' => 'decimal:5,2',
    'quality_level' => 'string', // 720p, 1080p, etc.
    'device_type' => 'string',
    'browser' => 'string',
    'os' => 'string',
    'country_code' => 'string',
    'referrer_url' => 'text',
    'traffic_source' => 'string',
    'viewed_at' => 'timestamp',
]

// video_engagement table - User interactions
[
    'user_id' => 'required',
    'video_id' => 'required',
    'is_liked' => 'boolean',
    'rating' => 'integer', // 1-5 stars
    'is_shared' => 'boolean',
    'is_bookmarked' => 'boolean',
    'engagement_score' => 'decimal:5,2', // Calculated metric
]

// user_preferences table - Implicit preferences
[
    'user_id' => 'required',
    'preferred_categories' => 'json', // Category weights
    'preferred_creators' => 'json', // Creator weights
    'preferred_duration' => 'string', // short, medium, long
    'preferred_language' => 'string',
    'watch_time_distribution' => 'json', // Time of day patterns
]
```

**Future Recommendation Readiness:**
- Collaborative filtering data (user-video matrix)
- Content-based filtering data (video features)
- Hybrid approach preparation
- A/B testing framework ready

### 5.7 Bandwidth Protection and Security

**CRITICAL REQUIREMENT:** Protect against unauthorized access and bandwidth abuse.

**Authentication Checks:**
```php
// Before playback authorization
1. Verify user authentication
2. Check subscription status
3. Validate access level
4. Check concurrent stream limits
5. Generate signed playback URL
6. Set URL expiration (24 hours)
7. Log playback authorization
```

**Signed Playback URLs:**
```php
class PlaybackAuthorizationService
{
    public function authorizePlayback(User $user, Video $video): PlaybackAuthorization
    {
        // Verify access
        if (!$this->canWatch($user, $video)) {
            throw new UnauthorizedException();
        }
        
        // Check concurrent streams
        if ($this->exceedsConcurrentLimit($user)) {
            throw new ConcurrentStreamLimitException();
        }
        
        // Generate signed URL
        $signedUrl = $this->videoProvider->getSignedPlaybackUrl(
            $video->provider_video_id,
            expiresIn: 86400, // 24 hours
            userId: $user->id,
            videoId: $video->id
        );
        
        // Log authorization
        $this->logPlaybackAuthorization($user, $video);
        
        return new PlaybackAuthorization(
            playbackUrl: $signedUrl,
            expiresAt: now()->addDay(),
            token: $this->generateSessionToken()
        );
    }
}
```

**Rate Limiting:**
```php
// Prevent abuse
RateLimiter::for('video-playback', function (Request $request) {
    return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('video-upload', function (Request $request) {
    return Limit::perHour(10)->by($request->user()->id);
});
```

### 5.8 Creator Economy Infrastructure

**CRITICAL REQUIREMENT:** Build creator economy foundations from day one, even if payouts come later.

**Creator Profile Structure:**
```php
// creator_profiles table
[
    'user_id' => 'required|unique',
    'display_name' => 'required',
    'bio' => 'text',
    'avatar_url' => 'url',
    'banner_url' => 'url',
    'is_verified' => 'boolean',
    'verification_date' => 'nullable|date',
    'creator_tier' => 'enum:bronze,silver,gold,platinum',
    
    // Revenue Settings
    'revenue_share_percentage' => 'decimal:5,2', // Default 70%
    'payment_method' => 'string',
    'payment_details' => 'encrypted',
    'tax_information' => 'encrypted',
    
    // Statistics
    'total_videos' => 'integer',
    'total_views' => 'bigInteger',
    'total_watch_time' => 'bigInteger',
    'subscriber_count' => 'integer',
    'total_earnings' => 'decimal:15,2',
    'pending_payout' => 'decimal:15,2',
    
    // Status
    'is_active' => 'boolean',
    'can_upload' => 'boolean',
    'upload_limit_per_month' => 'integer',
]

// creator_payouts table (for future)
[
    'creator_id' => 'required',
    'period_start' => 'date',
    'period_end' => 'date',
    'amount' => 'decimal:15,2',
    'currency' => 'string',
    'status' => 'enum:pending,processing,completed,failed',
    'payment_method' => 'string',
    'transaction_reference' => 'string',
    'total_views' => 'bigInteger',
    'total_watch_time' => 'bigInteger',
    'videos_count' => 'integer',
]

// video_revenue table (track per-video earnings)
[
    'video_id' => 'required',
    'creator_id' => 'required',
    'period' => 'date', // Monthly period
    'views' => 'integer',
    'watch_time' => 'integer',
    'revenue_generated' => 'decimal:15,2',
    'creator_share' => 'decimal:15,2',
    'platform_share' => 'decimal:15,2',
]
```

**Creator Dashboard Requirements:**
- Upload and manage videos
- View analytics (views, watch time, engagement)
- Track earnings (even if $0 initially)
- Manage profile and branding
- View payout history (when implemented)
- Access creator resources and guidelines

### 5.9 Analytics and Metrics Collection

**CRITICAL REQUIREMENT:** Comprehensive analytics from day one to inform content strategy.

**Core Metrics to Track:**

**Video Performance:**
- Total views (all-time, daily, weekly, monthly)
- Unique viewers
- Total watch time
- Average watch duration
- Completion rate
- Drop-off points (where users stop watching)
- Replay rate
- Like/dislike ratio
- Share count
- Bookmark/save count

**User Behavior:**
- Active users (DAU, WAU, MAU)
- Session duration
- Videos per session
- Search queries
- Browse patterns
- Device distribution
- Geographic distribution
- Peak viewing times

**Content Discovery:**
- Traffic sources (direct, search, browse, recommendations)
- Category popularity
- Tag effectiveness
- Search result click-through rates
- Featured content performance

**Business Metrics:**
- Subscription conversion rate
- Churn rate
- Revenue per user
- Content ROI
- Creator performance
- Bandwidth costs per video

**Analytics Tables:**
```php
// video_analytics_daily (aggregated daily)
[
    'video_id' => 'required',
    'date' => 'date',
    'views' => 'integer',
    'unique_viewers' => 'integer',
    'total_watch_time' => 'integer',
    'average_watch_duration' => 'integer',
    'completion_rate' => 'decimal:5,2',
    'likes' => 'integer',
    'shares' => 'integer',
    'bookmarks' => 'integer',
]

// platform_analytics_daily (platform-wide)
[
    'date' => 'date',
    'total_views' => 'bigInteger',
    'unique_viewers' => 'integer',
    'total_watch_time' => 'bigInteger',
    'new_subscribers' => 'integer',
    'cancelled_subscriptions' => 'integer',
    'revenue' => 'decimal:15,2',
    'bandwidth_used' => 'bigInteger', // bytes
]
```

### 5.10 Mobile API Readiness

**CRITICAL REQUIREMENT:** Design APIs to support future mobile applications without refactoring.

**Mobile-First API Design:**
```php
// RESTful, stateless API
// Consistent response format
// Pagination support
// Filtering and sorting
// Efficient data transfer (minimal payloads)
// Image optimization (multiple sizes)
// Offline support preparation
```

**Mobile-Specific Endpoints:**
```
GET    /api/v1/mobile/config          # App configuration
GET    /api/v1/mobile/home            # Optimized home feed
POST   /api/v1/mobile/device          # Register device for push notifications
GET    /api/v1/mobile/downloads       # Downloadable content (future)
POST   /api/v1/mobile/playback-quality # Report quality issues
```

**Response Optimization:**
```json
{
  "success": true,
  "data": {
    "videos": [...],
    "images": {
      "thumbnail": {
        "small": "url",
        "medium": "url",
        "large": "url"
      }
    }
  },
  "meta": {
    "pagination": {...},
    "cache_ttl": 300
  }
}
```

### 5.11 Laravel Backend Responsibilities

**API Layer:**
- RESTful endpoints for video operations
- Authentication and authorization
- Request validation
- Response formatting
- Rate limiting

**Business Logic:**
- Video access control
- Subscription validation
- Watch progress tracking
- Analytics calculation
- Revenue distribution
- Content recommendations

**Data Management:**
- Database operations via Eloquent
- Caching strategies (Redis)
- Queue management for async tasks
- Event broadcasting
- File upload handling

**Integration Layer:**
- Cloudflare Stream API integration
- Wasabi/S3 storage integration
- Payment gateway integration
- Email/notification services
- Analytics services

### 5.12 Vue Frontend Responsibilities

**User Interface:**
- Responsive component library
- Video player integration
- Browse and search interfaces
- User dashboards
- Admin panels

**State Management:**
- Pinia stores for global state
- User authentication state
- Video playback state
- Search and filter state
- Cart/purchase state

**API Integration:**
- Axios HTTP client
- API service layer
- Error handling
- Loading states
- Optimistic updates

**Performance:**
- Lazy loading components
- Image optimization
- Virtual scrolling for lists
- Code splitting
- Service worker (PWA)

### 5.13 Cloudflare Stream Responsibilities

**Video Processing:**
- Video upload and storage
- Automatic transcoding
- Multiple quality levels (360p, 480p, 720p, 1080p)
- Adaptive bitrate streaming (HLS/DASH)
- Thumbnail generation

**Delivery:**
- Global CDN distribution
- Low-latency streaming
- Bandwidth optimization
- Analytics and metrics
- Signed URL generation

**Features:**
- Watermarking (optional)
- Captions/subtitles support
- Live streaming (future)
- Recording and VOD
- Webhook notifications


### 5.14 Storage Responsibilities (Wasabi/S3)

**Non-Video Assets:**
- Thumbnail images (multiple sizes)
- Poster images
- Channel banners
- Creator avatars
- Subtitle files (.vtt, .srt)
- Downloadable resources (PDFs, documents)
- Backup metadata

**Organization:**
```
/growstream/
  /thumbnails/
    /{video_id}/
      /small.webp
      /medium.webp
      /large.webp
  /posters/
    /{video_id}/poster.jpg
  /banners/
    /{creator_id}/banner.jpg
  /subtitles/
    /{video_id}/
      /en.vtt
      /local.vtt
  /resources/
    /{video_id}/
      /workbook.pdf
      /slides.pdf
```

### 5.15 API Structure

**RESTful Design:**
- `/api/v1/videos` - Video resources
- `/api/v1/series` - Series resources
- `/api/v1/creators` - Creator resources
- `/api/v1/categories` - Category resources
- `/api/v1/watch` - Watch progress and history
- `/api/v1/search` - Search functionality
- `/api/v1/analytics` - Analytics data

**Authentication:**
- Laravel Sanctum for API tokens
- Session-based for web
- JWT for mobile apps (future)

**Response Format:**
```json
{
  "success": true,
  "data": { ... },
  "meta": {
    "pagination": { ... },
    "timestamp": "2026-03-11T10:30:00Z"
  }
}
```

### 5.16 GrowStream Domain Module Structure

**CRITICAL: Follow MyGrowNet's Domain Module Pattern**

```
app/Domain/GrowStream/
├── Application/
│   ├── UseCases/
│   │   ├── Video/
│   │   │   ├── CreateVideoUseCase.php
│   │   │   ├── UpdateVideoUseCase.php
│   │   │   ├── PublishVideoUseCase.php
│   │   │   └── DeleteVideoUseCase.php
│   │   ├── Series/
│   │   │   ├── CreateSeriesUseCase.php
│   │   │   └── ManageEpisodesUseCase.php
│   │   ├── Watch/
│   │   │   ├── AuthorizePlaybackUseCase.php
│   │   │   ├── TrackProgressUseCase.php
│   │   │   └── GetContinueWatchingUseCase.php
│   │   └── Creator/
│   │       ├── RegisterCreatorUseCase.php
│   │       └── ProcessPayoutUseCase.php
│   ├── Commands/
│   │   ├── UploadVideoCommand.php
│   │   ├── ProcessVideoCommand.php
│   │   └── FeatureContentCommand.php
│   ├── Queries/
│   │   ├── GetFeaturedVideosQuery.php
│   │   ├── GetTrendingVideosQuery.php
│   │   ├── SearchVideosQuery.php
│   │   └── GetVideoAnalyticsQuery.php
│   ├── DTOs/
│   │   ├── VideoDTO.php
│   │   ├── SeriesDTO.php
│   │   ├── PlaybackAuthorizationDTO.php
│   │   └── CreatorRevenueDTO.php
│   └── Services/
│       ├── VideoAccessService.php
│       ├── RecommendationService.php
│       └── AnalyticsAggregationService.php
├── Domain/
│   ├── Entities/
│   │   ├── Video.php
│   │   ├── VideoSeries.php
│   │   ├── Episode.php
│   │   ├── Creator.php
│   │   ├── Subscription.php
│   │   └── WatchProgress.php
│   ├── ValueObjects/
│   │   ├── VideoMetadata.php
│   │   ├── PlaybackUrl.php
│   │   ├── AccessLevel.php
│   │   ├── ContentRating.php
│   │   └── Duration.php
│   ├── Services/
│   │   ├── VideoAccessService.php
│   │   ├── ProgressTrackingService.php
│   │   ├── RevenueCalculationService.php
│   │   └── ContentRecommendationService.php
│   ├── Repositories/
│   │   ├── VideoRepositoryInterface.php
│   │   ├── SeriesRepositoryInterface.php
│   │   ├── CreatorRepositoryInterface.php
│   │   └── AnalyticsRepositoryInterface.php
│   └── Events/
│       ├── VideoUploadedEvent.php
│       ├── VideoProcessingCompletedEvent.php
│       ├── VideoPublishedEvent.php
│       ├── WatchProgressUpdatedEvent.php
│       └── CreatorPayoutProcessedEvent.php
├── Infrastructure/
│   ├── Persistence/
│   │   ├── Eloquent/
│   │   │   ├── VideoModel.php
│   │   │   ├── VideoSeriesModel.php
│   │   │   ├── CreatorProfileModel.php
│   │   │   ├── WatchHistoryModel.php
│   │   │   └── VideoAnalyticsModel.php
│   │   └── Repositories/
│   │       ├── EloquentVideoRepository.php
│   │       ├── EloquentSeriesRepository.php
│   │       └── EloquentAnalyticsRepository.php
│   ├── Providers/
│   │   ├── VideoProviderInterface.php
│   │   ├── CloudflareStreamProvider.php
│   │   ├── LocalVideoProvider.php
│   │   └── VideoProviderFactory.php
│   ├── Services/
│   │   ├── CloudflareAnalyticsService.php
│   │   ├── ThumbnailGenerationService.php
│   │   └── StorageService.php
│   ├── Jobs/
│   │   ├── ProcessVideoJob.php
│   │   ├── SyncCloudflareMetadataJob.php
│   │   ├── GenerateThumbnailsJob.php
│   │   ├── UpdateVideoAnalyticsJob.php
│   │   ├── CalculateCreatorRevenueJob.php
│   │   └── AggregateAnalyticsJob.php
│   └── Listeners/
│       ├── NotifyCreatorOfProcessing.php
│       ├── UpdateVideoAnalytics.php
│       ├── IndexVideoForSearch.php
│       └── UpdateRecommendations.php
├── Presentation/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── VideoController.php
│   │   │   │   ├── SeriesController.php
│   │   │   │   ├── WatchController.php
│   │   │   │   ├── SearchController.php
│   │   │   │   └── CreatorController.php
│   │   │   └── Admin/
│   │   │       ├── VideoManagementController.php
│   │   │       ├── SeriesManagementController.php
│   │   │       ├── CreatorManagementController.php
│   │   │       ├── AnalyticsController.php
│   │   │       └── ModerationController.php
│   │   ├── Requests/
│   │   │   ├── CreateVideoRequest.php
│   │   │   ├── UpdateVideoRequest.php
│   │   │   ├── UploadVideoRequest.php
│   │   │   └── UpdateProgressRequest.php
│   │   ├── Resources/
│   │   │   ├── VideoResource.php
│   │   │   ├── VideoDetailResource.php
│   │   │   ├── SeriesResource.php
│   │   │   ├── CreatorResource.php
│   │   │   └── AnalyticsResource.php
│   │   └── Middleware/
│   │       ├── CheckVideoAccess.php
│   │       ├── CheckCreatorStatus.php
│   │       └── TrackVideoView.php
│   └── Console/
│       ├── AggregateAnalyticsCommand.php
│       ├── ProcessCreatorPayoutsCommand.php
│       └── CleanupOldAnalyticsCommand.php
└── GrowStreamServiceProvider.php
```

**Module Registration:**
```php
// config/app.php
'providers' => [
    // ...
    App\Domain\GrowStream\GrowStreamServiceProvider::class,
],
```

**Service Provider:**
```php
// app/Domain/GrowStream/GrowStreamServiceProvider.php
class GrowStreamServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register bindings
        $this->app->bind(VideoRepositoryInterface::class, EloquentVideoRepository::class);
        $this->app->bind(VideoProviderInterface::class, function ($app) {
            return VideoProviderFactory::make();
        });
    }
    
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/Presentation/routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/Presentation/routes/web.php');
        
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/Infrastructure/Persistence/Migrations');
        
        // Load views
        $this->loadViewsFrom(__DIR__.'/Presentation/views', 'growstream');
        
        // Publish config
        $this->publishes([
            __DIR__.'/config/growstream.php' => config_path('growstream.php'),
        ], 'growstream-config');
        
        // Register event listeners
        Event::listen(VideoUploadedEvent::class, NotifyCreatorOfProcessing::class);
        Event::listen(VideoProcessingCompletedEvent::class, UpdateVideoAnalytics::class);
    }
}
```

**Module Structure:**
```
app/Modules/GrowStream/
├── Application/
│   ├── UseCases/
│   ├── Commands/
│   ├── Queries/
│   └── DTOs/
├── Domain/
│   ├── Entities/
│   ├── ValueObjects/
│   ├── Services/
│   └── Repositories/
├── Infrastructure/
│   ├── Persistence/
│   ├── Providers/
│   └── Services/
├── Presentation/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
└── GrowStreamServiceProvider.php
```

**Benefits:**
- Independent deployment
- Clear boundaries
- Easy testing
- Reusable components
- Team scalability


### 5.17 Event-Driven Job Processing

**Background Jobs for Async Processing:**

```php
// app/Domain/GrowStream/Infrastructure/Jobs/ProcessVideoJob.php
class ProcessVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 3;
    public $timeout = 300;
    public $queue = 'video-processing';
    
    public function __construct(
        public Video $video
    ) {}
    
    public function handle(VideoProviderInterface $provider): void
    {
        $maxAttempts = 60;
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            $status = $provider->getUploadStatus($this->video->provider_video_id);
            
            $this->video->update(['upload_status' => $status]);
            
            if ($status === 'ready') {
                event(new VideoProcessingCompletedEvent($this->video));
                
                // Dispatch follow-up jobs
                GenerateThumbnailsJob::dispatch($this->video);
                SyncCloudflareMetadataJob::dispatch($this->video);
                UpdateVideoAnalyticsJob::dispatch($this->video);
                
                return;
            }
            
            if ($status === 'failed') {
                event(new VideoProcessingFailedEvent($this->video));
                return;
            }
            
            sleep(5);
            $attempt++;
        }
    }
}

// app/Domain/GrowStream/Infrastructure/Jobs/SyncCloudflareMetadataJob.php
class SyncCloudflareMetadataJob implements ShouldQueue
{
    public $queue = 'default';
    
    public function handle(VideoProviderInterface $provider): void
    {
        $details = $provider->getVideo($this->video->provider_video_id);
        
        $this->video->update([
            'duration' => $details->duration,
            'resolution' => $details->resolution,
            'file_size' => $details->fileSize,
            'playback_url' => $details->playbackUrl,
            'thumbnail_url' => $details->thumbnailUrl,
        ]);
    }
}

// app/Domain/GrowStream/Infrastructure/Jobs/UpdateVideoAnalyticsJob.php
class UpdateVideoAnalyticsJob implements ShouldQueue
{
    public $queue = 'analytics';
    
    public function handle(AnalyticsService $analytics): void
    {
        $analytics->updateVideoMetrics($this->video);
        $analytics->updateCreatorMetrics($this->video->creator);
        $analytics->updatePlatformMetrics();
    }
}
```

**Queue Configuration:**
```php
// config/queue.php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
    ],
],

// Separate queues for different priorities
'video-processing' => ['high'],
'default' => ['default'],
'analytics' => ['low'],
```

### 5.18 Future Expansion Readiness

**Scalability Considerations:**
- Horizontal scaling via load balancers
- Database read replicas
- Redis cluster for caching
- Queue workers for background jobs
- Microservices migration path

**Feature Extensibility:**
- Plugin architecture for new features
- Event-driven architecture
- API versioning strategy
- Feature flags for gradual rollout
- A/B testing framework

**Multi-Tenancy:**
- White-label capability
- Subdomain routing
- Tenant-specific branding
- Isolated data storage
- Custom domain support

---

## 6. Database Design

### 6.1 Core Tables

#### `videos` Table
**Purpose:** Store video metadata and provider information

```sql
CREATE TABLE videos (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    
    -- Basic Info
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    long_description LONGTEXT,
    
    -- Video Provider (Abstraction)
    video_provider ENUM('cloudflare', 'local', 'youtube', 'vimeo') DEFAULT 'cloudflare',
    provider_video_id VARCHAR(255), -- Cloudflare video UID
    playback_url TEXT, -- Stream playback URL
    playback_policy ENUM('public', 'signed') DEFAULT 'signed',
    
    -- Upload Status
    upload_status ENUM('pending', 'uploading', 'processing', 'ready', 'failed') DEFAULT 'pending',
    upload_progress INT DEFAULT 0,
    processing_started_at TIMESTAMP NULL,
    processing_completed_at TIMESTAMP NULL,
    
    -- Video Properties
    duration INT, -- seconds
    file_size BIGINT, -- bytes
    resolution VARCHAR(20), -- e.g., "1920x1080"
    aspect_ratio VARCHAR(10), -- e.g., "16:9"
    
    -- Assets
    thumbnail_url TEXT,
    poster_url TEXT,
    trailer_video_id BIGINT UNSIGNED NULL,
    
    -- Access Control
    access_level ENUM('free', 'basic', 'premium', 'institutional') DEFAULT 'basic',
    is_published BOOLEAN DEFAULT FALSE,
    published_at TIMESTAMP NULL,
    
    -- Relationships
    series_id BIGINT UNSIGNED NULL,
    season_number INT NULL,
    episode_number INT NULL,
    creator_id BIGINT UNSIGNED NOT NULL,
    
    -- Engagement
    view_count BIGINT DEFAULT 0,
    like_count INT DEFAULT 0,
    completion_rate DECIMAL(5,2) DEFAULT 0,
    
    -- SEO
    meta_title VARCHAR(255),
    meta_description TEXT,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_slug (slug),
    INDEX idx_provider (video_provider, provider_video_id),
    INDEX idx_series (series_id, season_number, episode_number),
    INDEX idx_creator (creator_id),
    INDEX idx_published (is_published, published_at),
    INDEX idx_access (access_level),
    
    FOREIGN KEY (series_id) REFERENCES video_series(id) ON DELETE SET NULL,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trailer_video_id) REFERENCES videos(id) ON DELETE SET NULL
);
```


#### `video_categories` Table
**Purpose:** Organize videos into browsable categories

```sql
CREATE TABLE video_categories (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    parent_id BIGINT UNSIGNED NULL,
    icon VARCHAR(50), -- Icon name for UI
    color VARCHAR(7), -- Hex color code
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_parent (parent_id),
    INDEX idx_active (is_active, sort_order),
    FOREIGN KEY (parent_id) REFERENCES video_categories(id) ON DELETE CASCADE
);
```

#### `video_category_pivot` Table
**Purpose:** Many-to-many relationship between videos and categories

```sql
CREATE TABLE video_category_pivot (
    video_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (video_id, category_id),
    INDEX idx_category (category_id),
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES video_categories(id) ON DELETE CASCADE
);
```

#### `video_tags` Table
**Purpose:** Flexible tagging system for videos

```sql
CREATE TABLE video_tags (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) UNIQUE NOT NULL,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_usage (usage_count DESC)
);
```

#### `video_tag_pivot` Table

```sql
CREATE TABLE video_tag_pivot (
    video_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (video_id, tag_id),
    INDEX idx_tag (tag_id),
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES video_tags(id) ON DELETE CASCADE
);
```

#### `video_series` Table
**Purpose:** Group related videos into series

```sql
CREATE TABLE video_series (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    long_description LONGTEXT,
    
    -- Assets
    poster_url TEXT,
    banner_url TEXT,
    trailer_video_id BIGINT UNSIGNED NULL,
    
    -- Structure
    total_seasons INT DEFAULT 1,
    total_episodes INT DEFAULT 0,
    
    -- Metadata
    creator_id BIGINT UNSIGNED NOT NULL,
    access_level ENUM('free', 'basic', 'premium', 'institutional') DEFAULT 'basic',
    is_published BOOLEAN DEFAULT FALSE,
    published_at TIMESTAMP NULL,
    
    -- Engagement
    view_count BIGINT DEFAULT 0,
    subscriber_count INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_slug (slug),
    INDEX idx_creator (creator_id),
    INDEX idx_published (is_published, published_at),
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trailer_video_id) REFERENCES videos(id) ON DELETE SET NULL
);
```


#### `video_episodes` Table (Alternative to series_id in videos)
**Purpose:** Explicit episode management with additional metadata

```sql
CREATE TABLE video_episodes (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    series_id BIGINT UNSIGNED NOT NULL,
    video_id BIGINT UNSIGNED NOT NULL,
    season_number INT NOT NULL,
    episode_number INT NOT NULL,
    title VARCHAR(255),
    description TEXT,
    sort_order INT DEFAULT 0,
    is_published BOOLEAN DEFAULT TRUE,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_episode (series_id, season_number, episode_number),
    INDEX idx_series (series_id, season_number, episode_number),
    FOREIGN KEY (series_id) REFERENCES video_series(id) ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
);
```

#### `creator_profiles` Table
**Purpose:** Extended profile information for content creators

```sql
CREATE TABLE creator_profiles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED UNIQUE NOT NULL,
    
    -- Profile Info
    display_name VARCHAR(100) NOT NULL,
    bio TEXT,
    avatar_url TEXT,
    banner_url TEXT,
    
    -- Social Links
    website_url VARCHAR(255),
    facebook_url VARCHAR(255),
    twitter_url VARCHAR(255),
    instagram_url VARCHAR(255),
    youtube_url VARCHAR(255),
    
    -- Status
    is_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    verified_at TIMESTAMP NULL,
    
    -- Stats
    total_videos INT DEFAULT 0,
    total_views BIGINT DEFAULT 0,
    subscriber_count INT DEFAULT 0,
    
    -- Revenue
    revenue_share_percentage DECIMAL(5,2) DEFAULT 70.00,
    total_earnings DECIMAL(15,2) DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user (user_id),
    INDEX idx_verified (is_verified, is_active),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### `creator_payouts` Table
**Purpose:** Track revenue payments to creators

```sql
CREATE TABLE creator_payouts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    creator_id BIGINT UNSIGNED NOT NULL,
    
    -- Payout Details
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'ZMW',
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    
    -- Status
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    payment_method VARCHAR(50),
    transaction_reference VARCHAR(100),
    
    -- Metadata
    total_views BIGINT,
    total_watch_time BIGINT, -- seconds
    videos_count INT,
    notes TEXT,
    
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_creator (creator_id, period_end),
    INDEX idx_status (status),
    FOREIGN KEY (creator_id) REFERENCES creator_profiles(id) ON DELETE CASCADE
);
```


#### `video_subscriptions` Table
**Purpose:** Track user subscriptions and entitlements

```sql
CREATE TABLE video_subscriptions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    
    -- Subscription Details
    plan_type ENUM('basic', 'premium', 'family', 'institutional') NOT NULL,
    status ENUM('active', 'cancelled', 'expired', 'suspended') DEFAULT 'active',
    
    -- Billing
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'ZMW',
    billing_cycle ENUM('monthly', 'annual') DEFAULT 'monthly',
    
    -- Dates
    started_at TIMESTAMP NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    cancelled_at TIMESTAMP NULL,
    
    -- Payment
    payment_method VARCHAR(50),
    last_payment_at TIMESTAMP NULL,
    next_billing_date DATE NULL,
    
    -- Trial
    is_trial BOOLEAN DEFAULT FALSE,
    trial_ends_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user (user_id, status),
    INDEX idx_expires (expires_at),
    INDEX idx_status (status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### `video_purchases` Table
**Purpose:** One-time purchases of individual content

```sql
CREATE TABLE video_purchases (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    purchasable_type VARCHAR(50) NOT NULL, -- 'video' or 'series'
    purchasable_id BIGINT UNSIGNED NOT NULL,
    
    -- Purchase Details
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'ZMW',
    access_type ENUM('rental', 'purchase') DEFAULT 'purchase',
    
    -- Access Period
    access_granted_at TIMESTAMP NOT NULL,
    access_expires_at TIMESTAMP NULL, -- NULL for lifetime access
    
    -- Payment
    payment_method VARCHAR(50),
    transaction_reference VARCHAR(100),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user (user_id),
    INDEX idx_purchasable (purchasable_type, purchasable_id),
    INDEX idx_access (access_expires_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### `watch_history` Table
**Purpose:** Track viewing progress and history

```sql
CREATE TABLE watch_history (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    video_id BIGINT UNSIGNED NOT NULL,
    
    -- Progress
    current_position INT NOT NULL DEFAULT 0, -- seconds
    duration INT NOT NULL, -- total video duration
    progress_percentage DECIMAL(5,2) DEFAULT 0,
    
    -- Status
    is_completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    
    -- Session
    session_id VARCHAR(100),
    device_type VARCHAR(50),
    ip_address VARCHAR(45),
    user_agent TEXT,
    
    -- Timestamps
    started_at TIMESTAMP NOT NULL,
    last_watched_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_video (user_id, video_id),
    INDEX idx_user (user_id, last_watched_at),
    INDEX idx_video (video_id),
    INDEX idx_completed (is_completed),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
);
```


#### `watchlists` Table
**Purpose:** User's saved/bookmarked videos (My List)

```sql
CREATE TABLE watchlists (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    watchlistable_type VARCHAR(50) NOT NULL, -- 'video' or 'series'
    watchlistable_id BIGINT UNSIGNED NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_watchlist_item (user_id, watchlistable_type, watchlistable_id),
    INDEX idx_user (user_id, added_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### `video_views` Table
**Purpose:** Detailed view tracking for analytics

```sql
CREATE TABLE video_views (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    video_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL, -- NULL for anonymous views
    
    -- View Details
    watch_duration INT NOT NULL, -- seconds actually watched
    completion_percentage DECIMAL(5,2),
    
    -- Session Info
    session_id VARCHAR(100),
    device_type VARCHAR(50),
    browser VARCHAR(50),
    os VARCHAR(50),
    ip_address VARCHAR(45),
    country_code VARCHAR(2),
    
    -- Quality
    quality_level VARCHAR(20), -- e.g., "720p"
    buffering_count INT DEFAULT 0,
    
    -- Referrer
    referrer_url TEXT,
    traffic_source VARCHAR(50),
    
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_video (video_id, viewed_at),
    INDEX idx_user (user_id, viewed_at),
    INDEX idx_date (viewed_at),
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `video_engagement` Table
**Purpose:** Track likes, ratings, and other engagement

```sql
CREATE TABLE video_engagement (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    video_id BIGINT UNSIGNED NOT NULL,
    
    -- Engagement Types
    is_liked BOOLEAN DEFAULT FALSE,
    rating INT NULL, -- 1-5 stars (optional)
    
    -- Timestamps
    liked_at TIMESTAMP NULL,
    rated_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_engagement (user_id, video_id),
    INDEX idx_video (video_id),
    INDEX idx_liked (is_liked),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
);
```

#### `video_assets` Table
**Purpose:** Manage additional assets like subtitles and resources

```sql
CREATE TABLE video_assets (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    video_id BIGINT UNSIGNED NOT NULL,
    
    -- Asset Info
    asset_type ENUM('subtitle', 'resource', 'thumbnail', 'poster') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_url TEXT NOT NULL,
    file_size BIGINT,
    mime_type VARCHAR(100),
    
    -- Subtitle Specific
    language_code VARCHAR(5) NULL, -- e.g., 'en', 'ny'
    language_name VARCHAR(50) NULL,
    
    -- Resource Specific
    resource_title VARCHAR(255) NULL,
    resource_description TEXT NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_video (video_id, asset_type),
    INDEX idx_language (language_code),
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
);
```


#### `content_plans` Table
**Purpose:** Define subscription plans and pricing

```sql
CREATE TABLE content_plans (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    
    -- Pricing
    price DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'ZMW',
    billing_cycle ENUM('monthly', 'annual', 'lifetime') NOT NULL,
    
    -- Features
    access_level ENUM('basic', 'premium', 'family', 'institutional') NOT NULL,
    max_profiles INT DEFAULT 1,
    max_concurrent_streams INT DEFAULT 1,
    download_enabled BOOLEAN DEFAULT FALSE,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_active (is_active, sort_order),
    INDEX idx_slug (slug)
);
```

#### `featured_content` Table
**Purpose:** Manage homepage featured content

```sql
CREATE TABLE featured_content (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    featurable_type VARCHAR(50) NOT NULL, -- 'video' or 'series'
    featurable_id BIGINT UNSIGNED NOT NULL,
    
    -- Display
    title VARCHAR(255),
    description TEXT,
    custom_thumbnail_url TEXT,
    custom_banner_url TEXT,
    
    -- Placement
    placement ENUM('hero', 'trending', 'new_release', 'recommended') NOT NULL,
    sort_order INT DEFAULT 0,
    
    -- Scheduling
    starts_at TIMESTAMP NULL,
    ends_at TIMESTAMP NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Stats
    click_count INT DEFAULT 0,
    impression_count INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_placement (placement, is_active, sort_order),
    INDEX idx_schedule (starts_at, ends_at),
    INDEX idx_featurable (featurable_type, featurable_id)
);
```

### 6.2 Table Relationships Summary

**Core Relationships:**
- `videos` → `video_series` (many-to-one)
- `videos` → `creator_profiles` (many-to-one)
- `videos` ↔ `video_categories` (many-to-many via pivot)
- `videos` ↔ `video_tags` (many-to-many via pivot)
- `videos` → `video_assets` (one-to-many)
- `users` → `watch_history` (one-to-many)
- `users` → `watchlists` (one-to-many)
- `users` → `video_subscriptions` (one-to-many)
- `users` → `video_purchases` (one-to-many)
- `creator_profiles` → `creator_payouts` (one-to-many)

---

## 7. Video Provider Abstraction

### 7.1 Provider Architecture

The system uses a provider-based architecture to abstract video hosting services, making it easy to switch providers or support multiple providers simultaneously.

**Key Fields in `videos` Table:**
- `video_provider` - Identifies the provider (cloudflare, local, youtube, vimeo)
- `provider_video_id` - Provider's unique identifier for the video
- `playback_url` - Direct playback URL from provider
- `playback_policy` - Access control method (public, signed)
- `upload_status` - Current processing state


### 7.2 Provider Interface

**Laravel Service Contract:**

```php
interface VideoProviderInterface
{
    /**
     * Upload a video file to the provider
     */
    public function upload(UploadedFile $file, array $metadata = []): ProviderVideoResponse;
    
    /**
     * Get video details from provider
     */
    public function getVideo(string $providerVideoId): ProviderVideoResponse;
    
    /**
     * Get playback URL (signed or public)
     */
    public function getPlaybackUrl(string $providerVideoId, bool $signed = true): string;
    
    /**
     * Delete video from provider
     */
    public function delete(string $providerVideoId): bool;
    
    /**
     * Get upload status
     */
    public function getUploadStatus(string $providerVideoId): string;
    
    /**
     * Get video analytics
     */
    public function getAnalytics(string $providerVideoId, array $options = []): array;
}
```

### 7.3 Cloudflare Stream Implementation

```php
class CloudflareStreamProvider implements VideoProviderInterface
{
    protected string $accountId;
    protected string $apiToken;
    protected HttpClient $client;
    
    public function upload(UploadedFile $file, array $metadata = []): ProviderVideoResponse
    {
        // Upload via TUS protocol or direct upload
        $response = $this->client->post(
            "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/stream",
            [
                'file' => $file,
                'meta' => $metadata
            ]
        );
        
        return new ProviderVideoResponse([
            'provider_video_id' => $response['uid'],
            'playback_url' => $response['playback']['hls'],
            'thumbnail_url' => $response['thumbnail'],
            'duration' => $response['duration'],
            'status' => $response['status']['state']
        ]);
    }
    
    public function getPlaybackUrl(string $providerVideoId, bool $signed = true): string
    {
        if ($signed) {
            return $this->generateSignedUrl($providerVideoId);
        }
        
        return "https://customer-{$this->accountId}.cloudflarestream.com/{$providerVideoId}/manifest/video.m3u8";
    }
    
    protected function generateSignedUrl(string $providerVideoId): string
    {
        // Generate signed token with expiration
        $token = $this->createSignedToken($providerVideoId, now()->addHours(24));
        return "https://customer-{$this->accountId}.cloudflarestream.com/{$providerVideoId}/manifest/video.m3u8?token={$token}";
    }
}
```

### 7.4 Local/Fallback Provider

```php
class LocalVideoProvider implements VideoProviderInterface
{
    public function upload(UploadedFile $file, array $metadata = []): ProviderVideoResponse
    {
        // Store locally in storage/app/videos
        $path = $file->store('videos', 'local');
        $uuid = Str::uuid();
        
        return new ProviderVideoResponse([
            'provider_video_id' => $uuid,
            'playback_url' => Storage::url($path),
            'status' => 'ready'
        ]);
    }
    
    public function getPlaybackUrl(string $providerVideoId, bool $signed = true): string
    {
        if ($signed) {
            return URL::temporarySignedRoute(
                'video.stream',
                now()->addHours(24),
                ['video' => $providerVideoId]
            );
        }
        
        return route('video.stream', ['video' => $providerVideoId]);
    }
}
```


### 7.5 Provider Factory

```php
class VideoProviderFactory
{
    public static function make(string $provider = null): VideoProviderInterface
    {
        $provider = $provider ?? config('growstream.default_provider');
        
        return match($provider) {
            'cloudflare' => app(CloudflareStreamProvider::class),
            'local' => app(LocalVideoProvider::class),
            'youtube' => app(YouTubeProvider::class),
            'vimeo' => app(VimeoProvider::class),
            default => throw new InvalidProviderException("Provider {$provider} not supported")
        };
    }
}
```

### 7.6 Benefits of Provider Abstraction

1. **Flexibility** - Easy to switch providers without changing application code
2. **Multi-Provider Support** - Can use different providers for different content types
3. **Testing** - Use local provider in development, Cloudflare in production
4. **Cost Optimization** - Choose provider based on content type and usage patterns
5. **Vendor Independence** - Not locked into single provider
6. **Gradual Migration** - Can migrate content between providers incrementally

### 7.7 Configuration

```php
// config/growstream.php
return [
    'default_provider' => env('GROWSTREAM_VIDEO_PROVIDER', 'cloudflare'),
    
    'providers' => [
        'cloudflare' => [
            'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),
            'api_token' => env('CLOUDFLARE_API_TOKEN'),
            'customer_subdomain' => env('CLOUDFLARE_CUSTOMER_SUBDOMAIN'),
        ],
        
        'local' => [
            'disk' => 'local',
            'path' => 'videos',
        ],
    ],
    
    'upload' => [
        'max_file_size' => 5 * 1024 * 1024 * 1024, // 5GB
        'allowed_mimetypes' => ['video/mp4', 'video/quicktime', 'video/x-msvideo'],
        'chunk_size' => 5 * 1024 * 1024, // 5MB chunks
    ],
];
```

---

## 8. User Roles and Permissions

### 8.1 Role Definitions

#### Super Admin
**Capabilities:**
- Full system access
- Manage all content
- Manage all users and creators
- Configure system settings
- View all analytics
- Manage subscriptions and billing
- Moderate content
- Manage featured content

#### Content Manager
**Capabilities:**
- Upload and manage videos
- Create and manage series
- Assign categories and tags
- Moderate creator content
- Feature content on homepage
- View content analytics
- Manage video assets (subtitles, resources)

#### Creator
**Capabilities:**
- Upload videos (if approved)
- Manage own content
- View own analytics
- Manage creator profile
- View revenue reports
- Respond to comments (future)
- Create series and playlists

#### Subscriber (Premium/Basic)
**Capabilities:**
- Watch subscribed content
- Create watchlists
- Track watch history
- Rate and like videos
- Download content (premium only)
- Multiple profiles (family plan)

#### Guest (Free User)
**Capabilities:**
- Browse free content
- Watch trailers and previews
- Search content
- View video details
- Limited watch time


### 8.2 Permission Structure

**Using Spatie Laravel Permission:**

```php
// Permissions
'growstream.videos.view'
'growstream.videos.create'
'growstream.videos.edit'
'growstream.videos.delete'
'growstream.videos.publish'

'growstream.series.view'
'growstream.series.create'
'growstream.series.edit'
'growstream.series.delete'

'growstream.creators.manage'
'growstream.creators.approve'
'growstream.creators.payout'

'growstream.content.moderate'
'growstream.content.feature'

'growstream.analytics.view'
'growstream.analytics.export'

'growstream.subscriptions.manage'
'growstream.settings.manage'
```

### 8.3 Role Assignment

```php
// Seed roles and permissions
$superAdmin = Role::create(['name' => 'super_admin']);
$superAdmin->givePermissionTo(Permission::all());

$contentManager = Role::create(['name' => 'content_manager']);
$contentManager->givePermissionTo([
    'growstream.videos.*',
    'growstream.series.*',
    'growstream.content.moderate',
    'growstream.content.feature',
    'growstream.analytics.view',
]);

$creator = Role::create(['name' => 'creator']);
$creator->givePermissionTo([
    'growstream.videos.view',
    'growstream.videos.create',
    'growstream.videos.edit', // own only
    'growstream.analytics.view', // own only
]);

$subscriber = Role::create(['name' => 'subscriber']);
$subscriber->givePermissionTo([
    'growstream.videos.view',
    'growstream.videos.watch',
]);
```

---

## 9. Access Control and Subscription Logic

### 9.1 Access Control Flow

```
User requests video → Check authentication → Check subscription status → 
Check video access level → Check purchase history → Grant/Deny access
```

### 9.2 Access Level Matrix

| User Type | Free Content | Basic Content | Premium Content | Institutional |
|-----------|--------------|---------------|-----------------|---------------|
| Guest | ✅ Preview | ❌ | ❌ | ❌ |
| Free Member | ✅ Full | ❌ | ❌ | ❌ |
| Basic Subscriber | ✅ Full | ✅ Full | ❌ | ❌ |
| Premium Subscriber | ✅ Full | ✅ Full | ✅ Full | ❌ |
| Institutional | ✅ Full | ✅ Full | ✅ Full | ✅ Custom |
| Purchased | ✅ Full | ✅ If purchased | ✅ If purchased | ❌ |

### 9.3 Laravel Middleware Implementation

```php
class CheckVideoAccess
{
    public function handle(Request $request, Closure $next)
    {
        $video = $request->route('video');
        $user = $request->user();
        
        // Public/free content
        if ($video->access_level === 'free') {
            return $next($request);
        }
        
        // Require authentication
        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }
        
        // Check subscription
        if ($this->hasValidSubscription($user, $video->access_level)) {
            return $next($request);
        }
        
        // Check individual purchase
        if ($this->hasPurchased($user, $video)) {
            return $next($request);
        }
        
        // Check starter kit benefit (MyGrowNet integration)
        if ($this->hasStarterKitAccess($user, $video)) {
            return $next($request);
        }
        
        return response()->json(['error' => 'Subscription required'], 403);
    }
    
    protected function hasValidSubscription(User $user, string $requiredLevel): bool
    {
        $subscription = $user->activeVideoSubscription();
        
        if (!$subscription) {
            return false;
        }
        
        $accessHierarchy = ['basic' => 1, 'premium' => 2, 'institutional' => 3];
        
        return $accessHierarchy[$subscription->plan_type] >= $accessHierarchy[$requiredLevel];
    }
}
```


### 9.4 Policy-Based Authorization

```php
class VideoPolicy
{
    public function view(User $user, Video $video): bool
    {
        // Unpublished content
        if (!$video->is_published) {
            return $user->can('growstream.videos.view') || $video->creator_id === $user->id;
        }
        
        // Free content
        if ($video->access_level === 'free') {
            return true;
        }
        
        // Check subscription or purchase
        return $this->hasAccess($user, $video);
    }
    
    public function update(User $user, Video $video): bool
    {
        // Super admin or content manager
        if ($user->can('growstream.videos.edit')) {
            return true;
        }
        
        // Creator can edit own content
        return $video->creator_id === $user->id;
    }
    
    public function delete(User $user, Video $video): bool
    {
        if ($user->can('growstream.videos.delete')) {
            return true;
        }
        
        // Creator can delete own unpublished content
        return $video->creator_id === $user->id && !$video->is_published;
    }
}
```

### 9.5 MyGrowNet Integration

**Starter Kit Benefits:**
- Starter kit members get access to specific educational content
- Integration with existing MyGrowNet subscription system
- Seamless upgrade path to full GrowStream subscription

```php
class VideoAccessService
{
    public function hasStarterKitAccess(User $user, Video $video): bool
    {
        // Check if user has active starter kit
        if (!$user->hasActiveStarterKit()) {
            return false;
        }
        
        // Check if video is tagged as starter kit content
        return $video->tags->contains('slug', 'starter-kit-included');
    }
    
    public function getAccessibleContent(User $user): Collection
    {
        $query = Video::published();
        
        // Free content always accessible
        $query->where('access_level', 'free');
        
        // Add subscription-based content
        if ($subscription = $user->activeVideoSubscription()) {
            $query->orWhere(function($q) use ($subscription) {
                $q->where('access_level', $this->getAccessibleLevels($subscription->plan_type));
            });
        }
        
        // Add purchased content
        $purchasedIds = $user->videoPurchases()->pluck('purchasable_id');
        $query->orWhereIn('id', $purchasedIds);
        
        // Add starter kit content
        if ($user->hasActiveStarterKit()) {
            $query->orWhereHas('tags', function($q) {
                $q->where('slug', 'starter-kit-included');
            });
        }
        
        return $query->get();
    }
}
```

### 9.6 Institutional Access

**Group/Organization Access:**
- Bulk licenses for companies, schools, NGOs
- Custom access packages
- Admin dashboard for organization managers
- Usage reporting and analytics

```php
class InstitutionalAccess
{
    public function grantAccess(Organization $org, User $user, Carbon $expiresAt): void
    {
        InstitutionalMember::create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'access_level' => $org->access_level,
            'expires_at' => $expiresAt,
        ]);
    }
    
    public function hasInstitutionalAccess(User $user, Video $video): bool
    {
        return $user->institutionalMemberships()
            ->active()
            ->whereHas('organization', function($q) use ($video) {
                $q->where('access_level', '>=', $video->access_level);
            })
            ->exists();
    }
}
```

---

## 10. Admin Panel Requirements

### 10.1 Dashboard Overview

**Key Metrics:**
- Total videos and series
- Total views (today, week, month)
- Active subscribers
- Revenue (today, week, month)
- Top performing content
- Recent uploads
- Pending moderation queue
- Storage usage
- Bandwidth usage


### 10.2 Video Management

**Features:**
- List all videos with filters (status, category, creator, date)
- Bulk actions (publish, unpublish, delete, change category)
- Quick edit inline
- Detailed edit page
- Preview video
- View analytics
- Manage assets (thumbnails, subtitles, resources)
- Duplicate video
- Schedule publishing

**Video Edit Form:**
- Basic info (title, description, slug)
- Video file upload or provider ID
- Thumbnail and poster upload
- Category and tag assignment
- Access level selection
- Series and episode assignment
- Creator assignment
- SEO metadata
- Publishing options
- Related content suggestions

### 10.3 Series Management

**Features:**
- Create and edit series
- Manage episodes
- Reorder episodes
- Bulk episode operations
- Series analytics
- Season management
- Series artwork

### 10.4 Category Management

**Features:**
- Create/edit/delete categories
- Hierarchical category structure
- Assign icons and colors
- Reorder categories
- View category analytics
- Bulk video assignment

### 10.5 Creator Management

**Features:**
- List all creators
- Approve/reject creator applications
- Verify creators (badge)
- Suspend/ban creators
- View creator analytics
- Manage revenue share percentage
- Process payouts
- Communication tools

**Creator Approval Workflow:**
1. User applies to become creator
2. Admin reviews application
3. Admin approves or rejects with feedback
4. Approved creators can upload content
5. Content goes through moderation before publishing

### 10.6 Content Moderation

**Moderation Queue:**
- List pending content
- Preview video
- Approve or reject with reason
- Flag inappropriate content
- Automated flagging (future)
- User reports handling

**Moderation Actions:**
- Approve and publish
- Request changes
- Reject with reason
- Suspend creator
- Remove content

### 10.7 Featured Content Management

**Features:**
- Manage hero banner content
- Set trending content
- Schedule featured content
- A/B test featured content
- Track performance (clicks, conversions)

**Featured Content Form:**
- Select video or series
- Custom title and description
- Custom artwork
- Placement (hero, trending, new release)
- Schedule (start/end dates)
- Priority/sort order


### 10.8 Analytics Dashboard

**Platform Analytics:**
- Total views and watch time
- Unique viewers
- Average watch duration
- Completion rates
- Popular content
- Traffic sources
- Device breakdown
- Geographic distribution
- Peak viewing times
- Subscriber growth
- Revenue trends
- Churn analysis

**Content Analytics:**
- Views per video
- Watch time per video
- Completion rate
- Engagement metrics
- Traffic sources
- Audience retention graph
- Drop-off points

**Creator Analytics:**
- Top creators by views
- Top creators by revenue
- Creator growth trends
- Content output per creator

**Export Options:**
- CSV export
- PDF reports
- Scheduled email reports
- Custom date ranges

### 10.9 Subscription Management

**Features:**
- List all subscriptions
- Filter by status, plan, date
- View subscription details
- Cancel/refund subscriptions
- Extend trial periods
- Apply discounts
- View payment history
- Handle failed payments

### 10.10 Settings

**General Settings:**
- Platform name and branding
- Default video provider
- Upload limits
- Allowed file types
- Default access levels

**Player Settings:**
- Auto-play next episode
- Default quality
- Playback speed options
- Subtitle defaults
- Skip intro/outro (future)

**Monetization Settings:**
- Subscription plans and pricing
- Creator revenue share
- Payment methods
- Tax settings

**Email Settings:**
- Welcome emails
- Subscription confirmations
- Content notifications
- Creator notifications

---

## 11. Frontend Screens and Pages

### 11.1 Public Pages

#### Home Page (`/stream`)
**Components:**
- Hero banner with featured content
- Continue watching row
- Trending now row
- New releases row
- Category rows (dynamic)
- Personalized recommendations
- Quick search bar
- Navigation menu

#### Browse Page (`/stream/browse`)
**Components:**
- Filter sidebar (categories, duration, language)
- Sort options (newest, popular, trending)
- Grid or list view toggle
- Infinite scroll
- Quick preview on hover

#### Category Page (`/stream/category/{slug}`)
**Components:**
- Category header with description
- Subcategories
- Filtered content grid
- Sort and filter options

#### Search Results (`/stream/search`)
**Components:**
- Search input with auto-complete
- Filter options
- Results grid
- Suggested searches
- No results state with recommendations


#### Video Detail Page (`/stream/video/{slug}`)
**Components:**
- Video player or thumbnail
- Play/Resume button
- Title and metadata
- Description (expandable)
- Creator info with link
- Add to list button
- Share button
- Like button
- Tags
- Related videos section
- Episodes list (if series)
- Comments section (future)

#### Watch Page (`/stream/watch/{slug}`)
**Components:**
- Full-screen video player
- Playback controls
- Quality selector
- Subtitle selector
- Playback speed
- Next episode countdown (series)
- Up next suggestions
- Minimize to continue browsing (future)

#### Series Detail Page (`/stream/series/{slug}`)
**Components:**
- Series banner
- Series description
- Season selector
- Episode list with thumbnails
- Series trailer
- Creator info
- Add to list button
- Related series

#### Creator Profile (`/stream/creator/{username}`)
**Components:**
- Creator banner and avatar
- Bio and social links
- Subscribe button
- All videos by creator
- Series by creator
- Sort and filter options
- Stats (total views, videos)

### 11.2 User Pages

#### My List (`/stream/my-list`)
**Components:**
- Saved videos and series
- Remove from list option
- Sort options
- Empty state

#### Watch History (`/stream/history`)
**Components:**
- Chronological list of watched content
- Clear history option
- Remove individual items
- Filter by date range

#### Continue Watching (`/stream/continue`)
**Components:**
- Videos in progress
- Progress bars
- Resume buttons
- Remove from continue watching

#### Subscription Page (`/stream/subscribe`)
**Components:**
- Plan comparison table
- Feature highlights
- Pricing cards
- Payment form
- FAQ section
- Testimonials

### 11.3 Creator Pages

#### Creator Dashboard (`/stream/creator/dashboard`)
**Components:**
- Overview stats
- Recent videos performance
- Revenue summary
- Upload button
- Pending content status

#### Upload Video (`/stream/creator/upload`)
**Components:**
- Drag-and-drop upload
- Upload progress
- Metadata form
- Thumbnail upload
- Category selection
- Submit for review button

#### My Videos (`/stream/creator/videos`)
**Components:**
- List of all creator's videos
- Status indicators
- Edit/delete actions
- Analytics link
- Bulk actions

#### Analytics (`/stream/creator/analytics`)
**Components:**
- Views and watch time charts
- Top performing videos
- Audience demographics
- Traffic sources
- Revenue breakdown

#### Revenue (`/stream/creator/revenue`)
**Components:**
- Earnings summary
- Payout history
- Payment method settings
- Tax information


### 11.4 Admin Pages

#### Admin Dashboard (`/admin/growstream`)
**Components:**
- Key metrics cards
- Charts (views, revenue, subscribers)
- Recent activity feed
- Quick actions
- Alerts and notifications

#### Video Management (`/admin/growstream/videos`)
**Components:**
- Data table with filters
- Bulk actions toolbar
- Quick edit modal
- Status indicators
- Search and filter

#### Series Management (`/admin/growstream/series`)
**Components:**
- Series list
- Episode management
- Reorder episodes
- Series analytics

#### Creator Management (`/admin/growstream/creators`)
**Components:**
- Creator list
- Approval queue
- Verification actions
- Payout management

#### Moderation Queue (`/admin/growstream/moderation`)
**Components:**
- Pending content list
- Preview modal
- Approve/reject actions
- Flagged content

#### Analytics (`/admin/growstream/analytics`)
**Components:**
- Date range selector
- Multiple chart types
- Export options
- Custom reports

#### Settings (`/admin/growstream/settings`)
**Components:**
- Tabbed settings interface
- Form sections
- Save/reset buttons
- Preview changes

### 11.5 Reusable Vue Components

**Core Components:**
- `VideoCard.vue` - Video thumbnail card
- `VideoPlayer.vue` - Video player wrapper
- `VideoGrid.vue` - Responsive video grid
- `VideoRow.vue` - Horizontal scrolling row
- `SeriesCard.vue` - Series thumbnail card
- `CreatorCard.vue` - Creator profile card
- `CategoryBadge.vue` - Category tag
- `ProgressBar.vue` - Watch progress indicator
- `SubscriptionPrompt.vue` - Upgrade modal
- `SearchBar.vue` - Search input with autocomplete
- `FilterSidebar.vue` - Filter and sort controls
- `VideoMetadata.vue` - Video info display
- `EpisodeList.vue` - Series episodes
- `RelatedVideos.vue` - Recommendations
- `UploadForm.vue` - Video upload interface
- `AnalyticsChart.vue` - Chart component
- `DataTable.vue` - Admin table component

---

## 12. API Endpoints

### 12.1 Public Endpoints

#### Browse and Discovery
```
GET    /api/v1/videos                    # List videos with filters
GET    /api/v1/videos/featured           # Featured content
GET    /api/v1/videos/trending           # Trending videos
GET    /api/v1/videos/new-releases       # New releases
GET    /api/v1/videos/{slug}             # Video details
GET    /api/v1/videos/{slug}/related     # Related videos

GET    /api/v1/series                    # List series
GET    /api/v1/series/{slug}             # Series details
GET    /api/v1/series/{slug}/episodes    # Series episodes

GET    /api/v1/categories                # List categories
GET    /api/v1/categories/{slug}/videos  # Videos by category

GET    /api/v1/creators                  # List creators
GET    /api/v1/creators/{username}       # Creator profile
GET    /api/v1/creators/{username}/videos # Creator's videos

GET    /api/v1/search                    # Search videos
GET    /api/v1/search/suggestions        # Search autocomplete
```


#### Watch and Playback
```
POST   /api/v1/watch/authorize           # Get playback authorization
POST   /api/v1/watch/progress             # Update watch progress
GET    /api/v1/watch/history              # Watch history
DELETE /api/v1/watch/history/{id}        # Remove from history

GET    /api/v1/continue-watching          # Continue watching list
POST   /api/v1/watchlist                  # Add to watchlist
DELETE /api/v1/watchlist/{id}            # Remove from watchlist
GET    /api/v1/watchlist                  # Get watchlist
```

#### Engagement
```
POST   /api/v1/videos/{id}/like           # Like video
DELETE /api/v1/videos/{id}/like           # Unlike video
POST   /api/v1/videos/{id}/rate           # Rate video
```

### 12.2 Authenticated User Endpoints

#### Subscription Management
```
GET    /api/v1/subscriptions              # User's subscriptions
POST   /api/v1/subscriptions              # Create subscription
PUT    /api/v1/subscriptions/{id}         # Update subscription
DELETE /api/v1/subscriptions/{id}         # Cancel subscription

GET    /api/v1/plans                      # Available plans
POST   /api/v1/purchases                  # Purchase content
GET    /api/v1/purchases                  # User's purchases
```

#### User Profile
```
GET    /api/v1/user/profile               # User profile
PUT    /api/v1/user/profile               # Update profile
GET    /api/v1/user/preferences           # User preferences
PUT    /api/v1/user/preferences           # Update preferences
```

### 12.3 Creator Endpoints

#### Content Management
```
GET    /api/v1/creator/videos             # Creator's videos
POST   /api/v1/creator/videos             # Upload video
PUT    /api/v1/creator/videos/{id}        # Update video
DELETE /api/v1/creator/videos/{id}        # Delete video

POST   /api/v1/creator/series             # Create series
PUT    /api/v1/creator/series/{id}        # Update series
DELETE /api/v1/creator/series/{id}        # Delete series
```

#### Analytics and Revenue
```
GET    /api/v1/creator/analytics          # Creator analytics
GET    /api/v1/creator/analytics/videos/{id} # Video analytics
GET    /api/v1/creator/revenue            # Revenue summary
GET    /api/v1/creator/payouts            # Payout history
```

#### Profile Management
```
GET    /api/v1/creator/profile            # Creator profile
PUT    /api/v1/creator/profile            # Update profile
POST   /api/v1/creator/apply              # Apply to be creator
```

### 12.4 Admin Endpoints

#### Video Management
```
GET    /api/v1/admin/videos               # All videos
POST   /api/v1/admin/videos               # Create video
PUT    /api/v1/admin/videos/{id}          # Update video
DELETE /api/v1/admin/videos/{id}          # Delete video
POST   /api/v1/admin/videos/bulk          # Bulk actions
```

#### Content Moderation
```
GET    /api/v1/admin/moderation/queue     # Moderation queue
POST   /api/v1/admin/moderation/approve/{id} # Approve content
POST   /api/v1/admin/moderation/reject/{id}  # Reject content
```

#### Creator Management
```
GET    /api/v1/admin/creators             # All creators
POST   /api/v1/admin/creators/{id}/verify # Verify creator
POST   /api/v1/admin/creators/{id}/suspend # Suspend creator
GET    /api/v1/admin/creators/applications # Creator applications
POST   /api/v1/admin/payouts              # Process payout
```

#### Analytics
```
GET    /api/v1/admin/analytics/overview   # Platform overview
GET    /api/v1/admin/analytics/videos     # Video analytics
GET    /api/v1/admin/analytics/creators   # Creator analytics
GET    /api/v1/admin/analytics/revenue    # Revenue analytics
POST   /api/v1/admin/analytics/export     # Export report
```

#### Settings
```
GET    /api/v1/admin/settings             # Get settings
PUT    /api/v1/admin/settings             # Update settings
GET    /api/v1/admin/categories           # Manage categories
POST   /api/v1/admin/featured             # Feature content
```

### 12.5 Response Format Standards

**Success Response:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "title": "Video Title",
    ...
  },
  "meta": {
    "timestamp": "2026-03-11T10:30:00Z"
  }
}
```

**Paginated Response:**
```json
{
  "success": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "last_page": 8
  },
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "error": {
    "code": "SUBSCRIPTION_REQUIRED",
    "message": "You need an active subscription to watch this content",
    "details": {
      "required_plan": "premium",
      "upgrade_url": "/stream/subscribe"
    }
  }
}
```

---

## 13. Cloudflare Stream Integration Plan

### 13.1 Integration Overview

Cloudflare Stream handles video upload, encoding, storage, and delivery. The Laravel backend manages metadata, access control, and business logic.

**Integration Points:**
1. Video upload via Cloudflare API
2. Webhook notifications for processing status
3. Playback URL generation
4. Analytics data retrieval
5. Thumbnail generation


### 13.2 Upload Flow

**Direct Upload (Recommended):**
```
1. Frontend requests upload URL from Laravel
2. Laravel calls Cloudflare API to get direct upload URL
3. Frontend uploads directly to Cloudflare (bypassing Laravel)
4. Cloudflare returns video UID
5. Frontend sends UID to Laravel
6. Laravel stores video metadata with provider_video_id
```

**Laravel Implementation:**
```php
class VideoUploadService
{
    public function initiateUpload(array $metadata): array
    {
        $provider = VideoProviderFactory::make('cloudflare');
        
        // Get direct upload URL from Cloudflare
        $uploadUrl = $provider->getDirectUploadUrl([
            'maxDurationSeconds' => 3600,
            'requireSignedURLs' => true,
            'meta' => [
                'name' => $metadata['title'],
                'creator_id' => $metadata['creator_id'],
            ]
        ]);
        
        // Create pending video record
        $video = Video::create([
            'uuid' => Str::uuid(),
            'title' => $metadata['title'],
            'video_provider' => 'cloudflare',
            'upload_status' => 'pending',
            'creator_id' => $metadata['creator_id'],
        ]);
        
        return [
            'video_id' => $video->id,
            'upload_url' => $uploadUrl,
        ];
    }
    
    public function completeUpload(Video $video, string $providerVideoId): void
    {
        $provider = VideoProviderFactory::make('cloudflare');
        
        // Get video details from Cloudflare
        $details = $provider->getVideo($providerVideoId);
        
        $video->update([
            'provider_video_id' => $providerVideoId,
            'playback_url' => $details->playbackUrl,
            'duration' => $details->duration,
            'thumbnail_url' => $details->thumbnailUrl,
            'upload_status' => $details->status,
        ]);
        
        // Dispatch job to monitor processing
        ProcessVideoJob::dispatch($video);
    }
}
```

**Vue Upload Component:**
```typescript
async function uploadVideo(file: File, metadata: VideoMetadata) {
  // Step 1: Get upload URL
  const { video_id, upload_url } = await api.post('/api/v1/creator/videos/init-upload', {
    title: metadata.title,
    description: metadata.description,
  });
  
  // Step 2: Upload directly to Cloudflare
  const formData = new FormData();
  formData.append('file', file);
  
  const uploadResponse = await fetch(upload_url, {
    method: 'POST',
    body: formData,
  });
  
  const { uid } = await uploadResponse.json();
  
  // Step 3: Complete upload in Laravel
  await api.post(`/api/v1/creator/videos/${video_id}/complete-upload`, {
    provider_video_id: uid,
  });
  
  return video_id;
}
```

### 13.3 Processing States

**State Flow:**
```
pending → uploading → processing → ready
                              ↓
                           failed
```

**State Handling:**
```php
class ProcessVideoJob implements ShouldQueue
{
    public function handle(Video $video): void
    {
        $provider = VideoProviderFactory::make($video->video_provider);
        
        $maxAttempts = 60; // 5 minutes (5 second intervals)
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            $status = $provider->getUploadStatus($video->provider_video_id);
            
            $video->update(['upload_status' => $status]);
            
            if ($status === 'ready') {
                $this->onVideoReady($video);
                return;
            }
            
            if ($status === 'failed') {
                $this->onVideoFailed($video);
                return;
            }
            
            sleep(5);
            $attempt++;
        }
        
        // Timeout
        $video->update(['upload_status' => 'failed']);
    }
    
    protected function onVideoReady(Video $video): void
    {
        // Generate thumbnails
        // Notify creator
        // Update analytics
        event(new VideoProcessingCompleted($video));
    }
}
```


### 13.4 Playback URL Generation

**Signed URLs (Recommended):**
```php
class VideoPlaybackService
{
    public function getPlaybackUrl(Video $video, User $user = null): string
    {
        // Check access
        if (!$this->canWatch($user, $video)) {
            throw new UnauthorizedException('Subscription required');
        }
        
        $provider = VideoProviderFactory::make($video->video_provider);
        
        // Generate signed URL with 24-hour expiration
        return $provider->getPlaybackUrl(
            $video->provider_video_id,
            signed: true,
            expiresIn: 86400 // 24 hours
        );
    }
    
    protected function canWatch(?User $user, Video $video): bool
    {
        // Free content
        if ($video->access_level === 'free') {
            return true;
        }
        
        // Require authentication
        if (!$user) {
            return false;
        }
        
        // Check subscription or purchase
        return $user->hasAccessTo($video);
    }
}
```

**Cloudflare Signed Token:**
```php
class CloudflareStreamProvider implements VideoProviderInterface
{
    public function getPlaybackUrl(string $providerVideoId, bool $signed = true, int $expiresIn = 3600): string
    {
        if (!$signed) {
            return "https://customer-{$this->accountId}.cloudflarestream.com/{$providerVideoId}/manifest/video.m3u8";
        }
        
        $token = $this->generateToken($providerVideoId, $expiresIn);
        
        return "https://customer-{$this->accountId}.cloudflarestream.com/{$providerVideoId}/manifest/video.m3u8?token={$token}";
    }
    
    protected function generateToken(string $videoId, int $expiresIn): string
    {
        $payload = [
            'sub' => $videoId,
            'kid' => $this->signingKeyId,
            'exp' => time() + $expiresIn,
            'nbf' => time(),
        ];
        
        return JWT::encode($payload, $this->signingKey, 'RS256');
    }
}
```

### 13.5 Webhook Handling

**Cloudflare Webhooks:**
Cloudflare can send webhooks for video processing events.

```php
// routes/api.php
Route::post('/webhooks/cloudflare/stream', [CloudflareWebhookController::class, 'handle'])
    ->middleware('verify.cloudflare.webhook');

// CloudflareWebhookController
class CloudflareWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $event = $request->input('event');
        $videoUid = $request->input('uid');
        
        $video = Video::where('provider_video_id', $videoUid)->first();
        
        if (!$video) {
            return response()->json(['error' => 'Video not found'], 404);
        }
        
        match($event) {
            'video.upload.complete' => $this->handleUploadComplete($video, $request->all()),
            'video.processing.complete' => $this->handleProcessingComplete($video, $request->all()),
            'video.processing.failed' => $this->handleProcessingFailed($video, $request->all()),
            default => null,
        };
        
        return response()->json(['success' => true]);
    }
    
    protected function handleProcessingComplete(Video $video, array $data): void
    {
        $video->update([
            'upload_status' => 'ready',
            'duration' => $data['duration'],
            'processing_completed_at' => now(),
        ]);
        
        // Notify creator
        $video->creator->notify(new VideoProcessingCompleted($video));
    }
}
```

### 13.6 Thumbnail Handling

**Cloudflare Auto-Generated Thumbnails:**
```php
class VideoThumbnailService
{
    public function getThumbnailUrl(Video $video, int $time = 0, int $width = 320, int $height = 180): string
    {
        if ($video->video_provider !== 'cloudflare') {
            return $video->thumbnail_url;
        }
        
        // Cloudflare Stream thumbnail URL format
        return "https://customer-{$this->accountId}.cloudflarestream.com/{$video->provider_video_id}/thumbnails/thumbnail.jpg?time={$time}s&width={$width}&height={$height}";
    }
    
    public function generateThumbnails(Video $video): void
    {
        // Generate thumbnails at different timestamps
        $timestamps = [0, 10, 30, 60]; // seconds
        
        foreach ($timestamps as $time) {
            $url = $this->getThumbnailUrl($video, $time);
            
            // Download and store in Wasabi
            $image = file_get_contents($url);
            $path = "thumbnails/{$video->uuid}/thumb_{$time}.jpg";
            Storage::disk('wasabi')->put($path, $image);
        }
    }
}
```


### 13.7 Analytics Integration

**Retrieve Analytics from Cloudflare:**
```php
class CloudflareAnalyticsService
{
    public function getVideoAnalytics(Video $video, Carbon $startDate, Carbon $endDate): array
    {
        $response = Http::withToken($this->apiToken)
            ->get("https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/stream/analytics/views", [
                'videoUID' => $video->provider_video_id,
                'since' => $startDate->toIso8601String(),
                'until' => $endDate->toIso8601String(),
            ]);
        
        return $response->json()['result'];
    }
}
```

### 13.8 Building Without Active Cloudflare Subscription

**Development Strategy:**
1. Use local provider for development
2. Design all code with provider abstraction
3. Store provider-agnostic metadata
4. Mock Cloudflare responses in tests
5. When Cloudflare is activated, switch provider in config

**Mock Provider for Testing:**
```php
class MockVideoProvider implements VideoProviderInterface
{
    public function upload(UploadedFile $file, array $metadata = []): ProviderVideoResponse
    {
        return new ProviderVideoResponse([
            'provider_video_id' => 'mock-' . Str::random(16),
            'playback_url' => 'https://example.com/mock-video.m3u8',
            'thumbnail_url' => 'https://example.com/mock-thumb.jpg',
            'duration' => 300,
            'status' => 'ready',
        ]);
    }
}
```

---

## 14. Security and Content Protection

### 14.1 Signed Playback URLs

**Purpose:** Prevent unauthorized access to video streams

**Implementation:**
- Generate time-limited signed URLs
- Include user ID in token
- Validate signature before serving
- Expire tokens after 24 hours
- Regenerate on page refresh

### 14.2 Hidden Direct Source URLs

**Strategy:**
- Never expose direct video URLs in HTML
- Use API endpoint to get playback URL
- Rotate URLs periodically
- Use CDN with access control

### 14.3 Authorization Checks

**Multi-Layer Authorization:**
```php
// 1. Route middleware
Route::get('/stream/watch/{video}')
    ->middleware(['auth', 'check.video.access']);

// 2. Policy check
Gate::authorize('view', $video);

// 3. Playback authorization
$playbackUrl = $this->videoPlaybackService->getPlaybackUrl($video, $user);
```

### 14.4 Rate Limiting

**Prevent Abuse:**
```php
// Limit playback requests
RateLimiter::for('video-playback', function (Request $request) {
    return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
});

// Limit uploads
RateLimiter::for('video-upload', function (Request $request) {
    return Limit::perHour(10)->by($request->user()->id);
});
```


### 14.5 Content Visibility Rules

**Access Control Matrix:**
```php
class VideoAccessPolicy
{
    public function canView(User $user, Video $video): bool
    {
        // Unpublished content
        if (!$video->is_published) {
            return $user->isAdmin() || $video->creator_id === $user->id;
        }
        
        // Free content
        if ($video->access_level === 'free') {
            return true;
        }
        
        // Check subscription tier
        if ($subscription = $user->activeVideoSubscription()) {
            if ($this->subscriptionCoversVideo($subscription, $video)) {
                return true;
            }
        }
        
        // Check individual purchase
        if ($user->hasPurchased($video)) {
            return true;
        }
        
        // Check institutional access
        if ($user->hasInstitutionalAccess($video)) {
            return true;
        }
        
        return false;
    }
}
```

### 14.6 Moderation Workflow

**Content Review Process:**
1. Creator uploads video
2. Video enters moderation queue
3. Content manager reviews
4. Approve → Published
5. Reject → Creator notified with reason
6. Request changes → Creator can resubmit

**Automated Checks:**
- File format validation
- Duration limits
- File size limits
- Metadata completeness
- Copyright detection (future)

### 14.7 DRM Support (Future)

**Digital Rights Management:**
- Widevine DRM integration
- FairPlay for iOS
- PlayReady for Windows
- License server setup
- Key rotation

---

## 15. MVP Scope - Clear Boundaries

### 15.1 MVP Philosophy

**CRITICAL: The MVP must be shippable, valuable, and maintainable within 8-10 weeks.**

The MVP focuses on core streaming functionality that delivers immediate value to MyGrowNet members while establishing the foundation for future expansion. Every feature included must be essential for the initial launch.

### 15.2 Phase 1 - Core MVP Features (MUST HAVE)

#### ✅ Video Management (Admin Only)
**Scope:**
- Upload videos via admin panel
- Add comprehensive metadata (title, description, categories, tags)
- Set access levels (free, basic, premium)
- Publish/unpublish videos
- Basic video editing (metadata only, not video file)
- Delete videos

**Excluded:**
- ❌ Creator self-service uploads (Phase 2)
- ❌ Bulk upload (Phase 2)
- ❌ Video editing/trimming (Phase 3)
- ❌ Automated content moderation (Phase 3)

#### ✅ Series and Episodes
**Scope:**
- Create series with seasons
- Add episodes to series
- Episode ordering and navigation
- Auto-play next episode
- Series detail pages

**Excluded:**
- ❌ Complex season management (Phase 2)
- ❌ Series recommendations (Phase 2)

#### ✅ Browse and Discovery
**Scope:**
- Homepage with featured content (manually curated)
- Category browsing
- Basic search (title and description)
- Video grid/list views
- Video detail pages
- Series detail pages

**Excluded:**
- ❌ Advanced search filters (Phase 2)
- ❌ Personalized recommendations (Phase 2)
- ❌ Trending algorithm (Phase 2)
- ❌ Related videos algorithm (Phase 2)

#### ✅ Video Playback
**Scope:**
- Responsive video player (Video.js or Plyr)
- Adaptive streaming (via Cloudflare)
- Basic playback controls (play, pause, seek, volume)
- Quality selection
- Fullscreen mode
- Playback speed control

**Excluded:**
- ❌ Picture-in-picture (Phase 2)
- ❌ Keyboard shortcuts (Phase 2)
- ❌ Chromecast support (Phase 3)
- ❌ Offline downloads (Phase 3)

#### ✅ Watch Progress Tracking
**Scope:**
- Save playback position per user per video
- Continue watching section
- Resume from last position
- Mark as completed at 95%
- Cross-device sync (via database)

**Excluded:**
- ❌ Real-time sync (Phase 2)
- ❌ Watch history export (Phase 2)

#### ✅ Access Control
**Scope:**
- Free vs premium content
- Guest preview (first 5 minutes)
- Subscription checking (integration with existing MyGrowNet subscriptions)
- Signed playback URLs
- Basic rate limiting

**Excluded:**
- ❌ Geographic restrictions (Phase 2)
- ❌ Device limits (Phase 2)
- ❌ Concurrent stream limits (Phase 2)
- ❌ DRM (Phase 3)

#### ✅ User Features
**Scope:**
- My List (watchlist)
- Watch history
- Continue watching
- Basic user preferences (quality, autoplay)

**Excluded:**
- ❌ User profiles (multiple profiles per account) (Phase 2)
- ❌ Social features (sharing, comments) (Phase 2)
- ❌ Ratings and reviews (Phase 2)

#### ✅ Admin Panel
**Scope:**
- Dashboard with key metrics (views, watch time, subscribers)
- Video management (CRUD)
- Series management (CRUD)
- Category management
- Featured content management
- Basic analytics (views, watch time per video)
- User subscription overview

**Excluded:**
- ❌ Advanced analytics (retention, drop-off) (Phase 2)
- ❌ A/B testing (Phase 3)
- ❌ Automated reports (Phase 2)

#### ✅ Cloudflare Stream Integration
**Scope:**
- Direct upload to Cloudflare
- Video processing status tracking
- Playback URL generation
- Signed URLs
- Basic webhook handling

**Excluded:**
- ❌ Live streaming (Phase 3)
- ❌ Advanced DRM (Phase 3)
- ❌ Watermarking (Phase 3)

#### ✅ Basic Analytics
**Scope:**
- Total views per video
- Total watch time per video
- Unique viewers
- Platform-wide metrics (daily views, watch time)
- Simple charts (line, bar)

**Excluded:**
- ❌ Heatmaps (Phase 2)
- ❌ Drop-off analysis (Phase 2)
- ❌ User behavior analytics (Phase 2)
- ❌ Predictive analytics (Phase 3)

### 15.3 Phase 1 - Infrastructure (MUST HAVE)

#### ✅ Database Schema
- All 18 core tables implemented
- Proper indexes and foreign keys
- Migration files

#### ✅ API Endpoints
- Public endpoints (browse, search, video details)
- Authenticated endpoints (watch, progress, watchlist)
- Admin endpoints (video management, analytics)

#### ✅ Event-Driven Processing
- Queue setup (Redis)
- Background jobs for video processing
- Event listeners for analytics

#### ✅ Provider Abstraction
- VideoProviderInterface
- CloudflareStreamProvider
- LocalVideoProvider (for development)
- VideoProviderFactory

#### ✅ Security
- Authentication middleware
- Access control policies
- Signed playback URLs
- Rate limiting

### 15.4 Phase 1 - Explicitly EXCLUDED (Phase 2+)

#### ❌ Creator Economy (Phase 2)
- Creator self-service uploads
- Creator dashboard
- Revenue sharing
- Payout processing
- Creator analytics

**Rationale:** Focus on content consumption first. Creator features require additional complexity (approval workflows, revenue calculations, payment processing) that can be added once the core platform is stable.

#### ❌ Advanced Recommendations (Phase 2)
- ML-based recommendations
- Collaborative filtering
- Personalized homepage
- "Because you watched" sections

**Rationale:** Requires significant viewing data to be effective. Manual curation is sufficient for MVP.

#### ❌ Social Features (Phase 2)
- Comments and discussions
- User ratings and reviews
- Social sharing
- User profiles
- Follow creators

**Rationale:** Social features require moderation infrastructure and community management. Focus on core viewing experience first.

#### ❌ Live Streaming (Phase 3)
- Live broadcasts
- Real-time chat
- Live event scheduling

**Rationale:** Significantly different technical requirements. Add after VOD platform is stable.

#### ❌ Mobile Apps (Phase 3)
- iOS native app
- Android native app
- Offline downloads

**Rationale:** Web platform first. Mobile apps require separate development effort and app store management.

#### ❌ Advanced Content Protection (Phase 3)
- DRM (Widevine, FairPlay)
- Forensic watermarking
- Screen capture prevention

**Rationale:** Basic signed URLs provide sufficient protection for MVP. Advanced DRM adds complexity and cost.

#### ❌ Advanced Analytics (Phase 2-3)
- Heatmaps and drop-off analysis
- A/B testing framework
- Predictive analytics
- Custom reports
- Analytics export

**Rationale:** Basic metrics are sufficient for initial content strategy. Advanced analytics require more data and development time.

### 15.5 MVP Success Criteria

**Technical:**
- [ ] Video upload and processing works reliably
- [ ] Playback starts within 3 seconds
- [ ] Watch progress saves correctly
- [ ] Admin can manage all content
- [ ] No critical bugs

**Business:**
- [ ] 100+ videos in library at launch
- [ ] 50+ active subscribers in first month
- [ ] Average watch time > 15 minutes per session
- [ ] < 5% error rate on video playback
- [ ] Positive user feedback

**User Experience:**
- [ ] Users can find content easily
- [ ] Playback is smooth and reliable
- [ ] Continue watching works across devices
- [ ] Subscription flow is clear
- [ ] Admin panel is intuitive

### 15.6 MVP Timeline

**Week 1-2: Foundation**
- Database schema and migrations
- Domain module structure
- Provider abstraction
- Basic models and repositories

**Week 3-4: Backend Core**
- API endpoints
- Video upload flow
- Access control
- Queue jobs
- Event system

**Week 5-6: Frontend Core**
- Vue components
- Browse pages
- Video player
- Watch progress
- User features

**Week 7-8: Admin Panel**
- Video management UI
- Series management
- Analytics dashboard
- Featured content management

**Week 9-10: Polish and Testing**
- Bug fixes
- Performance optimization
- Security audit
- User testing
- Documentation

### 15.7 Post-MVP Roadmap Preview

**Phase 2 (Months 2-4):**
- Creator self-service uploads
- Advanced search and filters
- Recommendation engine v1
- Enhanced analytics
- Social features (ratings, comments)

**Phase 3 (Months 5-6):**
- Mobile applications
- Live streaming
- Advanced DRM
- AI-powered features
- Institutional portal

**Phase 4 (Months 7-12):**
- White-label platform
- Advanced monetization
- Community features
- Enterprise features
- International expansion

---

## 16. Phase 2 and Future Expansion

### 16.1 Phase 2 Features

**Creator Economy (3-4 months after MVP)**
- Creator application and approval
- Self-service video upload
- Creator dashboard
- Revenue sharing system
- Payout management
- Creator analytics

**Enhanced Discovery (2-3 months)**
- Recommendation engine
- Personalized homepage
- Trending algorithm
- Related content suggestions
- Smart search with filters

**Engagement Features (2-3 months)**
- Comments and discussions
- Video ratings
- Social sharing
- User profiles
- Follow creators
- Notifications

**Advanced Analytics (1-2 months)**
- Detailed viewer analytics
- Heatmaps and drop-off points
- A/B testing framework
- Custom reports
- Export capabilities


### 16.2 Phase 3 Features

**Live Streaming (4-6 months)**
- Live video broadcasting
- Real-time chat
- Live event scheduling
- Recording and VOD conversion
- Multi-bitrate live streaming
- Live analytics

**Mobile Applications (6-8 months)**
- iOS native app
- Android native app
- Offline downloads
- Push notifications
- Mobile-optimized player
- Chromecast support

**Advanced Monetization (3-4 months)**
- Pay-per-view events
- Tiered creator subscriptions
- Donations/tips for creators
- Sponsored content marketplace
- Affiliate marketing
- Ad insertion (VAST/VPAID)

**Community Features (3-4 months)**
- User-generated playlists
- Collaborative playlists
- Watch parties
- Discussion forums
- Creator Q&A sessions
- Polls and quizzes

### 16.3 Phase 4 Features

**Enterprise Features (6-12 months)**
- White-label platform
- Custom domain support
- Multi-tenant architecture
- SSO integration
- LMS integration
- SCORM compliance
- Certification system

**AI-Powered Features (6-12 months)**
- Auto-generated subtitles
- Content moderation AI
- Smart video chapters
- Automatic tagging
- Content recommendations ML
- Thumbnail optimization
- Video summarization

**Advanced Content Protection (3-6 months)**
- DRM implementation
- Watermarking
- Geographic restrictions
- Device fingerprinting
- Forensic watermarking
- Screen capture prevention

**Institutional Portal (4-6 months)**
- Organization dashboards
- Bulk user management
- Custom content packages
- Usage reporting
- Department-level access
- Learning paths
- Progress tracking

---

## 17. Implementation Roadmap

### 17.1 Phase 1: Foundation (Weeks 1-4)

**Week 1: Planning and Setup**
- Finalize requirements
- Set up development environment
- Create database schema
- Set up Git repository
- Configure CI/CD pipeline
- Set up staging environment

**Week 2: Backend Core**
- Create migrations
- Build models and relationships
- Implement video provider abstraction
- Set up Cloudflare Stream integration (or mock)
- Build repository pattern
- Create service layer

**Week 3: API Development**
- Build REST API endpoints
- Implement authentication
- Create middleware for access control
- Build video upload flow
- Implement watch progress tracking
- Set up queue system

**Week 4: Admin Panel Backend**
- Admin video management endpoints
- Category management
- Featured content management
- Basic analytics queries
- User management integration

### 17.2 Phase 2: Frontend Development (Weeks 5-8)

**Week 5: Core Components**
- Set up Vue 3 + TypeScript
- Create layout components
- Build video card component
- Build video player component
- Create navigation
- Set up routing

**Week 6: Public Pages**
- Homepage with featured content
- Browse page
- Category pages
- Video detail page
- Watch page
- Search functionality

**Week 7: User Features**
- User dashboard
- Watch history
- Continue watching
- My list
- Subscription page
- User settings

**Week 8: Admin Panel Frontend**
- Admin dashboard
- Video management UI
- Upload interface
- Category management
- Featured content management
- Basic analytics charts


### 17.3 Phase 3: Integration and Polish (Weeks 9-10)

**Week 9: Integration**
- MyGrowNet subscription integration
- Payment gateway integration
- Email notifications
- Analytics tracking
- SEO optimization
- Performance optimization

**Week 10: Testing and Refinement**
- Unit testing
- Integration testing
- User acceptance testing
- Bug fixes
- Performance tuning
- Security audit

### 17.4 Phase 4: Launch Preparation (Weeks 11-12)

**Week 11: Content and Documentation**
- Seed initial content
- Create user documentation
- Create admin documentation
- Create API documentation
- Prepare marketing materials
- Train content team

**Week 12: Deployment and Launch**
- Production deployment
- DNS configuration
- SSL setup
- Monitoring setup
- Backup configuration
- Soft launch to beta users
- Gather feedback
- Official launch

### 17.5 Post-Launch (Ongoing)

**Month 1-2:**
- Monitor performance
- Fix critical bugs
- Gather user feedback
- Optimize based on analytics
- Content library growth

**Month 3-4:**
- Implement Phase 2 features
- Creator onboarding
- Enhanced analytics
- Mobile optimization

**Month 5-6:**
- Scale infrastructure
- Advanced features
- Marketing campaigns
- Partnership development

---

## 18. Deliverables

### 18.1 Documentation Deliverables

✅ **Concept Document** (This document)
- Complete feature specification
- Business model
- Technical architecture
- Database design
- API specification
- Implementation roadmap

📋 **Technical Architecture Summary**
- System architecture diagram
- Technology stack details
- Integration points
- Scalability considerations

📋 **Database Schema Proposal**
- Complete table definitions
- Relationships diagram
- Indexing strategy
- Migration files

📋 **API Documentation**
- Complete endpoint list
- Request/response examples
- Authentication flow
- Error handling

📋 **Implementation Roadmap**
- Phased development plan
- Timeline estimates
- Resource requirements
- Risk assessment

📋 **MVP Feature Checklist**
- Must-have features
- Nice-to-have features
- Phase 2 features
- Success metrics

### 18.2 Code Deliverables

**A. Laravel Module Structure**
```
app/Modules/GrowStream/
├── Application/
│   ├── UseCases/
│   │   ├── Video/
│   │   │   ├── CreateVideoUseCase.php
│   │   │   ├── UpdateVideoUseCase.php
│   │   │   ├── DeleteVideoUseCase.php
│   │   │   └── PublishVideoUseCase.php
│   │   ├── Series/
│   │   │   ├── CreateSeriesUseCase.php
│   │   │   └── ManageEpisodesUseCase.php
│   │   └── Watch/
│   │       ├── AuthorizePlaybackUseCase.php
│   │       └── TrackProgressUseCase.php
│   ├── Commands/
│   │   ├── UploadVideoCommand.php
│   │   ├── ProcessVideoCommand.php
│   │   └── FeatureContentCommand.php
│   ├── Queries/
│   │   ├── GetFeaturedVideosQuery.php
│   │   ├── GetTrendingVideosQuery.php
│   │   └── SearchVideosQuery.php
│   └── DTOs/
│       ├── VideoDTO.php
│       ├── SeriesDTO.php
│       └── PlaybackAuthorizationDTO.php
├── Domain/
│   ├── Entities/
│   │   ├── Video.php
│   │   ├── Series.php
│   │   ├── Creator.php
│   │   └── Subscription.php
│   ├── ValueObjects/
│   │   ├── VideoMetadata.php
│   │   ├── PlaybackUrl.php
│   │   └── AccessLevel.php
│   ├── Services/
│   │   ├── VideoAccessService.php
│   │   ├── RecommendationService.php
│   │   └── AnalyticsService.php
│   └── Repositories/
│       ├── VideoRepositoryInterface.php
│       ├── SeriesRepositoryInterface.php
│       └── CreatorRepositoryInterface.php
├── Infrastructure/
│   ├── Persistence/
│   │   ├── Eloquent/
│   │   │   ├── VideoModel.php
│   │   │   ├── SeriesModel.php
│   │   │   └── CreatorProfileModel.php
│   │   └── Repositories/
│   │       ├── EloquentVideoRepository.php
│   │       └── EloquentSeriesRepository.php
│   ├── Providers/
│   │   ├── VideoProviderInterface.php
│   │   ├── CloudflareStreamProvider.php
│   │   ├── LocalVideoProvider.php
│   │   └── VideoProviderFactory.php
│   └── Services/
│       ├── CloudflareAnalyticsService.php
│       └── ThumbnailGenerationService.php
├── Presentation/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── VideoController.php
│   │   │   ├── SeriesController.php
│   │   │   ├── WatchController.php
│   │   │   └── SearchController.php
│   │   └── Admin/
│   │       ├── VideoManagementController.php
│   │       ├── CreatorManagementController.php
│   │       └── AnalyticsController.php
│   ├── Requests/
│   │   ├── CreateVideoRequest.php
│   │   ├── UpdateVideoRequest.php
│   │   └── UploadVideoRequest.php
│   ├── Resources/
│   │   ├── VideoResource.php
│   │   ├── SeriesResource.php
│   │   └── CreatorResource.php
│   └── Middleware/
│       ├── CheckVideoAccess.php
│       └── CheckCreatorStatus.php
├── Jobs/
│   ├── ProcessVideoJob.php
│   ├── GenerateThumbnailsJob.php
│   └── CalculateAnalyticsJob.php
├── Events/
│   ├── VideoUploaded.php
│   ├── VideoProcessingCompleted.php
│   └── VideoPublished.php
├── Listeners/
│   ├── NotifyCreatorOfProcessing.php
│   └── UpdateVideoAnalytics.php
└── GrowStreamServiceProvider.php
```


**B. Vue Frontend Structure**
```
resources/js/Pages/GrowStream/
├── Public/
│   ├── Home.vue                    # Homepage with featured content
│   ├── Browse.vue                  # Browse all content
│   ├── Category.vue                # Category page
│   ├── Search.vue                  # Search results
│   ├── VideoDetail.vue             # Video detail page
│   ├── Watch.vue                   # Video player page
│   ├── SeriesDetail.vue            # Series detail page
│   └── CreatorProfile.vue          # Creator profile page
├── User/
│   ├── Dashboard.vue               # User dashboard
│   ├── WatchHistory.vue            # Watch history
│   ├── ContinueWatching.vue        # Continue watching
│   ├── MyList.vue                  # Watchlist
│   ├── Subscriptions.vue           # Subscription management
│   └── Settings.vue                # User settings
├── Creator/
│   ├── Dashboard.vue               # Creator dashboard
│   ├── Upload.vue                  # Upload video
│   ├── Videos.vue                  # Manage videos
│   ├── Series.vue                  # Manage series
│   ├── Analytics.vue               # Creator analytics
│   └── Revenue.vue                 # Revenue reports
└── Admin/
    ├── Dashboard.vue               # Admin dashboard
    ├── Videos/
    │   ├── Index.vue               # Video list
    │   ├── Create.vue              # Create video
    │   └── Edit.vue                # Edit video
    ├── Series/
    │   ├── Index.vue               # Series list
    │   └── Edit.vue                # Edit series
    ├── Creators/
    │   ├── Index.vue               # Creator list
    │   └── Applications.vue        # Creator applications
    ├── Moderation/
    │   └── Queue.vue               # Moderation queue
    ├── Analytics/
    │   └── Overview.vue            # Analytics dashboard
    └── Settings/
        └── Index.vue               # Settings

resources/js/Components/GrowStream/
├── Video/
│   ├── VideoCard.vue               # Video thumbnail card
│   ├── VideoGrid.vue               # Video grid layout
│   ├── VideoRow.vue                # Horizontal video row
│   ├── VideoPlayer.vue             # Video player wrapper
│   ├── VideoMetadata.vue           # Video info display
│   └── VideoProgress.vue           # Progress bar
├── Series/
│   ├── SeriesCard.vue              # Series card
│   ├── EpisodeList.vue             # Episode list
│   └── SeasonSelector.vue          # Season selector
├── Creator/
│   ├── CreatorCard.vue             # Creator card
│   └── CreatorBadge.vue            # Verified badge
├── Navigation/
│   ├── CategoryNav.vue             # Category navigation
│   ├── SearchBar.vue               # Search input
│   └── FilterSidebar.vue           # Filter controls
├── Common/
│   ├── HeroBanner.vue              # Hero banner
│   ├── ContentRow.vue              # Generic content row
│   ├── LoadingSpinner.vue          # Loading state
│   ├── EmptyState.vue              # Empty state
│   └── SubscriptionPrompt.vue     # Upgrade modal
└── Admin/
    ├── DataTable.vue               # Admin table
    ├── UploadForm.vue              # Upload interface
    ├── AnalyticsChart.vue          # Chart component
    └── ModerationCard.vue          # Moderation item

resources/js/Stores/
├── videoStore.ts                   # Video state management
├── playerStore.ts                  # Player state
├── searchStore.ts                  # Search state
└── userStore.ts                    # User preferences

resources/js/Services/
├── api/
│   ├── videoApi.ts                 # Video API calls
│   ├── seriesApi.ts                # Series API calls
│   ├── creatorApi.ts               # Creator API calls
│   └── watchApi.ts                 # Watch API calls
├── player/
│   ├── playerService.ts            # Player logic
│   └── progressTracker.ts          # Progress tracking
└── utils/
    ├── formatters.ts               # Data formatters
    └── validators.ts               # Form validators
```

**C. Database Migrations**

Example migration files to be created:

```
database/migrations/
├── 2026_03_11_000001_create_videos_table.php
├── 2026_03_11_000002_create_video_categories_table.php
├── 2026_03_11_000003_create_video_category_pivot_table.php
├── 2026_03_11_000004_create_video_tags_table.php
├── 2026_03_11_000005_create_video_tag_pivot_table.php
├── 2026_03_11_000006_create_video_series_table.php
├── 2026_03_11_000007_create_video_episodes_table.php
├── 2026_03_11_000008_create_creator_profiles_table.php
├── 2026_03_11_000009_create_creator_payouts_table.php
├── 2026_03_11_000010_create_video_subscriptions_table.php
├── 2026_03_11_000011_create_video_purchases_table.php
├── 2026_03_11_000012_create_watch_history_table.php
├── 2026_03_11_000013_create_watchlists_table.php
├── 2026_03_11_000014_create_video_views_table.php
├── 2026_03_11_000015_create_video_engagement_table.php
├── 2026_03_11_000016_create_video_assets_table.php
├── 2026_03_11_000017_create_content_plans_table.php
└── 2026_03_11_000018_create_featured_content_table.php
```


**D. Example Model Relationships**

```php
// Video Model
class Video extends Model
{
    public function series(): BelongsTo
    {
        return $this->belongsTo(VideoSeries::class, 'series_id');
    }
    
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
    
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(VideoCategory::class, 'video_category_pivot')
            ->withTimestamps();
    }
    
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(VideoTag::class, 'video_tag_pivot')
            ->withTimestamps();
    }
    
    public function assets(): HasMany
    {
        return $this->hasMany(VideoAsset::class);
    }
    
    public function watchHistory(): HasMany
    {
        return $this->hasMany(WatchHistory::class);
    }
    
    public function views(): HasMany
    {
        return $this->hasMany(VideoView::class);
    }
    
    public function trailer(): BelongsTo
    {
        return $this->belongsTo(Video::class, 'trailer_video_id');
    }
    
    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
    
    public function scopeFree($query)
    {
        return $query->where('access_level', 'free');
    }
    
    public function scopeTrending($query, int $days = 7)
    {
        return $query->published()
            ->withCount(['views' => function($q) use ($days) {
                $q->where('viewed_at', '>=', now()->subDays($days));
            }])
            ->orderByDesc('views_count');
    }
}

// User Model Extensions
class User extends Authenticatable
{
    public function creatorProfile(): HasOne
    {
        return $this->hasOne(CreatorProfile::class);
    }
    
    public function videoSubscriptions(): HasMany
    {
        return $this->hasMany(VideoSubscription::class);
    }
    
    public function activeVideoSubscription(): ?VideoSubscription
    {
        return $this->videoSubscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();
    }
    
    public function videoPurchases(): HasMany
    {
        return $this->hasMany(VideoPurchase::class);
    }
    
    public function watchHistory(): HasMany
    {
        return $this->hasMany(WatchHistory::class);
    }
    
    public function watchlist(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }
    
    public function hasPurchased(Video $video): bool
    {
        return $this->videoPurchases()
            ->where('purchasable_type', 'video')
            ->where('purchasable_id', $video->id)
            ->where(function($q) {
                $q->whereNull('access_expires_at')
                  ->orWhere('access_expires_at', '>', now());
            })
            ->exists();
    }
    
    public function hasAccessTo(Video $video): bool
    {
        // Check subscription
        if ($subscription = $this->activeVideoSubscription()) {
            $accessLevels = ['basic' => 1, 'premium' => 2, 'institutional' => 3];
            $userLevel = $accessLevels[$subscription->plan_type] ?? 0;
            $requiredLevel = $accessLevels[$video->access_level] ?? 0;
            
            if ($userLevel >= $requiredLevel) {
                return true;
            }
        }
        
        // Check purchase
        return $this->hasPurchased($video);
    }
}

// VideoSeries Model
class VideoSeries extends Model
{
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class, 'series_id')
            ->orderBy('season_number')
            ->orderBy('episode_number');
    }
    
    public function episodes(): HasMany
    {
        return $this->hasMany(VideoEpisode::class);
    }
    
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
    
    public function trailer(): BelongsTo
    {
        return $this->belongsTo(Video::class, 'trailer_video_id');
    }
    
    public function getEpisodesBySeason(int $season): Collection
    {
        return $this->videos()
            ->where('season_number', $season)
            ->orderBy('episode_number')
            ->get();
    }
}

// CreatorProfile Model
class CreatorProfile extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class, 'creator_id', 'user_id');
    }
    
    public function series(): HasMany
    {
        return $this->hasMany(VideoSeries::class, 'creator_id', 'user_id');
    }
    
    public function payouts(): HasMany
    {
        return $this->hasMany(CreatorPayout::class, 'creator_id');
    }
    
    public function totalEarnings(): float
    {
        return $this->payouts()
            ->where('status', 'completed')
            ->sum('amount');
    }
}
```


**E. Example API Route List**

```php
// routes/api.php

// Public Routes
Route::prefix('v1')->group(function () {
    
    // Browse and Discovery
    Route::get('/videos', [VideoController::class, 'index']);
    Route::get('/videos/featured', [VideoController::class, 'featured']);
    Route::get('/videos/trending', [VideoController::class, 'trending']);
    Route::get('/videos/new-releases', [VideoController::class, 'newReleases']);
    Route::get('/videos/{video:slug}', [VideoController::class, 'show']);
    Route::get('/videos/{video:slug}/related', [VideoController::class, 'related']);
    
    Route::get('/series', [SeriesController::class, 'index']);
    Route::get('/series/{series:slug}', [SeriesController::class, 'show']);
    Route::get('/series/{series:slug}/episodes', [SeriesController::class, 'episodes']);
    
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category:slug}/videos', [CategoryController::class, 'videos']);
    
    Route::get('/creators', [CreatorController::class, 'index']);
    Route::get('/creators/{creator:username}', [CreatorController::class, 'show']);
    Route::get('/creators/{creator:username}/videos', [CreatorController::class, 'videos']);
    
    Route::get('/search', [SearchController::class, 'search']);
    Route::get('/search/suggestions', [SearchController::class, 'suggestions']);
    
    Route::get('/plans', [SubscriptionController::class, 'plans']);
    
    // Authenticated Routes
    Route::middleware('auth:sanctum')->group(function () {
        
        // Watch and Playback
        Route::post('/watch/authorize', [WatchController::class, 'authorize']);
        Route::post('/watch/progress', [WatchController::class, 'updateProgress']);
        Route::get('/watch/history', [WatchController::class, 'history']);
        Route::delete('/watch/history/{history}', [WatchController::class, 'deleteHistory']);
        
        Route::get('/continue-watching', [WatchController::class, 'continueWatching']);
        
        // Watchlist
        Route::get('/watchlist', [WatchlistController::class, 'index']);
        Route::post('/watchlist', [WatchlistController::class, 'store']);
        Route::delete('/watchlist/{watchlist}', [WatchlistController::class, 'destroy']);
        
        // Engagement
        Route::post('/videos/{video}/like', [EngagementController::class, 'like']);
        Route::delete('/videos/{video}/like', [EngagementController::class, 'unlike']);
        Route::post('/videos/{video}/rate', [EngagementController::class, 'rate']);
        
        // Subscriptions
        Route::get('/subscriptions', [SubscriptionController::class, 'index']);
        Route::post('/subscriptions', [SubscriptionController::class, 'store']);
        Route::put('/subscriptions/{subscription}', [SubscriptionController::class, 'update']);
        Route::delete('/subscriptions/{subscription}', [SubscriptionController::class, 'cancel']);
        
        // Purchases
        Route::post('/purchases', [PurchaseController::class, 'store']);
        Route::get('/purchases', [PurchaseController::class, 'index']);
        
        // User Profile
        Route::get('/user/profile', [UserController::class, 'profile']);
        Route::put('/user/profile', [UserController::class, 'updateProfile']);
        Route::get('/user/preferences', [UserController::class, 'preferences']);
        Route::put('/user/preferences', [UserController::class, 'updatePreferences']);
        
        // Creator Routes
        Route::prefix('creator')->middleware('role:creator')->group(function () {
            Route::get('/videos', [CreatorVideoController::class, 'index']);
            Route::post('/videos', [CreatorVideoController::class, 'store']);
            Route::post('/videos/init-upload', [CreatorVideoController::class, 'initUpload']);
            Route::post('/videos/{video}/complete-upload', [CreatorVideoController::class, 'completeUpload']);
            Route::put('/videos/{video}', [CreatorVideoController::class, 'update']);
            Route::delete('/videos/{video}', [CreatorVideoController::class, 'destroy']);
            
            Route::post('/series', [CreatorSeriesController::class, 'store']);
            Route::put('/series/{series}', [CreatorSeriesController::class, 'update']);
            Route::delete('/series/{series}', [CreatorSeriesController::class, 'destroy']);
            
            Route::get('/analytics', [CreatorAnalyticsController::class, 'overview']);
            Route::get('/analytics/videos/{video}', [CreatorAnalyticsController::class, 'videoAnalytics']);
            Route::get('/revenue', [CreatorRevenueController::class, 'summary']);
            Route::get('/payouts', [CreatorRevenueController::class, 'payouts']);
            
            Route::get('/profile', [CreatorProfileController::class, 'show']);
            Route::put('/profile', [CreatorProfileController::class, 'update']);
        });
        
        Route::post('/creator/apply', [CreatorApplicationController::class, 'apply']);
        
        // Admin Routes
        Route::prefix('admin')->middleware('role:super_admin|content_manager')->group(function () {
            
            // Video Management
            Route::get('/videos', [AdminVideoController::class, 'index']);
            Route::post('/videos', [AdminVideoController::class, 'store']);
            Route::put('/videos/{video}', [AdminVideoController::class, 'update']);
            Route::delete('/videos/{video}', [AdminVideoController::class, 'destroy']);
            Route::post('/videos/bulk', [AdminVideoController::class, 'bulkAction']);
            
            // Series Management
            Route::resource('/series', AdminSeriesController::class);
            
            // Category Management
            Route::resource('/categories', AdminCategoryController::class);
            
            // Creator Management
            Route::get('/creators', [AdminCreatorController::class, 'index']);
            Route::post('/creators/{creator}/verify', [AdminCreatorController::class, 'verify']);
            Route::post('/creators/{creator}/suspend', [AdminCreatorController::class, 'suspend']);
            Route::get('/creators/applications', [AdminCreatorController::class, 'applications']);
            Route::post('/creators/applications/{application}/approve', [AdminCreatorController::class, 'approveApplication']);
            Route::post('/creators/applications/{application}/reject', [AdminCreatorController::class, 'rejectApplication']);
            
            // Moderation
            Route::get('/moderation/queue', [ModerationController::class, 'queue']);
            Route::post('/moderation/approve/{video}', [ModerationController::class, 'approve']);
            Route::post('/moderation/reject/{video}', [ModerationController::class, 'reject']);
            
            // Featured Content
            Route::resource('/featured', FeaturedContentController::class);
            
            // Analytics
            Route::get('/analytics/overview', [AdminAnalyticsController::class, 'overview']);
            Route::get('/analytics/videos', [AdminAnalyticsController::class, 'videos']);
            Route::get('/analytics/creators', [AdminAnalyticsController::class, 'creators']);
            Route::get('/analytics/revenue', [AdminAnalyticsController::class, 'revenue']);
            Route::post('/analytics/export', [AdminAnalyticsController::class, 'export']);
            
            // Payouts
            Route::post('/payouts', [AdminPayoutController::class, 'process']);
            Route::get('/payouts', [AdminPayoutController::class, 'index']);
            
            // Settings
            Route::get('/settings', [AdminSettingsController::class, 'index']);
            Route::put('/settings', [AdminSettingsController::class, 'update']);
        });
    });
});

// Webhooks (no auth middleware, verified in controller)
Route::post('/webhooks/cloudflare/stream', [CloudflareWebhookController::class, 'handle']);
```

**F. Configuration Files**

```php
// config/growstream.php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Video Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default video provider used for uploads.
    | Supported: "cloudflare", "local", "youtube", "vimeo"
    |
    */
    'default_provider' => env('GROWSTREAM_VIDEO_PROVIDER', 'cloudflare'),
    
    /*
    |--------------------------------------------------------------------------
    | Video Providers Configuration
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'cloudflare' => [
            'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),
            'api_token' => env('CLOUDFLARE_API_TOKEN'),
            'customer_subdomain' => env('CLOUDFLARE_CUSTOMER_SUBDOMAIN'),
            'signing_key_id' => env('CLOUDFLARE_SIGNING_KEY_ID'),
            'signing_key' => env('CLOUDFLARE_SIGNING_KEY'),
        ],
        
        'local' => [
            'disk' => env('GROWSTREAM_LOCAL_DISK', 'local'),
            'path' => 'videos',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    */
    'upload' => [
        'max_file_size' => 5 * 1024 * 1024 * 1024, // 5GB
        'allowed_mimetypes' => [
            'video/mp4',
            'video/quicktime',
            'video/x-msvideo',
            'video/x-matroska',
        ],
        'chunk_size' => 5 * 1024 * 1024, // 5MB
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Access Control
    |--------------------------------------------------------------------------
    */
    'access' => [
        'preview_duration' => 300, // 5 minutes for guests
        'concurrent_streams' => [
            'basic' => 1,
            'premium' => 2,
            'family' => 5,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Creator Settings
    |--------------------------------------------------------------------------
    */
    'creator' => [
        'default_revenue_share' => 70, // 70% to creator
        'minimum_payout' => 100, // K100 minimum
        'payout_schedule' => 'monthly', // monthly, weekly
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Analytics
    |--------------------------------------------------------------------------
    */
    'analytics' => [
        'retention_days' => 365,
        'aggregate_after_days' => 90,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Player Settings
    |--------------------------------------------------------------------------
    */
    'player' => [
        'autoplay_next' => true,
        'countdown_duration' => 10, // seconds
        'default_quality' => 'auto',
        'playback_speeds' => [0.5, 0.75, 1, 1.25, 1.5, 2],
    ],
];
```


**G. Environment Variables**

```bash
# .env additions for GrowStream

# Video Provider
GROWSTREAM_VIDEO_PROVIDER=cloudflare

# Cloudflare Stream
CLOUDFLARE_ACCOUNT_ID=your_account_id
CLOUDFLARE_API_TOKEN=your_api_token
CLOUDFLARE_CUSTOMER_SUBDOMAIN=your_subdomain
CLOUDFLARE_SIGNING_KEY_ID=your_key_id
CLOUDFLARE_SIGNING_KEY=your_signing_key

# Storage (Wasabi/S3 for thumbnails and assets)
WASABI_ACCESS_KEY_ID=your_access_key
WASABI_SECRET_ACCESS_KEY=your_secret_key
WASABI_DEFAULT_REGION=us-east-1
WASABI_BUCKET=mygrownet-growstream
WASABI_ENDPOINT=https://s3.wasabisys.com

# GrowStream Settings
GROWSTREAM_MAX_UPLOAD_SIZE=5368709120
GROWSTREAM_PREVIEW_DURATION=300
GROWSTREAM_DEFAULT_REVENUE_SHARE=70
```

**H. Notes for Future Cloudflare Stream Integration**

When you're ready to activate Cloudflare Stream:

1. **Sign up for Cloudflare Stream**
   - Go to Cloudflare Dashboard
   - Enable Stream product
   - Get Account ID and API Token

2. **Generate Signing Keys** (for signed URLs)
   ```bash
   # Generate RSA key pair
   openssl genrsa -out private.pem 2048
   openssl rsa -in private.pem -pubout -out public.pem
   ```
   - Upload public key to Cloudflare
   - Store private key in environment

3. **Update Environment Variables**
   - Add Cloudflare credentials to `.env`
   - Set `GROWSTREAM_VIDEO_PROVIDER=cloudflare`

4. **Test Upload Flow**
   - Upload test video via admin panel
   - Verify processing status
   - Test playback URL generation
   - Verify signed URLs work

5. **Configure Webhooks** (optional)
   - Set webhook URL in Cloudflare: `https://yourdomain.com/api/webhooks/cloudflare/stream`
   - Add webhook secret to environment
   - Test webhook delivery

6. **Migration Strategy** (if you have existing videos)
   - Keep existing videos on current provider
   - New uploads go to Cloudflare
   - Gradually migrate old content
   - Update `video_provider` field during migration

---

## 19. Success Metrics and KPIs

### 19.1 Platform Health Metrics

**Technical Performance:**
- Average page load time < 2 seconds
- Video start time < 3 seconds
- Buffering ratio < 5%
- Uptime > 99.5%
- API response time < 200ms

**Content Metrics:**
- Total videos published
- Total watch time (hours)
- Average video completion rate > 60%
- Content upload frequency
- Video processing success rate > 95%

### 19.2 User Engagement Metrics

**Viewing Behavior:**
- Daily active users (DAU)
- Monthly active users (MAU)
- Average session duration > 20 minutes
- Videos per session > 3
- Return visitor rate > 40%

**Engagement:**
- Watchlist additions
- Video likes/ratings
- Search queries per user
- Continue watching usage
- Social shares

### 19.3 Business Metrics

**Revenue:**
- Monthly recurring revenue (MRR)
- Average revenue per user (ARPU)
- Subscription conversion rate > 5%
- Churn rate < 10%
- Customer lifetime value (LTV)

**Growth:**
- New subscriber growth rate
- Creator acquisition rate
- Content library growth
- Geographic expansion
- Institutional partnerships

### 19.4 Creator Economy Metrics

**Creator Success:**
- Active creators
- Videos per creator per month
- Average creator earnings
- Creator retention rate
- Top creator performance

**Content Quality:**
- Average video completion rate by creator
- Creator subscriber growth
- Content approval rate
- Moderation queue time < 24 hours

---

## 20. Risk Assessment and Mitigation

### 20.1 Technical Risks

**Risk: Cloudflare Stream Costs Exceed Budget**
- Mitigation: Implement usage monitoring and alerts
- Mitigation: Optimize encoding settings
- Mitigation: Cache popular content
- Mitigation: Tiered pricing to cover costs

**Risk: Video Processing Failures**
- Mitigation: Retry mechanism with exponential backoff
- Mitigation: Fallback to local processing
- Mitigation: Clear error messaging to users
- Mitigation: Manual intervention workflow

**Risk: Scalability Issues**
- Mitigation: Horizontal scaling architecture
- Mitigation: CDN for static assets
- Mitigation: Database read replicas
- Mitigation: Queue-based processing

### 20.2 Business Risks

**Risk: Low Subscription Conversion**
- Mitigation: Compelling free content strategy
- Mitigation: Trial periods
- Mitigation: Clear value proposition
- Mitigation: Targeted marketing

**Risk: Creator Churn**
- Mitigation: Competitive revenue share
- Mitigation: Creator support and training
- Mitigation: Performance analytics
- Mitigation: Community building

**Risk: Content Quality Issues**
- Mitigation: Robust moderation workflow
- Mitigation: Creator guidelines and training
- Mitigation: User reporting system
- Mitigation: Quality incentives

### 20.3 Legal and Compliance Risks

**Risk: Copyright Infringement**
- Mitigation: Content ID system (future)
- Mitigation: DMCA takedown process
- Mitigation: Creator verification
- Mitigation: Terms of service enforcement

**Risk: Data Privacy Violations**
- Mitigation: GDPR/privacy law compliance
- Mitigation: Data encryption
- Mitigation: User consent management
- Mitigation: Regular security audits

---

## 21. Conclusion

GrowStream represents a strategic expansion of the MyGrowNet platform into video streaming, creating new revenue opportunities while enhancing member value. The modular, provider-agnostic architecture ensures flexibility and scalability, while the phased implementation approach minimizes risk.

### Key Success Factors

1. **Quality Content** - Curate and produce engaging educational content
2. **Seamless Experience** - Netflix-quality streaming and discovery
3. **Creator Economy** - Attract and retain quality content creators
4. **Sustainable Economics** - Balance costs with revenue
5. **Technical Excellence** - Reliable, fast, and secure platform

### Next Steps

1. **Stakeholder Review** - Present concept to leadership
2. **Budget Approval** - Secure funding for development and Cloudflare
3. **Team Assembly** - Assign developers and content team
4. **Detailed Planning** - Break down tasks and timeline
5. **Development Kickoff** - Begin Phase 1 implementation

### Long-Term Vision

GrowStream will evolve from a video library into a comprehensive learning and entertainment platform, featuring live events, creator communities, AI-powered recommendations, and institutional partnerships. It will become a key differentiator for MyGrowNet in the market.

---

## Changelog

### March 11, 2026 - Version 2.0 (Major Update)
**Critical Architecture Improvements:**
- ✅ Added DDD modular structure requirement (GrowStream as domain module)
- ✅ Added event-driven processing architecture for video workflows
- ✅ Enhanced content metadata design with comprehensive fields
- ✅ Included series and episodic structures from the beginning
- ✅ Added watch progress tracking requirements
- ✅ Included recommendation and discovery data collection preparation
- ✅ Added bandwidth protection logic and signed URL requirements
- ✅ Included creator economy infrastructure readiness
- ✅ Enhanced analytics requirements with detailed metrics
- ✅ Added mobile API readiness requirements
- ✅ Defined clear MVP boundaries with explicit inclusions/exclusions
- ✅ Added 8-10 week MVP timeline with success criteria
- ✅ Restructured technical architecture section with 18 subsections
- ✅ Added comprehensive job processing examples
- ✅ Enhanced security and access control specifications

### March 11, 2026 - Version 1.0 (Initial Release)
- Initial concept document created
- Complete technical specification
- Database design (18 tables)
- API specification (50+ endpoints)
- Implementation roadmap
- Basic MVP scope defined

---

**Document Status:** Complete and Ready for Implementation  
**Next Review Date:** After stakeholder feedback  
**Maintained By:** MyGrowNet Development Team

