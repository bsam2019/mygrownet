# Editing Wedding Templates - Quick Guide

## Files to Provide to AI Assistant

### Core Template Files (Required)
```
resources/js/pages/Wedding/Templates/ModernMinimal.vue
resources/js/pages/Wedding/Templates/ElegantGold.vue
resources/js/pages/Wedding/Templates/GardenParty.vue
resources/js/pages/Wedding/Templates/SunsetRomance.vue
resources/js/pages/Wedding/Templates/FloraClassic.vue
resources/js/pages/Wedding/Templates/BirthdayBash.vue
resources/js/pages/Wedding/Templates/AnniversaryElegance.vue
resources/js/pages/Wedding/WeddingWebsite.vue
resources/js/pages/Wedding/LandingPage.vue
database/seeders/WeddingTemplateSeeder.php
```

### Supporting Files (Optional but Helpful)
```
resources/js/components/Wedding/WeddingTemplatePreviewModal.vue
resources/js/components/Wedding/RSVPModal.vue
app/Http/Controllers/Wedding/WeddingController.php
```

## Context to Provide

```markdown
# Wedding Template System Context

## Template Structure
Each template is a Vue 3 component that receives:

**Props:**
- `weddingEvent` - Event details (names, date, venue, story, etc.)
- `template` - Template settings (colors, fonts, layout, decorations)
- `galleryImages` - Array of image objects
- `isPreview` - Boolean indicating preview mode

**Template Settings (from seeder):**
```json
{
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
```

## How Templates Work

1. **WeddingWebsite.vue** dynamically imports template based on slug
2. Template component renders using settings from database
3. RSVP functionality handled by RSVPModal component
4. Preview mode shows demo data

## Making Changes

### Editing Template Vue Files
- Changes are immediate (just refresh browser)
- Located in: `resources/js/pages/Wedding/Templates/`

### Editing Template Settings
- Edit: `database/seeders/WeddingTemplateSeeder.php`
- Run: `php artisan db:seed --class=WeddingTemplateSeeder`
- Refresh browser

### Testing
- Landing page: http://127.0.0.1:8001/wowthem
- Preview: http://127.0.0.1:8001/wowthem/templates/{slug}/preview
- Live site: http://127.0.0.1:8001/wedding/{slug}

## Common Tasks

### Change Template Colors
Edit the `colors` object in WeddingTemplateSeeder.php

### Change Template Fonts
Edit the `fonts` object in WeddingTemplateSeeder.php

### Modify Template Layout
Edit the Vue component file directly

### Add New Template
1. Create new Vue component in Templates folder
2. Add template data to WeddingTemplateSeeder.php
3. Run seeder
```

## Example Request for AI

"I need to edit the Modern Minimal wedding template to:
1. Change the primary color from black to navy blue
2. Add a subtle animation to the hero section
3. Improve the mobile responsiveness

Here are the relevant files: [attach files listed above]"

## File Reading Commands

To quickly read template files:

```bash
# Read a specific template
cat resources/js/pages/Wedding/Templates/ModernMinimal.vue

# Read all templates
cat resources/js/pages/Wedding/Templates/*.vue

# Read seeder
cat database/seeders/WeddingTemplateSeeder.php

# Read main renderer
cat resources/js/pages/Wedding/WeddingWebsite.vue
```

## Testing After Changes

1. **Vue Component Changes:**
   - Just refresh browser
   - Check: http://127.0.0.1:8001/wowthem/templates/modern-minimal/preview

2. **Seeder Changes:**
   ```bash
   php artisan db:seed --class=WeddingTemplateSeeder
   ```
   - Then refresh browser

3. **Controller Changes:**
   - No action needed (Laravel auto-reloads)
   - Just refresh browser

## Tips for AI Assistant

1. **Focus on one template at a time** - Easier to review changes
2. **Test in preview mode first** - Use /wowthem/templates/{slug}/preview
3. **Check mobile responsiveness** - Use browser dev tools
4. **Verify RSVP functionality** - Test the RSVP modal
5. **Check all sections** - Hero, story, details, gallery, RSVP

## Common Issues

### Template Not Showing
- Check template slug matches seeder
- Verify is_active = true in seeder
- Run seeder again

### Colors Not Updating
- Run seeder after changing colors
- Clear browser cache
- Check template is using settings.colors

### Layout Broken
- Check Tailwind classes are correct
- Verify responsive classes (sm:, md:, lg:)
- Test on different screen sizes
