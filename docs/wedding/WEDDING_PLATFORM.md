# Wedding Platform

**Last Updated:** January 6, 2026  
**Status:** Production (Phase 1) / Development (Phase 2)  
**Business Line:** Wedding Services Platform (MyGrowNet Portfolio)

## Overview

A comprehensive electronic wedding invitation and planning platform that allows couples to create beautiful, shareable wedding websites with RSVP management. Built as a standalone business venture under MyGrowNet's investment portfolio.

## Current Features (Phase 1 - Complete)

### Public Wedding Website
- Responsive design with elegant flora decorations
- Multi-tab navigation: Home, Wedding Program, Q&A, Location, RSVP
- Countdown timer to wedding day
- Social sharing (WhatsApp, Facebook, copy link)
- Open Graph meta tags for rich social previews
- Google Maps integration for venue location

### RSVP System
- Guest search functionality (finds guests on invite list)
- Multi-step RSVP modal with elegant UX
- Handles unlisted guests gracefully (stores as pending inquiry)
- Dietary restrictions and message capture

### Admin Dashboard
- Access code protected
- Guest list management (add, edit, delete)
- RSVP tracking and status management
- CSV export for guest lists
- Pending inquiry review

### Live Example
- **Public URL:** `/kaoma-and-mubanga-dec-2025`
- **Admin URL:** `/wedding-admin/kaoma-and-mubanga-dec-2025`
- **Access Code:** `KAOMA2025`

---

## Phase 2: Admin Template System (In Development)

### Goal
Create an admin dashboard where staff can:
1. Select from pre-designed templates
2. Enter client details and upload images
3. Generate and publish wedding websites instantly

### Template System Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    ADMIN DASHBOARD                       │
├─────────────────────────────────────────────────────────┤
│  1. Select Template    →  Visual template gallery        │
│  2. Enter Details      →  Client info form               │
│  3. Upload Images      →  Hero, gallery, couple photos   │
│  4. Customize (opt)    →  Colors, fonts, sections        │
│  5. Preview & Publish  →  Generate public URL            │
└─────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────┐
│                  PUBLIC WEDDING WEBSITE                  │
│  /wedding/{groom}-and-{bride}-{month}-{year}            │
│  Example: /wedding/john-and-jane-dec-2025               │
└─────────────────────────────────────────────────────────┘
```

### Templates to Create

| Template | Style | Best For |
|----------|-------|----------|
| Flora Classic | Floral, purple/pink romantic (Kaoma & Mubanga design) | Traditional weddings |
| Modern Minimal | Clean, simple | Contemporary couples |
| Elegant Gold | Luxurious, gold accents | Premium weddings |
| Garden Party | Green, natural | Outdoor weddings |
| Sunset Romance | Warm oranges, pinks | Evening ceremonies |

> **Note:** The Flora Classic template is the current Kaoma & Mubanga design - this becomes our first template.

### Database Schema Changes

```sql
-- New table: Wedding Templates
CREATE TABLE wedding_templates (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    preview_image VARCHAR(255),
    settings JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_premium BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Template settings JSON structure:
{
    "colors": {
        "primary": "#9333ea",
        "secondary": "#ec4899",
        "accent": "#f59e0b",
        "background": "#ffffff",
        "text": "#1f2937"
    },
    "fonts": {
        "heading": "Great Vibes",
        "body": "Inter"
    },
    "layout": {
        "heroStyle": "full-width|centered|split",
        "navigationStyle": "tabs|sidebar|minimal"
    }
}

-- Add to wedding_events table:
ALTER TABLE wedding_events ADD COLUMN template_id BIGINT;
ALTER TABLE wedding_events ADD COLUMN custom_settings JSON;
ALTER TABLE wedding_events ADD COLUMN slug VARCHAR(100) UNIQUE;
ALTER TABLE wedding_events ADD COLUMN is_published BOOLEAN DEFAULT FALSE;
```

### Two Dashboard Types

#### 1. Staff Admin Dashboard (NEW - for MyGrowNet team)
Where staff create and manage wedding cards for clients.

```
/admin/weddings                    → List all wedding cards
/admin/weddings/create             → Create new wedding card (select template, enter details)
/admin/weddings/{id}/edit          → Edit existing wedding card
/admin/weddings/{id}/preview       → Preview before publishing
```

#### 2. Client Dashboard (EXISTING - for couples)
Where couples manage their own guest list and RSVPs. Access code protected.

```
/wedding-admin/{slug}              → Access code entry page
/wedding-admin/{slug}/dashboard    → Guest list & RSVP management
```

**Workflow:**
1. Staff creates wedding card in Admin Dashboard → selects template, enters couple details
2. System generates access code for the couple
3. Couple uses Client Dashboard to manage their guest list and track RSVPs

### Implementation Tasks

#### Phase 2.1: Database & Backend (Week 1) ✅ COMPLETE
- [x] Create `wedding_templates` migration
- [x] Add template fields to `wedding_events`
- [x] Create WeddingTemplate entity and repository
- [x] Seed initial templates (5 templates)
- [x] Create WeddingCardController for admin
- [x] Register routes for admin wedding management

#### Phase 2.2: Admin Dashboard (Week 2) ✅ COMPLETE
- [x] Create admin wedding list page (Index.vue)
- [x] Build template selection gallery
- [x] Create wedding details form (Create.vue)
- [x] Create edit page (Edit.vue)
- [x] Implement publish/unpublish functionality
- [x] Access code generation and regeneration

#### Phase 2.3: Template System (Week 3) ✅ COMPLETE
- [x] Create base template component (FloraClassic.vue)
- [x] Update WeddingController to pass template data
- [x] Implement dynamic rendering based on template_id
- [ ] Build additional template variations (Modern Minimal, Elegant Gold, etc.)

#### Phase 2.4: Publishing & Polish (Week 4)
- [x] Auto-generate slug from couple names (e.g., "john-and-jane-dec-2025")
- [x] Publish/unpublish functionality
- [x] Image upload for hero and story images
- [ ] QR code generation for sharing

---

## File Structure

```
app/Domain/Wedding/
├── Entities/
│   ├── WeddingEvent.php
│   ├── WeddingTemplate.php
│   └── WeddingGuest.php
├── Services/
│   └── WeddingPlanningService.php
└── Repositories/

resources/js/
├── components/Wedding/
│   ├── WeddingMediaLibraryModal.vue    # Media library with upload, crop, stock photos
│   ├── WeddingImageEditorModal.vue     # Image editor with crop, adjust, export
│   ├── WeddingTemplatePreviewModal.vue # Live template preview
│   └── RSVPModal.vue
├── pages/Admin/Weddings/
│   ├── Index.vue                       # List all wedding cards
│   ├── Create.vue                      # Create with template selection & media library
│   └── Edit.vue                        # Edit with media library & template preview
└── pages/Wedding/
    ├── Templates/
    │   └── FloraClassic.vue
    └── WeddingWebsite.vue              # Dynamic template rendering
```

---

## Routes Structure

### Public Routes (No Authentication)
- `/weddings` - Public landing page with templates and pricing
- `/weddings/templates/{slug}/preview` - Template preview with demo data
- `/kaoma-and-mubanga-dec-2025` - Live example wedding website
- `/wedding/{slug}` - Published wedding websites

### Member Routes (Authentication Required)
- `/my-weddings` - Member dashboard to view their wedding cards
- `/my-weddings/create` - Create new wedding event
- `/my-weddings/{id}` - View specific wedding event details
- `/my-weddings/vendors/browse` - Browse wedding vendors
- `/my-weddings/planning/tools` - Planning tools and timeline
- `/my-weddings/budget/calculator` - Budget calculator

### Client Dashboard (Access Code Protected)
- `/wedding-admin/{slug}` - Client-specific dashboard for guest management

### Staff Admin Routes (Admin Authentication Required)
- `/admin/weddings` - List all wedding cards
- `/admin/weddings/create` - Create wedding card for client
- `/admin/weddings/{id}/edit` - Edit wedding card
- `/admin/weddings/{id}/preview` - Preview before publishing

---

## Business Model

| Package | Price | Features |
|---------|-------|----------|
| Basic | K500 | 1 template, 50 guests |
| Standard | K1,500 | All templates, 150 guests |
| Premium | K3,000 | Custom design, unlimited guests |

---

## Changelog

### January 6, 2026
- **Routes reorganized:**
  - `/weddings` → Public landing page (no auth)
  - `/my-weddings` → Member dashboard (auth required)
  - `/wedding-admin/{slug}` → Client dashboard (access code)
  - `/admin/weddings` → Staff admin (admin auth)
- Created consolidated documentation
- Defined Phase 2 template system specification
- Implemented wedding_templates table and seeder
- Created WeddingTemplate entity and repository
- Built admin dashboard (Index, Create, Edit pages)
- Added WeddingCardController with full CRUD
- Registered admin routes at /admin/weddings
- Added 5 initial templates: Flora Classic, Modern Minimal, Elegant Gold, Garden Party, Sunset Romance
- Created FloraClassic.vue base template component
- Updated WeddingController to pass template settings to frontend
- Template colors, fonts, and decorations now configurable per wedding
- Rewrote WeddingWebsite.vue with full dynamic template support (all colors now use template settings)
- Added image upload support for hero and story images in Create.vue and Edit.vue
- Images stored in storage/app/public/wedding/heroes and storage/app/public/wedding/stories
- Added WeddingMediaLibraryModal component with:
  - My Media tab for uploaded images
  - Stock Photos tab with category search (wedding, couple, love, etc.)
  - Upload functionality with drag & drop
  - Delete media capability
- Added WeddingImageEditorModal component with:
  - Crop tool with aspect ratio presets (Free, 1:1, 16:9, 4:3, 3:2)
  - Adjustments: brightness, contrast, saturation, blur
  - Export settings: format (JPEG/PNG/WebP), quality, scale
  - Real-time preview with grid overlay
- Added WeddingTemplatePreviewModal component with:
  - Live preview of template with couple names and date
  - Color palette display
  - Font information
  - "Use This Template" quick selection
- Created public landing page at `/weddings`:
  - Hero section with CTA buttons
  - Features section (6 key features)
  - Templates gallery with live preview links
  - Pricing section with 3 packages (Basic K500, Standard K1500, Premium K3000)
  - Footer with company info
- Added template preview route at `/weddings/templates/{slug}/preview`:
  - Shows template with demo couple data (Sarah & Michael)
  - Uses actual template colors, fonts, and decorations
  - Full wedding website preview experience

### November 24-25, 2025
- Initial implementation for Kaoma & Mubanga wedding
- Built public website, RSVP system, admin dashboard
