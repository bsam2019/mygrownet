# GrowBuilder – MyGrowNet Website Builder

**Last Updated:** December 10, 2025  
**Status:** Concept/Planning  
**Product:** GrowBuilder (MyGrowNet Ecosystem)

---

## Table of Contents

1. [Vision Summary](#1-vision-summary)
2. [Key Advantages](#2-key-advantages-compared-to-wordpress--others)
3. [Development Plan](#3-complete-development-plan-phased)
4. [Technical Architecture](#4-technical-architecture)
5. [Team & Budget](#5-team--budget-estimate)
6. [Revenue Strategy](#6-sustainability--revenue-strategy)
7. [Risks & Mitigation](#7-risks--mitigation)
8. [Summary](#8-summary)

---

## 1. Vision Summary

MyGrowNet Website Builder (GrowBuilder) will be a **local-first, business-focused website creation platform** specifically designed for Zambian and African SMEs. It will combine:

- ✅ Easy drag-and-drop website editing
- ✅ Pre-made templates for local businesses
- ✅ Built-in MTN MoMo & Airtel Money payments
- ✅ WhatsApp-first communication
- ✅ Lightweight hosting
- ✅ Optional business tools (invoices, bookings, CRM)
- ✅ Integration with the MyGrowNet marketplace and ecosystem

**Goal:** Become the fastest, easiest way for a Zambian SME to go online.

---

## 2. Key Advantages Compared to WordPress & Others

### Market-Based Advantages

| Feature | GrowBuilder | WordPress/Others |
|---------|-------------|------------------|
| Local payment integration | ✅ MoMo & Airtel Money built-in | ❌ Requires plugins, complex setup |
| Templates for Zambian businesses | ✅ Designed for local business types | ❌ Generic global templates |
| Low-bandwidth optimization | ✅ Fast-loading pages | ❌ Often heavy, slow |
| WhatsApp business workflows | ✅ Built-in | ❌ Requires third-party tools |

### Technical Advantages

| Feature | GrowBuilder | WordPress/Others |
|---------|-------------|------------------|
| All-in-one system | ✅ No plugins, no security risks | ❌ Plugin dependency, vulnerabilities |
| Simple builder | ✅ Drag-drop built on Vue | ❌ Complex, learning curve |
| Automatic updates | ✅ Users never maintain anything | ❌ Manual updates required |
| Integrated business tools | ✅ Invoices, bookings, receipts | ❌ Separate plugins needed |

### Ecosystem Advantages

| Feature | Description |
|---------|-------------|
| MyGrowNet Marketplace | Connection to shared product catalog |
| Product Publishing | Publish products/services to marketplace |
| Referral Earnings | Optional referral program |
| Training & Guides | Built-in courses from MyGrowNet University |

---

## 3. Complete Development Plan (Phased)

### PHASE 1: Foundation (1–2 months)

#### Objectives
- Build the base website builder engine
- Provide a functional prototype for pilot customers

#### Core Features

**User Management**
- User registration/login
- Dashboard for managing websites

**Template System**
- 5 starter templates:
  - Retail Shop
  - Restaurant/Food
  - Professional Services
  - Church/Organization
  - Personal Portfolio

**Page Builder Basics**
- Sections: hero, about, services, gallery, contact
- Text & images editable inline
- Save page structure as JSON

**Hosting Model**
- Subdomains: `username.mygrownet.com`
- Lightweight rendering engine (fast on low bandwidth)

#### Technical Work
- Laravel backend
- Vue.js page editor (SPA module inside Laravel)
- Database structure (sites table, pages table, JSON layouts)
- Storage for images (AWS S3, DigitalOcean Spaces, or local first)

#### Outputs
- ✅ Working builder prototype
- ✅ Admin panel for managing users & sites

#### People Needed
- Full-stack developer
- UI/UX designer (even part-time)

---

### PHASE 2: Local Commerce Integration (1–1.5 months)

#### Objectives
- Introduce local payment systems
- Add e-commerce basics
- Release early commercial version

#### Features

**Payments**
- MTN MoMo integration
- Airtel Money integration

**Commerce**
- Simple product listing
- Cart functionality
- WhatsApp checkout option
- MoMo checkout option

**Business Tools**
- Basic invoice generator
- Business contact form with SMS or WhatsApp alerts

#### Technical Work
- Payment API integration
- Improve backend for products
- Order system
- Add SMS provider integration (Zamtel or third party)

#### Outputs
- ✅ Fully functioning business-ready builder

#### People Needed
- Backend developer
- QA tester

---

### PHASE 3: Advanced Builder + Business Tools (1.5–2 months)

#### Objectives
- Improve builder's power and flexibility
- Introduce tools SMEs need most

#### Features

**Builder Upgrades**
- Drag-and-drop blocks
- Reusable sections
- Theme colors & font options
- Blog/news feature
- SEO basics (titles, meta tags)

**Business Tools**
- Booking/scheduling system
- CRM-lite (customer list)
- Receipt generator
- Simple analytics dashboard
- Auto-generated social media posters (AI-generated inside builder)

**Ecosystem Integration**
- Publish to MyGrowNet marketplace
- Sync products to marketplace
- Dashboard for MyGrowNet services

#### Outputs
- ✅ Full MyGrowNet Website Builder 1.0
- ✅ Ready for mass rollout

---

### PHASE 4: Commercial Rollout (1 month)

#### Objectives
- Market aggressively
- Onboard SMEs, churches, tutors, farmers, local shops
- Start partnerships

#### Actions
- Partner with MTN or Airtel as a "partner app"
- Partner with Lusaka SMEs and co-ops
- Launch ambassador program
- Release tutorials (YouTube, MyGrowNet University)

#### Pricing Packages

| Package | Features | Price |
|---------|----------|-------|
| **Starter Plan** | Subdomain, templates, WhatsApp button | K50–K120/month |
| **Business Plan** | MoMo payments, products, bookings | K200–K400/month |
| **Pro / Done-for-You** | Agency build + hosting | K1,000–K2,500 setup + monthly fee |

---

### PHASE 5: Expansion (2–6 months)

#### Objectives
- Add a mobile app
- Add AI assistants
- Expand templates
- Improve builder intelligence

#### Features
- Android app for managing your website
- AI page generator ("Create my website automatically")
- AI content rewriting
- Advanced e-commerce (inventory, delivery options)

---

## 4. Technical Architecture

### System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        GrowBuilder                               │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐         │
│  │   Builder   │    │   Hosting   │    │  Commerce   │         │
│  │   Editor    │    │   Engine    │    │   Module    │         │
│  │  (Vue.js)   │    │  (Laravel)  │    │  (Laravel)  │         │
│  └─────────────┘    └─────────────┘    └─────────────┘         │
│         │                  │                  │                  │
│         └──────────────────┼──────────────────┘                  │
│                            ↓                                     │
│                   ┌─────────────────┐                           │
│                   │   REST API      │                           │
│                   │   (Laravel)     │                           │
│                   └─────────────────┘                           │
│                            │                                     │
│         ┌──────────────────┼──────────────────┐                  │
│         ↓                  ↓                  ↓                  │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐         │
│  │   MoMo      │    │   Airtel    │    │  WhatsApp   │         │
│  │   API       │    │   Money     │    │   API       │         │
│  └─────────────┘    └─────────────┘    └─────────────┘         │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Frontend (Builder)

| Technology | Purpose |
|------------|---------|
| Vue.js 3 | Page editor SPA |
| Vue Draggable / Custom | Drag-and-drop functionality |
| TailwindCSS | Styling |
| TypeScript | Type safety |

### Backend

| Technology | Purpose |
|------------|---------|
| Laravel 12 | REST API & business logic |
| MySQL/PostgreSQL | Database |
| Redis | Caching |
| Queue System | Background jobs |

### REST API Endpoints

```
Sites
├── GET    /api/builder/sites              # List user's sites
├── POST   /api/builder/sites              # Create new site
├── GET    /api/builder/sites/{id}         # Get site details
├── PUT    /api/builder/sites/{id}         # Update site
└── DELETE /api/builder/sites/{id}         # Delete site

Pages
├── GET    /api/builder/sites/{id}/pages   # List pages
├── POST   /api/builder/sites/{id}/pages   # Create page
├── GET    /api/builder/pages/{id}         # Get page
├── PUT    /api/builder/pages/{id}         # Update page
└── DELETE /api/builder/pages/{id}         # Delete page

Templates
├── GET    /api/builder/templates          # List templates
└── GET    /api/builder/templates/{id}     # Get template

Products
├── GET    /api/builder/sites/{id}/products
├── POST   /api/builder/sites/{id}/products
├── PUT    /api/builder/products/{id}
└── DELETE /api/builder/products/{id}

Orders
├── GET    /api/builder/sites/{id}/orders
├── GET    /api/builder/orders/{id}
└── PUT    /api/builder/orders/{id}/status

Payments
├── POST   /api/builder/payments/momo/initiate
├── POST   /api/builder/payments/momo/callback
├── POST   /api/builder/payments/airtel/initiate
└── POST   /api/builder/payments/airtel/callback

SMS/WhatsApp
├── POST   /api/builder/notifications/sms
└── POST   /api/builder/notifications/whatsapp
```

### Rendering Engine

```
JSON Layout Structure → Runtime Renderer → HTML Components

Example JSON:
{
  "sections": [
    {
      "type": "hero",
      "props": {
        "title": "Welcome to My Shop",
        "subtitle": "Quality products at great prices",
        "backgroundImage": "/uploads/hero.jpg",
        "ctaText": "Shop Now",
        "ctaLink": "/products"
      }
    },
    {
      "type": "products",
      "props": {
        "title": "Featured Products",
        "limit": 6,
        "layout": "grid"
      }
    }
  ]
}
```

### Hosting Architecture

| Component | Technology |
|-----------|------------|
| Web Server | Nginx or Apache |
| CDN | Cloudflare / AWS CloudFront |
| Image Storage | AWS S3 / DigitalOcean Spaces |
| Subdomain Routing | Wildcard DNS + Laravel routing |

**Subdomain Logic:**
```
*.mygrownet.com → Laravel Router → Site Lookup → Render Site
```

### Database Schema (Core Tables)

```sql
-- Sites
CREATE TABLE builder_sites (
    id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),
    name VARCHAR(255),
    subdomain VARCHAR(100) UNIQUE,
    custom_domain VARCHAR(255) NULLABLE,
    template_id BIGINT,
    settings JSON,
    is_published BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Pages
CREATE TABLE builder_pages (
    id BIGINT PRIMARY KEY,
    site_id BIGINT REFERENCES builder_sites(id),
    title VARCHAR(255),
    slug VARCHAR(100),
    layout JSON,
    is_homepage BOOLEAN DEFAULT FALSE,
    seo_title VARCHAR(255),
    seo_description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Templates
CREATE TABLE builder_templates (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    category VARCHAR(100),
    thumbnail VARCHAR(255),
    layout JSON,
    is_premium BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Products
CREATE TABLE builder_products (
    id BIGINT PRIMARY KEY,
    site_id BIGINT REFERENCES builder_sites(id),
    name VARCHAR(255),
    description TEXT,
    price DECIMAL(12,2),
    images JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Orders
CREATE TABLE builder_orders (
    id BIGINT PRIMARY KEY,
    site_id BIGINT REFERENCES builder_sites(id),
    customer_name VARCHAR(255),
    customer_phone VARCHAR(50),
    customer_email VARCHAR(255),
    items JSON,
    total DECIMAL(12,2),
    payment_method VARCHAR(50),
    payment_status VARCHAR(50),
    order_status VARCHAR(50),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 5. Team & Budget Estimate

### Minimum Team

| Role | Responsibility | Type |
|------|----------------|------|
| Fullstack Laravel/Vue Developer | Core development | Full-time |
| Frontend Vue Specialist | Builder UI/UX | Full-time |
| UI/UX Designer | Templates, interface design | Part-time |
| QA Tester | Testing, quality assurance | Part-time |
| Content Person | Templates, guides, tutorials | Part-time |

### Estimated Cost

| Phase | Budget Range (ZMW) | Notes |
|-------|-------------------|-------|
| MVP (Phase 1-2) | K35,000 – K60,000 | Using local developers, lean approach |
| Advanced (Phase 3-4) | K80,000 – K150,000 | Full feature set |
| Total | K115,000 – K210,000 | Can start with MVP and grow gradually |

### Development Timeline

```
Month 1-2:  Phase 1 (Foundation)
Month 3-4:  Phase 2 (Commerce)
Month 5-6:  Phase 3 (Advanced)
Month 7:    Phase 4 (Rollout)
Month 8+:   Phase 5 (Expansion)
```

---

## 6. Sustainability & Revenue Strategy

### Primary Revenue Streams

| Stream | Description | Estimated Revenue |
|--------|-------------|-------------------|
| Monthly Subscriptions | Starter, Business, Pro plans | K50 – K400/month per user |
| E-commerce Fees | Transaction fees on sales | 1-3% per transaction |
| Premium Templates | One-time template purchases | K50 – K200 per template |
| Done-for-You Setup | Agency build services | K1,000 – K2,500 per site |
| Domain Sales | Domain registration & renewals | K150 – K300/year |
| Marketplace Commissions | Fees on marketplace sales | 5-10% per sale |

### Secondary Revenue Streams

| Stream | Description |
|--------|-------------|
| Training Courses | MyGrowNet University courses |
| Advertising | Featured listings in marketplace |
| Partner Integrations | API access fees |
| White-Label | Reseller program for agencies |

### Revenue Projections (Year 1)

| Metric | Target |
|--------|--------|
| Registered Users | 5,000 |
| Paying Subscribers | 500 (10% conversion) |
| Average Revenue Per User | K150/month |
| Monthly Recurring Revenue | K75,000 |
| Annual Revenue | K900,000 |

---

## 7. Risks & Mitigation

| Risk | Impact | Likelihood | Mitigation Strategy |
|------|--------|------------|---------------------|
| Payment integration complexity | High | Medium | Start with MTN MoMo only, add Airtel later |
| Competition from cheap WordPress | Medium | High | Focus on local templates + MoMo + ease of use |
| Development delays | Medium | Medium | Phase approach + MVP-first |
| Users needing help | Medium | High | Add tutorials + ambassador program |
| Low initial adoption | High | Medium | Partner with SME organizations, offer free trials |
| Technical scalability | Medium | Low | Use cloud infrastructure, CDN from start |
| Payment fraud | High | Low | Implement verification, transaction limits |

### Competitive Response Strategy

| Competitor | Our Advantage |
|------------|---------------|
| WordPress | Simpler, local payments built-in, no plugins |
| Wix/Squarespace | Local templates, MoMo/Airtel, lower cost |
| Local developers | Faster, cheaper, self-service |
| Facebook Pages | Full website, e-commerce, professional look |

---

## 8. Summary

GrowBuilder represents a strong opportunity to build the **first powerful, localized website builder in Zambia** with:

### Core Differentiators

| Feature | Benefit |
|---------|---------|
| ✅ Local Payments | MTN MoMo & Airtel Money built-in |
| ✅ Local Templates | Designed for Zambian business types |
| ✅ Business Tools | Invoices, bookings, CRM included |
| ✅ Ecosystem Integration | Connected to MyGrowNet marketplace |
| ✅ Low Bandwidth | Fast on Zambian networks |
| ✅ WhatsApp-First | Native WhatsApp integration |

### Market Opportunity

- **Target Market:** 500,000+ SMEs in Zambia
- **Addressable Market:** 50,000 SMEs ready for digital presence
- **Initial Target:** 5,000 users in Year 1

### Success Metrics

| Metric | Year 1 Target |
|--------|---------------|
| Registered Users | 5,000 |
| Active Sites | 2,000 |
| Paying Subscribers | 500 |
| Monthly Revenue | K75,000 |
| Customer Satisfaction | 4.5/5 rating |

### Next Steps

1. **Immediate:** Finalize technical architecture
2. **Week 1-2:** Set up development environment
3. **Month 1:** Build MVP prototype
4. **Month 2:** Pilot with 10-20 beta users
5. **Month 3:** Launch Phase 2 with payments

---

> **Vision Statement:**  
> *"GrowBuilder: The fastest, easiest way for Zambian SMEs to go online."*

This plan positions MyGrowNet to build something **bigger than WordPress for the African market**.

---

## Appendix A: Template Categories

### Starter Templates (Phase 1)

| Template | Target Business | Key Sections |
|----------|-----------------|--------------|
| Retail Shop | General retail, boutiques | Hero, Products, About, Contact |
| Restaurant | Restaurants, food vendors | Menu, Gallery, Location, Hours |
| Professional | Consultants, lawyers, accountants | Services, About, Testimonials, Contact |
| Church/Org | Churches, NGOs, clubs | About, Events, Gallery, Donate |
| Portfolio | Freelancers, artists | Work, About, Skills, Contact |

### Additional Templates (Phase 3)

| Template | Target Business |
|----------|-----------------|
| Salon/Spa | Beauty services |
| School/Tutor | Education services |
| Real Estate | Property listings |
| Transport | Taxi, logistics |
| Agriculture | Farmers, agribusiness |

---

## Appendix B: Builder Block Types

### Basic Blocks

| Block | Description |
|-------|-------------|
| Hero | Large header with image/video |
| Text | Rich text content |
| Image | Single image with caption |
| Gallery | Image grid/carousel |
| Button | Call-to-action button |
| Divider | Section separator |

### Business Blocks

| Block | Description |
|-------|-------------|
| Products | Product grid/list |
| Services | Service cards |
| Pricing | Pricing table |
| Testimonials | Customer reviews |
| Team | Team member cards |
| Contact Form | Contact form with WhatsApp |

### Commerce Blocks

| Block | Description |
|-------|-------------|
| Featured Products | Highlighted products |
| Cart | Shopping cart |
| Checkout | Payment form |
| Order Status | Order tracking |

---

## Changelog

### December 10, 2025
- Initial concept document created
- Complete development plan outlined
- Technical architecture defined
- Revenue strategy documented
