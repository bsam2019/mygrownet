# GrowBuilder Product Roadmap

**Last Updated:** January 5, 2026
**Status:** Development

## Vision

GrowBuilder is not trying to be Wix or Webflow. We're building the **simplest, most effective website builder for African entrepreneurs and small businesses** - people who need to get online fast, sell products, and grow their business without technical complexity.

## Core Differentiators

1. **Simplicity over flexibility** - Guided experience, not blank canvas
2. **Mobile-first Africa** - Optimized for mobile money, low bandwidth, mobile editing
3. **AI-powered** - Generate content, not just edit it
4. **Business-focused** - Every feature helps users make money
5. **Community integrated** - Part of MyGrowNet ecosystem

---

## GrowBuilder vs BizBoost - Clear Boundaries

To avoid feature overlap, here's how GrowBuilder and BizBoost complement each other:

| Capability | GrowBuilder | BizBoost |
|------------|-------------|----------|
| **Primary Purpose** | Website builder + basic e-commerce | Marketing automation & campaigns |
| Website creation | ✅ Full builder | ❌ Mini-sites only |
| Product catalog | ✅ Full management | ❌ Display only |
| Order management | ✅ Full flow | ❌ Not included |
| Payment processing | ✅ MTN MoMo, Airtel | ❌ Not included |
| WhatsApp click-to-chat | ✅ Simple button | ❌ Use BizBoost |
| WhatsApp campaigns | ❌ Use BizBoost | ✅ Broadcast composer |
| Email campaigns | ❌ Use BizBoost | ✅ Full campaigns |
| Social media posting | ❌ Use BizBoost | ✅ Auto-posting |
| Advanced analytics | ❌ Use BizBoost | ✅ Engagement tracking |
| AI content | ✅ Website content | ✅ Marketing content |
| Customer CRM | ❌ Use BizBoost | ✅ Customer lists |

**Integration:** GrowBuilder sites can connect to BizBoost for marketing features. Users who need advanced marketing should subscribe to BizBoost.

---

## Current Features (What We Have)

### ✅ Core Editor
- Section-based drag-and-drop builder
- 15+ section types (hero, about, services, pricing, FAQ, testimonials, etc.)
- Live preview with responsive breakpoints (mobile, tablet, desktop)
- Single unified sidebar (Add, Pages, Edit tabs)
- Fullscreen preview mode
- Auto-save and undo/redo history
- Dark mode support

### ✅ Templates
- Industry-specific templates (consulting, restaurant, fitness, legal, etc.)
- Premium templates with Pro badge
- Template preview with live iframe
- Create site wizard with template selection

### ✅ AI Features (Extensive!)
- Smart chat assistant
- Section content generation
- SEO meta description generation
- Color palette suggestions
- Text improvement/rewriting
- Translation (Bemba, Nyanja, Tonga, Lozi, Swahili, French)
- Testimonial generation
- FAQ generation
- Full page generation
- Image keyword suggestions
- AI usage tracking per tier

### ✅ E-commerce
- Product management (create, edit, delete)
- Product variants support
- Order management with status tracking
- Order statistics (pending, processing, completed, revenue)
- Checkout flow
- Payment configuration (MTN MoMo, Airtel Money)
- Payment webhooks

### ✅ Site Settings
- General settings (name, subdomain, custom domain)
- Theme customization (colors, fonts, border radius)
- Splash screen options (7 styles)
- SEO settings (meta title, description, OG image, Google Analytics)
- Social links (Facebook, Instagram, Twitter, WhatsApp, LinkedIn)
- Contact info (phone, email, address)
- Logo and favicon upload (auto-generate favicon from logo)

### ✅ Analytics
- Page views over time (chart)
- Unique visitors
- Pages per visit
- Top pages
- Device breakdown (mobile, tablet, desktop)
- Period filtering (7d, 30d, 90d)

### ✅ User Management
- Site-level user management
- Role-based permissions
- Site member dashboard
- Contact message management (inbox, reply, archive)

### ✅ Blog System
- Blog posts with categories
- Post comments
- Blog section in editor

### ✅ Subscription Tiers
- Free, Starter, Business, Agency plans
- Storage limits per tier
- AI usage limits per tier
- Site limits per tier

---

## Phase 1: Polish & Quick Wins (Next 2 weeks)

Focus: Fix gaps and add high-value, low-effort features.

### 1.1 Form Submissions Dashboard (Owner View) ✅
- [x] View contact form submissions from main dashboard (not just site member area)
- [x] Messages stats card on dashboard with unread count
- [x] Recent messages panel on dashboard
- [x] Dedicated messages page per site with search/filter
- [x] Reply to messages functionality
- [x] Mark as read/archive messages
- [x] Export submissions to CSV
- [ ] Email notifications for new submissions (TODO: integrate with mail system)

### 1.2 SEO Improvements ✅
- [x] SEO score/checklist per page (in site settings)
- [x] Social share preview (Facebook, WhatsApp previews)
- [x] Auto-generate sitemap.xml (dynamic, includes pages, blog posts, products)
- [x] robots.txt configuration (auto-generated per site)

### 1.3 Performance Quick Wins ✅
- [x] Auto image compression on upload (WebP conversion) - Already implemented in ImageOptimizationService
- [x] Lazy loading for images in published sites (added loading="lazy" to all section images)
- [x] Loading speed indicator in editor (shows score, estimated load time, page size, tips)

### 1.4 UX Polish ✅
- [x] "Your site is missing X" suggestions (health suggestions on site cards)
- [x] Onboarding tutorial for first-time users
- [x] Better empty states with guidance (already implemented in dashboard)

---

## Phase 2: E-commerce & UX Enhancements (Weeks 3-6)

Focus: Make selling easier and improve the user experience.

### 2.1 E-commerce Enhancements
- [ ] Discount codes / coupons
- [ ] Low stock alerts
- [ ] Product reviews / ratings
- [ ] Order email notifications (confirmation, shipped, delivered)
- [ ] Simple inventory tracking

### 2.2 WhatsApp Quick Actions (Simple Only)
- [ ] Click-to-WhatsApp button in contact section (easy setup)
- [ ] WhatsApp link in order confirmation emails
- [ ] **Note:** For WhatsApp campaigns/broadcasts, use BizBoost

### 2.3 UX Improvements
- [ ] Pop-up / announcement bar builder
- [ ] Social media share buttons on products/blog
- [ ] Improved mobile editing experience
- [ ] Bulk product import (CSV)

### 2.4 Editor Enhancements (Guided Flexibility)
- [ ] Visual padding/margin controls (drag handles instead of number inputs)
- [ ] Better column layout options per section (1/2/3/4 columns)
- [ ] Drag-to-resize for images (corner handles)

### 2.4 Basic Analytics (Site-Level Only)
- [ ] Traffic sources (referrers)
- [ ] Conversion tracking (visits → orders)
- [ ] Revenue over time chart
- [ ] Export analytics data
- [ ] **Note:** For advanced marketing analytics, use BizBoost

---

## Phase 3: Mobile & Offline (Weeks 7-10)

Focus: Meet users where they are - on mobile, often with poor connectivity.

### 3.1 Mobile Editor (PWA)
- [ ] View/edit site content from phone
- [ ] Quick product updates
- [ ] View orders and analytics
- [ ] Push notifications for orders

### 3.2 Offline Resilience
- [ ] Draft saving when offline
- [ ] Sync when connection returns
- [ ] Optimized for 2G/3G networks
- [ ] Compressed image uploads

### 3.3 Payment Expansion
- [ ] Kazang, Zoona (Zambia)
- [ ] M-Pesa (Kenya, Tanzania)
- [ ] Card payments via DPO/Flutterwave

---

## Phase 4: AI Website Generator (Weeks 11-14)

Focus: Let AI do the heavy lifting - generate entire sites from a description.

### 4.1 AI Site Generator
- [ ] "Describe your business" → complete website
- [ ] Industry detection from description
- [ ] Auto-select best template
- [ ] Pre-fill all content with AI

### 4.2 Smart Suggestions
- [ ] "Your About page is empty" prompts
- [ ] SEO improvement suggestions
- [ ] "Add a call-to-action" recommendations
- [ ] Weekly site health report

### 4.3 AI Image Integration
- [ ] Unsplash integration for free images
- [ ] AI image search based on content
- [ ] Auto-suggest images for sections

---

## Phase 5: Ecosystem & Scale (Months 4-6)

Focus: Build network effects and recurring revenue.

### 5.1 MyGrowNet Integration
- [ ] Member profile links to their GrowBuilder site
- [ ] Showcase member businesses directory
- [ ] Referral rewards for site signups
- [ ] Community marketplace

### 5.2 Agency Features
- [ ] White-label option
- [ ] Client management dashboard
- [ ] Bulk site management
- [ ] Reseller pricing

### 5.3 Marketplace
- [ ] Premium template store (user-submitted)
- [ ] Third-party integrations
- [ ] Custom section library

---

## What We're NOT Building

To stay focused, we explicitly won't build:

- ❌ Pixel-perfect drag-and-drop (free-form canvas like Webflow - we use guided section-based editing instead)
- ❌ Complex membership sites (use Kajabi)
- ❌ Advanced e-commerce with inventory management (use Shopify)
- ❌ Custom code editor (use WordPress)
- ❌ Enterprise features (use Contentful)
- ❌ Complex animations/interactions (keep it simple)
- ❌ Email/SMS marketing campaigns (use BizBoost)
- ❌ WhatsApp broadcast/automation (use BizBoost)
- ❌ Social media auto-posting (use BizBoost)
- ❌ Customer CRM/lists (use BizBoost)
- ❌ Advanced marketing analytics (use BizBoost)

Our users want simple, fast, effective. Not powerful and complex.

**For marketing features:** Recommend users subscribe to BizBoost and connect their GrowBuilder site.

---

## Success Metrics

| Metric | Current | Target (6 months) |
|--------|---------|-------------------|
| Active sites | TBD | 500 |
| Published sites | TBD | 300 |
| Paid subscriptions | TBD | 150 |
| Avg. time to publish | TBD | < 30 min |
| Mobile editor usage | 0% | 40% |
| Sites with sales | TBD | 50 |

---

## Immediate Next Steps

1. ~~**Form submissions dashboard**~~ ✅ DONE
2. ~~**SEO improvements**~~ ✅ DONE
3. ~~**Performance quick wins**~~ ✅ DONE
4. ~~**UX Polish**~~ ✅ DONE
5. **Next:** Phase 2 - WhatsApp click-to-chat integration
6. **Then:** Discount codes / coupons

---

## Changelog

### January 6, 2026
- Completed Phase 1.4: UX Polish
  - Added site health suggestions on dashboard cards (missing logo, SEO, contact info, social links, pages)
  - Shows top 2 actionable suggestions per site with direct links to fix
  - Added onboarding tutorial for first-time editor users (7-step guided tour)
  - Tutorial highlights key features: Add sections, Pages, Edit, Preview, AI Assistant
  - Help menu in toolbar allows restarting the tutorial anytime

- Completed Phase 1.3: Performance Quick Wins
  - Verified auto image compression already implemented (WebP, thumbnails, quality optimization)
  - Added lazy loading to all image sections (about, gallery, testimonials, products)
  - Added page speed indicator in toolbar (score 0-100, estimated load time, page size, optimization tips)

- Completed Phase 1.2: SEO Improvements
  - Added SEO score/checklist in site settings
  - Added social share previews (Facebook, WhatsApp)
  - Implemented auto-generated sitemap.xml
  - Implemented auto-generated robots.txt

### January 5, 2026
- Completed Phase 1.1: Form Submissions Dashboard
  - Messages stats on dashboard
  - Recent messages panel
  - Dedicated messages page per site
  - Reply, archive, export functionality

---

## Notes

- Review this roadmap monthly
- Prioritize based on user feedback
- Ship small, iterate fast
- Every feature must help users make money
