# Wedding Template System - Complete File List

**Last Updated:** 2025-01-08
**Status:** Production

## Overview

This document lists all files associated with the wedding template system in MyGrowNet. The system supports multiple event types (weddings, birthdays, anniversaries, parties) with customizable templates.

---

## Database Layer

### Migrations
- `database/migrations/2026_01_06_100000_create_wedding_templates_table.php`
  - Creates `wedding_templates` table
  - Adds `template_id` to `wedding_events` table
  - Adds template-related fields to wedding events

- `database/migrations/2026_01_06_150000_add_category_fields_to_wedding_templates.php`
  - Adds category fields (category, category_name, category_icon, preview_text)
  - Enables multi-event type support (wedding, birthday, anniversary, party)

### Seeders
- `database/seeders/WeddingTemplateSeeder.php`
  - Seeds 7 templates:
    - **Wedding:** Modern Minimal, Elegant Gold, Garden Party, Sunset Romance
    - **Birthday:** Birthday Bash
    - **Anniversary:** Anniversary Elegance
    - **Party:** Party Vibes

- `database/seeders/DemoWeddingSeeder.php`
  - Seeds demo wedding data for testing

---

## Domain Layer (Business Logic)

### Entities
- `app/Domain/Wedding/Entities/WeddingTemplate.php`
  - Core domain entity for templates
  - Contains business logic and validation

- `app/Domain/Wedding/Entities/WeddingEvent.php`
  - Wedding event entity (uses templates)

- `app/Domain/Wedding/Entities/WeddingGuest.php`
  - Guest entity for RSVP management

- `app/Domain/Wedding/Entities/WeddingRsvp.php`
  - RSVP entity

- `app/Domain/Wedding/Entities/WeddingVendor.php`
  - Vendor entity

### Repositories (Interfaces)
- `app/Domain/Wedding/Repositories/WeddingTemplateRepositoryInterface.php`
  - Interface for template data access
  - Methods: findById, findBySlug, findAll, findActive, save, delete

- `app/Domain/Wedding/Repositories/WeddingEventRepositoryInterface.php`
- `app/Domain/Wedding/Repositories/WeddingGuestRepositoryInterface.php`
- `app/Domain/Wedding/Repositories/WeddingRsvpRepositoryInterface.php`
- `app/Domain/Wedding/Repositories/WeddingVendorRepositoryInterface.php`

### Services
- `app/Domain/Wedding/Services/WeddingPlanningService.php`
  - Business logic for wedding planning features

### Value Objects
- `app/Domain/Wedding/ValueObjects/WeddingBudget.php`
- `app/Domain/Wedding/ValueObjects/WeddingStatus.php`
- `app/Domain/Wedding/ValueObjects/VendorCategory.php`
- `app/Domain/Wedding/ValueObjects/VendorRating.php`

---

## Infrastructure Layer (Data Access)

### Eloquent Models
- `app/Infrastructure/Persistence/Eloquent/Wedding/WeddingTemplateModel.php`
  - Database model for templates
  - Table: `wedding_templates`
  - Casts: settings (JSON)

- `app/Infrastructure/Persistence/Eloquent/Wedding/WeddingEventModel.php`
  - Database model for wedding events
  - Relationship: belongsTo(WeddingTemplateModel)

- `app/Infrastructure/Persistence/Eloquent/Wedding/WeddingGuestModel.php`
- `app/Infrastructure/Persistence/Eloquent/Wedding/WeddingRsvpModel.php`
- `app/Infrastructure/Persistence/Eloquent/Wedding/WeddingVendorModel.php`
- `app/Infrastructure/Persistence/Eloquent/Wedding/WeddingBookingModel.php`
- `app/Infrastructure/Persistence/Eloquent/Wedding/WeddingReviewModel.php`

### Repository Implementations
- `app/Infrastructure/Persistence/Repositories/Wedding/EloquentWeddingTemplateRepository.php`
  - Implements WeddingTemplateRepositoryInterface
  - Converts between Eloquent models and domain entities

- `app/Infrastructure/Persistence/Repositories/Wedding/EloquentWeddingEventRepository.php`
- `app/Infrastructure/Persistence/Repositories/Wedding/EloquentWeddingGuestRepository.php`
- `app/Infrastructure/Persistence/Repositories/Wedding/EloquentWeddingRsvpRepository.php`
- `app/Infrastructure/Persistence/Repositories/Wedding/EloquentWeddingVendorRepository.php`

---

## Presentation Layer (Controllers & Routes)

### Controllers
- `app/Http/Controllers/Wedding/WeddingController.php`
  - Main controller for wedding features
  - Key methods:
    - `landingPage()` - Public landing page with template gallery
    - `previewTemplate($slug)` - Preview template with demo data
    - `weddingWebsite($slug)` - Render actual wedding website
    - `submitRSVP()` - Handle RSVP submissions
    - `searchGuest()` - Search for guests in invitation list

- `app/Http/Controllers/Admin/WeddingCardController.php`
  - Admin controller for managing wedding cards
  - Uses WeddingTemplateRepositoryInterface

### Routes
- `routes/web.php`
  - `/wowthem` - Landing page (WeddingController@landingPage)
  - `/wowthem/templates/{slug}/preview` - Template preview
  - Legacy redirects: /weddings, /celebrations

- `routes/subdomain.php`
  - Subdomain routing configuration

### Middleware
- `app/Http/Middleware/DetectSubdomain.php`
  - Handles `wowthem` subdomain routing
  - Method: `handleWowthemSubdomain()`

---

## Frontend Layer (Vue Components)

### Pages
- `resources/js/pages/Wedding/LandingPage.vue`
  - Public landing page
  - Template gallery with filtering
  - Pricing packages display
  - Template preview modal

- `resources/js/pages/Wedding/WeddingWebsite.vue`
  - Main wedding website renderer
  - Dynamically loads template components
  - Handles RSVP functionality
  - SEO/OG meta tags

- `resources/js/pages/Wedding/Dashboard.vue`
  - User dashboard for managing wedding events

- `resources/js/pages/Wedding/CreateEvent.vue`
  - Create new wedding event

- `resources/js/pages/Wedding/Planning.vue`
  - Wedding planning tools

- `resources/js/pages/Wedding/BudgetCalculator.vue`
  - Budget calculation tool

- `resources/js/pages/Wedding/Vendors.vue`
  - Vendor directory

- `resources/js/pages/Wedding/PublicLanding.vue`
  - Alternative public landing page

### Template Components
- `resources/js/pages/Wedding/Templates/ModernMinimal.vue`
  - Modern Minimal template (black/white, contemporary)

- `resources/js/pages/Wedding/Templates/ElegantGold.vue`
  - Elegant Gold template (champagne/gold, luxurious)

- `resources/js/pages/Wedding/Templates/GardenParty.vue`
  - Garden Party template (green, botanical)

- `resources/js/pages/Wedding/Templates/SunsetRomance.vue`
  - Sunset Romance template (coral/blush, romantic)

- `resources/js/pages/Wedding/Templates/FloraClassic.vue`
  - Flora Classic template (purple/pink, default)

- `resources/js/pages/Wedding/Templates/BirthdayBash.vue`
  - Birthday Bash template (colorful, playful)

- `resources/js/pages/Wedding/Templates/AnniversaryElegance.vue`
  - Anniversary Elegance template (red/pink, romantic)

### Shared Components
- `resources/js/components/Wedding/WeddingTemplatePreviewModal.vue`
  - Modal for previewing templates
  - Used in landing page

- `resources/js/components/Wedding/RSVPModal.vue`
  - RSVP form modal
  - Guest search and submission

- `resources/js/components/Wedding/WeddingMediaLibraryModal.vue`
  - Media library for wedding images

- `resources/js/components/Wedding/WeddingImageEditorModal.vue`
  - Image editor for wedding photos

---

## Service Provider

- `app/Providers/WeddingServiceProvider.php`
  - Registers wedding services
  - Binds repository interfaces to implementations
  - Registers:
    - WeddingTemplateRepositoryInterface → EloquentWeddingTemplateRepository
    - WeddingEventRepositoryInterface → EloquentWeddingEventRepository
    - WeddingGuestRepositoryInterface → EloquentWeddingGuestRepository
    - WeddingRsvpRepositoryInterface → EloquentWeddingRsvpRepository
    - WeddingVendorRepositoryInterface → EloquentWeddingVendorRepository

---

## Built Assets (Production)

- `public/build/assets/WeddingWebsite-D8BwI3WQ.css`
  - Compiled CSS for wedding website

- `public/build/assets/WeddingWebsite-BQjMSc-x.js`
  - Compiled JavaScript for wedding website

- `public/build/assets/WeddingTemplatePreviewModal.vue_vue_type_script_setup_true_lang-Bt4jj0T5.js`
  - Compiled JavaScript for template preview modal

---

## Template Structure

Each template in the database has the following structure:

```json
{
  "id": 1,
  "name": "Modern Minimal",
  "slug": "modern-minimal",
  "category": "wedding",
  "category_name": "Wedding",
  "category_icon": "💍",
  "preview_text": "Michael & Sarah",
  "description": "Ultra-clean monochrome design...",
  "preview_image": "https://...",
  "is_active": true,
  "is_premium": false,
  "settings": {
    "colors": {
      "primary": "#000000",
      "secondary": "#1a1a1a",
      "accent": "#f5f5f5",
      "background": "#ffffff"
    },
    "fonts": {
      "heading": "Cormorant Garamond",
      "body": "Inter"
    },
    "layout": {
      "heroStyle": "centered",
      "navigationStyle": "tabs",
      "showCountdown": true,
      "showGallery": true
    },
    "decorations": {
      "backgroundPattern": "minimal",
      "headerImage": "/images/Wedding/minimal-flora.jpg",
      "borderStyle": "sharp"
    }
  }
}
```

---

## Template Categories

### Wedding (💍)
- Modern Minimal
- Elegant Gold (Premium)
- Garden Party
- Sunset Romance

### Birthday (🎂)
- Birthday Bash

### Anniversary (💕)
- Anniversary Elegance

### Party (🎉)
- Party Vibes (coming soon)

---

## Key Features

1. **Multi-Event Support**
   - Weddings, birthdays, anniversaries, parties
   - Category-based filtering

2. **Template Customization**
   - Colors, fonts, layout options
   - Background patterns and decorations
   - Premium vs. free templates

3. **Preview System**
   - Live preview with demo data
   - Modal preview on landing page
   - Full-page preview with unique URL

4. **RSVP Management**
   - Guest search functionality
   - RSVP form with validation
   - Guest list management

5. **SEO Optimization**
   - Open Graph meta tags
   - Social sharing support
   - Custom OG images

---

## Usage

### Seeding Templates
```bash
php artisan db:seed --class=WeddingTemplateSeeder
```

### Accessing Templates
- Landing page: `http://127.0.0.1:8001/wowthem`
- Template preview: `http://127.0.0.1:8001/wowthem/templates/{slug}/preview`
- Wedding website: `http://127.0.0.1:8001/wedding/{slug}`

### Creating New Template
1. Add template data to `WeddingTemplateSeeder.php`
2. Create Vue component in `resources/js/pages/Wedding/Templates/`
3. Run seeder: `php artisan db:seed --class=WeddingTemplateSeeder`

---

## Common Issues

### Templates Not Loading
**Problem:** Landing page shows no templates
**Solution:** Run the seeder to populate templates
```bash
php artisan db:seed --class=WeddingTemplateSeeder
```

### Template Preview Not Working
**Problem:** Preview shows 404 error
**Solution:** Check that template slug matches database and is_active = true

### Subdomain Not Working
**Problem:** wowthem.mygrownet.com not loading
**Solution:** Check DetectSubdomain middleware configuration

---

## Related Documentation

- `docs/SUBDOMAIN_ROUTING_FIX.md` - Subdomain routing configuration
- `docs/growbuilder/SUBDOMAIN_FIXES.md` - Subdomain handling details
- `docs/LEVEL_STRUCTURE.md` - Platform structure
- `docs/MYGROWNET_PLATFORM_CONCEPT.md` - Platform overview
