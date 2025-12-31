# GrowBuilder Implementation Plan

**Last Updated:** December 29, 2025  
**Status:** Active Development  
**Planning Horizon:** Q1 2025 - Q4 2025  
**Current Progress:** 70/150+ features (47%) implemented

---

## Executive Summary

GrowBuilder has achieved significant progress with a solid foundation:
- **Core Builder:** 20 section types, 11 page templates, drag-drop editor, auto-save, undo/redo
- **E-Commerce:** Complete product/order/payment system (11 features)
- **AI Integration:** Smart chat with Groq/Llama 3.3 (6 features)
- **Team Collaboration:** Full user/role/permission system (13 features)
- **Analytics:** Page views, visitors, device tracking (6 features)

**Next Priority:** Industry-specific templates for Zambian market, Mobile Money integration, and responsive preview to accelerate user adoption.

---

## Priority Framework

**P0 (Critical):** Core functionality, revenue-generating, user retention  
**P1 (High):** Competitive advantage, user experience, scalability  
**P2 (Medium):** Nice-to-have, optimization, advanced features  
**P3 (Low):** Future enhancements, experimental features

---

## Phase 1: Foundation & Revenue (Q1 2025) - P0

### 1.1 Mobile Money Integration (2 weeks)
**Priority:** P0 - Critical for Zambian market  
**Status:** ✅ Complete (100%) - Production Ready

**Completed:**
- ✅ Payment gateway abstraction layer
- ✅ PawaPay integration (aggregator - recommended)
- ✅ Flutterwave integration (cards + mobile money)
- ✅ DPO PayGate integration (cards + mobile money)
- ✅ Transaction logging & reconciliation
- ✅ Payment status webhooks
- ✅ Refund handling
- ✅ Test mode for development
- ✅ Database schema and models
- ✅ Payment service layer
- ✅ Controllers and routes
- ✅ Frontend UI for payment configuration (Vue components)
- ✅ Payment settings page in site dashboard
- ✅ Transaction history UI with pagination
- ✅ Transaction details modal
- ✅ Webhook URL display and copy functionality
- ✅ Test connection feature
- ✅ **Checkout flow integration**
- ✅ **Payment status polling**
- ✅ **Backward compatibility with legacy system**

**Architecture:**
- **Separation:** GrowBuilder payment system is completely separate from MyGrowNet
- **User Control:** Site owners add their own payment API credentials at `/growbuilder/sites/{site}/payment/config`
- **Multi-Gateway:** Supports 3 publicly available gateways (PawaPay, Flutterwave, DPO)
- **Flexibility:** Users can switch between gateways and use test mode
- **Compatibility:** Seamlessly works with existing checkout flow, falls back to legacy system if not configured

**Documentation:** See `docs/growbuilder/MOBILE_MONEY_INTEGRATION.md`

**Status:** Production ready - ready for testing and deployment

---

### 1.2 Responsive Preview & Controls (1 week)
**Priority:** P0 - Essential for mobile-first Africa  
**Status:** Not Started

**Tasks:**
- [ ] Device preview switcher (mobile/tablet/desktop)
- [ ] Independent layout controls per breakpoint
- [ ] Responsive image handling
- [ ] Mobile navigation patterns
- [ ] Touch-friendly controls
- [ ] Preview iframe with device frames

**Dependencies:** None

---

### 1.3 Industry Templates (2 weeks)
**Priority:** P0 - Reduces time-to-launch for users  
**Status:** In Progress (11 generic templates exist, need Zambian-specific)

**Current Templates:**
- ✅ 3 Homepage variants (premium, minimal, business)
- ✅ 8 Page types (about, services, contact, faq, pricing, blog, gallery, testimonials)

**Zambian-Specific Templates Needed:**
- [ ] Restaurant/Food Business (with menu, WhatsApp ordering)
- [ ] Church/Ministry (sermons, events, donations)
- [ ] Salon/Beauty (services, booking, gallery)
- [ ] Retail Shop (products, mobile money checkout)
- [ ] Professional Services (lawyer, accountant, consultant)
- [ ] Real Estate (property listings, inquiry forms)
- [ ] Agriculture/Poultry (products, bulk orders)
- [ ] Lodge/Accommodation (rooms, booking, gallery)
- [ ] NGO/Non-Profit (mission, projects, donations)
- [ ] Tutor/Education (courses, booking, testimonials)

**Tasks:**
- [ ] Research Zambian business needs and design preferences
- [ ] Design industry-specific section combinations
- [ ] Create sample content in Zambian context
- [ ] Add industry-specific features (e.g., WhatsApp integration for restaurants)
- [ ] Template preview system with screenshots
- [ ] One-click template application with customization wizard

---

### 1.4 Custom Domain & SSL (1 week)
**Priority:** P0 - Professional credibility  
**Status:** Not Started

**Tasks:**
- [ ] Domain connection interface
- [ ] DNS configuration guide
- [ ] SSL certificate automation (Let's Encrypt)
- [ ] HTTPS enforcement
- [ ] Domain verification
- [ ] Subdomain management

---

## Phase 2: E-Commerce Enhancement (Q1-Q2 2025) - P0/P1

### 2.1 Advanced Inventory Management (2 weeks)
**Priority:** P0  
**Status:** Not Started

**Tasks:**
- [ ] Product variants (size, color, etc.)
- [ ] Stock tracking per variant
- [ ] Low stock alerts
- [ ] Bulk import/export (CSV)
- [ ] Product categories & tags
- [ ] Product search & filters
- [ ] Related products
- [ ] Product reviews & ratings

---

### 2.2 Coupons & Promotions (1 week)
**Priority:** P1  
**Status:** Not Started

**Tasks:**
- [ ] Coupon code system
- [ ] Discount types (percentage, fixed, free shipping)
- [ ] Usage limits & expiry
- [ ] Minimum order requirements
- [ ] Flash sales with countdown
- [ ] Bulk discounts
- [ ] First-time customer offers

---

### 2.3 Customer Accounts (1 week)
**Priority:** P1  
**Status:** Not Started

**Tasks:**
- [ ] Customer registration & login
- [ ] Order history
- [ ] Saved addresses
- [ ] Wishlist
- [ ] Account dashboard
- [ ] Password reset
- [ ] Email verification

---

### 2.4 WhatsApp Ordering Integration (1 week)
**Priority:** P0 - Critical for African market  
**Status:** Not Started

**Tasks:**
- [ ] WhatsApp Business API integration
- [ ] Click-to-order button
- [ ] Auto-generate order message
- [ ] WhatsApp chat widget
- [ ] Order confirmation via WhatsApp
- [ ] Catalog sync to WhatsApp

---

## Phase 3: Marketing & Growth Tools (Q2 2025) - P1

### 3.1 SEO Enhancement (1 week)
**Priority:** P1  
**Status:** Not Started

**Tasks:**
- [ ] XML sitemap auto-generation
- [ ] Robots.txt editor
- [ ] Structured data (Schema.org)
- [ ] Open Graph tags
- [ ] Twitter Cards
- [ ] SEO audit tool
- [ ] Keyword suggestions

---

### 3.2 Email Marketing (2 weeks)
**Priority:** P1  
**Status:** Not Started

**Tasks:**
- [ ] Email list management
- [ ] Campaign builder
- [ ] Email templates
- [ ] Automated sequences
- [ ] Abandoned cart emails
- [ ] Newsletter signup forms
- [ ] Analytics & tracking

---

### 3.3 Lead Capture & Popups (1 week)
**Priority:** P1  
**Status:** Not Started

**Tasks:**
- [ ] Popup builder
- [ ] Exit-intent popups
- [ ] Timed popups
- [ ] Scroll-triggered popups
- [ ] Lead form builder
- [ ] Integration with email lists

---

### 3.4 QR Code Generator (3 days)
**Priority:** P1 - Useful for menus, payments  
**Status:** Not Started

**Tasks:**
- [ ] QR code generation for pages
- [ ] QR code for products
- [ ] QR code for payments
- [ ] Downloadable QR codes
- [ ] Custom branding on QR codes

---

## Phase 4: Business Intelligence (Q2-Q3 2025) - P1

### 4.1 Analytics Dashboard (2 weeks)
**Priority:** P1  
**Status:** Not Started

**Tasks:**
- [ ] Sales reports (daily/weekly/monthly)
- [ ] Revenue charts
- [ ] Top products
- [ ] Customer analytics
- [ ] Traffic sources
- [ ] Conversion funnel
- [ ] Export reports (PDF/Excel)

---

### 4.2 Inventory Analytics (1 week)
**Priority:** P1  
**Status:** Not Started

**Tasks:**
- [ ] Stock movement reports
- [ ] Best/worst sellers
- [ ] Reorder recommendations
- [ ] Profit margin analysis
- [ ] Inventory valuation

---

## Phase 5: Advanced Features (Q3 2025) - P1/P2

### 5.1 Booking System (2 weeks)
**Priority:** P1 - High demand (salons, clinics, tutors)  
**Status:** Not Started

**Tasks:**
- [ ] Service/appointment management
- [ ] Calendar interface
- [ ] Time slot configuration
- [ ] Booking form
- [ ] Email/SMS confirmations
- [ ] Cancellation & rescheduling
- [ ] Staff assignment
- [ ] Payment integration

---

### 5.2 Restaurant Module (2 weeks)
**Priority:** P1 - Specific vertical  
**Status:** Not Started

**Tasks:**
- [ ] Menu builder
- [ ] Table management
- [ ] Online ordering
- [ ] Delivery zones & fees
- [ ] Kitchen display system
- [ ] WhatsApp order integration

---

### 5.3 Real Estate Module (2 weeks)
**Priority:** P2  
**Status:** Not Started

**Tasks:**
- [ ] Property listings
- [ ] Property search & filters
- [ ] Inquiry forms
- [ ] Virtual tours
- [ ] Agent profiles
- [ ] Featured properties

---

### 5.4 Simple LMS (3 weeks)
**Priority:** P2  
**Status:** Not Started

**Tasks:**
- [ ] Course builder
- [ ] Video hosting/embedding
- [ ] Quizzes & assessments
- [ ] Drip content
- [ ] Student progress tracking
- [ ] Certificates
- [ ] Course enrollment

---

## Phase 6: AI Enhancement (Q3-Q4 2025) - P1/P2

### 6.1 AI Image Tools (2 weeks)
**Priority:** P1  
**Status:** Not Started

**Tasks:**
- [ ] Background removal
- [ ] Image compression
- [ ] Image enhancement
- [ ] AI mockup generator
- [ ] AI product photography

---

### 6.2 AI Logo & Branding (1 week)
**Priority:** P2  
**Status:** Not Started

**Tasks:**
- [ ] AI logo generator
- [ ] Brand color palette generator
- [ ] Font pairing suggestions
- [ ] Brand style guide

---

### 6.3 AI Chatbot (2 weeks)
**Priority:** P1  
**Status:** Not Started

**Tasks:**
- [ ] Customer-facing chatbot
- [ ] Training on site content
- [ ] FAQ automation
- [ ] Lead qualification
- [ ] Handoff to human

---

### 6.4 AI Heatmaps & Analytics (2 weeks)
**Priority:** P2  
**Status:** Not Started

**Tasks:**
- [ ] Click heatmaps
- [ ] Scroll depth tracking
- [ ] Session recordings
- [ ] AI-powered insights
- [ ] Improvement suggestions

---

## Phase 7: POS System (Q4 2025) - P1

### 7.1 POS Core (3 weeks)
**Priority:** P1 - Unique differentiator  
**Status:** Not Started

**Tasks:**
- [ ] POS interface
- [ ] Product search & scan
- [ ] Cart management
- [ ] Payment processing (cash, mobile money)
- [ ] Receipt printing
- [ ] SMS receipts
- [ ] Inventory sync
- [ ] Daily sales reports

---

### 7.2 POS Advanced (2 weeks)
**Priority:** P2  
**Status:** Not Started

**Tasks:**
- [ ] Offline mode
- [ ] Staff management
- [ ] Cash drawer tracking
- [ ] Shift reports
- [ ] Customer display
- [ ] Barcode scanning

---

## Phase 8: Multi-Channel & Integrations (Q4 2025) - P2

### 8.1 Social Commerce (2 weeks)
**Priority:** P2  
**Status:** Not Started

**Tasks:**
- [ ] Facebook Shop sync
- [ ] Instagram Shopping
- [ ] TikTok Shop integration
- [ ] Unified inventory
- [ ] Multi-channel orders

---

### 8.2 Third-Party Integrations (ongoing)
**Priority:** P2  
**Status:** Not Started

**Tasks:**
- [ ] Zapier integration
- [ ] Google Analytics
- [ ] Facebook Pixel
- [ ] Mailchimp
- [ ] SMS providers

---

## Phase 9: Platform & Infrastructure (Ongoing) - P1/P2

### 9.1 Performance & Scalability
**Priority:** P1  
**Status:** Ongoing

**Tasks:**
- [ ] CDN integration
- [ ] Database optimization
- [ ] Caching strategy
- [ ] Load testing
- [ ] Auto-scaling

---

### 9.2 Security & Compliance
**Priority:** P1  
**Status:** Ongoing

**Tasks:**
- [ ] 2FA implementation
- [ ] Security audit
- [ ] GDPR/POPIA compliance tools
- [ ] Data encryption
- [ ] Backup automation

---

### 9.3 Developer Tools
**Priority:** P2  
**Status:** Not Started

**Tasks:**
- [ ] API documentation
- [ ] Webhooks
- [ ] Custom code blocks
- [ ] Plugin system
- [ ] Developer portal

---

## Phase 10: Agency & White-Label (2026) - P2/P3

### 10.1 Agency Features
**Priority:** P2  
**Status:** Not Started

**Tasks:**
- [ ] White-label mode
- [ ] Client workspace
- [ ] Team collaboration
- [ ] Client billing
- [ ] Reseller program

---

## Resource Allocation

**Phase 1 (Q1):** 2 developers, 6 weeks  
**Phase 2 (Q1-Q2):** 2 developers, 6 weeks  
**Phase 3 (Q2):** 1 developer, 5 weeks  
**Phase 4 (Q2-Q3):** 1 developer, 3 weeks  
**Phase 5 (Q3):** 2 developers, 9 weeks  
**Phase 6 (Q3-Q4):** 1 developer, 7 weeks  
**Phase 7 (Q4):** 2 developers, 5 weeks  
**Phase 8 (Q4):** 1 developer, 2 weeks  

---

## Success Metrics

**Q1 2025:**
- Mobile Money payments live
- 10 industry templates available
- 50% of sites using custom domains

**Q2 2025:**
- 100 active e-commerce sites
- Average order value: K500+
- Email marketing adoption: 30%

**Q3 2025:**
- Booking system: 20 active users
- Restaurant module: 15 active users
- AI tools usage: 60% of users

**Q4 2025:**
- POS system: 10 pilot users
- Multi-channel: 5 active users
- Platform revenue: 2x Q1

---

## Risk Mitigation

**Technical Risks:**
- Mobile Money API reliability → Implement retry logic & fallbacks
- Performance at scale → Early load testing & optimization
- Data security → Regular audits & penetration testing

**Business Risks:**
- Feature creep → Strict prioritization & MVP approach
- User adoption → Strong onboarding & support
- Competition → Focus on local market strengths

---

## Review Schedule

- **Weekly:** Sprint planning & progress review
- **Monthly:** Phase completion & metrics review
- **Quarterly:** Strategic review & roadmap adjustment

---

## Changelog

### December 29, 2025
- Initial implementation plan created
- Phases 1-10 defined with priorities
- Resource allocation estimated
- **Baseline audit completed:** 70/150+ features (47%) implemented
- **Discovered 11 existing page templates** (generic, need Zambian-specific variants)
- Updated plan to reflect actual implementation status
- Prioritized Phase 1 features for Q1 2025 launch
- Adjusted template task to focus on industry-specific Zambian templates
- **✅ Mobile Money Integration COMPLETED (100%)**:
  - Implemented payment gateway abstraction layer
  - Added 3 publicly available gateways (PawaPay, Flutterwave, DPO)
  - Removed direct mobile money APIs (not publicly available)
  - Created separate payment system for GrowBuilder (independent from MyGrowNet)
  - Built transaction logging, webhooks, and refund handling
  - Completed full frontend UI (configuration, transaction history, webhooks)
  - **Integrated with checkout flow** - customers can now pay via configured gateways
  - **Payment status polling** - real-time payment verification
  - **Backward compatibility** - works with legacy payment system
  - Site owners configure at `/growbuilder/sites/{site}/payment/config`
  - **Status:** Production ready
- **✅ Site Admin Product Management COMPLETED**:
  - Site admins can now manage products from `/sites/{subdomain}/dashboard/products`
  - Follows DDD architecture (Domain/Application/Infrastructure layers)
  - Added ProductRepositoryInterface and EloquentProductRepository
  - Created use cases: ListProducts, CreateProduct, UpdateProduct, DeleteProduct
  - Built SiteProductController for site member dashboard
  - Created Vue pages: Index, Create, Edit with full CRUD functionality
  - Added products.view/create/edit/delete permissions
  - Updated SiteMemberLayout navigation to include Products link
  - **Status:** Production ready
- **✅ Products Section Widget with Checkout COMPLETED**:
  - Products section now displays real products from database (dynamic per site)
  - Added cart functionality with localStorage persistence (unique per site)
  - Cart sidebar with quantity controls, remove items, clear cart
  - Cart notification toast when adding items
  - Cart icon in navigation with item count badge
  - Created Product detail page (`/sites/{subdomain}/product/{slug}`)
    - Image gallery with thumbnails
    - Quantity selector
    - Add to cart functionality
    - Related products section
  - Created Checkout page (`/sites/{subdomain}/checkout`)
    - Customer information form
    - Payment method selection (supports all configured methods)
    - Order summary with item management
    - Order submission to existing checkout API
    - Success confirmation with order number
    - WhatsApp redirect for WhatsApp orders
    - Online payment redirect for gateway payments
  - Updated routes to include product and checkout pages
  - **Status:** Production ready
- **✅ Section Layout Variations IMPLEMENTED**:
  - Added `layout` field to section schemas for: Hero, Services, Features, Testimonials, Pricing, Team, Gallery, About, FAQ, Contact, CTA, Stats
  - Updated `sectionTemplates.ts` with layout options and AI hints
  - Updated `SectionTemplateService.php` backend with layout support
  - Implemented rendering for multiple layouts in `Site.vue`:
    - **Hero**: Centered, Split-right, Split-left, Slideshow (with auto-play, dots, arrows)
    - **Services**: Grid, List, Cards with images, Alternating rows
    - **Features**: Grid, Checklist, Numbered steps
    - **Testimonials**: Grid, Carousel, Single quote, With photos
    - **Team**: Grid, Social links, Compact list
    - **About**: Image right, Image left, Image top
    - **FAQ**: Accordion, Two-column, Simple list
  - Added slideshow state management and navigation functions
  - Added testimonial carousel navigation
  - **Status:** Production ready


---

## Section Layout Variations & Page Templates

**Last Updated:** December 29, 2025  
**Status:** ✅ Phase 1-3 Complete (Core Layouts Implemented)  
**Priority:** P1 - Enhances user experience and site uniqueness

### Overview

To provide users with more design flexibility while maintaining quality and consistency, we have implemented multiple layout variations for each section type.

---

### Section Layout Variations

#### High-Impact Sections (3-5 layouts each) - ✅ IMPLEMENTED

| Section | Layouts | Status |
|---------|---------|--------|
| **Hero** | 5 layouts: Centered, Split-right, Split-left, Video background, Slideshow | ✅ Complete |
| **Services** | 4 layouts: Grid, List with icons, Cards with images, Alternating rows | ✅ Complete |
| **Testimonials** | 4 layouts: Cards grid, Carousel, Large single quote, With photos | ✅ Complete |
| **Pricing** | 3 layouts: Cards, Comparison table, Toggle (monthly/yearly) | Schema ready |
| **Team** | 3 layouts: Grid cards, Cards with social links, Compact list | ✅ Complete |
| **Gallery** | 3 layouts: Grid, Masonry, Lightbox | Schema ready |

#### Medium-Impact Sections (2-3 layouts each) - ✅ IMPLEMENTED

| Section | Layouts | Status |
|---------|---------|--------|
| **About** | 3 layouts: Image right, Image left, Full-width image top | ✅ Complete |
| **Features** | 3 layouts: Icon grid, Checklist, Numbered steps | ✅ Complete |
| **FAQ** | 3 layouts: Accordion, Two-column, Simple list | ✅ Complete |
| **Contact** | 3 layouts: Side-by-side, Stacked, With embedded map | Schema ready |
| **CTA** | 3 layouts: Banner full-width, Split with image, Minimal centered | Schema ready |
| **Stats** | 3 layouts: Row inline, Grid boxes, With icons | Schema ready |
| **Blog** | 3 layouts: Grid, List with thumbnails, Featured + grid | Existing |
| **Products** | 2 layouts: Grid (configurable columns), Carousel/slider | Existing |

#### Simple Sections (1-2 layouts each)

| Section | Layouts | Description |
|---------|---------|-------------|
| **Page Header** | 2 layouts | Centered, Left-aligned with breadcrumbs |
| **Map** | 2 layouts | Full-width, With sidebar info |
| **Video** | 2 layouts | Centered, With text overlay |
| **Divider** | 3 layouts | Line, Space, Decorative (dots/wave) |

---

### Slideshow/Carousel Components - ✅ IMPLEMENTED

#### Hero Slideshow ✅
- Multiple slides with individual backgrounds (image)
- Per-slide title, subtitle, and CTA buttons
- Auto-play with configurable interval (3-10 seconds)
- Navigation dots and arrows
- Mobile responsive

#### Testimonials Carousel ✅
- Auto-rotating testimonials
- Navigation dots and arrows
- Single testimonial view with navigation

#### Gallery Lightbox Carousel
- Click to open fullscreen (planned)
- Keyboard navigation (planned)
- Touch/swipe support (planned)

#### Products Carousel
- Featured products slider (planned)
- Configurable items per view (planned)

---

### Page Templates

#### Homepage Templates (5 variants)

| Template | Description | Key Sections |
|----------|-------------|--------------|
| **Business Pro** | Full-featured corporate | Hero, Stats, Services, About, Testimonials, CTA, Contact |
| **Minimal** | Clean, elegant | Hero (minimal), Features, About, CTA |
| **Landing Page** | Conversion-focused | Hero, Benefits, Testimonials, Pricing, CTA |
| **E-Commerce** | Shop-focused | Hero slideshow, Featured Products, Categories, Testimonials |
| **Portfolio** | Creative showcase | Hero, Gallery, About, Services, Contact |

#### Inner Page Templates (2-3 variants each)

| Page Type | Templates |
|-----------|-----------|
| **About** | Story-focused, Team-focused, Timeline/History |
| **Services** | Grid overview, Detailed (accordion), Individual service pages |
| **Contact** | Simple form, Full info + map, Multi-location |
| **Pricing** | Comparison cards, Feature matrix, FAQ included |
| **Shop** | Grid with filters, Featured + categories, Minimal |
| **Blog** | Grid layout, List with sidebar, Magazine style |
| **FAQ** | Accordion, Categorized, Search-enabled |
| **Gallery** | Grid, Masonry, Categorized albums |
| **Testimonials** | Cards, Video testimonials, Case studies |

---

### Implementation Phases

#### Phase 1: Core Layouts (Week 1-2)
- [ ] Add `layout` field to section schemas
- [ ] Implement layout selector in section inspector
- [ ] Create 2 layouts for Hero (centered, split)
- [ ] Create 2 layouts for Services (grid, list)
- [ ] Create 2 layouts for Testimonials (cards, single)

#### Phase 2: Slideshow Component (Week 3)
- [ ] Build reusable Slideshow/Carousel Vue component
- [ ] Implement Hero Slideshow layout
- [ ] Add slideshow controls to inspector
- [ ] Mobile touch/swipe support
- [ ] Accessibility (keyboard nav, ARIA)

#### Phase 3: Remaining High-Impact (Week 4)
- [ ] Complete Hero layouts (video bg, slideshow)
- [ ] Complete Services layouts (cards, alternating)
- [ ] Complete Testimonials layouts (carousel, photos)
- [ ] Pricing layouts (cards, table, toggle)
- [ ] Team layouts (grid, social, compact)

#### Phase 4: Medium-Impact Sections (Week 5)
- [ ] About layouts
- [ ] Features layouts
- [ ] FAQ layouts
- [ ] Contact layouts
- [ ] CTA layouts
- [ ] Stats layouts
- [ ] Blog layouts
- [ ] Products carousel

#### Phase 5: Page Templates (Week 6)
- [ ] Create 5 homepage template variants
- [ ] Create inner page template variants
- [ ] Template preview thumbnails
- [ ] Template selection UI in page creation
- [ ] AI template recommendation based on business type

---

### Technical Implementation

#### Schema Changes

```typescript
// sectionSchemas.ts - Add layout field
{
    key: 'layout',
    type: 'select',
    label: 'Layout',
    options: [
        { label: 'Centered', value: 'centered' },
        { label: 'Split Left', value: 'split-left' },
        { label: 'Split Right', value: 'split-right' },
        { label: 'Slideshow', value: 'slideshow' },
    ]
}
```

#### Rendering Logic

```vue
<!-- Site.vue - Layout-aware rendering -->
<template v-if="section.type === 'hero'">
    <HeroCentered v-if="section.content.layout === 'centered'" :section="section" />
    <HeroSplit v-else-if="section.content.layout?.startsWith('split')" :section="section" />
    <HeroSlideshow v-else-if="section.content.layout === 'slideshow'" :section="section" />
    <HeroCentered v-else :section="section" /> <!-- Default -->
</template>
```

#### Slideshow Component Props

```typescript
interface SlideshowProps {
    slides: Array<{
        backgroundImage?: string;
        backgroundVideo?: string;
        backgroundColor?: string;
        title: string;
        subtitle?: string;
        buttonText?: string;
        buttonLink?: string;
    }>;
    autoPlay?: boolean;
    interval?: number; // ms, default 5000
    transition?: 'fade' | 'slide' | 'zoom';
    showDots?: boolean;
    showArrows?: boolean;
    pauseOnHover?: boolean;
}
```

---

### Success Metrics

- **User Engagement:** 50%+ of users try different layouts
- **Site Uniqueness:** Reduced "sameness" across sites
- **Time to Launch:** Maintained or improved (templates help)
- **Support Tickets:** No increase in layout-related issues

---

### Dependencies

- Swiper.js or similar for carousel functionality
- Intersection Observer for lazy loading
- CSS transitions for smooth animations

