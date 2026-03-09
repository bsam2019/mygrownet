# GrowBuilder Static Export Feature

**Last Updated:** March 9, 2026  
**Status:** Development  
**Tier Requirement:** Business+ (Business and Agency tiers)

## Overview

The Static Export feature allows GrowBuilder users to download their entire site as a self-contained static HTML package. This provides data portability, enables hosting flexibility, and builds trust by eliminating vendor lock-in concerns.

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

The export currently supports these section types:
- ✅ Hero (with background images)
- ✅ About (with images)
- ✅ Services/Features (grid layout)
- ✅ Contact (info display)
- ✅ CTA (call-to-action)
- ✅ Testimonials (cards)
- ✅ Gallery (image grid)
- ✅ Text (rich content)

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
