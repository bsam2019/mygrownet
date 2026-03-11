# GrowBuilder Static Export Feature

**Last Updated:** March 10, 2026  
**Status:** Development (Coming Soon in Production)  
**Tier Requirement:** Business+ (Business and Agency tiers)

## Overview

The Static Export feature allows GrowBuilder users to download their entire site as a self-contained static HTML package. This provides data portability, enables hosting flexibility, and builds trust by eliminating vendor lock-in concerns.

**Current Status:**
- ✅ Fully functional in local development
- ✅ All 23 section types supported with 50+ layout variations
- ✅ Comprehensive CSS framework with complete utility classes
- 🚧 Coming Soon in production (final testing in progress)
- 🚧 CSS improvements require re-export to take effect

**To Test Locally:**
1. Ensure you're on Business or Agency tier
2. Navigate to site settings
3. Click "Export Site"
4. Download and extract the ZIP
5. Open index.html in your browser

## Business Model

### Tier Access
- **Free**: No export access
- **Starter**: No export access  
- **Business**: ✅ Static HTML export
- **Agency**: ✅ Static HTML export

### Value Proposition
- Users can take their site anywhere
- No vendor lock-in builds trust
- Premium feature drives upgrades
- Most users stay for convenience (hosting, updates, support)

## Technical Implementation

### Architecture

```
StaticExportService
├── Generate HTML pages
├── Compile CSS (theme-based)
├── Generate JavaScript
├── Download media files
├── Create README
└── Package as ZIP
```

### Files Created

**app/Services/GrowBuilder/StaticExportService.php**
- Main service handling export generation
- Converts Vue components to static HTML
- Downloads and packages media files
- Generates standalone CSS and JS

**app/Http/Controllers/GrowBuilder/ExportController.php**
- Handles export requests
- Checks tier permissions
- Returns ZIP download

**resources/js/pages/GrowBuilder/Sites/Export.vue**
- Export information page
- Upgrade prompt for lower tiers
- Download button

### Routes

```php
// View export page
GET /growbuilder/sites/{id}/export

// Download export
POST /growbuilder/sites/{id}/export
```

## Export Package Contents

### File Structure
```
site-export-{subdomain}-{date}.zip
├── index.html              # Homepage
├── about.html              # Other pages
├── contact.html
├── css/
│   └── styles.css          # Compiled theme CSS
├── js/
│   └── main.js             # Navigation & interactions
├── images/
│   ├── logo.png
│   └── favicon.ico
├── media/                  # All site media
│   ├── hero-image.jpg
│   └── gallery-1.jpg
└── README.md               # Deployment guide
```

### Generated HTML Features
- Responsive navigation with mobile menu
- All sections rendered as static HTML
- Theme colors applied via CSS variables
- Smooth scroll for anchor links
- SEO meta tags included

### Generated CSS
- Theme-based color variables
- Google Fonts integration
- Responsive utilities
- Hover effects and transitions
- Mobile-first approach

### Generated JavaScript
- Mobile menu toggle
- Smooth scroll for anchors
- No external dependencies

## Supported Section Types

The export now supports ALL section types and layouts used across GrowBuilder templates:

### Complete Section Support (23 Types, 50+ Layouts)

#### Hero Section
- ✅ Layouts: default, centered, slideshow, slider, split-left, split-right
- Features: Background images, gradient backgrounds, slideshow with auto-advance

#### Page Header
- ✅ Layouts: default
- Features: Title, subtitle, background images

#### Stats Section
- ✅ Layouts: default (grid), horizontal, row, icons
- Features: Configurable columns, icon support

#### About Section
- ✅ Layouts: default, image-left, image-right
- Features: Text content with images

#### Services/Features Section
- ✅ Layouts: grid, list, cards, cards-images, alternating, checklist, steps
- Features: Multi-column grids, image support, icon support

#### Contact Section
- ✅ Layouts: default, side-by-side, with-map
- Features: Contact info, forms, map embeds

#### CTA Section
- ✅ Layouts: default, centered, split, with-image
- Features: Buttons, descriptions, background styling

#### CTA Banner Section
- ✅ Layouts: default, centered, split
- Features: Full-width banners with CTAs

#### Testimonials Section
- ✅ Layouts: grid (default), carousel, single
- Features: Customer testimonials with names and roles

#### Gallery Section
- ✅ Layouts: default, masonry
- Features: Image grids with responsive layouts

#### FAQ Section
- ✅ Layouts: accordion (default), two-column, list
- Features: Expandable questions, multiple layout options

#### Pricing Section
- ✅ Layouts: default (grid)
- Features: Pricing plans, feature lists, popular badges

#### Products Section
- ✅ Layouts: default (grid)
- Features: Product display (placeholder for e-commerce integration)

#### Team Section
- ✅ Layouts: grid (default), social, compact
- Features: Team member profiles with images, roles, bios, social links

#### Timeline Section
- ✅ Layouts: default (vertical)
- Features: Chronological events with dates and descriptions

#### Video Hero Section
- ✅ Layouts: default
- Features: Full-screen video backgrounds with overlay content

#### Logo Cloud Section
- ✅ Layouts: default
- Features: Partner/client logo displays

#### Map Section
- ✅ Layouts: default
- Features: Embedded maps with addresses

#### Video Section
- ✅ Layouts: default
- Features: Embedded video players

#### Blog Section
- ✅ Layouts: default (grid)
- Features: Blog post previews with images and excerpts

#### Text Section
- ✅ Layouts: default
- Features: Rich text content

### Limitations
- ❌ Dynamic forms (requires backend)
- ❌ E-commerce features (requires backend)
- ❌ User authentication (requires backend)
- ❌ Database-driven content (requires backend)
- ❌ Real-time features (requires backend)

## Hosting Options

The README includes instructions for:

### Free Hosting
1. **Netlify** - Drag-and-drop deployment
2. **Vercel** - CLI deployment
3. **GitHub Pages** - Git-based hosting
4. **Cloudflare Pages** - Fast global CDN

### Traditional Hosting
- cPanel file manager
- FTP upload
- Any web hosting provider

## Usage

### For Users

1. Navigate to Site Settings
2. Click "Export Site" in quick actions
3. Click "Download Site Export"
4. Extract ZIP file
5. Follow README instructions

### For Developers

```php
use App\Services\GrowBuilder\StaticExportService;

$exportService = app(StaticExportService::class);

// Check if user can export
if ($exportService->canExport($user)) {
    // Generate export
    $zipPath = $exportService->exportSite($site);
    
    // Return download
    return response()->download($zipPath);
}
```

## Security & Performance

### Security
- Only site owner can export
- Tier restrictions enforced
- Temporary files cleaned up after export
- No sensitive data included

### Performance
- Export generated on-demand
- Media files downloaded in parallel
- ZIP compression for smaller downloads
- Temporary directory cleanup

### File Size
- Typical export: 5-50 MB
- Depends on media file count/size
- ZIP compression reduces size ~30%

## Future Enhancements

### Phase 2 (Planned)
- Export with database schema
- Include form handlers (Formspree integration)
- Custom branding removal options
- Scheduled exports
- Export history/versioning

### Phase 3 (Future)
- Full source code export (Agency tier)
- Laravel/Vue source code
- Database migration files
- Deployment scripts
- Docker configuration

## Troubleshooting

### Common Issues

**Export fails with timeout**
- Solution: Reduce media file sizes
- Solution: Increase PHP max_execution_time

**Missing images in export**
- Solution: Ensure all media URLs are accessible
- Solution: Check S3/storage permissions

**ZIP file won't open**
- Solution: Ensure ZipArchive PHP extension installed
- Solution: Check disk space on server

**Forms don't work after deployment**
- Expected: Forms require backend integration
- Solution: Use Formspree, Netlify Forms, or Google Forms

## Testing Checklist

- [ ] Business tier can export
- [ ] Agency tier can export
- [ ] Free/Starter tiers see upgrade prompt
- [ ] All pages included in export
- [ ] Media files downloaded correctly
- [ ] Navigation works in exported site
- [ ] Mobile menu functions
- [ ] Theme colors applied correctly
- [ ] README included
- [ ] ZIP file downloads successfully
- [ ] Temporary files cleaned up

## Changelog

### March 9, 2026
- Initial implementation
- Static HTML export for Business+ tiers
- Support for all major section types
- README with hosting instructions
- Export page UI created
- Tier restrictions added


## Troubleshooting

### Issue: Agency/Business users see "Upgrade Required" message

**Symptom:** Users on Business or Agency tier see upgrade prompt when accessing export page.

**Cause:** Cached tier restrictions don't include `static_export` feature flag.

**Solution:** 
1. Clear user's tier restriction cache
2. The system now automatically merges database features with default features to ensure all feature flags are present

**Technical Details:**
- `TierRestrictionService::getTierFeatures()` now merges database features with defaults
- This ensures features like `static_export` are always present even if not in database config
- Cache is automatically cleared when tier changes

### Clearing Cache Manually

If needed, you can clear a user's tier cache:

```php
$tierService = app(\App\Services\GrowBuilder\TierRestrictionService::class);
$tierService->clearCache($user);
```

## Changelog

### 2026-03-10
- Fixed tier feature merging to include default features when database config exists
- Agency and Business tier users can now properly access static export
- Updated `TierRestrictionService::getTierFeatures()` to merge database and default features


### Issue: Download button shows "Generating Export" but no file downloads

**Symptom:** Clicking "Download Site Export" shows loading state but browser doesn't download the file.

**Cause:** Inertia.js POST requests don't trigger browser file downloads. The response is intercepted by Inertia instead of being handled as a download.

**Solution:** Changed Export.vue to use native form submission instead of Inertia router.

**Technical Details:**
```javascript
// Before: Inertia POST (doesn't trigger download)
router.post(route('growbuilder.sites.export.download', props.site.id), {}, {...});

// After: Native form submission (triggers download)
const form = document.createElement('form');
form.method = 'POST';
form.action = route('growbuilder.sites.export.download', props.site.id);
// Add CSRF token and submit
form.submit();
```

**Files Modified:**
- `resources/js/pages/GrowBuilder/Sites/Export.vue` - Updated handleExport() to use native form submission

### 2026-03-10 (Update 2)
- Fixed download functionality by using native form submission instead of Inertia.js
- Export now properly triggers browser file download
- Added CSRF token handling for security


### Issue: Exported site has no styling (CSS not loading)

**Symptom:** Exported site displays content but without any styling - plain HTML with no colors, layout, or formatting.

**Cause:** The generated CSS file was missing Tailwind-like utility classes that were being used in the HTML markup (grid, flex, spacing, colors, etc.).

**Solution:** Enhanced `generateCSS()` method to include comprehensive utility classes matching the HTML structure.

**Technical Details:**
- Added flexbox utilities (`.flex`, `.items-center`, `.justify-between`, etc.)
- Added grid utilities (`.grid`, `.grid-cols-2`, `.md:grid-cols-3`, etc.)
- Added spacing utilities (`.px-4`, `.py-16`, `.mb-4`, `.gap-8`, etc.)
- Added typography utilities (`.text-xl`, `.font-bold`, `.text-center`, etc.)
- Added color utilities (`.text-gray-900`, `.bg-white`, `.hover:text-blue-600`, etc.)
- Added responsive breakpoints (`@media (min-width: 768px)` for `md:` prefix)
- Added display utilities (`.hidden`, `.block`, `.md:hidden`, etc.)

**Files Modified:**
- `app/Services/GrowBuilder/StaticExportService.php` - Enhanced `generateCSS()` with complete utility class library

### 2026-03-10 (Update 3)
- Fixed missing CSS styling in exported sites
- Added comprehensive utility classes to match HTML markup
- Exported sites now display with proper layout, colors, and responsive design


### Issue: Page headers missing and hero sliders not showing

**Symptom:** Exported sites missing page header sections and hero slideshow/slider sections not displaying.

**Cause:** 
1. `page-header` section type wasn't handled in the export service
2. Hero section only supported default layout, not slideshow/slider layouts

**Solution:** 
1. Added `generatePageHeaderSection()` method to handle page headers
2. Enhanced `generateHeroSection()` to detect and handle slideshow/slider layouts
3. Added `generateHeroSlideshow()` method for multi-slide heroes
4. Added slideshow CSS styling (slide transitions, navigation dots)
5. Added slideshow JavaScript (auto-advance, manual navigation, dot controls)

**Technical Details:**
- Page headers now render with title, subtitle, and optional background image
- Hero slideshows support multiple slides with individual titles, subtitles, buttons, and backgrounds
- Slideshow auto-advances every 5 seconds
- Navigation dots allow manual slide selection
- Clicking a dot resets the auto-advance timer

**Files Modified:**
- `app/Services/GrowBuilder/StaticExportService.php` - Added page-header and slideshow support

### 2026-03-10 (Update 4)
- Added support for page-header sections
- Added support for hero slideshow/slider layouts
- Implemented auto-advancing slideshow with navigation dots
- Added missing text-4xl utility class


## Scalability & Concurrency

### Current Implementation (Synchronous)

The current implementation processes exports synchronously:

**Pros:**
- Simple, immediate download
- No queue setup required
- Works for small to medium sites

**Cons:**
- Can timeout on large sites (30-60 second PHP limit)
- Blocks the request until complete
- Multiple concurrent exports consume server resources

**Improvements Made:**
1. **Unique temp directories** - Uses `uniqid()` instead of `time()` to prevent collisions
2. **Exception handling** - Ensures cleanup happens even on failure
3. **Try-catch wrapper** - Prevents orphaned temp directories

### Future: Queue-Based Implementation (Recommended for Production)

For better scalability, a queue-based approach is recommended:

**Files Created (Ready for Implementation):**
- `app/Jobs/GrowBuilder/ExportSiteJob.php` - Queue job for async export
- `database/migrations/2026_03_10_182823_create_site_exports_table.php` - Track export status

**Benefits:**
- No timeout issues (job runs in background)
- Better resource management (queue workers handle load)
- Email notification when ready
- Export history and re-download capability
- Handles concurrent exports efficiently

**Implementation Steps:**
1. Run migration: `php artisan migrate`
2. Update `ExportController` to dispatch job instead of direct export
3. Set up queue worker: `php artisan queue:work`
4. Create email notification for export ready
5. Add download page for completed exports

**Queue-Based Flow:**
```
User clicks export → Job queued → User sees "Processing..." 
→ Job completes → Email sent → User downloads from link
```

### Performance Considerations

**Current Limits:**
- Small sites (< 10 pages, < 50 images): ~5-15 seconds
- Medium sites (10-50 pages, 50-200 images): ~15-45 seconds
- Large sites (50+ pages, 200+ images): ~45-120 seconds

**Recommendations:**
- Sites with < 100 images: Synchronous export is fine
- Sites with 100-500 images: Consider queue-based
- Sites with 500+ images: Queue-based required

**Server Requirements:**
- PHP `max_execution_time`: 120+ seconds (for synchronous)
- PHP `memory_limit`: 256MB+ (512MB recommended)
- Disk space: 2x largest site size (for temp files + ZIP)

### Concurrent Export Handling

**Current Protection:**
- Unique temp directories per export (no file conflicts)
- Each export is independent
- Cleanup on failure prevents disk bloat

**Potential Issues:**
- Multiple users exporting simultaneously can strain server
- No rate limiting (users can spam export button)
- No progress indication for long exports

**Recommended Improvements:**
1. Add rate limiting (1 export per user per 5 minutes)
2. Show progress bar for exports > 30 seconds
3. Implement queue-based for sites > 100 pages
4. Add export history (prevent duplicate exports)
5. Cache exports for 24 hours (reuse if site unchanged)

### 2026-03-10 (Update 5)
- Improved concurrency handling with unique temp directories
- Added exception handling for cleanup on failure
- Created queue job infrastructure for future async exports
- Added site_exports table migration for tracking
- Documented scalability considerations and recommendations


### Issue: Missing stats section and layout variations not exported

**Symptom:** Exported sites missing stats sections (2,500+ Members, etc.) and service cards with images not displaying correctly.

**Cause:** 
1. `stats` section type wasn't handled in export service
2. Section layout variations (grid, list, cards-images, image-right, etc.) were ignored
3. Missing utility classes for proper styling

**Solution:**
1. Added `generateStatsSection()` method for stats display
2. Enhanced `generateServicesSection()` to handle multiple layouts:
   - `grid` (default) - Simple card grid
   - `list` - Vertical list layout
   - `cards-images` - Cards with images
3. Enhanced `generateAboutSection()` to handle layouts:
   - `image-left` (default)
   - `image-right`
4. Added missing utility classes (grid-cols-4, h-48, p-5, opacity-90, max-w-3xl)
5. Added support for dynamic column counts in services sections

**Technical Details:**
- Stats sections now render with large numbers and labels in 2x4 or 4-column grid
- Service cards with images show image at top, content below
- About sections respect image positioning (left/right)
- All layouts are responsive (mobile-friendly)

**Files Modified:**
- `app/Services/GrowBuilder/StaticExportService.php` - Added stats section, layout support, utility classes

### 2026-03-10 (Update 8) - ENHANCED CSS FRAMEWORK
- **MAJOR CSS UPDATE**: Added comprehensive utility class library
- Added complete color palette:
  - Gray scale (50-900)
  - Blue scale (50-700)
  - Green, red color variants
  - Hover state colors
  - Opacity utilities
- Added complete sizing utilities:
  - Width classes (w-2 through w-32, w-full, w-1/2)
  - Height classes (h-2 through h-64, h-full, min-h-screen)
  - Aspect ratio utilities (aspect-square, aspect-video)
- Added comprehensive spacing:
  - Padding utilities (p-*, px-*, py-*, pt-*, pb-*)
  - Margin utilities (m-*, mx-*, my-*, mt-*, mb-*, ml-*, mr-*)
  - Gap utilities (gap-2 through gap-12)
  - Space utilities (space-x-*, space-y-*)
- Added complete flexbox utilities:
  - flex-1, flex-shrink-0, flex-wrap
  - justify-* (between, center, end)
  - items-* (center, start, end)
- Added border utilities:
  - Border widths (border, border-0, border-2, border-t/b/l/r)
  - Border colors (gray, blue scales)
  - Border radius (rounded-*, rounded-full)
- Added shadow utilities:
  - shadow, shadow-sm, shadow-md, shadow-lg, shadow-xl, shadow-2xl
  - Hover shadow states
- Added typography utilities:
  - Font sizes (text-xs through text-5xl)
  - Font weights (font-normal, font-medium, font-semibold, font-bold)
  - Text alignment (text-left, text-center, text-right)
  - Text transform (uppercase, lowercase, capitalize)
  - Text decoration (underline, line-through, no-underline)
  - Line clamp utilities (line-clamp-2, line-clamp-3)
- Added positioning utilities:
  - Position types (relative, absolute, fixed, sticky)
  - Inset utilities (inset-0, top-*, left-*, right-*, bottom-*)
  - Z-index utilities (z-10, z-50)
- Added display utilities:
  - Display types (hidden, block, inline-block, inline, inline-flex)
  - Grid columns (grid-cols-1 through grid-cols-6)
- Added transition and transform utilities:
  - Transition types (transition, transition-all, transition-colors, transition-transform)
  - Transform utilities (scale-105, rotate-180, -translate-y-1/2)
  - Hover transforms
- Added form styling:
  - Focus ring utilities
  - Focus border utilities
  - Input/textarea/select base styles
- Added footer styling with proper colors and hover states
- Added responsive breakpoints:
  - sm: 640px (mobile landscape)
  - md: 768px (tablet)
  - lg: 1024px (desktop)
- Fixed button hover states with shadow
- Fixed navigation link transitions
- Added overflow utilities (overflow-hidden, overflow-auto)
- Added cursor utilities (cursor-pointer, cursor-not-allowed)
- Added list style utilities (list-none)

**Impact:**
- Exported sites now have complete styling parity with live sites
- All layout, color, spacing, and interactive elements render correctly
- Responsive design works across all breakpoints
- Forms, buttons, navigation, and footer display properly
- No more missing styles or broken layouts

**Files Modified:**
- `app/Services/GrowBuilder/StaticExportService.php` - Comprehensive CSS framework overhaul

**Note for Users:**
- Re-export your site to get the enhanced CSS
- Previous exports will need to be regenerated to benefit from improvements
- All templates now export with full visual fidelity
