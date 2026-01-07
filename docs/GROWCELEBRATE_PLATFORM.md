# GrowCelebrate Platform

**Last Updated:** January 7, 2026  
**Status:** Development  
**Brand:** GrowCelebrate (formerly MyGrowNet Celebrations)

## Overview

GrowCelebrate is a professional celebration services platform offering digital invitations, event decor, wedding stationery, and event planning services. The platform serves as a portfolio showcase and lead generation tool for the business.

## Business Model

**Service-Based Business** - We provide professional services to clients, not a DIY platform.

### Services Offered
1. **Digital Invitations** - Custom websites with RSVP management, photo galleries, countdown timers
2. **Event Decor** - Professional decoration services with theme design, setup & teardown
3. **Wedding Stationery** - Custom printed invitations, programs, menus, thank you cards
4. **Event Planning** - Full event coordination, vendor management, day-of coordination

### Event Types
- 💍 Weddings
- 🎂 Birthday Parties
- 💕 Anniversaries
- 🎉 General Celebrations

## Contact Information

- **Phone:** +260 976 244 690
- **Email:** celebrations@mygrownet.com
- **Location:** Lusaka, Zambia
- **Hours:** Mon-Fri 9AM-6PM, Sat 10AM-4PM

## Technical Implementation

### Routes
- `/celebrations` - Main landing page (portfolio showcase)
- `/celebrations/contact` - Contact page with quote request form
- `/weddings/templates/{slug}/preview` - Template preview system

### Key Files
- `resources/js/pages/Wedding/LandingPage.vue` - Main landing page
- `resources/js/pages/Celebrations/ContactPage.vue` - Contact page
- `app/Http/Controllers/Wedding/WeddingController.php` - Controller
- `database/seeders/WeddingTemplateSeeder.php` - Template data

### Templates (Portfolio Examples)
7 templates across 4 categories:

**Weddings (4):**
1. Modern Minimal - Clean, contemporary design
2. Elegant Gold - Luxurious gold accents
3. Garden Party - Natural, outdoor theme
4. Sunset Romance - Warm, romantic colors

**Birthdays (1):**
5. Birthday Bash - Fun, vibrant party theme

**Anniversaries (1):**
6. Anniversary Elegance - Sophisticated celebration

**Parties (1):**
7. Party Vibes - Energetic, festive design

### Features
- Category filtering (All, Weddings, Birthdays, Anniversaries, Parties)
- Template preview with device switching (Mobile, Tablet, Desktop)
- Contact form with service selection
- Responsive design with mobile navigation
- WhatsApp integration for quick contact

## Branding

### Logo
- Location: `public/images/growcelebrate/logo.png`
- Used in navigation and footers

### Color Scheme
- **Primary:** Purple (#9333ea, #7c3aed)
- **Secondary:** Pink (#ec4899)
- **Accent:** Yellow (#eab308)
- **Gradients:** Purple → Pink → Yellow for premium feel

### Messaging
- "We Create Stunning Celebration Experiences"
- Focus on professional service delivery
- Portfolio-based approach (not template marketplace)
- "Our Work" instead of "Templates"

## Database Schema

### wedding_templates table
- `id` - Primary key
- `name` - Template name
- `slug` - URL-friendly identifier
- `description` - Template description
- `category` - Event type (wedding, birthday, anniversary, party)
- `category_name` - Display name for category
- `category_icon` - Emoji icon for category
- `preview_text` - Text shown in preview
- `preview_image` - Template preview image
- `is_premium` - Premium template flag
- `settings` - JSON (colors, fonts, layout options)
- `created_at`, `updated_at` - Timestamps

## User Journey

1. **Discovery** - User visits `/celebrations` landing page
2. **Browse** - Views portfolio examples filtered by event type
3. **Preview** - Clicks to preview templates in different devices
4. **Contact** - Navigates to contact page
5. **Quote Request** - Fills form with event details and service needs
6. **Follow-up** - Team contacts within 24 hours with custom quote

## Mobile Experience

- Hamburger menu for navigation on mobile devices
- Responsive grid layouts
- Touch-friendly buttons and links
- Mobile-optimized forms

## Next Steps / Roadmap

- [ ] Integrate contact form with backend (email notifications)
- [ ] Add actual portfolio images from completed projects
- [ ] Implement pricing packages
- [ ] Add testimonials section
- [ ] Create admin panel for managing templates
- [ ] Add blog/resources section
- [ ] Implement booking/scheduling system
- [ ] Add payment integration

## Changelog

### January 7, 2026
- Rebranded from "MyGrowNet Celebrations" to "GrowCelebrate"
- Added logo integration
- Fixed contact page routing (404 issue resolved)
- Added mobile navigation menu
- Updated all contact information
- Changed "Templates" to "Our Work" terminology
- Replaced Photography service with Wedding Stationery
- Updated footer branding across all pages
